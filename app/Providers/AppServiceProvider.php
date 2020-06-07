<?php

namespace App\Providers;

use App\Models\Voucher;
use App\Observers\VoucherObserver;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\Log;

//class LaravelLoggerProxy {
//  public function log($msg) {
//    Log::info($msg);
//  }
//}

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        Voucher::observe(VoucherObserver::class);

//        $pusher = $this->app->make('Pusher');
//        $pusher->set_logger(new LaravelLoggerProxy());
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
