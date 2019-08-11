<?php
/**
 * lauth_authme.php
 * Created by Billyz (Player_Nguyen) at 2:29 CH 07/08/2019
 * Code in Lauth Project
 */

/**
 * Tớ đã bỏ md5, sha1 và một số thuật toán
 * mã hóa không an toàn vì tính bảo mật cho máy chủ
 */

lauth_modules_register(lauth::$_MODULES, "lauth_authme", basename(__FILE__));

/** @deprecated  */
define("AUTHME_SHA256", 0);
/** @deprecated  */
define("AUTHME_BCRYPT", 1);
/** @deprecated  */
define("AUTHME_PBKDF2", 2);
/** @deprecated  */
define("AUTHME_ARGON2", 3);


if (!extension_loaded('hash')) {
    new  lauth_error("Phần mở rộng hash không hoạt động hoặc bị vô hiệu hóa", LAUTH_ERRO_ERROR);
}

if (lauth_modules_is_registered(lauth::$_MODULES, "lauth_mysql")) {
    require_once "modules/lauth_mysql.php";
}

lauth_authme_init();

/**
 * Class LAuthHash
 */
abstract class lauth_encrypt
{

    /**
     * Dãy kí tự mà muối có thể tạo
     * @var array
     */
    public $salt_chars;
    /**
     * Giá trị đầu vào của muối
     */
    private $salt_length;

    /**
     * Dùng để tạo mật khẩu hoặc kiểm tra mật khẩu của AuthMe
     * LAuthHash constructor.
     * @param int $salt_length
     */
    public function __construct($salt_length = 16)
    {
        $this->salt_chars = $this->char_range();
        $this->salt_length = $salt_length;
    }

    /**
     * Chọn dãy dữ liệu cho muối
     * @return array
     * @since 1.0
     */
    protected function char_range()
    {
        return array_merge(range('0', '9'), range('a', 'f'));
    }

    /**
     * Mã hóa mật khẩu
     * @param $password
     * @return mixed
     * @since 1.0
     */
    public abstract function hash($password);

    /**
     * So sánh xem mật khẩu có đúng với hash hay không
     * @param $password
     * @param $hash
     * @return mixed
     * @since 1.0
     */
    public abstract function compare_password($password, $hash);

    /**
     * Tạo muối cho mật khẩu đỡ nhạt =))
     * @return string
     * @since 1.0
     */
    protected function generate_salt()
    {
        $maxCharIndex = count($this->salt_chars) - 1;
        $salt = '';
        for ($i = 0; $i < $this->salt_length; ++$i) {
            $salt .= $this->salt_chars[mt_rand(0, $maxCharIndex)];
        }
        return $salt;
    }

}

class authme_sha256 extends lauth_encrypt
{

    public function __construct()
    {
        parent::__construct(16);
    }

    /**
     * Mã hóa mật khẩu
     * @param $password
     * @return mixed
     * @since 1.0
     */
    public function hash($password)
    {
        $salt = $this->generate_salt();
        return "\$SHA$" . $salt . "$" . hash("sha256", hash('sha256', $password) . $salt);
    }

    /**
     * So sánh xem mật khẩu có đúng với hash hay không
     * @param $password
     * @param $hash
     * @return mixed
     * @since 1.0
     */
    public function compare_password($password, $hash)
    {
        $parts = explode('$', $hash);
        return count($parts) === 4 && $parts[3] === hash('sha256', hash('sha256', $password) . $parts[2]);
    }
}

class  authme_bcrypt extends lauth_encrypt
{


    /**
     * Mã hóa mật khẩu
     * @param $password
     * @return mixed
     * @since 1.0
     */
    public function hash($password)
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * So sánh xem mật khẩu có đúng với hash hay không
     * @param $password
     * @param $hash
     * @return mixed
     * @since 1.0
     */
    public function compare_password($password, $hash)
    {
        return password_verify($password, $hash);
    }
}

class authme_pbkdf2 extends lauth_encrypt
{

    const ROUNDS = 10000;

    public function __construct()
    {
        parent::__construct(16);
    }

    /**
     * Mã hóa mật khẩu
     * @param $password
     * @return mixed
     * @since 1.0
     */
    public function hash($password)
    {
        $salt = $this->generate_salt();
        return $this->compute_hash(self::ROUNDS, $salt, $password);
    }

    /**
     * Tính số vòng hash
     * @param $iterations
     * @param $salt
     * @param $password
     * @return string
     * @since 1.0
     */
    private function compute_hash($iterations, $salt, $password)
    {
        return '$pbkdf2_sha256$' . $iterations . '$' . $salt
            . '$' . hash_pbkdf2('sha256', $password, $salt, self::ROUNDS, 64, false);
    }

    /**
     * So sánh xem mật khẩu có đúng với hash hay không
     * @param $password
     * @param $hash
     * @return mixed
     * @since 1.0
     */
    public function compare_password($password, $hash)
    {
        $parts = explode('$', $hash);
        return count($parts) === 4 && $hash === $this->compute_hash($parts[1], $parts[2], $password);
    }
}

class authme_argon2 extends lauth_encrypt
{

    /**
     * Mã hóa mật khẩu
     * @param $password
     * @return mixed
     * @since 1.0
     */
    public function hash($password)
    {
        return password_hash($password, PASSWORD_ARGON2I);
    }

    /**
     * So sánh xem mật khẩu có đúng với hash hay không
     * @param $password
     * @param $hash
     * @return mixed
     * @since 1.0
     */
    public function compare_password($password, $hash)
    {
        return password_verify($password, $hash);
    }
}

/**
 * Lấy object lauth_encrypt
 * @param int $type
 * @return lauth_encrypt|null
 *
 * @since 1.0
 * @deprecated
 */
function authme_objects($type = AUTHME_SHA256)
{
    $object = null;
    switch ($type) {
        case AUTHME_SHA256:
        {
            $object = new authme_sha256();
            break;
        }
        case AUTHME_BCRYPT:
        {
            $object = new authme_bcrypt();
            break;
        }
        case AUTHME_PBKDF2:
        {
            $object = new authme_pbkdf2();
            break;
        }
        case AUTHME_ARGON2:
        {
            $object = new authme_argon2();
            break;
        }
        default:
        {
            break;
        }
    }
    return $object;
}

/**
 * Trả giá trị hash
 * @param $password
 * @param int $type
 * @return mixed
 * @since 1.0
 * @deprecated
 */
function authme_hash($password, $type = AUTHME_SHA256)
{
    $object = authme_objects($type);
    return $object->hash($password);
}

/**
 * Kiểm tra mật khẩu
 * @param $password
 * @param $hash
 * @return mixed
 * @since 1.0
 */
function authme_verify_password($password, $hash)
{
    $object = null;
    $exp = explode('$', $hash);

    switch ($exp[1]) {
        case 'SHA': {$object = new authme_sha256();break;}
        case 'argon2i': {$object = new authme_argon2();break;}
        case 'pbkdf2_sha256': {$object = new authme_pbkdf2();break;}
        case '2y': {$object = new authme_bcrypt();break;}
        default: null;
    }
    return $object->compare_password($password, $hash);
}

/**
 * Khởi tạo module lauth_authme.php
 *
 * @since 1.0
 */
function lauth_authme_init()
{
    if (!lauth_is_logged()) {
        lauth_navbar_register(lauth::$_NAVBAR, "Tài khoản", ["Đăng nhập" => "signin.php", "Đăng ký" => "register.php"]);
    } else {
        lauth_navbar_register(lauth::$_NAVBAR, lauth_sessions_get(LAUTH_SESSION_LOGGED_USERNAME), ["Thông tin cá nhân" => "profile.php", "Đăng xuất" => "signout.php"]);
    }

}

/**
 * Kiểm tra xem đã đăng nhập hay chưa
 * @return bool
 * @since 1.0
 */
function lauth_is_logged()
{
    return lauth_sessions_isset(LAUTH_SESSION_LOGGED)
        && lauth_sessions_get(LAUTH_SESSION_LOGGED) == true;
}

/**
 * Kiểm tra xem bảng AuthMe đã đăng ký hay chưa
 * @param $link lauth_mysql
 * @return bool
 * @since 1.0
 */
function lauth_table_is_authme_registered($link) {
    return lauth_mysql_table_isset($link, lauth_settings_get($link, "authme_table"));
}

/**
 * Trả về giá trị là tên bảng của AuthMe
 *
 * @param $link
 * @return mixed
 * @since 1.0
 */
function lauth_authme_table ($link) {
    return lauth_settings_get($link, "authme_table");
}

/**
 * Chọn người chơi trong bảng AuthMe
 * @param $link lauth_mysql
 * @param $query string
 * @param string $where
 * @param $what string
 * @return bool|mysqli_result
 * @since 1.0
 */
function authme_get_player ($link, $query, $where =  'realname', $what = '*') {
    $table =  lauth_authme_table($link);
    $query = addslashes($query);
    return lauth_mysql_select($link, $what, $table, "`{$where}` = '{$query}'");
}

/**
 * Kiểm tra xem mật khẩu có đúng với hash trong CSDL hay không
 * @param $link
 * @param $username
 * @param $password
 * @return mixed
 * @since 1.0
 */
function lauth_password_check ($link, $username, $password) {
    $data = authme_get_player($link, $username)->fetch_assoc();
    $hash = $data['password'];
    return authme_verify_password($password, $hash);
}

/**
 * Kiểm tra xem tài khoản trên đã đăng ký chưa
 * @param $link lauth_mysql
 * @param $username string
 * @return bool
 * @since 1.0
 */
function lauth_authme_is_username_registered ($link, $username) {
    return authme_get_player($link, $username, 'realname')->num_rows > 0;
}

/**
 * Kiểm tra xem email đã đăng ký hay chưa
 * @param $link lauth_mysql
 * @param $email string
 * @return bool
 * @since 1.0
 */
function lauth_authme_is_email_registered ($link, $email) {
    return authme_get_player($link, $email, 'email')->num_rows > 0;
}
