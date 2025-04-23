@props(['label', 'name'])

<div class="mb-4">
    @if ($label)
        <x-label :$name :$label />
    @endif

    <div class="mt-1">
        {{ $slot }}

        <x-error :error="$errors->first($name)" />
    </div>
</div>