<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zenn News</title>
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    @vite(['resources/css/app.css'])
</head>

<body class="text-gray-900">

    <nav class="sticky top-0 z-40 bg-white px-2 py-3">
        <div class="grid grid-cols-5 gap-2">
            @foreach ($topics as $slug => $meta)
                @php $isActive = $slug === $topic; @endphp
                <a href="?topic={{ $slug }}"
                    class="px-3 py-1 rounded-full text-sm font-bold text-center truncate {{ $isActive ? 'text-white' : 'text-gray-600 bg-gray-100' }}"
                    @if ($isActive) style="background-color: {{ $meta['color'] }}" @endif>
                    {{ $meta['label'] }}
                </a>
            @endforeach
        </div>
    </nav>

    <main class="max-w-2xl mx-auto px-4 pb-4">
        @forelse ($articles as $article)
            <a href="{{ $article['url'] }}" target="_blank" rel="noopener"
                class="block bg-white p-4 mb-3 shadow-sm border-l-4 active:bg-gray-50"
                style="border-color: {{ $topics[$topic]['color'] }}">
                <p class="font-bold leading-snug">{{ $article['title'] }}</p>
                <div class="mt-2 text-sm text-gray-400 flex gap-3">
                    <span>{{ $article['author_name'] }}</span>
                    <span>{{ $article['published_at'] }}</span>
                </div>
            </a>
        @empty
            <p class="text-gray-500 text-center py-10">記事がありません。</p>
        @endforelse
    </main>

</body>

</html>
