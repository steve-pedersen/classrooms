<h1>
	Department <small>{$department->name}</small>
</h1>

<h2>Users</h2>
<ul>
	{foreach $department->users as $user}
		<li>{$user->sfStateId} {$user->firstName} {$user->lastName} {$user->emailAddress} - {$user->position}</li>
	{foreachelse}
		<li>No users in this department</li>
	{/foreach}
</ul>

<hr>

<a href="departments/{$department->id}/edit" class="btn btn-primary">Add User</a>


<h2>Notes</h2>

<ul class="list-unstyled">

{foreach $notes as $note}
	<li>
		{$note->createdBy->fullName} ({$note->createdDate->format('Y-m-d h:i a')}): {$note->message}
		
		{if $note->newValues}
		<a class="collapse-button collapsed" data-toggle="collapse" data-parent="#accordion" href="#noteHistory{$note->id}" aria-expanded="true" aria-controls="noteHistory{$note->id}" style="margin-left: 2em; font-weight: bold;">
			Show History
		</a>
		<div id="accordion">
			<div class="panel-collapse collapse" role="tabpanel" id="noteHistory{$note->id}">
				<ul>
				{foreach $note->newValues as $key => $value}
					<li>
						{$note->newValues[$key]}
					</li>
				{/foreach}
				</ul>
			</div>
		</div>
		{/if}
	</li>
{/foreach}

</ul>