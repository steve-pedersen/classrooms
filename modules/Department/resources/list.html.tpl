<div class="">
	<h1>Departments</h1>

	<ul>
	{foreach $departments as $department}
		<li><a href="departments/{$department->id}">{$department->name}</a></li>
	{/foreach}
	</ul>
	
	<hr>
	<a href="departments/sync" class="btn btn-primary">Sync Departments</a>
</div>