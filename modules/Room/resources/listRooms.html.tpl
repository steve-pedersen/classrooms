
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
					<option value="allLabs" {if $allLabsSelected}selected{/if}>Select all Labs</option>
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
			{if $searchQuery}
				<p id="regular-results">{if $rooms}Showing{else}No{/if} search results for "{$searchQuery}"</p>
			{elseif $selectedBuildings || $selectedTypes || $selectedEquipment || $capacity}
				<!-- <h4>Active filters:</h4> -->
				<p class=""> 
				{if $selectedBuildings}
					<strong>Buildings:&nbsp;&nbsp;</strong>
					{foreach $selectedBuildings as $sb => $id}
						<em>
							{$sb}
							<a href="rooms?{$unselectQueries['buildings'][$id]}">
								<i class="glyphicon glyphicon-remove text-danger" style="padding-left:1px;font-size:10px;"></i></a>
							&nbsp;
						</em>
					{/foreach}
					<br>
				{/if}
				{if $selectedTypes}
					<strong>Types:&nbsp;&nbsp;</strong>
					{foreach $selectedTypes as $st => $id}
						<em>
							{$st}
							<a href="rooms?{$unselectQueries['types'][$id]}">
								<i class="glyphicon glyphicon-remove text-danger" style="padding-left:1px;font-size:10px;"></i></a>
							&nbsp;
						</em>
					{/foreach}
					<br>
				{/if}
				{if $selectedEquipment}
					<strong>Equipment:&nbsp;&nbsp;</strong>
					{foreach $selectedEquipment as $se}
						<em>
							{$allAvEquipment[$se]}
							<a href="rooms?{$unselectQueries['equipment'][$se]}">
								<i class="glyphicon glyphicon-remove text-danger" style="padding-left:1px;font-size:10px;"></i></a>
							&nbsp;
						</em>
					{/foreach}
					<br>
				{/if}
				{if $capacity}
					<strong>Capacity:&nbsp;&nbsp;</strong>
					<em>
						{$capacity}
							<a href="rooms?{$unselectQueries['cap']}">
								<i class="glyphicon glyphicon-remove text-danger" style="padding-left:1px;font-size:10px;"></i></a>
							&nbsp;
					</em>
				{/if}
				</p>
			{/if}
			<p id="js-results" style="display:none;"><em><span id="query"></span> <a href="rooms">Reset search query</a></em></p>
		</div>
	</div>
	<div class="col-sm-3">
		<div class="well multiselect-filter" style="">
			<h2>Search </h2>
			<!-- <p class=""><small>Search by building name <strong>or</strong> room number</small></p> -->
		    <form class="form-inline" role="form" id="filterForm2">
		    <div class="form-group">
		    	<input id="searchBox" type="text" name="s" value="{$searchQuery}" class="form-control autocomplete" placeholder="Building or room #">
		    </div>
		    <div class="form-group">
		    	<div class="loader"></div>
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
		    				<a href="{$room->roomUrl}?mode=basic" class="room-link">{if $room->building->code}{$room->building->code} {/if}{$room->number}</a>
		    			</h3>
		    		</li>
		    		<li>{$room->building->name}</li>
		    		<li>{$room->type->name}</li>
		    		{if $room->type->isLab}
		    		<li class="text-dark" style="margin-top:5px;">
		    			<img style="max-width:20px;display:inline-block;" src="assets/images/laboratory.png" class="img-responsive"> <em style="display:inline-block;"><strong style="margin-bottom:-15px;">Lab room</strong></em>
		    		</li>
		    		{/if}
	    		</ul>    				
			</div>
			<div class="col-sm-6 building-image text-center">
				<a href="{$room->roomUrl}" class="room-link">
				<img src="{$room->building->image}" class="img-responsive" style="max-width:100px;" alt="{$room->building->code} building">
				</a>
			</div>
    	</div>

    	{if $room->configured}
    	<div class="col-sm-5 config-info" >
    		<h4>Equipment</h4>
    		<ul class="list-unstyled">
    			<li><a href="{$room->roomUrl}?mode=software"></a></li>
    		{foreach $room->allConfigurations as $config}
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

    		{if $room->hasPendingUpgrade()}
    			{assign var=upgrade value=$room->getPendingUpgrade()}
    			<div class="alert alert-warning">
	    			<strong>
	    				<i class="glyphicon glyphicon-exclamation-sign text-warning"></i>
	    				This room will be upgraded on {$upgrade->upgradeDate->format('m/d/Y')}. 
	    			</strong>
  				{if $upgrade->relocatedTo}
  					<em><a href="{$room->roomUrl}">More info</a></em>.
  				{/if}
    			</div>
    		{/if}
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
    			<a href="{$room->roomUrl}?mode=tutorial" style="font-weight:bold;">{$room->tutorial->name}</a>
    		{else}
    			<span class="text-muted">No tutorial set up for this room.</span>
    		{/if}
    	</div>
    </div>
  </div>
</div>

{/foreach}
