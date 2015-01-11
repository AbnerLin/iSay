$(function() {
	$('#login').click(function() {
		$.ajax({
			url : '/controller.php',
			type : 'POST',
			dataType : 'json',
			async : false,
			data : {
				action : 'login',
				account : $('#accountInput').val(),
				password : $('#passwordInput').val()
			},
			success : function(responce) {
				if (responce.status) {
					window.location = ("/");
				} else {
					$('#status').html(responce.content);
				}
			}
		});
	});
});
