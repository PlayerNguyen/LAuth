<?php
/**
 * lauth_core.php
 * Created by Billyz (Player_Nguyen) at 4:23 CH 04/08/2019
 * Code in Lauth Project
 *
 * lauth_core là module hệ thống của lAuth,
 * dùng để load những modules khác cũng như
 * những tác vụ cơ bản của LAuth
 *
 */

/**
 * Khai báo hằng tên các loại cài đặt mặc định
 */
define("LAUTH_SETTINGS_TYPE_TEXT",          "text");
define("LAUTH_SETTINGS_TYPE_PASSWORD",      "password");
define("LAUTH_SETTINGS_TYPE_CHECKBOX",      "checkbox");
define("LAUTH_SETTINGS_TYPE_LARGE_TEXT",    "largetext");
define("LAUTH_SETTINGS_TYPE_LIST",          "list");

/**
 * Khai báo hằng tên sessions
 */
define("LAUTH_SESSION_LOGGED",              "lauth_logged");
define("LAUTH_SESSION_LOGGED_USERNAME",     "lauth_logged_username");
define("LAUTH_SESSION_LOGGED_ID",           "lauth_logged_id");
define("LAUTH_SESSION_ADMIN_LOGGED",        "lauth_admin_logged");

/**
 * Khai báo hằng lỗi
 * Các hằng này dùng với lauth_error()
 */
define("LAUTH_ERRO_ERROR",          256);
define("LAUTH_ERRO_WARN",           512);
define("LAUTH_ERRO_NOTICE",         1024);

/**
 * Khai bao hằng cài đặt
 */
define("LAUTH_MINECRAFT_USERNAME_RANGE",              [3, 16]);
define("LAUTH_MINECRAFT_USERNAME_PATTERN",            '/[a-zA-Z_]/');

/**
 * Request url
 * @return mixed
 * @since 1.0
 */
function request_url()
{
    return basename($_SERVER['REQUEST_URI']);
}

/**
 * Trả về tất cả tệp modules
 *
 * @return array
 * @since 1.0
 */
function lauth_file_modules()
{
    $modules = scandir(LAUTH_FOLDER_MODULES);
    $array = [];
    foreach ($modules as $value) {
        if ($value == ".." || $value == ".") { continue; }
        /** Kiểm tra nếu module đó là module chính */
        foreach (LAUTH_DEFAULT_MODULES as $module) {
            if ($value === $module) { continue 2; }
        }
        array_push($array, $value);
    }
    return $array;
}

/**
 * Dùng để khởi tạo module
 * @return lauth_modules
 * @since 1.0
 */
function lauth_modules_init()
{
    $new = new lauth_modules();
    $GLOBALS['_MODULES'] = $new;
    lauth::$_MODULES = $new;
    return $new;
}

/**
 * @param $modules lauth_modules
 * @param $name string tên module
 * @param $file string file của module
 * @return mixed
 * @since 1.0
 */
function lauth_modules_register($modules, $name, $file)
{
    if (!file_exists(LAUTH_FOLDER_MODULES . $file))
        new lauth_error("Không tìm thấy tệp module {$file} khi đăng ký module {$name}", LAUTH_ERRO_ERROR);
    return $modules->add($name, $file);
}

/**
 * Nhập tất cả module
 *
 * @param $modules lauth_modules
 * @since 1.0
 */
function lauth_modules_import($modules) {
    foreach (lauth_file_modules() as $file) {
        require_once $file;
        if (is_null($modules->search($file, $modules::SEARCH_VALUE))) {
            new lauth_error("Module {$file} chưa được đăng ký ", LAUTH_ERRO_NOTICE);
        }
    }
}

/**
 * Những modules đã đăng ký
 *
 * @param $modules lauth_modules
 * @return array
 * @since 1.0
 */
function lauth_modules_registered($modules) { return $modules->_TASK; }

/**
 * Class lauth_error
 * @since 1.0
 */
class lauth_error
{
    /**
     * lauth_error constructor.
     * @param $messages string the messages of error
     * @param int $property 0 = notice, 1 = warning; 2 = error
     */
    public function __construct($messages, $property = LAUTH_ERRO_NOTICE)
    {
        if ($messages == "") {
            die("Biến messages khi call hàm lauth_error() không được để trống!");
        }
        $bg = "";
        if ($property == LAUTH_ERRO_NOTICE) $bg = "#ffa184";
        if ($property == LAUTH_ERRO_WARN) $bg = "#f7ff84";
        if ($property == LAUTH_ERRO_ERROR) $bg = "#ff8495";

        echo "<div class='' style='text-align: center;background:{$bg};padding: 0;color: #676767'>";
        if (LAUTH_SETTINGS_DEEP_DEBUG) {
            debug_print_backtrace();
        }

        error_log($messages);
        trigger_error($messages, $property);
        echo "<br></div>";
    }

}

/**
 * Task dùng để tải các ngăn xếp trong
 * @since 1.0
 */
abstract class lauth_task
{
    /**
     * Search as the name (key)
     */
    const SEARCH_NAME = 0;
    /**
     * Search as the value
     */
    const SEARCH_VALUE = 1;
    /**
     * The tasks list
     * @var array
     */
    public $_TASK = [];

    /**
     * Thêm task vào ngăn xếp
     *
     * @param $name string Tên của task đó
     * @param $object mixed object bạn muốn đưa vào
     * @return mixed giá trị của biến object
     * @since 1.0
     */
    public function add($name, $object) {
        $adding = $this->_TASK[$name] = $object;
        return $adding;
    }

    /**
     * Lượm task
     * @param $index int
     * @return mixed
     * @since 1.0
     */
    public function get($index) { return $this->_TASK[$index]; }

    /**
     * Tìm kiếm với thời gian O(n) (linear search)
     *
     * @param $what
     * @param $search_type
     * @return null|mixed
     * @since 1.0
     */
    public function search($what, $search_type = self::SEARCH_NAME) {
        if ($search_type == self::SEARCH_NAME) {
            foreach ($this->_TASK as $key => $value) {
                if ($key == $what) return $this->_TASK[$key];
            }
        } else {
            foreach ($this->_TASK as $value) {
                if ($value == $what) return $what;
            }
        }
        return null;
    }
}

abstract class lauth_sortable_task extends lauth_task {
    /**
     * Sắp xếp sau khi thêm task vào ngăn xếp
     *
     * @param string $name
     * @param mixed $object
     * @return mixed
     * @since 1.0
     */
    public function add ($name, $object) {
        $adding = parent::add($name, $object);
        ksort($this->_TASK);
        return $adding;
    }
}
/**
 * Class lauth_modules
 * Dùng để load module
 * @since 1.0
 */
class lauth_modules extends lauth_task { }

/**
 * Thanh điều hướng
 *
 * Class lauth_navbar
 * @since 1.0
 */
class lauth_navbar extends lauth_task { }

/**
 * Class lauth
 * @since 1.0
 */
class lauth
{
    /**
     * Dùng để tải những module
     * @var lauth_modules
     */
    public static $_MODULES;
    /**
     * Dùng để tải thanh điều hướng
     * @var lauth_navbar
     */
    public static $_NAVBAR;
    /**
     * Dùng để tải MySQL
     * @var lauth_mysql
     */
    public static $_MYSQL;
    /**
     * Dùng để tải những cài đặt mặc định
     * @var lauth_default_settings
     */
    public static $_DEFAULT_SETTINGS;
    /**
     * Dùng để tải những mục cài đặt trong
     * trang quản trị
     *
     * @var lauth_settings_category
     */
    public static $_SETTINGS_CATEGORY;
}

/**
 * Xem rằng user đã có thết lập hay chưa
 *
 * @return bool
 * @since 1.0
 */
function is_setup()
{
    return file_exists(LAUTH_FILE_CONFIG);
}

/**
 * Kiểm tra xem phiên bản PHP hiện tại có phù hợp hay không
 *
 * @param $required_version string phiên bản yêu cầu (define LAUTH_PHP_VERSION_REQUEST)
 * @return bool
 * @since 1.0
 */
function is_valid_php_version($required_version = LAUTH_PHP_VERSION_REQUEST) { return $required_version < phpversion(); }

/**
 * Tạo tệp tin
 * @param $name
 * @param $data
 * @return bool|int
 * @since 1.0
 */
function lauth_files_create($name, $data)
{
    if (file_exists($name)) new lauth_error("Tệp đã có {$name} khi gọi func lauth_files_create", LAUTH_ERRO_ERROR);
    $open = fopen($name, "w");
    if (!$open) new lauth_error("Không thể tạo tệp {$name} khi gọi func lauth_files_create", LAUTH_ERRO_ERROR);
    $write = fwrite($open, $data);
    if (!$write) new lauth_error("Lỗi ghi tệp {$name} => {$data} ", LAUTH_ERRO_NOTICE);

    return $write;
}

/**
 * Khởi tạo thanh điều hướng
 * @since 1.0
 */
function lauth_navbar_init()
{
    $a = new lauth_navbar();
    lauth::$_NAVBAR = $a;
    $GLOBALS['_NAVBAR'] = $a;
    return $a;
}

/**
 * @param $modules lauth_navbar
 * @param $name
 * @param $navbar
 * @return mixed
 * @since 1.0
 */
function lauth_navbar_register($modules, $name, $navbar)
{
    return $modules->add($name, $navbar);
}

/**
 * Lấy những task đã đăng ký trong thanh điều hướng
 *
 * @param $modules lauth_navbar
 * @return array
 * @since 1.0
 */
function lauth_navbar_registered($modules)
{
    return $modules->_TASK;
}

/**
 * Tải thanh trạng thái ở dạng html.
 * Dùng ở phía dưới của tag body
 *
 * @param bool $for_admin
 * @since 1.0
 */
function lauth_navbar_load($for_admin = false)
{
    $_SERVERNAME = LAUTH_SERVER_NAME;
    $_HOMEPAGE = LAUTH_SERVER_URL;

    /** Kiểm tra cài đặt trước khi tải thanh điều hướng */
    lauth_error_check();

    $html = "<!-- Navbar -->";
    $html .= "<nav class='navbar bg-white collapsible' id='navbar' role='navigation'><div class='navbar-show'><div class='navbar-item'><a class='display-flex' href='{$_HOMEPAGE}'><img class='navbar-brand' src='https://minotar.net/avatar/Player_Nguyen/50.png' alt='Brand Icons'><h1 class='title-normal navbar-brand-title'>{$_SERVERNAME}</h1></a></div><button class='navbar-collapse for-mobile'>&#9776;</button></div>";
    $html .= "<div class='navbar-content'>";
    foreach (lauth_navbar_registered(lauth::$_NAVBAR) as $key => $navbar_item) {
        if (is_array($navbar_item)) {
            $_TITLE = $key;
            $html .= "<div class='dropdown navbar-item'><a class='dropdown-title navbar-link'>{$_TITLE}</a><div class='dropdown-content'>";
            $counter = 0;
            foreach ($navbar_item as $key1 => $value1) {
                if (empty($key1) || empty($value1)) {
                    new lauth_error(sprintf("Mảng phải có hai giá trị tại {$value1} [%s]", $counter),
                        LAUTH_ERRO_NOTICE
                    );
                    continue;
                }
                $html .= "<a class='dropdown-item navbar-link' href='{$value1}'>{$key1}</a>";
                $counter++;
            }
            $html .= "</div></div>";
        } else {
            $html .= "<div class='navbar-item'><a href='{$navbar_item}' class='navbar-link'>{$key}</a></div>";
        }
    }
    $html .= "</div>";
    $html .= "</nav>";

    if ($for_admin) {
        // TODO for admin?
        $html .= "<!-- Admin navbar -->";
    }

    echo $html;
}

/**
 * Dùng để kiểm tra lỗi cài đặt
 * Nó hiển thị ở phía trên thanh điều hướng
 *
 * @since 1.0
 */
function lauth_error_check()  {
    # Kiểm tra bảng AuthMe
    $authme_table = lauth_settings_get(lauth::$_MYSQL, LAUTH_SETTINGS_KEY_AUTHME_TABLE);
    if (!lauth_mysql_table_isset(lauth::$_MYSQL, $authme_table)) {
        display_alert("Không tìm thấy bảng `{$authme_table}` của AuthMe", LAUTH_ALERT_WARN);
    }
    # Kiểm tra reCaptcha
    $is_enable_recaptcha = lauth_settings_get(lauth::$_MYSQL,  "recaptcha_enable");
    if  ($is_enable_recaptcha) {
        $secret_key = lauth_settings_get(lauth::$_MYSQL, "recaptcha_secret_key");
        $site_key   = lauth_settings_get(lauth::$_MYSQL, "recaptcha_site_key");
        if ($secret_key == '' ||  $site_key == '') {
            display_alert("ReCaptcha: secret key hoặc site key chưa được thiết lập", LAUTH_ALERT_WARN);
        }
    }
}

/**
 * Chạy chế đô debug nếu setting debug bật
 *
 * @since 1.0
 */
function lauth_debug_init() {
    if (LAUTH_SETTINGS_DEBUG) error_reporting(LAUTH_ERRO_WARN || LAUTH_ERRO_NOTICE || LAUTH_ERRO_ERROR);
    else error_reporting(0);
}

/**
 * Chặn chỉ mục khi robot scan vào web, gọi ở đầu trang web (phần <head>)
 * @since 1.0
 */
function robots_norobot() {
    echo "<meta name='robots' content='noindex'>";
}

/**
 * Chặn chỉ mục đối với bot của google, gọi ở đầu trang web (phần <head>)
 * @since 1.0
 */
function grobots_norobot() {
    echo "<meta name='googlebot' content='noindex'>";
}

/**
 * Tải CSS trong folder css/
 * @param $file string tệp
 * @since 1.0
 */
function css_load($file)
{
    $file = LAUTH_FOLDER_CSS . $file;
    if (!file_exists($file)) new lauth_error("Không tìm thấy file {$file} khi sử dụng biến <b>css_load</b>", LAUTH_ERRO_ERROR);
    echo "<link href='{$file}' rel='stylesheet'>";
}

/**
 * Tải JS trong folder js/
 * @param $file string tệp
 * @since 1.0
 */
function js_load($file)
{
    $file = LAUTH_FOLDER_JS . $file;
    if (!file_exists($file)) new lauth_error("Không tìm thấy file {$file} khi sử dụng biến js_load", LAUTH_ERRO_ERROR);
    echo "<script src='{$file}' type='text/javascript'></script>";
}

define("LAUTH_ALERT_FINE",      'fine');
define("LAUTH_ALERT_ERROR",     'error');
define("LAUTH_ALERT_WARN",      'warn');
define("LAUTH_ALERT_PRIMARY",   'primary');
/**
 * Hiển thị thanh thông báo
 * @param $message string nội dung cần thông báo
 * @param $type string loại thông báo
 * @param string $custom_class
 * @since 1.0
 */
function display_alert($message, $type, $custom_class = "")
{
    echo "<div class='alert alert-{$type} {$custom_class}' role='alert'><div class='w-100'>{$message}</div><button class='alert-dismiss' aria-label='Đóng thông báo'>&times;</button></div>";
}

/**
 * Tìm xem modules đã được đăng ký hay chưa
 * @param $modules lauth_task
 * @param $name
 * @return bool
 * @since 1.0
 */
function lauth_modules_is_registered($modules, $name)
{
    return isset($modules->_TASK[$name]);
}

/**
 * Đặt sessions với tên và giá trị
 * @param $name
 * @param $value
 * @return mixed
 * @since 1.0
 */
function lauth_sessions_set($name, $value)
{
    return $_SESSION[$name] = $value;
}

/**
 * Lựm session với tên
 * @param $name
 * @return mixed
 * @since 1.0
 */
function lauth_sessions_get($name)
{
    return $_SESSION[$name];
}

/**
 * Đã có sessions này chưa
 * @param $name
 * @return bool
 * @since 1.0
 */
function lauth_sessions_isset($name)
{
    return isset($_SESSION[$name]);
}

/**
 * Kiểm tra xem có phải url hay không
 * @param $url string
 * @return false|int
 * @since 1.0
 */
function is_valid_url($url)
{
    return preg_match("%^((https?://)|(www\.))([a-z0-9-].?)+(:[0-9]+)?(/.*)?$%i", $url) == 1;
}

/**
 * Kiểm tra xem đây có phải
 * là email thật hay không
 *
 * @param $string
 * @return bool
 * @since 1.0
 */
function is_valid_email ($string) {
    return filter_var($string, FILTER_VALIDATE_EMAIL);
}
/**
 * @param $where string the url
 * @param array $options
 * @return false|resource
 * @since 1.0
 */
function lauth_curl($where, $options = [])
{
    // Check nếu không đúng url
    if (!is_valid_url($where))
        new lauth_error(sprintf("Địa chỉ url không hợp lệ (%s) khi gọi lauth_curl", $where), LAUTH_ERRO_ERROR);

    // Mở curl
    $curl = curl_init($where);
    curl_setopt_array($curl, $options);

    $result = curl_exec($curl);

    // Điều kiện
    if (!$result)
        new lauth_error("Lỗi khi kết nối cUrl đến {$where}. Lỗi " . curl_error($curl), LAUTH_ERRO_ERROR);

    // Đóng và trả về kết quả :v
    curl_close($curl);
    return $result;
}

/**
 * Chuyển hướng đến một trang khác
 * @param $destination
 * @since 1.0
 */
function redirect($destination) { header("Location: {$destination}"); }

/**
 * Sử dụng với func delay_redirect()
 */
define("LAUTH_DELAYING_SHORT",  3);
define("LAUTH_DELAYING_NORMAL", 5);
define("LAUTH_DELAYING_LONG",   7);
/**
 * Chuyển hướng đến một trang khác sau khi delay
 *
 * @param $destination string địa điểm muốn đến
 * @param $delay int thời gian
 * @since 1.0
 */
function delay_redirect($destination, $delay = 3)
{
    if (is_string($delay)) $delay = intval($delay);
    header("Refresh: {$delay}; url={$destination}");
}

/**
 * Class lauth_settings_category
 * @since 1.0
 */
class lauth_settings_category extends lauth_sortable_task { }

/**
 * Khởi tạo dữ liệu category của cài đặt trang quản trị
 * @return lauth_settings_category
 * @since 1.0
 */
function lauth_settings_category_init()
{
    $category_init = new lauth_settings_category();
    $GLOBALS['_SETTINGS_CATEGORY'] = $category_init;
    lauth::$_SETTINGS_CATEGORY = $category_init;
    return $category_init;
}

/**
 * @param $task lauth_settings_category
 * @param $id
 * @return array|null
 * @since 1.0
 */
function lauth_settings_category_by_id ($task, $id) {
    foreach ($task->_TASK as $k=>$v) {
        if ($v[0] == $id) return $task->_TASK[$k];
    }
    return null;
}

/**
 * Trả về kích thước của các danh mục cài đặt hiện có
 *
 * @param $task lauth_settings_category
 * @return int
 * @since 1.0
 */
function lauth_settings_category_size ($task) { return count($task->_TASK); }

/**
 * @param $task lauth_settings_category
 * @param $id
 * @return mixed
 * @since 1.0
 */
function lauth_settings_category_string_name ($task, $id) {
    return lauth_settings_category_by_id($task, $id)[1];
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
class lauth_default_settings extends lauth_task{ }

/**
 * Khởi tạo task cài đặt mặc định
 *
 * @return lauth_default_settings
 * @since 1.0
 */
function lauth_settings_default_init()
{
    $init = new lauth_default_settings();
    $GLOBALS['_DEFAULT_SETTINGS'] = $init;
    lauth::$_DEFAULT_SETTINGS = $init;
    return $init;
}
/**
 * Sinh chuỗi ngẫu nhiên
 *
 * @param $length
 * @return string
 * @since 1.0
 */
function rand_string($length)
{
    $arr = array_merge(range('a', 'z'), range('A', 'Z'), range('1', '9'), ['_', '-', '^']);
    $build = "";
    for ($i = 0; $i < $length; $i++) {
        $build .= $arr[mt_rand(0, count($arr) - 1)];
    }
    return $build;
}

/**
 * Mã hóa salty
 *
 * @param $password
 * @param $salt
 * @return string
 * @since 1.0
 */
function salty_hash($password, $salt)
{
    return hash("sha256", hash("sha256", hash("sha256", $password) . $salt) . LAUTH_SECURE_CODE);
}

/**
 * Salty là một thuật mã hóa dựa trên SHA256
 * Được kết hợp với salt và một secure code, mỗi trang web có một secure riêng
 * @param $password string
 * @return string
 * @since 1.0
 */
function salty($password)
{

    $salt = rand_string(32);
    $password = salty_hash($password, $salt);

    return '$salty$' . $salt . '$' . $password;
}

/**
 * Xác nhận xem mật khẩu này có đúng
 *
 * @param $password
 * @param $hash
 * @return bool
 * @since 1.0
 */
function salty_verify($password, $hash)
{
    $exps = explode('$', $hash);
    $salt = $exps[2]; $hash = $exps[3];
    $verify = salty_hash($password, $salt);

    if ($verify === $hash) return true;
    return false;
}

/**
 * Dùng để đăng nhập trên form đăng nhập
 *
 * <i>LAuth sử dụng giao thức POST cho các form đăng nhập, đăng ký, quên mật khẩu...(bảo mật cao)</i>
 *
 * @param array $input
 * @return array|void
 * @since 1.0
 */
function lauth_login ($input) {
    if (isset($input['login'])) {
        if (lauth_recaptcha_is_enabled()) {
            $verify = lauth_recaptcha_verify_data($input['token']);
            if (is_array($verify)) { return [sprintf("Đã có lỗi khi xác thực recaptcha, những lỗi bao gồm %s", join(", ", $verify)), LAUTH_ALERT_ERROR]; }
        }
        if (empty($input['password']) || empty($input['username'])) {
            return ["Thông tin bạn nhập bị thiếu, hãy thử nhập lại đầy đủ thông tin", LAUTH_ALERT_ERROR];
        }
        $username = addslashes($input['username']);
        $password = addslashes($input['password']);
        if (!lauth_authme_is_username_registered(lauth::$_MYSQL, $username)) {
            return ["Tài khoản này không có trong cơ sở dữ liệu", LAUTH_ALERT_ERROR];
        }
        if (!lauth_password_check(lauth::$_MYSQL, $username, $password)) {
            return ["Mật khẩu bạn nhập không chính xác", LAUTH_ALERT_ERROR];
        }

        $id = lauth_mysql_select(lauth::$_MYSQL, "id", lauth_authme_table(lauth::$_MYSQL), "`username`='{$username}'");

        lauth_sessions_set(LAUTH_SESSION_LOGGED,        true);
        lauth_sessions_set(LAUTH_SESSION_LOGGED_USERNAME,   $username);
        lauth_sessions_set(LAUTH_SESSION_LOGGED_ID,         $id);
        delay_redirect(LAUTH_SERVER_URL, 3);
        return [sprintf(/** @lang text */"Đăng nhập thành công. Bấm vào <a href='%s'>đây</a> nếu trình duyệt không tự động chuyển", "index.php"), LAUTH_ALERT_FINE];
    }
}

/**
 * Hiển thị danh sách các danh mục đã đăng ký trong cài đặt của quản trị
 *
 * @param $category_task
 * @return string
 * @since 1.0
 */
function lauth_settings_category_as_list_load ($category_task) {
    foreach ($category_task as $key=>$value) {
        $id     = $value[0];
        $s_name = $value[1];
        $active = "";
        if (get_category() == $id) $active = "active";
        echo "<li class='list-group-item $active'><a href='?/category={$id}'>{$s_name}</a></li>";
    }
    return "<li class='list-group-item'>Không tìm thấy gì ở đây</li>";
}

/**
 * Gọi đăng nhập trang quản trị
 *
 * @param $password
 * @return array
 * @since 1.0
 */
function lauth_admin_signin($password) {
    if (empty($password)) {
        return ["Không đủ dữ kiện để đăng nhập, hãy nhập đủ", LAUTH_ALERT_ERROR];
    }
    if (!salty_verify($password, LAUTH_ADMIN_PASSWORD)) {
        return ["Mật khẩu quản trị không đúng, hãy thử lại", LAUTH_ALERT_ERROR];
    }
    lauth_sessions_set(LAUTH_SESSION_ADMIN_LOGGED, true);
    return [sprintf(/** @lang text */"Đăng nhập thành công. Bấm vào <a href='%s'>đây</a> nếu trình duyệt không tự động chuyển", "admin-page.php"), LAUTH_ALERT_FINE];
}

/**
 * Thiết lập trang quản trị
 *
 * @param $category_task lauth_settings_category
 * @param $category_id string|integer
 * @param $mysql_task lauth_mysql
 * @return array
 * @since 1.0
 */
function lauth_admin_page_init($category_task, $category_id, $mysql_task, $input) {
    $keys                 = lauth_settings_get_keys_by_category($mysql_task,    $category_id);

    $_settings_key = [];
    $results = [];

    $done = false;
    foreach ($_GET as $key=>$value) {
        foreach ($keys as $comparator) {
            if ($comparator == $key) $_settings_key[$key] = $value;
        }
    }
    foreach ($_settings_key as $key=>$value) {
        $a = lauth_settings_update(lauth::$_MYSQL, $key, $value, lauth_settings_get(lauth::$_MYSQL, $key, 'category'));
        array_push($results, $a);
    }
    foreach ($results as $result) { if ($results) $done = true; else $done = false; }
    if ($done == true) {
        delay_redirect("?/category={$input['/category']}", LAUTH_DELAYING_SHORT);
        return ["Chỉnh sửa thành công", LAUTH_ALERT_FINE];
    } else {
        return ["Lỗi khi chỉnh sửa", LAUTH_ALERT_ERROR];
    }
}

/**
 * Tạo thời gian bằng mili giây (dùng cho mô-đun AuthMe)
 *
 * @param $time int
 * @return float|int
 * @since 1.0
 */
function millisecond($time) { return intval($time)/1000; }

/**
 * Lấy địa chỉ hiện tại
 *
 * @return array|false|string
 * @since 1.0
 */
function get_client_ip_env() {
    if (getenv('HTTP_CLIENT_IP'))
        $addr3ss = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $addr3ss = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $addr3ss = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $addr3ss = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
        $addr3ss = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $addr3ss = getenv('REMOTE_ADDR');
    else
        $addr3ss = 'UNKNOWN';
    return $addr3ss;
}

/**
 * Đăng ký tài khoản với form
 *
 * @param $input array
 * @param $mysql_task lauth_mysql
 * @return array
 * @since 1.0
 */
function lauth_signup ($input, $mysql_task) {
    if (lauth_recaptcha_is_enabled())  {
        $token  = $input['token'];
        $result = lauth_recaptcha_verify_data($token);

        if (is_array($result)) {
            $errors = join(',', $result);
            return ["Lỗi ReCaptcha: {$errors}", LAUTH_ALERT_ERROR];
        }
    }
    $username   = $input['username'];
    $password   = $input['password'];
    $repassword = $input['repassword'];
    $email      = $input['email'];
    if (lauth_authme_is_username_registered($mysql_task, $username)
        || lauth_authme_is_email_registered($mysql_task, $email)) {
        return ["Tài khoản {$username} hoặc email {$email} đã có người đăng ký", LAUTH_ALERT_ERROR];
    }
    if (empty($username) || empty($password) || empty($repassword) || empty($email)) {
        return ["Thông tin bạn nhập không đủ", LAUTH_ALERT_ERROR];
    }
    if (strlen($username) < LAUTH_MINECRAFT_USERNAME_RANGE[0]
        || strlen($username) > LAUTH_MINECRAFT_USERNAME_RANGE[1]
        || !preg_match("/^\w*$/", $username) === 1) {
        return ["Tên không hợp lệ. Tên chỉ được chứa kí tự từ <b>a-z, A-Z, 0-9</b> và chỉ chứa <b>_</b>(dấu gạch chân) và chỉ được từ <b>3-16</b> ký tự", LAUTH_ALERT_ERROR];
    }
    $password_min = AUTHME_PASSWORD_RANGE[0];
    $password_max = AUTHME_PASSWORD_RANGE[1];
    if (strlen($password) < $password_min
        || strlen($password) > $password_max) {
        return ["Mật khẩu không đủ điều kiện. Mật khẩu phải tối thiểu $password_min ký tự và tối đa là $password_max",  LAUTH_ALERT_ERROR];
    }
    foreach (AUTHME_PASSWORD_EASY_PASSWORD as $easy_password) {
        if ($password == $easy_password)  {
            return ["Mật khẩu này rất dễ đoán, hãy thử mật khẩu khác", LAUTH_ALERT_ERROR];
        }
    }
    if ($password !== $repassword) {
        return ["Mật khẩu và mật khẩu xác nhận không khớp", LAUTH_ALERT_ERROR];
    }
    if (!is_valid_email($email)) {
        return ["Email không hợp lệ", LAUTH_ALERT_ERROR];
    }
    $register = lauth_authme_register(lauth::$_MYSQL,  $username, $password, $email);
    if (!$register) {
        return ["Đã có lỗi khi đăng ký", LAUTH_ALERT_ERROR];
    }
    return ["Đã đăng ký thành công", LAUTH_ALERT_FINE];
}


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
class lauth_mysql extends mysqli
{
}

/**
 * Khởi tạo mysql
 *
 * @return lauth_mysql
 * @since 1.0
 */
function lauth_mysql_init()
{
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
 * @param $select_what
 * @param $select_table
 * @param $where
 * @return bool|mysqli_result
 * @since 1.0
 */
function lauth_mysql_select($link, $select_what, $select_table, $where)
{
    if (!lauth_mysql_table_isset($link, $select_table)) new lauth_error("Không tìm thấy table `{$select_table}` khi dùng lệnh lauth_mysql_select()", LAUTH_ERRO_ERROR);
    $query = sprintf(/** @lang text */ "SELECT %s FROM %s WHERE %s", $select_what, $select_table, $where);

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
    if (!lauth_mysql_table_isset($link, $insert_table))
        new lauth_error("Không tìm thấy table `{$insert_table}` khi dùng lệnh lauth_mysql_insert()", LAUTH_ERRO_ERROR);

    for ($i = 0; $i < count($insert_what); $i++) {
        $insert_what[$i] = "`$insert_what[$i]`";
    }

    for ($i = 0; $i < count($values); $i++) {
        $value = addslashes(htmlspecialchars($values[$i]));
        $values[$i] = "'$value'";
    }

    $insertTo = join(',', $insert_what);
    $values = join(",", $values);

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
function lauth_settings_update($link, $key, $value, $category)
{
    $key = addslashes($key);
    $value = addslashes($value);
    $category = addslashes($category);
    return lauth_mysql_query($link, sprintf(/** @lang text */ "UPDATE `%s` SET `value`='%s', `category` = '%s' WHERE `key` = '%s'; ", LAUTH_TABLE_SETTINGS, $value, $category, $key));
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
 * @param string $list
 * @return mixed
 * @since 1.0
 */
function lauth_settings_set_default($link, $key, $value, $category, $string_name, $small_text, $type = 'text', $selection = '')
{
    $key = addslashes($key);
    $value = addslashes($value);
    $string_name = addslashes(htmlentities($string_name));
    $small_text = addslashes(htmlentities($small_text));
    if (lauth_mysql_select($link, "`key`", LAUTH_TABLE_SETTINGS, "`key`='$key'")->num_rows <= 0) {
        return lauth_mysql_insert($link,
            LAUTH_TABLE_SETTINGS,
            ["key", "value", "category", "string_name", "small_text", "type", "selection"],
            [$key, $value, $category, $string_name, $small_text, $type, $selection]
        );
    } else return null;
}

define("LAUTH_SETTINGS_KEY_INDEX_DESCRIPTION", "index-description");
define("LAUTH_SETTINGS_KEY_AUTHME_TABLE", "authme-table");
define("LAUTH_SETTINGS_KEY_AUTHME_HASH", "authme-hash-algo");
define("LAUTH_SETTINGS_KEY_SERVER_ADDRESS", "server-address");
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
function lauth_settings_default_update($link)
{
    foreach (lauth::$_DEFAULT_SETTINGS->_TASK as $key => $value) {
        $name = $key;
        $val = $value["value"];
        $category = $value["category"];
        $string_name = $value["string_name"];
        $small_text = $value["small_text"];
        $type = $value["type"];
        $selection = $value['selection'];
        lauth_settings_set_default(
            $link,
            $name,
            strval($val),
            strval($category),
            strval($string_name),
            strval($small_text),
            strval($type),
            strval($selection)
        );
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
function lauth_settings_default_register($task, $key, $value, $category, $string_name, $small_text, $type = 'text', $selection = null)
{
    return $task->add($key, ["value" => $value, "category" => $category, "string_name" => $string_name, "small_text" => $small_text, "type" => $type, "selection" => $selection]);
}

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
function lauth_settings_category_register($task, $name, $string_name, $id)
{
    return $task->add($name, [$id, $string_name]);
}


/**
 * Google Analytics Services
 * @since 1.0
 */
function google_analytics_init() {
    // TODO thêm google analytics api
}