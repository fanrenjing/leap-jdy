## 关于leap/jdy
## 安装

```bash
composer require leap/laravel-jdy
```


## 使用
- 在/app/Http/Kernel.php的 protected $routeMiddleware 数组中新增
```bash
'jdy' => \Leap\Jdy\Middleware\Jdy::class,
```


- 发送配置
```bash
php artisan vendor:publish --provider="Leap\Jdy\JdyServiceProvider"
```

- config/jdy.php
```bash
在env文件中新增配置项
JDY_EXPIRE：简道云推送消息的时间的有效期限
JDY_SECRET：简道云的secret 获取地址：https://hc.jiandaoyun.com/open/10732
JDY_EXPIRE=3600
JDY_SECRET=xxxx
```

- 在路由中使用此中间件范例：
```bash
Route::group([
    'namespace' => 'Jdy',
    'middleware' => 'jdy'
], function($router) {
    $router->post('/jdy', 'QuestionnaireController@index');
});
```