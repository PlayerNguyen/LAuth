<?php
/**
 * lauth_core.php
 * Created by Billyz (Player_Nguyen) at 4:23 CH 04/08/2019
 * Code in Lauth Project
 *
 * lauth_core là module hệ thống của lauth, dùng để load những modules khác
 * nên sẽ không cần register =)))
 */

define("LAUTH_SETTINGS_TYPE_TEXT",          "text");
define("LAUTH_SETTINGS_TYPE_PASSWORD",      "password");
define("LAUTH_SETTINGS_TYPE_CHECKBOX",      "checkbox");
define("LAUTH_SETTINGS_TYPE_LARGE_TEXT",    "largetext");

define("LAUTH_SESSION_LOGGED",              "lauth_logged");
define("LAUTH_SESSION_LOGGED_USERNAME",     "lauth_logged_username");
define("LAUTH_SESSION_LOGGED_ID",           "lauth_logged_id");

define("LAUTH_SESSION_ADMIN_LOGGED",        "true");

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
 * Trả về tất cả file module
 * @since 1.0
 */
function lauth_modules()
{
    $sd = scandir(LAUTH_FOLDER_MODULES);
    $array = [];
    foreach ($sd as $value) {
        if ($value == ".." || $value == "." || $value == LAUTH_MODULES_CORE) {
            continue;
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
    if (!file_exists(LAUTH_FOLDER_MODULES . $file)) new  lauth_error("Không tìm thấy tệp modules {$file} khi đăng ký modules {$name}", LAUTH_ERRO_ERROR);
    return $modules->add($name, $file);
}

/**
 * Import tất cả module
 * @param $modules lauth_modules
 * @since 1.0
 */
function lauth_modules_import($modules)
{
    foreach (lauth_modules() as $file) {
        require_once $file;

        if (is_null($modules->search($file, $modules::SEARCH_VALUE))) {
            new lauth_error("Module {$file} chưa được đăng ký ", LAUTH_ERRO_NOTICE);
        }
    }

}

/**
 * Những modules đã đăng ký
 * @param $modules lauth_modules
 * @return array
 * @since 1.0
 */
function lauth_modules_registered($modules)
{
    return $modules->_TASK;
}

/** Error system */
/**
 * Defining property
 */
define("LAUTH_ERRO_NOTICE", 1024);
define("LAUTH_ERRO_WARN", 512);
define("LAUTH_ERRO_ERROR", 256);

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
 * Task dùng để load mọi phương tiện
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
     *
     * @param $name string Tên của task đó
     * @param $object mixed object bạn muốn đưa vào
     * @param bool $sort
     * @return mixed giá trị của biến object
     * @since 1.0
     */
    public function add($name, $object, $sort = false) {
        $adding = $this->_TASK[$name] = $object;
        if ($sort) ksort($this->_TASK);
        return $adding;
    }

    /**
     * Lượm trong _TASK;
     * @param $index int
     * @return mixed
     * @since 1.0
     */
    public function get($index) { return $this->_TASK[$index]; }

    /**
     * Tìm kiếm với thời gian O(n) (linear search)
     * @param $what
     * @param $search_type
     * @return null|mixed
     * @since 1.0
     */
    public function search($what, $search_type = self::SEARCH_NAME)
    {
        # search with the name only
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

/**
 * Class lauth_modules
 * Dùng để load module
 * @since 1.0
 */
class lauth_modules extends lauth_task
{
}

/**
 * Class lauth_navbar
 * @since 1.0
 */
class lauth_navbar extends lauth_task
{
}

/**
 * Class lauth
 * @since 1.0
 */
class lauth
{
    /**
     * @var lauth_modules
     */
    public static $_MODULES;
    /**
     * @var lauth_navbar
     */
    public static $_NAVBAR;
    /**
     * @var lauth_mysql
     */
    public static $_MYSQL;
    /**
     * Những cài đặt mặc định
     * @var
     */
    public static $_DEFAULT_SETTINGS;
    public static $_SETTINGS_CATEGORY;
}

/**
 * Xem rằng đã có setup chưa
 * @return bool
 * @since 1.0
 */
function is_setup()
{
    return file_exists(LAUTH_FILE_CONFIG);
}

/**
 * Kiểm tra xem phiên bản PHP hiện tại có phù hợp hay không
 * @param $required_version string phiên bản yêu cầu (define LAUTH_PHP_VERSION_REQUEST)
 * @return bool
 * @since 1.0
 */
function is_valid_php_version($required_version = LAUTH_PHP_VERSION_REQUEST)
{
    return $required_version < phpversion();
}

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
 * Khởi tạo navbar
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
 * Lấy những task đã register trong navbar
 * @param $modules lauth_navbar
 * @return array
 * @since 1.0
 */
function lauth_navbar_registered($modules)
{
    return $modules->_TASK;
}

/**
 * Tải navbar thành html. Dùng ở phía dưới của tag body
 * @since 1.0
 */
function lauth_navbar_load()
{
    $_SERVERNAME = LAUTH_SERVER_NAME;
    $_HOMEPAGE = LAUTH_SERVER_URL;

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

    echo $html;
}

/**
 * Dùng để tải navbar của admin
 * TODO: thay đổi nó
 * @since 1.0
 */
function lauth_navbar_admin_load()
{
    $_SERVERNAME = LAUTH_SERVER_NAME;
    $_HOMEPAGE = LAUTH_SERVER_URL;

    lauth_error_check();

    $html = "<!-- Administrative navbar -->";
    $html .= "<nav class='navbar bg-white collapsible' id='admin-navbar' role='navigation'><div class='navbar-show'><div class='navbar-item'><a href='{$_HOMEPAGE}'><img class='navbar-brand' src='https://minotar.net/avatar/Player_Nguyen/50.png' alt='Brand Icons'><h1 class='title-normal navbar-brand-title'>{$_SERVERNAME}</h1></a></div><button class='navbar-collapse for-mobile'>&#9776;</button></div>";
    $html .= "<div class='navbar-content'>";
    foreach (lauth_navbar_registered(lauth::$_NAVBAR) as $key => $navbar_item) {
        if (is_array($navbar_item)) {
            $_TITLE = $key;
            $html .= "<div class='dropdown navbar-item'><a class='dropdown-title'>{$_TITLE}</a><div class='dropdown-content'>";
            $counter = 0;
            foreach ($navbar_item as $key1 => $value1) {
                if (empty($key1) || empty($value1)) {
                    new lauth_error(sprintf("Mảng phải có hai giá trị tại {$value1} [%s]", $counter),
                        LAUTH_ERRO_NOTICE
                    );
                    continue;
                }
                $html .= "<a class='dropdown-item' href='{$value1}'>{$key1}</a>";
                $counter++;
            }
            $html .= "</div></div>";
        } else {
            $html .= "<div class='navbar-item'><a href='{$navbar_item}'>{$key}</a></div>";
        }
    }
    $html .= "</div>";
    $html .= "</nav>";

    echo $html;
}

function lauth_error_check()  {
    // Kiểm tra lỗi của cài đặt
    // MySQL
    # Kiểm tra bảng AuthMe
    $authme_table = lauth_settings_get(lauth::$_MYSQL, "authme_table");
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
 * @since 1.0
 */
function lauth_debug_init()
{
    if (LAUTH_SETTINGS_DEBUG) error_reporting(LAUTH_ERRO_WARN || LAUTH_ERRO_NOTICE || LAUTH_ERRO_ERROR);
    else error_reporting(0);
}

/**
 * Chặn chỉ mục khi robot scan vào web, gọi ở đầu trang web (phần <head>)
 * @since 1.0
 */
function robots_norobot()
{
    echo "<meta name='robots' content='noindex'>";
}

/**
 * Chặn chỉ mục đối với bot của google, gọi ở đầu trang web (phần <head>)
 * @since 1.0
 */
function grobots_norobot()
{
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
function redirect($destination)
{
    header("Location: {$destination}");
}

/**
 * Sử dụng với func delay_redirect()
 */
define("LAUTH_DELAYING_SHORT",  3);
define("LAUTH_DELAYING_NORMAL", 5);
define("LAUTH_DELAYING_LONG",   7);
/**
 * Chuyển hướng đến một trang khác sau khi delay
 * @param $destination string địa điểm muốn đến
 * @param $delay int thời gian
 * @since 1.0
 */
function delay_redirect($destination, $delay = 3)
{
    if (is_string($delay)) $delay = intval($delay);
    header("Refresh: {$delay}; url={$destination}");
}

class lauth_settings_category extends lauth_task
{

    /**
     * @param string $name
     * @param mixed $object
     * @param bool $sort
     * @return mixed
     * @since 1.0
     */
    public function add($name, $object, $sort = true) {
        return parent::add($name, $object, $sort);
    }

}

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
class lauth_default_settings extends lauth_task
{
}

/**
 * Khởi tạo task cài đặt mặc định
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
        return ["Đăng nhập thành công. Bấm vào <a href='index.php'>đây</a> nếu trình duyệt không tự động chuyển", LAUTH_ALERT_FINE];
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
    return "<li>Không tìm thấy gì ở đây</li>";
}

/**
 * Gọi những loader cần thiết
 *
 * @param string $propertyCaller
 * @param array $args
 * @since 1.0
 */
function call_loader($propertyCaller = '', $args = []) {
    switch ($propertyCaller) {
        case  'navbar': {

            break;
        }
    }
}

/**
 * Gọi đăng nhập trang quản trị
 *
 * @param $password
 * @return array
 */
function lauth_admin_login ($password) {
    if (lauth_sessions_get(LAUTH_SESSION_LOGGED_USERNAME) != LAUTH_ADMIN_USERNAME) {
        delay_redirect("index.php", 5);
        return ['Tài khoản của bạn không phải là tài khoản quản trị.', LAUTH_ALERT_ERROR];
    }
    if (empty($password)) {
        return ["Không đủ dữ kiện để đăng nhập, hãy nhập đủ", LAUTH_ALERT_ERROR];
    }
    if (!salty_verify($password, LAUTH_ADMIN_PASSWORD)) {
        return ["Mật khẩu quản trị không đúng, hãy thử lại", LAUTH_ALERT_ERROR];
    }
    lauth_sessions_set(LAUTH_SESSION_ADMIN_LOGGED, true);
    return ["Đăng nhập thành công, bấm vào <a href='admin-page.php'>đây</a> nếu trình duyệt của bạn không tự chuyển.", LAUTH_ALERT_FINE];
}

/**
 * Google Analytics Services
 * @since
 */
function google_analytics_init() {
    // TODO thêm google analytics api
}