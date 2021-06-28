<h1>Developers</h1>

<a href="developers/new/edit" class="btn btn-success">Add New Developer</a>
<br><br>

<ul>
{foreach $developers as $developer}
	<li><a href="developers/{$developer->id}/edit">{$developer->name}</a></li>
{/foreach}
</ul>
