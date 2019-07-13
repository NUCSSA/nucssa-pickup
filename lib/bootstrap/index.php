<?php

add_action('admin_menu', ['nucssa_pickup\admin_dashboard\menu_page\AdminMenu', 'init']);
add_action('admin_enqueue_scripts', ['nucssa_pickup\AdminScripts', 'init']);
add_action('rest_api_init', ['nucssa_pickup\RESTful', 'init']);