<?php

class Authentication {

	private $pdo;

	public function __construct() {
		file_exists("../model/connectDB.php") ?
		require_once "../model/connectDB.php" : die("系統發生錯誤。 Error:004");
		$this -> pdo = connectDB();
	}

	/* register */
	public function regist($username, $account, $email, $passwd, $id_number) {
		/* check account is unique */
		$sql = 'SELECT * FROM member WHERE account = ?';
		$pds = $this -> pdo -> prepare($sql);
		$pds -> execute(Array($account));
		$row_account = $pds -> fetch(PDO::FETCH_ASSOC);
		/* check id_number is unique */
		$sql = 'SELECT * FROM user WHERE id_number = ?';
		$pds = $this -> pdo -> prepare($sql);
		$pds -> execute(Array($id_number));
		$row_id_number = $pds -> fetch(PDO::FETCH_ASSOC);
		if ($row_account != NULL) {
			die("帳號重複。");
		} else if ($row_id_number != NULL) {
			die("身份證字號重複。");
		} else {
			/* insert data into member */
			$sql = 'INSERT INTO member(username, account, email, passwd) values(?, ?, ?, ?)';
			$pds = $this -> pdo -> prepare($sql);
			$pds -> execute(Array(htmlspecialchars($username), $account, $email, md5($passwd)));
			/* select id from member */
			$sql = 'SELECT id FROM member where username = ? and account = ? and email = ? and passwd = ?';
			$pds = $this -> pdo -> prepare($sql);
			$pds -> execute(Array(htmlspecialchars($username), $account, $email, md5($passwd)));
			$row = $pds -> fetch(PDO::FETCH_ASSOC);
			/* insert data into user */
			$sql = 'INSERT INTO user(me_id, id_number) values(?, ?)';
			$pds = $this -> pdo -> prepare($sql);
			$pds -> execute(Array($row['id'], $id_number));
			/* create folder on server for user */
			mkdir('/var/www/isay/front/web/file/' . $account);
			/* mailer */
			file_exists("../model/mailer.php") ?
			require_once "../model/mailer.php" : die("系統發生錯誤。 Error:010");
			if (regist_mailer($email, $account, $username, md5($passwd), $id_number) == TRUE)
				echo TRUE;

		}
	}

	/* login */
	public function login($account, $passwd) {
		/* match email & password */
		$sql = 'SELECT * FROM member, user WHERE account = ? AND passwd = ? AND type = 1';
		$pds = $this -> pdo -> prepare($sql);
		$pds -> execute(Array($account, md5($passwd)));
		$row = $pds -> fetch(PDO::FETCH_ASSOC);
		if ($row == NULL) {
			die("帳號或密碼錯誤。");
		} else if ($row['type'] == 0) {
			die("帳號未開通");
		} else if ($row['type'] == 1) {
			if (!isset($_SESSION)) {
				session_start();
			}
			$_SESSION['account'] = $account;
			// $_SESSION['username'] = $row['username'];

			echo TRUE;
		}
	}

	public function logout() {
		if (!isset($_SESSION))
			session_start();
		session_destroy();
		echo TRUE;
	}

	/* set member_info */
	public function setData($key, $value) {
		// $this -> member_info[$key] = $value;

	}

	/* return member_info */
	public function getData() {
		if (!isset($_SESSION)) {
			session_start();
		}
		$sql = 'SELECT username, email, bigHeadImg FROM user, member WHERE account = ? AND me_id = id';
		$pds = $this -> pdo -> prepare($sql);
		$pds -> execute(Array($_SESSION['account']));
		$row = $pds -> fetch(PDO::FETCH_ASSOC);
		if ($row['bigHeadImg']) {
			file_exists("/var/www/isay/config.php") ?
			require_once ("/var/www/isay/config.php") : die("系統發生錯誤。");
			$row['bigHeadImg'] = SERVER . "file/" . $_SESSION['account']. "/" . $row['bigHeadImg'];
		}

		if ($row) {
			return Array('status' => TRUE, 'content' => $row);
		} else
			return Array('status' => FALSE);
	}

}
?>