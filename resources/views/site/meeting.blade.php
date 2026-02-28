@extends('site.layouts.app')

@section('pageTitle', $session->title)

@section('content')

<div class="container-fluid py-3">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <input type="hidden" id="roomId" value="{{ $session->meeting_code }}">
    <input type="hidden" id="userId" value="{{ auth()->user()->id }}">
    <input type="hidden" id="userName" value="{{ auth()->user()->name }}">
    <input type="hidden" id="userRole" value="{{ auth()->user()->role }}">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4>{{ $session->title }}</h4>
            <small class="text-muted">{{ $virtualClass->name }}</small>
        </div>
        <span id="status" class="badge bg-success">Connected</span>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Main Video Area -->
            <div class="bg-dark rounded position-relative" style="height: 500px;">
                <!-- Remote Videos Container -->
                <div id="remoteVideos" class="h-100 d-flex flex-wrap justify-content-center align-items-center gap-3 p-3"></div>
                
                <!-- Local Video (Small) -->
                <div class="position-absolute bottom-0 end-0 m-3" style="width: 180px;">
                    <video id="localVideo" autoplay muted playsinline class="w-100 rounded shadow"></video>
                    <div class="text-center text-white small mt-1 bg-dark rounded p-1">You</div>
                </div>
                
                <!-- No participants message -->
                <div id="noParticipants" class="text-white text-center h-100 d-flex flex-column justify-content-center align-items-center">
                    <i class="bi bi-people" style="font-size: 3rem;"></i>
                    <p class="mt-2">Waiting for other participants...</p>
                </div>
            </div>
            
            <!-- Controls -->
            <div class="d-flex gap-2 mt-3 justify-content-center">
                <button id="toggleCam" class="btn btn-primary">
                    <i class="bi bi-camera-video"></i> Camera
                </button>
                <button id="toggleMic" class="btn btn-secondary">
                    <i class="bi bi-mic"></i> Mic
                </button>
                <button id="leaveCall" class="btn btn-danger">
                    <i class="bi bi-telephone-x"></i> Leave
                </button>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Participants -->
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-people"></i> Participants (<span id="participantCount">1</span>)
                    </h6>
                </div>
                <div class="card-body p-2" id="participantsList" style="max-height: 200px; overflow-y: auto;">
                    <!-- Participants will be added here -->
                </div>
            </div>
            
            <!-- Chat -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-chat"></i> Chat
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div id="chatMessages" class="p-2" style="height: 300px; overflow-y: auto;">
                        @foreach($session->chats->unique('message') as $chat)
                            <div class="mb-2">
                                <div class="bg-light rounded p-2">
                                    <strong>{{ $chat->user->name }}:</strong> {{ $chat->message }} 
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="p-2 border-top">
                        <div class="input-group">
                            <input type="text" id="chatInput" class="form-control" 
                                   placeholder="Type a message..." autocomplete="off">
                            <button id="sendMessage" class="btn btn-primary">
                                <i class="bi bi-send"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const room = document.getElementById('roomId').value;
const userId = document.getElementById('userId').value;
const userName = document.getElementById('userName').value;
const userRole = document.getElementById('userRole').value;
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

let localStream;
let peerConnections = {};
let participants = new Map();
let sentMessages = new Set();

// Initialize
async function init() {
    try {
        // Get user media
        localStream = await navigator.mediaDevices.getUserMedia({ 
            video: true, 
            audio: true 
        });
        
        document.getElementById('localVideo').srcObject = localStream;
        
        // Add yourself to participants
        participants.set(userId, { 
            id: userId, 
            name: userName, 
            role: userRole,
            isMicOn: true, 
            isCameraOn: true
        });
        
        updateParticipantList();
        
        // Notify server we joined
        await fetch('/webrtc/join', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json', 
                'X-CSRF-TOKEN': csrfToken 
            },
            body: JSON.stringify({ room })
        });
        
        setupWebSocketListeners();
        setupEventListeners();
        
        console.log('Meeting initialized');
        
    } catch (error) {
        console.error('Failed to initialize:', error);
        updateStatus('Failed to connect', 'danger');
    }
}

function setupWebSocketListeners() {
    window.Echo.channel('webrtc.' + room)
        .listen('.webrtc.signal', handleSignal)
        .listen('.session.chat', handleChatMessage);
}

async function handleSignal(e) {
    const { userId: fromId, data, userName: fromName, type } = e;
    
    if (fromId === userId && type !== 'presence') return;
    
    switch(type) {
        case 'presence':
            handlePresenceSignal(fromId, fromName, data);
            break;
        case 'signal':
            await handleWebRTCSignal(fromId, data);
            break;
    }
}

function handlePresenceSignal(fromId, fromName, data) {
    if (data.action === 'joined') {
        if (!participants.has(fromId)) {
            participants.set(fromId, { 
                id: fromId, 
                name: fromName, 
                isMicOn: true, 
                isCameraOn: true
            });
            addChatMessage(`${fromName} joined the meeting`, 'system');
            updateParticipantList();
            
            if (fromId !== userId) {
                createPeerConnection(fromId);
            }
        }
    } else if (data.action === 'left') {
        participants.delete(fromId);
        removeRemoteVideo(fromId);
        
        if (peerConnections[fromId]) {
            peerConnections[fromId].close();
            delete peerConnections[fromId];
        }
        
        addChatMessage(`${fromName} left the meeting`, 'system');
        updateParticipantList();
    }
}

async function handleWebRTCSignal(fromId, data) {
    try {
        if (data.offer) {
            await handleOffer(fromId, data.offer);
        } else if (data.answer) {
            await handleAnswer(fromId, data.answer);
        } else if (data.candidate) {
            await handleCandidate(fromId, data.candidate);
        }
    } catch (error) {
        console.error('Error handling WebRTC signal:', error);
    }
}

async function handleOffer(fromId, offer) {
    if (!peerConnections[fromId]) {
        createPeerConnection(fromId);
    }
    
    const pc = peerConnections[fromId];
    
    try {
        await pc.setRemoteDescription(new RTCSessionDescription(offer));
        
        const answer = await pc.createAnswer();
        await pc.setLocalDescription(answer);
        
        sendWebRTCSignal(fromId, { answer: answer });
        
    } catch (error) {
        console.error('Error handling offer:', error);
    }
}

async function handleAnswer(fromId, answer) {
    const pc = peerConnections[fromId];
    if (!pc) return;
    
    try {
        await pc.setRemoteDescription(new RTCSessionDescription(answer));
    } catch (error) {
        console.error('Error handling answer:', error);
    }
}

async function handleCandidate(fromId, candidate) {
    const pc = peerConnections[fromId];
    if (!pc) return;
    
    try {
        await pc.addIceCandidate(new RTCIceCandidate(candidate));
    } catch (error) {
        console.error('Error adding ICE candidate:', error);
    }
}

function createPeerConnection(remoteUserId) {
    if (peerConnections[remoteUserId]) {
        return peerConnections[remoteUserId];
    }
    
    const pc = new RTCPeerConnection({
        iceServers: [
            { urls: 'stun:stun.l.google.com:19302' }
        ]
    });
    
    peerConnections[remoteUserId] = pc;
    
    // Add local tracks
    if (localStream) {
        localStream.getTracks().forEach(track => {
            pc.addTrack(track, localStream);
        });
    }
    
    // Handle remote tracks
    pc.ontrack = (event) => {
        const stream = event.streams[0];
        if (!stream) return;
        
        // Hide "no participants" message
        document.getElementById('noParticipants').style.display = 'none';
        
        let video = document.getElementById('remoteVideo-' + remoteUserId);
        if (!video) {
            video = document.createElement('video');
            video.id = 'remoteVideo-' + remoteUserId;
            video.autoplay = true;
            video.playsInline = true;
            video.className = 'remote-video';
            video.style.width = '300px';
            video.style.height = '225px';
            video.style.objectFit = 'cover';
            video.style.borderRadius = '8px';
            video.style.border = '2px solid #4a5568';
            
            // Create container with user info
            const container = document.createElement('div');
            container.className = 'video-container position-relative';
            
            const nameLabel = document.createElement('div');
            nameLabel.className = 'video-name-label';
            nameLabel.textContent = participants.get(remoteUserId)?.name || 'User';
            
            container.appendChild(video);
            container.appendChild(nameLabel);
            
            document.getElementById('remoteVideos').appendChild(container);
        }
        video.srcObject = stream;
    };
    
    // Handle ICE candidates
    pc.onicecandidate = (event) => {
        if (event.candidate) {
            sendWebRTCSignal(remoteUserId, { candidate: event.candidate });
        }
    };
    
    // Handle connection state
    pc.onconnectionstatechange = () => {
        if (pc.connectionState === 'failed' || pc.connectionState === 'disconnected') {
            console.log(`Connection with ${remoteUserId} failed or disconnected`);
        }
    };
    
    // Create and send initial offer
    setTimeout(() => {
        if (pc.connectionState === 'new') {
            createAndSendOffer(remoteUserId);
        }
    }, 1000);
    
    return pc;
}

async function createAndSendOffer(remoteUserId) {
    const pc = peerConnections[remoteUserId];
    if (!pc) return;
    
    try {
        const offer = await pc.createOffer();
        await pc.setLocalDescription(offer);
        sendWebRTCSignal(remoteUserId, { offer: offer });
    } catch (error) {
        console.error('Error creating offer:', error);
    }
}

function sendWebRTCSignal(targetUserId, data) {
    fetch('/webrtc/signal', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json', 
            'X-CSRF-TOKEN': csrfToken 
        },
        body: JSON.stringify({ 
            room, 
            data,
            targetUserId,
            type: 'signal' 
        })
    }).catch(error => console.error('Signal error:', error));
}

function removeRemoteVideo(id) {
    const element = document.getElementById('remoteVideo-' + id);
    if (element) {
        element.parentElement?.remove();
    }
    
    // Show "no participants" message if no remote videos
    if (document.querySelectorAll('.remote-video').length === 0) {
        document.getElementById('noParticipants').style.display = 'flex';
    }
}

function updateParticipantList() {
    const list = document.getElementById('participantsList');
    const count = document.getElementById('participantCount');
    
    if (!list) return;
    
    let html = '';
    participants.forEach(p => {
        const roleBadge = p.role === 'teacher' ? 
            '<span class="badge bg-danger ms-1" style="font-size: 0.6rem;">Teacher</span>' : '';
        
        html += `
            <div class="d-flex align-items-center mb-2">
                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" 
                     style="width:32px;height:32px">
                    <i class="bi bi-person text-white"></i>
                </div>
                <div class="flex-grow-1">
                    <small class="fw-bold">
                        ${p.name} ${roleBadge}
                    </small>
                    <div>
                        <i class="bi bi-mic ${p.isMicOn ? 'text-success' : 'text-danger'} me-1"></i>
                        <i class="bi bi-camera-video ${p.isCameraOn ? 'text-success' : 'text-danger'}"></i>
                    </div>
                </div>
            </div>`;
    });
    
    list.innerHTML = html;
    if (count) {
        count.textContent = participants.size;
    }
}

function handleChatMessage(e) {
    const { userId: fromId, userName: fromName, message } = e;
    
    if (fromId === userId) return;
    
    const messageId = `${fromId}-${message}`;
    
    if (!sentMessages.has(messageId)) {
        sentMessages.add(messageId);
        addChatMessage(message, fromName);
        
        if (sentMessages.size > 100) {
            const firstKey = Array.from(sentMessages.keys())[0];
            sentMessages.delete(firstKey);
        }
    }
}

function addChatMessage(msg, sender = 'system') {
    const container = document.getElementById('chatMessages');
    if (!container) return;
    
    const div = document.createElement('div');
    
    if (sender === 'system') {
        div.className = 'text-center text-muted small my-1';
        div.textContent = msg;
    } else {
        div.className = 'mb-2';
        const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        div.innerHTML = `
            <div class="bg-light rounded p-2">
                <strong>${sender}:</strong> ${msg}
                <small class="text-muted d-block">${time}</small>
            </div>`;
    }
    
    container.appendChild(div);
    container.scrollTop = container.scrollHeight;
}

function setupEventListeners() {
    // Toggle Camera
    const toggleCamBtn = document.getElementById('toggleCam');
    if (toggleCamBtn) {
        toggleCamBtn.addEventListener('click', () => {
            if (localStream) {
                const track = localStream.getVideoTracks()[0];
                if (track) {
                    track.enabled = !track.enabled;
                    const participant = participants.get(userId);
                    if (participant) {
                        participant.isCameraOn = track.enabled;
                        updateParticipantList();
                    }
                }
            }
        });
    }
    
    // Toggle Mic
    const toggleMicBtn = document.getElementById('toggleMic');
    if (toggleMicBtn) {
        toggleMicBtn.addEventListener('click', () => {
            if (localStream) {
                const track = localStream.getAudioTracks()[0];
                if (track) {
                    track.enabled = !track.enabled;
                    const participant = participants.get(userId);
                    if (participant) {
                        participant.isMicOn = track.enabled;
                        updateParticipantList();
                    }
                }
            }
        });
    }
    
    // Chat
    const sendMessageBtn = document.getElementById('sendMessage');
    const chatInput = document.getElementById('chatInput');
    
    if (sendMessageBtn) {
        sendMessageBtn.addEventListener('click', sendChatMessage);
    }
    
    if (chatInput) {
        chatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                sendChatMessage();
            }
        });
    }
    
    // Leave Call
    const leaveBtn = document.getElementById('leaveCall');
    if (leaveBtn) {
        leaveBtn.addEventListener('click', () => {
            if (confirm('Are you sure you want to leave the meeting?')) {
                leaveMeeting();
            }
        });
    }
}

function sendChatMessage() {
    const input = document.getElementById('chatInput');
    if (!input) return;
    
    const message = input.value.trim();
    if (!message) return;
    
    const messageId = `${userId}-${message}`;
    
    if (!sentMessages.has(messageId)) {
        sentMessages.add(messageId);
        
        // Show locally
        addChatMessage(message, 'You');
        
        // Send to server
        fetch('/webrtc/chat', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json', 
                'X-CSRF-TOKEN': csrfToken 
            },
            body: JSON.stringify({ 
                room: room,
                message: message
            })
        }).catch(error => console.error('Chat error:', error));
        
        input.value = '';
        
        // Clean up old messages
        if (sentMessages.size > 100) {
            const firstKey = Array.from(sentMessages.keys())[0];
            sentMessages.delete(firstKey);
        }
    }
}

function leaveMeeting() {
    // Close all peer connections
    Object.values(peerConnections).forEach(pc => {
        try {
            pc.close();
        } catch (e) {
            console.error('Error closing peer connection:', e);
        }
    });
    
    // Stop local stream
    if (localStream) {
        localStream.getTracks().forEach(track => track.stop());
    }
    
    // Notify server
    fetch('/webrtc/leave', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken 
        },
        body: JSON.stringify({ room })
    }).finally(() => {
        window.location.href = "{{ route('student.course.show', $virtualClass->id) }}";
    });
}

function updateStatus(text, type = 'secondary') {
    const badge = document.getElementById('status');
    if (badge) {
        badge.textContent = text;
        badge.className = `badge bg-${type}`;
    }
}

// Initialize when DOM is loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
</script>

<style>
.remote-video {
    transition: all 0.3s ease;
}

.remote-video:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.video-container {
    position: relative;
    display: inline-block;
}

.video-name-label {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 4px 8px;
    font-size: 0.75rem;
    border-bottom-left-radius: 6px;
    border-bottom-right-radius: 6px;
    text-align: center;
}

#noParticipants {
    display: flex;
}

#participantsList::-webkit-scrollbar,
#chatMessages::-webkit-scrollbar {
    width: 6px;
}

#participantsList::-webkit-scrollbar-track,
#chatMessages::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

#participantsList::-webkit-scrollbar-thumb,
#chatMessages::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

#participantsList::-webkit-scrollbar-thumb:hover,
#chatMessages::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>
@endsection