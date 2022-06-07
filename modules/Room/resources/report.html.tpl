<h1>Classrooms Report
 - <small>{$selectedSemester}</small>
</h1>

<form class="form-inline" role="form" id="filterForm">
<div class="row">
<div class="col-sm-12">
<div class="well multiselect-filter">
	
	<h2>Filter</h2>

	<div class="row" style="padding:0;margin:1em 0 1em 0;">
	  <div class="col-sm-12" style="padding:0;margin:0;">
		    
	    <div class="form-group" style="padding-right:1em;">
			<select class="form-control" name="t" id="selectedTerm">
			{foreach $semesters as $semester}
				<option value="{$semester.code}" {if $selectedTerm == $semester.code}selected{/if}>
					{$semester.disp}
				</option>
			{/foreach}
			</select>  
	    </div>

	    <div class="form-group" style="padding-right:1em;">
			<select class="form-control" name="dow" id="selectedDow">
				<option value="">Select day of week...</option>
			{foreach $dowCount as $day => $count}
				<option value="{$day}" {if $selectedDow == $day}selected{/if}>
					{$day|ucfirst}
				</option>
			{/foreach}
			</select>  
	    </div>

	    <div class="form-group">
			<div class="checkbox">
				<label>
					<input name="includeOnline" type="checkbox" {if $includeOnline}checked{/if}>  Include synchronous-online courses?
				</label>
			</div>
	    </div>

	    <div class="form-group pull-right">    
	        <button type="submit" class="btn btn-info filter-col">
	            Apply
	        </button>
	        <a href="report" class="btn btn-link filter-col" id="clearFilters">
	            Clear filters
	        </a> 
	    </div>
	  </div>
	</div>


</div>	
</div>
</div>
</form>

<h2>Results</h2>
<p class="lead">{$courseCount} total courses</p>

<canvas
	id="hourChart"
	class="canvas-chart"
	height="120"
	data-type="bar"
	data-color="#284a80"
	data-title="classes per hour"
	data-labels='[{foreach $hoursCount as $hour => $count}"{$hour}:00"{if !$count@last}, {/if}{/foreach}]'
	data-data='[{foreach $hoursCount as $hour => $count}{$count}{if !$count@last}, {/if}{/foreach}]'
	></canvas>

{if !$selectedDow}
<h3>Classes per day</h3>
<canvas
	id="dayChart"
	class="canvas-chart"
	height="120"
	data-type="bar"
	data-color="#321c4a"
	data-title="classes per day"
	data-labels='[{foreach $dowCount as $day => $count}"{$day|ucfirst}"{if !$count@last}, {/if}{/foreach}]'
	data-data='[{foreach $dowCount as $day => $count}{$count}{if !$count@last}, {/if}{/foreach}]'
	></canvas>
{/if}

<br><hr><br>
<div id="course-collapse">
<a role="button" class="btn btn-success" data-toggle="collapse" data-parent="#course-collapse" href="#courses" aria-expanded="true" aria-controls="courses">
	+ Show all courses
</a>
<br><br>
<div class="collapse out" role="tabpanel" id="courses">
<ul>
	{foreach $courses as $course}
	<li><strong>{$course.course->shortName}</strong> - {$course.course->title}
		<ul class="list-unstyled">
			{foreach $course.schedules as $schedule}
			<li>{$schedule}</li>
			{/foreach}
		</ul>
	</li>
	{/foreach}
</ul>
</div>
</div>