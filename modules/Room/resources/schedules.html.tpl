
<h1>Room Schedules
 - <small>{$selectedSemester}</small>
</h1>

<form class="form-inline" role="form" id="filterForm">
<div class="row">
<div class="col-sm-12">
<div class="well multiselect-filter">
	
	<h2>Filter</h2>

	<div class="row" style="padding:0;margin:1em 0 1em 0;">
	  <div class="col-sm-12" style="padding:0;margin:0;">
			
	    {if !$pFaculty}
	    <div class="form-group" id="autcompleteContainer">
				<input type="text" id="auto" name="auto" value="{$selectedUser}" class="form-control account-autocomplete" placeholder="Search user..."> 
	    	<div class="search-container"></div>
	    </div>

	    <div class="form-group">
		    	<input id="searchBox" type="text" name="s" value="{$roomQuery}" class="form-control autocomplete" placeholder="Building or room #">
	    </div>
	    {/if}
		    
	    <div class="form-group" style="padding-right:0em;">
				<select class="form-control" name="t" id="selectedTerm">
				{foreach $semesters as $semester}
					<option value="{$semester.code}" {if $selectedTerm == $semester.code}selected{/if}>
						{$semester.disp}
					</option>
				{/foreach}
				</select>  
	    </div>
    
	    {if !$pFaculty}
	    <div class="form-group" id="currentCourses">
		    <label for="selectedWindow">Show courses occurring:</label><br>
				<select class="form-control" name="window" id="selectedWindow">
					<option value="" {if !$selectedWindow}selected{/if}> &mdash; Anytime &mdash; </option>
				{foreach $windows as $window}
					<option value="{$window.hours}" {if $selectedWindow === $window.hours}selected{/if}>
						{$window.text}
					</option>
				{/foreach}
				</select>  
	    </div>

	    <div class="form-group">
				<div class="checkbox">
					<label>
						<input name="showEmptyRooms" type="checkbox" {if $showEmptyRooms}checked{/if}> Show empty rooms only
					</label>
				</div>
	    </div>
	    {/if}	

	    <div class="form-group pull-right">    
	        <button type="submit" class="btn btn-info filter-col">
	            Apply
	        </button>
	        <a href="schedules" class="btn btn-link filter-col" id="clearFilters">
	            Clear filters
	        </a> 
	    </div>
	  </div>
	</div>

<!-- 	<div class="row" style="padding:0;margin:1em 0 1em 0;">
	  <div class="col-sm-12" style="padding:0;margin:0;">
	    <div class="form-group">
		    <label for="selectedWindow">Show courses occurring:</label><br>
				<select class="form-control" name="window" id="selectedWindow">
					<option value="">Choose window...</option>
				{foreach $windows as $window}
					<option value="{$window.hours}" {if $selectedWindow == $window.hours}selected{/if}>
						{$window.text}
					</option>
				{/foreach}
				</select>  
	    </div> 	
	  </div>
	</div> -->

</div>	
</div>
</div>
</form>

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
		    			<h3 id="room{$room->id}">
		    				<a href="{$room->roomUrl}?mode=basic" class="room-link">{if $room->building->code}{$room->building->code} {/if}{$room->number}</a>
		    			</h3>
		    		</li>
		    		<li>{$room->building->name}</li>
		    		<li>{$room->type->name}</li>
	    		</ul>    				
			</div>
			<div class="col-sm-6 building-image text-center">
				<a href="{$room->roomUrl}" class="room-link">
				<img src="{$room->building->getImage()}" class="img-responsive" style="max-width:100px;" alt="{$room->building->code} building">
				</a>
			</div>
			
    	</div>

    	<div class="col-sm-9 config-info" >
    		<h4>Schedules</h4>
    		<table class="table table-condensed table-striped">
    			<thead>
    				<tr>
    					<th style="width: 30%">Instructor</th>
    					<th style="width: 50%">Course</th>
    					<th style="width: 20%;">Details</th>
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
	    							<strong>{$detail.info.stnd_mtg_pat}</strong> {$detail.info.start_time} to {$detail.info.end_time}
	    						</li>
	    					{/foreach}
	    					</ul>
	    				</td>
	    			</tr>
	    			{/foreach}
		    		{foreachelse}
		    			<tr>
		    				<td colspan="3">
		    					<p class="text-muted">There are no courses scheduled in this room.</p>
		    				</td>
		    			</tr>
	    		{/foreach}
    			</tbody>
    		</table>
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
    	
    </div>
  </div>
</div>
{foreachelse}
	{if $pSupport || $pEdit || $pAdmin}
		{if $selectedUser || $roomQuery}
			<p>No room or schedule info found with your search parameters.</p>
		{else}
			<p>Search by instructor or room.</p>
		{/if}
	{else}
		<p>No room or schedule info found for you.</p>
	{/if}
{/foreach}

{if $onlineCourses}
<div class="panel panel-default room-card" id="{$room->id}">
  <div class="panel-body">
    <div class="row equal" style="min-height: 8rem;">
    	<div class="col-sm-12 room-number" style="display:inline;">
			
    					<h3>
				Courses not in physical rooms
			</h3>
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

	    			{foreach $onlineCourses as $schedule}

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
	    							{$detail.info.stnd_mtg_pat} {$detail.info.start_time} to {$detail.info.end_time}
	    						</li>
	    					{/foreach}
	    					</ul>
	    				</td>
	    			</tr>
	    			{/foreach}

    			</tbody>
    		</table>

    	
    </div>
  </div>
</div>

{/if}
