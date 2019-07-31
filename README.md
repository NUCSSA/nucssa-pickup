### Screenshots
#### Admin Dashboard (司机审核、新生审核)
![admin dashboard](README/admin_dashboard.png)

#### 创建订单
<img src="README/创建订单+申请司机菜单.png" width="300" />
<img src="README/创建订单.png" width="300" />

#### 司机报名
<img src="README/司机报名表.png" width="300" />

#### 司机抢单
<img src="README/抢单菜单.png" width="300" />
<img src="README/抢单页面.png" alt="抢单页面" />


#### 司机查看自己抢到的订单
<img src="README/司机查看自己抢到的单.gif" alt="抢到的单" width="600" />

#### 其他截图
<img alt="新生订单完成提交" src="README/finish_ordering.png" width="300" />

### Installation Instructions
0. Install this plugin to `$WordPress$/wp-content/plugins` directory
1. This plugin requires `nucssa-core` to perform properly
2. Admin users need to have capacility of `manage_pickups` to see the admin dashboard pages
3. Need to define the following constants in `wp-config.php`:
```php
/**
 * SendGrid Mailing Relay Configurations
 */
define('PICKUP_ADMIN_EMAIL', 'xxx');
define('SENDGRID_SMTP_HOST', 'xxx');
define('SENDGRID_SMTP_PORT', xxx);
define('SENDGRID_SMTP_USERNAME', 'xxx');
define('SENDGRID_API_KEY', 'xxx');
define('SENDGRID_SMTP_SECURE', 'TLS');
```