<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index()
    {
        // Users who have sent at least one message
        $conversations = User::whereHas('chatMessages', fn ($q) => $q->where('direction', 'user'))
            ->withCount(['chatMessages as unread_count' => fn ($q) => $q->where('direction', 'user')->where('is_read', false)])
            ->withMax('chatMessages as last_message_at', 'created_at')
            ->orderByDesc('last_message_at')
            ->paginate(20);

        return view('admin.chat.index', compact('conversations'));
    }

    public function conversation(User $user)
    {
        // All conversations view with first user selected
        $conversations = User::whereHas('chatMessages')
            ->withCount(['chatMessages as unread_count' => fn ($q) => $q->where('direction', 'user')->where('is_read', false)])
            ->withMax('chatMessages as last_message_at', 'created_at')
            ->orderByDesc('last_message_at')
            ->paginate(20);

        $messages = ChatMessage::where('user_id', $user->id)
            ->orderBy('created_at')
            ->get();

        // Mark user messages as read
        ChatMessage::where('user_id', $user->id)
            ->where('direction', 'user')
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return view('admin.chat.index', compact('conversations', 'messages', 'user'));
    }

    public function reply(Request $request, User $user)
    {
        $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        ChatMessage::create([
            'user_id'   => $user->id,
            'admin_id'  => Auth::id(),
            'message'   => $request->message,
            'direction' => 'admin',
            'is_read'   => false,
        ]);

        return back()->with('success', 'Message sent.');
    }
}
