<?php
/**
 * lauth_recaptcha.php
 * Created by Billyz (Player_Nguyen) at 6:47 CH 08/08/2019
 * Code in Lauth Project
 */

lauth_modules_register(lauth::$_MODULES, "lauth_recaptcha", basename(__FILE__));

define("RECAPTCHA_SCRIPTS_SRC", "https://www.google.com/recaptcha/api.js?render=");
define("RECAPTCHA_VERIFY_URL", "https://www.google.com/recaptcha/api/siteverify");
define("LAUTH_SETTINGS_CATEGORY_RECAPTCHA_ID", 1);

/**
 * Chỉ hoạt động khi đã thiết lập
 */
if (is_setup() && lauth_modules_is_registered(lauth::$_MODULES, "lauth_mysql")) {

    function recaptcha_load()
    {
        // Register settings category
        lauth_settings_category_register(lauth::$_SETTINGS_CATEGORY, "recaptcha", "ReCaptcha", LAUTH_SETTINGS_CATEGORY_RECAPTCHA_ID);
        // Register settings default
        lauth_settings_default_register(lauth::$_DEFAULT_SETTINGS, "recaptcha_enable", 0, LAUTH_SETTINGS_CATEGORY_RECAPTCHA_ID, "Bật tính năng reCaptcha", "LAuth sử dụng reCaptcha v3.0 của Google", LAUTH_SETTINGS_TYPE_TEXT);
        lauth_settings_default_register(lauth::$_DEFAULT_SETTINGS, "recaptcha_site_key", "", LAUTH_SETTINGS_CATEGORY_RECAPTCHA_ID, "Site key của reCaptcha", "Mã trang của reCaptcha.", LAUTH_SETTINGS_TYPE_TEXT);
        lauth_settings_default_register(lauth::$_DEFAULT_SETTINGS, "recaptcha_secret_key", "", LAUTH_SETTINGS_CATEGORY_RECAPTCHA_ID, "Secret key của reCaptcha",  "Mã bí mật của reCaptcha.", LAUTH_SETTINGS_TYPE_PASSWORD);

    }

    recaptcha_load();

    /**
     * Kiểm tra xem recaptcha có được bật không
     * @return bool
     * @since 1.0
     */
    function lauth_recaptcha_is_enabled()
    {
        return lauth_settings_get(lauth::$_MYSQL, "recaptcha_enable") === "1"
            && lauth_settings_get(lauth::$_MYSQL, "recaptcha_site_key") != ''
            && lauth_settings_get(lauth::$_MYSQL, "recaptcha_secret_key") != '';
    }

    /**
     * Dùng để tải reCaptcha trong form
     *
     * @param $action
     * @since 1.0
     */
    function lauth_recaptcha_form_load($action)
    {
        if (lauth_recaptcha_is_enabled())  {
            echo "<input type='hidden' id='recaptcha-id' name='token' />";
            $site_key = lauth_settings_get(lauth::$_MYSQL, "recaptcha_site_key");

            echo "<script src='" . RECAPTCHA_SCRIPTS_SRC . "{$site_key}'></script> <script>grecaptcha.ready(function() {
          grecaptcha.execute('{$site_key}', {action: '{$action}'}).then(function(token) {
             let _recaptcha = document.getElementById('recaptcha-id');
             if (_recaptcha ==  null) console.error('Không thấy object có id recaptcha-id khi load recaptcha');
             else _recaptcha.value = token;
          });});</script>";
        } else {
            new lauth_error("reCaptcha chưa được bật, bật trong admin", LAUTH_ERRO_WARN);
        }
    }

    /**
     * Dùng để xác thực reCaptcha v3
     *
     * @param $verify_token
     * @return bool|array Trả về giá trị array là dãy lỗi nếu gặp lỗi, trả về già trị bool true nếu verify thành công
     * @since 1.0
     */
    function lauth_recaptcha_verify_data($verify_token)
    {
        $_postfield = ["secret" => lauth_settings_get(lauth::$_MYSQL, "recaptcha_secret_key"), "response" => $verify_token];
        $result = lauth_curl(RECAPTCHA_VERIFY_URL, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POST => 1,
            CURLOPT_HEADER => $_SERVER['HTTP_USER_AGENT'],
            CURLOPT_POSTFIELDS => $_postfield
        ]);

        $result = json_decode($result, true);
        $is_success = boolval($result['success']);
        if (!$is_success) return $result['error-codes'];
        else return true;
    }

}
