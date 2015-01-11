<?php
switch ($_POST['action']) {
	case 'login':
		file_exists("../model/authentication.php") ?
		require_once ("../model/authentication.php") : die("系統發生錯誤。");
		echo json_encode(login($_POST));
		break;
	case 'logout':
		file_exists("../model/authentication.php") ?
		require_once ("../model/authentication.php") : die("系統發生錯誤。");
		echo logout();
		break;
	case 'loadAnnounce':
		file_exists("../model/announce.php") ?
		require_once ("../model/announce.php") : die("系統發生錯誤。");
		echo json_encode(loadAnnounce());
		break;
	default :
		break;
}

?>