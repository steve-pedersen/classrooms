<h1>Edit Room Metadata</h1>

<form method="post">
	<div class="">
	{if !empty($scheduledBy)}
		<div class="form-group">
			<h2>Current "Scheduled By" Departments</h2>
		{foreach $scheduledBy as $key => $sb}
			<div class="checkbox">
				<label>
					<input name="scheduledBy[{$sb}]" type="checkbox" value="{$sb}" checked> {$sb}
				</label>
			</div>
		{/foreach}
		</div>
	{/if}
		<div class="form-group">
			<label>Add new "Scheduled By" department</label>
			<input type="text" name="scheduledBy[new]" class="form-control">
		</div>
	</div>
<hr>
	<div class="">
	{if !empty($supportedBy)}
		<div class="form-group">
			<h2>Current "Supported By" Departments</h2>
		{foreach $supportedBy as $key => $sb}
			<div class="checkbox">
				<label>
					<input name="supportedBy[{$sb}]" type="checkbox" value="{$sb}" checked> {$sb}
				</label>
			</div>
		{/foreach}
		</div>
	{/if}
		<div class="form-group">
			<label>Add new "Supported By" department</label>
			<input type="text" name="supportedBy[new]" class="form-control">
		</div>
	</div>
<hr>
	<div class="">
	{if !empty($supportedBy)}
		<div class="form-group">
			<h2>Current "Supported By" Department's default room/email text</h2>
		{foreach $supportedBy as $key => $sb}
			<label for="text{$key}">Default text for {$key}</label>
			<textarea name="supportedByText[{$key}]" class="wysiwyg form-control" rows="4" id="text{$key}">{$supportedByText[$key]}</textarea>
		{/foreach}
		</div>
	{/if}
	</div>
<hr>
	{generate_form_post_key}
	<div>
		<button type="submit" name="command[save]" class="btn btn-primary">Save Room Metadata</button>
	</div>
</form>