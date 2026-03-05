@extends('site.layouts.app')

@section('pageTitle', $group->name)

@section('content')

    <style>
        .chat-container {
            height: 70vh;
            display: flex;
            flex-direction: column;
        }

        .chat-body {
            flex: 1;
            overflow-y: auto;
            background: #f5f7fb;
            padding: 20px;
        }

        .message-row {
            display: flex;
            margin-bottom: 12px;
        }

        .message-row.me {
            justify-content: flex-end;
        }

        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #0d6efd;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 8px;
        }

        .bubble {
            max-width: 320px;
            padding: 10px 14px;
            border-radius: 16px;
            font-size: 14px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }

        .bubble.me {
            background: #0d6efd;
            color: #fff;
            border-bottom-right-radius: 4px;
        }

        .bubble.other {
            background: #fff;
            border-bottom-left-radius: 4px;
        }

        .message-meta {
            font-size: 11px;
            color: #6c757d;
            margin-top: 3px;
        }

        .chat-input {
            border-top: 1px solid #eee;
            background: #fff;
            padding: 12px;
        }

        .typing {
            font-size: 12px;
            color: #888;
            padding-left: 15px;
            height: 20px;
        }
    </style>


    <section class="py-4 bg-light" style="min-height:100vh;">
        <div class="container">

            <div class="card border-0 shadow rounded-4 chat-container">

                <!-- HEADER -->

                <div class="card-header bg-white d-flex justify-content-between align-items-center">

                    <div>
                        <h5 class="fw-bold mb-0">{{ $group->name }}</h5>
                        <small class="text-muted">{{ $group->members()->count() }} members</small>
                    </div>

                    <a href="{{ route('student.my_groups') }}" class="btn btn-light btn-sm">← Back</a>

                </div>
                <div class="px-3 pt-2 pb-2 border-bottom bg-light">
                    <div class="d-flex flex-wrap gap-2 align-items-center">

                        @foreach ($group->members as $member)
                            <div class="d-flex align-items-center bg-white border rounded-pill px-2 py-1">

                                <div class="avatar me-2" style="width:20px;height:20px;font-size:12px;">
                                    {{ strtoupper(substr($member->name, 0, 1)) }}
                                </div>

                                <small class="fw-semibold">
                                    {{ $member->name }}
                                </small>

                            </div>
                        @endforeach

                    </div>
                </div>

                <!-- CHAT BODY -->

                <div id="chatBody" class="chat-body">

                    @foreach ($group->messages as $msg)
                        @php
                            $isMe = $msg->user_id == auth()->id();
                        @endphp

                        <div class="message-row {{ $isMe ? 'me' : '' }}">

                            @if (!$isMe)
                                <div class="avatar">
                                    {{ strtoupper(substr($msg->user->name, 0, 1)) }}
                                </div>
                            @endif

                            <div>

                                @if (!$isMe)
                                    <div class="small text-muted mb-1">{{ $msg->user->name }}</div>
                                @endif

                                <div class="bubble {{ $isMe ? 'me' : 'other' }}">
                                    {{ $msg->message }}
                                </div>

                                <div class="message-meta">
                                    {{ $msg->created_at->format('H:i') }}
                                </div>

                            </div>

                        </div>
                    @endforeach

                </div>

                <div id="typingIndicator" class="typing"></div>


                <!-- MESSAGE INPUT -->

                <div class="chat-input">

                    <form id="chatForm" class="d-flex gap-2">

                        <input type="text" id="messageInput" class="form-control rounded-pill"
                            placeholder="Type a message..." autocomplete="off" required>

                        <button class="btn btn-primary rounded-pill px-4">
                            Send
                        </button>

                    </form>

                </div>

            </div>
        </div>
    </section>


    <script>
        const messageTone = new Audio('/Message-Alert.mp3');
        messageTone.volume = 0.6;

        const groupId = "{{ $group->id }}";
        const userId = "{{ auth()->id() }}";
        const userName = "{{ auth()->user()->name }}";

        const chatBody = document.getElementById('chatBody');
        const messageInput = document.getElementById('messageInput');
        const typingIndicator = document.getElementById('typingIndicator');

        let typingTimer;

        chatBody.scrollTop = chatBody.scrollHeight;


        /*
        |--------------------------------------------------------------------------
        | WebSocket Listener (same structure as WebRTC)
        |--------------------------------------------------------------------------
        */

        function setupGroupChatListeners() {


            window.Echo.channel('group.' + groupId)
                .listen('.group.chat', handleGroupChatMessage);

        }


        /*
        |--------------------------------------------------------------------------
        | Receive realtime message
        |--------------------------------------------------------------------------
        */

        function handleGroupChatMessage(e) {

            const {
                userId: fromId,
                userName: fromName,
                message
            } = e;

            if (fromId == userId) return;

            // 🔊 play sound
            messageTone.currentTime = 0;
            messageTone.play().catch(() => {});

            const html = `
                    <div class="message-row">

                    <div class="avatar">
                    ${fromName.charAt(0)}
                    </div>

                    <div>

                    <div class="small text-muted mb-1">
                    ${fromName}
                    </div>

                    <div class="bubble other">
                    ${message}
                    </div>

                    <div class="message-meta">
                    ${new Date().toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'})}
                    </div>

                    </div>

                    </div>
                    `;

            chatBody.insertAdjacentHTML("beforeend", html);
            chatBody.scrollTop = chatBody.scrollHeight;

        }


        /*
        |--------------------------------------------------------------------------
        | Send message
        |--------------------------------------------------------------------------
        */

        document.getElementById('chatForm').addEventListener('submit', function(e) {

            e.preventDefault();

            const message = messageInput.value.trim();

            if (!message) return;

            // show locally
            const html = `
<div class="message-row me">

<div>

<div class="bubble me">
${message}
</div>

<div class="message-meta">
${new Date().toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'})}
</div>

</div>

</div>
`;

            chatBody.insertAdjacentHTML("beforeend", html);
            chatBody.scrollTop = chatBody.scrollHeight;

            fetch("{{ route('student.groups.sendMessage', $group->id) }}", {

                method: "POST",

                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },

                body: JSON.stringify({
                    message: message
                })

            }).catch(error => {
                console.error("Send message error:", error);
            });

            messageInput.value = "";

        });


        /*
        |--------------------------------------------------------------------------
        | Typing indicator (WebSocket whisper)
        |--------------------------------------------------------------------------
        */

        document.addEventListener("DOMContentLoaded", function() {

            if (!window.Echo) {
                console.error("Echo is not loaded yet");
                return;
            }

            const channel = window.Echo.join('group.' + groupId);

            messageInput.addEventListener('input', () => {
                channel.whisper('typing', {
                    name: userName
                });
            });

            channel.listenForWhisper('typing', (e) => {

                typingIndicator.innerHTML = e.name + " is typing...";

                clearTimeout(typingTimer);

                typingTimer = setTimeout(() => {
                    typingIndicator.innerHTML = '';
                }, 1500);

            });

        });


        /*
        |--------------------------------------------------------------------------
        | Initialize
        |--------------------------------------------------------------------------
        */

        document.addEventListener("DOMContentLoaded", () => {

            setupGroupChatListeners();

        });
    </script>

@endsection
