<?php

namespace App\Http\Livewire\Chat;

use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use Livewire\Component;

class Chat extends Component
{

    public $query;
    public $selectedConversation;
    public $searchQuery;
    public $activeTab = 'all';
    public $countConversation;
    public $conversations;

    public function mount()
    {
        // Count the number of conversations
        $this->countConversation = $this->countConversationsWithUsers();

        if (!empty($this->query)) {
            if (is_numeric($this->query)) {
                $this->selectedConversation = Conversation::find($this->query);
            } else {
                $user = User::where('username', $this->query)->first();

                if ($user->id === auth()->id()) {
                    abort(404);
                }

                if ($user) {
                    $this->selectedConversation = Conversation::where('receiver_id', auth()->id())
                        ->where('sender_id', $user->id)
                        ->first();
                    
                    if (!$this->selectedConversation) {
                        $this->selectedConversation = $this->findOrCreateConversation($user);
                    }
                }
            }

            if (!$this->selectedConversation) {
                abort(404);
            }

            $user = User::where('username', $this->query)->orWhere('id', $this->query)->first();
            if ($user) {
                if ($user->role === 'support') {
                    $this->setActiveTab('support');
                } else {
                    $this->setActiveTab('all');
                }
            } else {
                $this->setActiveTab('all');
            }
            
            #mark message belogning to receiver as read 
            Message::where('conversation_id', $this->selectedConversation->id)
                ->where('receiver_id', auth()->id())
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }
    }

    public function render()
    {
        return view('livewire.chat.index');
    }

    public function search()
    {
        $user = User::where('username', 'like', $this->searchQuery)->first();

        if ($user->id === auth()->id()) {
            return $this->showAlert('Lỗi', 'Bạn không thể nhắn tin cho chính mình.', 'error');
        }

        if ($user) {
            $conversation = $this->findOrCreateConversation($user);
            $this->selectedConversation = $conversation->id;
            $this->searchQuery = '';
            $this->showAlert('Thành công', 'Cuộc hội thoại đã được tạo hoặc chọn.', 'success');

            return redirect()->route('chat', $conversation->id);
        } else {
            $this->showAlert('Lỗi', 'Không tìm thấy người dùng.', 'error');
        }
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    private function countConversationsWithUsers()
    {
        $currentUserId = auth()->id();
        
        return Conversation::where(function ($query) use ($currentUserId) {
            $query->where('sender_id', $currentUserId)
                  ->orWhere('receiver_id', $currentUserId);
        })->where(function ($query) use ($currentUserId) {
            $query->whereHas('sender', function ($q) use ($currentUserId) {
                $q->where('role', 'user')
                  ->where('id', '!=', $currentUserId);
            })->orWhereHas('receiver', function ($q) use ($currentUserId) {
                $q->where('role', 'user')
                  ->where('id', '!=', $currentUserId);
            });
        })->count();
    }

    private function findOrCreateConversation($user)
    {
        $existingConversation = Conversation::where(function ($query) use ($user) {
            $query->where('sender_id', auth()->id())
                  ->where('receiver_id', $user->id);
        })->orWhere(function ($query) use ($user) {
            $query->where('sender_id', $user->id)
                  ->where('receiver_id', auth()->id());
        })->first();

        if ($existingConversation) {
            return $existingConversation;
        }

        return Conversation::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $user->id,
        ]);
    }

    private function showAlert($title, $message, $type)
    {
        $this->dispatchBrowserEvent('show-alert', [
            'title' => $title,
            'message' => $message,
            'type' => $type,
        ]);
    }
}
