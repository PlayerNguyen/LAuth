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

lauth_authme_init();

if (!extension_loaded('hash')) {
    new  lauth_error("Phần mở rộng hash không hoạt động hoặc bị vô hiệu hóa", LAUTH_ERRO_ERROR);
}

if (lauth_modules_is_registered(lauth::$_MODULES, "lauth_mysql")) {
    require_once "modules/lauth_mysql.php";
}

define("AUTHME_SHA256", 0);
define("AUTHME_BCRYPT", 1);
define("AUTHME_PBKDF2", 2);
define("AUTHME_ARGON2", 3);

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
        return 'pbkdf2_sha256$' . $iterations . '$' . $salt
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
 * @return authme_argon2|authme_bcrypt|authme_pbkdf2|authme_sha256|null
 * @since 1.0
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
 * @param int $type
 * @return mixed
 * @since 1.0
 */
function authme_verify($password, $hash, $type = AUTHME_SHA256)
{
    $object = authme_objects($type);
    return $object->compare_password($password, $hash);
}

/**
 *
 * @since 1.0
 */
function lauth_authme_init()
{
    if (!isset($_SESSION['_logged'])) {
        lauth_navbar_register(lauth::$_NAVBAR, "Tài khoản", ["Đăng nhập" => "login.php", "Đăng ký" => "register.php"]);
    } else {
        lauth_navbar_register(lauth::$_NAVBAR, lauth_sessions_get("lauth_logged_username"), ["Đăng xuất" => "login.php", "Đăng ký" => "register.php"]);
    }
}

/**
 * Kiểm tra xem đã đăng nhập hay chưa
 * @return bool
 * @since 1.0
 */
function lauth_is_logged()
{
    return lauth_sessions_isset("lauth_logged")
        && lauth_sessions_get("lauth_logged") == true;
}

/**
 * Kiểm tra xem bảng AuthMe đã đăng ký hay chưa
 * @since 1.0
 */
function lauth_table_is_authme_registered( ) {

}
