<h1>Room Tutorials</h1>

<a href="tutorials/new/edit" class="btn btn-success">Add New Tutorial</a>
<br><br>

<ul>
{foreach $tutorials as $tutorial}
	<li>{$tutorial->name}
		<a href="tutorials/{$tutorial->id}/edit" class="btn btn-primary btn-sm">edit</a>
		<a href="tutorials/{$tutorial->id}" class="btn btn-info btn-sm">preview</a>
	</li>
{/foreach}
</ul>
