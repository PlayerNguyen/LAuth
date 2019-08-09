<?php
/**
 * admin-page.php
 * Created by Billyz (Player_Nguyen) at 9:51 CH 08/08/2019
 * Code in Lauth Project
 */

require_once "includes.php";

var_dump($_GET);

/**
 * Phân loại
 * @param $sub
 * @since 1.0
 */
function active_at ($sub) {
    if (isset($_GET[$sub])) echo "active";
}

/**
 * @return mixed|null
 * @since 1.0
 */
function get_category () {
    if (!isset($_GET['category'])) return null;
    else  return $_GET['category'];
}

?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="">
    <meta name="keywords" content="\">
    <meta name="author" content="\">
    <meta name="theme-color" content="">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Trang quản trị | <?php echo LAUTH_SERVER_NAME ?></title>
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

    <?php lauth_navbar_admin_load(); ?>

    <div class="container-board mt-m-5 p-1">
        <div class="display-flex" role="group" >
            <ul class="list-group w-25" id="right-list-admin" role="group">
                <li class="list-group-item <?php if (get_category() == null || get_category() == LAUTH_SETTINGS_CATEGORY_DEFAULT) echo "active"; ?>"><a href="?category=0">Cài đặt chính</a></li>
                <li class="list-group-item"><a href="?category=1">ReCaptcha</a></li>
                <li class="list-group-item"><a href="">Cài đặt chính</a></li>
            </ul>
            <div class="container w-75 bg-white shadowing p-3">
                <?php if (get_category() == null || get_category() == LAUTH_SETTINGS_CATEGORY_DEFAULT) { ?>
                    <h3 class="title-normal">Cài đặt chính</h3>
                    <form action="" role="form" class="form">
                        <table class="table p-3 w-100">
                            <tr class="table-group">
                                <td><p>Tên bảng AuthMe</p><small class="form-small-text">Tên bảng của AuthMe</small></td>
                                <td><input type="text" value="<?php echo lauth_settings_get(lauth::$_MYSQL, "authme_table") ?>" name="authme-table" class="form-control" title="Tên bảng của AuthMe"></td>
                            </tr>
                        </table>
                    </form>
                <?php } ?>
            </div>
        </div>
    </div>

</body>
</html>
