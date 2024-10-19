<div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex" wire:poll.5s
x-data="{ type: 'all' }"
x-init="
Echo.private('users.{{ auth()->user()->id  }}').notification((notification) => {
    if (notification['type'] == 'App\\Notifications\\MessageRead' || notification['type'] == 'App\\Notifications\\MessageSent') {
        window.Livewire.emit('refresh');
    }
});
">
    <x-nav-link :href="route('messenger')" :active="request()->routeIs('messenger') || request()->routeIs('chat')">
        {{ __('Messenger') }}
        <span id="unread-messages-count" class="ml-2">
            @if ($count > 0)
            <div class="pulsating-dot negative"></div>
            @endif
        </span>
    </x-nav-link>
</div>