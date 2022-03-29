<?php

namespace App\Providers;

use App\Models\CastMember;
use App\Models\Category;
use App\Models\Genre;
use App\Observers\SyncModelObserver;
use Illuminate\Support\ServiceProvider;
use DB;
use Log;

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
        if (env('TESTING_PROD') !== false) {
            Category::observe(SyncModelObserver::class);
            Genre::observe(SyncModelObserver::class);
            CastMember::observe(SyncModelObserver::class);
        }

        \View::addExtension('html', 'blade');

        // DB::listen(function($query) {
        //     Log::info(
        //         $query->sql,
        //         $query->bindings,
        //         $query->time
        //     );
        // });
    }
}
