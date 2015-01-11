<?php
if (!isset($_SESSION))
	session_start();
if (isset($_SESSION['session']) && $_SESSION['session'])
	echo '
<div id="friend-manage">
	<a href="javascript:void(0)" id="BlockUserList">黑名單</a>
	<a href="javascript:void(0)" id="UserRequest">好友邀請</a>
	<a href="javascript:void(0)" id="showUserSearch">好友搜尋</a>
</div>
<div id="searchFriend">
	<input type="text" id="friendSearchInput">
	<a href="javascript:void(0)" id="friendSearchBtn">搜尋</a>
</div>';
?>
<div id="searchList"></div>
<div id="requestList"></div>
<div id="blockList"></div>
<div id="friendList">
	<div class="title">
		<div>
			好友清單
		</div>
	</div>
</div>