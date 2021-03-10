
<h1>Classrooms</h1>

<div class="well multiselect-filter">
	<h2>Filter</h2>
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
    <div class="form-group">
    	<input type="text" name="s" class="form-control autocomplete" placeholder="Search...">
    </div>
	{if $canEdit}
    <div class="form-group" id="addNew">
		<a href="rooms/new/edit" class="btn btn-success">Add New Room</a>
    </div>
	{/if}
	</form>
</div>

{foreach $rooms as $room}
	{assign var="facets" value=unserialize($room->facets)}

<div class="panel panel-default room-card" id="{$room->id}">
  <div class="panel-body">
    <div class="row equal" style="min-height: 9rem;">
    	<div class="col-sm-4 room-number" style="display:inline;">
			
			<div class="col-sm-6">
	    		<ul class="list-unstyled">
		    		<li>
		    			<h3>
		    				<a href="rooms/{$room->id}?mode=basic" class="room-link">{if $room->building->code}{$room->building->code} {/if}{$room->number}</a>
		    			</h3>
		    		</li>
		    		<li>{$room->building->name}</li>
		    		<li>{$room->type->name}</li>
	    		</ul>    				
			</div>
			<div class="col-sm-6 building-image text-center">
				<a href="rooms/{$room->id}" class="room-link">
				<img src="assets/images/buildings-{$room->building->code|lower}.jpg" class="img-responsive" style="max-width:100px;" alt="{$room->building->code} building">
				</a>
			</div>
			
    	</div>
    	<div class="col-sm-5 config-info" >
    		<h4>Equipment</h4>
    		<ul class="list-unstyled">
    			<li><a href="rooms/{$room->id}?mode=software"></a></li>
    		{foreach $room->configurations as $config}
    			{if !$config->isBundle}
    				<li>{$config->deviceQuantity} {$config->deviceType}</li>
    			{/if}
    		{/foreach}
    			<li>
				{foreach $facets as $key => $facet}
					{$allFacets[$key]}{if !$facet@last}, {/if}
				{/foreach}
    			</li>
    		</ul>
    	</div>
    	<div class="col-sm-3 tutorial-info">
    		<h4>Tutorial</h4>
    		{if $room->tutorial->name}
    			<a href="rooms/{$room->id}?mode=tutorial">{$room->tutorial->name}</a>
    		{else}
    			No tutorial set up for this room.
    		{/if}
    	</div>
    </div>
  </div>
</div>

{/foreach}


<!-- <table class="table table-bordered table-striped table-condensed">
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
</table> -->