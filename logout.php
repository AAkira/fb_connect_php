<?php 

require_once('config.php');

session_start();

// sessionの中身を消す作業
$_SESSION = array();

# sessionのcookieを過去に変更することで削除
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-86400, '/fb_connect_php/');
}

session_destroy();

# indexに戻す
header('Location: '.SITE_URL);


