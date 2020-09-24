<h1>Classrooms</h1>

{if $pAdmin}
<div class="container"><a href="rooms/new/edit" class="btn btn-primary">Create New Room</a></div>
{/if}

<form>
	<div class="row" id="selectBuildingType">
		<div class="form-group col-sm-6">
			<label for="building" class="col-sm-12">Browse Rooms by Building</label>
			<div class="col-sm-8">
				<select class="form-control" name="building">
					<option value="">All</option>
				{foreach $buildings as $building}
					<option value="{$building->id}" {if $selectedBuilding == $building->id}selected{/if}>{$building->name}</option>
				{/foreach}
				</select>
			</div>
			<div class="col-sm-4">
				<button type="submit" class="btn btn-default">Apply</button>
			</div>

		</div>
		<div class="form-group col-sm-6">
			<label for="building" class="col-sm-12">Browse Rooms by Type</label>
			<div class="col-sm-8">
				<select class="form-control" name="type">
					<option value="">All</option>
				{foreach $types as $type}
					<option value="{$type->id}" {if $selectedType == $type->id}selected{/if}>{$type->name}</option>
				{/foreach}
				</select>
			</div>
			<div class="col-sm-4">
				<button type="submit" class="btn btn-default">Apply</button>
			</div>
		</div>
	</div>
</form>


<div class="container">
	<table class="table table-bordered table-striped table-condensed">
		<thead>
			<tr>
				<th scope="Room#">Room #</th>
			{foreach $allFacets as $key => $facet}
				<th scope="{$facet}">{$facet}</th>
			{/foreach}
				<th scope="capacity">Cap</th>
			</tr>
		</thead>
		<tbody>
		{foreach $rooms as $room}
			{assign var="facets" value=unserialize($room->facets)}
			<tr>
				<th scope="col"><a href="rooms/{$room->id}">{$room->number}</a></th>
			{foreach $allFacets as $key => $facet}
				<td>{if $facets[$key]}<i class="glyphicon glyphicon-ok text-success"></i>{else}<i class="glyphicon glyphicon-remove text-danger"></i>{/if}</td>
			{/foreach}	
				<td>{$room->capacity}</td>			
			</tr>
		{/foreach}
		</tbody>
	</table>
</div>