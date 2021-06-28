<div class="container">
	<div class="row">
		<div class="col-xs-12">
		{if $category->inDatasource}
			<h1>Edit Category <small>{$category->name} {$category->code}</small></h1>
		{else}
			<h1>New Category</h1>
		{/if}
		</div>
	</div>
</div>

<form action="" method="post">
	<div class="container"> 

		<div class="row">
			<div class="col-xs-12 edit-details">
				<h2>Category Details</h2>
				<div class="form-horizontal">
					<div class="form-group">
						<label for="name" class="col-sm-2 control-label">Category Name</label>
						<div class="col-sm-2">
							<input type="text" class="form-control" name="name" value="{$category->name}" placeholder="">
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="controls">
			{generate_form_post_key}
			<input type="hidden" name="typeId" value="{$category->id}">
			<button type="submit" name="command[save]" class="btn btn-primary">Save Category</button>
			{if $category->inDatasource}
			<button type="submit" name="command[delete]" class="btn btn-danger">Delete Category</button>
			{/if}
			<a href="categories" class="btn btn-link pull-right">Cancel</a>
		</div>
	</div>
</form>