@props(['images'])

<x-layout>
    <x-header></x-header>

    <main class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-lg font-medium text-gray-900">Afbeeldingengalerij</h2>
            </div>

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative my-4"
                    role="alert">
                    <strong class="font-bold">Succes!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative my-4" role="alert">
                    <strong class="font-bold">Fout!</strong>
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="px-6 py-5">
                @if ($images && count($images) > 0)
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                @foreach ($images as $image)
                                                <div
                                                    class="relative group overflow-hidden rounded-lg shadow-lg border border-gray-200 transition-transform duration-300 hover:scale-102 hover:shadow-xl">
                                                    @php
                                                        $imageUrl = Storage::url($image->image_data);
                                                        $imageExists = Storage::disk('public')->exists(str_replace('/storage/', '', $image->image_data));
                                                        if (!$imageExists) {
                                                        // Generate a random seed based on image ID for consistent yet random images
                                                        $seed = $image->id * 13;
                                                        $randomNum = ($seed % 1000) + 1;
                                                        $width = 640;
                                                        $height = 480;
                                                        $imageUrl = "https://source.unsplash.com/random/{$width}x{$height}?sig={$randomNum}";
                                                        $headers = @get_headers($imageUrl);
                                                        if (!$headers || strpos($headers[0], '200') === false) {
                                                        $imageUrl = "https://picsum.photos/seed/{$randomNum}/{$width}/{$height}";}}

                                                    @endphp

                                                    <img src="{{ $imageUrl }}" alt="Afbeelding {{ $image->id }}"
                                                        class="w-full h-60 object-cover transition-transform duration-500 group-hover:scale-105"
                                                        onerror="this.onerror=null; this.src='https://source.unsplash.com/random/640x480?sig=' + Math.floor(Math.random()*1000);">

                                                    <!-- Overlay gradient -->
                                                    <div
                                                        class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent opacity-70">
                                                    </div>

                                                    <!-- Image info and buttons -->
                                                    <div
                                                        class="absolute bottom-0 left-0 right-0 p-4 z-10 transition-all duration-300 transform translate-y-0 group-hover:translate-y-0">
                                                        <div class="flex justify-between items-center mb-3">
                                                            <p class="text-white text-sm font-medium">
                                                                <span class="bg-black/40 py-1 px-2 rounded-md">
                                                                    {{ $image->created_at->format('d-m-Y H:i') }}
                                                                </span>
                                                            </p>
                                                            <span class="text-white/80 text-xs bg-blue-600/80 py-1 px-2 rounded-full">
                                                                Foto #{{ $image->id }}
                                                                @if (!$imageExists)
                                                                    <span class="ml-1 bg-yellow-500 px-1 rounded-sm text-xs">Fallback</span>
                                                                @endif
                                                            </span>
                                                        </div>

                                                        <div class="flex flex-col sm:flex-row gap-2">
                                                            <a href="{{ route('images.downloadZip', [$image->id, $image->created_at->timestamp]) }}"
                                                                class="flex-1 inline-flex items-center justify-center px-4 py-2 font-medium text-white transition-all duration-200 bg-blue-600 rounded-md shadow-md hover:bg-blue-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1">
                                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                                                    xmlns="http://www.w3.org/2000/svg">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                        d="M7 16a4 4 0 01-.88-7.93A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10">
                                                                    </path>
                                                                </svg>
                                                                <span>ZIP</span>
                                                            </a>

                                                            <a href="{{ route('images.downloadImg', [$image->id, $image->created_at->timestamp]) }}"
                                                                class="flex-1 inline-flex items-center justify-center px-4 py-2 font-medium text-white transition-all duration-200 bg-green-600 rounded-md shadow-md hover:bg-green-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-1">
                                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                                                    xmlns="http://www.w3.org/2000/svg">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                                    </path>
                                                                </svg>
                                                                <span>IMG</span>
                                                            </a>
                                                        </div>
                                                    </div>

                                                    <!-- Hover effect overlay -->
                                                    <div
                                                        class="absolute inset-0 bg-gradient-to-b from-blue-500/10 to-black/30 opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-0">
                                                    </div>
                                                </div>
                                @endforeach
                            </div>
                @else
                    <div class="text-center py-10 text-gray-500">
                        <i class="fas fa-images text-4xl mb-3"></i>
                        <p>Er zijn nog geen afbeeldingen geüpload.</p>
                        <a href="/create" class="mt-3 inline-flex items-center text-blue-600 hover:text-blue-500">
                            <span>Upload je eerste afbeelding</span>
                            <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                @endif
            </div>
        </div>

        @if ($images->hasPages())
            <div class="mt-6">
                {{ $images->links() }}
            </div>
        @endif
    </main>
</x-layout>