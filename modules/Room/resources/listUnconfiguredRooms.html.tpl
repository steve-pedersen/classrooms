<h1>Unconfigured rooms</h1>
<p class="lead">These rooms have been automatically imported and have not had fields like room type, description and A/V Equipment configured.</p>

<table class="table table-bordered table-striped table-condensed">
	<thead>
		<tr>
			<th>Room</th>
			<!-- <th>Date Created</th> -->
			<th>Actions</th>
		</tr>
	</thead>
	<tbody>
{foreach $rooms as $room}
	<tr>
		<td>{$room->codeNumber}</td>
		<!-- <td>{if $room->createdDate}{$room->createdDate->format('Y-m-d h:ia')}{else}N/A{/if}</td> -->
		<td>
			<a href="rooms/{$room->id}/edit" class="btn btn-xs btn-info">edit</a>
			<a href="{$room->roomUrl}" class="btn btn-xs btn-primary">view</a>
		</td>
	</tr>
{/foreach}		
	</tbody>
</table>