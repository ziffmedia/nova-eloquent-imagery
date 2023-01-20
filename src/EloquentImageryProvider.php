<?php

namespace ZiffMedia\NovaEloquentImagery;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class EloquentImageryProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot(Router $router)
    {
        Nova::serving(function (ServingNova $event) {
            Nova::script('eloquent-imagery', __DIR__ . '/../dist/js/nova.js');
        });
    }
}
