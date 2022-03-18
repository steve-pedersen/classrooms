<div class="container">
	<div class="row">
		<div class="col-xs-12">
		{if $type->inDatasource}
			<h1>Edit Communication Type <small>{$type->name}</small></h1>
		{else}
			<h1>New Communication Type</h1>
		{/if}
		</div>
	</div>
</div>
<hr>
<form action="" method="post">
	<div class="container"> 

		<div class="row">
			<div class="col-xs-12 edit-details">
				<div class="form-horizontal">
					<div class="form-group">
						<label for="name" class="col-sm-3 control-label">Type Name</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" name="name" value="{$type->name}" placeholder="Lab, Non-Lab, etc.">
						</div>
					</div>
					<div class="form-group">
						<label for="isUpgrade" class="col-sm-3 control-label">
							Is this a room upgrade notification?
						</label>
						<div class="col-sm-9">
							<input type="checkbox" name="isUpgrade" {if $type->isUpgrade}checked{/if}>
						</div>
					</div>
					<div class="form-group">
						<label for="includeCoursesWithoutRooms" class="col-sm-3 control-label">
							Include any courses without rooms (e.g. online)?
						</label>
						<div class="col-sm-9">
							<input type="checkbox" name="includeCoursesWithoutRooms" {if $type->includeCoursesWithoutRooms}checked{/if}>
						</div>
					</div>
					<div class="form-group">
						<label for="includeUnconfiguredRooms" class="col-sm-3 control-label">
							Include unconfigured rooms?
						</label>
						<div class="col-sm-9">
							<input type="checkbox" name="includeUnconfiguredRooms" {if $type->includeUnconfiguredRooms}checked{/if}>
						</div>
					</div>
					<div class="form-group">
						<label for="isLab" class="col-sm-3 control-label">
							<span href="#" data-toggle="tooltip" data-placement="top" title="Communication templates created with this communication-type will include only classrooms of the following specified room-types.">
								Choose room types for this communication type
							</span>
							<small class="help-block text-muted">N/A for upgrades</small>
						</label>
						<div class="col-sm-9">
                        <ul class="list-unstyled">
                    	{foreach $roomTypes as $roomType}
                            <li class="">
                                <input class="custom-control-input" name="roomTypes[{$roomType->id}]" id="roomTypes[{$roomType->id}]" type="checkbox" {if $type->roomTypes->has($roomType)}checked{/if}>
                                <label class="" for="roomTypes[{$roomType->id}]">
                                	{$roomType->name}{if $roomType->isLab}<sup class="text-info"><i class="glyphicon glyphicon-asterisk"></i></sup>{/if}
                                </label>
                            </li>
                        {/foreach}
                        </ul>
                        <span class="pull-right text-info" style=""> <i class="glyphicon glyphicon-asterisk"></i> <em>Lab room type</em></span>
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