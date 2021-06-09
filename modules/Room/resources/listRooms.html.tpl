
<h1>Classrooms</h1>
	{if $pEdit}
    <div class="form-group" id="addNew">
		<a href="rooms/new/edit" class="btn btn-success">Add New Room</a>
    </div>
	{/if}

<div class="row filters">
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

		    <div class="form-group">
				<select id="filter-equipment" class="multiselect form-control" name="equipment[]" multiple label="Equipment">
				{foreach $allAvEquipment as $key => $equipment}
					<option value="{$key}" {if $selectedEquipment && in_array($key, $selectedEquipment)}selected{/if}>
						{$allAvEquipment[$key]}
					</option>
				{/foreach}
				</select>  
		    </div>

		    <div class="form-group">
		    	<!-- <label for="cap" class="">Capacity</label> -->
				<input type="text" id="cap" name="cap" value="{$capacity}" class="form-control" placeholder="Capacity..."> 
		    </div>
		    {if $pFaculty}
		    <div class="form-group">
					<div class="checkbox">
						<label>
							<input name="display" type="checkbox"> Show my rooms only
						</label>
					</div>
		    </div>
		    {/if}
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
		<div class="well multiselect-filter" style="">
			<h2>Search</h2>
			<!-- <p class=""><small>Search by building name <strong>or</strong> room number</small></p> -->
		    <form class="form-inline" role="form" id="filterForm2">
		    <div class="form-group">
		    	<input id="searchBox" type="text" name="s" class="form-control autocomplete" placeholder="Building or room #">
		    </div>

			</form>
		</div>			
	</div>
</div>

<div id="userResultMessage" style="display:none;">
	Showing rooms for the following users:
	<span id="userResultList"></span>
</div>

{foreach $rooms as $room}
	{assign var="avEquipment" value=unserialize($room->avEquipment)}

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
				<img src="{$room->building->image}" class="img-responsive" style="max-width:100px;" alt="{$room->building->code} building">
				</a>
			</div>
    	</div>

    	{if $room->configured}
    	<div class="col-sm-5 config-info" >
    		<h4>Equipment</h4>
    		<ul class="list-unstyled">
    			<li><a href="rooms/{$room->id}?mode=software"></a></li>
    		{foreach $room->configurations as $config}
    			{if !$config->isBundle}
    				<li>{$config->deviceQuantity} {$config->deviceType}</li>
    			{/if}
    		{/foreach}
    		{if $avEquipment}
    			<li><strong>A/V: </strong>
				{foreach $avEquipment as $key => $equipment}
					{$allAvEquipment[$key]}{if !$equipment@last}, {/if}
				{/foreach}
    			</li>
    		{/if}
    		</ul>
    	</div>
    	{else}
    	<div class="col-sm-5 config-info">
    		<h4>Equipment</h4>
    		<span class="{if !$avEquipment}text-muted{/if}">{$room->description}</span>
    	</div>    	
    	{/if}

    	<div class="col-sm-3 tutorial-info">
    		<h4>Tutorial</h4>
    		{if $room->tutorial->name}
    			<a href="rooms/{$room->id}?mode=tutorial" style="font-weight:bold;">{$room->tutorial->name}</a>
    		{else}
    			<span class="text-muted">No tutorial set up for this room.</span>
    		{/if}
    	</div>
    </div>
  </div>
</div>

{/foreach}
