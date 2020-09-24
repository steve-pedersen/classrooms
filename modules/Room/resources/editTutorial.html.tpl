<div class="container">
	<div class="row">
		<div class="col-xs-12">
		{if $tutorial->id}
			<h1>Edit Tutorial <small>{$tutorial->name}</small></h1>
		{else}
			<h1>New Tutorial</h1>
		{/if}
		</div>
	</div>
</div>

<form action="" method="post">
	<div class="container"> 

		<div class="row">
			<div class="col-xs-12 edit-details">
				<h2>Tutorial Details</h2>
				<div class="form-horizontal">
					<div class="form-group">
						<label for="name" class="col-sm-2 control-label">Tutorial Title</label>
						<div class="col-sm-2">
							<input type="text" class="form-control" name="name" value="{$tutorial->name}" placeholder="">
						</div>
					</div>

					<div class="form-group">
						<label for="description" class="col-sm-2 control-label">Description</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="description" value="{$tutorial->description}" placeholder="To be replaced with a wysiwyg">
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="controls">
			{generate_form_post_key}
			<input type="hidden" name="tutorialId" value="{$tutorial->id}">
			<button type="submit" name="command[save]" class="btn btn-primary">Save Tutorial</button>
			<button type="submit" name="command[delete]" class="btn btn-danger">Delete Tutorial</button>
			<a href="rooms" class="btn btn-link pull-right">Cancel</a>
		</div>
	</div>
</form>