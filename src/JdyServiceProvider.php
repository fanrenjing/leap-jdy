<?php
namespace Leap\Jdy;
use Illuminate\Support\ServiceProvider;

class JdyServiceProvider extends ServiceProvider
{
    /**
     * 在注册后启动服务.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/jdy.php' => config_path('jdy.php'),
        ]);
    }
    /**
     * 在容器中注册绑定。
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/config/jdy.php', 'jdy');
    }
}