# Livewire Crypto Price Tracker 🚀

A real-time cryptocurrency price tracker built with Laravel Livewire.

## ⚡ Quick Start

```sh
# Clone the repository
git clone https://github.com/oyatmicah/Crypto-price-assessment
cd Crypto-price-assessment

# Install dependencies
composer install && npm install

# Setup environment
cp .env.example .env

# Run setup script
php artisan key:generate
php artisan migrate --seed
php artisan serve
Run php artisan schedule:work #to set up the queue to run at every interval or 
Run php artisan queue:work
Run php artisan websockets:serve # for real-time updates (open your browser http://127.0.0.1:8000/laravel-websockets)
#testing the UI
http://127.0.0.1:8000/livewire/crypto-prices
Run php artisan test to run test
Run docker-compose up -d #while using composer

#summary
## Testing everything together
php artisan serve
php artisan queue:work
php artisan websockets:serve

#dispatch the price fetch job manually
php artisan tinker
dispatch(new App\Jobs\FetchCryptoPrices());



