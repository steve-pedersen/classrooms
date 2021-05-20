<div class="container">
	<div class="row">
		<div class="col-xs-12">
		{if $building->inDatasource}
			<h1>Edit Building <small>{$building->name} {$building->code}</small></h1>
		{else}
			<h1>New Building</h1>
		{/if}
		</div>
	</div>
</div>

<form action="" method="post">
	<div class="container"> 

		<div class="row">
			<div class="col-xs-12 edit-details">
				<h2>Building Details</h2>

				<div class="form-horizontal">

					<div class="form-group">
						<label for="name" class="col-sm-2 control-label">Building Name</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" name="name" value="{$building->name}" placeholder="">
						</div>
					</div>

					<div class="form-group">
						<label for="code" class="col-sm-2 control-label">Building Code (Abbreviation)</label>
						<div class="col-sm-4">
							<input type="text" class="form-control" name="code" value="{$building->code}" placeholder="">
						</div>
					</div>
				
				</div>

			</div>
		</div>

		<div class="controls">
			{generate_form_post_key}
			<input type="hidden" name="buildingId" value="{$building->id}">
			<button type="submit" name="command[save]" class="btn btn-primary">Save Building</button>
			{if $building->inDatasource}
			<button type="submit" name="command[delete]" class="btn btn-danger">Delete Building</button>
			{/if}
			<a href="rooms" class="btn btn-link pull-right">Cancel</a>
		</div>
	</div>
</form>