{{-- <div>
    <h2>Live Crypto Prices</h2>
    <div>
        <strong>Current Time:</strong> <span wire:poll.1s="updateTime">{{ $currentTime }}</span>
    </div>

    <table>
        <tr>
            <th>Pair</th>
            <th>Avg. Price</th>
            <th>Change</th>
            <th>Exchanges</th>
            <th>Last Updated</th>
        </tr>
        @foreach($prices as $price)
            <tr>
                <td>{{ $price['pair'] }}</td>
                <td>${{ number_format($price['average_price'], 2) }}</td>
                <td>
                    <span style="color: {{ $price['change_percentage'] >= 0 ? 'green' : 'red' }};">
                        {{ $price['change_percentage'] }}%
                        {!! $price['change_percentage'] >= 0 ? '🔼' : '🔽' !!}
                    </span>
                </td>
                <td>{{ $price['exchanges'] }}</td>
                <td>{{ $price['last_updated'] }}</td>
            </tr>
        @endforeach
    </table>
</div>

<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        Pusher.logToConsole = false;

        var pusher = new Pusher("{{ env('PUSHER_APP_KEY') }}", {
            cluster: "{{ env('PUSHER_APP_CLUSTER') }}",
            encrypted: true
        });

        var channel = pusher.subscribe('crypto-prices');

        channel.bind('price.updated', function (data) {
            Livewire.emit('updatePrices', data.prices);
        });
    });
</script> --}}
<div class="container mx-auto p-4">
    <div class="bg-white p-4 shadow-md rounded-md">
        <h2 class="text-2xl font-bold mb-4">Live Crypto Prices</h2>

        <div class="mb-2">
            <strong>Current Time:</strong> <span wire:poll.1s="updateTime">{{ $currentTime }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse border border-gray-300 shadow-md">
                <thead class="bg-gray-200 text-gray-700">
                    <tr>
                        <th class="p-2 border">Pair</th>
                        <th class="p-2 border">Avg. Price</th>
                        <th class="p-2 border">Change</th>
                        <th class="p-2 border">Exchanges</th>
                        <th class="p-2 border">Last Updated</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($prices as $price)
                        <tr wire:key="price-{{ $price['pair'] }}" class="price-row border transition-all duration-500"
                            data-symbol="{{ $price['pair'] }}">
                            <td class="p-2 border">{{ $price['pair'] }}</td>
                            <td class="p-2 border font-bold">
                                ${{ number_format($price['average_price'], 2) }}
                            </td>
                            <td class="p-2 border text-center">
                                <span class="{{ $price['change_percentage'] >= 0 ? 'text-green-500' : 'text-red-500' }}">
                                    {{ $price['change_percentage'] }}%
                                </span>
                                <span>
                                    @if($price['change_percentage'] >= 0)
                                        🔼
                                    @else
                                        🔻
                                    @endif
                                </span>
                            </td>
                            <td class="p-2 border">{{ $price['exchanges'] }}</td>
                            <td class="p-2 border">{{ $price['last_updated'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Pusher.logToConsole = false;

            var pusher = new Pusher("{{ env('PUSHER_APP_KEY') }}", {
                cluster: "{{ env('PUSHER_APP_CLUSTER') }}",
                encrypted: true
            });

            var channel = pusher.subscribe('crypto-prices');

            channel.bind('price.updated', function (data) {
                Livewire.emit('updatePrices', data.prices);
            });
        });

        document.addEventListener("highlightPrices", function (event) {
            let updatedPairs = event.detail.pairs;
            updatedPairs.forEach(pair => {
                let row = document.querySelector(`[data-symbol="${pair}"]`);
                if (row) {
                    row.classList.add('glow');
                    setTimeout(() => row.classList.remove('glow'), 2000);
                }
            });
        });
    </script>

    <style>
        @keyframes highlight {
            0% { background-color: #ffffcc; }
            100% { background-color: transparent; }
        }

        .glow {
            animation: highlight 2s ease-in-out;
        }
    </style>
</div>
