
{if $pAdmin}
	<fieldset class="field">
		<legend>Roles</legend>
		<ul>
{foreach item="role" from=$roleList}
			<li>
				<input type="checkbox" name="role[{$role->id}]" id="account-role-{$role->id}" {if $account->roles->has($role)}checked{/if}>
				<label for="account-role-{$role->id}">{$role->name|escape}</label>
			</li>
{/foreach}
		</ul>
	</fieldset>
{/if}
