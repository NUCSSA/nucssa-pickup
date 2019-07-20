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
 * Global Constants
 */
define('NUCSSA_PICKUP_DIR_URL', \plugin_dir_url(__FILE__));
define('NUCSSA_PICKUP_DIR_PATH', \plugin_dir_path(__FILE__));
define('NUCSSA_PICKUP_PLUGIN_FNAME', __FILE__);
define('NUCSSA_PICKUP_PLUGIN_NAME', 'NUCSSA Airport Pickup');

/**
 * There is nothing to look here, everything happens in the following file:
 */
require_once 'lib/bootstrap/index.php';
