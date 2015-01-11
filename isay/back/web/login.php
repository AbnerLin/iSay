<?php
require_once "/var/www/isay/config.php";
if (!isset($_SESSION))
	session_start();
if (isset($_SESSION) && !empty($_SESSION['superUser']))
	header("location:" . SERVERBACK);
?>

<!DOCTYPE HTML>
<html lang="zh-TW">
	<head>
		<meta charset="UTF-8">
		<link rel="stylesheet" href="/css/login.css" />
		<script src="js/jquery.min.js"></script>
		<script src="/js/login.js"></script>
		<title><?php echo TITLE; ?></title>
	</head>
	
	<body>
		<div id="main">
			<div id="right">
				<div id="login_logo"><img src="/css/images/iSay_txt.gif" /></div>
					<div id="status"></div>
					<div id="login_dialog">
						帳　　號：
						<input type="text" id="accountInput" autocomplete="off" />
						<br>
						密　　碼：
						<input type="password" id="passwordInput" autocomplete="off" />
						<br>
						<div class="button">
							<a href="javascript:void(0);" class="btn" id="login">登　入</a>
							<br>
						</div>
				</div>
			</div>
		</div>
		
		<footer>
			<?php echo COPYRIGHT; ?>
		</footer>
	</body>
</html>