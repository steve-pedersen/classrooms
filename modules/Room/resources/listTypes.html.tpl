<h1>Room types</h1>

<a href="types/new/edit" class="btn btn-success">Add New Type</a>
<br><br>

<ul>
{foreach $types as $type}
	{if $type->name == 'Lab'}
		<li>{$type->name} <span class="text-muted">(can't edit name of lab)</span></li>
	{else}
		<li><a href="types/{$type->id}/edit">{$type->name}</a></li>
	{/if}
{/foreach}
</ul>
