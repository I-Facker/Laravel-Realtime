<?php

namespace App\Http\Livewire\Chat;

use App\Models\User;
use App\Models\Conversation;
use Livewire\Component;

class ChatSupport extends Component
{
    public $conversations;
    public $selectedConversation;
    public $query;
    public $searchQuery = '';
    protected $listeners = ['refresh' => '$refresh'];

    public function mount()
    {
        $this->loadConversations();
    }

    public function render()
    {
        return view(
            'livewire.chat.chat-support',
            [
                'conversations' => $this->conversations
            ]
        );
    }

    private function loadConversations()
    {
        $currentUser = auth()->user();
        $supportUsers = User::where('role', 'support')->get();

        $this->conversations = Conversation::where(function ($query) use ($currentUser, $supportUsers) {
            $query->whereIn('sender_id', $supportUsers->pluck('id'))
                  ->where('receiver_id', $currentUser->id);
        })->orWhere(function ($query) use ($currentUser, $supportUsers) {
            $query->where('sender_id', $currentUser->id)
                  ->whereIn('receiver_id', $supportUsers->pluck('id'));
        })->latest('updated_at')->get();

        foreach ($supportUsers as $supportUser) {
            if (!$this->conversations->contains(function ($conversation) use ($supportUser, $currentUser) {
                return ($conversation->sender_id == $supportUser->id && $conversation->receiver_id == $currentUser->id) ||
                       ($conversation->sender_id == $currentUser->id && $conversation->receiver_id == $supportUser->id);
            })) {
                $newConversation = Conversation::create([
                    'sender_id' => $currentUser->id,
                    'receiver_id' => $supportUser->id,
                ]);
                $this->conversations->push($newConversation);
            }
        }
    }

    public function selectConversation($conversationId)
    {
        $this->selectedConversation = $this->conversations->find($conversationId);
    }
}
