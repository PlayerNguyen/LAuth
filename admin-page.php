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
function get_category() {
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

    <?php lauth_navbar_load(true); ?>

    <div class="container-board mt-m-5 p-1">
        <div class="display-flex" role="group">
            <ul class="list-group w-25" id="right-list-admin" role="group">
                <?php lauth_settings_category_as_list_load(lauth::$_SETTINGS_CATEGORY->_TASK); ?>
            </ul>
            <div class="container w-75 bg-white shadowing p-3">
                <?php
                    $_result    = lauth_settings_category_by_id(lauth::$_SETTINGS_CATEGORY, get_category());
                    if (isset($_GET['set'])) {
                        $init = lauth_admin_page_init(lauth::$_SETTINGS_CATEGORY, get_category(), lauth::$_MYSQL, $_GET);
                        display_alert($init[0], $init[1]);
                    }
                ?>

                <h3 class="title-normal"><?php echo $_result[1]; ?></h3>
                <form action="" role="form" class="form">
                    <table class="table p-3 w-100">
                        <?php foreach (lauth_settings_get_keys_by_category(lauth::$_MYSQL, get_category()) as $key)  {
                            $_val               = html_entity_decode(lauth_settings_get(lauth::$_MYSQL, $key));
                            $_string_name       = html_entity_decode(lauth_settings_get(lauth::$_MYSQL, $key, 'string_name'));
                            $_small_text        = html_entity_decode(lauth_settings_get(lauth::$_MYSQL, $key, 'small_text'));
                            $type               = html_entity_decode(lauth_settings_get(lauth::$_MYSQL, $key, 'type'));
                            ?>
                            <tr class="table-group">
                                <td style="width: 30%;"><p class="m-0 p-0"><?=$_string_name ?></p><small class="form-small-text"><?php echo html_entity_decode($_small_text) ?></small></td>
                                <td style="width: 70%;">
                                    <?php if ($type == LAUTH_SETTINGS_TYPE_TEXT || $type == LAUTH_SETTINGS_TYPE_PASSWORD || $type == LAUTH_SETTINGS_TYPE_CHECKBOX) { ?>
                                        <input
                                                class="form-control"
                                                type="<?=$type?>"
                                                value="<?=$_val; ?>"
                                                name="<?=$key?>"
                                                title="<?=strip_tags($_small_text) ?>"
                                        />
                                    <?php } else if ($type == LAUTH_SETTINGS_TYPE_LARGE_TEXT) { ?>
                                        <div style="height: auto;color:#000000;" class="lauth-editor form-control" name="<?=$key?>" title="<?=strip_tags($_small_text)?>"><?=$_val?></div>
                                    <?php } else if ($type === LAUTH_SETTINGS_TYPE_LIST) { ?>
                                        <select  class='form-control' name="<?=$key?>" id="" title="<?=strip_tags($_small_text);?>">
                                            <?php
                                                $list = html_entity_decode(lauth_settings_get(lauth::$_MYSQL, $key, 'selection'));

                                                $values = explode('|', $list);
                                                foreach ($values as $value) {
                                                    if ($value === $_val) {
                                                        echo "<option value='$_val' selected>{$_val}</option>";
                                                        continue;
                                                    }echo "<option value='$value'>$value</option>";
                                                }
                                            ?>
                                        </select>
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
