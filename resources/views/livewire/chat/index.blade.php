<div class="chat-container">
    <div class="flex flex-row justify-center">
        <div class="basis-1/4">
            <div class="p-2">
                <form wire:submit.prevent="search">
                    <div class="relative w-full">
                        <input wire:model.debounce.300ms="searchQuery" type="search" id="search-dropdown" class="block p-2.5 w-full z-20 text-sm text-gray-900 bg-gray-50 rounded-lg border-s-2 border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-s-gray-700  dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:border-blue-500" placeholder="Tên tài khoản" required="">
                        <button type="submit" class="absolute top-0 end-0 p-2.5 text-sm font-medium h-full text-white bg-blue-700 rounded-e-lg border border-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                            <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"></path>
                            </svg>
                            <span class="sr-only">Search</span>
                        </button>
                    </div>
                </form>
                @error('searchQuery') 
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
                <div class="mt-3 p-3 flex justify-between items-center bg-zinc-100 gap-5 rounded-md">
                    <div class="flex justify-center items-center w-full rounded-md {{ $activeTab === 'all' ? 'bg-white' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-fill" viewBox="0 0 16 16">
                            <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"></path>
                        </svg>
                        <button wire:click="setActiveTab('all')" class="py-2 px-2 text-black">Tất cả ({{$this->countConversation}})</button>
                    </div>
                    <div class="flex justify-center items-center w-full rounded-md {{ $activeTab === 'support' ? 'bg-white' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-shield-shaded" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M8 14.933a1 1 0 0 0 .1-.025q.114-.034.294-.118c.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56"></path>
                        </svg>
                        <button wire:click="setActiveTab('support')" class="py-2 px-2 text-blue-500">Hỗ trợ</button>
                    </div>
                </div>
            </div>
            <div>
                @if ($activeTab === 'all')
                    <livewire:chat.chat-list :selectedConversation="$selectedConversation" :query="$query" />
                @elseif ($activeTab === 'support')
                    <livewire:chat.chat-support />
                @endif
            </div>
        </div>
        <div class="basis-1/2 border-l border-l-neutral-300">
            <div class="p-2">
                <livewire:chat.chat-box :selectedConversation="$selectedConversation" :query="$query">
            </div>
        </div>
    </div>
    <script>
        window.addEventListener('show-alert', event => {
            Swal.fire({
                title: event.detail.title,
                text: event.detail.message,
                icon: event.detail.type,
                timer: 5000,
                timerProgressBar: true,
                showConfirmButton: false
            });
        });
    </script>
</div>