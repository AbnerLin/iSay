<?php

function insertReply($diaryId, $replyContent, $replyPermission) {
	file_exists("../model/auth.php") ?
	require_once "../model/auth.php" : die("系統發生錯誤。");
	if ($replyContent == "留言...               ")
		return FALSE;

	if (!isset($_SESSION))
		session_start();

	date_default_timezone_set('Asia/Taipei');
	$datetime = new DateTime();
	$datetime = $datetime -> format('Y-m-d H:i:s');

	if (checkPermission($diaryId)) {
		file_exists("../model/connectDB.php") ?
		require_once "../model/connectDB.php" : die("系統發生錯誤。");
		$pdo = connectDB();
		$sql = 'INSERT INTO reply VALUES((SELECT id FROM member WHERE account = ?), ?, ?, ?, ?)';
		$pds = $pdo -> prepare($sql);
		$pds -> execute(Array($_SESSION['account'], $diaryId, $datetime, htmlspecialchars($replyContent), $replyPermission));
		return TRUE;
	}
	return FALSE;

}

function selectReply($diaryId) {
	if (checkPermission($diaryId)) {
		file_exists("../../config.php") ?
		require_once "../../config.php" : die("系統發生錯誤。");
		file_exists("../model/connectDB.php") ?
		require_once "../model/connectDB.php" : die("系統發生錯誤。");

		$pdo = connectDB();

		/* select reply */
		$sql = 'SELECT * FROM reply WHERE d_id = ? AND permission >= 0 ORDER BY time';
		$pds = $pdo -> prepare($sql);
		$pds -> execute(Array($diaryId));		$rowReply = $pds -> fetchAll(PDO::FETCH_ASSOC);
		if (!$rowReply)
			return Array('status' => FALSE);

		/* select user's id */
		if (!isset($_SESSION))
			session_start();

		$userId = null;
		if (isset($_SESSION['account'])) {
			$sql = 'SELECT id FROM member WHERE account = ?';
			$pds = $pdo -> prepare($sql);
			$pds -> execute(Array($_SESSION['account']));
			$userId = $pds -> fetch(PDO::FETCH_ASSOC);
		}

		$replyResponce = Array();
		for ($i = 0; $i < sizeof($rowReply); $i++) {
			$sql = 'SELECT username, account, bigHeadImg FROM user, member WHERE me_id = ? AND me_id = id';
			$pds = $pdo -> prepare($sql);
			$pds -> execute(Array($rowReply[$i]['m_id']));
			$userInfo = $pds -> fetch(PDO::FETCH_ASSOC);
			if ($userInfo['bigHeadImg'])
				$userInfo['bigHeadImg'] = SERVER . 'file/' . $userInfo['account'] . '/' . $userInfo['bigHeadImg'];
			else
				$userInfo['bigHeadImg'] = NULL;
			// $isUser = FALSE;
			// if ($rowReply['m_id'] == $userId['id'])
			// $isUser = TRUE;			$isUser = ($rowReply[$i]['m_id'] == $userId['id']) ? TRUE : FALSE;

			if ($rowReply[$i]['permission'] == 0 && !$isUser && !$_SESSION['session'])
				$rowReply[$i]['content'] = NULL;

			$replyResponce[$i] = Array('replyContent' => Array('time' => $rowReply[$i]['time'], 'content' => $rowReply[$i]['content']), 'userInfo' => $userInfo, 'isUser' => $isUser);
		}
		return Array('status' => TRUE, 'content' => $replyResponce, 'isHome' => $_SESSION['session']);
	} else {
		return Array('status' => FALSE);
	}

}

/* check user's permission for select diary */
function checkPermission($diaryId) {
	file_exists("../model/connectDB.php") ?
	require_once "../model/connectDB.php" : die("系統發生錯誤。");
	$pdo = connectDB();

	$sql = 'SELECT account FROM member, diary WHERE diary.me_id = member.id AND diary.id = ?';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array($diaryId));
	$rowAccount = $pds -> fetch(PDO::FETCH_ASSOC);

	$sql = 'SELECT * FROM diary WHERE id = :id AND permission >= :permission';
	$pds = $pdo -> prepare($sql);
	$pds -> bindParam(':id', $diaryId, PDO::PARAM_INT);
	$pds -> bindParam(':permission', permission($rowAccount['account']), PDO::PARAM_INT);
	$pds -> execute();
	return $pds -> fetch(PDO::FETCH_ASSOC);
}

function permission($account) {
	file_exists("../model/connectDB.php") ?
	require_once "../model/connectDB.php" : die("系統發生錯誤。");
	$pdo = connectDB();

	if (!isset($_SESSION))
		session_start();

	if (isset($_SESSION['account']) && $_SESSION['account'] == $account)
		return 0;
	else if (isset($_SESSION['account'])) {
		$sql = 'SELECT * FROM friend WHERE me_id1 = (SELECT id FROM member WHERE account = ?) AND me_id2 = (SELECT id FROM member WHERE account = ?) AND permission = 1';
		$pds = $pdo -> prepare($sql);
		$pds -> execute(Array($account, $_SESSION['account']));
		$row = $pds -> fetch(PDO::FETCH_ASSOC);
		if ($row)
			return 1;
	}
	return 2;
}

function updateReply($diaryId, $time, $replyContent) {
	file_exists("../model/auth.php") ?
	require_once "../model/auth.php" : die("系統發生錯誤。");
	file_exists("../model/connectDB.php") ?
	require_once "../model/connectDB.php" : die("系統發生錯誤。");
	$pdo = connectDB();

	if (!isset($_SESSION))
		session_start();

	/* get user's ID */
	$sql = 'SELECT id FROM member WHERE account = ?';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array($_SESSION['account']));
	$userId = $pds -> fetch(PDO::FETCH_ASSOC);

	if (checkPermission($diaryId)) {
		/* update reply's permission & insert a new reply */
		$sql = 'SELECT permission FROM reply WHERE m_id = ? AND d_id = ? AND time = ? AND permission != -1';
		$pds = $pdo -> prepare($sql);
		$pds -> execute(Array($userId['id'], $diaryId, $time));
		$oldReply = $pds -> fetch(PDO::FETCH_ASSOC);

		$sql = 'UPDATE reply SET permission = -1 WHERE m_id = ? AND d_id = ? AND time = ?';
		$pds = $pdo -> prepare($sql);
		$pds -> execute(Array($userId['id'], $diaryId, $time));

		$sql = 'INSERT INTO reply VALUES(?, ?, ?, ?, ?)';
		$pds = $pdo -> prepare($sql);
		$pds -> execute(Array($userId['id'], $diaryId, $time, $replyContent, $oldReply['permission']));

		return TRUE;
	}
	return FALSE;

}

function deleteReply($diaryId, $time, $_account) {
	file_exists("../model/auth.php") ?
	require_once "../model/auth.php" : die("系統發生錯誤。");
	file_exists("../model/connectDB.php") ?
	require_once "../model/connectDB.php" : die("系統發生錯誤。");
	$pdo = connectDB();

	if (!isset($_SESSION))
		session_start();

	if (checkPermission($diaryId)) {
		$sql = 'SELECT id FROM member WHERE account = ?';
		$pds = $pdo -> prepare($sql);
		$pds -> execute(Array($_account));
		$tmpId = $pds -> fetch(PDO::FETCH_ASSOC);

		$pds -> execute(Array($_SESSION['account']));
		$nowId = $pds -> fetch(PDO::FETCH_ASSOC);

		$sql = 'SELECT account FROM member, diary WHERE diary.me_id = member.id AND diary.id = ?';
		$pds = $pdo -> prepare($sql);
		$pds -> execute(Array($diaryId));
		$diaryOwner = $pds -> fetch(PDO::FETCH_ASSOC);

		if (($diaryOwner['account'] == $_SESSION['account']) || ($tmpId['id'] == $nowId['id'])) {
			$sql = 'UPDATE reply SET permission = -1 WHERE m_id = ? AND d_id = ? AND time = ?';
			$pds = $pdo -> prepare($sql);
			$pds -> execute(Array($tmpId['id'], $diaryId, $time));			// $pds -> execute(Array("83", $diaryId, $time));			return TRUE;
		}	}
	return FALSE;
}

function replyLongPolling($_account) {
	file_exists("../model/connectDB.php") ?
	require_once "../model/connectDB.php" : die("系統發生錯誤。");
	$pdo = connectDB();

	$permission = (int)permission($_account);

	$sql = 'SELECT reply.time FROM reply, diary WHERE diary.permission >= ? AND reply.d_id = diary.id AND diary.me_id = (SELECT id FROM member WHERE account = ?) ORDER BY reply.time DESC';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array($permission, $_account));
	$prevTime = $pds -> fetch(PDO::FETCH_ASSOC);

	$startTime = time();
	session_write_close();
	while ($startTime + 15 > time()) {
		$sql = 'SELECT diary.id, diary.title, reply.time, reply.m_id FROM reply, diary WHERE diary.permission >= ? AND reply.d_id = diary.id AND diary.me_id = (SELECT id FROM member WHERE account = ?) AND reply.time > ? ORDER BY reply.time DESC';
		$pds = $pdo -> prepare($sql);
		$pds -> execute(Array($permission, $_account, $prevTime['time']));
		$result = $pds -> fetchAll(PDO::FETCH_ASSOC);

		if ($result) {
			for ($i = 0; $i < sizeof($result); $i++) {
				$sql = 'SELECT username, account FROM member WHERE id = ?';
				$pds = $pdo -> prepare($sql);
				$pds -> execute(Array($result[$i]['m_id']));
				$row = $pds -> fetch(PDO::FETCH_ASSOC);
				$result[$i]['username'] = $row['username'];
				$result[$i]['account'] = $row['account'];
				unset($result[$i]['m_id']);
			}
			return Array('status' => TRUE, 'content' => $result);
			break;
		}
		sleep(3);
	}

	return Array('status' => FALSE);
}
?>