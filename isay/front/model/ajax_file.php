<?php

function upload_image($file) {
	file_exists("../model/auth.php") ?
	require_once "../model/auth.php" : die("系統發生錯誤。");
	file_exists("../../config.php") ?
	require_once "../../config.php" : die("系統發生錯誤。");
	$file_size = 0;
	/* check image's format */
	for ($i = 0; $i < sizeof($file['image']['type']); $i++) {
		if (!in_array($file['image']['type'][$i], $image_formats)) {
			$responce = Array('status' => false, 'content' => "照片格式限於：jpg, png, gif, bmp, jpeg");
			return ($responce);
		}
		$file_size += $file['image']['size'][$i];
	}

	if (!isset($_SESSION))
		session_start();

	file_exists("../model/connectDB.php") ?
	require_once "../model/connectDB.php" : die("系統發生錯誤。");
	$pdo = connectDB($dsn, $account, $passwd);
	$sql = 'SELECT id, capacity FROM member, user where account = ? and id = me_id';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array($_SESSION['account']));
	$row = $pds -> fetch(PDO::FETCH_ASSOC);
	$me_id = $row['id'];
	/* check user's quota */
	if ($row['capacity'] < number_format($file_size / 1048576, 2)) {
		$responce = Array('status' => false, 'content' => "空間不足。剩餘空間：" . $row['capacity'] . "MB");
		return ($responce);
	} else {
		/* upload file => set db's quota & insert image table */
		date_default_timezone_set('Asia/Taipei');
		$datetime = new DateTime();
		$datetime = $datetime -> format('Y-m-d H:i:s');

		$array_responce = Array();
		for ($i = 0; $i < sizeof($file['image']['tmp_name']); $i++) {
			$datatype = split("/", $file['image']['type'][$i]);
			$tmp_name = md5($file['image']['name'][$i] . $datetime) . "." . $datatype[1];
			if (move_uploaded_file($file['image']['tmp_name'][$i], FILE_PATH . $_SESSION['account'] . "/" . $tmp_name)) {
				/* insert image */
				$sql = 'INSERT INTO image(path, name, me_id) VALUES(?, ?, ?)';
				$pds = $pdo -> prepare($sql);
				$pds -> execute(Array($tmp_name, $file['image']['name'][$i], $me_id));
				/* set capacity */
				$sql = 'UPDATE user SET capacity = ? WHERE me_id = ?';
				$pds = $pdo -> prepare($sql);
				$pds -> execute(Array($row['capacity'] - number_format($file_size / 1048576, 2), $me_id));
				$img_name = split("\.", $file['image']['name'][$i]);
				$array_responce[$i] = Array('src' => SERVER . 'file/' . $_SESSION['account'] . '/' . $tmp_name, 'name' => $img_name[0], 'tmp_name' => $tmp_name);
			}
		}
		return Array('status' => true, 'content' => $array_responce);
	}
}

function get_imginfo($tmp_name) {
	file_exists("../../config.php") ?
	require_once "../../config.php" : die("系統發生錯誤。");

	if (!isset($_SESSION))
		session_start();

	file_exists("../model/connectDB.php") ?
	require_once "../model/connectDB.php" : die("系統發生錯誤。");
	$pdo = connectDB($dsn, $account, $passwd);

	$sql = 'SELECT name, content FROM image, member WHERE path = ? AND me_id = id AND account = ?';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array($tmp_name, $_SESSION['account']));
	$row = $pds -> fetch(PDO::FETCH_ASSOC);

	return Array('name' => $row['name'], 'content' => $row['content']);
}

function set_imginfo($tmp_name, $name, $content) {
	file_exists("../model/auth.php") ?
	require_once "../model/auth.php" : die("系統發生錯誤。");
	if ($name == '請輸入相片名稱。               ' || $name == null)
		return Array('status' => FALSE, 'content' => '名稱不可為空。');
	if ($content == '請輸入相片敘述。               ')
		$content = NULL;
	else
		$content = htmlspecialchars($content);

	file_exists("../../config.php") ?
	require_once "../../config.php" : die("系統發生錯誤。");

	if (!isset($_SESSION))
		session_start();

	file_exists("../model/connectDB.php") ?
	require_once "../model/connectDB.php" : die("系統發生錯誤。");
	$pdo = connectDB($dsn, $account, $passwd);

	$sql = 'UPDATE image SET name = ?, content = ? WHERE path = ? AND me_id = (SELECT id FROM member WHERE account = ?)';
	$pds = $pdo -> prepare($sql);

	$pds -> execute(Array(htmlspecialchars($name), $content, htmlspecialchars($tmp_name), $_SESSION['account']));
	$row = $pds -> fetch(PDO::FETCH_ASSOC);

	return Array('status' => true);
}

function upload_music($file) {
	file_exists("../model/auth.php") ?
	require_once "../model/auth.php" : die("系統發生錯誤。");
	file_exists("../../config.php") ?
	require_once "../../config.php" : die("系統發生錯誤。");

	/* check image's format */
	if (!in_array($file['music']['type'], $music_formats)) {
		$responce = Array('status' => false, 'content' => "音樂格式限於：mp3");
		return ($responce);
	}

	if (!isset($_SESSION))
		session_start();

	file_exists("../model/connectDB.php") ?
	require_once "../model/connectDB.php" : die("系統發生錯誤。");
	$pdo = connectDB($dsn, $account, $passwd);
	$sql = 'SELECT id, capacity FROM member, user where account = ? and id = me_id';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array($_SESSION['account']));
	$row = $pds -> fetch(PDO::FETCH_ASSOC);
	$me_id = $row['id'];
	/* check user's quota */
	if ($row['capacity'] < number_format($file['music']['size'] / 1048576, 2)) {
		$responce = Array('status' => false, 'content' => "空間不足。剩餘空間：" . $row['capacity'] . "MB");
		return ($responce);
	} else {
		/* upload file => set db's quota & insert music table */
		date_default_timezone_set('Asia/Taipei');
		$datetime = new DateTime();
		$datetime = $datetime -> format('Y-m-d H:i:s');

		$array_responce = Array();

		$datatype = split("/", $file['music']['type']);
		$tmp_name = md5($file['music']['name'] . $datetime) . "." . $datatype[1];

		if (move_uploaded_file($file['music']['tmp_name'], FILE_PATH . $_SESSION['account'] . "/" . $tmp_name)) {
			/* insert image */
			$sql = 'INSERT INTO music VALUES(?, ?, ?)';
			$pds = $pdo -> prepare($sql);
			$pds -> execute(Array($tmp_name, $file['music']['name'], $me_id));
			/* set capacity */
			$sql = 'UPDATE user SET capacity = ? WHERE me_id = ?';
			$pds = $pdo -> prepare($sql);
			$pds -> execute(Array($row['capacity'] - number_format($file['music']['size'] / 1048576, 2), $me_id));
			$music_name = split("\.", $file['music']['name']);
			$array_responce = Array('src' => SERVER . 'file/' . $_SESSION['account'] . '/' . $tmp_name, 'name' => $music_name[0], 'tmp_name' => $tmp_name);
		}
		return Array('status' => true, 'content' => $array_responce);
	}
}

function delete_file($file, $table_name, $dsn = null, $account = null, $passwd = null) {
	file_exists("../model/auth.php") ?
	require_once "../model/auth.php" : die("系統發生錯誤。");
	file_exists("../../config.php") ?
	require_once "../../config.php" : die("系統發生錯誤。");

	if (!isset($_SESSION))
		session_start();

	file_exists("../model/connectDB.php") ?
	require_once "../model/connectDB.php" : die("系統發生錯誤。");
	$pdo = connectDB($dsn, $account, $passwd);

	$sql = 'SELECT capacity, id FROM user, member WHERE account = ? and id = me_id';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array($_SESSION['account']));
	$row = $pds -> fetch(PDO::FETCH_ASSOC);

	$sql = 'UPDATE user SET capacity = ? WHERE me_id = ?';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array($row['capacity'] + number_format(filesize(FILE_PATH . $_SESSION['account'] . "/" . $file) / 1048576, 2), $row['id']));

	$sql = 'DELETE FROM ' . $table_name . ' WHERE path = ? and me_id = ?';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array($file, $row['id']));

	return unlink(FILE_PATH . $_SESSION['account'] . "/" . $file);
}

function uploadHeader($file) {
	file_exists("../model/auth.php") ?
	require_once "../model/auth.php" : die("系統發生錯誤。");
	file_exists("../../config.php") ?
	require_once "../../config.php" : die("系統發生錯誤。");

	/* check image's format */
	if (!in_array($file['headerImg']['type'], $image_formats)) {
		$responce = Array('status' => false, 'content' => "圖片格式限於：jpg, png, gif, bmp, jpeg");
		return ($responce);
	}

	if (!isset($_SESSION))
		session_start();

	file_exists("../model/connectDB.php") ?
	require_once "../model/connectDB.php" : die("系統發生錯誤。");
	$pdo = connectDB($dsn, $account, $passwd);
	$sql = 'SELECT id, capacity, headerImg FROM member, user where account = ? and id = me_id';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array($_SESSION['account']));
	$row = $pds -> fetch(PDO::FETCH_ASSOC);
	$me_id = $row['id'];

	if ($row['headerImg'] != NULL)
		delete_file($row['headerImg'], "image", $dsn, $account, $passwd);

	/* check user's quota */
	if ($row['capacity'] < number_format($file['headerImg']['size'] / 1048576, 2)) {
		$responce = Array('status' => false, 'content' => "空間不足。剩餘空間：" . $row['capacity'] . "MB");
		return ($responce);
	} else {
		date_default_timezone_set('Asia/Taipei');
		$datetime = new DateTime();
		$datetime = $datetime -> format('Y-m-d H:i:s');

		$datatype = split("/", $file['headerImg']['type']);
		$tmp_name = md5($file['headerImg']['name'] . $datetime) . "." . $datatype[1];

		if (move_uploaded_file($file['headerImg']['tmp_name'], FILE_PATH . $_SESSION['account'] . "/" . $tmp_name)) {
			/* insert image */
			$sql = 'INSERT INTO image(path, name, me_id) VALUES(?, ?, ?)';
			$pds = $pdo -> prepare($sql);
			$pds -> execute(Array($tmp_name, $file['headerImg']['name'], $me_id));
			/* set capacity & headerImg */
			$sql = 'UPDATE user SET capacity = ?, headerImg = ? WHERE me_id = ?';
			$pds = $pdo -> prepare($sql);
			$pds -> execute(Array($row['capacity'] - number_format($file['headerImg']['size'] / 1048576, 2), $tmp_name, $me_id));
		}

		$sql = 'INSERT INTO diary(title, content, place, weather, time, permission, music_path, me_id) VALUES("?", "?", "?", "?", "2012-08-24 17:18:34", "?", "?", (SELECT id FROM member WHERE account = "peace0805"))';
		$pds = $pdo -> prepare($sql);
		$pds -> execute();
		return Array('status' => TRUE, 'content' => SERVER . 'file/' . $_SESSION['account'] . '/' . $tmp_name);
	}
}

function headerImgRemove($_account) {
	file_exists("../model/auth.php") ?
	require_once "../model/auth.php" : die("系統發生錯誤。");
	file_exists("../model/connectDB.php") ?
	require_once "../model/connectDB.php" : die("系統發生錯誤。");
	$pdo = connectDB();
	$sql = 'SELECT headerImg FROM user, member WHERE me_id = (SELECT id FROM member WHERE account = ?)';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array($_account));
	$row = $pds -> fetch(PDO::FETCH_ASSOC);

	delete_file($row['headerImg'], "image");

	$sql = 'UPDATE user SET headerImg = NULL WHERE me_id = (SELECT id FROM member WHERE account = ?)';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array($_account));

	return Array('status' => TRUE);
}

?>