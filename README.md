<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Livewire Crypto Price Tracker

This repository contains a **Livewire-based Crypto Price Tracker** that updates in real-time using Pusher.

## Prerequisites
Ensure you have the following installed:
- PHP (8.1 or higher)
- Laravel (10 or higher)
- Composer
- Node.js & npm (for frontend dependencies)
- MySQL (or any supported database)
- Redis (optional but recommended for queue optimization)

## Installation

### 1. Clone the repository
```sh
git clone https://github.com/your-repo/livewire-crypto-tracker.git
cd livewire-crypto-tracker
```

### 2. Install dependencies
```sh
composer install
npm install && npm run build
```

### 3. Environment Configuration
Copy the example environment file and configure it:
```sh
cp .env.example .env
```

Update the `.env` file with your database and Pusher credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=crypto_prices
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_pusher_app_id
PUSHER_APP_KEY=your_pusher_app_key
PUSHER_APP_SECRET=your_pusher_app_secret
PUSHER_APP_CLUSTER=your_pusher_cluster
```

### 4. Generate application key
```sh
php artisan key:generate
```

### 5. Run database migrations
```sh
php artisan migrate
```

## Running the Application

### Start the Laravel development server
```sh
php artisan serve
```

### Start the queue worker (for real-time updates)
```sh
php artisan queue:work
```

## Configuring Pusher
1. Sign up at [Pusher](https://pusher.com/) and create a new app.
2. Copy the app credentials and paste them into your `.env` file under `PUSHER_APP_ID`, `PUSHER_APP_KEY`, `PUSHER_APP_SECRET`, and `PUSHER_APP_CLUSTER`.
3. Cache the configuration:
```sh
php artisan config:cache
```

## Livewire Component: `CryptoPrices.php`
Create the Livewire component:
```sh
php artisan make:livewire CryptoPrices
```

Update `app/Http/Livewire/CryptoPrices.php`:
```php
namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class CryptoPrices extends Component
{
    public $prices = [];
    public $currentTime;

    protected $listeners = ['updatePrices' => 'refreshPrices'];

    public function mount()
    {
        $this->updateTime();
        $this->refreshPrices();
    }

    public function updateTime()
    {
        $this->currentTime = now()->format('H:i:s');
    }

    public function refreshPrices($newPrices = null)
    {
        if (!$newPrices) {
            $newPrices = Cache::get('crypto_prices', []);
        }
        $this->prices = $newPrices;
        $this->dispatchBrowserEvent('highlightPrices', ['pairs' => array_column($newPrices, 'pair')]);
    }

    public function render()
    {
        return view('livewire.crypto-prices');
    }
}
```

## Livewire Blade View: `crypto-prices.blade.php`
Located at `resources/views/livewire/crypto-prices.blade.php`:
```html
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
                                        🔽
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
```

## Final Steps
- Open [http://127.0.0.1:8000/livewire/crypto-prices](http://127.0.0.1:8000/livewire/crypto-prices) in your browser.
- Verify real-time updates when price changes occur.

---
**Happy coding!**

# Livewire Crypto Price Tracker

## Architecture Overview
The system consists of the following key components:

### Laravel (Livewire) Application
- Backend is built using Laravel and Livewire for real-time updates.
- Manages cryptocurrency price data, WebSockets (Pusher), and database operations.

### Frontend (Livewire with Tailwind CSS)
- Displays live cryptocurrency prices in a user-friendly table.
- Uses Alpine.js for lightweight UI enhancements.
- Livewire's `wire:poll` mechanism fetches real-time updates.

### WebSockets (Pusher)
- Enables real-time price updates without polling the backend frequently.
- Reduces server load compared to traditional AJAX polling.

### Database (MySQL)
- Stores historical and real-time price data.
- Used for user-related data persistence.

### Caching (Redis)
- Speeds up retrieval of frequently requested data.
- Used for Livewire session storage.

### Docker & Nginx
- Containers are used for running the Laravel app, MySQL, Redis, and Pusher.
- Nginx serves as the reverse proxy.

---

## Design Decisions and Trade-offs

### 1. Laravel Livewire for Real-time Updates
✅ **Pros:**
- Eliminates the need for complex Vue.js or React setup.
- Allows seamless real-time updates via Pusher.
- Simplifies backend and frontend integration.

❌ **Trade-offs:**
- May introduce minor latency compared to client-side WebSockets.
- Can impact performance at high loads if not optimized.

### 2. Pusher vs. Native WebSockets
✅ **Why Pusher?**
- No need to maintain a WebSocket server.
- Scales easily with Laravel Broadcasting.

❌ **Trade-off:**
- External dependency (Pusher service fees at scale).
- Could have been replaced with Laravel WebSockets for self-hosting.

### 3. Docker for Development & Deployment
✅ **Why Docker?**
- Ensures consistent environment across machines.
- Allows easy scaling via `docker-compose`.

❌ **Trade-off:**
- Slight overhead in initial setup compared to local installation.

### 4. MySQL vs. NoSQL
✅ **Why MySQL?**
- Structured relational database supports financial transactions well.
- ACID compliance ensures data integrity.

❌ **Trade-off:**
- NoSQL (MongoDB) might handle high-velocity price updates better.

---

## Known Issues or Limitations & Possible Improvements

### 1. Multiple Root Element Issue in Livewire
**Problem:**
- Livewire components must have a single root element, but the view previously had multiple root elements.

✅ **Fix:**
- Wrapped the entire component in a `<div>` container.

### 2. WebSocket Delays with Pusher
**Problem:**
- Price updates might be slightly delayed due to network latency.

✅ **Improvement:**
- Switch to **Laravel WebSockets** (self-hosted) for better control over WebSocket connections.

### 3. UI Flickering on Updates
**Problem:**
- When prices update, the UI flickers as Livewire refreshes the DOM.

✅ **Fix:**
- Implement **Alpine.js** to update only changed parts dynamically.
- Use **CSS animations** (`@keyframes highlight`) for smooth updates.

### 4. Database Storage for Historical Data
**Problem:**
- The app currently doesn’t store past prices for analysis.

✅ **Improvement:**
- Implement a cron job to **save hourly price snapshots** for historical analysis.
- Use **Redis** to cache previous prices for faster access.

### 5. Scaling Beyond a Few Users
**Problem:**
- Livewire and Pusher might not scale well if thousands of users request updates simultaneously.

✅ **Improvement:**
- Offload frequent queries to **Redis caching** instead of hitting MySQL.
- Introduce **GraphQL** API for efficient data fetching.

---

## Final Thoughts
This setup balances **real-time updates, scalability, and ease of deployment** while using Laravel Livewire and WebSockets. However, future improvements could include **Laravel Octane for speed, Redis for more caching, and self-hosted WebSockets for cost control**.
