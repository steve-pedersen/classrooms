<h1>
	Department <small>{$department->name}</small>
</h1>

<h2>Users</h2>
<ul>
	{foreach $department->users as $user}
		<li>{$user->sfStateId} {$user->firstName} {$user->lastName} {$user->emailAddress} {$user->position}</li>
	{foreachelse}
		<li>No users in this department</li>
	{/foreach}
</ul>

<hr>

<a href="departments/{$department->id}/edit" class="btn btn-primary">Add User</a>