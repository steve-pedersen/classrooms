<h1>Edit Room Metadata</h1>
<div class="row">
		<div class="col-md-3 col-sm-6 col-xs-12">
			<a href="{$app->baseUrl('/buildings')}" class="">
			<div class="panel panel-default">
				<div class="panel-heading text-center">
					<h2 style="font-size:2rem">Buildings</h2>
				</div>
				<div class="panel-body">
					<img style="max-width:100px;margin-left:auto;margin-right:auto;" src="assets/images/building.png" class="img-responsive">
				</div>
			</div>
			</a>
		</div>

		<div class="col-md-3 col-sm-6 col-xs-12">
			<a href="{$app->baseUrl('/types')}" class="">
			<div class="panel panel-default">
				<div class="panel-heading text-center">
					<h2 style="font-size:2rem">Room Types</h2>
				</div>
				<div class="panel-body">
					<img style="max-width:100px;margin-left:auto;margin-right:auto;" src="assets/images/laboratory.png" class="img-responsive">
				</div>
			</div>
			</a>
		</div>	
</div>
<form method="post">

{if !empty($avEquipment)}
	<div class="panel panel-default">
		<div class="panel-heading">
			<h2>A/V Equipment Notes</h2>
		</div>
		<div class="panel-body">
		{foreach $avEquipment as $key => $label}
			<div class="form-group">
				<label for="equipment{$key}">{$label}</label>
				<textarea name="avEquipmentNotes[{$key}]" class="wysiwyg form-control" rows="2" id="equipment{$key}">{$avEquipmentNotes[$key]}</textarea>
			</div>
		{/foreach}
		</div>
	</div>
	{/if}


	<div class="panel panel-default">
	{if !empty($scheduledBy)}
		<div class="panel-heading"><h2>Current "Scheduled By" Departments</h2></div>
		<div class="panel-body">
		
		{foreach $scheduledBy as $key => $sb}
		<div class="form-group">
			<div class="checkbox">
				<label>
					<input name="scheduledBy[{$sb}]" type="checkbox" value="{$sb}" checked> {$sb}
				</label>
			</div>
		</div>
		{/foreach}
		
	{else}
		<div class="panel-body">
	{/if}
		<div class="form-group">
			<label>Add new "Scheduled By" department</label>
			<input type="text" name="scheduledBy[new]" class="form-control">
		</div>
		</div>
	</div>

	<div class="panel panel-default">
	{if !empty($supportedBy)}
		<div class="panel-heading"><h2>Current "Supported By" Departments</h2></div>
		<div class="panel-body">
		{foreach $supportedBy as $key => $sb}
		<div class="form-group">
			<div class="checkbox">
				<label>
					<input name="supportedBy[{$sb}]" type="checkbox" value="{$sb}" checked> {$sb}
				</label>
			</div>
		</div>
		{/foreach}
	{else}
		<div class="panel-body">
	{/if}
		<div class="form-group">
			<label>Add new "Supported By" department</label>
			<input type="text" name="supportedBy[new]" class="form-control">
		</div>
		</div>
	</div>

	
	{if !empty($supportedBy)}
	<div class="panel panel-default">
		<div class="panel-heading">
			<h2>Current "Supported By" Department's default room/email text</h2>
		</div>
		<div class="panel-body">
		
		{foreach $supportedBy as $key => $sb}
		<div class="form-group">
			<label for="text{$sb}">Default text for {$sb}</label>
			<textarea name="supportedByText[{$sb}]" class="wysiwyg form-control" rows="4" id="text{$sb}">{$supportedByText[$sb]}</textarea>
		</div>
		{/foreach}
		</div>
	</div>
	{/if}
	
	{generate_form_post_key}
	<div>
		<button type="submit" name="command[save]" class="btn btn-primary">Save Room Settings</button>
	</div>
</form>