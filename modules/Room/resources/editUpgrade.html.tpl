<div class="container">
	<div class="row">
		<div class="col-xs-12">
		{if $upgrade->inDatasource}
			<h1>Edit Room <em>{$room->codeNumber}</em> Upgrade 
				<small>on {$upgrade->upgradeDate->format('m/d/Y')} relocating to {$upgrade->relocatedTo->codeNumber}</small>
			</h1>
		{else}
			<h1>New Room Upgrade for <em>{$room->codeNumber}</em></h1>
		{/if}
		</div>
	</div>
</div>
<hr>
<form action="" method="post">
	<div class="container"> 
		<div class="row">
			<div class="col-xs-12 edit-details">
				<h2>Upgrade Details</h2>
				<div class="form-horizontal">
					<div class="form-group">
						<label for="upgradeDate" class="col-sm-3 control-label">Upgrade date</label>
						<div class="col-sm-9">
							<input type="text" class="form-control datepicker" name="upgradeDate" value="{if $upgrade->inDatasource}{$upgrade->upgradeDate->format('m/d/Y')}{/if}" required>
						</div>
					</div>
					<div class="form-group">
						<label for="relocatedTo" class="col-sm-3 control-label">Classes relocated to</label>
						<div class="col-sm-9">
							<select class="form-control selectpicker" id="relocatedTo" name="relocatedTo">
								<option>Select room...</option>
							{foreach $locations as $location}
								<option data-tokens="{$location->id}" value="{$location->id}" {if $upgrade->relocated_to == $location->id}selected{/if}>
									{$location->codeNumber}
								</option>
							{/foreach}
							</select>
						</div>
					</div>
					<div class="form-group">
						<label for="sendNotification" class="col-sm-3 control-label">
							Send email notification?
						</label>
						<div class="col-sm-9">
							<input type="checkbox" id="sendNotification" name="sendNotification" checked disabled>
						</div>
					</div>
				</div>
			</div>
		</div>
	<hr>
		<div class="controls">
			{generate_form_post_key}
			<input type="hidden" name="typeId" value="{$type->id}">
			
		{if !$upgrade->inDatasource}
			<button type="submit" name="command[save]" class="btn btn-success">Save Upgrade</button>
		{else}
			<button type="submit" name="command[update]" class="btn btn-info">Update Info</button>
			<button type="submit" name="command[delete]" class="btn btn-danger">Delete Upgrade</button>
		{/if}
			<a href="rooms/{$room->id}/edit" class="btn btn-link pull-right">Cancel</a>
		</div>
	</div>
</form>