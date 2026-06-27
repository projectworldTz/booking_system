{{-- AI Concierge Chat Widget --}}
{{-- $hotel (optional): current Hotel model when on a hotel page --}}
@php
    use App\Enums\Feature;

    // When on a hotel page, only show the widget if the hotel has AI_CONCIERGE granted
    if (isset($hotel) && !$hotel->hasFeature(Feature::AI_CONCIERGE)) {
        return; // silently skip — no widget for hotels without the feature
    }

    $hotelId   = isset($hotel) ? $hotel->id   : null;
    $hotelName = isset($hotel) ? $hotel->name : config('app.name') . ' Concierge';
    $welcomeMsg = isset($hotel)
        ? "Hello! I'm your AI concierge for {$hotel->name}. How can I help you today? I can answer questions about our rooms, facilities, policies, and local attractions."
        : "Hello! I'm your travel assistant for " . config('app.name') . ". Ask me anything about our hotels or how to make a booking!";
@endphp

<div
    x-data="chatWidget({
        hotelId: {{ $hotelId ?? 'null' }},
        welcomeMsg: {{ json_encode($welcomeMsg) }},
        csrfToken: {{ json_encode(csrf_token()) }}
    })"
    class="fixed bottom-6 right-6 z-50 flex flex-col items-end gap-3"
    style="z-index: 9999;"
>
    {{-- ── Chat Panel ── --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 scale-95"
        x-cloak
        class="w-80 sm:w-96 rounded-2xl shadow-2xl overflow-hidden flex flex-col bg-white dark:bg-slate-800"
        style="height: 520px; max-height: calc(100vh - 100px);"
    >
        {{-- Header --}}
        <div class="flex items-center gap-3 px-4 py-3 bg-[#0F2147] text-white flex-shrink-0">
            {{-- AI avatar --}}
            <div class="relative flex-shrink-0">
                <div class="h-9 w-9 rounded-full bg-[#C9A227] flex items-center justify-center text-[#0F2147] font-bold text-sm">
                    AI
                </div>
                <span class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full bg-emerald-400 border-2 border-[#0F2147]"></span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-sm leading-tight truncate">{{ $hotelName }}</p>
                <p class="text-xs text-white/60">AI Concierge · Typically replies instantly</p>
            </div>
            <div class="flex items-center gap-1 flex-shrink-0">
                {{-- Clear chat --}}
                <button
                    @click="clearChat()"
                    title="Clear conversation"
                    class="p-1.5 rounded-lg hover:bg-white/10 transition text-white/60 hover:text-white"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
                {{-- Close --}}
                <button
                    @click="open = false"
                    title="Close"
                    class="p-1.5 rounded-lg hover:bg-white/10 transition text-white/60 hover:text-white"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Gold accent bar --}}
        <div class="h-0.5 bg-[#C9A227] flex-shrink-0"></div>

        {{-- Messages --}}
        <div
            class="flex-1 overflow-y-auto px-4 py-3 space-y-3 bg-slate-50 dark:bg-slate-900/50"
            x-ref="messageContainer"
        >
            <template x-for="(msg, index) in messages" :key="index">
                <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    {{-- AI avatar on left messages --}}
                    <div x-show="msg.role === 'assistant'" class="flex-shrink-0 mr-2 mt-1">
                        <div class="h-6 w-6 rounded-full bg-[#0F2147] flex items-center justify-center text-[#C9A227] text-xs font-bold">AI</div>
                    </div>
                    <div
                        :class="msg.role === 'user'
                            ? 'bg-[#0F2147] text-white rounded-2xl rounded-tr-sm'
                            : 'bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 rounded-2xl rounded-tl-sm shadow-sm border border-slate-100 dark:border-slate-700'"
                        class="max-w-[78%] px-3.5 py-2.5 text-sm leading-relaxed"
                        x-text="msg.content"
                    ></div>
                </div>
            </template>

            {{-- Typing indicator --}}
            <div x-show="loading" class="flex justify-start">
                <div class="flex-shrink-0 mr-2 mt-1">
                    <div class="h-6 w-6 rounded-full bg-[#0F2147] flex items-center justify-center text-[#C9A227] text-xs font-bold">AI</div>
                </div>
                <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-2xl rounded-tl-sm px-4 py-3 shadow-sm">
                    <div class="flex gap-1 items-center h-4">
                        <span class="h-2 w-2 rounded-full bg-slate-400 animate-bounce" style="animation-delay: 0ms"></span>
                        <span class="h-2 w-2 rounded-full bg-slate-400 animate-bounce" style="animation-delay: 150ms"></span>
                        <span class="h-2 w-2 rounded-full bg-slate-400 animate-bounce" style="animation-delay: 300ms"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick suggestions (shown only when 1 message in history) --}}
        <div
            x-show="messages.length === 1 && !loading"
            class="px-3 pb-2 flex flex-wrap gap-1.5 flex-shrink-0 bg-slate-50 dark:bg-slate-900/50"
        >
            <template x-for="suggestion in suggestions" :key="suggestion">
                <button
                    @click="sendSuggestion(suggestion)"
                    class="text-xs px-3 py-1.5 rounded-full border border-[#0F2147]/20 dark:border-slate-600 text-[#0F2147] dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-[#0F2147] hover:text-white dark:hover:bg-[#0F2147] transition"
                    x-text="suggestion"
                ></button>
            </template>
        </div>

        {{-- Input --}}
        <div class="flex-shrink-0 border-t border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-3">
            <form @submit.prevent="send()" class="flex items-end gap-2">
                <textarea
                    x-model="input"
                    x-ref="inputBox"
                    @keydown.enter.exact.prevent="send()"
                    @keydown.shift.enter="/* allow newline */"
                    @input="autoResize($event.target)"
                    :disabled="loading"
                    rows="1"
                    placeholder="Type your message…"
                    class="flex-1 resize-none rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 px-3 py-2 text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#0F2147] dark:focus:ring-[#C9A227] disabled:opacity-50 leading-relaxed"
                    style="max-height: 100px; min-height: 38px;"
                ></textarea>
                <button
                    type="submit"
                    :disabled="loading || !input.trim()"
                    class="flex-shrink-0 h-9 w-9 rounded-xl bg-[#0F2147] hover:bg-[#1a3a6b] disabled:opacity-40 disabled:cursor-not-allowed transition flex items-center justify-center text-white"
                >
                    <svg x-show="!loading" class="h-4 w-4 translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
                    </svg>
                    <svg x-show="loading" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                </button>
            </form>
            <p class="text-center text-[10px] text-slate-400 mt-1.5">Powered by AI · Responses may not be 100% accurate</p>
        </div>
    </div>

    {{-- ── FAB Button ── --}}
    <button
        @click="toggleChat()"
        class="relative h-14 w-14 rounded-full shadow-xl flex items-center justify-center transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-[#C9A227]/40"
        :class="open ? 'bg-slate-600' : 'bg-[#0F2147] hover:bg-[#1a3a6b]'"
        aria-label="Open AI concierge chat"
    >
        {{-- Unread badge (shown when chat has been closed with messages) --}}
        <span
            x-show="!open && unread > 0"
            class="absolute -top-1 -right-1 h-5 w-5 rounded-full bg-red-500 text-white text-xs font-bold flex items-center justify-center"
            x-text="unread"
        ></span>

        {{-- Chat icon --}}
        <svg x-show="!open" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>

        {{-- Close icon --}}
        <svg x-show="open" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>

        {{-- Pulse ring when closed --}}
        <span x-show="!open" class="absolute inset-0 rounded-full bg-[#C9A227]/30 animate-ping"></span>
    </button>
</div>

@push('scripts')
<script>
function chatWidget({ hotelId, welcomeMsg, csrfToken }) {
    return {
        open:        false,
        loading:     false,
        input:       '',
        unread:      0,
        hotelId:     hotelId,
        messages:    [{ role: 'assistant', content: welcomeMsg }],
        suggestions: hotelId
            ? ['What rooms are available?', 'What are your check-in times?', 'Tell me about amenities', 'What\'s your cancellation policy?']
            : ['Find me a hotel in Dar es Salaam', 'How do I make a booking?', 'What types of hotels are listed?'],

        toggleChat() {
            this.open = !this.open;
            if (this.open) {
                this.unread = 0;
                this.$nextTick(() => {
                    this.scrollToBottom();
                    this.$refs.inputBox?.focus();
                });
            }
        },

        async send() {
            const text = this.input.trim();
            if (!text || this.loading) return;

            this.messages.push({ role: 'user', content: text });
            this.input   = '';
            this.loading = true;

            this.$nextTick(() => {
                this.scrollToBottom();
                this.autoResize(this.$refs.inputBox);
            });

            try {
                const res = await fetch('/chat/message', {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept':       'application/json',
                    },
                    body: JSON.stringify({
                        message:  text,
                        hotel_id: this.hotelId,
                    }),
                });

                const data = await res.json();
                const reply = data.reply || 'Sorry, something went wrong. Please try again.';

                this.messages.push({ role: 'assistant', content: reply });

                if (!this.open) this.unread++;
            } catch (e) {
                this.messages.push({
                    role:    'assistant',
                    content: 'I\'m having trouble connecting. Please check your internet and try again.',
                });
            } finally {
                this.loading = false;
                this.$nextTick(() => this.scrollToBottom());
            }
        },

        sendSuggestion(text) {
            this.input = text;
            this.send();
        },

        async clearChat() {
            await fetch('/chat/clear', {
                method:  'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ hotel_id: this.hotelId }),
            });
            this.messages = [{ role: 'assistant', content: welcomeMsg }];
            this.input    = '';
        },

        scrollToBottom() {
            const el = this.$refs.messageContainer;
            if (el) el.scrollTop = el.scrollHeight;
        },

        autoResize(el) {
            if (!el) return;
            el.style.height = 'auto';
            el.style.height = Math.min(el.scrollHeight, 100) + 'px';
        },
    };
}
</script>
@endpush
