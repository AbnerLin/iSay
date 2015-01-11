<?php

function password_mailer($account, $email, $id_number) {
	file_exists("../model/connectDB.php") ?
	require_once "../model/connectDB.php" : die("系統發生錯誤。 Error:005");
	$pdo = connectDB();

	$sql = 'SELECT id, username FROM user, member WHERE account = ? and email = ? and id_number = ? and id = me_id';
	$pds = $pdo -> prepare($sql);
	$pds -> execute(Array($account, $email, $id_number));
	$row = $pds -> fetch(PDO::FETCH_ASSOC);
	if ($row != NULL) {
		/* random new passwd and update sql, email to the user */
		$new_passwd = rand(00000000, 99999999);
		$sql = 'UPDATE member SET passwd = ? where id = ?';
		$pds = $pdo -> prepare($sql);
		$pds -> execute(Array(md5($new_passwd), $row['id']));
		return postmail($email, $row['username'], "iSay 密碼通知", "您的新密碼為 " . $new_passwd . " 請登入後立即更改密碼。");
	} else {
		die("資料填寫有誤。");
	}
}

function regist_mailer($email, $account, $username, $passwd, $id_number) {
	file_exists("../../config.php") ?
	require_once "../../config.php" : die("系統發生錯誤。 Error:009");
	$body = "請連結至 " . SERVER . "start_account.php?account=" . $account . "&username=" . $username . "&check=" . md5($passwd . $id_number);
	return postmail($email, $username, "iSay 開通帳號通知", $body);
}

function postmail($to, $username, $subject = "", $body = "") {

	error_reporting(E_STRICT);
	date_default_timezone_set("Asia/Taipei");
	require_once ('mailer/class.phpmailer.php');
	include ("mailer/class.smtp.php");
	$mail = new PHPMailer();
	$body = eregi_replace("[\]", '', $body);
	$mail -> CharSet = "UTF-8";
	$mail -> IsSMTP();
	// 1 = errors and messages
	// 2 = messages only
	$mail -> SMTPDebug = 1;
	$mail -> SMTPAuth = true;
	$mail -> SMTPSecure = "ssl";
	$mail -> Host = "smtp.gmail.com";
	$mail -> Port = 465;
	
	/* Email account */
	$mail -> Username = "iSay.Team@gmail.com";
	/* Email password */
	$mail -> Password = "iSay1234";
	
	$mail -> SetFrom('iSay.Team@isay.com', 'iSay');
	// $mail -> SetFrom('iSay.Team@gmail.com', 'iSay');

	$mail -> AddReplyTo("iSay.Team@gmail.com", "iSay");
	$mail -> Subject = $subject;
	// $mail -> AltBody = "To view the message, please use an HTML compatible email viewer! - From www.jiucool.com";
	$mail -> MsgHTML($body);
	$address = $to;
	$mail -> AddAddress($address, $username);
	if (!$mail -> Send()) {
		// die("Mailer Error: " . $mail -> ErrorInfo);
		die("系統發生錯誤。 Error：006");
	} else {
		return TRUE;
	}
}
?>