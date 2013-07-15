<?php
/*
* このスクリプトで実際にfacebookとの処理を行なっていく
*/


require_once('config.php');

session_start();

//認証が終わると'code'に値が返ってくる -> elseに入る
if (empty($_GET['code'])) {
    // 認証の準備
    // facebookに一旦飛ばす
	
	//決まり文句
	$_SESSION['state'] = sha1(uniqid(mt_rand(), true));
	
	
	# @client_id			configで定義したAPP_ID
	# @param redirect_uri	このファイル名
	# @state				上記のstateをつっこむ
	# @scope				公式サイトに色々と載っかている
	#					今回はuser_website,friends_websiteをひっぱってくる
	$params = array(
		'client_id' => APP_ID,
		'redirect_uri' => SITE_URL.'redirect.php',
		'state' => $_SESSION['state'],
		'scope' => 'user_website,friends_website'
	);
	$url = "https://www.facebook.com/dialog/oauth?".http_build_query($params); 
	
	// facebookに一旦飛ばす
	header('Location: '.$url);
	exit;

}else {// 認証後の処理
		
	// CSRF対策
	#	保存していたstateがGETで返ってきた値と比較して合っているか？
	if ($_SESSION['state'] != $_GET['state']) {
		echo "Error!  Illegal operation";
		exit;
	}
	// ユーザー情報の取得
	# @code				getに入っている
	# @redirect_uri		今回のファイル名と同じ
	$params = array(
		'client_id' => APP_ID,
		'client_secret' => APP_SECRET,
		'code' => $_GET['code'],
		'redirect_uri' => SITE_URL.'redirect.php'
	);
	$url = 'https://graph.facebook.com/oauth/access_token?'.http_build_query($params);
	
	# urlにアクセスして中身を引っ張ってくる
	$body = file_get_contents($url);
	# 変数と値のペアにする
	parse_str($body);
	# ここでAccess tokenが取得できる
	$url = 'https://graph.facebook.com/me?access_token='.$access_token.'&fields=name,picture';
	# urlにアクセスした結果をfile_get_contentsにとってきて
	# json_decodeでJSON形式で取得可能
	$me = json_decode(file_get_contents($url));
	
	
	//動作確認
	/*
	var_dump($me);
	exit;
	*/

    // DB処理
	try {
		$dbh = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASSWORD);
	} catch (PDOException $e) {
		#例外が出たらメッセージを表示
		echo $e->getMessage();
		exit;
	}
	# 既にその情報があるかどうかを調べる
	$stmt = $dbh->prepare("select * from users where facebook_user_id=:user_id limit 1");
	# user idが meのidに入っている
	$stmt->execute(array(":user_id"=>$me->id));
	# user情報取得
	$user = $stmt->fetch();
	 
	# userが取得出来ない -> user情報をdatabaseに保存する
	if (empty($user)) {
		$stmt = $dbh->prepare("insert into users (facebook_user_id, facebook_name, facebook_picture, facebook_access_token, created, modified) values (:user_id, :name, :picture, :access_token, now(), now());");
		# executeに入れる配列の作成		
		$params = array(
			":user_id"=>$me->id,
			":name"=>$me->name,
			":picture"=>$me->picture->data->url,
			":access_token"=>$access_token
		);
		$stmt->execute($params);
		
		#上記同様databaseに入れたらuserに値を入れる
		$stmt = $dbh->prepare("select * from users where facebook_user_id=:last_insert_id limit 1");
		# database最後に挿入
		$stmt->execute(array(":last_insert_id"=>$dbh->lastInsertId()));
		$user = $stmt->fetch(); 
	}

	// 動作確認	
	/*
	var_dump($user);
	exit;
	*/

    // ログイン処理
	# userの中身があるか確認
	if (!empty($user)) {
		# Session Hijack対策
		session_regenerate_id(true);
		# sessionにuserを入れる
		$_SESSION['user'] = $user;
	}
	
    // index.phpに飛ばす	
	header('Location: '.SITE_URL);
}

