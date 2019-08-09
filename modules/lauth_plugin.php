<?php
/**
 * lauth_plugin.php
 * Created by Billyz (Player_Nguyen) at 4:41 CH 04/08/2019
 * Code in Lauth Project
 */

lauth_modules_register(lauth::$_MODULES, "lauth_plugin", basename(__FILE__));

/**
 *
 * Interface Plugin
 * @since 1.0
 */
interface Plugin
{

    public function name();

    public function version();

    public function author();

}

abstract class Listener
{
    public abstract function callEvent();
}