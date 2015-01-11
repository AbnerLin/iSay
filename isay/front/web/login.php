<?php
require_once "../../config.php";
if (!isset($_SESSION))
	session_start();
if (isset($_SESSION) && !empty($_SESSION['account']))
	header("location:" . SERVER);
?>
<!DOCTYPE HTML>
<html lang="zh-TW">
	<head>
		<meta charset="UTF-8">
		<link rel="stylesheet" href="/css/login.css" />
		<script src="js/jquery.min.js"></script>
		<script src="js/login.js"></script>
		<title><?php echo TITLE; ?></title>
	</head>
	<body>
		<div id="main">
			<div id="left">
				<div id="left_down">
					<iframe class="show_ad" src="./ad/2.php"></iframe>
					<iframe class="show_ad" src="./ad/1.php"></iframe>
				</div>
			</div>
			<div id="right">
				<div id="login_logo"><img src="/css/images/iSay_txt.gif" />
				</div>
				<div id="status"></div>
				<div id="regist_dialog">
					暱　　稱：
					<input type="text" id="regist_username" autocomplete="off" />
					<br>
					帳　　號：
					<input type="text" id="regist_account" autocomplete="off" />
					<br>
					信　　箱：
					<input type="text" id="regist_email" autocomplete="off" />
					<br>
					確認信箱：
					<input type="text" id="regist_email_confirm" autocomplete="off" />
					<br>
					密　　碼：
					<input type="password" id="regist_password" autocomplete="off" />
					<br>
					確認密碼：
					<input type="password" id="regist_password_confirm" autocomplete="off" />
					<br>
					身份證字號：
					<input type="text" id="id_number" autocomplete="off" />
					<br>
					<div class="verify_dialog">
						<img src="" class="verify_image" />
						<a href="javascript:void(0);" class="verify_image">點此刷新驗證碼</a>
					</div>
					驗 證 碼 ：
					<input type="text" id="regist_verify" autocomplete="off" />
					<div class="button">
						<a href="login.html" class="btn" id="regist_cancel">取消註冊</a><a href="javascript:void(0);" class="btn" id="regist">提　交</a>
					</div>
				</div>
				<div id="login_dialog">
					帳　　號：
					<input type="text" id="login_account" autocomplete="off" />
					<br>
					密　　碼：
					<input type="password" id="login_password" autocomplete="off" />
					<br>
					<div class="verify_dialog">
						<img src="lib/verify/verify_image.php" class="verify_image" />
						<a href="javascript:void(0);" class="verify_image">點此刷新驗證碼</a>
					</div>
					驗 證 碼 ：
					<input type="text" id="login_verify" autocomplete="off" />
					<div class="button">
						<a href="regist.html" class="btn" id="regist_btn">註　冊</a><a href="javascript:void(0);" class="btn" id="login">登　入</a>
						<br>
						<a href="forget_passwd.html" id="forget_passwd">忘記密碼?</a>
					</div>
				</div>
				<div id="forget_dialog">
					帳　　號：
					<input type="text" id="forget_account" autocomplete="off" />
					<br>
					信　　箱：
					<input type="text" id="forget_email" autocomplete="off" />
					<br>
					身份證字號：
					<input type="text" id="forget_id_number" autocomplete="off" />
					<br>
					<div class="verify_dialog">
						<img src="lib/verify/verify_image.php" class="verify_image" />
						<a href="javascript:void(0);" class="verify_image">點此刷新驗證碼</a>
					</div>
					驗 證 碼 ：
					<input type="text" id="forget_verify" autocomplete="off" />
					<div class="button">
						<a href="login.html" class="btn" id="forget_cancel">取消填寫</a><a href="javascript:void(0);" class="btn" id="forget">提　交</a>
					</div>
				</div>
			</div>
		</div>
		<footer>
			<?php echo COPYRIGHT; ?>
		</footer>
	</body>
</html>