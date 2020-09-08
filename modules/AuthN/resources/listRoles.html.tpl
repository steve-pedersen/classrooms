<a href="admin">Admin dashboard</a>
<h1>Roles and access levels. {if $showAll}Be careful!{/if}</h1>

{if $showAll}
<p class="opportunity">
	You are living dangerously. I would recommend going back to the <a href="admin/roles">normal list of roles and access levels</a>.
</p>
{/if}

<h2>Roles.</h2>
<ul class="headlines">
{foreach item="role" from=$roleList}
	<li><a href="admin/roles/{$role->id}">{$role->name|escape}</a> <span class="minor detail">{$role->description|strip_tags}</span></li>
{/foreach}
	<li class="minor detail"><a href="admin/roles/new">Add new role</a></li>
</ul>

<h2>Access levels.</h2>
<ul class="headlines">
{foreach item="accessLevel" from=$accessLevelList}
    <li><a href="admin/levels/{$accessLevel->id}">{$accessLevel->name|escape}</a> <span class="minor detail">{$accessLevel->description|strip_tags}</span></li>
{/foreach}
	<li class="minor detail"><a href="admin/levels/new">Add new access level</a></li>
</ul>

{if !$showAll}
<p class="minor detail">
	If you really, really know what you're doing, there's a <a href="admin/roles/all">list of all roles (including internal ones)</a>.
	Tread carefully.
</p>
{/if}