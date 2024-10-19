<div 
    x-data="{
        height: 0,
        conversationElement: document.getElementById('conversation'),
        markAsRead: null
    }"
    x-init="
        height = conversationElement.scrollHeight;
        $nextTick(() => conversationElement.scrollTop = height);
        Echo.private('users.{{ auth()->user()->id }}')
        .notification((notification) => {
            if(notification['type'] == 'App\\Notifications\\MessageRead' && notification['conversation_id'] == {{ $this->selectedConversation ? $this->selectedConversation->id : null }}) {
                markAsRead=true;
            }
        });
    "
    @scroll-bottom.window="$nextTick(() => conversationElement.scrollTop = conversationElement.scrollHeight);"
    class="w-full overflow-hidden" style="height: 70vh">
    <div class="flex flex-col overflow-y-scroll grow h-full scrollbar-hide">
        @if ($selectedConversation)
        {{-- header --}}
        <header class="w-full sticky inset-x-0 flex pb-[5px] pt-[5px] top-0 z-10 border-b " >
            <div class="flex w-full items-center px-2 lg:px-4 gap-2 md:gap-5">
                <a class="shrink-0 lg:hidden" href="#">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15m0 0l6.75 6.75M4.5 12l6.75-6.75" />
                    </svg>
                </a>
                {{-- avatar --}}
                <div class="shrink-0">
                    <x-avatar class="h-9 w-9 lg:w-11 lg:h-11" />
                </div>
                <h6 class="font-bold truncate"> {{ $selectedConversation ? $selectedConversation->getReceiver()->username : '' }} </h6>
            </div>
        </header>
        {{-- body --}}
        <main @scroll="
            scropTop = $el.scrollTop;
            if(scropTop <= 0) {
                window.livewire.emit('loadMore');
            }
        "
        @update-chat-height.window="
            newHeight = $el.scrollHeight;
            oldHeight = height;
            $el.scrollTop = newHeight - oldHeight;
            height = newHeight;
        "
        id="conversation" class="flex flex-col gap-3 p-2.5 overflow-y-auto flex-grow overscroll-contain overflow-x-hidden w-full my-auto">
        @if ($loadedMessages)
            @php
                $previousMessage= null;
            @endphp
            @foreach ($loadedMessages as $key => $message)
                {{-- keep track of the previous message --}}
                @if ($key > 0)
                    @php
                        $previousMessage = $loadedMessages->get($key - 1)
                    @endphp
                @endif
                <div wire:key="{{ time().$key }}"
                    @class([
                        'max-w-[85%] md:max-w-[78%] flex w-auto gap-2 relative mt-2',
                        'ml-auto' => $message->sender_id === auth()->id(),
                    ])>
                    {{-- avatar --}}

                    <div @class([
                        'shrink-0',
                        'invisible' => $previousMessage?->sender_id == $message->sender_id,
                        'hidden' => $message->sender_id === auth()->id()
                    ])>
                        <x-avatar />
                    </div>
                    {{-- messsage body --}}
                    <div @class(['flex flex-wrap text-[15px]  rounded-xl p-2.5 flex flex-col text-black bg-[#f6f6f8fb]',
                         'rounded-bl-none border  border-gray-200/40 ' => !($message->sender_id === auth()->id()),
                         'rounded-br-none bg-blue-500/80 text-white' => $message->sender_id === auth()->id()
                    ])>
                    <p class="whitespace-normal truncate text-sm md:text-base tracking-wide lg:tracking-normal">
                        {{$message->body}}
                    </p>
                    <div class="ml-auto flex gap-2">
                        <p @class([
                            'text-xs ',
                            'text-gray-500' => !($message->sender_id === auth()->id()),
                            'text-white' => $message->sender_id === auth()->id(),
                        ]) >
                            {{$message->created_at->format('g:i a')}}
                        </p>
                        {{-- message status , only show if message belongs auth --}}
                        @if ($message->sender_id === auth()->id())
                            <div x-data="{markAsRead:@json($message->isRead())}">
                                {{-- double ticks --}}
                                <span x-cloak x-show="markAsRead" @class('text-gray-200')>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2-all" viewBox="0 0 16 16">
                                        <path d="M12.354 4.354a.5.5 0 0 0-.708-.708L5 10.293 1.854 7.146a.5.5 0 1 0-.708.708l3.5 3.5a.5.5 0 0 0 .708 0l7-7zm-4.208 7-.896-.897.707-.707.543.543 6.646-6.647a.5.5 0 0 1 .708.708l-7 7a.5.5 0 0 1-.708 0z"/>
                                        <path d="m5.354 7.146.896.897-.707.707-.897-.896a.5.5 0 1 1 .708-.708z"/>
                                    </svg>
                                </span>

                                {{-- single ticks --}}
                                <span x-show="!markAsRead" @class('text-gray-200')>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2" viewBox="0 0 16 16">
                                        <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/>
                                    </svg>
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @if (!empty($message->image))
                <div wire:key="{{time().$key}}"
                    @class([
                        'max-w-[85%] md:max-w-[78%] flex w-auto gap-2 relative mt-2',
                        'ml-auto' => $message->sender_id === auth()->id(),
                ])>
                    <div @class(['flex flex-wrap text-[15px]  rounded-xl p-2.5 flex flex-col text-black bg-[#f6f6f8fb]',
                        'rounded-bl-none border  border-gray-200/40 ' => !($message->sender_id === auth()->id()),
                        'rounded-br-none bg-blue-500/80 text-white' => $message->sender_id === auth()->id()
                    ])>
                        <p class="whitespace-normal truncate text-sm md:text-base tracking-wide lg:tracking-normal">
                            <img src="{{ asset('storage/' . $message->image) }}" alt="">
                        </p>
                    </div>
                </div>
            @endif
        @endforeach
        @endif
        </main>

        {{-- send message  --}}
        <footer class="shrink-0 z-10 inset-x-0"
            x-data="{
                body: @entangle('body').defer,
                picker: null,
                initEmojiPicker() {
                    this.picker = new EmojiButton();
                    this.picker.on('emoji', emoji => {
                        if (this.body == null || this.body == '') {
                            this.body = emoji;
                            return;
                        } else {
                            this.body += emoji;
                        }
                    });
                },
                openEmojiPicker() {
                    this.picker.togglePicker(this.$refs.emojiButton);
                }
            }"
            x-init="initEmojiPicker"
        >
            <!-- Image Preview -->
            @if ($uploadedImageUrl)
                <div class="mt-2 relative">
                    <img src="{{ $uploadedImageUrl }}" alt="Uploaded Image" class="max-w-xs max-h-64 rounded-lg">
                </div>
            @endif
            <div class="p-2">
                <form class="flex flex-row" @submit.prevent="$wire.sendMessage" method="POST" enctype="multipart/form-data" autocapitalize="off">
                    @csrf
                    <div class="flex flex-row justify-around items-center" style="width: 10%">
                        <div class="form-group">
                            <label for="image-upload" class="cursor-pointer">
                                <div class="p-2 bg-blue-500 rounded-md" aria-label="Gửi ảnh" data-bs-original-title="Gửi ảnh">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#fff" class="bi bi-card-image" viewBox="0 0 16 16">
                                        <path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0"/>
                                        <path d="M1.5 2A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2zm13 1a.5.5 0 0 1 .5.5v6l-3.775-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12v.54L1 12.5v-9a.5.5 0 0 1 .5-.5z"/>
                                    </svg>
                                </div>
                            </label>
                            <input id="image-upload" type="file" accept="image/*" class="hidden" wire:model="image">
                        </div>
                        <div class="form-group">
                            <div @click="openEmojiPicker" x-ref="emojiButton" class="p-2 bg-violet-500 rounded-md" aria-label="Gửi Emoji" data-bs-original-title="Gửi Emoji">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#fff" class="bi bi-emoji-smile" viewBox="0 0 16 16">
                                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                                    <path d="M4.285 9.567a.5.5 0 0 1 .683.183A3.5 3.5 0 0 0 8 11.5a3.5 3.5 0 0 0 3.032-1.75.5.5 0 1 1 .866.5A4.5 4.5 0 0 1 8 12.5a4.5 4.5 0 0 1-3.898-2.25.5.5 0 0 1 .183-.683M7 6.5C7 7.328 6.552 8 6 8s-1-.672-1-1.5S5.448 5 6 5s1 .672 1 1.5m4 0c0 .828-.448 1.5-1 1.5s-1-.672-1-1.5S9.448 5 10 5s1 .672 1 1.5"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="form-group" style="width: 90%">
                        <input type="hidden" autocomplete="false" style="display:none">
                        <div class="grid grid-cols-12 gap-2">
                            <input 
                                x-model="body"
                                type="text"
                                autocomplete="off"
                                autofocus
                                placeholder="Tin nhắn..."
                                maxlength="1700"
                                class="col-span-11 bg-gray-100 border-0 outline-0 focus:border-0 focus:ring-0 hover:ring-0 rounded-lg  focus:outline-none"
                            >
                            <button x-bind:disabled="!body" class="flex items-center justify-center col-span-1 bg-slate-800 rounded-lg" type='submit'>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#fff" class="bi bi-send-fill" viewBox="0 0 16 16">
                                    <path d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855H.766l-.452.18a.5.5 0 0 0-.082.887l.41.26.001.002 4.995 3.178 3.178 4.995.002.002.26.41a.5.5 0 0 0 .886-.083zm-1.833 1.89L6.637 10.07l-.215-.338a.5.5 0 0 0-.154-.154l-.338-.215 7.494-7.494 1.178-.471z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </form>
                @error('body')
                    <p> {{$message}} </p>
                @enderror
            </div>
        </footer>
        @endif
    </div>
</div>
