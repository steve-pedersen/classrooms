<a href="admin">Admin dashboard</a> &gt; <a href="admin/roles">Roles and access levels</a>
<h1>{if $accessLevel->inDataSource}Edit access level &ldquo;{$accessLevel->name|escape}&rdquo;{else}Add access level{/if}.</h1>

<form method="post" action="" class="data-entry">
	<div class="field">
		<label for="level-name" class="field-label field-linked">Name</label>
		<div class="field-control">
			<input type="text" class="heading" name="name" id="level-name" value="{$accessLevel->name|escape}">
			{foreach item="error" from=$errorMap.name}<div class="error">{$error}</div>{/foreach}
		</div>
	</div>

	<div class="field">
		<label for="level-description" class="field-label field-linked">Description</label>
		<div class="field-control">
			<textarea name="description" id="level-description" rows="5" cols="64">{$accessLevel->description|escape}</textarea>
			{foreach item="error" from=$errorMap.description}<div class="error">{$error}</div>{/foreach}
		</div>
	</div>

	<div class="field commands">
		{generate_form_post_key}
		<button type="submit" name="command[save]" class="btn btn-primary">{if $accessLevel->inDataSource}Save changes{else}Add{/if}</button>
		{if $accessLevel->inDataSource}<a href="admin/levels/{$accessLevel->id}/delete" class="btn btn-warning">Delete</a>{/if}
		<a href="admin/roles" class="btn btn-default pull-right">Cancel</a>
	</div>
</form>
