function openMessages()
{
	$('.messages div').css('display', 'block').html("Loading..");
}

//$(function() {
	/* ============= Sidebar Chat ============= */
	/*
	function sendMessage() {
		var message = $('input[name="messageBox"]');

		if ( message != '' ) {
			$.ajax({
				type: 'post',
				url: 'chat_send.php',
				data: message,
				cache: false,
				success: function(data) {
					$('#chatContent').html(data);
				},
				error: function() {
					alert("There's been an error while sending your message. Please contact <a href=\'profile.php?id=1\'>Toxocious</a> or <a href=\'profile.php?id=2\'>Ephenia</a>.");
				}
			});

			$('input[name="messageBox"]').val('');
			return false;
		}
	}

	function deleteMessage(id) {
		$.ajax({
			type: 'post',
			url: 'ajax/ajax_chat.php',
			data: { request: 'delete', id: id },
			success: function(data) {
				$('#chatContent').html(data);
			},
			error: function() {
				alert("There's been an error while deleting this message. Please contact <a href=\'profile.php?id=1\'>Toxocious</a> or <a href=\'profile.php?id=2\'>Ephenia</a>.");
			}
		});
	}

	function banUser(id) {
		var banReason = $('#banReason').val();
		var banDuration = $('#banDuration').val();
		
		$.ajax({
			type: 'post',
			url: 'ajax/ajax_chat.php',
			data: { request: 'ban', reason: banReason, duration: banDuration, id: id },
			cache: false,
			success: function(data) {
				$('#chatContent').html(data);
			},
			error: function() {
				alert("There's been an error while banning this user. Please contact <a href=\'profile.php?id=1\'>Toxocious</a> or <a href=\'profile.php?id=2\'>Ephenia</a>.");
			}
		});
		
		$('#user_options').hide();
		$('#chatContent').show();
	}

	function userOptions(id) {
		$('#chatContent').hide();
		$('.chat .subhead').hide();
		$('#user_options').show();
		
		$.ajax({
			type: 'post',
			url: 'ajax/ajax_chat.php',
			data: { request: 'user_options', id: id },
			success: function(data) {
				$('#user_options').html(data);
			},
			error: function() {
				$('#user_options').html("There's been an error. Please contact <a href=\'profile.php?id=1\'>Toxocious</a> or <a href=\'profile.php?id=2\'>Ephenia</a>.");
			}
		});
	}

	function hideUserOptions() {
		$('.chat .subhead').show();
		$('#chatContent').show();
		$('#user_options').hide();
	}

	$(function() {
		setInterval(function() {
			$('#chatContent').load('chat_history.php');
		}, 500);
	});
	*/

	/* ============= Private Messaging ============= */
	function showInbox(id) {
			$('#messages').html("<div class='description' style='margin: 5px 0px 0px 0px'>Loading..</div>");
			
			$.ajax({
				type: 'post',
				url: 'messages_ajax.php',
				data: { request: 'show_inbox', id: id },
				success: function(data) {
					$('#messages').html(data);
				},
				error: function() {
					$('#message').html("<div class='error'>An error has occurred while trying to display your inbox.<br />Please contact <a href=\'profile.php?id=1\'>Toxocious</a> or <a href=\'profile.php?id=2\'>Ephenia</a>.");
				}
			});
		}
		
		function showOutbox(id) {
			$('#messages').html("<div class='description' style='margin: 5px 0px 0px 0px'>Loading..</div>");
			
			$.ajax({
				type: 'post',
				url: 'messages_ajax.php',
				data: { request: 'show_outbox', id: id },
				success: function(data) {
					$('#messages').html(data);
				},
				error: function() {
					$('#message').html("<div class='error'>An error has occurred while trying to display your outbox.<br />Please contact <a href=\'profile.php?id=1\'>Toxocious</a> or <a href=\'profile.php?id=2\'>Ephenia</a>.");
				}
			});
		}

		function showMessage(id) {
			$('#messages').html("<div class='description' style='margin: 5px 0px 0px 0px'>Loading..</div>");
			
			$.ajax({
				type: 'post',
				url: 'messages_ajax.php',
				data: { request: 'show_message', id: id },
				success: function(data) {
					$('#messages').html(data);
				},
				error: function() {
					$('#message').html("<div class='error'>An error has occurred while trying to display this private message.<br />Please contact <a href=\'profile.php?id=1\'>Toxocious</a> or <a href=\'profile.php?id=2\'>Ephenia</a>.");
				}
			});
		}
		
		function showDelivery() {
			$('#messages').html("<div class='description' style='margin: 5px 0px 0px 0px'>Loading..</div>");
			
			$.ajax({
				type: 'post',
				url: 'messages_ajax.php',
				data: { request: 'show_delivery' },
				success: function(data) {
					$('#messages').html(data);
				},
				error: function() {
					$('#message').html("<div class='error'>An error has occurred while trying to display the message sending feature.<br />Please contact <a href=\'profile.php?id=1\'>Toxocious</a> or <a href=\'profile.php?id=2\'>Ephenia</a>.");
				}
			});
		}
		
		// ============= POKEMON CENTER AJAX REQUESTS ============= //
		if ( document.URL.indexOf('pokemon_center.php') >= 0 ) {
			function showPokemon(id, tab) {
				$('.overlay').css({ "visibility":"visible" });

				$.ajax({
					type: 'post',
					url: 'ajax/ajax_pokecenter.php',
					data: { request: 'pokemon_statistics', id: id },
					success: function(data) {
						$('.overlay').css({ "display":"none" });
						$('#selectedPokemon').html(data);
					},
					error: function() {
						$('.overlay').css({ "display":"none" });
						$('#selectedPokemon').html('An error has occurred while retrieving this Pokemon\'s data.<br /> Please contact <a href=\'profile.php?id=1\'>Toxocious</a> or <a href=\'profile.php?id=2\'>Ephenia</a>.');
					}
				});
			}

			function changeSlot(id, slot) {
				$('.description').show().html("Loading..");

				$.ajax({
					type: 'post',
					url: 'ajax/ajax_pokecenter.php',
					data: { request: 'slot_change', id: id, slot: slot },
					success: function(data) {
						$('#pokemon_center').html(data);
					},
					error: function() {
						$('#selectedPokemon').html('An error has occurred while changing this Pokemon\'s slot.<br /> Please contact <a href=\'profile.php?id=1\'>Toxocious</a> or <a href=\'profile.php?id=2\'>Ephenia</a>.');
					}
				});
			}
			
			function selectItem(id) {
				$('#selectedItem').html("Loading..");
				
				$.ajax({
					type: 'post',
					url: 'ajax/ajax_pokecenter.php',
					data: { request: 'pokecenter_item', id: id },
					success: function(data) {
						$('#selectedItem').html(data);
					},
					error: function(data) {
						$('#pokemon_center').html(data);
					}
				});
			}
			
			function attachItem(id, slot) {
				$('.description').show().html("Loading..").css({ "margin-bottom":"5px" , "margin-top":"0px" , "width":"100%" });
				
				$.ajax({
					type: 'post',
					url: 'ajax/ajax_pokecenter.php',
					data: { request: 'pokecenter_attachitem', id: id, slot: slot },
					success: function(data) {
						$('#pokemon_center').html(data);
					},
					error: function() {
						$('#pokemon_center').html(data);
					}
				});
			}
			
			function removeItem(id) {
				$('.description').show().html("Loading..").css({ "margin-bottom":"5px" , "margin-top":"0px" , "width":"100%" });
				
				$.ajax({
					type: 'post',
					url: 'ajax/ajax_pokecenter.php',
					data: { request: 'pokecenter_removeitem', id: id },
					success: function(data) {
						$('#pokemon_center').html(data);
					},
					error: function(data) {
						$('#pokemon_center').html(data);
					}
				});
			}
			
			function showRoster(id) {
				$('.overlay').css({ "display":"block" });
				
				$.ajax({
					type: 'post',
					url: 'ajax/ajax_pokecenter.php',
					data: { request: 'pokecenter_roster', id: id },
					success: function(data) {
						$('.overlay').css({ "display":"none" });
						$('#pokemon_center').html(data);
					},
					error: function() {
						$('.overlay').css({ "display":"none" });
						$('#error').show();
						$('#error').html("An error has occurred while attempting to retrive your roster information.");
					}
				});
			}
			
			function showBag(id) {
				$('.description').show().html("Loading..").css({ "margin-bottom":"5px" , "margin-top":"0px" , "width":"100%" });
				
				$.ajax({
					type: 'post',
					url: 'ajax/ajax_pokecenter.php',
					data: { request: 'pokecenter_bag', id: id },
					success: function(data) {
						$('#pokemon_center').html(data);
					},
					error: function() {
						$('#error').show();
						$('#error').html(data);
					}
				});
			}
			
			function showNickname(id) {
				$('.description').show().html("Loading..");
				
				$.ajax({
					type: 'post',
					url: 'ajax/ajax_pokecenter.php',
					data: { request: 'pokecenter_nickname', id: id },
					success: function(data) {
						$('#pokemon_center').html(data);
					},
					error: function() {
						$('#error').show();
						$('#error').html("An error has occurred while attempting to open the nickname tab.");
					}
				});
			}
			
			function showRelease(id) {
				$('.description').show().html("Loading..");
				
				$.ajax({
					type: 'post',
					url: 'ajax/ajax_pokecenter.php',
					data: { request: 'pokecenter_release', id: id },
					success: function(data) {
						$('#pokemon_center').html(data);
					},
					error: function() {
						$('#pokemon_center').html(data);
					}
				});
			}
			
			function changeNickname(event, id, num) {
				event.preventDefault();

				$.ajax({
					type: 'post',
					url: 'ajax/ajax_pokecenter.php',
					data: { request: 'pokecenter_nickchange', id: id, nickname: $('form input[name="nickname' + num + '"]').val() },
					success: function(data) {
						$('#pokemon_center').html(data);
					},
					error: function() {
						$('#pokemon_center').html(data);
					}
				});
			}
		}
		
	/* ========== Evolution Center ========== */
	if ( document.URL.indexOf('evolution_center.php') >= 0 ) {
		function showPokemon(id) {
			$('#selectedPokemon').html("Loading..");
			
			$.ajax({
				type: 'post',
				url: 'ajax/ajax.php',
				data: { request: 'evolution_info', id: id },
				success: function(data) {
					$('#selectedPokemon').html(data);
				},
				error: function(data) {
					$('#selectedPokemon').html(data);
				}
			});
		}

		function evolvePokemon(id) {
			$('#selectedPokemon').html("<div style='padding: 10px;'>Loading..</div>");
			
			$.ajax({
				type: 'post',
				url: 'ajax/ajax_evocenter.php',
				data: { request: 'evolve_pokemon', id: id },
				success: function(data) {
					$('#selectedPokemon').html(data);
				},
				error: function(data) {
					$('#selectedPokemon').html(data);
				}
			});
		}
	}
//});