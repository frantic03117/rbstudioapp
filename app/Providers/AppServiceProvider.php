<?php

namespace App\Providers;

use App\Models\Booking;
use App\Models\RbNotification;
use App\Observers\BookingObserver;
use App\Observers\RbNotificationObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        RbNotification::observe(RbNotificationObserver::class);
        Booking::observe(BookingObserver::class);
    }
}
