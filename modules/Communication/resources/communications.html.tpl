<h1>Manage Email Communications Campaign</h1>

{if $communications}
<div class="row">
	<div class="col-md-6 col-xs-12">
	{foreach item='comm' from=$communications}
		<div class="panel panel-default">
			<div class="panel-heading">
				Created ({$comm->creationDate->format('M j, Y')})
				<a class="btn btn-default btn-xs pull-right" href="admin/communications/{$comm->id}/events/new">
					<i class="halflings-icon plus"></i> New event
				</a>
			</div>

			<div class="panel-body">
			{if count($comm->events)}
				<div class="list-group">
				{foreach item='event' from=$comm->events}
					<a class="list-group-item" href="admin/communications/{$comm->id}/events/{$event->id}">
						<i class="halflings-icon search"></i> {$event->formatTermYear()} - {$event->sendDate->format('M j, Y')}{if $event->sent}<span class="badge">sent</span>{/if}
					</a>
				{/foreach}
				</div>
			{else}
				<a class="btn btn-primary btn-xs" href="admin/communications/{$comm->id}/events/new">
					<i class="halflings-icon white plus"></i> Create an event
				</a>
			{/if}
			</div>

			<div class="panel-footer">
				<a class="btn btn-primary btn-xs" href="admin/communications/{$comm->id}">
					<i class="halflings-icon white pencil"></i> edit
				</a>
			</div>
		</div>
	{/foreach}
	</div>
</div>
{/if}

<a href="admin/communications/new" class="btn btn-primary btn-xs">
	<i class="halflings-icon white plus"></i> Create new communication
</a>