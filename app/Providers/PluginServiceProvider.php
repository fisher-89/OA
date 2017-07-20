<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\PluginService;
use App\Services\DingdingApi;

class PluginServiceProvider extends ServiceProvider {

    /**
     * 服务提供者加是否延迟加载.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot() {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register() {
        $this->app->instance('Plugin', new PluginService);
        $this->app->singleton('DingdingApi', function() {
            return new DingdingApi();
        });
    }

    /**
     * 获取由提供者提供的服务.
     *
     * @return array
     */
    public function provides() {
        return ['Plugin', 'DingdingApi'];
    }

}