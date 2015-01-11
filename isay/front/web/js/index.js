$(function() {
	$(document).ready(function() {
		$(window).unload(function() {
			clearBuff();
		});
		init();		/* change inner's page */
		// 1. clear_image()
		// 2. clear_music()
		// alert("123");
	});
	function init() {
		setAnnonuce();
		setPostCardInfo();

		if (checkUser() == false)
			return 0;

		switch(currentPage) {
			case 'diary':
				if ( typeof (diaryId) != "undefined" && diaryId !== null)
					loadDetailDiary();
				else
					load_diaryPage();
				break;
			case 'info':
				loadInfoPage();
				break;
			case 'friend':
				loadFriendPage();
				break;
			case 'orderDiary':
				loadOrderDiary();
				break;
			case 'editDiary':
				if ( typeof (diaryId) != "undefined" && diaryId !== null)
					loadEditDiary();
				else
					errorPage();
				break;
			default:
				errorPage();
				break;
		}
		loadHeader();
		getNewReply();
		mostView();
		replyLongPolling();
		checkFriendRequest();
	}

	function checkFriendRequest() {
		$.ajax({
			url : '/controller.php',
			type : 'POST',
			dataType : 'json',
			data : {
				action : 'checkFriendRequest',
				account : account
			},
			success : function(responce) {
				if (responce.status) {
					$('#friendOpt').append("<span id=\"friendRequestHint\">您有 " + responce.content + " 個好友邀請！</span>");
				}
			}
		});
	}

	function replyLongPolling() {
		$.ajax({
			url : '/controller.php',
			type : 'POST',
			dataType : 'json',
			data : {
				action : 'replyLongPolling',
				account : account
			},
			success : function(responce) {
				if (responce.status) {
					for (var i = 0; i < responce.content.length; i++)
						$('#newReply').prepend('<div><div class="newReplyTitle"><a href="/' + account + '/diary/' + responce.content[i].id + '">RE:　' + responce.content[i].title + '</a></div><div class="newReplyInfo">By　<a href="/' + responce.content[i].account + '" >' + responce.content[i].username + '</a>　' + $.format.date(responce.content[i].time, "MMM d h:mm a") + '</div></div>');
					$('#newReply > div:first').hide();
					if ($('#newReply > div').size() > 15)
						$('#newReply > div:last').fadeOut('1000', function() {
							$('#newReply > div:last').remove();
						});
					$('#newReply > div:first').slideDown();
					$('#newReply .noData').remove();				}				replyLongPolling();
			}
		});
	}

	function mostView() {
		$.ajax({
			url : '/controller.php',
			type : 'POST',
			dataType : 'json',
			data : {
				action : 'mostView',
				account : account
			},
			success : function(responce) {
				if (responce.status)
					for (var i = 0; i < responce.content.length; i++)
						$('#mostView').append('<div class="mostViewBlock">' + (i + 1) + '.<a href="/' + account + '/diary/' + responce.content[i].id + '">' + responce.content[i].title + '</a></div>');
				else
					noData($('#mostView'));
			}
		});
	}

	function getNewReply() {
		$.ajax({
			url : '/controller.php',
			type : 'POST',
			dataType : 'json',
			data : {
				action : 'getNewReply',
				account : account
			},
			success : function(responce) {
				if (responce.status) {
					for (var i = 0; i < responce.content.length; i++)
						$('#newReply').append('<div><div class="newReplyTitle"><a href="/' + account + '/diary/' + responce.content[i].id + '">RE:　' + responce.content[i].title + '</a></div><div class="newReplyInfo">By　<a href="/' + responce.content[i].account + '" >' + responce.content[i].username + '</a>　' + $.format.date(responce.content[i].time, "MMM d h:mm a") + '</div></div>');
				} else
					noData($('#newReply'));
			}
		});
	}

	function clearBuff() {
		clear_image();
		clear_music();
		if (longPolling.polling != undefined)
			longPolling.polling.abort();
	}

	function setAnnonuce() {
		$.ajax({
			url : '/controller.php',
			type : 'POST',
			dataType : 'json',
			data : {
				action : 'setAnnonuce'
			},
			success : function(responce) {
				if (responce.status == true) {
					for ( i = 0; i < responce.content.length; i++)
						$('#announce').append('<div>' + responce.content[i].content + '</div>');
					$('#announce').cycle({
						fx : 'scrollUp',
						speed : 1000,
						timeout : 5000,
						// next : '#announce',
						pause : 1
					});

				}
			}
		});
	}

	function setPostCardInfo() {
		$.ajax({
			url : '/controller.php',
			type : 'POST',
			dataType : 'json',
			data : {
				action : 'setPostCardInfo'
			},
			success : function(responce) {
				if (responce.status == true) {
					if (responce.content.bigHeadImg != null) {
						$('#optionInfo img').attr('src', responce.content.bigHeadImg);
						$('#postCardHead').css('background-image', 'url("' + responce.content.bigHeadImg + '")');
					}
					$('.postCardName').html(responce.content.username);
					$('#postCardEmail').text(responce.content.email);
				}
			}
		});
	}

	function checkUser() {
		$.ajax({
			url : '/controller.php',
			type : 'POST',
			dataType : 'json',
			data : {
				action : 'checkUser',
				account : account
			},
			success : function(responce) {
				// alert(JSON.stringify(responce));				if (responce.status) {
					if (responce.check) {
						$('#friendOpt').append('<a href="javascript:void(0)" class="unBlockUser">解除封鎖</a>');
						$('.blockUser').remove();
					}
					if (responce.isFriend) {
						$('.addUser').remove();
						switch(responce.isFriend) {
							case '0':
								$('#friendOpt').prepend('<a href="javascript:void(0)" class="unAddUser">取消好友邀請</a>');
								break;
							case '1':
								$('#friendOpt').prepend('<a href="javascript:void(0)" class="delUser">刪除好友</a>');
								break;
							case '2':
								$('#friendOpt').prepend('<a href="javascript:void(0)" class="allowUser">答應好友邀請</a>');
								break;
						}
					}
					return true;
				} else {
					errorPage();
					return false;
				}
			}
		});
	}

	function loadHeader() {
		$.ajax({
			url : '/controller.php',
			type : 'POST',
			dataType : 'json',
			data : {
				action : 'loadHeader',
				account : account
			},
			success : function(responce) {
				if (responce.status == true) {
					$('#headerBG').css('background-image', 'url(' + responce.content + ')');
					$('#headerImgRemove').show();
				}
			}
		});
	}


	$(document).click(function(e) {
		var elemId = $(e.target).attr('id');
		var elemClass = $(e.target).attr('class');
		if (elemId == "geocoder-input" || elemId == "geocoder-icon") {
			geocoder_places("");
		} else if (elemId != "geocoder-places")
			$('#geocoder-places').hide();

		if (elemId != "editHeader" && elemId != "header" && elemId != "headerBG") {
			$('#headerForm').hide();
			$('#editHeader').hide();
			$('#header').bind('mouseleave', function() {
				$('#editHeader').hide();
			});
		}

		if ($(e.target).parents('#postCard').length == 0 && $(e.target).parents('#optionInfo').length == 0 && elemClass != "edit")
			$('#postCard').hide();
	});

	$('#diaryBtn').click(function() {
		window.location = ("/" + account + "/diary");
	});

	$('#logout').click(function() {
		clearBuff();
		$.ajax({
			url : '/controller.php',
			type : 'POST',
			datatype : 'text',
			data : {
				action : 'logout'
			},
			success : function(responce) {
				if (responce == true)
					window.location = "/login.php";
			}
		});
	});

	$('#cancel_geocoder').live('click', function() {
		$('#geocoder-input').val('隱藏所在位置。               ');
	});

	function geocoder() {
		$('#geocoder-input').val('定位中...               ');
		if (navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(function showPosition(position) {
				var lat = position.coords.latitude;
				var lng = position.coords.longitude;
				var latlng = new google.maps.LatLng(lat, lng);
				var geocoder = new google.maps.Geocoder();
				geocoder.geocode({
					'latLng' : latlng
				}, function(results, status) {
					if (status == google.maps.GeocoderStatus.OK) {
						if (results[1]) {
							$('#geocoder-input').val(results[1].address_components[2].long_name);
							$('#geocoder-input').css('color', '#3B5998');
						}
					} else {
						$('#geocoder-input').val('無法取得位置。               ');
					}
				});
			});
		}
	}

	function geocoder_places(keyword) {
		$('#geocoder-places').show();
		$('#geocoder-ul').children('ul').html('');
		navigator.geolocation.getCurrentPosition(function showPosition(position) {
			var lat = position.coords.latitude;
			var lng = position.coords.longitude;
			var latlng = new google.maps.LatLng(lat, lng);
			map = new google.maps.Map(document.getElementById('map'), {
				mapTypeId : google.maps.MapTypeId.ROADMAP,
				center : latlng,
				zoom : 15
			});

			var marker = new google.maps.Marker({
				position : latlng,
			});
			marker.setMap(map);

			var request = {
				keyword : keyword,
				location : latlng,
				radius : '10000',
				types : ['store', 'park', 'airport', 'amusement_park', 'aquarium', 'bar', 'beauty_salon', 'book_store', 'bowling_alley', 'cafe', 'campground', 'clothing_store', 'convenience_store', 'dentist', 'department_store', 'fire_station', 'florist', 'food', 'furniture_store', 'gas_station', 'grocery_or_supermarket', 'gym', 'hair_care', 'home_goods store', 'hospital', 'laundry', 'library', 'liquor_store', 'lodging', 'movie_rental', 'movie_theater', 'museum', 'night_club', 'parking', 'pet_store', 'pharmacy', 'police', 'post_office', 'restaurant', 'school', 'shoe_store', 'shopping_mall', 'stadium', 'storage', 'synagogue', 'train_station', 'travel_agency', 'university', 'veterinary care', 'zoo']
			};
			service = new google.maps.places.PlacesService(map);
			service.search(request, function callback(results, status) {
				if (status == google.maps.places.PlacesServiceStatus.OK) {
					$('#geocoder-status').html('');
					for ( i = 0; i < results.length; i++) {
						$('#geocoder-ul').children('ul').append('<li latlng="' + results[i].geometry.location + '"><img src="' + results[i].icon + '" /><span>' + results[i].name + ' </span> - ' + results[i].vicinity + '</li>');

					}
				} else if (status == google.maps.places.PlacesServiceStatus.ZERO_RESULTS) {
					$('#map').hide();
					$('#geocoder-status').html('共有 0 筆資料。');
					$('#geocoder-ul').children('ul').html('');
				}
			});
		});
	}


	$('#geocoder-ul ul li').live('mouseover mouseout', function(event) {
		if (event.type == 'mouseover') {
			$('#map').show();
			var tmp = $(this).attr('latlng').replace("(", "").replace(")", "").replace(" ", "").split(",");

			var latlng = new google.maps.LatLng(tmp[0], tmp[1]);
			map = new google.maps.Map(document.getElementById('map'), {
				mapTypeId : google.maps.MapTypeId.ROADMAP,
				center : latlng,
				zoom : 15
			});

			var marker = new google.maps.Marker({
				position : latlng,
			});
			marker.setMap(map);
		} else
			$('#map').hide();
	});

	$('#geocoder-places li').live('click', function() {
		$('#geocoder-input').val($(this).children('span').html());
	});

	$('#geocoder-input').live('change', function() {
		geocoder_places($('#geocoder-input').val());
	});

	$('#permission').live('mouseover mouseout', function(event) {
		if (event.type == "mouseover")
			$('.permission_icon').css('opacity', '1');
		else
			$('.permission_icon').css('opacity', '0.5');
	});

	$('#img-input').live('change', function() {
		$("#image-form").ajaxForm({
			dataType : 'json',
			success : function(responce) {
				$('#img-input').val('');
				if (responce.status == true) {
					status(true, "相片處理中，請稍候... 100%");
					$('#preview').show();
					for ( i = 0; i < responce.content.length; i++) {
						$('#preview').append("<div class=\"preview_block\"><img class=\"preview_img\" src=\"" + responce.content[i].src + "\" name=\"" + responce.content[i].name + "\" tmp_name=\"" + responce.content[i].tmp_name + "\" /><span class=\"cancel cancel_image\"></span></div>")
					}
					// $('#status').fadeOut();
				} else {
					status(true, responce.content);
				}
			},
			uploadProgress : function(event, position, total, percentComplete) {
				status(false, "相片處理中，請稍候... " + (percentComplete - 1) + "%");
			}
		}).submit();
	});

	$('.cancel_image').live('click', function() {
		var $parent = $(this).parent();
		del_image($(this).parent().find('img').attr('tmp_name'));
		$parent.remove();
	});

	function del_image(tmp_name) {
		$.ajax({
			url : '/controller.php',
			type : 'POST',
			datatype : 'text',
			data : {
				action : 'cancel_image',
				image : tmp_name
			},
			success : function(responce) {
				preview_count();
			}
		});
	}

	function preview_count() {
		if ($('#preview').children().length == 0)
			$('#preview').hide();
	}

	function clear_image() {
		if ($('#preview').children().length != 0) {
			$('.preview_img').each(function() {
				$.ajax({
					url : '/controller.php',
					async : false,
					type : 'POST',
					datatype : 'text',
					data : {
						action : 'cancel_image',
						image : $(this).attr('tmp_name')
					}
				});
			});
		}
	}

	function status(isHide, str) {
		$('#status #statusText').html(str);
		$('#status').hide().show();
		if (isHide)
			setTimeout(function() {
				$('#status').fadeOut('slow');
			}, 4000);
	}


	$('#absolute_back').click(function(event) {
		var elem = $(event.target).attr('id');
		if (elem == "absolute_back") {
			$(this).hide();
			window.document.body.style.overflow = 'auto';
		}
	});

	$('.preview_block').live('click', function(event) {
		var elem = $(event.target).attr('class');
		if (elem == "preview_block" || elem == "preview_img" || elem == "editImage") {
			$('#stage').find('img').attr('src', $(this).find('img').attr('src'));
			$('#stage').find('img').attr('tmp_name', $(this).find('img').attr('tmp_name'));
			window.document.body.style.overflow = 'hidden';
			get_imginfo($(this).find('img').attr('tmp_name'));
			$('#absolute_back').show();
		}
	});

	function get_imginfo(tmp_name) {
		$.ajax({
			url : '/controller.php',
			type : 'POST',
			dataType : 'json',
			data : {
				action : 'get_imginfo',
				tmp_name : tmp_name
			},
			success : function(responce) {
				$('#image-name').val(responce.name);
				$('#image-content').val(responce.content);
				$('#image-content').css('color', '#000');
				$('#image-name').css('color', '#000');
				$('#image-name').Watermark('請輸入相片名稱。               ', "#d3d3d3");
				$('#image-content').Watermark('請輸入相片敘述。               ', "#d3d3d3");
			}
		});
	}


	$('#image-del').click(function() {
		del_image($('#stage').find('img').attr('tmp_name'));
		var tmp = $('.preview_block');
		for ( i = 0; i < tmp.length; i++) {
			if ($(tmp[i]).find('img').attr('tmp_name') == $('#stage').find('img').attr('tmp_name')) {
				$(tmp[i]).remove();
				break;
			}
		}
		$('#absolute_back').hide();
		window.document.body.style.overflow = 'auto';

	});

	$('#image-setting #okbtn').click(function() {
		$.ajax({
			url : '/controller.php',
			type : 'POST',
			dataType : 'json',
			data : {
				action : 'set_imginfo',
				tmp_name : $('#stage').find('img').attr('tmp_name'),
				name : $('#image-name').val(),
				content : $('#image-content').val()
			},
			success : function(responce) {
				if (responce.status == false)
					status(true, responce.content);
				else
					status(true, "資料已儲存。");
			}
		});
	});

	$('#music-input').live('change', function() {
		$("#music-form").ajaxSubmit({
			dataType : 'json',
			success : function(responce) {
				$('#music-input').val('');
				if (responce.status == true) {
					status(true, "音樂處理中，請稍候... 100%");
					$('.music_player').show();
					$('.music_player').attr('src', responce.content.src)[0];
					$('.music_player').attr('name', responce.content.name)[0];
					$('.music_player').attr('tmp_name', responce.content.tmp_name)[0];
					$('.music_player')[0].play();
					$('#music_uploadBtn').hide();
					$('#cancel_music').show();
				} else {
					status(true, responce.content);
				}
			},
			uploadProgress : function(event, position, total, percentComplete) {
				status(false, "音樂處理中，請稍候... " + (percentComplete - 1) + "%");
			}
		});
	});

	function clear_music() {
		if ($('.music_player').attr('tmp_name') == undefined)
			return 0;
		if (!$('.music_player').hasClass("old"))
			del_music($('.music_player').attr('tmp_name'));
	}

	function del_music(tmp_name) {
		$.ajax({
			url : '/controller.php',
			type : 'POST',
			async : false,
			datatype : 'text',
			data : {
				action : 'cancel_music',
				music : tmp_name
			},
			success : function(responce) {
				preview_count();
			}
		});
	}


	$('#cancel_music').live('click', function() {
		del_music($('.music_player').attr('tmp_name'));
		$('.music_player').removeAttr("src");
		$('.music_player').removeAttr("name");
		$('.music_player').removeAttr("tmp_name");
		$(this).hide();
		$('.music_player').hide();
		$('#music_uploadBtn').show();
	});

	$('#share-diary #submit').live('click', function() {
		var image = new Array();
		var music = null;
		$('.preview_img').each(function() {
			image.push($(this).attr('tmp_name'));
		});
		if (image.length == 0)
			image = null;
		if ($('.music_player').attr('tmp_name') != undefined) {
			music = $('.music_player').attr('tmp_name');
		} else
			music = null;

		$.ajax({
			url : '/controller.php',
			type : 'POST',
			dataType : 'json',
			data : {
				action : 'new_diary',
				title : $('#diary-title').val(),
				content : $('#diary-content').val(),
				geocoder : $('#geocoder-input').val(),
				image : image,
				music : music,
				permission : $('#permission-select').val()
			},
			success : function(responce) {
				if (responce.status == false)
					status(true, responce.content);
				else {
					longPolling.polling.abort();
					load_diaryPage();
				}
			}
		});
	});
	function load_diaryPage() {
		$('#content').load('/inner/diary.php', function() {
			$('#diary-title').Watermark('Title              ', "#d3d3d3");
			$('#diary-content').Watermark('Say Something ...               ', "#d3d3d3");
			$('#diary-content').autosize();
			$('#geocoder-input').Watermark('請輸入所在位置。               ', "#d3d3d3");
			$('#diarySearchInput').Watermark('請輸入日誌關鍵字。 　　　　　', '#d3d3d3');
			$('#content').prepend('<div id="diary-manage"></div>');
			//$('#diary-manage').append('<a href="javascript:void(0)" id="show_diary_input">新增日誌</a>').hide().slideDown();
			load_diary(0);
			longPolling();
		});
		$('#content').hide().fadeIn('slow');
		$('#diaryBtn').css('opacity', '1');
	}

	function loadDetailDiary() {
		$('#content').load('/inner/detailDiary.php', function() {
			/* 1. check diaryID */
			$.ajax({
				url : '/controller.php',
				type : 'POST',
				dataType : 'json',
				data : {
					action : 'loadDetailDiary',
					diaryId : parseInt(diaryId),
					account : account
				},
				success : function(responce) {
					if (responce.status) {
						$('#detailDiaryTitle span').html(responce.diaryContent.title);
						$('#detailDiaryTime').html($.format.date(responce.diaryContent.time, "MMMM dd, yyyy - ddd h:mm a"));

						$('#detailDiaryContent').html(responce.diaryContent.content ? responce.diaryContent.content.replace(/\n/g, "<br>") : "");

						var place = responce.diaryContent.place ? "於 " + responce.diaryContent.place + " 發文     |     " : ""
						var diaryImg = responce.diaryImg ? responce.diaryImg.length : 0;

						if (responce.diaryContent.permission) {
							switch(responce.diaryContent.permission) {
								case '0':
									var permissionStr = "瀏覽權限：本人";
									break;
								case '1':
									var permissionStr = "瀏覽權限：好友";
									break;
								case '2':
									var permissionStr = "瀏覽權限：公開";
									break;
							}
							$('#detailDiaryTitle').append("<div class=\"diaryOpt\" id=\"" + diaryId + "\"><a href=\"javascript:void(0)\" class=\"delDetailDiary btn\">刪除日誌</a><a href=\"/" + account + "/" + "editDiary" + "/" + diaryId + "\" class=\"editDiary btn\">編輯日誌</a></div>");
						} else
							permissionStr = "";
						$('#detailDiaryInfo').html("　　　瀏覽次數： " + responce.diaryContent.diaryCount + " <br>" + responce.userInfo.username + "(" + responce.userInfo.account + ")　" + place + "相片(" + diaryImg + ")     |     " + "回覆(" + responce.replyCount + ")");
						$('#detailDiaryInfo').prepend('<span class="diaryPermission">' + permissionStr + '</span>');						if (responce.diaryImg) {
							for ( i = 0; i < responce.diaryImg.length; i++) {
								$('#detailDiarySlideShow > #SlideShow-clip > ul').append("<li><div class=\"detailDiarySlideShowBlock\"></div></li>");								var slideImg = $('.detailDiarySlideShowBlock');
								$(slideImg[i]).css('background-image', 'url("' + responce.diaryImg[i].i_path + '")');
								$(slideImg[i]).attr('name', responce.diaryImgInfo[i].name);
								$(slideImg[i]).attr('content', responce.diaryImgInfo[i].content);
							}
							var auto = 0;
							var scroll = 0;
							var visible = responce.diaryImg.length;
							if (responce.diaryImg.length > 4) {
								auto = 5000;
								scroll = 2;
								visible = 4;
							} else
								$('#SlideShow-prev, #SlideShow-next').hide();
							$("#SlideShow-clip").jCarouselLite({
								auto : auto, // 0
								speed : 1000,
								scroll : scroll, // 0
								visible : visible,
								btnNext : '#SlideShow-next',
								btnPrev : '#SlideShow-prev'
							});						} else
							$('#detailDiarySlideShow').hide();

						if (responce.diaryContent.music_path) {
							$('#detailDiaryMusic').show();
							$('#detailDiaryMusic audio').attr('src', responce.diaryContent.music_path)[0];

							setTimeout(function() {
								$('#detailDiaryMusic audio')[0].play();
								$('#detailDiaryMusic').animate({
									opacity : 0.3
								}, function() {
									$('#detailDiaryMusic').hover(function() {
										$(this).css('opacity', 1);
									}, function() {
										$(this).animate({
											opacity : 0.3
										});
									});
								});							}, 3000);						}
						initReply();					} else
						errorPage();

				}
			});		});

		$('#content').hide().fadeIn('slow');
		$('#menu span').css('opacity', '0.5');
		$('#diaryBtn').css('opacity', '1');
	}

	function initReply() {
		$('#detailDiaryReply .detailDiaryReplyHead').css('background-image', $('#postCardHead').css('background-image'));		$('#detailDiaryReplyInput').Watermark('留言...               ', "#d3d3d3");
		$('#detailDiaryReplyInput').autosize();
		$.ajax({
			url : '/controller.php',
			type : 'POST',
			dataType : 'json',
			data : {
				action : 'loadReply',
				diaryId : parseInt(diaryId)
			},
			success : function(responce) {
				if (!responce.status)
					$('#detailDiaryReplyPage').hide();
				else {
					// alert(responce.content.length);
					$('#detailDiaryReplyPage').html('');					for (var i = 0; i < responce.content.length; i++) {
						$('#detailDiaryReplyPage').append('<div class="detailDiaryReplyBlock"><div class="detailDiaryReplyHead"></div><div class="detailDiaryReplyBlockRight"><div class="detailDiaryReplyContent"></div><div class="detailDiaryReplyInfo"></div><div class="detailDiaryReplyOpt"></div></div></div>');
						if (responce.content[i].userInfo.bigHeadImg)
							$($('.detailDiaryReplyHead')[i]).css('background-image', 'url(' + responce.content[i].userInfo.bigHeadImg + ')');
						if (responce.content[i].replyContent.content)							$($('.detailDiaryReplyContent')[i]).html((responce.content[i].replyContent.content).replace(/\n/g, "<br>"));
						else
							$($('.detailDiaryReplyContent')[i]).html("悄悄話").css('font-style', 'italic');
						$($('.detailDiaryReplyInfo')[i]).html('<a class="detailDiaryReplyUserAccount" account="' + responce.content[i].userInfo.account + '" href="/' + responce.content[i].userInfo.account + '">' + responce.content[i].userInfo.username + '(' + responce.content[i].userInfo.account + ')</a>　<span class="detailDiaryReplyTime" time="' + responce.content[i].replyContent.time + '">' + $.format.date(responce.content[i].replyContent.time, "MMMM dd, yyyy - ddd h:mm a") + '</span>');

						if (responce.content[i].isUser)
							$($('.detailDiaryReplyOpt')[i]).html('<a href="javascript:void(0)" class="detailDiaryReplyEdit">編輯</a><a href="javascript:void(0)" class="detailDiaryReplyDel">刪除</a>');
						else if (responce.isHome)
							$($('.detailDiaryReplyOpt')[i]).html('<a href="javascript:void(0)" class="detailDiaryReplyDel">刪除</a>');
						else
							$($('.detailDiaryReplyOpt')[i]).hide();
					}
					setReplyPaging();
					replyPaging(0);
				}
			}
		});	}

	function setReplyPaging(currentPage) {
		/* paging */
		var length = $('.detailDiaryReplyBlock').size();
		$('#detailDiaryReplyPaging').html('');
		if (length > 5) {
			$('#detailDiaryReplyPaging').append('<span id="replyPagePre" class="pageBtn">Prev</span>');
			for (var i = 1; i <= Math.ceil(length / 5); i++) {
				$('#detailDiaryReplyPaging').append('<span>' + i + '</span>');
			}
			$('#detailDiaryReplyPaging').append('<span id="replyPageNxt" class="pageBtn">Next</span>');
			$($('#detailDiaryReplyPaging span')[1]).attr('class', 'current');
			$('#detailDiaryReplyPaging').show();
		}

	}


	$('#replyPagePre').live('click', function() {
		if ($('#detailDiaryReplyPaging .current').html() > 1)
			replyPaging($('#detailDiaryReplyPaging .current').html() - 2);
	});

	$('#replyPageNxt').live('click', function() {
		if ($('#detailDiaryReplyPaging .current').html() < $('#detailDiaryReplyPaging span').size() - 2)
			replyPaging(parseInt($('#detailDiaryReplyPaging .current').html()));
	});

	function replyPaging(limit) {
		$('.detailDiaryReplyBlock').hide();
		for (var i = limit * 5; i < limit * 5 + 5; i++) {
			$($('.detailDiaryReplyBlock')[i]).show();
		}

		$('#detailDiaryReplyPaging span').css({
			color : 'blue',
			fontSize : '16px',
			cursor : 'pointer',
			border : '0px'
		});

		$('#replyPagePre, #replyPagenxt').css({
			color : 'blue',
			cursor : 'pointer'
		});

		$($('#detailDiaryReplyPaging span')[limit + 1]).css({
			color : '#000',
			fontSize : '18px',
			cursor : 'default',
			border : '1px solid #000'
		});

		$('#detailDiaryReplyPaging span').removeClass('current');
		$($('#detailDiaryReplyPaging span')[limit + 1]).attr('class', 'current');
		if (limit == 0)
			$('#replyPagePre').css({
				color : '#999',
				cursor : 'default'
			});
		if (limit == ($('#detailDiaryReplyPaging span').size() - 3))
			$('#replyPageNxt').css({
				color : '#999',
				cursor : 'default'
			});
		$('#detailDiaryReplyPage').hide().fadeIn();
	}


	$('#detailDiaryReplyPaging span').live('click', function() {
		if ($(this).attr('id') == undefined) {
			replyPaging($(this).html() - 1);
		}
	});

	$('.detailDiaryReplyEdit').live('click', function() {
		var replyContent = $(this).parents('.detailDiaryReplyBlock').find('.detailDiaryReplyContent').html();
		var parents = $(this).parents('.detailDiaryReplyBlock');
		$(parents).find('.detailDiaryReplyBlockRight').prepend('<textarea>' + replyContent.replace(/<br>/g, "\n") + '</textarea>');
		$(parents).find('textarea').focus();
		$(parents).find('textarea').autosize();
		$(parents).find('.detailDiaryReplyContent').hide();
		$(parents).find('.detailDiaryReplyOpt .detailDiaryReplyEdit').hide();
		$(parents).find('.detailDiaryReplyOpt').prepend('<a href="javascript:void(0)" class="detailDiaryReplyOk">完成</a>');		$(parents).find('.detailDiaryReplyOpt').prepend("<span>ESC 取消編輯</span>");

		$(parents).css({
			background : '#EDEFF4',
			border : '1px dashed #000'
		});
		$('.detailDiaryReplyBlock textarea').live('keyup', function(e) {
			if (e.keyCode == 27) {
				$(parents).find('.detailDiaryReplyContent, .detailDiaryReplyOpt .detailDiaryReplyEdit').show();

				$(parents).find('textarea, .detailDiaryReplyOk').hide();
				$(parents).find('.detailDiaryReplyOpt span').remove();
				$(parents).css({
					background : 'transparent',
					border : '0px',
					borderBottom : '1px solid #999'
				});
			}
		});
	});

	$('.detailDiaryReplyOk').live('click', function() {
		var parents = $(this).parents('.detailDiaryReplyBlock');
		if (($(parents).find('textarea').val()).replace(/\n/g, "<br>") != $(parents).find('.detailDiaryReplyContent').html()) {
			$.ajax({
				url : '/controller.php',
				type : 'POST',
				dataType : 'json',
				data : {
					action : 'editReply',
					diaryId : parseInt(diaryId),
					time : $(parents).find('.detailDiaryReplyTime').attr('time'),
					replyContent : $(parents).find('textarea').val()
				},
				success : function(responce) {
					if (responce) {
						$(parents).find('.detailDiaryReplyContent').html($(parents).find('textarea').val().replace(/\n/g, "<br>"));
						$(parents).find('textarea').remove();
						$(parents).find('.detailDiaryReplyContent, .detailDiaryReplyOpt .detailDiaryReplyEdit').show();
						$(parents).find('.detailDiaryReplyOk').hide();
						$(parents).find('.detailDiaryReplyOpt span').remove();
						$(parents).css({
							background : 'transparent',
							border : '0px',
							borderBottom : '1px solid #999'
						});
					} else {
						status(true, "系統發生問題，請稍候在試。");
					}
				}
			});
		}
	});

	$('.detailDiaryReplyDel').live('click', function() {
		// alert($('.detailDiaryReplyBlock').size());		var parents = $(this).parents('.detailDiaryReplyBlock');
		$.ajax({
			url : '/controller.php',
			type : 'POST',
			dataType : 'json',
			data : {
				action : 'delReply',
				diaryId : parseInt(diaryId),
				time : $(parents).find('.detailDiaryReplyTime').attr('time'),
				account : $(parents).find('.detailDiaryReplyUserAccount').attr('account')
			},
			success : function(responce) {
				if (responce) {
					$(parents).remove();
					if ($('.detailDiaryReplyBlock').size() % 5 == 0) {
						replyPaging($('#detailDiaryReplyPaging span').size() - 4);
						setReplyPaging();
					}
				} else {
					status(true, "系統發生問題，請稍候在試。");
				}
			}
		});
	});

	function load_diary(limit) {
		$('#diaryLoading img').show();
		// load_diary.loader = true;		$.ajax({
			url : '/controller.php',
			type : 'POST',
			dataType : 'json',
			data : {
				action : 'load_diary',
				account : account,
				limit : limit
			},
			success : function(responce) {
				if (responce.status == true) {
					for ( i = 0; i < responce.content.length; i++) {
						var place = "";
						if (responce.content[i].place != null)
							place = "於 " + responce.content[i].place + " 發文     |     ";

						if (responce.content[i].content != null)
							responce.content[i].content = (responce.content[i].content).replace(/\n/g, "<br>");
						else
							responce.content[i].content = "";

						if (responce.content[i].permission) {
							switch(responce.content[i].permission) {
								case '0':
									var permissionStr = "瀏覽權限：本人";
									break;
								case '1':
									var permissionStr = "瀏覽權限：好友";
									break;
								case '2':
									var permissionStr = "瀏覽權限：公開";
									break;
							}
						} else
							permissionStr = "";

						$('#diary-list').append("<div class=\"diary-block\"><div class=\"date\">" + responce.content[i].date + "</div><div class=\"title\">" + responce.content[i].title + "</div><div class=\"content-border\"><div class=\"content\">" + responce.content[i].content + "</div><div class=\"contentImageSlideShow\"></div></div><div class=\"extend\"><span class=\"diaryPermission\">" + permissionStr + "</span><a href=\"/" + account + "/diary/" + responce.content[i].id + "\">(詳全文)</a></div><div class=\"detail\">" + responce.userInfo.username + "(" + responce.userInfo.account + ")　" + place + "相片(" + responce.content[i].img_count + ")     |     " + "回覆(" + responce.content[i].reply_count + ")" + "</div></div>");

						if (responce.content[i].img_count > 0) {
							var contentBorder = $('.content-border');
							$(contentBorder[limit + i]).children('.content').css('width', '70%');

							for ( j = 0; j < responce.content[i].img_count; j++) {
								var slideShow = $(contentBorder[limit + i]).children('.contentImageSlideShow');
								$('<div></div>').css('background-image', 'url("' + responce.content[i].imgArray[j] + '")').appendTo(slideShow);
							}

							if (responce.content[i].img_count > 1) {
								$(contentBorder[limit + i]).children('.contentImageSlideShow').children('div:first').addClass('active');

								var now = new Date();
								var id = now.getHours().toString() + now.getMinutes().toString() + now.getSeconds().toString() + now.getMilliseconds().toString();
								$(contentBorder[limit + i]).children('.contentImageSlideShow').attr('id', id);
								$('#' + id.toString()).cycle({
									fx : 'fade',
									speed : 1000,
									timeout : (Math.floor(Math.random() * 5) + 3) * 1000,
									next : '#' + id.toString(),
									pause : 1
								});
							}

							$(contentBorder[limit + i]).children('.contentImageSlideShow').show();
						}
						if (responce.content[i].permission) {
							$($('.diary-block')[limit + i]).append("<div class=\"diaryOpt\" id=\"" + responce.content[i].id + "\"><a href=\"javascript:void(0)\" class=\"delDiary btn\">刪除日誌</a><a href=\"/" + account + "/" + "editDiary" + "/" + responce.content[i].id + "\" class=\"editDiary btn\">編輯日誌</a></div>");
						}
					}
					$(window).bind('scroll', function() {
						if ($(document).height() - $(window).height() == $(window).scrollTop()) {
							load_diary($('.diary-block').length);
						}
					});
				} else {
					if ($('.diary-block').size() == 0)
						noData($('#diary-list'));

					$(window).unbind('scroll');
				}
				$('#diaryLoading img').hide();
			}
		});
	}

	function noData(target) {
		$(target).append('<div class="noData">0　筆資料顯示。</div>').hide().fadeIn();
	}


	$('#show_diary_input').live('click', function() {
		$('#share-diary').slideDown();
		if (!$.browser.safari)
			$('#speech-input').hide();
		$(this).html("取消新增");
		$(this).attr('id', 'hide_diary_input');
		geocoder();
	});

	$('#hide_diary_input').live('click', function() {
		$('#share-diary').slideUp();
		if (!$.browser.safari)
			$('#speech-input').hide();
		$(this).html("新增日誌");
		$(this).attr('id', 'show_diary_input');
	});

	function longPolling() {
		longPolling.polling = $.ajax({
			url : '/controller.php',
			type : 'POST',
			dataType : 'json',
			data : {
				action : 'longPolling',
				account : account
			},
			success : function(responce) {
				if (responce.status == true) {
					for ( i = responce.content.length - 1; i >= 0; i--) {
						var place = "";
						if (responce.content[i].place != null)
							place = "於 " + responce.content[i].place + " 發文     |     ";
						if (responce.content[i].content != null)
							responce.content[i].content = (responce.content[i].content).replace(/\n/g, "<br>");
						else
							responce.content[i].content = "";
						$('#diary-list').prepend("<div class=\"diary-block\"><div class=\"date\">" + responce.content[i].date + "</div><div class=\"title\">" + responce.content[i].title + "</div><div class=\"content-border\"><div class=\"content\">" + responce.content[i].content + "</div><div class=\"contentImageSlideShow\"></div></div><div class=\"extend\"><a href=\"/" + account + "/diary/" + responce.content[i].id + "\" diary-id=\"" + responce.content[i].id + "\">(詳全文)</a></div><div class=\"detail\">" + place + "相片(" + responce.content[i].img_count + ")     |     " + "回覆(" + responce.content[i].reply_count + ")" + "</div></div>");
						var tmp = $('.diary-block');

						if (responce.content[i].img_count > 0) {
							var contentBorder = $('.content-border');
							$(contentBorder[0]).children('.content').css('width', '70%');

							for ( j = 0; j < responce.content[i].img_count; j++) {
								var slideShow = $(contentBorder[0]).children('.contentImageSlideShow');
								$('<div></div>').css('background-image', 'url("' + responce.content[i].imgArray[j] + '")').appendTo(slideShow);
							}

							if (responce.content[i].img_count > 1) {
								$(contentBorder[0]).children('.contentImageSlideShow').children('div:first').addClass('active');

								var now = new Date();
								var id = now.getHours().toString() + now.getMinutes().toString() + now.getSeconds().toString() + now.getMilliseconds().toString();
								$(contentBorder[0]).children('.contentImageSlideShow').attr('id', id);
								$('#' + id.toString()).cycle({
									fx : 'fade',
									speed : 1000,
									timeout : (Math.floor(Math.random() * 5) + 3) * 1000,
									next : '#' + id.toString(),
									pause : 1
								});
							}

							$(contentBorder[0]).children('.contentImageSlideShow').show();
						}
						$(tmp[0]).hide().slideDown('slow');
					}
				}
				longPolling();
			}
		});
	}


	$('#header-input').change(function() {
		$("#header-form").ajaxForm({
			dataType : 'json',
			success : function(responce) {
				$('#header-input').val('');
				if (responce.status == true) {
					status(true, "圖片處理中，請稍候... 100%");
					$('#headerBG').css('background-image', 'url("' + responce.content + '")');
					// $('#headerBG').attr('src', 'data:image/jpeg;base64,' + responce.content );
					$('#headerImgRemove').show();
				} else
					status(true, responce.content);
			},
			uploadProgress : function(event, position, total, percentComplete) {
				status(false, "圖片處理中，請稍候... " + (percentComplete - 1) + "%");
			}
		}).submit();
	});

	$('#header').hover(function() {
		$('#editHeader').show();
		if ($('#headerForm').css('display') != "block")
			$('#header').bind('mouseleave', function() {
				$('#editHeader').hide();
			});
	});

	$('#editHeader').click(function() {
		$('#headerForm').css('display', 'block');
		$('#header').unbind('mouseleave');
	});

	$('#headerImgRemove').click(function() {
		$.ajax({
			url : '/controller.php',
			type : 'POST',
			dataType : 'json',
			data : {
				action : 'headerImgRemove',
				account : account
			},
			success : function(responce) {
				if (responce.status == true) {
					$('#headerBG').css('background-image', 'url("/css/images/headerBG.gif")');
					$('#headerImgRemove').hide();
				}
			}
		});
	});

	$('#optionInfo, #option .edit').click(function() {
		$('#postCard').hide().fadeIn();
	});

	function errorPage() {
		$('#main').html('').append("<div id=\"error\">找不到你要瀏覽的頁面。</div>");
	}


	$('#detailDiaryReply #submit').live('click', function() {
		$.ajax({
			url : '/controller.php',
			type : 'POST',
			dataType : 'json',
			data : {
				action : 'newReply',
				replyContent : $('#detailDiaryReplyInput').val(),
				replyPermission : $('#replyPermission input').is(":checked") ? 0 : 1,
				diaryId : parseInt(diaryId)
			},
			success : function(responce) {
				initReply();
				$('#detailDiaryReplyInput').val('');
			}
		});	});

	$('#diarySearchBtn').live('click', function() {
		if ($('#diarySearchInput').val() != '請輸入日誌關鍵字。 　　　　　') {
			// alert($('#diarySearchInput').val());			$('#diaryLoading img').show();
			$.ajax({
				url : '/controller.php',
				type : 'POST',
				dataType : 'json',
				data : {
					action : 'diarySearch',
					account : account,
					searchKey : $('#diarySearchInput').val()
				},
				success : function(responce) {
					// alert(JSON.stringify(responce));					if (responce.status == true) {
						$(window).unbind('scroll');
						$('#diary-list').html('').hide().fadeIn();
						for ( i = 0; i < responce.content.length; i++) {
							var place = "";
							if (responce.content[i].place != null)
								place = "於 " + responce.content[i].place + " 發文     |     ";

							if (responce.content[i].content != null)
								responce.content[i].content = (responce.content[i].content).replace(/\n/g, "<br>");
							else
								responce.content[i].content = "";

							if (responce.content[i].permission) {
								switch(responce.content[i].permission) {
									case '0':
										var permissionStr = "瀏覽權限：本人";
										break;
									case '1':
										var permissionStr = "瀏覽權限：好友";
										break;
									case '2':
										var permissionStr = "瀏覽權限：公開";
										break;
								}
							} else
								permissionStr = "";

							$('#diary-list').append("<div class=\"diary-block\"><div class=\"date\">" + responce.content[i].date + "</div><div class=\"title\">" + responce.content[i].title + "</div><div class=\"content-border\"><div class=\"content\">" + responce.content[i].content + "</div><div class=\"contentImageSlideShow\"></div></div><div class=\"extend\"><span class=\"diaryPermission\">" + permissionStr + "</span><a href=\"/" + account + "/diary/" + responce.content[i].id + "\">(詳全文)</a></div><div class=\"detail\">" + responce.userInfo.username + "(" + responce.userInfo.account + ")　" + place + "相片(" + responce.content[i].img_count + ")     |     " + "回覆(" + responce.content[i].reply_count + ")" + "</div></div>");

							if (responce.content[i].img_count > 0) {
								var contentBorder = $('.content-border');
								$(contentBorder[i]).children('.content').css('width', '70%');

								for ( j = 0; j < responce.content[i].img_count; j++) {
									var slideShow = $(contentBorder[i]).children('.contentImageSlideShow');
									$('<div></div>').css('background-image', 'url("' + responce.content[i].imgArray[j] + '")').appendTo(slideShow);
								}

								if (responce.content[i].img_count > 1) {
									$(contentBorder[i]).children('.contentImageSlideShow').children('div:first').addClass('active');

									var now = new Date();
									var id = now.getHours().toString() + now.getMinutes().toString() + now.getSeconds().toString() + now.getMilliseconds().toString();
									$(contentBorder[i]).children('.contentImageSlideShow').attr('id', id);
									$('#' + id.toString()).cycle({
										fx : 'fade',
										speed : 1000,
										timeout : (Math.floor(Math.random() * 5) + 3) * 1000,
										next : '#' + id.toString(),
										pause : 1
									});
								}

								$(contentBorder[i]).children('.contentImageSlideShow').show();
							}
						}
					} else {
						status(true, "找不到符合的日誌。");
					}
					$('#diaryLoading img').hide();
				}
			});
		} else {
			status(true, "請輸入日誌關鍵字。");
		}

	});

	$('.detailDiarySlideShowBlock').live('click', function() {
		window.document.body.style.overflow = 'hidden';		$('#slideShowBackground').show();
		if ($('#slideShowStageLeft').width() < 700) {
			$('#slideShowStageRight').hide();
			$('#slideShowStageLeft').css('width', '100%');
		}

		window.onresize = function() {
			if ($('#slideShowStageLeft').width() < 700) {				$('#slideShowStageRight').hide();
				$('#slideShowStageLeft').css('width', '100%');
			} else {
				$('#slideShowStageRight').show();
				$('#slideShowStageLeft').css('width', '75%');
			}
		};

		$('#slideShowImg').attr('src', $(this).css('background-image').replace('url(', '').replace(')', ''));
		$('#slideShowImgStage th').html($(this).attr('name'));
		$('#slideShowImgInfo').html($(this).attr('content') ? $(this).attr('content') : "無圖文說明。");
		var Img_tmp = Array();
		for (var i = 0; i < $('.detailDiarySlideShowBlock').length; i++) {
			var check = false;
			for (var j = 0; j < Img_tmp.length; j += 3) {
				if (Img_tmp[j] == $($('.detailDiarySlideShowBlock')[i]).css('background-image'))
					check = true;
			}
			if (!check) {
				Img_tmp.push($($('.detailDiarySlideShowBlock')[i]).css('background-image'));
				Img_tmp.push($($('.detailDiarySlideShowBlock')[i]).attr('name'));
				if ($($('.detailDiarySlideShowBlock')[i]).attr('content'))
					Img_tmp.push($($('.detailDiarySlideShowBlock')[i]).attr('content'));
				else
					Img_tmp.push("無圖文說明。");
			}		}
		$('#slideShowStageRight').html('');
		for (var i = 0; i < Img_tmp.length; i += 3) {
			var currentBlock = $('<div class="slideShowImgMenu"></div>');
			$('#slideShowStageRight').append(currentBlock.css('background-image', Img_tmp[i]));
			currentBlock.attr('name', Img_tmp[i + 1]);
			currentBlock.attr('content', Img_tmp[i + 2]);
			if ($(this).css('background-image') == Img_tmp[i]) {
				currentBlock.css('border-color', 'blue');
				currentBlock.addClass('current');
			}
		}
	});

	$('.slideShowImgMenu').live('click', function() {
		$('#slideShowImg').attr('src', $(this).css('background-image').replace('url(', '').replace(')', ''));
		$('#slideShowImgStage th').html($(this).attr('name'));
		$('#slideShowImgInfo').html($(this).attr('content') ? $(this).attr('content') : "無圖文說明。");

		$('.slideShowImgMenu').removeClass('current')
		$(this).addClass('current');
		$('.slideShowImgMenu').css('border', '2px solid #fff');		$(this).css('border-color', 'blue');

		$('.slideShowImgMenu').hover(function() {
			if (!$(this).hasClass('current'))
				$(this).css('border', '2px solid red');
		}, function() {
			if ($(this).hasClass('current'))
				$(this).css('border', '2px solid blue');
			else
				$(this).css('border', '2px solid white');
		});	});

	$('#closeSlideShow').live('click', function() {
		$('#slideShowBackground').hide();
		window.document.body.style.overflow = 'auto';
	});

	$('#slideShowOpt #next').live('click', function() {
		var next = null;
		var current = $('.current');
		if (current.is($('.slideShowImgMenu:last')))
			next = $('.slideShowImgMenu:first');
		else
			next = current.next();

		$('#slideShowImg').attr('src', next.css('background-image').replace('url(', '').replace(')', ''));
		$('#slideShowImgStage th').html(next.attr('name'));
		$('#slideShowImgInfo').html(next.attr('content') ? next.attr('content') : "無圖文說明。");
		current.removeClass('current');
		next.addClass('current');

		$('.slideShowImgMenu').css('border', '2px solid #fff');
		next.css('border-color', 'blue');

		$('.slideShowImgMenu').hover(function() {
			if (!$(this).hasClass('current'))
				$(this).css('border', '2px solid red');
		}, function() {
			if ($(this).hasClass('current'))
				$(this).css('border', '2px solid blue');
			else
				$(this).css('border', '2px solid white');
		});

	});

	$('#slideShowOpt #prev').live('click', function() {
		var prev = null;
		var current = $('.current');
		if (current.is($('.slideShowImgMenu:first')))
			prev = $('.slideShowImgMenu:last');
		else
			prev = current.prev();

		$('#slideShowImg').attr('src', prev.css('background-image').replace('url(', '').replace(')', ''));
		$('#slideShowImgStage th').html(prev.attr('name'));
		$('#slideShowImgInfo').html(prev.attr('content') ? prev.attr('content') : "無圖文說明。");
		current.removeClass('current');
		prev.addClass('current');

		$('.slideShowImgMenu').css('border', '2px solid #fff');
		prev.css('border-color', 'blue');

		$('.slideShowImgMenu').hover(function() {
			if (!$(this).hasClass('current'))
				$(this).css('border', '2px solid red');
		}, function() {
			if ($(this).hasClass('current'))
				$(this).css('border', '2px solid blue');
			else
				$(this).css('border', '2px solid white');
		});
	});

	$('#infoBtn').click(function() {
		window.location = ("/" + account + "/info");
	});

	function loadInfoPage() {
		$('#content').load('/inner/info.php', function() {
			clearBuff();
			$.ajax({
				url : '/controller.php',
				type : 'POST',
				dataType : 'json',
				data : {
					action : 'loadInfo',
					account : account
				},
				success : function(responce) {
					if (responce.content.bigHeadImg)
						$('#InfoBigHead').attr('src', responce.content.bigHeadImg);
					if (responce.status) {
						if (responce.content.username)
							$('#InfoTable').append('<tr><th>暱稱</th><td class="label"><span>' + responce.content.username + '</span><div class="InfoEdit" id="InfoUsernameEdit"></div></td></tr><tr class="spacer"><td colspan="2"><hr></td></tr>');
						if (responce.content.account)
							$('#InfoTable').append('<tr><th>帳號</th><td class="label"><span>' + responce.content.account + '</span></td></tr><tr class="spacer"><td colspan="2"><hr></td></tr>');
						if (responce.isUser)
							$('#InfoTable').append('<tr><th>密碼</th><td class="label"><span><a href="javascript:void(0)" class="InfoPasswdEdit">設定密碼</a></span><div class="InfoEdit InfoPasswdEdit"></div></td></tr><tr class="spacer"><td colspan="2"><hr></td></tr>');
						if (responce.content.email)
							$('#InfoTable').append('<tr><th>信箱</th><td class="label"><span data=' + (responce.data).split("")[3] + '>' + responce.content.email + '</span><div class="InfoEdit InfoEmaildEdit"></td></tr><tr class="spacer"><td colspan="2"><hr></td></tr>');
						if (responce.content.birthday || responce.isUser)
							$('#InfoTable').append('<tr><th>生日</th><td class="label"><span dateNum=' + responce.content.birthday + ' data=' + (responce.data).split("")[0] + '>' + (responce.content.birthday ? $.format.date(responce.content.birthday, "MMM d, yyyy") : "<a href=\"javascript:void(0)\" class=\"InfoBirthEdit\">設定生日</a>") + '</span><div class="InfoEdit InfoBirthEdit"></div></td></tr><tr class="spacer"><td colspan="2"><hr></td></tr>');
						if (responce.content.gender || responce.isUser) {
							if (responce.content.gender) {
								var gender;
								switch(responce.content.gender) {
									case '1':
										gender = "男";
										break;
									case '0':
										gender = "女";
										break;
								}
							}
							$('#InfoTable').append('<tr><th>性別</th><td class="label"><span data=' + (responce.data).split("")[1] + '>' + ( gender ? gender : "<a href=\"javascript:void(0)\" class=\"InfoGenderEdit\">設定性別</a>") + '</span><div class="InfoEdit InfoGenderEdit"></div></td></tr><tr class="spacer"><td colspan="2"><hr></td></tr>');
						}
						if (responce.isUser)
							$('#InfoTable').append('<tr><th>身份證字號</th><td class="label"><span>' + responce.content.id_number + '</span></td></tr><tr class="spacer"><td colspan="2"><hr></td></tr>');
						if (responce.content.info || responce.isUser)
							$('#InfoTable').append('<tr><th>關於我</th><td class="label"><span data=' + (responce.data).split("")[2] + '>' + (responce.content.info ? (responce.content.info).replace(/\n/g, "<br>") : "<a href=\"javascript:void(0)\" class=\"InfoAboutEdit\">設定關於我</a>") + '</span><div class="InfoEdit InfoAboutEdit"></div></td></tr><tr class="spacer"><td colspan="2"><hr></td></tr>');

						if (responce.isUser) {
							var capacityTotal;
							var userTitle;
							switch(responce.content.membership) {
								case '0':
									userTitle = "一般會員";
									capacityTotal = 150;
									break;
								case '1':
									userTitle = "黃金會員";
									capacityTotal = 300;
									break;
								case '2':
									userTitle = "白金會員";
									capacityTotal = 600;
									break;
							}
							$('#InfoTable').append('<tr><th>會員類型</th><td class="label">' + userTitle + '</td></tr><tr class="spacer"><td colspan="2"><hr></td></tr>');
							$('#InfoTable').append('<tr><th>剩餘容量</th><td class="label">' + responce.content.capacity + ' / ' + capacityTotal + ' MB</td></tr><tr class="spacer"><td colspan="2"><hr></td></tr>');
						} else {
							$('.InfoEdit, #changeBigHead').remove();
						}
						$('.spacer:last').remove();
					} else
						errorPage();
				}
			});		});
		$('#content').hide().fadeIn('slow');
		$('#infoBtn').css('opacity', '1');
	}


	$('#InfoUsernameEdit').live('click', function() {
		$(this).hide();
		$(this).parents('tr').after('<tr><td></td><td><input type="text" id="UsernameInput"></td></tr>');
		$('#UsernameInput').parents('tr').after('<tr><td></td><td><a href="javascript:void(0)" id="UsernameInputCancel">取消</a><a href="javascript:void(0)" id="UsernameInputOK">確定</a></td></tr>');		$('#UsernameInput').Watermark('輸入新暱稱              ', "#d3d3d3");
	});

	$('#UsernameInputCancel').live('click', function() {
		$('#InfoUsernameEdit').show();
		$(this).parents('tr').prev('tr').remove();
		$(this).parents('tr').remove();
	});

	$('#UsernameInputOK').live('click', function() {
		if ($('#UsernameInput').val() != "輸入新暱稱              ") {
			$.ajax({
				url : '/controller.php',
				type : 'POST',
				async : false,
				dataType : 'json',
				data : {
					action : 'UsernameEdit',
					username : $('#UsernameInput').val()
				},
				success : function(responce) {
					if (responce) {
						$('#InfoUsernameEdit').parent().find('span').html($('#UsernameInput').val());
						$('.postCardName').html($('#UsernameInput').val());
						$('#InfoUsernameEdit').show();
					} else
						status(true, "請輸入新暱稱。");
				}
			});
			$(this).parents('tr').prev('tr').remove();
			$(this).parents('tr').remove();
		} else
			status(true, "請輸入新暱稱。");
	});

	$('.InfoPasswdEdit').live('click', function() {
		$(this).parents('tr').after('<tr><td></td><td>舊密碼：<input type="password" id="OldPasswdInput"></td></tr>');
		$('#OldPasswdInput').parents('td').append('<br>新密碼：<input type="password" id="NewPasswdInput">');
		$('#NewPasswdInput').parents('td').append('<br>確　認：<input type="password" id="AgainPasswdInput">');
		$('#OldPasswdInput').parents('tr').after('<tr><td></td><td><a href="javascript:void(0)" id="PasswdInputCancel">取消</a><a href="javascript:void(0)" id="PasswdInputOK">確定</a></td></tr>');
		$('.InfoPasswdEdit').hide();
	});

	$('#PasswdInputCancel').live('click', function() {
		$('.InfoPasswdEdit').show();
		$(this).parents('tr').prev('tr').remove();
		$(this).parents('tr').remove();
	});

	$('#PasswdInputOK').live('click', function() {
		var check = false;
		if (!$('#OldPasswdInput').val())
			status(true, "舊密碼不可為空。");
		else if (!$('#NewPasswdInput').val())
			status(true, "新密碼不可為空。");
		else if (!$('#AgainPasswdInput').val())
			status(true, "確認密碼不可為空。");
		else {
			$.ajax({
				url : '/controller.php',
				type : 'POST',
				async : false,
				dataType : 'json',
				data : {
					action : 'PasswdEdit',
					OldPasswdInput : $('#OldPasswdInput').val(),
					NewPasswdInput : $('#NewPasswdInput').val(),
					AgainPasswdInput : $('#AgainPasswdInput').val()
				},
				success : function(responce) {
					status(true, responce.content);
					if (responce.status) {
						$('.InfoPasswdEdit').show();
						check = true;
					}
				}
			});
			if (check) {
				$(this).parents('tr').prev('tr').remove();				$(this).parents('tr').remove();			}
		}
	});

	$('.InfoBirthEdit').live('click', function() {
		$('.InfoBirthEdit').hide();
		var permission = $(this).parents('td').find('span').attr('data');
		$(this).parents('tr').after('<tr><td></td><td><input type="text" id="birthdayInput"></td></tr>');
		$('#birthdayInput').datepicker({
			dateFormat : 'yy-mm-dd'
		});
		if (!$('a.InfoBirthEdit').length > 0) {
			$('#birthdayInput').val($(this).parent().find('span').attr('dateNum'));		}
		$('#birthdayInput').parents('tr').after('<tr><td></td><td><input type="checkbox" id="birthData" class="InfoCheckBox"><span class="InfoCheckBoxText">隱藏</span><a href="javascript:void(0)" id="birthInputCancel">取消</a><a href="javascript:void(0)" id="birthInputOK">確定</a></td></tr>')
		if (permission == "0")
			$('#birthData').attr('checked', true);
		else
			$('#birthData').attr('checked', false);
	});

	$('#birthInputCancel').live('click', function() {
		$('.InfoBirthEdit').show();
		$(this).parents('tr').prev('tr').remove();
		$(this).parents('tr').remove();
	});

	$('#birthInputOK').live('click', function() {
		$.ajax({
			url : '/controller.php',
			type : 'POST',
			async : false,
			dataType : 'json',
			data : {
				action : 'birthEdit',
				date : $('#birthdayInput').val() ? $('#birthdayInput').val() : "null",
				permission : $('#birthData').is(":checked") ? 0 : 1
			},
			success : function(responce) {
				if (responce) {
					$('.InfoBirthEdit').show();
					if ($('#birthdayInput').val() != "") {
						$('.InfoBirthEdit').parent().find('span').html($.format.date($('#birthdayInput').val() + " 00:00:00", "MMM d, yyyy"));
					} else
						$('.InfoBirthEdit').parent().find('span').html('<a href="javascript:void(0)" class="InfoBirthEdit">設定生日</a>');
					$('.InfoBirthEdit').parent().find('span').attr('data', $('#birthData').is(":checked") ? 0 : 1);
					$('.InfoBirthEdit').parent().find('span').attr('dateNum', $('#birthdayInput').val());
				}
			}
		});
		$(this).parents('tr').prev('tr').remove();
		$(this).parents('tr').remove();
	});

	$('.InfoGenderEdit').live('click', function() {
		$('.InfoGenderEdit').hide();
		var permission = $(this).parents('td').find('span').attr('data');
		$(this).parents('tr').after('<tr><td></td><td><select id="InfoGenderSelect"><option value="1">男</option><option value="0">女</option></select></td></tr>');

		$('#InfoGenderSelect').parents('tr').after('<tr><td></td><td><input type="checkbox" id="genderData" class="InfoCheckBox"><span class="InfoCheckBoxText">隱藏</span><a href="javascript:void(0)" id="genderInputCancel">取消</a><a href="javascript:void(0)" id="genderInputOK">確定</a></td></tr>');
		if (permission == "0")
			$('#genderData').attr('checked', true);
		else
			$('#genderData').attr('checked', false);
	});

	$('#genderInputCancel').live('click', function() {
		$('.InfoGenderEdit').show();
		$(this).parents('tr').prev('tr').remove();
		$(this).parents('tr').remove();
	});

	$('#genderInputOK').live('click', function() {
		$.ajax({
			url : '/controller.php',
			type : 'POST',
			async : false,
			dataType : 'json',
			data : {
				action : 'genderEdit',
				gender : $('#InfoGenderSelect').val(),
				permission : $('#genderData').is(":checked") ? 0 : 1
			},
			success : function(responce) {
				if (responce) {
					$('.InfoGenderEdit').show();
					var tmpGender;
					if ($('#InfoGenderSelect').val() == 1)
						tmpGender = "男";
					else
						tmpGender = "女";
					$('.InfoGenderEdit').parent().find('span').html(tmpGender);
					$('.InfoGenderEdit').parent().find('span').attr('data', $('#genderData').is(":checked") ? 0 : 1);
				}
			}
		});

		$(this).parents('tr').prev('tr').remove();
		$(this).parents('tr').remove();
	});

	$('.InfoAboutEdit').live('click', function() {
		$('.InfoAboutEdit').hide();
		var permission = $(this).parents('td').find('span').attr('data');
		$(this).parents('tr').after('<tr><td></td><td><textarea id="InfoAboutInput"></textarea></td></tr>');
		if (!$('a.InfoAboutEdit').length > 0) {
			$('#InfoAboutInput').val(($(this).parent().find('span').html()).replace(/<br>/g, "\n"));
		}
		$('#InfoAboutInput').autosize();
		$('#InfoAboutInput').Watermark('關於我 ...               ', "#d3d3d3");
		$('#InfoAboutInput').parents('tr').after('<tr><td></td><td><input type="checkbox" id="AboutData" class="InfoCheckBox"><span class="InfoCheckBoxText">隱藏</span><a href="javascript:void(0)" id="AboutInputCancel">取消</a><a href="javascript:void(0)" id="AboutInputOK">確定</a></td></tr>')
		if (permission == "0")
			$('#AboutData').attr('checked', true);
		else
			$('#AboutData').attr('checked', false);
	});

	$('#AboutInputCancel').live('click', function() {
		$('.InfoAboutEdit').show();
		$(this).parents('tr').prev('tr').remove();
		$(this).parents('tr').remove();
	});

	$('#AboutInputOK').live('click', function() {
		$.ajax({
			url : '/controller.php',
			type : 'POST',
			async : false,
			dataType : 'json',
			data : {
				action : 'infoEdit',
				info : $('#InfoAboutInput').val(),
				permission : $('#AboutData').is(":checked") ? 0 : 1
			},
			success : function(responce) {
				if (responce) {
					$('.InfoAboutEdit').show();
					if ($('#InfoAboutInput').val() != "關於我 ...               ")
						$('.InfoAboutEdit').parent().find('span').html($('#InfoAboutInput').val().replace(/\n/g, "<br>"));
					else
						$('.InfoAboutEdit').parent().find('span').html('<a href="javascript:void(0)" class="InfoAboutEdit">設定關於我</a>');
					$('.InfoAboutEdit').parent().find('span').attr('data', $('#AboutData').is(":checked") ? 0 : 1);
				}
			}
		});
		$(this).parents('tr').prev('tr').remove();
		$(this).parents('tr').remove();
	});

	$('.InfoEmaildEdit').live('click', function() {
		$('.InfoEmaildEdit').hide();
		var permission = $(this).parents('td').find('span').attr('data');
		$(this).parents('tr').after('<tr><td></td><td><input type="checkbox" id="EmailData" class="InfoCheckBox"><span class="InfoCheckBoxText">隱藏</span><a href="javascript:void(0)" id="EmailInputCancel">取消</a><a href="javascript:void(0)" id="EmailInputOK">確定</a></td></tr>')
		if (permission == "0")
			$('#EmailData').attr('checked', true);
		else
			$('#EmailData').attr('checked', false);
	});

	$('#EmailInputCancel').live('click', function() {
		$('.InfoEmaildEdit').show();
		$(this).parents('tr').remove();
	});

	$('#EmailInputOK').live('click', function() {
		$.ajax({
			url : '/controller.php',
			type : 'POST',
			async : false,
			dataType : 'json',
			data : {
				action : 'emailEdit',
				permission : $('#EmailData').is(":checked") ? 0 : 1
			},
			success : function(responce) {
				if (responce)
					$('.InfoEmaildEdit').show();
				$('.InfoEmaildEdit').parent().find('span').attr('data', $('#EmailData').is(":checked") ? 0 : 1);
			}
		});
		$(this).parents('tr').remove();
	});

	$('#bigHead-input').live('change', function() {
		$("#bigHead-form").ajaxSubmit({
			dataType : 'json',
			success : function(responce) {
				$('#bigHead-input').val('');
				$('#InfoBigHead, #bigHeadImg').attr('src', responce.content.src);
				$('#postCardHead').css('background-image', 'url(' + responce.content.src + ')');
				status(true, "圖片處理中，請稍候... 100%");			},
			uploadProgress : function(event, position, total, percentComplete) {
				status(false, "圖片處理中，請稍候... " + (percentComplete - 1) + "%");
			}
		});
	});

	$('.addUser').live('click', function() {
		var btn = $(this);
		$.ajax({
			url : '/controller.php',
			type : 'POST',
			async : false,
			dataType : 'json',
			data : {
				action : 'addUser',
				account : $(this).parent().attr('account') ? $(this).parent().attr('account') : account
			},
			success : function(responce) {
				if (responce) {
					$(btn).replaceWith('<a href="javascript:void(0)" class="unAddUser btn">取消好友邀請</a>');
				}
			}
		});
	});

	$('.blockUser').live('click', function() {
		var btn = $(this);
		_account = $(this).parent().attr('account') ? $(this).parent().attr('account') : account;
		$.ajax({
			url : '/controller.php',
			type : 'POST',
			async : false,
			dataType : 'json',
			data : {
				action : 'blockUser',
				account : _account
			},
			success : function(responce) {
				if (responce) {
					$(btn).replaceWith('<a href="javascript:void(0)" class="unBlockUser btn">解除封鎖</a>');
					$('.blockListView[account="' + _account + '"], .friendListView[account="' + _account + '"], .searchListView[account="' + _account + '"]').find('.blockUser').replaceWith('<a href="javascript:void(0)" class="unBlockUser btn">解除封鎖</a>');
				}
			}
		});
	});

	$('.unBlockUser').live('click', function() {
		var btn = $(this);
		var _account = $(this).parent().attr('account') ? $(this).parent().attr('account') : account;
		$.ajax({
			url : '/controller.php',
			type : 'POST',
			async : false,
			dataType : 'json',
			data : {
				action : 'unBlockUser',
				account : _account
			},
			success : function(responce) {
				if (responce) {
					$(btn).replaceWith('<a href="javascript:void(0)" class="blockUser btn">封　鎖</a>');
					$('.blockListView[account="' + _account + '"], .friendListView[account="' + _account + '"], .searchListView[account="' + _account + '"]').find('.unBlockUser').replaceWith('<a href="javascript:void(0)" class="blockUser btn">封　鎖</a>');
				}
			}
		});
	});

	$('.delUser').live('click', function() {
		var btn = $(this);
		$.ajax({
			url : '/controller.php',
			type : 'POST',
			async : false,
			dataType : 'json',
			data : {
				action : 'delUser',
				account : $(this).parent().attr('account') ? $(this).parent().attr('account') : account
			},
			success : function(responce) {
				if (responce) {
					$(btn).replaceWith('<a href="javascript:void(0)" class="addUser btn">加為好友</a>');
					$('.blockListView[account="' + _account + '"], .friendListView[account="' + _account + '"], .searchListView[account="' + _account + '"]').find('.delUser').replaceWith('<a href="javascript:void(0)" class="addUser btn">加為好友</a>');
				}
			}
		});
	});

	$('.unAddUser').live('click', function() {
		var btn = $(this);
		$.ajax({
			url : '/controller.php',
			type : 'POST',
			async : false,
			dataType : 'json',
			data : {
				action : 'unAddUser',
				account : $(this).parent().attr('account') ? $(this).parent().attr('account') : account
			},
			success : function(responce) {
				if (responce) {
					$(btn).replaceWith('<a href="javascript:void(0)" class="addUser btn">加為好友</a>');
					$('.blockListView[account="' + _account + '"], .searchListView[account="' + _account + '"]').find('.unAddUser').replaceWith('<a href="javascript:void(0)" class="addUser btn">加為好友</a>');
				}
			}
		});
	});

	$('.allowUser').live('click', function() {
		var btn = $(this);
		$.ajax({
			url : '/controller.php',
			type : 'POST',
			async : false,
			dataType : 'json',
			data : {
				action : 'allowUser',
				account : $(this).parent().attr('account') ? $(this).parent().attr('account') : account
			},
			success : function(responce) {
				if (responce) {
					$(btn).replaceWith('<a href="javascript:void(0)" class="delUser btn">刪除好友</a>');
					$('.blockListView[account="' + _account + '"], .searchListView[account="' + _account + '"]').find('.allowUser').replaceWith('<a href="javascript:void(0)" class="delUser btn">刪除好友</a>');
				}
			}
		});
	});

	$('#friendBtn').click(function() {
		window.location = ("/" + account + "/friend");
	});

	function loadFriendPage() {
		$('#content').load('/inner/friend.php', function() {
			$.ajax({
				url : '/controller.php',
				type : 'POST',
				async : false,
				dataType : 'json',
				data : {
					action : 'loadFriend',
					account : account
				},
				success : function(responce) {
					if (responce.request) {
						$('#UserRequest').html('好友邀請(' + responce.request + ')');
						$('#UserRequest').css({
							'border-color' : 'red',
							'-moz-box-shadow' : '0px 0px 5px red',
							'-webkit-box-shadow' : '0px 0px 5px red',
							'box-shadow' : '0px 0px 5px red'
						});
					}
					if (responce.status) {
						for (var i = 0; i < responce.content.length; i++) {
							$('#friendList').append('<div class="friendListView"><div class="friendListBigHead" style="background-image: url(\'/css/images/defaultHead.jpg\');"></div><div class="friendListName"></div></div><hr>');

							if (responce.isHome) {
								$($('.friendListView')[i]).append('<a href="javascript:void(0)" class="delUser btn">刪除好友</a><a href="javascript:void(0)" class="blockUser btn">封　鎖</a>');
								if (responce.content[i].isBlock)
									$($('.friendListView')[i]).find('.blockUser').replaceWith('<a href="javascript:void(0)" class="unBlockUser btn">解除封鎖</a>');
							}
							if (responce.content[i].bigHeadImg)
								$($('.friendListBigHead')[i]).css('background-image', 'url("' + responce.content[i].bigHeadImg + '")');
							$($('.friendListName')[i]).html('<a href="/' + responce.content[i].account + '">' + responce.content[i].username + '</a>');
							$($('.friendListView')[i]).attr('account', responce.content[i].account);
						}
					} else
						noData($('#friendList'));
				}
			});
		});
		$('#content').hide().fadeIn('slow');
		$('#friendBtn').css('opacity', '1');
	}


	$('.friendListView').live('click', function(e) {
		var elemClass = $(e.target).attr('class');
		if (elemClass == "friendListView" || elemClass == "friendListName" || elemClass == "friendListBigHead")			window.location = ("/" + $(this).attr('account') + "/diary");
	});

	$('.searchListView').live('click', function(e) {
		var elemClass = $(e.target).attr('class');
		if (elemClass == "searchListView" || elemClass == "searchListName" || elemClass == "searchListBigHead")
			window.location = ("/" + $(this).attr('account') + "/diary");
	});

	$('.requestUser, .requestYou').live('click', function(e) {
		var elemClass = $(e.target).attr('class');
		// if (elemClass == "requestListView" || elemClass == "requestListName" || elemClass == "requestListBigHead")
		if (elemClass != "btn")			window.location = ("/" + $(this).attr('account') + "/diary");
	});

	$('.blockListView').live('click', function(e) {
		var elemClass = $(e.target).attr('class');
		if (elemClass == "blockListView" || elemClass == "blockListName" || elemClass == "blockListBigHead")
			window.location = ("/" + $(this).attr('account') + "/diary");
	});
	$('#show_diary_search').live('click', function() {
		$('#searchDiary').slideDown();
		$(this).html("取消搜尋");
		$(this).attr('id', 'hide_diary_search');
	});

	$('#hide_diary_search').live('click', function() {
		$('#searchDiary').slideUp();
		$(this).html("搜尋日誌");
		$(this).attr('id', 'show_diary_search');

	});

	$('#BlockUserList').live('click', function() {
		$.ajax({
			url : '/controller.php',
			type : 'POST',
			async : false,
			dataType : 'json',
			data : {
				action : 'loadBlockList',
				account : account
			},
			success : function(responce) {
				$('#blockList').html('');
				$('#blockList').prepend('<div class="title"><div>封鎖清單</div></div>');				if (responce.status) {
					for (var i = 0; i < responce.content.length; i++) {
						$('#blockList').append('<div class="blockListView"><div class="blockListBigHead" style="background-image: url(\'/css/images/defaultHead.jpg\');"></div><div class="blockListName"></div><a href="javascript:void(0)" class="unBlockUser btn">解除封鎖</a></div><hr>');

						if (responce.content[i].bigHeadImg)
							$($('.blockListBigHead')[i]).css('background-image', 'url("' + responce.content[i].bigHeadImg + '")');

						$($('.blockListName')[i]).html('<a href="/' + responce.content[i].account + '">' + responce.content[i].username + '</a>');

						$($('.blockListView')[i]).attr('account', responce.content[i].account);
					}
					$('#blockList').slideDown(800);
				} else
					noData($('#blockList'));
				$('#blockList').append('<div class="slideUp"><a href="javascript:void(0)">捲起清單▲<a></div>');
			}
		});
	});

	$('.slideUp a').live('click', function() {
		$(this).parent().parent().slideUp(800);	});

	$('#UserRequest').live('click', function() {
		$(this).css({
			'border-color' : '#AAA',
			'-moz-box-shadow' : '0px 0px 5px #999999',
			'-webkit-box-shadow' : '0px 0px 5px #999999',
			'box-shadow' : '0px 0px 5px #999999'
		});
		$('#UserRequest').html('好友邀請');
		$('#friendRequestHint').fadeOut();
		$.ajax({
			url : '/controller.php',
			type : 'POST',
			async : false,
			dataType : 'json',
			data : {
				action : 'UserRequest',
				account : account
			},
			success : function(responce) {
				$('#requestList').html('');
				$('#requestList').prepend('<div class="title"><div>好友邀請</div></div>');
				if (responce.status) {
					if (responce.content.requestYou) {
						for (var i = 0; i < responce.content.requestYou.length; i++) {
							$('#requestList').append('<div class="requestListView requestYou"><div class="requestListBigHead requestYouBigHead" style="background-image: url(\'/css/images/defaultHead.jpg\');"></div><div class="requestListName requestYouListName"></div><a href="javascript:void(0)" class="allowUser btn">答應好友邀請</a></div><hr>');
							if (responce.content.requestYou[i].bigHeadImg)
								$($('.requestYouBigHead')[i]).css('background-image', 'url("' + responce.content.requestYou[i].bigHeadImg + '")');
							$($('.requestYouListName')[i]).html('<a href="/' + responce.content.requestYou[i].account + '">' + responce.content.requestYou[i].username + '</a>');
							$($('.requestYou')[i]).attr('account', responce.content.requestYou[i].account);
						}
					} else {
						$('#requestList .title:first').remove();
					}
					if (responce.content.requestUser) {
						$('#requestList').append('<div class="title"><div>邀請中</div></div>');
						for (var i = 0; i < responce.content.requestUser.length; i++) {
							$('#requestList').append('<div class="requestListView requestUser"><div class="requestListBigHead requestUserBigHead" style="background-image: url(\'/css/images/defaultHead.jpg\');"></div><div class="requestListName requestUserListName"></div><a href="javascript:void(0)" class="unAddUser btn">取消好友邀請</a></div><hr>');
							if (responce.content.requestUser[i].bigHeadImg)
								$($('.requestUserBigHead')[i]).css('background-image', 'url("' + responce.content.requestUser[i].bigHeadImg + '")');
							$($('.requestUserListName')[i]).html('<a href="/' + responce.content.requestUser[i].account + '">' + responce.content.requestUser[i].username + '</a>');
							$($('.requestUser')[i]).attr('account', responce.content.requestUser[i].account);
						}
					}
					$('#requestList').slideDown(800);
				} else
					noData($('#requestList'));
				$('#requestList').append('<div class="slideUp"><a href="javascript:void(0)">捲起清單▲<a></div>');
			}
		});
	});

	$('#showUserSearch').live('click', function() {
		$('#searchFriend').slideDown();
		$(this).html("取消搜尋");
		$(this).attr('id', 'hideFriendSearch');
		$('#friendSearchInput').Watermark('請輸入好友名稱關鍵字。 　　　　　', '#d3d3d3');
	});

	$('#hideFriendSearch').live('click', function() {
		$('#searchFriend').slideUp();
		$('#searchList').slideUp();
		$(this).html("好友搜尋");
		$(this).attr('id', 'showUserSearch');
	});

	$('#friendSearchBtn').live('click', function() {
		$('#searchList').html('');
		if ($('#friendSearchInput').val() != "請輸入好友名稱關鍵字。 　　　　　") {
			$.ajax({
				url : '/controller.php',
				type : 'POST',
				async : false,
				dataType : 'json',
				data : {
					action : 'friendSearch',
					keyWord : $('#friendSearchInput').val()
				},
				success : function(responce) {
					$('#searchList').prepend('<div class="title"><div>搜尋結果</div></div>');
					if (responce.status) {
						for (var i = 0; i < responce.content.length; i++) {
							$('#searchList').append('<div class="searchListView"><div class="searchListBigHead" style="background-image: url(\'/css/images/defaultHead.jpg\');"></div><div class="searchListName"></div><a href="javascript:void(0)" class="addUser btn">加為好友</a><a href="javascript:void(0)" class="blockUser btn">封　鎖</a></div><hr>');

							if (responce.content[i].isFriend)
								switch(responce.content[i].isFriend) {
									case '0':
										$($('.searchListView')[i]).find('.addUser').replaceWith('<a href="javascript:void(0)" class="unAddUser btn">取消好友邀請</a>');
										break;
									case '1':
										$($('.searchListView')[i]).find('.addUser').replaceWith('<a href="javascript:void(0)" class="delUser btn">刪除好友</a>');
										break;
								}
							if (responce.content[i].isBlock)
								$($('.searchListView')[i]).find('.blockUser').replaceWith('<a href="javascript:void(0)" class="unBlockUser btn">解除封鎖</a>');

							if (responce.content[i].bigHeadImg)
								$($('.searchListBigHead')[i]).css('background-image', 'url("' + responce.content[i].bigHeadImg + '")');

							$($('.searchListName')[i]).html('<a href="/' + responce.content[i].account + '">' + responce.content[i].username + '</a>');
							$($('.searchListView')[i]).attr('account', responce.content[i].account);
						}
						$('#searchList').slideDown(800);
					} else {
						noData($('#searchList'));
						status(true, "找不到使用者。");
					}
					$('#searchList').append('<div class="slideUp"><a href="javascript:void(0)">捲起清單▲<a></div>');
				}
			});
		} else {
			status(true, "請輸入好友名稱關鍵字。");
		}

	});

	$('.delDiary').live('click', function() {
		var parent = $(this);
		$.ajax({
			url : '/controller.php',
			type : 'POST',
			dataType : 'json',
			data : {
				action : 'delDiary',
				diaryId : $(this).parent().attr('id')
			},
			success : function(responce) {
				if (responce) {
					$(parent).parents('.diary-block').fadeOut('slow');
				}
			}
		});
	});

	$('.delDetailDiary').live('click', function() {
		var parent = $(this);
		$.ajax({
			url : '/controller.php',
			type : 'POST',
			dataType : 'json',
			data : {
				action : 'delDiary',
				diaryId : $(this).parent().attr('id')
			},
			success : function(responce) {
				if (responce) {
					window.location = ("/" + account + "/diary");
				}
			}
		});
	});
	function loadEditDiary() {
		$('#content').load('/inner/editDiary.php', function() {

			$.ajax({
				url : '/controller.php',
				type : 'POST',
				dataType : 'json',
				data : {
					action : 'loadEditDiary',
					diaryId : parseInt(diaryId)
				},
				success : function(responce) {
					if (responce.status) {
						$('#editTitleInput').val(responce.content.title);
						$('#editTitleInput').Watermark("Title              ", "#d3d3d3");
						$('textarea#editContentInput').val(responce.content.content);
						if (!responce.content.content)
							$('#editContentInput').Watermark('Say Something ...               ', "#d3d3d3");
						$('#editContentInput').autosize();

						$('#geocoder-input').val(responce.content.place);
						if (!responce.content.place)
							$('#geocoder-input').Watermark('請輸入所在位置。               ', "#d3d3d3");

						$('#permission-select').val(responce.content.permission);

						if (responce.img) {
							for (var i = 0; i < responce.img.length; i++)
								$('#preview').append('<div class="preview_block"><img class="editImage" src="' + responce.img[i].i_path + '" name="' + responce.img[i].name + '" tmp_name="' + responce.img[i].path + '"><span class="cancel cancelImage"></span></div>');
							$('#preview').show();
						}

						if (responce.content.music_path) {
							$('#music_uploadBtn').replaceWith('<button type="button" id="cancelMusic">取消音樂</button>');
							$('#music-input').hide();							$('#audioContainer .music_player').show();
							$('#audioContainer .music_player').attr('src', responce.content.music_path)[0];
							var tmp_name = responce.content.music_path.split("/");
							$('#audioContainer .music_player').attr('tmp_name', tmp_name[tmp_name.length - 1]);							$('#audioContainer .music_player').addClass('old');
							setTimeout(function() {
								$('#audioContainer .music_player')[0].play();
							}, 3000);
						}
					} else {
						errorPage();
					}
				}
			});		});

	}


	$('.cancelImage').live('click', function() {
		$(this).parent().remove();
		preview_count();
	});

	$('#cancelMusic').live('click', function() {
		$(this).replaceWith('<button type="button" id="music_uploadBtn">音樂上傳</button>');
		$('#music-input').show();

		if (!$('#audioContainer .music_player').hasClass("old"))
			del_music($('#audioContainer .music_player').attr('tmp_name'));

		$('.music_player').removeAttr("src");
		$('.music_player').removeAttr("name");		$('.music_player').removeAttr("tmp_name");
		$(this).hide();
		$('.music_player').hide();		$('#music_uploadBtn').show();		$('#audioContainer .music_player').removeClass("old");
	});

	$('#submitEditDiary').live('click', function() {

		var image = new Array();
		var music = null;

		$('.preview_block img').each(function() {
			image.push($(this).attr('tmp_name'));
		});
		if (image.length == 0)
			image = null;

		if ($('.music_player').attr('tmp_name') != undefined) {
			music = $('.music_player').attr('tmp_name');
		}

		$.ajax({
			url : '/controller.php',
			type : 'POST',
			dataType : 'json',
			async : false,
			data : {
				action : 'editDiary',
				diaryId : parseInt(diaryId),
				title : $('#editTitleInput').val(),
				content : $('textarea#editContentInput').val(),
				permission : $('#permission-select').val(),
				geocoder : $('#geocoder-input').val(),
				image : image,
				music : music
			},
			success : function(responce) {
				if (responce.status) {
					$('#preview').remove();
					$('.music_player').remove();
					window.location = ("/" + account + "/diary/" + responce.diaryId);
				} else {
					status(true, responce.content);
				}
			}
		});
	});

	$('#exportBtn').click(function() {
		window.location = ("/" + account + "/orderDiary");
	});

	$('#orderQuantity').live('change', function() {
		setMoney();	});

	$('.diarySelectBlock > input:checkbox').live('click', function() {
		setMoney();
	});

	function setMoney() {
		var diaryCount = $('.diarySelectBlock > input:checked').length;		$('#orderDiaryMoney').html("金額：　" + ($('#orderQuantity').val() * 100 + (diaryCount * 40) * $('#orderQuantity').val()).toString() + " 元");	}
	function loadOrderDiary() {
		$('#content').load('/inner/orderDiary.php', function() {
			$('#orderName').Watermark('請填寫收件人姓名...               ', "#d3d3d3");
			$('#orderPhone').Watermark('請填寫聯絡電話...               ', "#d3d3d3");
			$('#orderAddress').Watermark('請填寫收件地址...               ', "#d3d3d3");
			$.ajax({
				url : '/controller.php',
				type : 'POST',
				dataType : 'json',
				async : false,
				data : {
					action : 'loadOrderDiaryList',
					account : account
				},
				success : function(responce) {
					if (responce.status) {
						if (responce.content) {
							for (var i = 0; i < responce.content.length; i++)
								$('#diarySelect').append('<div class="diarySelectBlock"><input diaryId=' + responce.content[i].id + ' type="checkbox" /><div class="diaryName"><a href="javascript:void(0)">' + responce.content[i].title + '</a></div></div>');
						} else {
							noData($('#diarySelect'));
						}

					} else {
						errorPage();
					}
				}
			});
		});

		$('#content').hide().fadeIn('slow');
		$('#menu span').css('opacity', '0.5');
		$('#exportBtn').css('opacity', '1');
	}


	$('.diaryName a').live('click', function() {
		window.open('/' + account + '/diary/' + $(this).parents('.diarySelectBlock').find('input').attr('diaryId'));
	});

	$('#submitOrderDiary').live('click', function() {
		var diaryId = Array();		$('.diarySelectBlock').each(function() {
			if ($(this).find('input:checked').attr('diaryId') != undefined)
				diaryId.push($(this).find('input:checked').attr('diaryId'));
		});

		$.ajax({
			url : '/controller.php',
			type : 'POST',
			dataType : 'json',
			data : {
				action : 'submitOrderDiary',
				name : $('#orderName').val(),
				address : $('#orderAddress').val(),
				phone : $('#orderPhone').val(),
				quantity : $('#orderQuantity').val(),
				diaryList : diaryId
			},
			success : function(responce) {
				if (responce.status) {
					window.location = ("/" + account + "/orderDiary");
				} else {
					status(true, responce.content);
				}
			}
		});
	});

	$('#showOrderRecord').live('click', function() {
		$.ajax({
			url : '/controller.php',
			type : 'POST',
			dataType : 'json',
			data : {
				action : 'loadOrderRecord',
				account : account
			},
			success : function(responce) {
				$('.slideUp').remove();
				$('#orderDiaryRecordList').html('');
				if (responce.status) {
					for (var i = 0; i < responce.content.length; i++) {
						$('#orderDiaryRecordList').append('<div class="orderDiaryRecordBlock"></div>');
						var status;
						switch(responce.content[i].status) {
							case '0':
								status = "尚未繳費";
								break;
							case '1':
								status = "已繳費，日誌裝訂中"
								break;
						}
						$($('.orderDiaryRecordBlock')[i]).append('<div class="orderDiaryRecordParent"><div class="orderDiaryRecordLeft"><div class="RecordName">收件人：' + responce.content[i].name + '</div><br><div class="RecordPhone">電　話：' + responce.content[i].phone + '</div><br><div class="RecordAddress">地　址：' + responce.content[i].address + '</div><br><div class="RecordQuantity">數　量：' + responce.content[i].quantity + '</div><br><div class="RecordMoney">金　額：' + responce.content[i].money + '</div><br><div class="RecordStatus">狀　態：' + status + '</div><br></div><div class="RecordOpt"><a href="javascript:void(0)" class="cancelOrderDiary" orderId="' + responce.content[i].id + '">取消裝訂</a></div></div>');

						var right = $('<div class="orderDiaryRecordRight"></div>');
						for (var j = 0; j < responce.content[i].diaryList.length; j++) {
							$(right).append('<div class="RecordDiaryName"><a href="/' + account + '/diary/' + responce.content[i].diaryList[j].id + '">' + responce.content[i].diaryList[j].title + '</a></div>');
						}
						$($('.orderDiaryRecordParent')[i]).append($(right));
					}
				} else {
					noData($('#orderDiaryRecordList'));				}
				$('#orderDiaryRecord').append('<div class="slideUp"><a href="javascript:void(0)">捲起清單▲<a></div>');
				$('#orderDiaryRecord').slideDown();
			}
		});
	});

	$('.cancelOrderDiary').live('click', function() {
		var btn = $(this);
		$.ajax({
			url : '/controller.php',
			type : 'POST',
			dataType : 'json',
			data : {
				action : 'cancelOrderDiary',
				orderId : $(this).attr('orderId')
			},
			success : function(responce) {
				if (responce)
					btn.parents('.orderDiaryRecordBlock').fadeOut();
			}
		});

		if ($('#orderDiaryRecord > .orderDiaryRecordBlock').size() == 0)
			$('#orderDiaryRecord').fadeOut();
	});

});
