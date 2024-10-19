<?php

namespace App\Http\Livewire\Chat;

use App\Models\Message;
use App\Notifications\MessageRead;
use App\Notifications\MessageSent;
use Livewire\Component;
use Livewire\WithFileUploads;

class ChatBox extends Component
{
    use WithFileUploads;

    public $selectedConversation;
    public $body;
    public $loadedMessages;
    public $image;
    public $imagePreview = '';

    public $paginate_var = 10;

    protected $listeners = [
        'loadMore'
    ];


    public function getListeners()
    {
        $auth_id = auth()->user()->id;

        return [
            'loadMore',
            "echo-private:users.{$auth_id},.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated" => 'broadcastedNotifications'
        ];
    }

    public function broadcastedNotifications($event)
    {
        if ($event['type'] == MessageSent::class) {
            if ($this->selectedConversation && $event['conversation_id'] == $this->selectedConversation->id) {
                $this->dispatchBrowserEvent('scroll-bottom');
                $newMessage = Message::find($event['message_id']);

                #push message
                if (!$this->loadedMessages->contains('id', $newMessage->id)) {
                    $this->loadedMessages->push($newMessage);
                }

                #mark as read
                $newMessage->read_at = now();
                $newMessage->save();

                #broadcast 
                $this->selectedConversation->getReceiver()
                    ->notify(new MessageRead($this->selectedConversation->id));
            }
        }
    }

    public function loadMore(): void
    {
        #increment
        $this->paginate_var += 10;

        #call loadMessages()
        $this->loadMessages();

        #update the chat height
        $this->dispatchBrowserEvent('update-chat-height');
    }

    public function loadMessages()
    {
        $userId = auth()->id();

        if ($this->selectedConversation) {
            #get count
            $count = Message::where('conversation_id', $this->selectedConversation->id)
                ->where(function ($query) use ($userId) {
                    $query->where(function ($subQuery) use ($userId) {
                        $subQuery->where('sender_id', $userId)
                            ->whereNull('sender_deleted_at');
                    })->orWhere(function ($subQuery) use ($userId) {
                        $subQuery->where('receiver_id', $userId)
                            ->whereNull('receiver_deleted_at');
                    });
                })
                ->count();

            #skip and query
            $this->loadedMessages = Message::where('conversation_id', $this->selectedConversation->id)
                ->where(function ($query) use ($userId) {
                    $query->where(function ($subQuery) use ($userId) {
                        $subQuery->where('sender_id', $userId)
                            ->whereNull('sender_deleted_at');
                    })->orWhere(function ($subQuery) use ($userId) {
                        $subQuery->where('receiver_id', $userId)
                            ->whereNull('receiver_deleted_at');
                    });
                })
                ->latest()
                // ->skip($count - $this->paginate_var)
                // ->take($this->paginate_var)
                ->get()
                ->reverse();
            
            $this->loadedMessages = $this->loadedMessages->merge($this->loadedMessages->filter(function ($message) {
                return !$this->loadedMessages->contains('id', $message->id);
            }));

            return $this->loadedMessages;
        }
        return [];
    }

    public function sendMessage()
    {
        $this->validate(['body' => 'required|string']);

        $imageUrl = null;
        if ($this->image) {
            $imageUrl = $this->image->store('chat-images', 'public');
        }

        $createdMessage = Message::create([
            'conversation_id' => $this->selectedConversation->id,
            'sender_id' => auth()->id(),
            'receiver_id' => $this->selectedConversation->getReceiver()->id,
            'body' => $this->body,
            'image' => $imageUrl,
        ]);

        $this->reset('body', 'image');

        #scroll to bottom
        $this->dispatchBrowserEvent('scroll-bottom');

        #push the message
        if (!$this->loadedMessages->contains('id', $createdMessage->id)) {
            $this->loadedMessages->push($createdMessage);
        }

        #update conversation model
        $this->selectedConversation->updated_at = now();
        $this->selectedConversation->save();

        #refresh chatlist
        $this->emitTo('chat.chat-list', 'refresh');
        $this->emitTo('chat.unread-messages-count', 'refresh');

        #broadcast
        $this->selectedConversation->getReceiver()
            ->notify(new MessageSent(
                Auth()->User(),
                $createdMessage,
                $this->selectedConversation,
                $this->selectedConversation->getReceiver()->id
            ));
    }

    public function mount()
    {
        $this->loadMessages();
    }

    public function render()
    {
        return view('livewire.chat.chat-box');
    }
}
