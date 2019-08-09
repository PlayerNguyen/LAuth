<?php
/**
 * lauth_recaptcha.php
 * Created by Billyz (Player_Nguyen) at 6:47 CH 08/08/2019
 * Code in Lauth Project
 */

lauth_modules_register(lauth::$_MODULES, "lauth_recaptcha", basename(__FILE__));

define("RECAPTCHA_SCRIPTS_SRC", "https://www.google.com/recaptcha/api.js?render=");
define("RECAPTCHA_VERIFY_URL",  "https://www.google.com/recaptcha/api/siteverify");

/**
 * Chỉ hoạt động khi đã thiết lập
 */
if (is_setup() && lauth_modules_is_registered(lauth::$_MODULES, "lauth_mysql") ) {

    /**
     * Kiểm tra xem recaptcha có được bật không
     * @return bool
     * @since 1.0
     */
    function lauth_recaptcha_is_enabled ()  {
        return lauth_settings_get(lauth::$_MYSQL, "recaptcha_enable");
    }
    /**
     * Dùng để tải reCaptcha trong form, gọi ngay đít và nhớ thêm input có id recaptcha-id
     * @param $action
     * @since 1.0
     */
    function lauth_recaptcha_form_load($action) {

        $site_key = lauth_settings_get(lauth::$_MYSQL, "recaptcha_site_key");

        echo "<script src='". RECAPTCHA_SCRIPTS_SRC ."{$site_key}'></script> <script>grecaptcha.ready(function() {
          grecaptcha.execute('{$site_key}', {action: '{$action}'}).then(function(token) {
             let _recaptcha = document.getElementById('recaptcha-id');
             if (_recaptcha ==  null) console.error('Không thấy object có id recaptcha-id khi load recaptcha');
             else _recaptcha.value = token;
          });});</script>";
    }

    /**
     * Dùng để xác thực reCaptcha v3
     *
     * @param $verify_token
     * @return bool|array Trả về giá trị array là dãy lỗi nếu gặp lỗi, trả về già trị bool true nếu verify thành công
     * @since 1.0
     */
    function lauth_recaptcha_verify_data ($verify_token) {
        $_postfield = ["secret"=>lauth_settings_get(lauth::$_MYSQL, "recaptcha_secret_key"),  "response"=>$verify_token];
        $result = lauth_curl(RECAPTCHA_VERIFY_URL, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POST => 1,
            CURLOPT_HEADER => $_SERVER['HTTP_USER_AGENT'],
            CURLOPT_POSTFIELDS => $_postfield
        ]);

        $result = json_decode($result, true);
        $is_success = boolval($result['success']);
        if (!$is_success)  return $result['error-codes'];
        else return true;
    }

}
