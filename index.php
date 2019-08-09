<?php
/**
 * index.php
 * Created by Billyz (Player_Nguyen) at 4:22 CH 04/08/2019
 * Code in Lauth Project
 */

require_once "includes.php";

if (!is_setup()) header("Location: setup.php");

/**
 * TODO
 * - Thêm phần mysql của settings như lauth_settings_add, lauth_settings_set
 * - Thêm phần cài đặt AuthMe
 */


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
    <title><?php echo LAUTH_SERVER_NAME; ?></title>
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
        <h1 class="title-xhuge animated slideInUp text-center"><?php echo LAUTH_SERVER_NAME ?></h1>
        <div  class="container p-2 w-75" style="background: transparent;text-align: center">
            <div class=" animated slideInUp" style="text-align: left">
                <p class="c-white">
                    Chào, tớ là dòng mô tả về máy chủ của bạn. Bạn có thể chỉnh sửa nó trong phần <b>settings.php</b>. LAuth là một dạng giao diện trang web (ứng dụng web) được hỗ trợ cho những máy chủ Minecraft với mục đích sử dụng miễn phí. Bạn có thể sử dụng Lauth hoàn toàn miễn phí
                </p>
                <h3>Tính năng</h3>
                <ul>
                    <li><b>Hỗ trợ AuthMe</b> (đăng nhập/đăng ký)</li>
                    <li><b>Hỗ trợ nạp thẻ</b></li>
                    <li><b>Dễ dàng sử dụng</b></li>
                    <li><b>...</b></li>
                </ul>
            </div>
            <div class="">
                <a class="btn btn-transparent">Tài khoản</a>
            </div>
        </div>
    </div>

</body>
</html>
