<?php
function loadInfo($_account) {
	file_exists("../model/connectDB.php") ?
	require_once "../model/connectDB.php" : die("系統發生錯誤。");
	$pdo = connectDB();
	if (!isset($_SESSION))
		session_start();
	file_exists("../../config.php") ?
	require_once "../../config.php" : die("系統發生錯誤。");

	$sql = 'SELECT username, account, email, datapermission FROM member WHERE account = ?';
	$pds = $pdo -> prepare($sql);	$pds -> execute(Array($_account));	$memberRow = $pds -> fetch(PDO::FETCH_ASSOC);
	$memberInfo = Array();
	$memberInfo['username'] = $memberRow['username'];
	if ((isset($_SESSION['account']) && $_SESSION['account'] != $_account) || !isset($_SESSION['account'])) {
		if ($memberRow['datapermission'] != "0")
			$memberInfo['email'] = $memberRow['email'];
	} else
		$memberInfo = $memberRow;

	$sql = 'SELECT birthday, gender, info, membership, capacity, id_number, bigHeadImg, datapermission FROM user WHERE me_id = (SELECT id FROM member WHERE account = ?)';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array($_account));
	$userRow = $pds -> fetch(PDO::FETCH_ASSOC);

	$userInfo = Array();
	if ($userRow['bigHeadImg'])
		$userRow['bigHeadImg'] = $userInfo['bigHeadImg'] = SERVER . "file/" . $_account . "/" . $userRow['bigHeadImg'];
	if ((isset($_SESSION['account']) && $_SESSION['account'] != $_account) || !isset($_SESSION['account'])) {
		if ($userRow['datapermission'][0] != "0" && $userRow['birthday'])
			$userInfo['birthday'] = $userRow['birthday'];
		if ($userRow['datapermission'][1] != "0" && $userRow['gender'])
			$userInfo['gender'] = $userRow['gender'];
		if ($userRow['datapermission'][2] != "0" && $userRow['info'])
			$userInfo['info'] = $userRow['info'];
	} else
		$userInfo = $userRow;

	$data = $userRow['datapermission'] . $memberRow['datapermission'];
	unset($userInfo['datapermission']);
	unset($memberInfo['datapermission']);

	if ($memberRow)
		return Array('status' => TRUE, 'content' => array_merge($memberInfo, $userInfo), 'data' => $data, 'isUser' => (isset($_SESSION['account']) && $_SESSION['account'] == $_account) ? TRUE : FALSE);
	else
		return Array('status' => FALSE);

}

function UsernameEdit($username) {
	file_exists("../model/auth.php") ?
	require_once "../model/auth.php" : die("系統發生錯誤。");
	file_exists("../model/connectDB.php") ?
	require_once "../model/connectDB.php" : die("系統發生錯誤。");
	$pdo = connectDB();
	if (!isset($_SESSION))
		session_start();
	if($username == "輸入新暱稱              ")
		return FALSE;

	$sql = 'UPDATE member SET username = ? WHERE account = ?';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array($username, $_SESSION['account']));

	return TRUE;
}

function PasswdEdit($old, $new, $again) {
	file_exists("../model/auth.php") ?
	require_once "../model/auth.php" : die("系統發生錯誤。");
	if ($new != $again)
		return Array('status' => FALSE, 'content' => "密碼不成對。");
	else if (strlen($new) < 8)
		return Array('status' => FALSE, 'content' => "密碼不可少於 8。");
	file_exists("../model/connectDB.php") ?
	require_once "../model/connectDB.php" : die("系統發生錯誤。");
	$pdo = connectDB();
	if (!isset($_SESSION))
		session_start();

	$sql = 'SELECT * FROM member WHERE passwd = ? AND account = ?';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array(md5($old), $_SESSION['account']));
	$row = $pds -> fetch(PDO::FETCH_ASSOC);
	if (!$row)
		return Array('status' => FALSE, 'content' => "密碼不正確。");

	$sql = 'UPDATE member SET passwd = ? WHERE account = ?';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array(md5($new), $_SESSION['account']));
	return Array('status' => TRUE, 'content' => "密碼更改成功。");
}

function birthEdit($date, $permission) {
	file_exists("../model/auth.php") ?
	require_once "../model/auth.php" : die("系統發生錯誤。");
	file_exists("../model/connectDB.php") ?
	require_once "../model/connectDB.php" : die("系統發生錯誤。");
	$pdo = connectDB();
	if (!isset($_SESSION))
		session_start();

	$sql = 'SELECT datapermission FROM user WHERE me_id = (SELECT id FROM member WHERE account = ?)';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array($_SESSION['account']));
	$datapermission = $pds -> fetch(PDO::FETCH_ASSOC);

	$datapermission['datapermission'][0] = $permission;

	$sql = 'UPDATE user SET datapermission = ?, birthday = ? WHERE me_id = (SELECT id FROM member WHERE account = ?)';
	$pds = $pdo -> prepare($sql);
	if ($date == "null")
		$date = NULL;
	$pds -> execute(Array($datapermission['datapermission'], $date, $_SESSION['account']));

	return TRUE;
}

function genderEdit($gender, $permission) {
	file_exists("../model/auth.php") ?
	require_once "../model/auth.php" : die("系統發生錯誤。");
	file_exists("../model/connectDB.php") ?
	require_once "../model/connectDB.php" : die("系統發生錯誤。");
	$pdo = connectDB();
	if (!isset($_SESSION))
		session_start();

	$sql = 'SELECT datapermission FROM user WHERE me_id = (SELECT id FROM member WHERE account = ?)';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array($_SESSION['account']));
	$datapermission = $pds -> fetch(PDO::FETCH_ASSOC);

	$datapermission['datapermission'][1] = $permission;

	$sql = 'UPDATE user SET datapermission = ?, gender = ? WHERE me_id = (SELECT id FROM member WHERE account = ?)';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array($datapermission['datapermission'], $gender, $_SESSION['account']));

	return TRUE;
}

function infoEdit($info, $permission) {
	file_exists("../model/auth.php") ?
	require_once "../model/auth.php" : die("系統發生錯誤。");
	file_exists("../model/connectDB.php") ?
	require_once "../model/connectDB.php" : die("系統發生錯誤。");
	$pdo = connectDB();
	if (!isset($_SESSION))
		session_start();

	$sql = 'SELECT datapermission FROM user WHERE me_id = (SELECT id FROM member WHERE account = ?)';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array($_SESSION['account']));
	$datapermission = $pds -> fetch(PDO::FETCH_ASSOC);

	$datapermission['datapermission'][2] = $permission;
	if ($info == "關於我 ...               ")
		$info = NULL;
	else 
		$info = htmlspecialchars($info);
	

	$sql = 'UPDATE user SET datapermission = ?, info = ? WHERE me_id = (SELECT id FROM member WHERE account = ?)';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array($datapermission['datapermission'], $info, $_SESSION['account']));

	return TRUE;
}

function emailEdit($permission) {
	file_exists("../model/auth.php") ?
	require_once "../model/auth.php" : die("系統發生錯誤。");
	file_exists("../model/connectDB.php") ?
	require_once "../model/connectDB.php" : die("系統發生錯誤。");
	$pdo = connectDB();
	if (!isset($_SESSION))
		session_start();

	$sql = 'UPDATE member SET datapermission = ? WHERE account = ?';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array($permission, $_SESSION['account']));

	return TRUE;
}

function bigHeadEdit($file) {
	file_exists("../model/auth.php") ?
	require_once "../model/auth.php" : die("系統發生錯誤。");
	file_exists("../../config.php") ?
	require_once "../../config.php" : die("系統發生錯誤。");
	file_exists("../model/connectDB.php") ?
	require_once "../model/connectDB.php" : die("系統發生錯誤。");
	$pdo = connectDB($dsn, $account, $passwd);
	if (!isset($_SESSION))
		session_start();

	/* check image's format */
	if (!in_array($file['bigHead']['type'], $image_formats)) {
		$responce = Array('status' => false, 'content' => "照片格式限於：jpg, png, gif, bmp, jpeg");
		return ($responce);
	}

	$sql = 'SELECT capacity FROM user WHERE me_id = (SELECT id FROM member WHERE account = ?)';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array($_SESSION['account']));
	$row = $pds -> fetch(PDO::FETCH_ASSOC);

	if ($row['capacity'] < number_format($file['bigHead']['size'] / 1048576, 2)) {
		$responce = Array('status' => false, 'content' => "空間不足。剩餘空間：" . $row['capacity'] . "MB");
		return ($responce);
	} else {
		date_default_timezone_set('Asia/Taipei');
		$datetime = new DateTime();
		$datetime = $datetime -> format('Y-m-d H:i:s');

		$array_responce = Array();

		$datatype = split("/", $file['bigHead']['type']);
		$tmp_name = md5($file['bigHead']['name'] . $datetime) . "." . $datatype[1];

		if (move_uploaded_file($file['bigHead']['tmp_name'], FILE_PATH . $_SESSION['account'] . "/" . $tmp_name)) {
			/* insert image */
			$sql = 'INSERT INTO image(path, name, me_id) VALUES(?, ?, (SELECT id FROM member WHERE account = ?))';
			$pds = $pdo -> prepare($sql);
			$pds -> execute(Array($tmp_name, $file['bigHead']['name'], $_SESSION['account']));
			/* update bigHead */
			$sql = 'SELECT bigHeadImg FROM user WHERE me_id = (SELECT id FROM member WHERE account = ?)';
			$pds = $pdo -> prepare($sql);
			$pds -> execute(Array($_SESSION['account']));
			$prevImg = $pds -> fetch(PDO::FETCH_ASSOC);
			
			$sql = 'DELETE FROM image WHERE path = ? AND me_id = (SELECT id FROM member WHERE account = ?)';
			$pds = $pdo -> prepare($sql);
			$pds -> execute(Array($prevImg['bigHeadImg'], $_SESSION['account']));

			$sql = 'UPDATE user SET bigHeadImg = ? WHERE me_id = (SELECT id FROM member WHERE account = ?)';
			$pds = $pdo -> prepare($sql);
			$pds -> execute(Array($tmp_name, $_SESSION['account']));
			/* set capacity */
			$sql = 'UPDATE user SET capacity = ? WHERE me_id = (SELECT id FROM member WHERE account = ?)';
			$pds = $pdo -> prepare($sql);
			$pds -> execute(Array($row['capacity'] - number_format($file['bigHead']['size'] / 1048576, 2) + number_format(filesize(FILE_PATH . $_SESSION['account'] . "/" . $prevImg['bigHeadImg']) / 1048576, 2), $_SESSION['account']));
			$array_responce = Array('src' => SERVER . 'file/' . $_SESSION['account'] . '/' . $tmp_name);
			unlink(FILE_PATH . $_SESSION['account'] . "/" . $prevImg['bigHeadImg']);
		}
		return Array('status' => TRUE, 'content' => $array_responce);
	}
}
?>