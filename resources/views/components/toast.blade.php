{{--
    Toast notification system.
    Usage: <x-toast /> in layout | reads from session flash.
    Alpine.js powered, auto-dismiss after 4s.
--}}
<div
    x-data="{
        toasts: [],
        add(type, message) {
            const id = Date.now();
            this.toasts.push({ id, type, message, visible: true });
            setTimeout(() => this.remove(id), 5000);
        },
        remove(id) {
            const t = this.toasts.find(t => t.id === id);
            if (t) t.visible = false;
            setTimeout(() => this.toasts = this.toasts.filter(t => t.id !== id), 300);
        }
    }"
    x-init="
        @if(session('success')) add('success', @js(session('success'))); @endif
        @if(session('error'))   add('error',   @js(session('error'))); @endif
        @if(session('warning')) add('warning', @js(session('warning'))); @endif
        @if(session('info'))    add('info',    @js(session('info'))); @endif
    "
    @toast.window="add($event.detail.type, $event.detail.message)"
    class="fixed top-5 right-5 z-[100] flex flex-col gap-2 w-80 pointer-events-none"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-show="toast.visible"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-8"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0 translate-x-8"
            class="pointer-events-auto flex items-start gap-3 px-4 py-3 rounded-xl border shadow-lg backdrop-blur-sm"
            :class="{
                'bg-emerald-900/90 border-emerald-500/30 text-emerald-300': toast.type === 'success',
                'bg-red-900/90 border-red-500/30 text-red-300': toast.type === 'error',
                'bg-amber-900/90 border-amber-500/30 text-amber-300': toast.type === 'warning',
                'bg-cyan-900/90 border-cyan-500/30 text-cyan-300': toast.type === 'info',
            }"
        >
            {{-- Icon --}}
            <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path x-show="toast.type === 'success'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                <path x-show="toast.type === 'error'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                <path x-show="toast.type === 'warning'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                <path x-show="toast.type === 'info'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-xs font-medium flex-1" x-text="toast.message"></p>
            <button @click="remove(toast.id)" class="flex-shrink-0 opacity-60 hover:opacity-100 transition-opacity">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </template>
</div>
