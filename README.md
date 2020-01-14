# laravel-auth
laravel jwt 对接vue前端的扩展

1. 提供注册、登录（获取token），重设密码等路由和控制器
2. 使用laravel自带的Auth提供者
3. 因为 `tymon/jwt-auth` 版本和laravel不能一一对应，所以请自行在项目内依赖它

## 使用
1. 执行 `composer require shellus/laravel-auth`
2. 在 `config/app.php` 添加提供者 `\Shellus\LaravelAuth\AuthProvider::class`

### 注意

1. 记得生成 `JWT_SECRET`： 执行 `php artisan jwt:secret`
2. 如果要使用重设密码功能，按laravel邮件文档配置好邮箱服务即可

### jwt和laravel版本对应

laravel 版本 | jwt版本
-|-
laravel5.6 | "tymon/jwt-auth": "1.0.0-rc.2" |


