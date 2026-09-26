@extends('layouts.app')

@section('title', $blog->title . ' - ' . $settings->hospital_name)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="space-y-6">
        <span class="px-2.5 py-1 bg-blue-50 border border-blue-100 text-blue-600 rounded-lg text-[10px] font-extrabold uppercase tracking-wider">
            {{ $blog->category->name ?? 'Clinical Wellness' }}
        </span>
        
        <h2 class="text-2xl md:text-4xl font-black text-slate-900 leading-tight tracking-tight">
            {{ $blog->title }}
        </h2>

        <div class="flex items-center gap-4 text-xs text-slate-500 border-b border-slate-100 pb-6">
            <p>Author: <span class="font-semibold text-slate-700">{{ $blog->author }}</span></p>
            <span>&bull;</span>
            <p>Published: <span class="font-semibold text-slate-700">{{ $blog->published_at ? $blog->published_at->format('M d, Y') : '' }}</span></p>
        </div>

        <img src="{{ $blog->featured_image }}" class="w-full h-96 object-cover rounded-[36px] shadow-sm border">

        <div class="text-xs text-slate-600 leading-relaxed space-y-4 pt-6">
            {!! $blog->content !!}
        </div>

        @if(!empty($blog->tags))
            <div class="pt-6 border-t border-slate-100 flex gap-2">
                @foreach($blog->tags as $tag)
                    <span class="px-2.5 py-1 bg-slate-100 border rounded-lg text-[10px] font-bold text-slate-500 uppercase tracking-wide">
                        #{{ $tag }}
                    </span>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Related Articles Section -->
    @if($related_blogs->isNotEmpty())
        <div class="mt-20 pt-12 border-t border-slate-200">
            <h3 class="text-lg font-extrabold text-slate-950 mb-8">Related Health Resources</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($related_blogs as $rel)
                    <div class="bg-white border rounded-3xl overflow-hidden hover:shadow-md transition">
                        <img src="{{ $rel->featured_image }}" class="w-full h-32 object-cover">
                        <div class="p-4 space-y-2">
                            <h4 class="font-bold text-xs text-slate-900 leading-snug line-clamp-2">
                                <a href="{{ route('blogs.show', $rel->slug) }}" class="hover:text-blue-600">{{ $rel->title }}</a>
                            </h4>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
