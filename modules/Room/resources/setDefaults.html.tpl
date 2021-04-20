<h1>Set Defualt Room Description</h1>

<form action="" method="post">
	{generate_form_post_key}
	<div class="row">
		<div class="col-xs-12">
			<div class="form-group template-widget">
				<label for="default-room-description">Default room description</label>
				<p class="lead">Set the default to desciption for rooms.</p>
				<textarea name="default-room-description" id="default-room-description" class="wysiwyg form-control" rows="10">{$defaultRoomDescription}</textarea>
			</div>
		</div>
	</div>

	<div class="controls">
		<button type="submit" name="command[save]" class="btn btn-primary">Save</button>
		<a href="admin" class="btn btn-default pull-right">Cancel</a>
	</div>
</form>