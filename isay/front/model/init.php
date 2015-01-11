<?php
/* chech user isExist */
function checkUser($_account) {
	file_exists("../model/connectDB.php") ?
	require_once "../model/connectDB.php" : die("系統發生錯誤。");
	$pdo = connectDB();
	if (!isset($_SESSION))
		session_start();

	if (isset($_SESSION['account']) && $_SESSION['account'] == $_account)
		return Array('status' => TRUE);

	$sql = 'SELECT * FROM member WHERE account = ? AND type = 1';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array($_account));
	$row = $pds -> fetch(PDO::FETCH_ASSOC);

	if ($row) {
		/* check is User block me? */
		$sql = 'SELECT * FROM friend WHERE me_id1 = (SELECT id FROM member WHERE account = ?) AND me_id2 = (SELECT id FROM member WHERE account = ?) AND permission = -1';
		$pds = $pdo -> prepare($sql);
		$pds -> execute(Array($_account, $_SESSION['account']));
		$permission = $pds -> fetch(PDO::FETCH_ASSOC);
		if ($permission)
			return Array('status' => FALSE);
		else {
			/* check is You block User? */
			$sql = 'SELECT * FROM friend WHERE me_id1 = (SELECT id FROM member WHERE account = ?) AND me_id2 = (SELECT id FROM member WHERE account = ?) AND permission = -1';
			$pds = $pdo -> prepare($sql);
			$pds -> execute(Array($_SESSION['account'], $_account));
			$ownPermission = $pds -> fetch(PDO::FETCH_ASSOC);
			$check = NULL;
			if ($ownPermission)
				$check = TRUE;
			/* check is User added you? */
			$sql = 'SELECT permission FROM friend WHERE me_id1 = (SELECT id FROM member WHERE account = ?) AND me_id2 = (SELECT id FROM member WHERE account = ?) AND permission = 0';
			$pds = $pdo -> prepare($sql);
			$pds -> execute(Array($_account, $_SESSION['account']));
			$isFriend = $pds -> fetch(PDO::FETCH_ASSOC);
			if ($isFriend)
				$isFriend['permission'] = '2';
			else {
				/* check is User your friend? */
				$sql = 'SELECT permission FROM friend WHERE me_id1 = (SELECT id FROM member WHERE account = ?) AND me_id2 = (SELECT id FROM member WHERE account = ?) AND permission != -1';
				$pds = $pdo -> prepare($sql);
				$pds -> execute(Array($_SESSION['account'], $_account));
				$isFriend = $pds -> fetch(PDO::FETCH_ASSOC);
			}

			return Array('status' => TRUE, 'check' => $check, 'isFriend' => $isFriend ? $isFriend['permission'] : NULL);
		}
	} else
		return Array('status' => FALSE);
}

/* load header Image */
function loadHeader($_account) {
	file_exists("../../config.php") ?
	require_once "../../config.php" : die("系統發生錯誤。");

	file_exists("../model/connectDB.php") ?
	require_once "../model/connectDB.php" : die("系統發生錯誤。");
	$pdo = connectDB($dsn, $account, $passwd);

	$sql = 'SELECT headerImg FROM user, member WHERE account = ? and id = me_id';
	// $sql = 'SELECT id, capacity, headerImg FROM member, user where account = ? and id = me_id';
	// $sql = 'select * from user';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array($_account));
	$row = $pds -> fetch(PDO::FETCH_ASSOC);

	if ($row['headerImg'])
		return Array('status' => TRUE, 'content' => SERVER . 'file/' . $_account . '/' . $row['headerImg']);
	else
		return Array('status' => FALSE);
}

/* load Annonuce */
function loadAnnonuce() {
	file_exists("../model/connectDB.php") ?
	require_once "../model/connectDB.php" : die("系統發生錯誤。");
	$pdo = connectDB();

	$sql = 'SELECT content, time FROM announce ORDER BY time';
	$pds = $pdo -> prepare($sql);
	$pds -> execute();
	$row = $pds -> fetchAll(PDO::FETCH_ASSOC);

	if ($row)
		return Array('status' => TRUE, 'content' => $row);
	else
		return Array('status' => FALSE);
}

/* get New Reply */
function getNewReply($_account) {
	file_exists("../model/connectDB.php") ?
	require_once "../model/connectDB.php" : die("系統發生錯誤。");
	$pdo = connectDB();	$permission = (int)permission($_account);
	$sql = 'SELECT diary.id, diary.title, reply.time, reply.m_id FROM reply, diary WHERE reply.permission != -1 AND diary.permission >= :permission AND diary.id = reply.d_id AND diary.me_id = (SELECT id FROM member WHERE account = :account) ORDER BY reply.time DESC LIMIT 15';
	$pds = $pdo -> prepare($sql);
	$pds -> bindParam(':account', $_account, PDO::PARAM_STR);
	$pds -> bindParam(':permission', $permission, PDO::PARAM_INT);
	$pds -> execute();

	$result = $pds -> fetchAll(PDO::FETCH_ASSOC);
	if ($result) {
		for ($i = 0; $i < sizeof($result); $i++) {
			$sql = 'SELECT username, account FROM member WHERE id = ?';
			$pds = $pdo -> prepare($sql);
			$pds -> execute(Array($result[$i]['m_id']));
			$row = $pds -> fetch(PDO::FETCH_ASSOC);
			// array_push($result[$i], $pds -> fetch(PDO::FETCH_ASSOC));			$result[$i]['username'] = $row['username'];
			$result[$i]['account'] = $row['account'];
			unset($result[$i]['m_id']);
		}
		return Array('status' => TRUE, 'content' => $result);
	}
	return Array('status' => FALSE);

}

function mostView($_account) {
	file_exists("../model/connectDB.php") ?
	require_once "../model/connectDB.php" : die("系統發生錯誤。");
	$pdo = connectDB();
	$permission = (int)permission($_account);

	$sql = 'SELECT title, id FROM diary WHERE me_id = (SELECT id FROM member WHERE account = ?) AND permission >= ?ORDER BY diaryCount desc LIMIT 15';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array($_account, $permission));
	$result = $pds -> fetchAll(PDO::FETCH_ASSOC);

	if ($result)
		return Array('status' => TRUE, 'content' => $result);
	else
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
		$sql = 'SELECT * FROM friend WHERE me_id1 = (SELECT id FROM member WHERE account = ?) AND me_id2 = (SELECT id FROM member WHERE account = ?) AND permission = 1';
		$pds = $pdo -> prepare($sql);
		$pds -> execute(Array($account, $_SESSION['account']));
		$row = $pds -> fetch(PDO::FETCH_ASSOC);
		if ($row)
			return 1;
	}
	return 2;

}

function checkFriendRequest($account) {
	file_exists("../model/connectDB.php") ?
	require_once "../model/connectDB.php" : die("系統發生錯誤。");
	$pdo = connectDB();

	if (!isset($_SESSION))
		session_start();

	if (isset($_SESSION['account']) && $_SESSION['account'] == $account) {
		$sql = 'SELECT count(*) FROM member, user, friend WHERE me_id1 = id AND me_id1 = me_id AND me_id2 = (SELECT id FROM member WHERE account = ?) AND friend.permission = 0';
		$pds = $pdo -> prepare($sql);
		$pds -> execute(Array($_SESSION['account']));
		$result = $pds -> fetch(PDO::FETCH_ASSOC);
		if ($result['count(*)'] > 0)
			return Array('status' => TRUE, 'content' => $result['count(*)']);
	}
	return Array('status' => FALSE);
}
?>