<?php

namespace App\Providers;

use App\Models\FoundItem;
use App\Models\LostItem;
use App\Observers\FoundItemObserver;
use App\Observers\LostItemObserver;
use App\Services\AnalyticsCounter;
use Illuminate\Support\ServiceProvider;

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
        FoundItem::observe(FoundItemObserver::class);
        LostItem::observe(LostItemObserver::class);

        if (!app()->runningUnitTests()) {
            AnalyticsCounter::ensurePrimed();
        }
	}


    protected function mapApiRoutes()
    {
        //
    }
}
