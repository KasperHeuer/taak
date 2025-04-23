<form {{ $attributes(['method' => 'GET', 'class' => 'space-y-6']) }}>
    @if ($attributes->get('method', 'GET') !== 'GET')
        @csrf
        @method($attributes->get('method'))
    @endif

    {{ $slot }}
</form>