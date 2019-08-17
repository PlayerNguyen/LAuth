<?php
/**
 * signup.php
 * Created by Billyz (Player_Nguyen) at 1:21 CH 16/08/2019
 * Code in Lauth Project
 */

require_once "includes.php";

?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="author" content="">
    <meta name="theme-color" content="">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Đăng ký | <?= LAUTH_SERVER_NAME; ?></title>
    <?php
        /**
         * CSS Load
         */
        css_load(LAUTH_FILE_CSS_DEFAULT);
        css_load(LAUTH_FILE_CSS_ANIMATE);

        /**
         * JS Load
         */
        js_load(LAUTH_FILE_JS_JQUERY);
        js_load(LAUTH_FILE_JS_DEFAULT);
    ?>
</head>
<body>

    <?php lauth_navbar_load(); ?>

    <div class="container p-3 mt-m-5" id="signup-box">
        <?php if (lauth_is_logged()) {
            display_alert(/** @lang HTML */ "Bạn đã đăng nhập rồi, hãy về đúng vị trí của mình", LAUTH_ALERT_ERROR);
            delay_redirect("index.php", 5);
            return;
        } ?>
        <?php  ?>
        <h1 class="title-large">Đăng ký</h1>
        <form action="" method="post" class="form">
            <div class="form-group">
                <label for="username">Tên tài khoản</label>
                <input
                    type="text"
                    class="form-control"
                    title="Tên tài khoản"
                    name="username"

                    placeholder="Tài khoản sử dụng trong máy chủ..."
                />
                <small class="form-small-text">Tên tài khoản mà bạn dùng để chơi trong <?=LAUTH_SERVER_NAME?></small>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input
                    type="email"
                    class="form-control"
                    title="Tài khoản email"
                    name="email"
                    placeholder="Tài khoản email..."
                >
                <small class="form-small-text">Tài khoản email dùng khi quên mật khẩu, nên hãy nhập chính xác</small>
            </div>
            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <input
                    type="password"
                    class="form-control"
                    title="Mật khẩu"
                    name="password"
                    placeholder="Mật khẩu dùng để đăng nhập..."
                />
            </div>
            <div class="form-group">
                <label for="repassword">Nhập lại mật khẩu</label>
                <input
                    type="password"
                    class="form-control"
                    title="Nhập lại mật khẩu"
                    name="repassword"

                    placeholder="Nhập lại mật khẩu..."
                />
            </div>
            <?php if (lauth_recaptcha_is_enabled()) lauth_recaptcha_form_load("signup") ; ?>
            <div class="form-group rtl">
                <button type="submit" class="btn btn-primary">Đăng ký</button>
            </div>
        </form>
    </div>
</body>
</html>
