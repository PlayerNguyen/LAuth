<?php
/**
 * index.php
 * Created by Billyz (Player_Nguyen) at 3:16 CH 08/08/2019
 * Code in Lauth Project
 */

require_once "includes.php";

if (lauth_sessions_get("_admin_logged")) redirect("admin-page.php");
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
    <title>Quản trị | <?php echo LAUTH_SERVER_NAME; ?></title>
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
        if (empty($_POST['username']) || empty($_POST['password'])) {
            display_alert("Không tìm thấy các post, hãy đăng lại", LAUTH_ALERT_ERROR);
        } else {
            if (lauth_recaptcha_is_enabled()) {
                $verify = lauth_recaptcha_verify_data($_POST['recaptcha-token']);
                if (is_array($verify)) {
                    $errors = join(",", $verify);
                    display_alert("Lỗi khi xác nhận reCaptcha, các lỗi bao gồm {$errors}.", LAUTH_ALERT_ERROR);
                    return;
                }
            }
            if ($_POST['username'] != LAUTH_ADMIN_USERNAME || !salty_verify($_POST['password'], LAUTH_ADMIN_PASSWORD)) {
                display_alert("Đăng nhập không thành công, tài khoản hoặc mật khẩu không hợp lệ", LAUTH_ALERT_ERROR);
            } else {
                lauth_sessions_set("_admin_logged", true);
                display_alert("Đăng nhập thành công, bấm vào <a href='admin.php'>đây</a> nếu không được chuyển đến trang quản trị", LAUTH_ALERT_FINE);
                delay_redirect("admin-page.php");
            }
        }
    } ?>
    <h1 class="title-large">Trang quản trị</h1>
    <form action="" method="post" class="form">
        <div class="form-group">
            <label for="username">Tên tài khoản</label>
            <input class="form-control" type="text" name="username" title="Tên tài khoản" required
                   placeholder="tên tài khoản...">
            <small class="form-small-text">Tài khoản của admin</small>
        </div>
        <div class="form-group">
            <label for="password">Mật khẩu</label>
            <input class="form-control" type="password" name="password" title="Mật khẩu" required
                   placeholder="mật khẩu...">
        </div>
        <?php if (lauth_recaptcha_is_enabled()) echo '<input type="hidden" name="recaptcha-token" id="recaptcha-id">' ?>
        <div class="form-group rtl">
            <button type="submit" class="btn btn-primary" name="login">Đăng nhập</button>
        </div>
    </form>
</div>
<?php if (lauth_recaptcha_is_enabled()) lauth_recaptcha_form_load("admin"); ?>
</body>
</html>
