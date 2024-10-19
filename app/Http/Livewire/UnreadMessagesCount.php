<?php

namespace App\Http\Livewire;

use Livewire\Component;

class UnreadMessagesCount extends Component
{
    public $count;

    protected $listeners = [
        'messageReceived' => 'updateCount',
        'refresh' => '$refresh'
    ];

    public function mount()
    {
        $this->count = auth()->user()->unreadMessagesCount();
    }

    public function render()
    {
        return view('livewire.chat.unread-messages-count')->with(['count' => $this->count]);
    }

    public function updateCount()
    {
        $this->count = auth()->user()->unreadMessagesCount();
    }

    public function getListeners()
    {
        $auth_id = auth()->user()->id;

        return [
            'messageReceived' => 'updateCount',
            'poll' => '$refresh',
            "echo-private:users.{$auth_id},.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated" => 'broadcastedNotifications'
        ];
    }

    public function broadcastedNotifications()
    {
        $this->emit('messageReceived');
    }
}
