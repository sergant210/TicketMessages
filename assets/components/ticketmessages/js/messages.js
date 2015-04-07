var Messages = {
	initialize: function() {
		if (typeof window['prettyPrint'] != 'function') {
			document.write('<script src="'+TicketsConfig.jsUrl+'lib/prettify/prettify.js"><\/script>');
			document.write('<link href="'+TicketsConfig.jsUrl+'lib/prettify/prettify.css" rel="stylesheet">');
		}
		if(!jQuery().ajaxForm) {
			document.write('<script src="'+TicketsConfig.jsUrl+'lib/jquery.form.min.js"><\/script>');
		}
		if(!jQuery().jGrowl) {
			document.write('<script src="'+TicketsConfig.jsUrl+'lib/jquery.jgrowl.min.js"><\/script>');
		}
		if(!jQuery().sisyphus) {
			document.write('<script src="'+TicketsConfig.jsUrl+'lib/jquery.sisyphus.min.js"><\/script>');
		}
		// Forms listeners
		$(document).on('submit', '#comment-form', function(e) {
			Messages.comment.save(this, $(this).find('[type="submit"]')[0]);
			e.preventDefault();
			return false;
		});
		// submit
		$(document).on('click touchend', '#comment-form .submit', function(e) {
			Messages.comment.save(this.form, this);
			e.preventDefault();
			return false;
		});
		// Hotkeys
		$(document).on('keydown', '#comment-form', function(e) {
			if (e.keyCode == 13) {
				if (e.shiftKey && (e.ctrlKey || e.metaKey)) {
					$(this).submit();
				}
			}
		});
		//todo Refresh messages
		var refreshMsg = setInterval(Messages.comment.getnewmessages, msg_refresh_interval);
		$(document).on('click','#refreshMessage',function(e){
			e.preventDefault();
			Messages.comment.getnewmessages();

		});
		//todo Choose User
		$(document).on('click','.usersList a.user4message',function(e){
			e.preventDefault();
			$(".usersList a.active").removeClass('active');
			$(this).addClass('active');
			Messages.comment.getmessages(this);

		});
		$(document).ready(function() {
			if (TicketsConfig.enable_editor == true) {
				$('#comment-editor').markItUp(TicketsConfig.editor.comment);
			}

			$.jGrowl.defaults.closerTemplate = '<div>[ '+TicketsConfig.close_all_message+' ]</div>';
		});
	}

	,comment: {
		//todo get the new user messages
		getnewmessages: function() {
			var user = $(".usersList a.active");
			if (!user) {return false;}
			var thread = $(user).data('thread');
			if (!thread) {return false;}

			$.post(TicketsConfig.actionUrl, {action: 'comment/getlist', thread: thread, ctx: TicketsConfig.ctx}, function(response) {
				if (response.success == true ) {
					for (var k in response.data.comments) {
						if (response.data.comments.hasOwnProperty(k)) {
							Messages.comment.insert(response.data.comments[k], true);
						}
					}
					var count = $('.ticket-comment').size();
					$('.messages-count', '.usersList .active').text(count);
				}
			},'json');
		}
		//todo Get user messages
		,getmessages: function(user) {
			var thread = $(user).data('thread');
			if (!thread) {return false;}

			$.post(TicketsConfig.actionUrl, {action: 'message/getlist', thread: thread, ctx: TicketsConfig.ctx}, function(response) {
				if (response.success == true ) {
					$("#comments").html(response.data);
					var count = $('.ticket-comment').size();
					$('.messages-count', '.usersList .active').text(count);
					if ($(user).data('thread-closed') == false) {
						$("#comment-form").show();
						Messages.utils.goto('comment-form');
						$('#comment-editor').focus();
					} else {
						$("#comment-form").hide();
						Messages.utils.goto('thread-alert');
					}
				} else {
					$("#comments").html(response.message);
					$("#comment-form").hide();
				}

			},'json');
		}
		,save: function(form, button)  {
			//Verification of the specified user
			if ($("a.active",".usersList") != null) {
				var message_user = $("a.active", ".usersList");
				if (message_user.size() == 0) {
					Messages.Message.error("Выберите пользователя!");
					return false;
				}
			}
			var action = 'comment/save';
			if ($(button).hasClass('send-message')) {
				action = 'message/save';
			}
			$('[name="thread"]', form).val($(".usersList a.active").data('thread'));
			$(form).ajaxSubmit({
				data: {action: action}
				,url: TicketsConfig.actionUrl
				,form: form
				,button: button
				,dataType: 'json'
				,beforeSubmit: function() {
					clearInterval(window.timer);
					$('.error',form).text('');
					$(button).attr('disabled','disabled');
					return true;
				}
				,success: function(response) {
					$(button).removeAttr('disabled');
					if (response.success) {
						Messages.forms.comment(false);
						$('#comment-preview-placeholder').html('').hide();
						$('#comment-editor',form).val('');
						$('.ticket-comment .comment-reply a').show();

						// autoPublish = 0
						if (!response.data.length && response.message) {
							Messages.Message.info(response.message);
						}
						else {
							Messages.comment.insert(response.data.comment);
							Messages.utils.goto($(response.data.comment).attr('id'));
						}
						Messages.comment.getlist();
						prettyPrint();
					}
					else {
						Messages.Message.error(response.message);
						if (response.data) {
							var i, field;
							for (i in response.data) {
								field = response.data[i];
								$(form).find('[name="' + field.field + '"]').parent().find('.error').text(field.message)
							}
						}
					}
					if (response.data.captcha) {
						$('input[name="captcha"]', form).val('').focus();
						$('#comment-captcha', form).text(response.data.captcha);
					}
				}
			});
			return false;
		}

		,getlist: function() {
			var form = $('#comment-form');
			var thread = $('[name="thread"]', form);
			if (!thread) {return false;}
			$.post(TicketsConfig.actionUrl, {action: 'comment/getlist', thread: thread.val()}, function(response) {
				for (var k in response.data.comments) {
					if (response.data.comments.hasOwnProperty(k)) {
						Messages.comment.insert(response.data.comments[k], true);
					}
				}
				var count = $('.ticket-comment').size();
				$('#comment-total, .ticket-comments-count').text(count);
				$('.messages-count','.usersList .active').text(count);

			}, 'json');
			return true;
		}

		,insert: function(data, remove) {
			var comment = $(data);
			var parent = $(comment).attr('data-parent');
			var id = $(comment).attr('id');
			var exists = $('#' + id);
			var children = '';

			if (exists.length > 0) {
				var np = exists.data('newparent');
				comment.attr('data-newparent', np);
				data = comment[0].outerHTML;
				if (remove) {
					children = exists.find('.comments-list').html();
					exists.remove();
				}
				else {
					exists.replaceWith(data);
					return;
				}
			}

			if (parent == 0 && TicketsConfig.formBefore) {
				$('#comments').prepend(data)
			}
			else if (parent == 0) {
				$('#comments').append(data)
			}
			else {
				var pcomm = $('#comment-'+parent);
				if (pcomm.data('parent') != pcomm.data('newparent')) {
					parent = pcomm.data('newparent');
					comment.attr('data-newparent', parent);
					data = comment[0].outerHTML;
				}
				else if (TicketsConfig.thread_depth) {
					var level = pcomm.parents('.ticket-comment').length;
					if (level > 0 && level >= (TicketsConfig.thread_depth - 1)) {
						parent = pcomm.data('parent');
						comment.attr('data-newparent', parent);
						data = comment[0].outerHTML;
					}
				}
				$('#comment-'+parent+' > .comments-list').append(data);
			}

			if (children.length > 0) {
				$('#' + id).find('.comments-list').html(children);
			}
		}
	}

	,forms: {
		comment: function(focus) {
			if (focus !== false) {focus = true;}
			clearInterval(window.timer);

			$('#comment-new-link').hide();

			var form = $('#comment-form');
			$('.time', form).text('');
			$('.ticket-comment .comment-reply a:hidden').show();

			$('#comment-preview-placeholder').hide();
			$('input[name="parent"]',form).val(0);
			$('input[name="id"]',form).val(0);
			$(form).insertAfter('#comment-form-placeholder').show();

			$('#comment-editor', form).val('');
			if (focus) {
				$('#comment-editor', form).focus();
			}
			return false;
		}
	}
	,utils: {
		goto: function(id) {
			$('html, body').animate({
				scrollTop: $('#' + id).offset().top
			}, 1000);
		}
	}
};
Messages.Message = {
	success: function(message) {
		if (message) {
			$.jGrowl(message, {theme: 'tickets-message-success'});
		}
	}
	,error: function(message) {
		if (message) {
			$.jGrowl(message, {theme: 'tickets-message-error'/*, sticky: true*/});
		}
	}
	,info: function(message) {
		if (message) {
			$.jGrowl(message, {theme: 'tickets-message-info'});
		}
	}
	,close: function() {
		$.jGrowl('close');
	}
};

Messages.initialize();
