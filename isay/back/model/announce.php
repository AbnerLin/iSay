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

function loadAnnounce() {
	$sql = 'SELECT id, content, time FROM announce ORDER BY time';
	$pds = scriptInit() -> prepare($sql);
	$pds -> execute();
	$row = $pds -> fetchAll(PDO::FETCH_ASSOC);

	if ($row)
		return Array('status' => TRUE, 'content' => $row);
	else
		return Array('status' => FALSE);
}
?>