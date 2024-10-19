<div
    x-data="{type:'all',query:@entangle('query')}"
    x-init="
        setTimeout(()=>{
            conversationElement = document.getElementById('conversation-'+query);
            // scroll to the element
            if(conversationElement) {
                conversationElement.scrollIntoView({'behavior':'smooth'});
            }
        }, 200);

        Echo.private('users.{{Auth()->User()->id}}').notification((notification) => {
            if(notification['type'] == 'App\\Notifications\\MessageRead' || notification['type'] == 'App\\Notifications\\MessageSent') {
                window.Livewire.emit('refresh');
            }
        });
   " class="flex flex-col transition-all h-full overflow-hidden">
    <main class=" overflow-y-scroll overflow-hidden grow h-full relative scrollbar-hide" style="contain: content;height: 60vh">
        {{-- chatlist  --}}
        <ul class="p-2 grid w-full spacey-y-2">
            @if ($conversations)
                @foreach ($conversations as $key => $conversation)
                    @if ($conversation->getReceiver()->id != auth()->id())
                        <li id="conversation-{{ $conversation->id }}" wire:key="{{ $conversation->id }}" class="py-3 hover:bg-gray-50 rounded-2xl dark:hover:bg-gray-700/70 transition-colors duration-150 flex gap-4 relative w-full cursor-pointer px-2 {{$conversation->id == $selectedConversation?->id ? 'bg-gray-100/70':''}}">
                            <a href="#" class="shrink-0">
                                <x-avatar src="/{{ $conversation->getReceiver()->avatar }}"  />
                            </a>

                            <aside class="grid grid-cols-12 w-full">
                                <a href="{{ route('chat', $conversation->getReceiver()->username) }}" class="col-span-11 pb-2 relative overflow-hidden truncate leading-5 w-full flex-nowrap p-1">
                                    {{-- name and date  --}}
                                    <div class="flex justify-between w-full items-center">
                                        <h6 class="truncate font-medium tracking-wider text-gray-900">
                                            {{$conversation->getReceiver()->name}}
                                        </h6>

                                        <small class="text-gray-700">{{$conversation->messages?->last()?->created_at?->shortAbsoluteDiffForHumans()}} </small>
                                    </div>
                                    {{-- Message body --}}

                                    <div class="flex gap-x-2 items-center">
                                        @if ($conversation->messages?->last()?->sender_id==auth()->id())
                                            @if ($conversation->isLastMessageReadByUser())
                                                {{-- double tick  --}}
                                                <span>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2-all" viewBox="0 0 16 16">
                                                        <path d="M12.354 4.354a.5.5 0 0 0-.708-.708L5 10.293 1.854 7.146a.5.5 0 1 0-.708.708l3.5 3.5a.5.5 0 0 0 .708 0l7-7zm-4.208 7-.896-.897.707-.707.543.543 6.646-6.647a.5.5 0 0 1 .708.708l-7 7a.5.5 0 0 1-.708 0z"/>
                                                        <path d="m5.354 7.146.896.897-.707.707-.897-.896a.5.5 0 1 1 .708-.708z"/>
                                                    </svg>
                                                </span>
                                            @else
                                                {{-- single tick  --}}
                                                <span>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2" viewBox="0 0 16 16">
                                                        <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/>
                                                    </svg>
                                                </span>
                                                
                                            @endif
                                        @endif
                                        <p class="grow truncate text-sm font-[100]">
                                            {{$conversation->messages?->last()?->body??' '}}
                                        </p>
                                        {{-- unread count --}}
                                        @if ($conversation->unreadMessagesCount()>0)
                                            <span class="font-bold p-px px-2 text-xs shrink-0 rounded-full bg-blue-500 text-white">
                                                {{$conversation->unreadMessagesCount()}}
                                            </span>
                                        @endif
                                    </div>
                                </a>
                            </aside>
                        </li>
                    @endif
                @endforeach
            @else   
            @endif
        </ul>
    </main>
</div>