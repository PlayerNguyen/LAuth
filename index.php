<?php
/**
 * index.php
 * Created by Billyz (Player_Nguyen) at 4:22 CH 04/08/2019
 * Code in Lauth Project
 */

require_once "includes.php";

if (!is_setup()) header("Location: setup.php");

?>
<!doctype html>
<html lang="vi">
<head>
    <!-- System tags -->
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!-- SEO tags -->
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="author" content="">
    <meta name="theme-color" content="">
    <!-- Request tags -->
    <title><?=LAUTH_SERVER_NAME; ?></title>
    <?php
    # Load css
    css_load(LAUTH_FILE_CSS_DEFAULT);
    css_load(LAUTH_FILE_CSS_ANIMATE);
    # Load jQuery
    js_load(LAUTH_FILE_JS_JQUERY);
    js_load(LAUTH_FILE_JS_DEFAULT);
    ?>
</head>
<body>

    <?php lauth_navbar_load(); ?>

    <div class="padding-content">
        <h1 class="title-huge wrapword animated slideInUp text-center c-white"><?= LAUTH_SERVER_NAME ?> đây</h1>
        <div class="container p-2 w-75" style="background: transparent;text-align: center">
            <div class="display-flex">
                <input
                        type="text"
                        class="form-control bg-transparent b-radius-0 c-white animated fadeInUp"
                        title="Địa chỉ máy chủ"
                        readonly
                        aria-readonly="true"
                        value="<?=lauth_settings_get(lauth::$_MYSQL, "server-ip")?>"
                >

                <button type="button" class="btn btn-transparent animated fadeInUp" aria-label="Sao chép" onclick="copy('<?=lauth_settings_get(lauth::$_MYSQL, "server-ip")?>');">📋</button>
            </div>
            <div class="c-white animated slideInUp" style="text-align: left">
                <?= html_entity_decode(lauth_settings_get(lauth::$_MYSQL, "lauth_index_description")); ?>
            </div>
            <div class="">
                <a class="btn btn-transparent">Tài khoản</a>
            </div>
        </div>
    </div>

</body>
</html>
