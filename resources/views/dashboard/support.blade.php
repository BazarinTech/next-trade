@extends('layouts.trading')
@section('title', 'Support — Next Trade')
@section('page-title', 'Support')
@section('page-subtitle', 'Get help when you need it')

@section('content')
<div class="max-w-2xl">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        @foreach([['Live Chat','Chat with our support team in real-time.','message-circle','cyan'],['Email Support','Send us a detailed message.','mail','purple'],['FAQ','Find answers to common questions.','book-open','amber'],['Video Guides','Step-by-step video tutorials.','play-circle','emerald']] as [$title,$desc,$icon,$color])
        <div class="rounded-2xl border p-5 cursor-pointer transition-all duration-200 hover:border-{{ $color }}-500/40 group"
             :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
            <div class="w-10 h-10 rounded-xl bg-{{ $color }}-500/10 border border-{{ $color }}-500/20 flex items-center justify-center mb-3">
                <svg class="w-5 h-5 text-{{ $color }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    @switch($icon)
                        @case('message-circle')<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>@break
                        @case('mail')<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>@break
                        @case('book-open')<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>@break
                        @case('play-circle')<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>@break
                    @endswitch
                </svg>
            </div>
            <p class="text-sm font-semibold mb-1" :class="isDark ? 'text-white' : 'text-gray-900'">{{ $title }}</p>
            <p class="text-xs text-gray-500">{{ $desc }}</p>
        </div>
        @endforeach
    </div>
</div>
@endsection
