<h1>
	Room <small>{$room->building->code} {$room->number}</small>
</h1>

{if $pEdit}
<div class="row pull-right" style="margin-bottom:10px;">
	<div class="col-sm-12">
		<a href="rooms/{$room->id}/edit" class="btn btn-info">Edit Room</a>
		<a href="rooms/{$room->id}/tutorials/{if $room->tutorial}{$room->tutorial->id}{else}new{/if}/edit" class="btn btn-info">Edit Tutorial</a>
	</div>
</div>
{/if}
<br>

<div class="row">
<div class="col-xs-12">
<ul class="nav nav-pills nav-justified room-pills" style="margin-top:2em;">
	<li {if $mode == 'basic'}class="active"{/if}>
		<a data-toggle="pill" href="rooms/{$room->id}?mode=basic#basicInfo">Basic Info</a>
	</li>
	{if $room->tutorial}
	<li {if $mode == 'tutorial'}class="active"{/if}>
		<a data-toggle="pill" href="rooms/{$room->id}?mode=tutorial#tutorial">Room Tutorial</a>
	</li>
	{/if}
	<li {if $mode == 'software'}class="active"{/if}>
		<a data-toggle="pill" href="rooms/{$room->id}?mode=software#software">Software/Equipment</a>
	</li>
	{if $notes && $pEdit}
		<li {if $mode == 'notes'}class="active"{/if}>
			<a data-toggle="pill" href="rooms/{$room->id}?mode=notes#notes">Notes</a>
		</li>
	{/if}
</ul>

<div class="tab-content">
<div id="basicInfo" class="tab-pane fade {if $mode == 'basic'}in active{/if}" style="margin-top:3em;">
	<h3>Basic info</h3>
	<div class="view-room-details">
		<dl class="dl-horizontal">
			<dt>Type</dt>
			<dd>{$room->type->name}</dd>
			<dt>Building</dt>
			<dd>{$room->building->name}</dd>
			{if $room->description}
			<dt>Description</dt>
			<dd>{$room->description}</dd>
			{/if}
			{if $room->scheduledBy}
			<dt>Scheduled By</dt>
			<dd>{$room->scheduledBy}</dd>
			{/if}
			{if $room->supportedBy}
			<dt>Supported By</dt>
			<dd>{$room->supportedBy}</dd>
			{/if}
			<dt>Capacity</dt>
			<dd>{if $room->capacity}{$room->capacity}{else}N/A{/if}</dd>
		</dl>
	</div>
	{if $trackUrl}
	<div class="col-sm-12">
		<a href="{$trackUrl}" target="_blank" class="">View all computers and hardware in this room
			<i class="glyphicon glyphicon-new-window"></i>
		</a>
	</div>
	<br><br>
	{/if}

	<table class="table table-bordered table-condensed table-responsive">
		<thead>
			<tr>
				<th style="font-size:2rem;">A/V Equipment</th>
				<th style="font-size:2rem;">In Room?</th>
			</tr>
		</thead>
		<tbody>

		{assign var="facets" value=unserialize($room->facets)}
			
		{foreach $allFacets as $key => $facet}
			<tr>
				<td style="vertical-align:middle;padding:6px;">
					<img src="assets/images/avequipment-{$key}.png" class="img-responsive" style="max-width:50px;padding:3px;display:inline;">
					<span style="margin-left:5px;font-weight:bold">{$facet}</span>
				</td>
				<td class="bg-{if $facets[$key]}success{else}default{/if}" style="vertical-align:middle;padding:6px;">
				{if $facets[$key]}
					<i class="glyphicon glyphicon-ok text-success" style="font-size:1.5rem;"></i>
				{else}
					<i class="glyphicon glyphicon-remove text-danger" style="font-size:1.5rem;"></i>
				{/if}
				</td>
			</tr>			
		{/foreach}
		</tbody>
	</table>

<!-- 	<table class="table table-bordered table-striped table-condensed">
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
			{assign var="facets" value=unserialize($room->facets)}
			<tr>
				<th scope="col" class="">
					{if $room->building->code}{/if}{$room->number}
				</th>
			{foreach $allFacets as $key => $facet}
				<td>{if $facets[$key]}<i class="glyphicon glyphicon-ok text-success"></i>{else}<i class="glyphicon glyphicon-remove text-danger"></i>{/if}</td>
			{/foreach}	
				<td>{$room->capacity}</td>			
			</tr>
		</tbody>
	</table> -->
</div>

<div id="tutorial" class="tab-pane fade {if $mode == 'tutorial'}in active{/if}" style="margin-top:3em;">
	{if $room->tutorial}
		{if $room->tutorial->name}
			<h2>{$room->tutorial->name}</h2>
		{/if}

		{include file="partial:_view.tutorial.html.tpl"}
	{/if}
</div>

<div id="software" class="tab-pane fade {if $mode == 'software'}in active{/if}" style="margin-top:3em;">
 	<h3>Software/Equipment in this room</h3>
	<div class="">
		<div class="row">
	{foreach $room->configurations as $config}
		{if $config->softwareLicenses->count() > 0}
			{if !$config->isBundle}
			<div class="col-sm-12">
				<div class="panel panel-default">
					<div class="panel-heading"><strong>Configuration: {$config->model}</strong></div>
					<div class="panel-body">
						<p>{$config->deviceQuantity} {$config->deviceType}</p>
						<p><strong>Software:</strong></p>
						<ul>
						{foreach $config->softwareLicenses as $license}
							<li>{$license->version->title->name} {$license->version->number}</li>
						{/foreach}
						</ul>
					</div>
				</div>
			</div>
			{/if}
		{/if}
	{/foreach}
		</div>

		<div class="row">
	{foreach $room->configurations as $config}
		{if $config->softwareLicenses->count() > 0}
			{if $config->isBundle}
			<div class="col-sm-12">
				<div class="panel panel-default">
					<div class="panel-heading"><strong>Config Bundle: {$config->model}</strong></div>
					<div class="panel-body">
						<ul>
						{foreach $config->softwareLicenses as $license}
							<li>{$license->version->title->name} {$license->version->number}</li>
						{/foreach}
						</ul>		
					</div>
				</div>
			</div>
			{/if}
		{/if}
	{/foreach}
		</div>

	{if $pRequest}
		<div class="row" style="">
			<div class="col-sm-12">
				<a href="#" class="btn btn-primary">Request Software</a>
			</div>
		</div>
	{/if}
	</div>
</div>

{if $notes && $pEdit}
<div id="notes" class="tab-pane fade {if $mode == 'notes'}in active{/if}" style="margin-top:3em;">
  	<h3>Notes</h3>
	<hr>
	{include file="partial:_view.notes.html.tpl"}
</div>
{/if}

</div>
</div>

</div>
