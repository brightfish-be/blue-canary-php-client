<?php

namespace Brightfish\BlueCanary\Laravel;

use Brightfish\BlueCanary\Logger;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

/**
 * Laravel service provider.
 *
 * @copyright 2019 Brightfish
 * @author Arnaud Coolsaet <a.coolsaet@brightfish.be>
 */
class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register the service provider.
     * @return void
     */
    public function register()
    {
        $this->app->singleton('blue-canary', function () {
            return new Logger($this->app);
        });
    }
}
