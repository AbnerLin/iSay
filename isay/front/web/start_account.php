<?php

// email, username, passwd, id_number check
if ($_GET['account'] == NULL || $_GET['username'] == NULL || $_GET['check'] == NULL)
	die("請重新確認開通網址。");

$account = $_GET['account'];
$username = $_GET['username'];
$check = $_GET['check'];

file_exists("../model/connectDB.php") ?
require_once "../model/connectDB.php" : die("系統發生錯誤。 Error:010");
$pdo = connectDB();

$sql = 'SELECT type, passwd, id_number FROM member, user WHERE username = ? and account = ? and id = me_id';
$pds = $pdo -> prepare($sql);
$pds -> execute(Array($username, $account));
$row = $pds -> fetch(PDO::FETCH_ASSOC);
if ($check == md5($row['passwd'] . $row['id_number']) && $row['type'] == 0) {
	$sql = 'UPDATE member SET type = 1 WHERE account = ?';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array($account));
	file_exists("../../config.php") ?
	require_once "../../config.php" : die("系統發生錯誤。 Error:008");
	header("location:" . SERVER . "login.php");
} else {
	die("請重新確認開通網址。");
}
?>