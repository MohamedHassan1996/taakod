<?php

namespace App\Providers;

use App\Models\Product\Category;
use App\Models\Product\Product;
use App\Models\Stock\InStock;
use App\Observers\CategoryObserver;
use App\Observers\ProductObserver;
use App\Observers\PurchaseInvoice\InStockObserver;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Str::macro('randomNumeric', function ($length = 14) {
            return collect(range(1, $length))
                ->map(fn () => mt_rand(0, 9))
                ->implode('');
        });

    }
}
