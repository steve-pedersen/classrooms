<h1>Room Tutorials</h1>

<a href="tutorials/new/edit" class="btn btn-success">Add New Tutorial</a>
<br><br>

<div id="accordion">
<table class="table table-condensed table-striped table-bordered">
	<thead>
		<tr>
			<th>Name</th>
			<th>
				Rooms used in
			</th>

			<th>Actions</th>
		</tr>
	</thead>
	<tbody>
	{foreach $tutorials as $tutorial}
		<tr>
			<td>{$tutorial->name}</td>
			<td>
				{if count($tutorial->rooms) > 0}
				<a role="button" class="btn btn-default btn-xs  collapse-button collapsed" data-toggle="collapse" data-parent="#accordion" href="#rooms{$tutorial->id}" aria-expanded="true" aria-controls="rooms{$tutorial->id}">
					Show/hide all rooms&nbsp;
				</a>
				<div class="panel-collapse collapse" role="tabpanel" id="rooms{$tutorial->id}">
					<ul class="list-unstyled">
						{foreach $tutorial->rooms as $room}
							<li><strong><a href="{$room->roomUrl}">{$room->codeNumber}</a></strong></li>
						{/foreach}
					</ul>
				</div>
				{/if}
			</td>
			<td class="text-">
				<a href="tutorials/{$tutorial->id}/edit" class="btn btn-primary btn-xs ">edit</a>
				<a href="tutorials/{$tutorial->id}" class="btn btn-info btn-xs ">preview</a>
			</td>
		</tr>
	{/foreach}		
	</tbody>
</table>
</div>