<?php
switch ($_POST['action']) {
	case 'regist' :
		/* check format */
		foreach ($_POST as $key => $value) {
			switch ($key) {
				case 'username' :
					if ($value == NULL) {
						die("暱稱不可為空。");
					}
					break;
				case 'account' :
					if ($value == NULL) {
						die("帳號不可為空。");
					} else if (!eregi("^[0-9a-zA-Z]+$", $value)) {
						die("帳號不可包含符號。");
					}
					break;
				case 'email' :
					if ($value == NULL) {
						die("信箱不可為空。");
					} else if (!eregi("^[_.0-9a-z-]+@([0-9a-z][0-9a-z-])+.+[a-z]{2,4}$", $value)) {
						die("信箱格式不正確。");
					}
					break;
				case 'email_confirm' :
					if ($value == NULL) {
						die("確認信箱不可為空。");
					} else if ($value != $_POST['email']) {
						die("信箱與確認信箱不符合。");
					}
					break;
				case 'password' :
					if ($value == NULL) {
						die("密碼不可為空。");
					} else if (strlen($_POST['password']) < 8) {
						die("密碼長度必須大於 8。");
					}
					break;
				case 'password_confirm' :
					if ($value == NULL) {
						die("確認密碼不可為空。");
					} else if ($value != $_POST['password']) {
						die("密碼與確認密碼不符合。");
					}
					break;
				case 'id_number' :
					if ($value == NULL) {
						die("身份證字號不可為空。");
					} else if (strlen($value) != 10 || !check_id_number($value)) {
						die("身份證字號不合法。");
					}
					break;
				case 'verify' :
					if (!isset($_SESSION)) {
						session_start();
					}
					$vCode = explode("|", $_SESSION['vCode']);
					if ($value == NULL) {
						die("驗證碼不可為空。");
					} else if ($vCode[0] != $_POST['verify']) {
						die("驗證碼錯誤。");
					}
					break;
			}
		}
		/* register */
		Authentication() -> regist($_POST['username'], $_POST['account'], $_POST['email'], $_POST['password'], $_POST['id_number']);
		break;
	case 'login' :
		/* check input tag is null */
		if (!isset($_SESSION)) {
			session_start();
		}
		$vCode = explode("|", $_SESSION['vCode']);
		if ($_POST['account'] == NULL) {
			die("信箱不可為空。");
		} else if ($_POST['password'] == NULL) {
			die("密碼不可為空。");
		} else if ($_POST['verify'] == NULL) {
			die("驗證碼不可為空。");
		} else if ($vCode[0] != $_POST['verify']) {
			die("驗證碼錯誤。");
		}
		/* login */
		Authentication() -> login($_POST['account'], $_POST['password']);
		break;
	case 'forget_passwd' :
		if (!isset($_SESSION)) {
			session_start();
		}
		$vCode = explode("|", $_SESSION['vCode']);
		if ($_POST['account'] == NULL) {
			die("帳號不可為空。");
		} else if ($_POST['email'] == NULL) {
			die("信箱不可為空。");
		} else if (!eregi("^[_.0-9a-z-]+@([0-9a-z][0-9a-z-])+.+[a-z]{2,4}$", $_POST['email'])) {
			die("信箱格式不正確。");
		} else if ($_POST['id_number'] == NULL) {
			die("身份證字號不可為空。");
		} else if (strlen($_POST['id_number']) != 10 || !check_id_number($_POST['id_number'])) {
			die("身份證字號不合法。");
		} else if ($_POST['verify'] == NULL) {
			die("驗證碼不可為空。");
		} else if ($vCode[0] != $_POST['verify']) {
			die("驗證碼錯誤。");
		}
		/*  mailer  */
		file_exists("../model/mailer.php") ?
		require_once "../model/mailer.php" : die("系統發生錯誤。");
		if (password_mailer($_POST['account'], $_POST['email'], $_POST['id_number']) == TRUE)
			echo TRUE;
		break;
	case 'logout' :
		Authentication() -> logout();
		unset($Auth);
		break;
	case 'image-upload' :
		file_exists("../model/ajax_file.php") ?
		require_once ("../model/ajax_file.php") : die("系統發生錯誤。");
		echo json_encode(upload_image($_FILES));
		break;
	case 'cancel_image' :
		file_exists("../model/ajax_file.php") ?
		require_once ("../model/ajax_file.php") : die("系統發生錯誤。");
		echo delete_file($_POST['image'], "image");
		break;
	case 'get_imginfo' :
		file_exists("../model/ajax_file.php") ?
		require_once ("../model/ajax_file.php") : die("系統發生錯誤。");
		echo json_encode(get_imginfo($_POST['tmp_name']));
		break;
	case 'set_imginfo' :
		file_exists("../model/ajax_file.php") ?
		require_once ("../model/ajax_file.php") : die("系統發生錯誤。");
		echo json_encode(set_imginfo($_POST['tmp_name'], $_POST['name'], $_POST['content']));
		break;
	case 'music-upload' :
		file_exists("../model/ajax_file.php") ?
		require_once ("../model/ajax_file.php") : die("系統發生錯誤。");
		echo json_encode(upload_music($_FILES));
		break;
	case 'cancel_music' :
		file_exists("../model/ajax_file.php") ?
		require_once ("../model/ajax_file.php") : die("系統發生錯誤。");
		echo delete_file($_POST['music'], "music");
		break;
	case 'new_diary' :
		file_exists("../model/diary.php") ?
		require_once ("../model/diary.php") : die("系統發生錯誤。");
		echo json_encode(insert_diary($_POST['title'], $_POST['content'], $_POST['geocoder'], $_POST['image'], $_POST['music'], $_POST['permission']));
		break;
	case 'load_diary' :
		file_exists("../model/diary.php") ?
		require_once ("../model/diary.php") : die("系統發生錯誤。");
		echo json_encode(select_diary($_POST['account'], $_POST['limit']));
		break;
	case 'longPolling' :
		file_exists("../model/diary.php") ?
		require_once ("../model/diary.php") : die("系統發生錯誤。");
		echo json_encode(longPolling($_POST['account']));
		break;
	case 'uploadHeader' :
		file_exists("../model/ajax_file.php") ?
		require_once ("../model/ajax_file.php") : die("系統發生錯誤。");
		echo json_encode(uploadHeader($_FILES));
		break;
	case 'loadHeader' :
		file_exists("../model/init.php") ?
		require_once ("../model/init.php") : die("系統發生錯誤。");
		echo json_encode(loadHeader($_POST['account']));
		break;
	case 'headerImgRemove' :
		file_exists("../model/ajax_file.php") ?
		require_once ("../model/ajax_file.php") : die("系統發生錯誤。");
		echo json_encode(headerImgRemove($_POST['account']));
		break;
	case 'checkUser' :
		file_exists("../model/init.php") ?
		require_once ("../model/init.php") : die("系統發生錯誤。");
		echo json_encode(checkUser($_POST['account']));
		break;
	case 'setPostCardInfo' :
		file_exists("../model/Authentication.php") ?
		require_once "../model/Authentication.php" : die("系統發生錯誤。 Error: 001");
		echo json_encode( Authentication() -> getData());
		break;
	case 'setAnnonuce' :
		file_exists("../model/init.php") ?
		require_once ("../model/init.php") : die("系統發生錯誤。");
		echo json_encode(loadAnnonuce());
		break;
	case 'loadDetailDiary' :
		file_exists("../model/diary.php") ?
		require_once ("../model/diary.php") : die("系統發生錯誤。");
		// echo json_encode(longPolling($_POST['account']));
		echo json_encode(loadDetailDiary($_POST['account'], $_POST['diaryId']));		break;
	case 'newReply' :
		file_exists("../model/reply.php") ?
		require_once ("../model/reply.php") : die("系統發生錯誤。");
		echo insertReply($_POST['diaryId'], $_POST['replyContent'], $_POST['replyPermission']);
		break;
	case 'loadReply' :
		file_exists("../model/reply.php") ?
		require_once ("../model/reply.php") : die("系統發生錯誤。");
		echo json_encode(selectReply($_POST['diaryId']));
		break;
	case 'editReply' :
		file_exists("../model/reply.php") ?
		require_once ("../model/reply.php") : die("系統發生錯誤。");
		echo updateReply($_POST['diaryId'], $_POST['time'], $_POST['replyContent']);
		break;
	case 'delReply' :
		file_exists("../model/reply.php") ?
		require_once ("../model/reply.php") : die("系統發生錯誤。");
		echo deleteReply($_POST['diaryId'], $_POST['time'], $_POST['account']);
		break;

	case 'getNewReply' :
		file_exists("../model/init.php") ?
		require_once ("../model/init.php") : die("系統發生錯誤。");
		echo json_encode(getNewReply($_POST['account']));
		break;
	case 'mostView' :
		file_exists("../model/init.php") ?
		require_once ("../model/init.php") : die("系統發生錯誤。");
		echo json_encode(mostView($_POST['account']));
		break;
	case 'replyLongPolling' :
		file_exists("../model/reply.php") ?
		require_once ("../model/reply.php") : die("系統發生錯誤。");
		echo json_encode(replyLongPolling($_POST['account']));
		break;
	case 'diarySearch' :
		file_exists("../model/diary.php") ?
		require_once ("../model/diary.php") : die("系統發生錯誤。");
		echo json_encode(diarySearch($_POST['account'], $_POST['searchKey']));
		break;
	case 'loadInfo' :
		file_exists("../model/info.php") ?
		require_once ("../model/info.php") : die("系統發生錯誤。");
		echo json_encode(loadInfo($_POST['account']));
		break;
	case 'UsernameEdit' :
		file_exists("../model/info.php") ?
		require_once ("../model/info.php") : die("系統發生錯誤。");
		echo UsernameEdit($_POST['username']);
		break;
	case 'PasswdEdit' :
		file_exists("../model/info.php") ?
		require_once ("../model/info.php") : die("系統發生錯誤。");
		echo json_encode(PasswdEdit($_POST['OldPasswdInput'], $_POST['NewPasswdInput'], $_POST['AgainPasswdInput']));
		break;
	case 'birthEdit' :
		file_exists("../model/info.php") ?
		require_once ("../model/info.php") : die("系統發生錯誤。");
		echo birthEdit($_POST['date'], $_POST['permission']);
		break;
	case 'genderEdit' :
		file_exists("../model/info.php") ?
		require_once ("../model/info.php") : die("系統發生錯誤。");
		echo genderEdit($_POST['gender'], $_POST['permission']);
		break;
	case 'infoEdit' :
		file_exists("../model/info.php") ?
		require_once ("../model/info.php") : die("系統發生錯誤。");
		echo infoEdit($_POST['info'], $_POST['permission']);
		break;
	case 'emailEdit' :
		file_exists("../model/info.php") ?
		require_once ("../model/info.php") : die("系統發生錯誤。");
		echo emailEdit($_POST['permission']);
		break;
	case 'bigHead-upload' :
		file_exists("../model/info.php") ?
		require_once ("../model/info.php") : die("系統發生錯誤。");
		echo json_encode(bigHeadEdit($_FILES));
		break;
	case 'addUser' :
		file_exists("../model/friend.php") ?
		require_once ("../model/friend.php") : die("系統發生錯誤。");
		echo addUser($_POST['account']);
		break;
	case 'blockUser' :
		file_exists("../model/friend.php") ?
		require_once ("../model/friend.php") : die("系統發生錯誤。");
		echo BlockUser($_POST['account']);
		break;
	case 'unBlockUser' :
		file_exists("../model/friend.php") ?
		require_once ("../model/friend.php") : die("系統發生錯誤。");
		echo unBlockUser($_POST['account']);
		break;
	case 'delUser' :
		file_exists("../model/friend.php") ?
		require_once ("../model/friend.php") : die("系統發生錯誤。");
		echo delUser($_POST['account']);
		break;
	case 'unAddUser' :
		file_exists("../model/friend.php") ?
		require_once ("../model/friend.php") : die("系統發生錯誤。");
		echo unAddUser($_POST['account']);
		break;
	case 'allowUser' :
		file_exists("../model/friend.php") ?
		require_once ("../model/friend.php") : die("系統發生錯誤。");
		echo allowUser($_POST['account']);
		break;
	case 'loadFriend' :
		file_exists("../model/friend.php") ?
		require_once ("../model/friend.php") : die("系統發生錯誤。");
		echo json_encode(loadFriend($_POST['account']));
		break;
	case 'loadBlockList' :
		file_exists("../model/friend.php") ?
		require_once ("../model/friend.php") : die("系統發生錯誤。");
		echo json_encode(loadBlockList($_POST['account']));
		break;
	case 'UserRequest' :
		file_exists("../model/friend.php") ?
		require_once ("../model/friend.php") : die("系統發生錯誤。");
		echo json_encode(UserRequest($_POST['account']));
		break;
	case 'checkFriendRequest' :
		file_exists("../model/init.php") ?
		require_once ("../model/init.php") : die("系統發生錯誤。");
		echo json_encode(checkFriendRequest($_POST['account']));
		break;
	case 'friendSearch' :
		file_exists("../model/friend.php") ?
		require_once ("../model/friend.php") : die("系統發生錯誤。");
		echo json_encode(friendSearch($_POST['keyWord']));
		break;
	case 'delDiary' :
		file_exists("../model/diary.php") ?
		require_once ("../model/diary.php") : die("系統發生錯誤。");
		echo delDiary($_POST['diaryId']);
		break;
	case 'loadEditDiary' :
		file_exists("../model/diary.php") ?
		require_once ("../model/diary.php") : die("系統發生錯誤。");
		echo json_encode(loadEditDiary($_POST['diaryId']));
		break;
	case 'editDiary' :
		file_exists("../model/diary.php") ?
		require_once ("../model/diary.php") : die("系統發生錯誤。");
		echo json_encode(editDiary($_POST));
		break;
	case 'loadOrderDiaryList':
		file_exists("../model/orderDiary.php") ?
		require_once ("../model/orderDiary.php") : die("系統發生錯誤。");
		echo json_encode(loadOrderDiaryList($_POST['account']));
		break;
	case 'submitOrderDiary':
		file_exists("../model/orderDiary.php") ?
		require_once ("../model/orderDiary.php") : die("系統發生錯誤。");
		echo json_encode(submitOrderDiary($_POST));
		break;
	case 'loadOrderRecord':
		file_exists("../model/orderDiary.php") ?
		require_once ("../model/orderDiary.php") : die("系統發生錯誤。");
		echo json_encode(loadOrderRecord($_POST['account']));
		break;
	case 'cancelOrderDiary':
		file_exists("../model/orderDiary.php") ?
		require_once ("../model/orderDiary.php") : die("系統發生錯誤。");
		echo cancelOrderDiary($_POST['orderId']);
		break;
	default :
		break;
}

/* new Object for Authentication */
function Authentication() {
	file_exists("../model/Authentication.php") ?
	require_once "../model/Authentication.php" : die("系統發生錯誤。 Error: 001");
	return new Authentication();
}

/* check id_number' s format */
function check_id_number($value) {
	$head = array("A" => 10, "B" => 11, "C" => 12, "D" => 13, "E" => 14, "F" => 15, "G" => 16, "H" => 17, "I" => 34, "J" => 18, "K" => 19, "L" => 20, "M" => 21, "N" => 22, "O" => 35, "P" => 23, "Q" => 24, "R" => 25, "S" => 26, "T" => 27, "U" => 28, "V" => 29, "W" => 32, "X" => 30, "Y" => 31, "Z" => 33);
	$sum = (int)($head[strtoupper($value[0])] % 10 * 9) + (int)($head[strtoupper($value[0])] / 10);
	// echo $sum;
	$count = 8;
	for ($i = 1; $i <= 9; $i++) {
		if ($count != 0)
			$sum += $value[$i] * $count--;
		else
			$sum += $value[$i];
	}
	if ($sum % 10 == 0)
		return TRUE;
	else
		return FALSE;
}
?>