<?php
/**
 * admin-page.php
 * Created by Billyz (Player_Nguyen) at 9:51 CH 08/08/2019
 * Code in Lauth Project
 */

require_once "includes.php";

if (!lauth_sessions_isset(LAUTH_SESSION_ADMIN_LOGGED) || lauth_sessions_get(LAUTH_SESSION_ADMIN_LOGGED) != true) {
    redirect("index.php");
}
/**
 * @return mixed|null
 * @since 1.0
 */
function get_category()
{
    if (!isset($_GET['/category'])) return 0;
    else  return $_GET['/category'];
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
        <div class="display-flex" role="group">
            <ul class="list-group w-25" id="right-list-admin" role="group">
                <?php lauth_settings_category_as_list_load(lauth::$_SETTINGS_CATEGORY->_TASK); ?>
            </ul>
            <div class="container w-75 bg-white shadowing p-3">
                <?php
                $_result    = lauth_settings_category_by_id(lauth::$_SETTINGS_CATEGORY, get_category());
                $__result   = lauth_settings_get_key_by_category(lauth::$_MYSQL, get_category());
                if (isset($_GET['set'])) {
                    $_settings_key = [];
                    $results = [];

                    $done = false;
                    foreach ($_GET as $key=>$value) {
                        foreach ($__result as $comparator) {
                            if ($comparator == $key) $_settings_key[$key] = $value;
                        }
                    }
                    foreach ($_settings_key as $key=>$value) {
                        $a = lauth_settings_update(lauth::$_MYSQL, $key, $value, lauth_settings_get(lauth::$_MYSQL, $key, 'category'));
                        array_push($results, $a);
                    }
                    foreach ($results as $result) { if ($results) $done = true; else $done = false; }
                    if ($done == true) display_alert("Chỉnh sửa thành công", LAUTH_ALERT_FINE);
                    delay_redirect("?/category={$_GET['/category']}");
                } ?>
                <h3 class="title-normal"><?php echo $_result[1]; ?></h3>
                <form action="" role="form" class="form">
                    <table class="table p-3 w-100">
                        <?php foreach ($__result as $__key)  {
                            $_val               = lauth_settings_get(lauth::$_MYSQL, $__key);
                            $_string_name       = lauth_settings_get(lauth::$_MYSQL, $__key, 'string_name');
                            $_small_text        = lauth_settings_get(lauth::$_MYSQL, $__key, 'small_text');
                            $type               = lauth_settings_get(lauth::$_MYSQL, $__key, 'type');
                            ?>
                            <tr class="table-group">
                                <td style="width: 30%;"><p class="m-0 p-0"><?=$_string_name ?></p><small class="form-small-text"><?php echo html_entity_decode($_small_text) ?></small></td>
                                <td style="width: 70%;">
                                    <?php if ($type == LAUTH_SETTINGS_TYPE_TEXT || $type == LAUTH_SETTINGS_TYPE_PASSWORD || $type == LAUTH_SETTINGS_TYPE_CHECKBOX) { ?>
                                        <input
                                                class="form-control"
                                                type="<?=$type ?>"
                                                value="<?=$_val; ?>"
                                                name="<?=$__key ?>"
                                                title="<?=$__key ?>"
                                        />
                                    <?php } else if ($type == LAUTH_SETTINGS_TYPE_LARGE_TEXT) { ?>
                                        <div style="height: auto;color:#000000;" class="lauth-editor form-control" name="<?=$__key ?>" title="<?=$__key ?>"><?=$_val; ?></div>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </table>
                    <input type="hidden" name="/category" value="<?php echo get_category(); ?>">
                    <div class="rtl w-100" >
                        <button type="submit" name="set"  class="btn btn-primary">Chỉnh sửa</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
