$(function() {
	$(document).ready(function() {
		$('#regist_dialog').hide();
		$('#forget_dialog').hide();
		$('.show_ad').hide();

		var test = $('#left_down iframe');
		var i = 0;
		function autoShow() {
			$(test[i]).hide();
			i = (i + 1) % test.size();
			$(test[i]).fadeIn('slow');
			setTimeout(autoShow, 15000);
		}

		autoShow();
		history.pushState({
			page : 'login.html'
		}, 'login.html', 'login.html');
	});

	$('#regist_btn').click(function(e) {
		push_url(e, this);

		$('#status').html('');
		$('#login_dialog').hide();
		refresh_verify();
		$('#regist_dialog').fadeIn('slow');
	});
	$('#regist_cancel').click(function(e) {
		push_url(e, this);

		$('#status').html('');
		$('#regist_dialog').hide();
		refresh_verify();
		$('#login_dialog').fadeIn('slow');
	});

	$('#forget_passwd').click(function(e) {
		push_url(e, this);

		$('#status').html('請填入以下資訊，系統將新密碼寄至您的信箱。');
		$('#login_dialog').hide();
		refresh_verify();
		$('#forget_dialog').fadeIn('slow');
	});

	$('#forget_cancel').click(function(e) {
		push_url(e, this);

		$('#status').html('');
		$('#forget_dialog').hide();
		refresh_verify();
		$('#login_dialog').fadeIn('slow');
	});

	$('.verify_image').click(function() {
		refresh_verify();
	});
	$('.verify_dialog').click(function() {
		refresh_verify();
	});
	function refresh_verify() {
		$('.verify_image').attr('src', 'lib/verify/verify_image.php?' + Math.random());
	};


	$('#regist').click(function() {
		loader();
		$.ajax({
			url : 'controller.php',
			async : false,
			type : 'POST',
			datatype : 'text',
			data : {
				action : 'regist',
				username : $('#regist_username').val(),
				account : $('#regist_account').val(),
				email : $('#regist_email').val(),
				email_confirm : $('#regist_email_confirm').val(),
				password : $('#regist_password').val(),
				password_confirm : $('#regist_password_confirm').val(),
				id_number : $('#id_number').val(),
				verify : $('#regist_verify').val()
			},
			success : function(responce) {
				if(responce == true) {
					$('#status').html("註冊成功，請收信開通帳號並登入！");
					$('#regist_dialog').hide();
					$('#login_dialog').fadeIn('slow');
					var input_tag = $('#regist_dialog input');
					for(var i = 0; i < input_tag.size(); i++) {
						$(input_tag[i]).val("");
					}
				} else
					$('#status').html(responce);
			}
		});

	});

	$('#login').click(function() {
		loader();
		$.ajax({
			url : 'controller.php',
			async : false,
			type : 'POST',
			datatype : 'text',
			data : {
				action : 'login',
				account : $('#login_account').val(),
				password : $('#login_password').val(),
				verify : $('#login_verify').val()
			},
			success : function(responce) {
				if(responce == true) {
					window.location = "index.html";
				} else
					$('#status').html(responce);
			}
		});

	});

	$('#forget').click(function() {
		loader();
		$.ajax({
			url : 'controller.php',
			type : 'POST',
			datatype : 'text',
			data : {
				action : 'forget_passwd',
				account : $('#forget_account').val(),
				email : $('#forget_email').val(),
				id_number : $('#forget_id_number').val(),
				verify : $('#forget_verify').val()
			},
			success : function(responce) {
				if(responce == true) {
					$('#status').html('請至信箱收取新密碼。');
					$('#forget_dialog').hide();
					$('#login_dialog').fadeIn('slow');
					var input_tag = $('#forget_dialog input');
					for(var i = 0; i < input_tag.size(); i++) {
						$(input_tag[i]).val("");
					}
				} else
					$('#status').html(responce);
			}
		});

	});
	function loader() {
		$('#status').html('<img src="/css/images/loader.gif" class="loader"/> 資料處理中...');
	};

	function push_url(e, obj) {
		e.preventDefault();
		var url = $(obj).attr("href");
		history.pushState({
			page : url
		}, url, url);
	}


	window.onpopstate = function(event) {
		var tmp = event.state;
		switch(tmp.page) {
			case 'login.html':
				$('#status').html('');
				$('#regist_dialog').hide();
				$('#forget_dialog').hide();
				$('#login_dialog').fadeIn('slow');
				break;
			case 'regist.html':
				$('#status').html('');
				$('#login_dialog').hide();
				$('#forget_dialog').hide();
				$('#regist_dialog').fadeIn('slow');
				break;
			case 'forget_passwd.html':
				$('#status').html('請填入以下資訊，系統將新密碼寄至您的信箱。');
				$('#login_dialog').hide();
				$('#regist_dialog').hide();
				$('#forget_dialog').fadeIn('slow');
				break;
		}
	};
});
