<div class="container">
	<div class="row">
		<div class="col-xs-12">
			<h1>
			{if $tutorial->id}
				Edit Tutorial <small>{$tutorial->name}</small>
			{else}
				New Tutorial
			{/if}
				<small> for room {$room->codeNumber}</small>
			</h1>
		</div>
	</div>
</div>

<form action="" method="post">
	<div class="container"> 
		<div class="row">
			<div class="col-xs-12 edit-details">
				<div class="form-horizontals">
					<div class="form-group">
						<label for="name" class="control-label">Tutorial Title</label>
						<div class="">
							<input type="text" class="form-control" name="name" value="{if $tutorial->name}{$tutorial->name}{else}Room Tutorial: {$room->codeNumber}{/if}">
						</div>
					</div>
					<div class="form-group">
						<label for="description" class="control-label">Description</label>
			            <div class="form-control-wrapper textarea">
			                <textarea class="text-field form-control wysiwyg" name="description"  rows="15">{$tutorial->description}</textarea>
			            </div>
					</div>
				</div>
			</div>
		</div>

		<div class="controls">
			{generate_form_post_key}
			<input type="hidden" name="tutorialId" value="{$tutorial->id}">
			<button type="submit" name="command[save]" class="btn btn-primary">Save Tutorial</button>
			<button type="submit" name="command[delete]" class="btn btn-danger">Delete</button>
			<a href="rooms" class="btn btn-link pull-right">Cancel</a>
		</div>
	</div>
</form>