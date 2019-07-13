<?php
namespace nucssa_pickup;

use EasyWeChat\Factory;
use EasyWeChat\Kernel\Messages\Message;

class RESTful {

  public static function init(){
    // /nucssa-pickup/v1/wechat GET,POST
    self::wechatResponseAPI();
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

          $response = $app->server->serve();
          $response->send();
        }
      ]
    ]);
  }
}