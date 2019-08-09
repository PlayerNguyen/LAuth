<?php
/**
 * includes.php
 * Created by Billyz (Player_Nguyen) at 4:22 CH 04/08/2019
 * Code in Lauth Project
 */

/**
 * Khởi tạo hằng trước khi load trang
 */
define("LAUTH_FILE_CONFIG",             "config.php");

define("LAUTH_FOLDER_MODULES",          "modules/");
define("LAUTH_FOLDER_CSS",              "css/");
define("LAUTH_FOLDER_JS",               "js/");

define("LAUTH_MODULES_CORE",            "lauth_core.php");

define("LAUTH_FILE_CSS_DEFAULT",        "default.css");
define("LAUTH_FILE_CSS_ANIMATE",        "animate.css");
define("LAUTH_FILE_JS_JQUERY",          "jquery-3.4.1.js");
define("LAUTH_FILE_JS_DEFAULT",         "default.js");

define("LAUTH_TABLE_SETTINGS",          "lauth_settings");

define("LAUTH_PHP_VERSION_REQUEST",     "5.4.0");

define("LAUTH_SETTINGS_DEBUG",          true);
define("LAUTH_SETTINGS_DEEP_DEBUG",     true);

require_once (LAUTH_FOLDER_MODULES .  LAUTH_MODULES_CORE);

session_start();
session_regenerate_id(true);

// Require biến cài đặt nếu đã thiết lập
if (is_setup()) {
    require_once LAUTH_FILE_CONFIG;

    // Tải navbar
    lauth_navbar_init();
    lauth_navbar_register(lauth::$_NAVBAR, "Trang chủ", LAUTH_SERVER_URL);

    // Tải modules
    lauth_modules_init();
    lauth_modules_import(lauth::$_MODULES);

    // Tải chế độ debug
    lauth_debug_init();

    lauth_settings_default_init();

    // MySQL
    lauth_mysql_init();
    lauth_settings_init(lauth::$_MYSQL);

}
