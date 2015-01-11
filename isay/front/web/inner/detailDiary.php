<?php
// echo $_GET['diaryId'];
?>

<div id="detailDiary">
	<div id="detailDiaryTitle"><span></span></div>
	<div id="detailDiaryTime"></div>
	<div id="detailDiaryMain">
		<div id="detailDiaryContent"></div>
		<div id="detailDiarySlideShow">
			<div id="SlideShow-clip">
				<ul>

				</ul>
			</div>
			<div id="SlideShow-prev"></div>
			<div id="SlideShow-next"></div>
		</div>
		<div id="detailDiaryInfo"></div>
	</div>
</div>
<div id="detailDiaryReplyPage"></div>
<div id="detailDiaryReplyPaging"></div>

<div id="slideShowBackground">
	<div id="slideShowStage">
		<div id="slideShowStageLeft">
			<table id="slideShowImgStage">
				<tr>
					<th></th>
				</tr>
				<tr>
					<td><img id="slideShowImg" src=""></td>
				</tr>
			</table>
			<div id="slideShowImgInfo"></div>
			<div id="closeSlideShow"></div>
			<div id="slideShowOpt">
				<div id="prev"></div><div id="next"></div>
			</div>
		</div>
	</div>
	<div id="slideShowStageRight"></div>
</div>
<?php
if (!isset($_SESSION))
	session_start();

if (isset($_SESSION['account'])) {
	echo '<div id="detailDiaryReply"><div class="detailDiaryReplyHead"></div><textarea id="detailDiaryReplyInput"></textarea><div id="detailDiaryReplyOpt"><span id="replyPermission"><input type="checkbox" />悄悄話 </span><button type="button" id="submit">送　出	</button></div></div>';
} else {
	echo "只有會員可回應，請先登入。";
}
?>
