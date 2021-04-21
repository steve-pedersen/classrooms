<h1>
	Faculty Communication - 
	<small>
	{if !$event->inDatasource}
		New Scheduled Email Event
	{else}
		Scheduled Email Event for {$event->formatTermYear()}
	{/if}
	</small>
</h1>

<form action="" method="post">
	{generate_form_post_key}
	<div class="row">
		<div class="col-xs-4">
			<div class="form-group">
				<label for="sendDate">Date</label>
				<input type="text" name="sendDate" id="sendDate" value="{if $event->sendDate}{$event->sendDate->format('c')}{/if}" class="form-control datepicker timepicker" required>
			</div>
		</div>
		<div class="col-xs-4">
			<div class="form-group">
				<label for="term">Semester</label>
				<select name="term" id="term" value="{$eventTerm}" class="form-control" required>
					<option value="">Semester...</option>
					{foreach $terms as $code => $term}
						<option value="{$code}" {if $code == $eventTerm}selected{/if}>{$term}</option>
					{/foreach}
				</select>
			</div>
		</div>
		<div class="col-xs-4">
			<div class="form-group">
				<label for="year">Year</label>
				<select name="year" id="year" value="{$eventYear}" class="form-control" required>
					<option value="">Year...</option>
					{foreach $years as $code => $year}
						<option value="{$code}" {if $code == $eventYear}selected{/if}>{$year}</option>
					{/foreach}
				</select>
			</div>
		</div>
	</div>

	<div class="controls">
		<button type="submit" name="command[save]" class="btn btn-primary">Save</button>
		<a href="admin/faculty/communications" class="btn btn-default pull-right">Cancel</a>
	</div>
</form>

{if $event->logs && count($event->logs)}
<div class="panel panel-default">
	<div class="panel-heading">
		<h2 class="panel-title">Event Logs</h2>
	</div>

	<div class="panel-body">
		<ul class="list-group">
{foreach item='entry' from=$event->logs}
			<li class="list-group-item">
				Sent to {$entry->faculty->fullName} ({$entry->emailAddress}) at {$entry->creationDate->format("c")}
				<div>{$entry->message}</div>
			</li>
{/foreach}
		</ul>
	</div>
</div>
{/if}