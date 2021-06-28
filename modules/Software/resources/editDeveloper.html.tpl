<div class="container">
	<div class="row">
		<div class="col-xs-12">
		{if $developer->inDatasource}
			<h1>Edit Developer <small>{$developer->name} {$developer->code}</small></h1>
		{else}
			<h1>New Developer</h1>
		{/if}
		</div>
	</div>
</div>

<form action="" method="post">
	<div class="container"> 

		<div class="row">
			<div class="col-xs-12 edit-details">
				<h2>Developer Details</h2>
				<div class="form-horizontal">
					<div class="form-group">
						<label for="name" class="col-sm-2 control-label">Developer Name</label>
						<div class="col-sm-2">
							<input type="text" class="form-control" name="name" value="{$developer->name}" placeholder="">
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="controls">
			{generate_form_post_key}
			<input type="hidden" name="typeId" value="{$developer->id}">
			<button type="submit" name="command[save]" class="btn btn-primary">Save Developer</button>
			{if $developer->inDatasource}
			<button type="submit" name="command[delete]" class="btn btn-danger">Delete Developer</button>
			{/if}
			<a href="developers" class="btn btn-link pull-right">Cancel</a>
		</div>
	</div>
</form>