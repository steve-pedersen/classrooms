
<h1>Classrooms</h1>

{if $pAdmin}
<div class="container-fluid">
<div class="row pull-right" style="margin-bottom: 1em;">
	<div class="col-sm-12">
		<a href="rooms/new/edit" class="btn btn-primary">Add New Room</a>
	</div>
</div>
</div>
{/if}

<div class="panel panel-default">
    <div class="panel-heading"><h2>Filter Rooms</h2></div>
    <div class="panel-body">
        <form class="form-inline" role="form">
            <div class="form-group">
                <label class="filter-col" for="pref-perpage">Building</label>
				<select class="form-control" name="building" style="margin-right:2em;">
					<option value="">All</option>
				{foreach $buildings as $building}
					<option value="{$building->id}" {if $selectedBuilding == $building->id}selected{/if}>{$building->name}</option>
				{/foreach}
				</select>                             
            </div>
            <div class="form-group">
                <label class="filter-col" for="pref-perpage">Room Type</label>
				<select class="form-control" name="type" style="margin-right:2em;">
					<option value="">All</option>
				{foreach $types as $type}
					<option value="{$type->id}" {if $selectedType == $type->id}selected{/if}>{$type->name}</option>
				{/foreach}
				</select>                         
            </div>
            <div class="form-group">    
                <button type="submit" class="btn btn-info filter-col">
                    Apply
                </button>
                {if $hasFilters}
                <a href="rooms" class="btn btn-link filter-col">
                    Clear filters
                </a> 
                {/if}
            </div>
        </form>
    </div>
</div>

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
			<th scope="col" class="">
				<a href="rooms/{$room->id}">{if $room->building->code}{/if}{$room->number}</a>
			</th>
		{foreach $allFacets as $key => $facet}
			<td>{if $facets[$key]}<i class="glyphicon glyphicon-ok text-success"></i>{else}<i class="glyphicon glyphicon-remove text-danger"></i>{/if}</td>
		{/foreach}	
			<td>{$room->capacity}</td>			
		</tr>
	{foreachelse}
		<tr>
			<td colspan="{count($allFacets) + 2}">There are no rooms that meet your search criteria.</td>
		</tr>
	{/foreach}
	</tbody>
</table>