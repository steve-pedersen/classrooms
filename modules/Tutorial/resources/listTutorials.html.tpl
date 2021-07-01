<h1>Room Tutorials</h1>

<a href="tutorials/new/edit" class="btn btn-success">Add New Tutorial</a>
<br><br>

<table class="table table-condensed table-striped">
	<thead>
		<tr>
			<th>Name</th>
			<th>Actions</th>
		</tr>
	</thead>
	<tbody>
	{foreach $tutorials as $tutorial}
		<tr>
			<td>{$tutorial->name}</td>
			<td>
				<a href="tutorials/{$tutorial->id}/edit" class="btn btn-primary btn-sm">edit</a>
				<a href="tutorials/{$tutorial->id}" class="btn btn-info btn-sm">preview</a>
			</td>
		</tr>
	{/foreach}		
	</tbody>
</table>
