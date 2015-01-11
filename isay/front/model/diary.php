<?php

function insert_diary($title, $content, $geocoder, $image, $music, $permission) {
	file_exists("../model/auth.php") ?
	require_once "../model/auth.php" : die("系統發生錯誤。");
	if ($title == 'Title              ')
		return Array('status' => false, 'content' => "標題不可為空。");
	else
		$title = htmlspecialchars($title);
	if ($content == "Say Something ...               ")
		$content = NULL;
	else
		$content = htmlspecialchars($content);
	if ($geocoder == "隱藏所在位置。               " || $geocoder == "定位中...               " || $geocoder == "無法取得位置。               " || $geocoder == "請輸入所在位置。               ")
		$geocoder = NULL;
	else
		$geocoder = htmlspecialchars($geocoder);

	file_exists("../model/connectDB.php") ?
	require_once "../model/connectDB.php" : die("系統發生錯誤。");

	$pdo = connectDB();
	if (!isset($_SESSION))
		session_start();
	date_default_timezone_set('Asia/Taipei');
	$datetime = new DateTime();
	$datetime = $datetime -> format('Y-m-d H:i:s');

	if ($music == "null")
		$music = NULL;

	$sql = 'INSERT INTO diary(title, content, place, time, permission, music_path, me_id) VALUES(?, ?, ?, ?, ?, ?, (SELECT id FROM member WHERE account = ?))';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array($title, $content, $geocoder, $datetime, $permission, $music, $_SESSION['account']));

	$diary_id = $pdo -> lastInsertId();

	if ($image != "null") {
		$sql = 'INSERT INTO diary_img(d_id, i_path) VALUES(?, ?)';
		for ($i = 0; $i < sizeof($image); $i++) {
			$pds = $pdo -> prepare($sql);
			$pds -> execute(Array($diary_id, $image[$i]));
		}
	}

	return Array('status' => true);
}

function select_diary($account, $limit) {
	file_exists("../model/connectDB.php") ?
	require_once "../model/connectDB.php" : die("系統發生錯誤。");
	$pdo = connectDB();

	$permission = permission($account);
	// $permission = 2;
	session_write_close();
	$sql = 'SELECT id, permission, title, content, place, time FROM diary WHERE me_id = (SELECT id FROM member WHERE account = :account) AND permission >= :permission  ORDER BY time DESC LIMIT :limit, 5';
	$pds = $pdo -> prepare($sql);
	$pds -> bindParam(':account', $account, PDO::PARAM_STR);
	$pds -> bindParam(':permission', $permission, PDO::PARAM_INT);
	$limit = (int)$limit;
	$pds -> bindParam(':limit', $limit, PDO::PARAM_INT);
	$pds -> execute();

	$diary = $pds -> fetchAll(PDO::FETCH_ASSOC);

	$sql = 'SELECT account, username FROM member WHERE account = ?';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array($account));
	$userInfo = $pds -> fetch(PDO::FETCH_ASSOC);

	if ($diary) {
		$diary_responce = Array();
		date_default_timezone_set('Asia/Taipei');
		for ($i = 0; $i < sizeof($diary); $i++) {
			$sql = 'SELECT * FROM diary_img WHERE d_id = ?';
			$pds = $pdo -> prepare($sql);
			$pds -> execute(Array($diary[$i]['id']));
			$img_count = $pds -> fetchAll(PDO::FETCH_ASSOC);
			$imgArray = Array();
			if (sizeof($img_count) > 0)
				for ($j = 0; $j < sizeof($img_count); $j++)
					array_push($imgArray, SERVER . "file/" . $account . "/" . $img_count[$j]['i_path']);

			$sql = 'SELECT count(d_id) FROM reply WHERE d_id = ? AND permission != -1';
			$pds = $pdo -> prepare($sql);
			$pds -> execute(Array($diary[$i]['id']));
			$reply_count = $pds -> fetch(PDO::FETCH_ASSOC);
			$diary_responce[$i] = Array('id' => $diary[$i]['id'], 'date' => date('Y/m/d', strtotime($diary[$i]['time'])), 'title' => $diary[$i]['title'], 'content' => $diary[$i]['content'], 'place' => $diary[$i]['place'], 'img_count' => sizeof($img_count), 'reply_count' => $reply_count['count(d_id)'], 'imgArray' => $imgArray);
			if ($permission == 0)				$diary_responce[$i]['permission'] = $diary[$i]['permission'];
			unset($imgArray);
		}
		return Array('status' => true, 'content' => $diary_responce, 'userInfo' => $userInfo);
	} else
		return Array('status' => false);

}

function longPolling($account) {
	file_exists("../model/connectDB.php") ?
	require_once "../model/connectDB.php" : die("系統發生錯誤。");
	$pdo = connectDB();

	$permission = permission($account);

	date_default_timezone_set('Asia/Taipei');
	$sql = 'SELECT time FROM diary ORDER BY time DESC';
	$pds = $pdo -> prepare($sql);
	$pds -> execute();
	$time = $pds -> fetch(PDO::FETCH_ASSOC);
	$prevTime = date_create($time['time']);

	$sql = 'SELECT count(id) FROM diary WHERE me_id = (SELECT id FROM member WHERE account = :account) AND permission >= :permission';
	$pds = $pdo -> prepare($sql);
	$pds -> bindParam(':account', $account, PDO::PARAM_STR);
	$pds -> bindParam(':permission', $permission, PDO::PARAM_INT);
	$pds -> execute();
	$diaryCount = $pds -> fetch(PDO::FETCH_ASSOC);

	$startTime = time();
	session_write_close();
	while ($startTime + 15 > time()) {
		$check = FALSE;
		$pds -> execute();
		$diaryCurrentCount = $pds -> fetch(PDO::FETCH_ASSOC);

		if ($diaryCount['count(id)'] < $diaryCurrentCount['count(id)']) {

			$sql = 'SELECT id, title, content, place, time FROM diary WHERE me_id = (SELECT id FROM member WHERE account = :account) AND permission >= :permission  ORDER BY time DESC';
			$pds = $pdo -> prepare($sql);
			$pds -> bindParam(':account', $account, PDO::PARAM_STR);
			$pds -> bindParam(':permission', $permission, PDO::PARAM_INT);
			$pds -> execute();
			$currentDiary = $pds -> fetchAll(PDO::FETCH_ASSOC);

			$diary_responce = Array();
			for ($i = 0; $i < $diaryCurrentCount['count(id)'] - $diaryCount['count(id)']; $i++) {
				$currentTime = date_create($currentDiary[$i]['time']);
				if (date_format($prevTime, 'Y-m-d H:i:s') < date_format($currentTime, 'Y-m-d H:i:s')) {
					$sql = 'SELECT * FROM diary_img WHERE d_id = ?';
					$pds = $pdo -> prepare($sql);
					$pds -> execute(Array($currentDiary[$i]['id']));
					$img_count = $pds -> fetchAll(PDO::FETCH_ASSOC);
					$imgArray = Array();
					if (sizeof($img_count) > 0)
						for ($j = 0; $j < sizeof($img_count); $j++)
							array_push($imgArray, SERVER . "file/" . $account . "/" . $img_count[$j]['i_path']);

					$sql = 'SELECT count(d_id) FROM reply WHERE d_id = ?';
					$pds = $pdo -> prepare($sql);
					$pds -> execute(Array($currentDiary[$i]['id']));
					$reply_count = $pds -> fetch(PDO::FETCH_ASSOC);

					$diary_responce[$i] = Array('id' => $currentDiary[$i]['id'], 'date' => date('Y/m/d', strtotime($currentDiary[$i]['time'])), 'title' => $currentDiary[$i]['title'], 'content' => $currentDiary[$i]['content'], 'place' => $currentDiary[$i]['place'], 'img_count' => sizeof($img_count), 'reply_count' => $reply_count['count(d_id)'], 'imgArray' => $imgArray);
					$check = TRUE;
					unset($imgArray);
				}
			}
			if ($check) {
				return Array('status' => TRUE, 'content' => $diary_responce);
				break;
			}
		}
		sleep(3);
	}
	return Array('status' => FALSE);
}

function permission($account) {

	file_exists("../model/connectDB.php") ?
	require_once "../model/connectDB.php" : die("系統發生錯誤。");
	$pdo = connectDB();

	if (!isset($_SESSION))
		session_start();

	if (isset($_SESSION['account']) && $_SESSION['account'] == $account) {
		return 0;
	} else if (isset($_SESSION['account'])) {
		$sql = 'SELECT * FROM friend WHERE me_id1 = (SELECT id FROM member WHERE account = ?) AND me_id2 = (SELECT id FROM member WHERE account = ?) AND permission = 1';		$pds = $pdo -> prepare($sql);		$pds -> execute(Array($account, $_SESSION['account']));		$row = $pds -> fetch(PDO::FETCH_ASSOC);
		if ($row)
			return 1;
	}
	return 2;

}

function loadDetailDiary($_account, $diaryId) {
	$cookie = (string)$diaryId;
	file_exists("../../config.php") ?
	require_once "../../config.php" : die("系統發生錯誤。");
	file_exists("../model/connectDB.php") ?
	require_once "../model/connectDB.php" : die("系統發生錯誤。");
	$pdo = connectDB($dsn, $account, $passwd);

	$permission = permission($_account);
	/* load diary */
	$sql = 'SELECT * FROM diary WHERE id = :id AND permission >= :permission AND me_id = (SELECT id FROM member WHERE account = :account)';
	$pds = $pdo -> prepare($sql);
	$diaryId = (int)$diaryId;
	$pds -> bindParam(':id', $diaryId, PDO::PARAM_INT);
	$pds -> bindParam(':permission', $permission, PDO::PARAM_INT);
	$pds -> bindParam(':account', $_account, PDO::PARAM_STR);
	$pds -> execute();	$diaryRow = $pds -> fetch(PDO::FETCH_ASSOC);

	if (!$diaryRow) {
		return Array('status' => FALSE);
	} else if (!isset($_COOKIE["diary" . $cookie])) {
		setcookie("diary" . $cookie, $cookie);
		$diaryRow['diaryCount'] = (int)$diaryRow['diaryCount'] + 1;
		$sql = 'UPDATE diary SET diaryCount = :diaryCount WHERE id = :id';
		$pds = $pdo -> prepare($sql);
		$pds -> bindParam(':id', $diaryId, PDO::PARAM_INT);
		$pds -> bindParam(':diaryCount', $diaryRow['diaryCount'], PDO::PARAM_INT);
		$pds -> execute();
	}

	/* load diary's image */
	$sql = 'SELECT * FROM diary_img WHERE d_id = ?';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array($diaryId));
	$img = $pds -> fetchAll(PDO::FETCH_ASSOC);

	/* load userInfo */
	$sql = 'SELECT account, username FROM member WHERE account = ?';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array($_account));
	$userInfo = $pds -> fetch(PDO::FETCH_ASSOC);

	/* load Reply's count */
	$sql = 'SELECT count(*) FROM reply WHERE d_id = ? AND permission != -1';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array($diaryId));
	$replyCount = $pds -> fetch(PDO::FETCH_ASSOC);

	$imgInfo = NULL;
	if ($img) {
		$tmpi_path = Array();
		for ($i = 0; $i < sizeof($img); $i++) {
			array_push($tmpi_path, "'" . $img[$i]['i_path'] . "'");
			$img[$i]['i_path'] = SERVER . 'file/' . $_account . '/' . $img[$i]['i_path'];
		}
		$i_path = implode(",", $tmpi_path);
		$sql = 'SELECT name, content FROM image WHERE path IN(' . $i_path . ')';
		$pds = $pdo -> prepare($sql);
		$pds -> execute();
		$imgInfo = $pds -> fetchAll(PDO::FETCH_ASSOC);
	}

	if ($diaryRow['music_path'])
		$diaryRow['music_path'] = SERVER . 'file/' . $_account . '/' . $diaryRow['music_path'];

	if ($permission != 0)
		unset($diaryRow['permission']);
	return $diaryRow ? Array('status' => TRUE, 'diaryContent' => $diaryRow, 'diaryImg' => $img ? $img : NULL, 'diaryImgInfo' => $imgInfo ? $imgInfo : NULL, 'userInfo' => $userInfo, 'replyCount' => $replyCount['count(*)']) : Array('status' => FALSE);
}

function diarySearch($_account, $keyWord) {
	file_exists("../model/connectDB.php") ?
	require_once "../model/connectDB.php" : die("系統發生錯誤。");
	$pdo = connectDB();
	$permission = (int)permission($_account);

	$sql = 'SELECT id, permission, title, content, place, time FROM diary WHERE (title LIKE ? or content LIKE ?) AND permission >= ? AND me_id = (SELECT id FROM member WHERE account = ?)';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array("%$keyWord%", "%$keyWord%", $permission, $_account));
	$diary = $pds -> fetchAll(PDO::FETCH_ASSOC);

	$sql = 'SELECT account, username FROM member WHERE account = ?';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array($_account));
	$userInfo = $pds -> fetch(PDO::FETCH_ASSOC);

	if ($diary) {
		$diary_responce = Array();
		date_default_timezone_set('Asia/Taipei');
		for ($i = 0; $i < sizeof($diary); $i++) {
			$sql = 'SELECT * FROM diary_img WHERE d_id = ?';
			$pds = $pdo -> prepare($sql);
			$pds -> execute(Array($diary[$i]['id']));
			$img_count = $pds -> fetchAll(PDO::FETCH_ASSOC);
			$imgArray = Array();
			if (sizeof($img_count) > 0)
				for ($j = 0; $j < sizeof($img_count); $j++)
					array_push($imgArray, SERVER . "file/" . $_account . "/" . $img_count[$j]['i_path']);

			$sql = 'SELECT count(d_id) FROM reply WHERE d_id = ?';
			$pds = $pdo -> prepare($sql);
			$pds -> execute(Array($diary[$i]['id']));
			$reply_count = $pds -> fetch(PDO::FETCH_ASSOC);
			$diary_responce[$i] = Array('id' => $diary[$i]['id'], 'date' => date('Y/m/d', strtotime($diary[$i]['time'])), 'title' => $diary[$i]['title'], 'content' => $diary[$i]['content'], 'place' => $diary[$i]['place'], 'img_count' => sizeof($img_count), 'reply_count' => $reply_count['count(d_id)'], 'imgArray' => $imgArray);
			if ($permission == 0)
				$diary_responce[$i]['permission'] = $diary[$i]['permission'];
			unset($imgArray);
		}
		return Array('status' => true, 'content' => $diary_responce, 'userInfo' => $userInfo);
	} else
		return Array('status' => false);
}

function delDiary($diaryId) {
	file_exists("../model/auth.php") ?
	require_once "../model/auth.php" : die("系統發生錯誤。");
	file_exists("../../config.php") ?
	require_once "../../config.php" : die("系統發生錯誤。");
	file_exists("../model/connectDB.php") ?
	require_once "../model/connectDB.php" : die("系統發生錯誤。");
	$pdo = connectDB($dsn, $account, $passwd);

	if (!isset($_SESSION))
		session_start();

	$prevCapacity = 0;

	$sql = 'SELECT music_path FROM diary WHERE id = ? AND me_id = (SELECT id FROM member WHERE account = ?)';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array($diaryId, $_SESSION['account']));
	$music_path = $pds -> fetch(PDO::FETCH_ASSOC);

	if ($music_path)
		$prevCapacity += number_format(filesize(FILE_PATH . $_SESSION['account'] . "/" . $music_path['music_path']) / 1048576, 2);

	$sql = 'SELECT i_path FROM diary_img WHERE d_id = ?';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array($diaryId));
	$image_path = $pds -> fetchAll(PDO::FETCH_ASSOC);
	if ($image_path)
		for ($i = 0; $i < sizeof($image_path); $i++)
			$prevCapacity += number_format(filesize(FILE_PATH . $_SESSION['account'] . "/" . $image_path[$i]['i_path']) / 1048576, 2);

	if ($prevCapacity > 0) {
		$sql = 'SELECT capacity FROM user WHERE me_id = (SELECT id FROM member WHERE account = ?)';
		$pds = $pdo -> prepare($sql);
		$pds -> execute(Array($_SESSION['account']));
		$capacity = $pds -> fetch(PDO::FETCH_ASSOC);

		$sql = 'UPDATE user SET capacity = ? WHERE me_id = (SELECT id FROM member WHERE account = ?)';
		$pds = $pdo -> prepare($sql);
		$pds -> execute(Array($capacity['capacity'] + $prevCapacity, $_SESSION['account']));
	}

	$sql = 'UPDATE diary SET permission = -1 WHERE id = ? AND me_id = (SELECT id FROM member WHERE account = ?)';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array($diaryId, $_SESSION['account']));

	return TRUE;
}

function loadEditDiary($diaryId) {
	file_exists("../model/auth.php") ?
	require_once "../model/auth.php" : die("系統發生錯誤。");
	file_exists("../../config.php") ?
	require_once "../../config.php" : die("系統發生錯誤。");
	file_exists("../model/connectDB.php") ?
	require_once "../model/connectDB.php" : die("系統發生錯誤。");
	$pdo = connectDB($dsn, $account, $passwd);

	if (!isset($_SESSION))
		session_start();

	/* check permission */
	$sql = 'SELECT * FROM diary WHERE id = ? AND me_id = (SELECT id FROM member WHERE account = ?) AND permission != -1';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array($diaryId, $_SESSION['account']));
	if (!($pds -> fetch(PDO::FETCH_ASSOC)))
		return Array('status' => FALSE);

	$sql = 'SELECT title, content, place, permission, music_path FROM diary WHERE id = ?';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array($diaryId));
	$diary = $pds -> fetch(PDO::FETCH_ASSOC);

	if ($diary['music_path'])
		$diary['music_path'] = SERVER . "file/" . $_SESSION['account'] . "/" . $diary['music_path'];

	$sql = 'SELECT i_path, name, content, path FROM image, diary_img WHERE d_id = ? AND i_path = path';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array($diaryId));
	$img = $pds -> fetchAll(PDO::FETCH_ASSOC);

	if ($img)
		for ($i = 0; $i < sizeof($img); $i++)
			$img[$i]['i_path'] = SERVER . "file/" . $_SESSION['account'] . "/" . $img[$i]['i_path'];
	// SERVER . "file/" . $account . "/" . $img_count[$j]['i_path']
	if ($diary)
		return Array('status' => TRUE, 'content' => $diary, 'img' => $img ? $img : NULL);
	else
		return Array('status' => FALSE);
}

function editDiary($post) {
	file_exists("../model/auth.php") ?
	require_once "../model/auth.php" : die("系統發生錯誤。");
	file_exists("../../config.php") ?
	require_once "../../config.php" : die("系統發生錯誤。");
	file_exists("../model/connectDB.php") ?
	require_once "../model/connectDB.php" : die("系統發生錯誤。");
	$pdo = connectDB($dsn, $account, $passwd);

	if (!isset($_SESSION))
		session_start();

	if ($post['title'] == "Title              ")
		return Array('status' => FALSE, 'content' => "標題不可無空。");
	else
		$post['title'] = htmlspecialchars($post['title']);

	if ($post['content'] == "Say Something ...               ")
		$post['content'] = NULL;
	else
		$post['content'] = htmlspecialchars($post['content']);

	if ($post['geocoder'] == "隱藏所在位置。               " || $post['geocoder'] == "定位中...               " || $post['geocoder'] == "無法取得位置。               " || $post['geocoder'] == "請輸入所在位置。               ")
		$post['geocoder'] = NULL;
	else
		$post['geocoder'] = htmlspecialchars($post['geocoder']);

	$prevCapacity = 0;

	$sql = 'SELECT time, music_path, diaryCount FROM diary WHERE id = ?';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array($post['diaryId']));
	$prevInfo = $pds -> fetch(PDO::FETCH_ASSOC);

	if ($post['music'] == "null") {
		$post['music'] = NULL;
		if ($prevInfo['music_path'])
			$prevCapacity += number_format(filesize(FILE_PATH . $_SESSION['account'] . "/" . $prevInfo['music_path']) / 1048576, 2);
	}

	$sql = 'INSERT INTO diary(title, content, place, time, diaryCount, permission, music_path, me_id) VALUES(?, ?, ?, ?, ?, ?, ?, (SELECT id FROM member WHERE account = ?))';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array($post['title'], $post['content'], $post['geocoder'], $prevInfo['time'], $prevInfo['diaryCount'], $post['permission'], $post['music'], $_SESSION['account']));

	$diary_id = $pdo -> lastInsertId();

	if ($post['image'] != "null") {
		$sql = 'INSERT INTO diary_img(d_id, i_path) VALUES(?, ?)';
		for ($i = 0; $i < sizeof($post['image']); $i++) {
			$pds = $pdo -> prepare($sql);
			$pds -> execute(Array($diary_id, $post['image'][$i]));
		}
	}
	if ($prevCapacity > 0) {
		$sql = 'SELECT capacity FROM user WHERE me_id = (SELECT id FROM member WHERE account = ?)';
		$pds = $pdo -> prepare($sql);
		$pds -> execute(Array($_SESSION['account']));
		$capacity = $pds -> fetch(PDO::FETCH_ASSOC);
		$sql = 'UPDATE user SET capacity = ? WHERE me_id = (SELECT id FROM member WHERE account = ?)';
		$pds = $pdo -> prepare($sql);
		$pds -> execute(Array($capacity['capacity'] - $prevCapacity, $_SESSION['account']));
	}

	$sql = 'UPDATE diary SET permission = -1 WHERE id = ?';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array($post['diaryId']));

	$sql = 'UPDATE reply SET d_id = ? WHERE d_id = ?';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array($diary_id, $post['diaryId']));

	return Array('status' => TRUE, 'diaryId' => $diary_id);
}
?>
