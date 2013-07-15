<?php

	require_once('config.php');
	
	session_start();
	
	/*
	エスケープ処理の為の関数
	*/
	function h($s){
		return htmlspecialchars($s, ENT_QUOTES,"UTF-8");
	}
	
	// ログインチェック
	/*
	 sessionの中身を見てuserがsetされていなければ
	 login.phpに飛ばす
	*/
	if (empty($_SESSION['user'])) {
		header('Location: '.SITE_URL.'login.php');
		exit;
	}
	
	// 友達情報の取得
	$url = "https://graph.facebook.com/me/friends?access_token=".$_SESSION['user']['facebook_access_token'];
	$friends = json_decode(file_get_contents($url));
	
	//test code
	/*
	var_dump($friends);
	exit;
	*/
?>

<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>Facebook Friends</title>
    </head>
    <body>
        <h1>Facebook Friends</h1>
        <div>
            <!-- 自分のプロフィール画像を表示 -->
            <img src="<?php echo h($_SESSION['user']['facebook_picture']); ?>">
        </div>
        <p><?php echo h($_SESSION['user']['facebook_name']); ?>としてログインしています。 <a href="logout.php">[logout]</a></p>
        <ul>
            <?php foreach ($friends->data as $friend) : ?>
                <li><?php echo h($friend->name); ?></li>
            <?php endforeach; ?>
        </ul>
    </body>
</html>