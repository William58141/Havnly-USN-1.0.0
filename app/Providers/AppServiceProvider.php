<?php

namespace App\Providers;

use App\Support\ApiHelper;
use App\Support\Client as NoenomicsClient;
use GuzzleHttp\Client as GuzzleHttpClient;
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
        $this->app->bind('neonomics', function () {
            $httpClient = new GuzzleHttpClient([
                'base_uri' => env('NEONOMICS_BASE_URL') . '/' . env('NEONOMICS_PRODUCT') . '/' . env('NEONOMICS_API_VERSION') . '/',
            ]);
            $apiHelper = new ApiHelper($httpClient);
            return new NoenomicsClient($apiHelper);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
