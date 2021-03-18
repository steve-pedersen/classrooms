
<h1>Classrooms</h1>
	{if $pEdit}
    <div class="form-group" id="addNew">
		<a href="rooms/new/edit" class="btn btn-success">Add New Room</a>
    </div>
	{/if}

<div class="row">
	<div class="col-sm-9">
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
		<!--     <div class="form-group">
				<select id="filter-titles" class="multiselect form-control" name="titles[]" multiple label="Software Titles">
				{foreach $titles as $title}
					<option value="{$title->id}" {if $selectedTitles && in_array($title->id, $selectedTitles)}selected{/if}>
						{$title->name}
					</option>
				{/foreach}
				</select>  
		    </div> -->
		    <div class="form-group">
				<select id="filter-equipment" class="multiselect form-control" name="equipment[]" multiple label="Equipment">
				{foreach $allFacets as $key => $equipment}
					<option value="{$key}" {if $selectedEquipment && in_array($key, $selectedEquipment)}selected{/if}>
						{$allFacets[$key]}
					</option>
				{/foreach}
				</select>  
		    </div>

		    <div class="form-group">
		    	<!-- <label for="cap" class="">Capacity</label> -->
				<input type="text" id="cap" name="cap" value="{$capacity}" class="form-control" placeholder="Capacity..."> 
		    </div>
		    <div class="form-group">    
		        <button type="submit" class="btn btn-info filter-col">
		            Apply
		        </button>
		        <a href="rooms" class="btn btn-link filter-col" id="clearFilters">
		            Clear filters
		        </a> 
		    </div>
			</form>
		</div>	
	</div>
	<div class="col-sm-3">
		<div class="well multiselect-filter" >
			<h2>Search</h2>
		    <form class="form-inline" role="form" id="filterForm2">
		    <div class="form-group">
		    	<input type="text" name="s" class="form-control autocomplete" placeholder="Search...">
		    </div>

			</form>
		</div>			
	</div>
</div>


{foreach $rooms as $room}
	{assign var="facets" value=unserialize($room->facets)}

<div class="panel panel-default room-card" id="{$room->id}">
  <div class="panel-body">
    <div class="row equal" style="min-height: 8rem;">
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
    			<li><strong>A/V: </strong>
				{foreach $facets as $key => $facet}
					{$allFacets[$key]}{if !$facet@last}, {/if}
				{/foreach}
    			</li>
    		</ul>
    	</div>
    	{if $room->tutorial->name}
    	<div class="col-sm-3 tutorial-info">
    		<h4>Tutorial</h4>
    		{if $room->tutorial->name}
    			<a href="rooms/{$room->id}?mode=tutorial" style="font-weight:bold;">{$room->tutorial->name}</a>
    		{else}
    			No tutorial set up for this room.
    		{/if}
    	</div>
    	{/if}
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