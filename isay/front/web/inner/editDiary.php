<div id="editDiaryPage">
	
	<div id="editTitleBlock">標題：<br><input id="editTitleInput" type="text" /></div>
	<div id="editContentBlock">內容：<br><textarea id="editContentInput"></textarea></div>
	
	<div id="editDiaryOpt">
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
		
		<button type="button" id="submitEditDiary">更　新</button>
		<br><div id="geocoder-places" style="margin-top: 5px;"><div id="geocoder-status"></div><div id="geocoder-ul"><ul></ul></div><div id="map"></div></div>
	</div>
	<div id="previewContainer">
		<div id="preview"></div>
	</div>
	
	<div id="audioContainer">
		<audio controls="controls" class="music_player">
		</audio>
	</div>
	
</div>