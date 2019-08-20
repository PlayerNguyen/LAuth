<?php
/**
 * setup.php
 * Created by Billyz (Player_Nguyen) at 4:35 CH 04/08/2019
 * Code in Lauth Project
 */
/**
 * Require the includes.php
 */
require_once "includes.php";

?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="author" content="">
    <meta name="theme-color" content="">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Thiết lập</title>

    <?php
    # Không có robot ở đây
    grobots_norobot();
    robots_norobot();
    # Load css
    css_load(LAUTH_FILE_CSS_DEFAULT);
    css_load(LAUTH_FILE_CSS_ANIMATE);
    # Load jQuery
    js_load(LAUTH_FILE_JS_JQUERY);
    js_load(LAUTH_FILE_JS_DEFAULT);
    ?>
</head>
<body>

<div class="padding-content display-block">
    <div class="container-board text-center" id="setup-title">
        <div class="display-inline animated fadeIn" style="color: #ffffff;font-weight: 800;">
            <h1 class="title-huge">LAuth</h1>
        </div>
    </div>
    <?php
    if (file_exists(LAUTH_FILE_CONFIG)) {
        display_alert("Bạn đã thiết lập máy chủ rồi, nếu bạn muốn chỉnh sửa phần này sinh hãy vào tệp <b>Config.php</b>. <a href='index.php'>Trở về trang chủ</a>.", LAUTH_ALERT_ERROR);
        return;
    }
    ?>

    <div class="container bg-white bs-border-box p-3 animated slideInUp mobile-w-100" id="setup-box">

        <?php if (isset($_POST['setup'])) {

            if (empty($_POST['server-name']) || empty($_POST['server-url']) || empty($_POST['admin-username']) || empty($_POST['mysql-host']) || empty($_POST['mysql-port']) || empty($_POST['mysql-username']) || empty($_POST['mysql-password']) || empty($_POST['mysql-database'])) {
                display_alert("Bạn chưa nhập đầy đủ thông tin, hãy chắc chắn rằng bạn nhập đủ thông tin", LAUTH_ALERT_ERROR);
            } else {
                $mysqli = @new lauth_mysql($_POST['mysql-host'], $_POST['mysql-username'], $_POST['mysql-password'], $_POST['mysql-database'], $_POST['mysql-port']);
                if ($mysqli->connect_errno != 0)
                    display_alert("Lỗi khi kết nối đến MySQL, lỗi {$mysqli->connect_error}", LAUTH_ALERT_ERROR);
                else {
                    $password_admin = salty($_POST['admin-password']);
                    $file = lauth_files_create(LAUTH_FILE_CONFIG, join("\n",
                            [
                                "<?php",
                                "define('LAUTH_SERVER_NAME',    '{$_POST['server-name']}');",
                                "define('LAUTH_SERVER_URL',     '{$_POST['server-url']}');",
                                "define('LAUTH_MYSQL_HOST',     '{$_POST['mysql-host']}');",
                                "define('LAUTH_MYSQL_PORT',     '{$_POST['mysql-port']}');",
                                "define('LAUTH_MYSQL_USERNAME', '{$_POST['mysql-username']}');",
                                "define('LAUTH_MYSQL_PASSWORD', '{$_POST['mysql-password']}');",
                                "define('LAUTH_MYSQL_DATABASE', '{$_POST['mysql-database']}');",
                                "/** Tài khoản quản trị */",
                                "define('LAUTH_ADMIN_USERNAME', '{$_POST['admin-username']}');",
                                "define('LAUTH_ADMIN_PASSWORD', '{$password_admin}');"
                            ])
                    );

                    if (!$file) {
                        display_alert("Không thể tạo hoặc ghi tệp thiết lập", LAUTH_ALERT_ERROR);
                    } else {
                        $lauth_settings = lauth_mysql_table_create($mysqli, LAUTH_TABLE_SETTINGS, [
                            "`id` INT (32) NOT NULL AUTO_INCREMENT",
                            "`key` VARCHAR(255) NOT NULL",
                            "`value` TEXT NOT NULL",
                            "`category` VARCHAR(255) NOT NULL DEFAULT '0'",
                            /** -------- */
                            "`type` VARCHAR(255) NOT NULL DEFAULT 'text'",
                            "`string_name` TEXT NOT NULL",
                            "`small_text` TEXT NOT NULL",
                            "`selection` TEXT NOT NULL DEFAULT ''",
                            "PRIMARY KEY (`id`)"
                        ]);

                        lauth_settings_default_init();

                        lauth_settings_init($mysqli);

                        if ($lauth_settings) {
                            display_alert("Thiết lập thành công. <a href='index.php'>Trở về trang chủ</a>", LAUTH_ALERT_FINE);
                            delay_redirect("index.php", 3);
                        }
                        else {
                            display_alert("Không thể tạo bảng cài đặt của LAuth, lỗi: " . lauth_mysql_last_error(lauth::$_MYSQL), LAUTH_ALERT_ERROR);
                        }
                    }
                }
            }

        } ?>

        <h1 class="title-normal c-black">Thiết lập</h1>
        <i style="<?php if (is_valid_php_version(LAUTH_PHP_VERSION_REQUEST)) echo "color:#76ecb0;"; else echo "color:#ec7676;"; ?>">Yêu
            cầu <b>PHP <?php echo LAUTH_PHP_VERSION_REQUEST ?></b> trở lên. Bạn đang ở phiên
            bản <?php echo phpversion() ?></i>
        <p>Cảm ơn bạn đã sử dụng LAuth. Để hoàn tất việc sử dụng, hãy làm đủ các bước và điền đầy đủ thông tin bên dưới.
            <b>LAuth sẽ sử dụng dữ liệu từ cơ sở dữ liệu của AuthMe</b> để hoạt động. Vì thế bạn hãy chắc chắn rằng máy
            chủ của bạn có sử dụng AuthMe.</p>
        <p>Ngoài ra LAuth còn hỗ trợ rất nhiều thứ khác nữa nhưng đó là trong tương lai. Nếu bạn muốn đóng góp tài
            nguyên cho LAuth, bạn cũng có thể phát triển module cho LAuth</p>
        <ul>
            <li><a class="bold" href="https://www.spigotmc.org/resources/authmereloaded.6269/">Tải về AuthMeReloaded</a>
            </li>
        </ul>
        <form action="" method="post" class="form mt-2" role="form">
            <div class="form-group">
                <h3>Cài đặt máy chủ</h3>
                <div class="form-group">
                    <label for="server-name">Tên máy chủ</label>
                    <input class="form-control" type="text" name="server-name" title="Tên máy chủ"
                           placeholder="Tên máy chủ..." required
                           value="<?php if (isset($_POST['server-name'])) echo $_POST['server-name']; ?>">
                </div>
                <div class="form-group">
                    <label for="server-url">Địa chỉ trang web</label>
                    <input class="form-control" type="url" name="server-url" title="Địa chỉ trang web"
                           placeholder="Địa chỉ của trang web..." required
                           value="<?php if (isset($_POST['server-url'])) $_POST['server-url']; ?>">
                    <small class="form-small-text">Địa chỉ máy chủ như http://example.com/</small>
                </div>
                <div class="form-group">
                    <label for="admin-username">Tài khoản quản trị</label>
                    <input class="form-control" type="text" name="admin-username" title="Tên tài khoản quản trị"
                           placeholder="Tên tài khoản quản trị..." required
                           value="<?php if (isset($_POST['admin-username'])) echo $_POST['admin-username']; ?>">
                </div>
                <div class="form-group">
                    <label for="admin-password">Mật khẩu quản trị</label>
                    <input class="form-control" type="password" name="admin-password" title="Mật khẩu quản trị"
                           placeholder="Mật khẩu quản trị..." required
                           value="<?php if (isset($_POST['admin-password'])) echo $_POST['admin-password']; ?>">
                </div>
            </div>
            <div class="form-group">
                <h3>Cài đặt MySQL</h3>
                <div class="form-group">
                    <label for="mysql-host" class="display-block">Địa chỉ MySQL</label>
                    <div class="form-inline">
                        <input class="form-control" type="text" name="mysql-host" title="Địa chỉ MySQL"
                               placeholder="localhost..." required
                               value="<?php if (isset($_POST['mysql-host'])) $_POST['mysql-host']; else echo "localhost"; ?>"
                               style="width: 70%;">
                        <input class="form-control" type="text" name="mysql-port" title="Cổng MySQL" placeholder="Cổng"
                               required
                               value="<?php if (isset($_POST['mysql-port'])) $_POST['mysql-port']; else echo "3306" ?>"
                               style="width: 30%">
                    </div
                </div>
                <div class="form-group">
                    <label for="mysql-username">Tài khoản MySQL</label>
                    <input class="form-control" type="text" name="mysql-username" title="Tài khoản MySQL"
                           placeholder="root..." required
                           value="<?php if (isset($_POST['mysql-username'])) echo $_POST['mysql-username']; ?>">
                </div>
                <div class="form-group">
                    <label for="mysql-password">Mật khẩu MySQL</label>
                    <input class="form-control" type="password" name="mysql-password" title="Mật khẩu MySQL"
                           placeholder="Mật khẩu MySQL..." required>
                </div>
                <div class="form-group">
                    <label for="mysql-database">Tên cơ sở dữ liệu MySQL</label>
                    <input class="form-control" type="text" name="mysql-database" title="Tên CSDL MySQL"
                           placeholder="Tên CSDL MySQL..." required
                           value="<?php if (isset($_POST['mysql-database'])) $_POST['mysql-database']; ?>">
                </div>
            </div>
            <p>Bằng việc bấm "Thiết lập", bạn đã đồng ý với <a href="license.php">điều khoản sử dụng</a> của LAuth</p>
            <div class="form-group rtl">
                <button type="submit" class="btn btn-primary" name="setup">Thiết lập</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>