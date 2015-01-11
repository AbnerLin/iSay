<?php
function connectDB($dsn = "", $account = "", $passwd = "") {
	global $pdo;
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
?>