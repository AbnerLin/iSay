<?php
function scriptInit() {
	file_exists("../model/auth.php") ?
	require_once "../model/auth.php" : die("系統發生錯誤。");
	if (!isset($_SESSION))
		session_start();

	file_exists("../model/connectDB.php") ?
	require_once "../model/connectDB.php" : die("系統發生錯誤。");
	$pdo = connectDB();
	return $pdo;
}

function addUser($_account) {
	$sql = 'INSERT INTO friend(me_id1, me_id2, permission) VALUES((SELECT id FROM member WHERE account = ?), (SELECT id FROM member WHERE account = ?), 0)';
	$pds = scriptInit() -> prepare($sql);
	$pds -> execute(Array($_SESSION['account'], $_account));
	return TRUE;
}

function blockUser($_account) {
	$sql = 'INSERT INTO friend(me_id1, me_id2, permission) VALUES((SELECT id FROM member WHERE account = ?), (SELECT id FROM member WHERE account = ?), -1)';
	$pds = scriptInit() -> prepare($sql);
	$pds -> execute(Array($_SESSION['account'], $_account));
	return TRUE;
}

function unBlockUser($_account) {
	$sql = 'DELETE FROM friend WHERE permission = -1 AND me_id1 = (SELECT id FROM member WHERE account = ?) AND me_id2 = (SELECT id FROM member WHERE account = ?)';
	$pds = scriptInit() -> prepare($sql);
	$pds -> execute(Array($_SESSION['account'], $_account));
	return TRUE;
}

function delUser($_account) {
	$sql = 'DELETE FROM friend WHERE permission = 1 AND ((me_id1 = (SELECT id FROM member WHERE account = ?) AND me_id2 = (SELECT id FROM member WHERE account = ?)) OR (me_id1 = (SELECT id FROM member WHERE account = ?) AND me_id2 = (SELECT id FROM member WHERE account = ?)))';
	$pds = scriptInit() -> prepare($sql);
	$pds -> execute(Array($_SESSION['account'], $_account, $_account, $_SESSION['account']));
	return TRUE;
}

function unAddUser($_account) {
	$sql = 'DELETE FROM friend WHERE permission  = 0 AND me_id1 = (SELECT id FROM member WHERE account = ?) AND me_id2 = (SELECT id FROM member WHERE account = ?)';
	$pds = scriptInit() -> prepare($sql);
	$pds -> execute(Array($_SESSION['account'], $_account));
	return TRUE;
}

function allowUser($_account) {
	$sql = 'SELECT * FROM friend WHERE me_id1 = (SELECT id FROM member WHERE account = ?) AND me_id2 = (SELECT id FROM member WHERE account = ?) AND permission = 0';
	$pds = scriptInit() -> prepare($sql);
	$pds -> execute(Array($_account, $_SESSION['account']));
	if ($pds -> fetch(PDO::FETCH_ASSOC)) {
		$sql = 'UPDATE friend SET permission = 1 WHERE me_id1 = (SELECT id FROM member WHERE account = ?) AND me_id2 = (SELECT id FROM member WHERE account = ?)';
		$pds = scriptInit() -> prepare($sql);
		$pds -> execute(Array($_account, $_SESSION['account']));
		$sql = 'INSERT INTO friend(me_id1, me_id2, permission) VALUES((SELECT id FROM member WHERE account = ?), (SELECT id FROM member WHERE account = ?), 1)';
		$pds = scriptInit() -> prepare($sql);
		$pds -> execute(Array($_SESSION['account'], $_account));
		return TRUE;
	}
}

function loadFriend($_account) {
	if (!isset($_SESSION))
		session_start();

	file_exists("../../config.php") ?
	require_once "../../config.php" : die("系統發生錯誤。");
	file_exists("../model/connectDB.php") ?
	require_once "../model/connectDB.php" : die("系統發生錯誤。");
	$pdo = connectDB($dsn, $account, $passwd);

	$sql = 'SELECT me_id2 FROM friend WHERE me_id1 = (SELECT id FROM member WHERE account = ?) AND permission = 1';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array($_account));
	$idList = $pds -> fetchAll(PDO::FETCH_ASSOC);

	if (isset($_SESSION['account']) && $_SESSION['account'] == $_account) {
		$sql = 'SELECT count(*) FROM friend WHERE me_id2 = (SELECT id FROM member WHERE account = ?) AND permission = 0';
		$pds = scriptInit() -> prepare($sql);
		$pds -> execute(Array($_SESSION['account']));
		$requestYou = $pds -> fetch(PDO::FETCH_ASSOC);
	}
	
	$responceArray = Array();
	for ($i = 0; $i < sizeof($idList); $i++) {
		$sql = 'SELECT account, username, bigHeadImg FROM member, user WHERE me_id = ? AND me_id = id';
		$pds = $pdo -> prepare($sql);
		$pds -> execute(Array($idList[$i]['me_id2']));
		$userInfo = $pds -> fetch(PDO::FETCH_ASSOC);

		if ($userInfo['bigHeadImg'])
			$userInfo['bigHeadImg'] = SERVER . 'file/' . $userInfo['account'] . '/' . $userInfo['bigHeadImg'];

		if (isset($_SESSION['account']) && $_SESSION['account'] == $_account) {
			$sql = 'SELECT permission FROM friend WHERE me_id1 = (SELECT id FROM member WHERE account = ?) AND me_id2 = ? AND permission = -1';
			$pds = $pdo -> prepare($sql);
			$pds -> execute(Array($_SESSION['account'], $idList[$i]['me_id2']));
			$isBlock = $pds -> fetch(PDO::FETCH_ASSOC);
			$userInfo['isBlock'] = $isBlock['permission'] ? TRUE : FALSE;
		}

		array_push($responceArray, $userInfo);	}
	
	if ($responceArray)
		return Array('status' => TRUE, 'content' => $responceArray, 'isHome' => (isset($_SESSION['account']) && $_SESSION['account'] == $_account), 'request' => (isset($requestYou['count(*)']) && $requestYou['count(*)'] > 0) ? $requestYou['count(*)'] : NULL);
	else
		return Array('status' => FALSE, 'request' => (isset($requestYou['count(*)']) && $requestYou['count(*)'] > 0) ? $requestYou['count(*)'] : NULL);
}

function loadBlockList($_account) {
	$sql = 'SELECT me_id2 FROM friend WHERE me_id1 = (SELECT id FROM member WHERE account = ?) AND permission = -1';
	$pds = scriptInit() -> prepare($sql);
	$pds -> execute(Array($_SESSION['account']));
	$blockList = $pds -> fetchAll(PDO::FETCH_ASSOC);

	$responceArray = Array();
	for ($i = 0; $i < sizeof($blockList); $i++) {
		$sql = 'SELECT account, username, bigHeadImg FROM member, user WHERE me_id = ? AND me_id = id';
		$pds = scriptInit() -> prepare($sql);
		$pds -> execute(Array($blockList[$i]['me_id2']));
		$userInfo = $pds -> fetch(PDO::FETCH_ASSOC);
		if ($userInfo['bigHeadImg'])
			$userInfo['bigHeadImg'] = SERVER . 'file/' . $userInfo['account'] . '/' . $userInfo['bigHeadImg'];
		array_push($responceArray, $userInfo);
	}

	if ($blockList)
		return Array('status' => TRUE, 'content' => $responceArray);
	else
		return Array('status' => FALSE);
}

function UserRequest($_account) {
	$sql = 'SELECT account, username, bigHeadImg FROM member, user, friend WHERE me_id1 = id AND me_id1 = me_id AND me_id2 = (SELECT id FROM member WHERE account = ?) AND friend.permission = 0';
	$pds = scriptInit() -> prepare($sql);
	$pds -> execute(Array($_SESSION['account']));
	$requestYou = $pds -> fetchAll(PDO::FETCH_ASSOC);

	for ($i = 0; $i < sizeof($requestYou); $i++) {
		if ($requestYou[$i]['bigHeadImg'])
			$requestYou[$i]['bigHeadImg'] = SERVER . 'file/' . $requestYou[$i]['account'] . '/' . $requestYou[$i]['bigHeadImg'];
	}

	$sql = 'SELECT account, username, bigHeadImg FROM member, user, friend WHERE me_id2 = id AND me_id2 = me_id AND me_id1 = (SELECT id FROM member WHERE account = ?) AND friend.permission = 0';
	$pds = scriptInit() -> prepare($sql);
	$pds -> execute(Array($_SESSION['account']));
	$requestUser = $pds -> fetchAll(PDO::FETCH_ASSOC);

	for ($i = 0; $i < sizeof($requestUser); $i++) {
		if ($requestUser[$i]['bigHeadImg'])
			$requestUser[$i]['bigHeadImg'] = SERVER . 'file/' . $requestUser[$i]['account'] . '/' . $requestUser[$i]['bigHeadImg'];
	}

	if ($requestUser || $requestYou)
		return Array('status' => TRUE, 'content' => Array('requestYou' => $requestYou ? $requestYou : NULL, 'requestUser' => $requestUser ? $requestUser : NULL));
	else
		return Array('status' => FALSE);

}

function friendSearch($keyWord) {
	$sql = 'SELECT id, username, account, bigHeadImg FROM user, member WHERE id = me_id AND (username LIKE ? OR account LIKE ?) AND type != 0 AND id != (SELECT id FROM member WHERE account = ?) AND id NOT IN (SELECT me_id1 FROM friend WHERE me_id2 = (SELECT id FROM member WHERE account = ?) AND permission = -1)';
	$pds = scriptInit() -> prepare($sql);
	$pds -> execute(Array("%$keyWord%", "%$keyWord%", $_SESSION['account'], $_SESSION['account']));
	$result = $pds -> fetchAll(PDO::FETCH_ASSOC);

	for ($i = 0; $i < sizeof($result); $i++) {
		$sql = 'SELECT permission FROM friend WHERE me_id2 = ? AND me_id1 = (SELECT id FROM member WHERE account = ?) AND permission != -1';
		$pds = scriptInit() -> prepare($sql);
		$pds -> execute(Array($result[$i]['id'], $_SESSION['account']));
		$isFriend = $pds -> fetch(PDO::FETCH_ASSOC);

		$result[$i]['isFriend'] = $isFriend ? $isFriend['permission'] : NULL;

		$sql = 'SELECT permission FROM friend WHERE me_id1 = (SELECT id FROM member WHERE account = ?) AND me_id2 = ? AND permission = -1';
		$pds = scriptInit() -> prepare($sql);
		$pds -> execute(Array($_SESSION['account'], $result[$i]['id']));
		$isBlock = $pds -> fetch(PDO::FETCH_ASSOC);

		$result[$i]['isBlock'] = $isBlock != NULL ? TRUE : FALSE;

		if ($result[$i]['bigHeadImg'])
			$result[$i]['bigHeadImg'] = SERVER . 'file/' . $result[$i]['account'] . '/' . $result[$i]['bigHeadImg'];
		unset($result[$i]['id']);
	}

	if ($result)
		return Array('status' => TRUE, 'content' => $result);
	else
		return Array('status' => FALSE);
}
?>