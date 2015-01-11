<?php

function scriptInit() {
	try {
		if (file_exists("/var/www/isay/config.php"))
			require_once "/var/www/isay/config.php";
		else
			die("系統發生錯誤。 Error:002");
		return isset($pdo) ? $pdo : $pdo = new PDO($dsn, $account, $passwd);
	} catch(PDOException $e) {
		die("系統發生錯誤。 Error:003");
	}
}

function logout() {
	if (!isset($_SESSION))
		session_start();
	unset($_SESSION['superUser']);
	return TRUE;
}

function login($post) {
	if (!$post['account'])
		return Array('status' => FALSE, 'content' => "帳號不可為空。");
	else if (!$post['password'])
		return Array('status' => FALSE, 'content' => "密碼不可為空。");

	$sql = 'SELECT * FROM member WHERE account = ? AND passwd = ? AND type = 100';
	$pds = scriptInit() -> prepare($sql);
	$pds -> execute(Array($post['account'], md5($post['password'])));

	if ($pds -> fetch(PDO::FETCH_ASSOC)) {
		if (!isset($_SESSION))
			session_start();
		$_SESSION['superUser'] = $post['account'];
		return Array('status' => TRUE);
	} else
		return Array('status' => FALSE, 'content' => "帳號或密碼錯誤。");
}
?>