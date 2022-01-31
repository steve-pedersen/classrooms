<h1>Faculty Communication Template
 - <small>{$commType->name}</small>
</h1>

{if $sendSuccess}
<div class="alert alert-info">
	<p>{$sendSuccess}</p>
	<p><strong>If you have made changes to the templates please make sure to save the changes below.</strong></p>
</div>
{/if}

<form action="" method="post">
	{generate_form_post_key}
	<input type="hidden" name="type_id" value={$commType->id}>
	<div class="row">
		<div class="col-xs-8">
			<div class="form-group template-widget">
				<label for="roomMasterTemplate">Master Template for Classroom Email Communications</label>
				<p class="form-text text-muted">
					The Master Template is an outline of the email. It should include intro text, widget tokens, and then any concluding text.
				</p>
				<textarea name="roomMasterTemplate" id="roomMasterTemplate" class="wysiwyg form-control" rows="12">{$comm->roomMasterTemplate}</textarea>
				<span class="help-block">
					You can use the following tokens for context replacements to fill out the template: 
					<code>|%FIRST_NAME%|</code>, <code>|%LAST_NAME%|</code>, <code>|%SEMESTER%|</code>, <code>|%LAB_ROOM_WIDGET%|</code>, <code>|%NONLAB_ROOM_WIDGET%|</code>, <code>|%UNCONFIGURED_ROOM_WIDGET%|</code>, <code>|%NO_ROOM_WIDGET%|</code>, <code>|%CONTACT_EMAIL%|</code>
				</span>
			</div>
		</div>

		<div class="col-xs-4">
			<label id="testroommastertemplate">Test Master Template</label>
			<p class="lead">This will send an email to your account showing how the email will look to you.</p>
			<div class="form-group">
				<label for="rooms-labs" class="sr-only">Lab(s) to include</label>
				<select name="rooms[lab][]" id="rooms-labs" class="multiselect form-control" multiple label="Lab(s) to include">
					<!-- <option value="">Choose Lab(s)</option> -->
				{foreach $labRooms as $room}
					<option value="{$room->id}">
						{$room->building->code} {$room->number}
					</option>
				{/foreach}
				</select>
			</div>
			<div class="form-group">
				<!-- <label for="rooms-nonlabs">Non-Lab room(s) to include</label> -->
				<select name="rooms[nonlab][]" id="rooms-nonlabs" class="multiselect form-control" multiple label="Non-Lab room(s) to include">
					<!-- <option value="">Configured room(s)</option> -->
				{foreach $nonlabRooms as $room}
					<option value="{$room->id}">
						{$room->building->code} {$room->number}
					</option>
				{/foreach}
				</select>
			</div>
			<div class="form-group">
				<!-- <label for="rooms-unconfigured">Unconfigured room(s) to include</label> -->
				<select name="rooms[unconfigured][]" id="rooms-unconfigured" class="multiselect form-control" multiple label="Unonfigured room(s) to include">
					<!-- <option value="">Unconfigured room(s)</option> -->
				{foreach $unconfiguredRooms as $room}
					<option value="{$room->id}">
						{$room->building->code} {$room->number}
					</option>
				{/foreach}
				</select>
			</div>
			<div class="form-group">
				<div class="checkbox">
					<label>
						<input name="rooms[noroom]" type="checkbox"> Include a few sample courses that aren't in physical rooms
					</label>
				</div>
			</div>
			<button type="submit" name="command[send][roomMasterTemplate]" aria-describedby="testroommastertemplate" class="btn btn-light">Send Test</button>
		</div>
	</div>

	<div class="row">
		<div class="col-xs-12">
			<div class="form-group template-widget">
				<label for="labRoom">Lab Room Widget</label>
				<p class="lead">Choose a heading and introduction to go before rooms of this type are listed.</p>
				<textarea name="labRoom" id="labRoom" class="wysiwyg form-control" rows="10">{$comm->labRoom}</textarea>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-xs-12">
			<div class="form-group template-widget">
				<label for="nonlabRoom">Non-Lab Room Widget</label>
				<p class="lead">Choose a heading and introduction to go before rooms of this type are listed.</p>
				<textarea name="nonlabRoom" id="nonlabRoom" class="wysiwyg form-control" rows="10">{$comm->nonlabRoom}</textarea>
			</div>
		</div>
	</div>
	
	<div class="row">
		<div class="col-xs-12">
			<div class="form-group template-widget">
				<label for="unconfiguredRoom">Unconfigured Room</label>
				<p class="lead">Choose a heading and introduction to go before rooms of this type are listed.</p>
				<textarea name="unconfiguredRoom" id="unconfiguredRoom" class="wysiwyg form-control" rows="10">{$comm->unconfiguredRoom}</textarea>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-xs-12">
			<div class="form-group template-widget">
				<label for="noRoom">No Room - Course without a physical location, e.g. online</label>
				<p class="lead">Choose a heading and introduction to go before courses of this type are listed.</p>
				<textarea name="noRoom" id="noRoom" class="wysiwyg form-control" rows="10">{$comm->noRoom}</textarea>
			</div>
		</div>
	</div>

	<div class="controls">
		<button type="submit" name="command[save]" class="btn btn-primary">Save</button>
		<a href="admin/communications" class="btn btn-default pull-right">Cancel</a>
	</div>
</form>