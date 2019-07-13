<?php
/**
 * Plugin Name:     NUCSSA Airport Pickup
 * Plugin URI:      https://www.nucssa.org
 * Description:     NUCSSA 接机服务插件
 * Author:          NUCSSA IT
 * Author URI:      https://www.nucssa.org/IT
 * Text Domain:     nucssa-pickup
 * Domain Path:     /languages
 * Version:         1.0.0
 *
 * @package         NUCSSA_Pickup
 */

// prevent direct access
defined('ABSPATH') || exit;

include_once __DIR__ . '/vendor/autoload.php';

/**
 * Constants
 */
define('NUCSSA_PICKUP_DIR_URL', \plugin_dir_url(__FILE__));

require_once 'lib/bootstrap/index.php';
