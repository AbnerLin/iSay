<?php
require_once "../../config.php";
if (!isset($_SESSION))
	session_start();
$_SESSION['session'] = FALSE;
if (isset($_SESSION['account'])) {
	if ((isset($_GET['account']) && $_GET['account'] == $_SESSION['account']) || !isset($_GET['account'])) 
		$_SESSION['session'] = TRUE;
} else if (!isset($_GET['account']))
	header("location:login.html");
?>
<!DOCTYPE HTML>
<html lang="zh-TW">
	<head>
		<meta charset="UTF-8">
		<link rel="stylesheet" type="text/css" href="/css/index.css" />
		<link rel="stylesheet" type="text/css" href="/css/jquery-ui-1.9.0.custom.css" />
		<script type="text/jscript" src="/js/jquery.min.js"></script>
		<script type="text/jscript" src="/js/index.js"></script>
		<script type="text/jscript" src="/js/jquery.form.js"></script>
		<script type="text/jscript" src="/js/watermark.js"></script>
		<script type="text/jscript" src="/js/cycle.js"></script>
		<script type="text/jscript" src="/js/jquery.jcarousellite.js"></script>
		<script type="text/jscript" src="/js/jquery.timeformat.js"></script>
		<script type="text/jscript" src="/js/autoResize.js"></script>
		<script type="text/jscript" src="http://maps.google.com/maps/api/js?sensor=false&language=zh-TW&libraries=places,weather"></script>
		<script type="text/jscript" src="/js/jquery-ui-1.9.0.custom.js"></script>
		<script type="text/javascript">
		var account = "<?php
			if (isset($_GET['account']))
				echo $_GET['account'];
			else
				echo $_SESSION['account'];?>";
		var currentPage = "<?php
			if (isset($_GET['curpage'])){
				echo $_GET['curpage'];
				if(isset($_GET['page']))
					echo "\";\nvar diaryId = \"" . $_GET['page'];
			} else 
				echo "diary";	
			 ?>";
		</script>
		<title><?php echo TITLE; ?></title>
	</head>
	<body>
		<div id="status">
			<div id="statusText"></div>
		</div>
		<div id="option">
			<div id="announce"></div>
			<?php
			if(isset($_SESSION['account']))
				echo "<div id=\"postCard\"><div id=\"postCardInfo\"><div id=\"postCardHead\"></div><div id=\"postCardInfoRight\"><span class=\"postCardName\"></span><br><span id=\"postCardEmail\"></span></div></div><div id=\"postCardOpt\"><a href=\"javascript:void(0);\" id=\"logout\">登　出</a><a href=\"/\">首　頁</a></div></div><div class=\"edit\"></div><div id=\"optionInfo\"><img src=\"/css/images/defaultHead.jpg\" id=\"bigHeadImg\" /><div class=\"postCardName\"></div></div>";
			else
				echo "<a href=\"/login.html\">登　入</a>";
			
			?>
		</div>
		<header id="header">
			<?php 
				if(isset($_SESSION['session']) && $_SESSION['session']) 
					echo '<a href="javascript:void(0)" id="editHeader">編輯封面</a>
						  <div id="headerForm">
							  <form id="header-form" method="post" enctype="multipart/form-data" action="/controller.php">
								<input type="hidden" name="action" value="uploadHeader" />
								<input id="header-input" name="headerImg" type="file" />
							  </form>
							  	<span>
							  		<a href="javascript:void(0);" id="changeHeaderUI">更換封面</a>
							  	</span>
							  	<hr>
						  		<span>
						  			<a id="headerImgRemove" href="javascript:void(0);">移除封面</a>
						  		</span>
						  </div>'; 
			?>
		<div id="headerBG"></div>
		</header>
		<div id="main">
			<div id="center">
				<div id="menu">
					<span  id="infoBtn"> <img src="/css/images/infoBtn.png" />
						<br>
						個人資料 
					</span>
						<br>
					<span  id="diaryBtn"> <img src="/css/images/diaryBtn.png" />
						<br>
						日誌 
					</span>
						<br>
					<span id="friendBtn"> <img src="/css/images/friendBtn.png" />
						<br>
						好友 
					</span>
						<br>
						<?php
							if (isset($_SESSION['session']) && $_SESSION['session'])
								echo '<span id="exportBtn"> <img src="/css/images/export.png" />
									<br>
									日誌裝訂 
								</span>';
									// <br>
								// <span id="fileBtn"> <img src="/css/images/file.png" />
									// <br>
									// 檔案 
								// </span>';
						?>
				</div>
				<div id="ajax-block">
					<div id="content">
					</div>
					<div id="diaryLoading"><img src="/css/images/loader2.gif" /></div>
				</div>
			</div>
			<div id="right"><div id="friendOpt">
				<?php
					if ((isset($_SESSION['session']) && !$_SESSION['session']) && isset($_SESSION['account'])){
						echo '<a href="javascript:void(0)" class="addUser">加為好友</a>
							  <a href="javascript:void(0)" class="blockUser">封　鎖</a>';
					}
				?>
				</div><fieldset id="newReply"><legend>最新回應</legend></fieldset><fieldset id="mostView"><legend>最多瀏覽</legend></fieldset><div id="detailDiaryMusic">
			<audio controls="controls" ></audio>
		</div></div>
		</div>
		<?php
		if (isset($_SESSION['session']) && $_SESSION['session'])
			echo '
			<div id="absolute_back">
			<div id="absolute_front">
				<div id="stage">
					<div id="stage-img"><img src="">
					</div>
				</div>
				<div id="image-info">
					<div id="image-setting">
						<ul>
							<li>
								<a href="javascript:void(0);">相片名稱</a>
								<input type="text" id="image-name">
							</li>
							<li>
								<a href="javascript:void(0);" id="image-del">刪除相片</a>
							</li>
							<li>
								<a href="javascript:void(0);">相片敘述</a>
								<textarea id="image-content"></textarea>
								<a href="javascript:void(0);" id="okbtn">儲存</a>
							</li>
						</ul>
					</div>
				</div>
				<span class="cancel"></span>
			</div>
		</div>';
		?>
		<footer>
			<?php echo COPYRIGHT; ?>
		</footer>
	</body>
</html>