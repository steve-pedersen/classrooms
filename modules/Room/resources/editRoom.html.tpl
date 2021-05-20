{if $location->id}
	<h1>Edit Room <small><a href="rooms/{$location->id}">{$location->building->name} {$location->number}</a></small></h1>
{else}
	<h1>New Room</h1>
{/if}

<div class="row pull-right" style="margin-bottom: 2em;">
	<div class="col-sm-12">
	{if $location->inDatasource}
		{if $location->tutorial}
			<a href="rooms/{$location->id}/tutorials/{$location->tutorial->id}/edit" class="btn btn-info">Edit Tutorial</a>
		{else}
			<a href="rooms/{$location->id}/tutorials/new/edit" class="btn btn-success">Add New Tutorial</a>
		{/if}
	{/if}
	</div>
</div>


{if $customConfigurations && count($customConfigurations) > 1}
<br><br><br>
<form action="" method="get" id="selectConfiguration">
	<div class="container">
		<hr>
		<div class="row">
			<div class="col-sm-12">
				<div class="form-horizontal">

					<div class="form-group existing-items">
						<label for="configuration" class="col-sm-2 control-label">Selected Configuration</label>
						<div class="col-sm-8">
							<select name="configuration" id="configuration" class="form-control">
								<option value="">Choose Configuration</option>
							{foreach $customConfigurations as $configuration}
								<option value="{$configuration->id}" {if $selectedConfiguration->id == $configuration->id}selected{/if}>{$configuration->model} - {$configuration->location}</option>
							{/foreach}
							</select>
						</div>
						<div class="controls col-sm-2 edit">
							<button class="btn btn-primary pull-right">Go</button>
							<a href="rooms/{$location->id}/configurations/{$selectedConfiguration->id}/edit" data-baseurl="rooms/{$location->id}/configurations/" class="btn btn-info pull-right">Edit</a>
						</div>
					</div>

				</div>
			</div>
		</div>
	</div>
</form>
{/if}

<form action="" method="post">
<div class="container"> 
<div class="row">
	<div class="col-xs-12 edit-details">
				
		<div class="panel panel-default details">
			<div class="panel-heading"><h2>Room Details</h2></div>
			<div class="panel-body">
				<div class="form-horizontal">
					<div class="form-group">
						<label for="building" class="col-sm-2 control-label">Building</label>
						<div class="col-sm-10">
							<select name="room[building]" id="building" class="form-control" required>
								<option value="">Choose Building</option>
							{foreach $buildings as $building}
								<option value="{$building->id}"{if $location->building_id == $building->id} selected{/if}>{$building->code} - {$building->name}</option>
							{/foreach}
							</select>
						</div>
					</div>

					<div class="form-group">
						<label for="type" class="col-sm-2 control-label">Type</label>
						<div class="col-sm-10">
							<select name="room[type]" id="type" class="form-control" required>
								<option value="">Choose Room Type</option>
							{foreach $types as $type}
								<option value="{$type->id}"{if $location->type_id == $type->id} selected{/if}>{$type->name}</option>
							{/foreach}
							</select>
						</div>
					</div>

					<div class="form-group">
						<label for="number" class="col-sm-2 control-label">Room #</label>
						<div class="col-sm-2">
							<input type="text" class="form-control" name="room[number]" value="{$location->number}" placeholder="#">
						</div>
					</div>

					<div class="form-group">
						<label for="description" class="col-sm-2 control-label">Description</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="room[description]" value="{$location->description}" placeholder="Brief description of room">
						</div>
					</div>

					<div class="form-group">
						<label for="capacity" class="col-sm-2 control-label">Capacity</label>
						<div class="col-sm-2">
							<input type="text" class="form-control" name="room[capacity]" value="{$location->capacity}" placeholder="">
						</div>
					</div>

					<div class="form-group">
						<label for="scheduledBy" class="col-sm-2 control-label">Scheduled By</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="room[scheduledBy]" value="{$location->scheduledBy}" placeholder="">
						</div>
					</div>

					<div class="form-group">
						<label for="supportedBy" class="col-sm-2 control-label">Supported By</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="room[supportedBy]" value="{$location->supportedBy}" placeholder="Academic Technology">
						</div>
					</div>

					<div class="form-group">
						<label for="url" class="col-sm-2 control-label">URL</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="room[url]" value="{$location->url}" placeholder="Website URL if applicable">
						</div>
					</div>

					<div class="form-group">
						<label for="url" class="col-sm-2 control-label">A/V Equipment</label>
						<div class="col-sm-10">
							<table class="table table-bordered">
								<thead>
									<tr>
									{foreach $allAvEquipment as $key => $equipment}
										<th scope="{$equipment}">{$equipment}</th>
									{/foreach}
									</tr>
								</thead>
								<tbody>
									<tr>
									{foreach $allAvEquipment as $key => $equipment}
										<td class="text-centers">
											<input type="checkbox" name="room[avEquipment][{$key}]" {if $roomAvEquipment[$key]}checked{/if}>
										</td>
									{/foreach}
									</tr>
								</tbody>
							</table>
						</div>
					</div>

				</div>
				
			</div>
		</div>

		<div class="panel panel-default bundles">
			<div class="panel-heading"><h2>Configuration Bundles</h2></div>
			<div class="panel-body">
				
				<div class="form-horizontal">
					<div class="form-group">
						<label for="bundles" class="col-sm-2 control-label">Available Bundles</label>
						<div class="col-sm-10">
						
							<table class="table table-bordered table-condensed table-striped">
								<thead>
									<tr>
										<th scope="col"></th>
										<th scope="col">Model</th>
										<th scope="col"># of software licenses</th>
									</tr>
								</thead>
								<tbody>
							{foreach $bundles as $bundle}
								<tr>
									<td class="text-center">
										<input type="checkbox" name="bundles[{$bundle->id}]" id="bundles[{$bundle->id}]" {if $location->configurations->has($bundle)}checked{/if}>
									</td>
									<td><label for="bundles[{$bundle->id}]">{$bundle->model}</label></td>
									<td>{$bundle->softwareLicenses->count()}</td>
								</tr>
							{/foreach}
								</tbody>
							</table>
						
						</div>
					</div>
				</div>
			</div>
		</div>

		{include file="partial:_configurations.html.tpl"}

		<div class="panel panel-default notes">
			<div class="panel-heading"><h2>Internal Notes</h2></div>
			<div class="panel-body">
				<div class="form-group">
					<label for="internalNote" class="col-sm-2 control-label">Add Internal Note</label>
					<div class="col-sm-10">
						<input type="text" class="form-control" name="internalNote" value="" placeholder="">
					</div>
				</div>

				{if count($location->internalNotes)}
				<div class="form-group">
					<label for="internalNote" class="col-sm-2 control-label">
						<a class="collapse-button collapsed" data-toggle="collapse" data-parent="#accordion1" href="#showNotes" aria-expanded="true" aria-controls="showNotes" style="margin-bottom: 1em;">
							Past Notes &nbsp;
						</a>
					</label>
					<div class="col-sm-10">
					<div id="accordion1">
						<div class="panel-collapse collapse" role="tabpanel" id="showNotes">
							<ul class="">
							{foreach $location->internalNotes as $note}
								<li>
									<strong>{$note->addedBy->fullname} on {$note->createdDate->format('Y/m/d')}:</strong> {$note->message}
								</li>
							{/foreach}
							</ul>
						</div>
					</div>
					</div>
				</div>
				{/if}

			</div>
		</div>


		<div class="controls">
			{generate_form_post_key}
			<input type="hidden" name="locationId" value="{$location->id}">
			<button type="submit" name="command[save]" class="btn btn-primary">Save Room</button>
			<a href="rooms/{$location->id}" class="btn btn-default">Cancel</a>
			<button type="submit" name="command[delete]" class="btn btn-danger pull-right">Delete</button>
		</div>

	</div>
</div>
</div>
</form>

{if $notes}
	<hr>
	<h2>Notes</h2>
	{include file="partial:_view.notes.html.tpl"}
{/if}
