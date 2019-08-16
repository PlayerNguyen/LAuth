<?php
/**
 * error.php
 * Created by Billyz (Player_Nguyen) at 1:34 CH 16/08/2019
 * Code in Lauth Project
 */
require_once "includes.php";

/**
 * Ngôn ngữ
 *
 */
$_LANGUAGES = [
    '404'=>"Không tìm thấy trang hoặc địa chỉ này"
];

if (!isset($_GET['err'])) {
    echo "Không tìm thấy mã lỗi ??";
} else {
    $_error = $_GET['err'];
}



?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="author" content="">
    <meta name="theme-color" content="">

    <title>Lỗi <?=$_error;?> | <?=LAUTH_SERVER_NAME ?></title>

    <?php
        /** CSS Load */
        css_load(LAUTH_FILE_CSS_ANIMATE);
        css_load(LAUTH_FILE_CSS_DEFAULT);

        /** JS Load */
        js_load(LAUTH_FILE_JS_JQUERY);
        js_load(LAUTH_FILE_JS_DEFAULT);

        /** No robot */
        grobots_norobot();
        robots_norobot();
    ?>


</head>
<body>

    <?php lauth_navbar_load(); ?>

    <div class="padding-content">
        <div class="bg-white p-5 text-center" style="width: 75%;margin-left: auto;margin-right: auto;">
            <img
                src="https://media1.giphy.com/media/l4FsIC6XXeS0wGIBG/giphy.gif?cid=790b76112d72fe983ff824a81155f9acee88205bb928c63d&rid=giphy.gif"
                alt="GIF error"
                style="max-width: 250px;"
            >
            <h1 class="title-huge c-black va-m m-0 c-error">/** <?=$_error;?> */</h1>
            <h1 class="title-large c-black p-0 va-m m-0 c-dark"><?=$_LANGUAGES[$_error]?></h1>
            <h1 class="title-large c-black p-0 va-m underline"><a class="underline" href="index.php">Về trang chủ</a></h1>
        </div>
    </div>
</body>
</html>
