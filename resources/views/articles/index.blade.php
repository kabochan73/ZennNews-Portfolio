<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zenn News</title>
    @vite(['resources/css/app.css'])
</head>

<body class="bg-gray-100 text-gray-800">

    <header class="{{ $topic === 'laravel' ? 'bg-orange-500' : ($topic === 'nextjs' ? 'bg-gray-900' : 'bg-sky-400') }} px-10 py-5 sticky top-0 z-50 flex items-center justify-between">
        <h1 class="text-white text-xl font-bold">Zenn News</h1>
        <div class="flex gap-3">
            <a href="?topic=laravel"
                class="px-5 py-2 rounded-full font-bold text-sm {{ $topic === 'laravel' ? 'bg-white text-orange-500' : 'bg-white/20 text-white hover:bg-white/30' }}">
                Laravel
            </a>
            <a href="?topic=nextjs"
                class="px-5 py-2 rounded-full font-bold text-sm {{ $topic === 'nextjs' ? 'bg-white text-gray-900' : 'bg-white/20 text-white hover:bg-white/30' }}">
                Next.js
            </a>
            <a href="?topic=aws"
                class="px-5 py-2 rounded-full font-bold text-sm {{ $topic === 'aws' ? 'bg-white text-sky-400' : 'bg-white/20 text-white hover:bg-white/30' }}">
                AWS
            </a>
        </div>
    </header>

    <div class="max-w-6xl mx-auto my-8 px-4">
        <div class="flex flex-col md:flex-row gap-6 items-start">

            <main class="flex-1 min-w-0">
                @forelse ($articles as $article)
                    <div class="bg-white rounded-lg p-5 mb-4 shadow-sm">
                        <a href="{{ $article->url }}" target="_blank" rel="noopener"
                            class="font-bold text-gray-900 hover:text-[#3ea8ff]">
                            {{ $article->title }}
                        </a>
                        <div class="mt-2 text-sm text-gray-400 flex justify-between">
                            <div class="flex gap-4">
                                <span>{{ $article->author_name }}</span>
                                <span>{{ $article->published_at->format('Y/m/d') }}</span>
                            </div>
                            <span class="text-red-400">♥ {{ $article->liked_count }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500">記事がありません。</p>
                @endforelse
            </main>

            <aside class="w-full md:w-72 md:shrink-0 md:sticky md:top-25">
                <div class="bg-white rounded-lg p-5 shadow-sm">
                    <h2 class="text-sm font-bold mb-4 pb-2 border-b-2 {{ $topic === 'laravel' ? 'border-orange-500' : ($topic === 'nextjs' ? 'border-gray-900' : 'border-sky-400') }}">
                        人気記事ランキング
                    </h2>
                    @foreach ($popular as $i => $article)
                        <div class="flex gap-3 items-start mb-3 last:mb-0">
                            <span class="text-lg font-bold {{ $topic === 'laravel' ? 'text-orange-500' : ($topic === 'nextjs' ? 'text-gray-900' : 'text-sky-400') }} w-5 shrink-0">
                                {{ $i + 1 }}
                            </span>
                            <div>
                                <a href="{{ $article->url }}" target="_blank" rel="noopener"
                                    class="text-sm text-gray-900 {{ $topic === 'laravel' ? 'hover:text-orange-500' : ($topic === 'nextjs' ? 'hover:text-gray-600' : 'hover:text-sky-400') }} leading-snug block">
                                    {{ $article->title }}
                                </a>
                                <div class="mt-1 text-sm text-red-400 flex justify-end">♥ {{ $article->liked_count }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </aside>

        </div>
    </div>

</body>

</html>
