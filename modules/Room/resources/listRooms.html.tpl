
<h1>Classrooms</h1>

{if $canEdit}
<!-- <div class="container-fluid">
<div class="row pull-right" style="margin-bottom: 1em;">
	<div class="col-sm-12">
		<a href="rooms/new/edit" class="btn btn-primary">Add New Room</a>
	</div>
</div>
</div> -->
{/if}

<div class="well multiselect-filter">
	<h2>Filter Rooms</h2>
	<form class="form-inline" role="form" id="filterForm">
    <div class="form-group">
		<select id="filter-buildings" name="buildings[]" class="multiselect form-control" multiple label="Buildings">
		{foreach $buildings as $building}
			<option value="{$building->id}" {if $selectedBuildings && in_array($building->id, $selectedBuildings)}selected{/if}>
				{$building->name}
			</option>
		{/foreach}	
		</select>		        
    </div>
    <div class="form-group">
		<select id="filter-types" class="multiselect form-control" name="types[]" multiple label="Room Types">
		{foreach $types as $type}
			<option value="{$type->id}" {if $selectedTypes && in_array($type->id, $selectedTypes)}selected{/if}>
				{$type->name}
			</option>
		{/foreach}
		</select>     
    </div>

    <div class="form-group">
		<select id="filter-titles" class="multiselect form-control" name="titles[]" multiple label="Software Titles">
		{foreach $titles as $title}
			<option value="{$title->id}" {if $selectedTitles && in_array($title->id, $selectedTitles)}selected{/if}>
				{$title->name}
			</option>
		{/foreach}
		</select>  
    </div>
    <div class="form-group">    
        <button type="submit" class="btn btn-info filter-col">
            Apply
        </button>
        <a href="rooms" class="btn btn-link filter-col" id="clearFilters">
            Clear filters
        </a> 
    </div>
	{if $canEdit}
    <div class="form-group" id="addNew">
		<a href="rooms/new/edit" class="btn btn-success">Add New Room</a>
    </div>
	{/if}
	</form>
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