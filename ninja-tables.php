<?php defined('ABSPATH') or die;

/*
Plugin Name: Ninja Tables
Description: The Easiest & Fastest Responsive Table Plugin on WordPress. Multiple templates, drag-&-drop live table builder, multiple color scheme, and styles.
Version: 4.3.4
Author: WPManageNinja LLC
Author URI: https://ninjatables.com/
Plugin URI: https://wpmanageninja.com/downloads/ninja-tables-pro-add-on/
License: GPL-2.0+
Text Domain: ninja-tables
Domain Path: /language
*/

require __DIR__.'/vendor/autoload.php';

call_user_func(function($bootstrap) {
    $bootstrap(__FILE__);
}, require(__DIR__.'/boot/app.php'));
