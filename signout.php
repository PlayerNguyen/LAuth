<?php
/**
 * signout.php
 * Created by Billyz (Player_Nguyen) at 9:23 CH 10/08/2019
 * Code in Lauth Project
 */

require_once "includes.php";


lauth_sessions_set(LAUTH_SESSION_LOGGED,            null);
lauth_sessions_set(LAUTH_SESSION_LOGGED_USERNAME,   null);
redirect(LAUTH_SERVER_URL);