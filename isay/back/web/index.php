<?php
if (!isset($_SESSION))
	session_start();
if (!isset($_SESSION['superUser']))
	header("location:/login.php");
require_once "/var/www/isay/config.php";
// print_r($_SESSION);
// unset($_SESSION['superUser']);
?>
<!DOCTYPE HTML>
<html lang="zh-TW">
	<head>
		<meta charset="UTF-8">
		<link rel="stylesheet" href="/css/index.css" />
		<script src="js/jquery.min.js"></script>
		<script src="/js/index.js"></script>
		<title><?php echo TITLE; ?></title>
	</head>
	<body>
		<div id="main">
			<div id="left">
				<lu>
					<li id="announceManage">公告管理</li>
					<li>日誌管理</li>
					<li>會員管理</li>
					<li id="logout">登出</li>
				</lu>
			</div>
			<div id="right">
				<table id="announceTable">
					<caption>公告管理</caption>
					<th class="announceId">編號</th><th class="announceContent">內容</th><th class="announceTime">時間</th><th class="announceOpt">操作</th>
					<tr><td class="announceId">1</td><td class="announceContent">12312312312312312312</td><td class="announceTime">2</td><td class="announceOpt">刪除</td></tr>
				</table>
			</div>
		</div>

		<footer>
			<?php echo COPYRIGHT; ?>
		</footer>
	</body>
</html>