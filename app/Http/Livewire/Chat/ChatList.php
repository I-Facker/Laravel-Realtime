<?php

namespace App\Http\Livewire\Chat;

use App\Models\User;
use App\Models\Conversation;
use Livewire\Component;

class ChatList extends Component
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
    
    public function deleteByUser($id) {
        $userId = auth()->id();
        $conversation = Conversation::find(decrypt($id));

        $conversation->messages()->each(function($message) use($userId){
            if($message->sender_id === $userId){
                $message->update(['sender_deleted_at'=>now()]);
            } elseif ($message->receiver_id === $userId){
                $message->update(['receiver_deleted_at'=>now()]);
            }
        });

        $receiverAlsoDeleted = $conversation->messages()
            ->where(function ($query) use($userId) {
                $query->where('sender_id',$userId)
                      ->orWhere('receiver_id',$userId);
            })->where(function ($query) use($userId){
                $query->whereNull('sender_deleted_at')
                        ->orWhereNull('receiver_deleted_at');
            })->doesntExist();

        if ($receiverAlsoDeleted) {
            $conversation->forceDelete();
            # code...
        }

        return redirect(route('messenger'));
   }

    public function render()
    {
        return view(
            'livewire.chat.chat-list',
            [
                'conversations' => $this->conversations
            ]
        );
    }

    private function loadConversations()
    {
        $currentUser = auth()->user();
        $supportUsers = User::where('role', 'user')->get();

        $this->conversations = Conversation::where(function ($query) use ($currentUser, $supportUsers) {
            $query->whereIn('sender_id', $supportUsers->pluck('id'))
                  ->where('receiver_id', $currentUser->id);
        })->orWhere(function ($query) use ($currentUser, $supportUsers) {
            $query->where('sender_id', $currentUser->id)
                  ->whereIn('receiver_id', $supportUsers->pluck('id'));
        })->latest('updated_at')->get();
    }

    public function selectConversation($conversationId)
    {
        $this->selectedConversation = $this->conversations->find($conversationId);
    }
}
