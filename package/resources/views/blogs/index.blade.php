@extends('layouts.app')

@section('title', 'Ocular Health Blogs & Resources - ' . $settings->hospital_name)

@section('content')
<div class="py-16 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center space-y-3 max-w-2xl mx-auto mb-16">
            <span class="px-2.5 py-1 bg-blue-50 border border-blue-100 text-blue-600 rounded-lg text-[10px] font-extrabold uppercase tracking-wider">
                Health Resources
            </span>
            <h2 class="text-3xl font-black text-slate-900 tracking-tight">Ocular Health & Wellness Articles</h2>
            <p class="text-xs text-slate-500">
                Educational medical recommendations authored by Nagpur's leading tertiary consultants.
            </p>
        </div>

        <!-- Filter bar -->
        <div class="bg-white border rounded-2xl p-4 shadow-sm mb-12">
            <form action="{{ route('blogs.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 justify-between">
                <div class="flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search articles by title or keyword..."
                           class="w-full p-2.5 border border-slate-200 rounded-xl text-xs bg-slate-50/50 outline-none focus:bg-white focus:border-blue-400 transition">
                </div>

                <div class="flex gap-4">
                    <select name="category" class="p-2.5 border border-slate-200 rounded-xl text-xs bg-slate-50/50 outline-none focus:bg-white transition font-semibold text-slate-600">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') === $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>

                    <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl transition cursor-pointer">
                        Filter Blogs
                    </button>
                </div>
            </form>
        </div>

        <!-- Blogs grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @forelse($blogs as $blog)
                <div class="bg-white border rounded-[32px] overflow-hidden hover:shadow-xl transition duration-300 flex flex-col justify-between">
                    <div>
                        <img src="{{ $blog->featured_image }}" class="w-full h-48 object-cover">
                        <div class="p-6 space-y-3">
                            <span class="px-2 py-0.5 bg-blue-50 text-blue-600 text-[10px] font-extrabold uppercase tracking-wider rounded">
                                {{ $blog->category->name ?? 'Specialty' }}
                            </span>
                            <h3 class="font-extrabold text-slate-950 text-md leading-snug line-clamp-2">
                                <a href="{{ route('blogs.show', $blog->slug) }}" class="hover:text-blue-600">{{ $blog->title }}</a>
                            </h3>
                            <p class="text-[10px] text-slate-400 font-bold">By {{ $blog->author }} | {{ $blog->published_at ? $blog->published_at->format('M d, Y') : '' }}</p>
                            <p class="text-xs text-slate-500 leading-relaxed line-clamp-3">
                                {!! strip_tags($blog->content) !!}
                            </p>
                        </div>
                    </div>

                    <div class="p-6 border-t bg-slate-50/20">
                        <a href="{{ route('blogs.show', $blog->slug) }}" class="text-xs text-blue-600 font-bold hover:underline">
                            Read Full Article &rarr;
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full p-12 text-center text-slate-400 text-xs font-semibold">No health articles published matching your filters.</div>
            @endforelse
        </div>

        <!-- Pagination links -->
        <div class="mt-12">
            {{ $blogs->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
