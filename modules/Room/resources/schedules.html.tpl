
<h1>Room Schedules</h1>

<div class="row">
	<div class="col-sm-12">
		<div class="well multiselect-filter">
			<h2>Filter</h2>
			<form class="form-inline" role="form" id="filterForm">
		    <div class="form-group" id="autcompleteContainer">
		    	<!-- <label for="u" class="">Capacity</label> -->
				<input type="text" id="auto" name="auto" value="{$selectedUser}" class="form-control account-autocomplete" placeholder="Search user..."> 
				<!-- <input type="hidden" name="record" value="0"> -->
		    	<div class="search-container"></div>
		    </div>
		    
		    <div class="form-group">
				<select class="form-control" name="t">
				{foreach $semesters as $semester}
					<option value="{$semester.code}" {if $selectedTerm == $semester.code}selected{/if}>
						{$semester.disp}
					</option>
				{/foreach}
				</select>  
		    </div>
		    <div class="form-group">    
		        <button type="submit" class="btn btn-info filter-col">
		            Apply
		        </button>
		        <a href="schedules" class="btn btn-link filter-col" id="clearFilters">
		            Clear filters
		        </a> 
		    </div>
			</form>
		</div>	
	</div>

</div>

<div id="userResultMessage" style="display:none;">
	Showing rooms for the following users:
	<span id="userResultList"></span>
</div>


{foreach $scheduledRooms as $scheduledRoom}
	{assign var="room" value=$scheduledRoom.room}
	

<div class="panel panel-default room-card" id="{$room->id}">
  <div class="panel-body">
    <div class="row equal" style="min-height: 8rem;">
    	<div class="col-sm-3 room-number" style="display:inline;">
			
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

    	<div class="col-sm-9 config-info" >
    		<h4>Schedules</h4>
    		<table class="table table-condensed table-striped">
    			<thead>
    				<tr>
    					<th>Instructor</th>
    					<th>Course</th>
    					<th>Details</th>
    				</tr>
    			</thead>
    			<tbody>
	    		{foreach $scheduledRoom.schedules as $scheduledCourse}
	    			{foreach $scheduledCourse as $schedule}

	    			{assign var="details" value=unserialize($schedule->schedules)}
	    			<tr>
	    				<td>
	    					{$schedule->faculty->lastName}, {$schedule->faculty->firstName} {$schedule->faculty->id}
	    				</td>
	    				<td>{$schedule->course->fullDisplayName}</td>
	    				<td>
	    					<ul class="list-unstyled">
	    					{foreach $details as $detail}
	    						<li>
	    							{$detail.stnd_mtg_pat} {$detail.start_time} to {$detail.end_time}
	    						</li>
	    					{/foreach}
	    					</ul>
	    				</td>
	    			</tr>
	    			{/foreach}
	    		{/foreach}
    			</tbody>
    		</table>
    	</div>
    	
    </div>
  </div>
</div>

{/foreach}
