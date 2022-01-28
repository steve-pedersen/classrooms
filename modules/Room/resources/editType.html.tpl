<div class="container">
	<div class="row">
		<div class="col-xs-12">
		{if $type->inDatasource}
			<h1>Edit Room Type <small>{$type->name} {$type->code}</small></h1>
		{else}
			<h1>New Room Type</h1>
		{/if}
		</div>
	</div>
</div>
<hr>
<form action="" method="post">
	<div class="container"> 

		<div class="row">
			<div class="col-xs-12 edit-details">
				<h2>Room Type Details</h2>
				<div class="form-horizontal">
					<div class="form-group">
						<label for="name" class="col-sm-3 control-label">Type Name</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" name="name" value="{$type->name}" placeholder="">
						</div>
					</div>
					<div class="form-group">
						<label for="isLab" class="col-sm-3 control-label">
							This room type is a <u href="#" data-toggle="tooltip" data-placement="top" title="Lab type rooms typically have hardware and software information that may be configured for that room.">Lab</u>
						</label>
						<div class="col-sm-9">
							<input type="checkbox" id="isLab" name="isLab" {if $type->isLab}checked{/if}>
						</div>
					</div>
				</div>
			</div>
		</div>
<hr>
		<div class="controls">
			{generate_form_post_key}
			<input type="hidden" name="typeId" value="{$type->id}">
			<button type="submit" name="command[save]" class="btn btn-primary">Save Type</button>
			{if $type->inDatasource}
			<button type="submit" name="command[delete]" class="btn btn-danger">Delete Type</button>
			{/if}
			<a href="rooms" class="btn btn-link pull-right">Cancel</a>
		</div>
	</div>
</form>