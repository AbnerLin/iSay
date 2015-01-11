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

function loadOrderDiaryList($_account) {
	if (!isset($_SESSION))
		session_start();
	if (!(isset($_SESSION['account']) && $_account == $_SESSION['account']))
		return Array('status' => FALSE);

	$sql = 'SELECT id, title FROM diary WHERE me_id = (SELECT id FROM member WHERE account = ?) AND permission != -1 ORDER BY time';
	$pds = scriptInit() -> prepare($sql);
	$pds -> execute(Array($_SESSION['account']));
	$diaryList = $pds -> fetchAll(PDO::FETCH_ASSOC);

	if ($diaryList)
		return Array('status' => TRUE, 'content' => $diaryList);
	else
		return Array('status' => TRUE);
}

function submitOrderDiary($post) {
	if ($post['name'] == "請填寫收件人姓名...               ")
		return Array('status' => FALSE, 'content' => "收件人不可為空。");
	else
		$post['name'] = htmlspecialchars($post['name']);

	if ($post['phone'] == "請填寫聯絡電話...               ")
		return Array('status' => FALSE, 'content' => "電話不可為空。");
	else
		$post['phone'] = htmlspecialchars($post['phone']);

	if ($post['address'] == "請填寫收件地址...               ")
		return Array('status' => FALSE, 'content' => "地址不可為空。");
	else
		$post['address'] == htmlspecialchars($post['address']);

	if (!is_numeric($post['quantity']))
		return Array('status' => FALSE, 'content' => "請正確填寫數量。");

	if (!isset($post['diaryList']))
		return Array('status' => FALSE, 'content' => "請勾選日誌。");

	$diaryList = Array();
	for ($i = 0; $i < sizeof($post['diaryList']); $i++) {
		$sql = 'SELECT * FROM diary WHERE id = ? AND me_id = (SELECT id FROM member WHERE account = ?) AND permission != -1';
		$pds = scriptInit() -> prepare($sql);
		$pds -> execute(Array($post['diaryList'][$i], $_SESSION['account']));
		if ($pds -> fetch(PDO::FETCH_ASSOC))
			array_push($diaryList, $post['diaryList'][$i]);
	}

	$money = $post['quantity'] * 100 + 40 * sizeof($diaryList);

	$sql = 'INSERT INTO orderDiary(money, name, address, phone, quantity, me_id) VALUES(?, ?, ?, ?, ?, (SELECT id FROM member WHERE account = ?))';
	$pds = scriptInit() -> prepare($sql);
	$pds -> execute(Array($money, $post['name'], $post['address'], $post['phone'], $post['quantity'], $_SESSION['account']));

	$order_id = scriptInit() -> lastInsertId();

	$sql = 'INSERT INTO binding VALUES(?, ?)';
	$pds = scriptInit() -> prepare($sql);
	for ($i = 0; $i < sizeof($diaryList); $i++)
		$pds -> execute(Array($diaryList[$i], $order_id));

	return Array('status' => TRUE);
}

function loadOrderRecord($_account) {
	if (!isset($_SESSION))
		session_start();
	if (!(isset($_SESSION['account']) && $_account == $_SESSION['account']))
		return Array('status' => FALSE);

	$sql = 'SELECT * FROM orderDiary WHERE me_id = (SELECT id FROM member WHERE account = ?)';
	$pds = scriptInit() -> prepare($sql);
	$pds -> execute(Array($_SESSION['account']));
	$orderList = $pds -> fetchAll(PDO::FETCH_ASSOC);

	if ($orderList) {
		$sql = 'SELECT id, title FROM diary, binding WHERE d_id = id AND o_id = ?';
		$pds = scriptInit() -> prepare($sql);
		for ($i = 0; $i < sizeof($orderList); $i++) {
			$pds -> execute(Array($orderList[$i]['id']));
			$diaryList = $pds -> fetchAll(PDO::FETCH_ASSOC);
			$orderList[$i]['diaryList'] = $diaryList;
		}
		return Array('status' => TRUE, 'content' => $orderList);
	} else
		return Array('status' => FALSE);

}

function cancelOrderDiary($orderId){
	$sql = 'DELETE FROM orderDiary WHERE status = 0 AND id = ? AND me_id = (SELECT id FROM member WHERE account = ?)';
	$pds = scriptInit() -> prepare($sql);
	$pds -> execute(Array($orderId, $_SESSION['account']));
	return TRUE;
}
?>