<h1>Manage Email Communications</h1>
<p class="lead">
	This is a list of the email templates that have been created. For each template, you are able to schedule email events (send dates) for a specific semester.
</p>
<hr>
<h2>Communications & Types</h2>
<form method="post" action="admin/communications/new">
<div class="row">
	<div class="col-md-6 col-xs-12 ">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3>Create New Communication</h3>
			</div>
			<div class="panel-body">
				<div class="input-group">
					<select class="form-control" name="type" id="commType" required>
						<option value="">Choose communication type...</option>
					{foreach $communicationTypes as $type}
						<option value="{$type->id}">{$type->name}</option>
					{/foreach}
					</select>
					<span class="input-group-btn">
						<button class="btn btn-success" type="submit">
							<i class="halflings-icon white plus"></i> New Communication
						</button>
					</span>
				</div>
			</div>
		</div>
	</div>

	<div class="col-md-6 col-xs-12 ">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3>Communication Types</h3>
			</div>
			<div class="panel-body">
			{if $communicationTypes}
				<ul class="list-group">
				{foreach $communicationTypes as $type}
					<a href="admin/communications/types/{$type->id}/edit">
						<li class="list-group-item ">
							<strong>{$type->name}</strong>
						</li>
					</a>
				{/foreach}
				</ul>
			{/if}
				<a href="admin/communications/types/new/edit" class="btn btn-success" type="submit">
					<i class="halflings-icon white plus"></i> New Type
				</a>
			</div>
		</div>
	</div>
</div>
{generate_form_post_key}
</form>

<hr>

<h2>Templates & Events</h2>
{if $communications}
<div class="row">
{foreach $communicationTypes as $communicationType}
	<div class="col-md-6 col-xs-12">
	
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3>{$communicationType->name} - <small>Communication Template</small></h3>
			</div>
	{if $communicationType->communications->count()}
		{foreach $communicationType->communications as $comm}
			<div class="panel-body">
				<div class="well">
					Created {$comm->creationDate->format('M j, Y')}
					{if $communicationType->isUpgrade}
						<span class="pull-right label label-info">Upgrade events are created automatically</span>
					{else if $communicationType->communications->count()}
					<a class="btn btn-default btn-sm pull-right" href="admin/communications/{$comm->id}/events/new">
						<i class="halflings-icon plus"></i> New event
					</a>
					{/if}
				</div>
			{if count($comm->events)}
				<strong>Scheduled email events:</strong>
				<div class="list-group">
				{foreach item='event' from=$comm->events}
					<a class="list-group-item" href="admin/communications/{$comm->id}/events/{$event->id}">
						<i class="halflings-icon search"></i> 
						{$event->formatTermYear()} - 
						{if $event->sent}Sent on{else}Sending on{/if} 
						{$event->sendDate->format('M j, Y')}
						{if $event->sent}<span class="badge">sent</span>{/if}
					</a>
				{/foreach}
				</div>
			{else}
				<p>There are no email events scheduled for this communication.</p>
			{/if}
			</div>

			<div class="panel-footer">
				<a class="btn btn-primary btn-xs" href="admin/communications/{$comm->id}">
					<i class="halflings-icon white pencil"></i> edit communication
				</a>
			</div>
		{/foreach}
	{else}
			<div class="panel-body">
				<p>No communication templates have been created for this type.</p>
			</div>
	{/if}
		</div>
	
	</div>
{/foreach}
</div>
{/if}
