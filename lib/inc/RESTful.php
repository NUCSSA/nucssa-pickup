<?php
namespace nucssa_pickup;

use EasyWeChat\Factory;
use EasyWeChat\Kernel\Messages\Message;
use function nucssa_core\utils\debug\file_log;

class RESTful {

  public static function init(){
    // 微信服务器认证 -- /nucssa-pickup/v1/wechat GET,POST
    // 由于种种原因我们放弃了微信的接入。
    // 也许某一天我们会重新启用这个功能，保留源代码
    // self::wechatResponseAPI();

    // 司机申请表格提交 -- /nucssa_pickup/v1/driver GET|POST
    self::driverApplicationAPI();
  }

  private static function wechatResponseAPI() {
    $namespace = 'nucssa-pickup/v1';
    $route = 'wechat';

    register_rest_route($namespace, $route, [
      [
        'methods' => ['GET', 'POST'],
        'callback' => function() {
          $config = [
            'app_id' => get_option('WECHAT_APP_ID'),
            'secret' => get_option('WECHAT_SECRET'),
            'token'  => get_option('WECHAT_TOKEN'),
            'aes_key'  => get_option('WECHAT_AES_KEY'),
            'log' => [
              'file' => __DIR__ . '/log.txt',
              'level' => 'debug',
            ]
          ];
          $app = Factory::officialAccount($config);
          $app->server->push(function($message) use ($app) {
            if ($message['Content'] == '接机'){
              return 'Hello';
            }
          }, Message::TEXT);

          header('content-type:text');
          $response = $app->server->serve();
          ob_clean();
          $response->send();
          exit;
        }
      ]
    ]);
  }

  private static function driverApplicationAPI() {
    $namespace = 'nucssa-pickup/v1';
    $route = 'driver';

    register_rest_route($namespace, $route, [
      [
        'methods' => ['GET', 'POST'],
        'callback' => function () {
          file_log('>>>');
          file_log($_SESSION['user']);
          file_log($_FILES);
          file_log($_POST);
          file_log($_GET);
        }
      ]
    ]);
  }
}