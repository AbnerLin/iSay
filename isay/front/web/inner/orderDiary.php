<div id="order-manage">
	<a href="javascript:void(0)" id="showOrderRecord">裝訂記錄</a>
</div>
<div id="orderDiaryRecord">
	<div class="title">
		<div>
			裝訂記錄
		</div>
	</div>
	<div id="orderDiaryRecordList">
		<!-- <div class="orderDiaryRecordBlock">
		<div class="orderDiaryRecordLeft">
		<div class="RecordName">收件人：123</div><br>
		<div class="RecordPhone">電　話：09811</div><br>
		<div class="RecordAddress">地　址：*************************************************************</div><br>
		<div class="RecordQuantity">數　量：**</div><br>
		<div class="RecordMoney">金　額：**</div><br>
		<div class="RecordStatus">狀　態：**</div>
		</div>
		<div class="RecordOpt"><a href="javascript:void(0)" class="cancelOrderDiary">取消裝訂</a></div>
		<div class="orderDiaryRecordRight">
		<div class="RecordDiaryName">12312312323123123123123123123123123</div>
		</div>
		</div> -->
	</div>
</div>

<div id="orderDiaryBlock">
	<div class="title">
		<div>
			日誌裝訂
		</div>
	</div>
	<div id="userInfoBlock">
		<div>
			收件人：
			<input id="orderName" type="text" />
		</div>
		<div>
			電　話：
			<input id="orderPhone" type="tel" />
		</div>
		<div>
			地　址：
			<input id="orderAddress" type="text" />
		</div>
		<div>
			數　量：
			<input id="orderQuantity" type="number" value=1 min=1 />
		</div>
	</div>
	日誌選擇
	<div id="diarySelect"></div>
	<div id="orderDiaryMoney">
		金額：　100　元
	</div><a href="javascript:void(0)" id="submitOrderDiary">送　出</a>
</div>