<?php
/**
 * login.php
 * Created by Billyz (Player_Nguyen) at 9:49 CH 07/08/2019
 * Code in Lauth Project
 */

require_once "includes.php";

?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!-- SEO tags -->
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="author" content="">
    <meta name="theme-color" content="">

    <title>Đăng nhập | <?php echo LAUTH_SERVER_NAME ?></title>
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

<?php
if (lauth_is_logged()) {
    display_alert("Bạn đã đăng nhập", LAUTH_ALERT_ERROR);
    return;
}
?>
<div class="container p-3 mt-m-5" id="login-div">

    <h1 class="title-large">Đăng nhập</h1>

    <form action="" method="post" class="form">
        <div class="form-group">
            <label for="username">Tên tài khoản</label>
            <input class="form-control" type="text" name="username" placeholder="Tên tài khoản Minecraft"
                   title="Tên tài khoản Minecraft">
        </div>
        <div class="form-group">
            <label for="password">Mật khẩu</label>
            <input class="form-control" type="password" name="password" placeholder="Mật khẩu" title="Mật khẩu">
        </div>
        <div class="form-group rtl">
            <button type="submit" class="btn btn-primary" name="setup">Đăng nhập</button>
        </div>
    </form>
</div>

</body>
</html>
