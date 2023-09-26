<?php
namespace Zongyanbin\Zego\Exceptions;

use Illuminate\Support\ServiceProvider;

class ZegoServiceProvider extends ServiceProvider
{

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/zego.php' => config_path('zego.php')
        ]);
    }

}