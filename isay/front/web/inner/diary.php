<?php
if (!isset($_SESSION))
	session_start();
?>
<div id="diary-manage">
	<a href="javascript:void(0)" id="show_diary_search" class="btn">搜尋日誌</a>
	<?php
	if (isset($_SESSION['session']) && $_SESSION['session'])
		echo '<a href="javascript:void(0)" id="show_diary_input">新增日誌</a>';
	?>
</div>
<?php
if (isset($_SESSION['session']) && $_SESSION['session'])
	echo '<div id="share-diary">
	<input type="text" id="diary-title" check="null"/>
	<textarea id="diary-content"></textarea>
	<br>
	<div id="preview"></div>
	<br>
	
	<audio controls="controls" autoplay class="music_player">
	</audio>

	<div id="diary-option">
		<span id="geocoder"><span id="geocoder-icon" class="geocoder_icon"></span><input type="text" id="geocoder-input" /><span class="cancel" id="cancel_geocoder"></span></span>
		<span id="permission"><div class="permission_icon"></div>
		<select id="permission-select">
			<option value="0">私人</option>
			<option value="1">好友</option>
			<option value="2">公開</option>
		</select>
		</span>
		
		<form id="image-form" method="post" enctype="multipart/form-data" action="/controller.php">
			<input type="hidden" name="action" value="image-upload" />
			<div id="diary_img"><input id="img-input" name="image[]" type=file multiple /><button type="button" id="img_uploadBtn">照片上傳</button></div>
		</form>
		
		
		<form id="music-form" method="post" enctype="multipart/form-data" action="/controller.php">
			<input type="hidden" name="action" value="music-upload" />
			<div id="diary_music"><input id="music-input" name="music" type="file" /><button type="button" id="music_uploadBtn">音樂上傳</button><button type="button" id="cancel_music">取消音樂</button></div>
		</form>
		
		
		<input type="text" onwebkitspeechchange="onChange(this.value)" x-webkit-speech id="speech-input" />
		<button type="button" id="submit">送　出	</button>
		<div id="geocoder-places"><div id="geocoder-status"></div><div id="geocoder-ul"><ul></ul></div><div id="map"></div></div>
	</div>
	</div>';
?>
<div id="searchDiary">
	<input type="text" id="diarySearchInput">
	<a href="javascript:void(0)" id="diarySearchBtn">搜尋</a>
</div>
<script type="text/javascript">
	function onChange(val) {
		var diary = document.getElementById('diary-content');
		diary.focus();
		var speech = document.getElementById('speech-input');
		diary.value = (diary.value) + (speech.value) + "，";
		diary.style.color = "#000000";
	}
</script>
<div id="diary-list"></div>
