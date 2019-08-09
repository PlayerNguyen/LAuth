<?php
/**
 * lauth_mysqli.php
 * Created by Billyz (Player_Nguyen) at 4:44 CH 04/08/2019
 * Code in Lauth Project
 */

if (is_setup()) lauth_modules_register(lauth::$_MODULES, "lauth_mysql", basename(__FILE__));

if (!extension_loaded("mysqli")) { new lauth_error("Phần mở rộng mysqli chưa được cài đặt hoặc bị vô hiệu hóa", LAUTH_ERRO_ERROR); }

define("LAUTH_SETTINGS_CATEGORY_DEFAULT",           0);
define("LAUTH_SETTINGS_CATEGORY_RECAPTCHA",         1);


define("LAUTH_DEAULT_SETTINGS", [

    "authme_table"                      =>  ["authme", LAUTH_SETTINGS_CATEGORY_DEFAULT],
    "recaptcha_enable"                  =>  [true, LAUTH_SETTINGS_CATEGORY_RECAPTCHA],
    "recaptcha_site_key"                =>  ['', LAUTH_SETTINGS_CATEGORY_RECAPTCHA],
    "recaptcha_secret_key"              =>  ['', LAUTH_SETTINGS_CATEGORY_RECAPTCHA],

]);

/**
 * Class lauth_mysql
 * @since 1.0
 */
class lauth_mysql extends mysqli {

    public function __construct($host = null, $username = null, $passwd = null, $dbname = null, $port = null, $socket = null)
    {
        parent::__construct($host, $username, $passwd, $dbname, $port, $socket);
    }

}

/**
 * Initial mysql
 * @return lauth_mysql
 * @since 1.0
 */
function lauth_mysql_init () {
    $a = new lauth_mysql(LAUTH_MYSQL_HOST, LAUTH_MYSQL_USERNAME, LAUTH_MYSQL_PASSWORD, LAUTH_MYSQL_DATABASE, LAUTH_MYSQL_PORT, null);

    if ($a->connect_errno != 0) {
        new lauth_error(sprintf(
            "Lỗi khi kết nối đến MySQL (%s)",
            $a->connect_error
        ), LAUTH_ERRO_ERROR);
    }

    lauth::$_MYSQL = $a;
    $GLOBALS['_MYSQL'] = $a;
    return $a;
}

/**
 * Chạy lệnh mysql
 * @param $link lauth_mysql
 * @param $query
 * @return bool|mysqli_result
 * @since 1.0
 */
function lauth_mysql_query($link, $query) {
    $selector = $link->query($query);

    if (!$selector) {
        new lauth_error(sprintf("Lỗi khi chạy lệnh (%s). Lỗi vì %s", $query, mysqli_error($link)), LAUTH_ERRO_ERROR);
    }
    return $selector;
}

/**
 * Kiểm tra xem có tồn tại bảng
 * @param $link lauth_mysql
 * @param $table_name string tên của table
 * @return bool
 * @since 1.0
 */
function lauth_mysql_table_isset ($link, $table_name) {
    $tables = lauth_mysql_query($link, "SHOW TABLES;")->fetch_all();
    for ($i = 0; $i < count($tables); $i ++) {
        if ($tables[$i][0] == $table_name) return true;
    }
    return false;
}

/**
 * Tạo một bảng mới nếu chưa có
 * @param $link lauth_mysql
 * @param $table_name
 * @param $values
 * @return bool|mysqli_result|null
 * @since 1.0
 */
function lauth_mysql_table_create($link, $table_name, $values) {
    if (!lauth_mysql_table_isset($link, $table_name)) {
        $body = join(",", $values);
        $query = /** @lang text */
            "CREATE TABLE `{$table_name}` ($body); ";
        return lauth_mysql_query($link, $query);
    }
    return null;
}
/**
 * @param $link lauth_mysql
 * @return mixed
 * @since 1.0
 */
function lauth_mysql_last_error($link) {
    return $link->connect_error;
}
function lauth_mysql_select ($link, $selectWhat, $selectTable, $where) {
    if (!lauth_mysql_table_isset($link, $selectTable)) new lauth_error("Không tìm thấy table `{$selectTable}` khi dùng lệnh lauth_mysql_select()",  LAUTH_ERRO_ERROR);
    $query      = sprintf(/** @lang text */ "SELECT %s FROM %s WHERE %s", $selectWhat, $selectTable, $where);

    $execute    = lauth_mysql_query($link, $query);
    if (!$execute) {
        new lauth_error(sprintf("Lỗi khi thực hiện lệnh lauth_mysql_select(). Lỗi %s", lauth_mysql_last_error($link)));
    }
    return $execute;
}
/**
 * Thêm dòng vào bảng
 * @param $link
 * @param $insertTable string tên tables
 * @param $insertWhat array những cột muốn thêm
 * @param $values array giá trị của cột muốn thêm
 * @return bool|mysqli_result
 * @since 1.0
 */
function lauth_mysql_insert ($link, $insertTable, $insertWhat, $values = []){

    if (!lauth_mysql_table_isset($link, $insertTable)) new lauth_error("Không tìm thấy table `{$insertTable}` khi dùng lệnh lauth_mysql_insert()",  LAUTH_ERRO_ERROR);

    $insertion  = join(",", $insertWhat);
    $values     = join(",", $values);

    $buildQuery = sprintf(/** @lang text */ "INSERT INTO `%s` (%s) VALUES (%s);", $insertTable, $insertion, $values);
    $execute    = lauth_mysql_query($link, $buildQuery);
    if (!$execute)
        new lauth_error(sprintf("Lỗi khi insert vào MySQL. Lỗi %s", lauth_mysql_last_error($link)), LAUTH_ERRO_ERROR);
    return $execute;
}

/**
 * Thêm phần cài đặt mặc định
 * @param $link lauth_mysql
 * @param $key string
 * @param $value string
 * @return mixed
 * @since 1.0
 */
function lauth_settings_set_default ($link, $key, $value, $category = LAUTH_SETTINGS_CATEGORY_DEFAULT) {
    $_t     = LAUTH_TABLE_SETTINGS; $key    = addslashes($key); $value  = addslashes($value);
    if (lauth_mysql_select($link, "`key`", $_t, "`key`='$key'")->num_rows <= 0)  return lauth_mysql_query($link, /** @lang text */ "INSERT INTO $_t (`key`, `value`, `category`) VALUES ('{$key}', '{$value}', '{$category}');");
    else return null;
}

/**
 * Khởi tạo những phần mặc định của LAuth
 * @param $link lauth_mysql
 * @since 1.0
 */
function lauth_settings_init ($link) {

    lauth_settings_default_register(lauth::$_DEFAULT_SETTINGS, "authme_table",  "authme", LAUTH_SETTINGS_CATEGORY_DEFAULT);

    lauth_settings_default_register(lauth::$_DEFAULT_SETTINGS, "recaptcha_enable",  0, LAUTH_SETTINGS_CATEGORY_RECAPTCHA);
    lauth_settings_default_register(lauth::$_DEFAULT_SETTINGS, "recaptcha_site_key",  "", LAUTH_SETTINGS_CATEGORY_RECAPTCHA);
    lauth_settings_default_register(lauth::$_DEFAULT_SETTINGS, "recaptcha_secret_key",  "", LAUTH_SETTINGS_CATEGORY_RECAPTCHA);

    foreach (lauth::$_DEFAULT_SETTINGS->_TASK as $key=>$value) {
        $name       = $key;
        $val        = $value["value"];
        $category   = $value["category"];
        lauth_settings_set_default($link, $name, strval($val), strval($category));
    }
}

/**
 * @param $link lauth_mysql
 * @param $key
 * @return null
 * @since 1.0
 */
function lauth_settings_get ($link, $key) {

    $_t         = LAUTH_TABLE_SETTINGS;
    $selector   = lauth_mysql_select($link, "`value`", $_t, "`key`='{$key}'");

    if ($selector->num_rows <= 0) return null;
    else return $selector->fetch_assoc()['value'];
}

/**
 * Tìm key setting với category.
 * Trả về null nếu không tìm thấy
 * Trả về array là kết quả của những key trong setting
 * thuộc nhóm đó
 * @param $link
 * @param int $category
 * @return array|null
 * @since 1.0
 */
function lauth_settings_get_key_by_category ($link, $category = LAUTH_SETTINGS_CATEGORY_DEFAULT) {
    $_t         = LAUTH_TABLE_SETTINGS;
    $selector   = lauth_mysql_select($link, "`key`", $_t, "`category` = '{$category}' ");
    if ($selector->num_rows <= 0) return null;
    else {
        $array = [];
        foreach ($selector->fetch_all() as $value) {
            array_push($array, $value[0]);
        }
        return $array;
    }
}

/**
 * Dùng để lưu trữ những cài đặt mặc định và đăng ký nếu không tìm thấy
 * cài đặt đó trong máy chủ CSDL
 *
 * Bạn có thể thêm cài đặt mặc định với: lauth_settings_default_register($task, $key, $value, $category);
 *
 * Class lauth_default_settings
 * @since 1.0
 */
class lauth_default_settings extends lauth_task {  }
/**
 * Khởi tạo task cài đặt mặc định
 * @return lauth_default_settings
 * @since 1.0
 */
function lauth_settings_default_init () {
    $init = new lauth_default_settings();
    $GLOBALS['_DEFAULT_SETTINGS'] = $init;
    lauth::$_DEFAULT_SETTINGS     = $init;
    return $init;
}

/**
 * @param $task lauth_default_settings task default
 * @param $key
 * @param $value
 * @param $category
 * @return mixed
 * @since 1.0
 */
function lauth_settings_default_register ($task, $key, $value, $category) {
    return $task->add($key, ["value"=>$value, "category"=>$category]);
}