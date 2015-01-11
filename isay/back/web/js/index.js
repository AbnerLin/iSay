$(function() {
	$('#announceManage').click(function() {
		$.ajax({
			url : '/controller.php',
			type : 'POST',
			dataType : 'json',
			async : false,
			data : {
				action : 'loadAnnounce',
			},
			success : function(responce) {
				alert(JSON.stringify(responce));
				// for (var i = 0; i < responce.content.length; i++) {
					// $('#right').append();
				// }
			}
		});	});

	$('#logout').click(function() {
		$.ajax({
			url : '/controller.php',
			type : 'POST',
			dataType : 'json',
			async : false,
			data : {
				action : 'logout',
			},
			success : function(responce) {
				if (responce) {
					window.location = ("/");
				}
			}
		});
	});
});
