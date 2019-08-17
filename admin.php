<?php
/**
 * admin.php
 * Created by Billyz (Player_Nguyen) at 3:16 CH 08/08/2019
 * Code in Lauth Project
 */

require_once "includes.php";

if (lauth_sessions_get(LAUTH_SESSION_ADMIN_LOGGED)) redirect("admin-page.php");

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
    <title>Quản trị | <?=LAUTH_SERVER_NAME; ?></title>
    <?php
    # JS Load
    js_load("jquery-3.4.1.js");
    js_load("default.js");

    # CSS Load
    css_load("animate.css");
    css_load("default.css");

    # No robot
    robots_norobot();
    grobots_norobot();
    ?>
</head>
<body>

    <?php lauth_navbar_load(); ?>

    <div class="container mt-m-3 p-3" id="admin-login">
        <?php if (isset($_POST['login'])) {
            $display = lauth_admin_signin($_POST['password']);
            display_alert($display[0], $display[1]);
        } ?>
        <h1 class="title-large">Trang quản trị</h1>
        <form action="admin.php" method="post" class="form">
            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <input class="form-control" type="password" name="password" title="Mật khẩu" required
                       placeholder="mật khẩu...">
            </div>
            <div class="form-group rtl">
                <button type="submit" class="btn btn-primary" name="login">Đăng nhập</button>
            </div>
        </form>
    </div>

</body>
</html>
