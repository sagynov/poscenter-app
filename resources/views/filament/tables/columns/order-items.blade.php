<div class="flex flex-col gap-1 text-sm">
    @foreach ($getState() ?? [] as $item)
        <div class="flex justify-between gap-4">
            <span>{{ $item['name'] }} × {{ $item['quantity'] }}</span>
            <span class="text-gray-500"> = {{ number_format($item['subtotal'], 0, '.', ' ') }} ₸</span>
        </div>
    @endforeach
</div>