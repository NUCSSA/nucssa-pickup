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
 * There is nothing to look here, everything happens in the following file:
 */
include_once 'lib/constants.php';
require_once 'lib/bootstrap/index.php';
