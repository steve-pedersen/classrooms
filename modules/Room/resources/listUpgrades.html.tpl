<h1>
{if $status == 'pending'}
	Pending Upgrades
{else if $status == 'complete'}
	Completed Upgrades
{/if}
</h1>
<p class="lead">View the status of room upgrades.</p>

<div class="panel panel-default">
	<!-- <div class="panel-heading"></div> -->
	<div class="panel-body">
		<form class="form-inline">
			<div class="form-group">
				<label for="status">Select upgrade status </label>
				<select class="form-control" name="status" id="status" style="margin-left:5px;">
					<option value="pending" {if $status == 'pending'}selected{/if}>Pending</option>
					<option value="complete" {if $status == 'complete'}selected{/if}>Complete</option>
				</select>
			</div>
			<div class="form-group" style="margin-left:5px;">
				<button type="submit" class="btn btn-primary">Apply</button>
			</div>
		</form>
	</div>
</div>

<table class="table table-bordered table-striped table-condensed">
	<thead>
		<tr>
			<th>Room</th>
			<th>Upgrade Date</th>
			<th>Relocated to</th>
			<th>Actions</th>
		</tr>
	</thead>
	<tbody>
	{foreach $upgrades as $upgrade}
		{assign var=room value=$upgrade->room}
		<tr>
			<td>
				<strong><a href="{$room->roomUrl}">{$room->codeNumber}</a></strong>
			</td>
			<td>
				{if $upgrade->upgradeDate}{$upgrade->upgradeDate->format('m/d/Y')}{else}N/A{/if}
				{if $upgrade->isComplete}
					<span class="text-muted"> - complete</span>
				{/if}
			</td>
			<td>
				{if $upgrade->relocatedTo}
					<strong><a href="{$upgrade->relocatedTo->getRoomUrl()}">{$upgrade->relocatedTo->getCodeNumber()}</a></strong>
				{else}
					N/A
				{/if}
			</td>
			<td>
				<a href="rooms/{$room->id}/edit" class="btn btn-xs btn-info">edit room</a>
				<a href="rooms/{$room->id}/upgrades/{$upgrade->id}/edit" class="btn btn-xs btn-default">edit upgrade</a>
			</td>
		</tr>		
	{foreachelse}
		<tr>
			<td colspan="4">No {$status} upgrades found.</td>
		</tr>
	{/foreach}	
	</tbody>
</table>