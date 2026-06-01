<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Lease;
use App\Models\Message;
use App\Models\MessageAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Build a query for conversations the user is a participant in
        $query = Conversation::with(['context', 'latestMessage.sender'])
            ->withCount(['messages as unread_count' => function ($q) use ($user) {
                $q->whereNull('read_at')->where('sender_id', '!=', $user->id);
            }]);

        if ($user->hasRole('landlord')) {
            $propertyIds = $user->landlord->properties()->pluck('id');
            $query->where(function ($q) use ($propertyIds) {
                $q->where(function ($sub) use ($propertyIds) {
                    $sub->where('context_type', Lease::class)
                        ->whereHasMorph('context', Lease::class, fn ($r) =>
                            $r->whereHas('unit', fn ($u) => $u->whereIn('property_id', $propertyIds))
                        );
                });
            });
        } elseif ($user->hasRole('caretaker')) {
            $propertyIds = $user->caretaker->properties()->pluck('properties.id');
            $query->where(function ($q) use ($propertyIds) {
                $q->where(function ($sub) use ($propertyIds) {
                    $sub->where('context_type', Lease::class)
                        ->whereHasMorph('context', Lease::class, fn ($r) =>
                            $r->whereHas('unit', fn ($u) => $u->whereIn('property_id', $propertyIds))
                        );
                });
            });
        }

        $conversations = $query->latest()->paginate(20);

        return view('messages.index', compact('conversations'));
    }

    public function show(Conversation $conversation)
    {
        $conversation->load(['context', 'messages.sender', 'messages.attachments']);

        // Mark messages as read for the current user
        $conversation->messages()
            ->where('sender_id', '!=', auth()->id())
            ->whereNull('read_at')
            ->each(fn ($msg) => $msg->markRead());

        return view('messages.show', compact('conversation'));
    }

    public function store(Request $request, Conversation $conversation)
    {
        $validated = $request->validate([
            'body'        => ['required', 'string'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:10240'],
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $request->user()->id,
            'body'            => $validated['body'],
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store("messages/{$conversation->id}", 'public');
                MessageAttachment::create([
                    'message_id' => $message->id,
                    'path'       => $path,
                    'filename'   => $file->getClientOriginalName(),
                    'mime_type'  => $file->getMimeType(),
                    'size'       => $file->getSize(),
                ]);
            }
        }

        return back()->with('success', 'Message sent.');
    }
}
