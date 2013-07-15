<?php

define('DB_HOST', 'localhost');
define('DB_USER', '(database user name)');
define('DB_PASSWORD', '(database password)');
define('DB_NAME', '(database name)');

define('APP_ID','(取得したAPP_ID)');
define('APP_SECRET','(取得したAPP_SECRET)');

define('SITE_URL', '(hostに登録したlocalのアドレス ex:http://local.fbconnect.com/)');

//error表示関係 決まり文句 notice以外は表示
error_reporting(E_ALL & ~E_NOTICE);

/*sessionの有効期限 0->ブラウザを閉じるまで
  fb_connect_php/の間ではsessionが有効になる
*/
session_set_cookie_params(0, '(sessionで使うアドレスのpath ex:/)');

?>

