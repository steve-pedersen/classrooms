<h1>{if !$event->inDatasource}New{/if} Event for {$event->termYear} Faculty Communication</h1>

<form action="" method="post">
	{generate_form_post_key}
	<div class="row">
		<div class="col-xs-4">
			<div class="form-group">
				<label for="sendDate">Date</label>
				<input type="text" name="sendDate" id="sendDate" value="{if $event->sendDate}{$event->sendDate->format('c')}{/if}" class="form-control datepicker timepicker">
			</div>
		</div>
		<div class="col-xs-4">
			<div class="form-group">
				<label for="termYear">Semester Year</label>
				<input type="text" name="termYear" id="termYear" value="{$event->termYear}" class="form-control">
				<small class="text-muted">
					The Year+Semester code for which this email even is being sscheduled for. This code takes the form YYYS where YYY is the year without a zero, e.g. 2019 is 219, and S is 1 for Winter, 3 for Spring, 5 for Summer, and 7 for Fall.
				</small>
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
			</li>
{/foreach}
		</ul>
	</div>
</div>
{/if}