<?php

namespace App\Http\Controllers;

use App\Events\GroupMessageSent;
use App\Events\UserTyping;
use App\Models\GroupMember;
use App\Models\GroupMessage;
use App\Models\StudyGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudyGroupController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Show all groups
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $user = Auth::user();

        $classIds = $user->classes()->pluck('classes.id');

        $groups = StudyGroup::withCount('members')
            ->whereIn('class_id', $classIds)
            ->latest()
            ->get();

        $classes = $user->classes()->get();

        return view('student.groups', compact('groups', 'classes'));
    }

    /*
    |--------------------------------------------------------------------------
    | My Groups
    |--------------------------------------------------------------------------
    */

    public function myStudyGroups()
    {
        $groups = Auth::user()
            ->studyGroups()
            ->withCount('members')
            ->get();

        return view('student.my_study_groups', compact('groups'));
    }


    /*
    |--------------------------------------------------------------------------
    | Create Group
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable',
            'max_members' => 'required|integer|min:2|max:100'
        ]);

        $group = StudyGroup::create([
            'name' => $request->name,
            'description' => $request->description,
            'class_id' => $request->class_id,
            'created_by' => Auth::id(),
            'max_members' => $request->max_members
        ]);

        /* creator automatically joins group */

        GroupMember::create([
            'group_id' => $group->id,
            'user_id' => Auth::id(),
            'role' => 'admin',
            'joined_at' => now()
        ]);

        return back()->with('success', 'Group created successfully.');
    }


    /*
    |--------------------------------------------------------------------------
    | Join Group
    |--------------------------------------------------------------------------
    */

    public function join($groupId)
    {
        $group = StudyGroup::findOrFail($groupId);

        if ($group->isFull()) {
            return back()->with('error', 'Group is full.');
        }

        $exists = GroupMember::where([
            'group_id' => $groupId,
            'user_id' => Auth::id()
        ])->exists();

        if ($exists) {
            return back()->with('error', 'Already joined.');
        }

        GroupMember::create([
            'group_id' => $groupId,
            'user_id' => Auth::id(),
            'role' => 'member',
            'joined_at' => now()
        ]);

        return back()->with('success', 'Joined successfully.');
    }


    /*
    |--------------------------------------------------------------------------
    | Group Chat Page
    |--------------------------------------------------------------------------
    */

  public function show($id)
{
    $group = StudyGroup::with(['messages.user','members'])->findOrFail($id);

    return view('student.group_chat', compact('group'));
}


    /*
    |--------------------------------------------------------------------------
    | Send Message
    |--------------------------------------------------------------------------
    */

    public function sendMessage(Request $request, StudyGroup $group)
    {
        try {

            $validated = $request->validate([
                'message' => 'required|string|max:2000'
            ]);

            $isMember = GroupMember::where('group_id', $group->id)
                ->where('user_id', Auth::id())
                ->exists();

            if (!$isMember) {
                return response()->json([
                    'error' => 'You are not a member of this group.'
                ], 403);
            }

            $message = GroupMessage::create([
                'group_id' => $group->id,
                'user_id' => Auth::id(),
                'message' => $validated['message']
            ]);

            $message->load('user');

            broadcast(new GroupMessageSent($message))->toOthers();

            return response()->json($message);
        } catch (\Throwable $e) {

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function typing(StudyGroup $group)
    {
        broadcast(new UserTyping($group->id, auth()->user()))->toOthers();

        return response()->json(['status' => 'ok']);
    }
}
