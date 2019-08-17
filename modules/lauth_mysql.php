<?php
/**
 * lauth_mysqli.php
 * Created by Billyz (Player_Nguyen) at 4:44 CH 04/08/2019
 * Code in Lauth Project
 */

if (!extension_loaded("mysqli")) {
    new lauth_error("Phần mở rộng mysqli chưa được cài đặt hoặc bị vô hiệu hóa", LAUTH_ERRO_ERROR);
}

define("LAUTH_SETTINGS_CATEGORY_DEFAULT_ID", 0);

/**
 * Dựa trên hàm mysqli
 *
 * Class lauth_mysql
 * @since 1.0
 */
class lauth_mysql extends mysqli { }

/**
 * Khởi tạo mysql
 *
 * @return lauth_mysql
 * @since 1.0
 */
function lauth_mysql_init() {
    /** @var lauth_mysql $connection */
    $connection = new lauth_mysql(LAUTH_MYSQL_HOST, LAUTH_MYSQL_USERNAME, LAUTH_MYSQL_PASSWORD, LAUTH_MYSQL_DATABASE, LAUTH_MYSQL_PORT, null);
    /** Nếu gặp lỗi */
    if ($connection->connect_errno != 0) {
        new lauth_error(sprintf(
            "Lỗi khi kết nối đến MySQL (%s)",
            $connection->connect_error
        ), LAUTH_ERRO_ERROR);
    }
    /** Thiết lập charset UTF */
    mysqli_set_charset($connection, 'UTF8');
    /** Thiết lập task */
    lauth::$_MYSQL = $connection;
    $GLOBALS['_MYSQL'] = $connection;
    return $connection;
}

/**
 * Chạy lệnh mysql
 *
 * @param $link lauth_mysql
 * @param $query
 * @return bool|mysqli_result
 * @since 1.0
 */
function lauth_mysql_query($link, $query)
{
    $selector = $link->query($query);

    if (!$selector) {
        new lauth_error(sprintf("Lỗi khi chạy lệnh (%s). Lỗi vì %s", $query, mysqli_error($link)), LAUTH_ERRO_ERROR);
    }
    return $selector;
}

/**
 * Kiểm tra xem có tồn tại bảng
 *
 * @param $link lauth_mysql
 * @param $table_name string tên của table
 * @return bool
 * @since 1.0
 */
function lauth_mysql_table_isset($link, $table_name)
{
    $tables = lauth_mysql_query($link, "SHOW TABLES;")->fetch_all();
    for ($i = 0; $i < count($tables); $i++) {
        if ($tables[$i][0] == $table_name) return true;
    }
    return false;
}

/**
 * Tạo một bảng mới nếu chưa có
 *
 * @param $link lauth_mysql
 * @param $table_name
 * @param $values
 * @return bool|mysqli_result|null
 * @since 1.0
 */
function lauth_mysql_table_create($link, $table_name, $values)
{
    if (!lauth_mysql_table_isset($link, $table_name)) {
        $body = join(",", $values);
        $query = /** @lang text */
            "CREATE TABLE `{$table_name}` ($body); ";
        return lauth_mysql_query($link, $query);
    }
    return null;
}

/**
 * Lỗi cuối của MySQL
 *
 * @param $link lauth_mysql
 * @return mixed
 * @since 1.0
 */
function lauth_mysql_last_error($link)
{
    return $link->connect_error;
}

/**
 * @param $link
 * @param $selectWhat
 * @param $selectTable
 * @param $where
 * @return bool|mysqli_result
 * @since 1.0
 */
function lauth_mysql_select($link, $selectWhat, $selectTable, $where)
{
    if (!lauth_mysql_table_isset($link, $selectTable)) new lauth_error("Không tìm thấy table `{$selectTable}` khi dùng lệnh lauth_mysql_select()", LAUTH_ERRO_ERROR);
    $query = sprintf(/** @lang text */ "SELECT %s FROM %s WHERE %s", $selectWhat, $selectTable, $where);

    $execute = lauth_mysql_query($link, $query);
    if (!$execute) {
        new lauth_error(sprintf("Lỗi khi thực hiện lệnh lauth_mysql_select(). Lỗi %s", lauth_mysql_last_error($link)));
    }
    return $execute;
}

/**
 * Thêm dòng vào bảng
 *
 * @param $link
 * @param $insert_table string tên tables
 * @param $insert_what array những cột muốn thêm
 * @param $values array giá trị của cột muốn thêm
 * @return bool|mysqli_result
 * @since 1.0
 */
function lauth_mysql_insert($link, $insert_table, $insert_what, $values = [])
{
    if (!lauth_mysql_table_isset($link, $insert_table)) new lauth_error("Không tìm thấy table `{$insert_table}` khi dùng lệnh lauth_mysql_insert()", LAUTH_ERRO_ERROR);

    for ($i = 0; $i < count($insert_what); $i ++ ) {
        $insert_what[$i] = "`$insert_what[$i]`";
    }

    for ($i = 0; $i < count($values); $i ++ ) {
        $value = addslashes(htmlspecialchars($values[$i]));
        $values[$i] = "'$value'";
    }

    $insertTo = join(',', $insert_what); $values = join(",", $values);

    $buildQuery = sprintf(/** @lang text */ "INSERT INTO `%s` (%s) VALUES (%s);", $insert_table, $insertTo, $values);
    $execute = lauth_mysql_query($link, $buildQuery);
    if (!$execute)
        new lauth_error(sprintf("Lỗi khi insert vào MySQL. Lỗi %s", lauth_mysql_last_error($link)), LAUTH_ERRO_ERROR);
    return $execute;
}

/**
 * @param $link lauth_mysql
 * @param $key
 * @param $value
 * @param $category
 * @return bool|mysqli_result
 * @since 1.0
 */
function lauth_settings_update ($link, $key, $value, $category) {
    $key =  addslashes($key); $value = addslashes($value); $category = addslashes($category);
    return lauth_mysql_query($link, sprintf(/** @lang text */"UPDATE `%s` SET `value`='%s', `category` = '%s' WHERE `key` = '%s'; ", LAUTH_TABLE_SETTINGS, $value, $category, $key));
}

/**
 * Thêm phần cài đặt mặc định
 *
 * @param $link lauth_mysql
 * @param $key string
 * @param $value string
 * @param int $category string|int
 * @param $string_name string
 * @param $small_text string
 * @param string $type
 * @param string $selection
 * @return mixed
 * @since 1.0
 */
function lauth_settings_set_default($link, $key, $value, $category, $string_name, $small_text, $type = 'text', $selection = '')
{
    $key            = addslashes($key);
    $value          = addslashes($value);
    $string_name    = addslashes(htmlentities($string_name));
    $small_text     = addslashes(htmlentities($small_text));
    if (lauth_mysql_select($link, "`key`", LAUTH_TABLE_SETTINGS, "`key`='$key'")->num_rows <= 0) {
        return lauth_mysql_insert($link,
            LAUTH_TABLE_SETTINGS,
            ["key", "value", "category", "string_name", "small_text",  "type"],
            [$key, $value, $category, $string_name, $small_text, $type]
        );
    }
    else return null;
}

define("LAUTH_SETTINGS_KEY_INDEX_DESCRIPTION",  "index-description");
define("LAUTH_SETTINGS_KEY_AUTHME_TABLE",       "authme-table");
define("LAUTH_SETTINGS_KEY_AUTHME_HASH",        "authme-hash-algo");
define("LAUTH_SETTINGS_KEY_SERVER_ADDRESS",     "server-address");
/**
 * Khởi tạo những phần mặc định của LAuth
 * @param $link lauth_mysql
 * @since 1.0
 */
function lauth_settings_init($link)
{
    lauth_settings_default_register(
        lauth::$_DEFAULT_SETTINGS,
        LAUTH_SETTINGS_KEY_INDEX_DESCRIPTION,
        "<p>Chào, tớ là dòng mô tả về máy chủ của bạn. Bạn có thể chỉnh sửa nó trong phần <b>Cài đặt chung</b> trên trang <a href=\"admin.php\">quản trị</a>. LAuth là một dạng giao diện trang web (ứng dụng web) được hỗ trợ cho những máy chủ Minecraft với mục đích sử dụng miễn phí. Bạn có thể sử dụng Lauth hoàn toàn miễn phí</p><h3>Tính năng</h3><ul><li><b>Hỗ trợ AuthMe</b> (đăng nhập/đăng ký)</li><li><b>Hỗ trợ nạp thẻ</b></li><li><b>Dễ dàng sử dụng</b></li><li><b>...</b></li></ul>",
        LAUTH_SETTINGS_CATEGORY_DEFAULT_ID,
        "Dòng giới thiệu",
        "Dòng chữ hiễn thị ở đầu trang khi vào trang chủ. Có thể dùng HTML",
        LAUTH_SETTINGS_TYPE_LARGE_TEXT
    );
    lauth_settings_default_register(
        lauth::$_DEFAULT_SETTINGS,
        LAUTH_SETTINGS_KEY_SERVER_ADDRESS,
        "Địa chỉ IP của máy chủ có thể cài đặt tại trang quản trị",
        LAUTH_SETTINGS_CATEGORY_DEFAULT_ID,
        "Địa chỉ máy chủ",
        "Địa chỉ(IP) của máy chủ dùng để cho mọi người biết đến máy chủ của mình"
    );
}

/**
 * Cập nhật cài đặt mặc định của LAuth
 *
 * @param $link
 * @since 1.0
 */
function lauth_settings_default_update($link) {
    foreach (lauth::$_DEFAULT_SETTINGS->_TASK as $key => $value) {
        $name = $key;
        $val = $value["value"]; $category = $value["category"]; $string_name = $value["string_name"]; $small_text = $value["small_text"];$type = $value["type"];
        lauth_settings_set_default($link, $name, strval($val), strval($category), strval($string_name), strval($small_text), strval($type));
    }
}

/**
 *
 * @param $link lauth_mysql
 * @param $key
 * @param string $what
 * @return mixed
 * @since 1.0
 */
function lauth_settings_get($link, $key, $what = 'value')
{
    $_t = LAUTH_TABLE_SETTINGS;
    $selector = lauth_mysql_select($link, "`{$what}`", $_t, "`key`='{$key}'");

    if ($selector->num_rows <= 0) return null;
    else return $selector->fetch_assoc()[$what];
}

/**
 * Tìm key setting với category.
 * Trả về null nếu không tìm thấy
 * Trả về chuỗi là kết quả của những key
 * trong danh mục thuộc nhóm đó
 *
 * @param $link
 * @param int $category
 * @return array|null
 * @since 1.0
 */
function lauth_settings_get_keys_by_category($link, $category = LAUTH_SETTINGS_CATEGORY_DEFAULT_ID)
{
    $selector = lauth_mysql_select($link, "`key`", LAUTH_TABLE_SETTINGS, "`category` = '{$category}' ");
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
 *
 * Dùng để đăng ký những cài đặt mặc định trong LAuth
 *
 * @param $task lauth_default_settings task default
 * @param $key string
 * @param $value
 * @param $category
 * @param $string_name
 * @param $small_text
 * @param string $type
 * @return mixed
 * @since 1.0
 */
function lauth_settings_default_register($task, $key, $value, $category, $string_name, $small_text, $type = 'text')
{ return $task->add($key, ["value" => $value, "category" => $category, "string_name"=>$string_name, "small_text"=>$small_text, "type"=>$type]); }

/**
 *
 * Đăng ký thể loại của cài đặt trong trang quản trị
 *
 * @param $task lauth_settings_category
 * @param $name
 * @param $string_name
 * @param $id
 * @return mixed
 * @since 1.0
 */
function lauth_settings_category_register($task, $name, $string_name, $id) { return $task->add($name, [$id, $string_name]); }
