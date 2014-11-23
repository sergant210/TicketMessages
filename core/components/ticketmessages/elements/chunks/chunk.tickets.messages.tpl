<div class="panel panel-info panel-widget messages-col1 pull-left">
	<div class="panel-heading"><h3 class="panel-title"> Пользователи </h3></div>
	<div class="list-group usersList">
		[[!msgUsers? &groups=`MessageUsers` &tpl=`@INLINE <a href="#" class="list-group-item user4message" data-id="[[+id]]" data-thread="[[+thread]]" data-thread-closed=[[+thread_closed]]><span class="badge messages-count [[+thread_closed_class]]">[[+messages]]</span>[[+fullname]]</a>` &users=`-[[!+modx.user.id]]`]]
	</div>
</div>
<div class="panel panel-info panel-widget messages-col2 pull-left">
	<div class="panel-heading"><h3 class="panel-title pull-left">Сообщения</h3><a href="#" id="refreshMessage" class="pull-right" title="Проверить новые сообщения"><i class="glyphicon glyphicon-refresh"></i></a></div>
	<div class="panel-body">
		[[!TicketMessages?]]
	</div>
</div>