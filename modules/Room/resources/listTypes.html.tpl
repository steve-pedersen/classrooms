<h1>Room types</h1>

<a href="types/new/edit" class="btn btn-success">Add New Type</a>
<br><br>

<table class="table table-bordered table-striped table-condensed">
	<thead>
		<tr>
			<th>Type Name</th>
			<th># of Rooms</th>
			<th>Is Lab?</th>
		</tr>
	</thead>
	<tbody>
{foreach $types as $type}
	<tr>
		<td><a href="types/{$type->id}/edit">{$type->name}</a></td>
		<td>{if $type->locations}{count($type->locations)}{else}0{/if}</td>
		<td>
		{if $type->isLab}
			<span class="text-success"><i class="glyphicon glyphicon-ok"></i> &nbsp;Yes</span>
		{else}
			<span class="text-danger"><i class="glyphicon glyphicon-remove"></i> &nbsp;No</span>
		{/if}
		</td>
	</tr>
{/foreach}		
	</tbody>
</table>