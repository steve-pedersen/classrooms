<h1>
	Room <small>{$room->building->code} {$room->number}</small>
</h1>

{if $canEdit}
<div class="row pull-right" style="margin-bottom:10px;">
	<div class="col-sm-12">
		<a href="rooms/{$room->id}/edit" class="btn btn-primary">Edit Room</a>
		<a href="rooms/{$room->id}/tutorials/{if $room->tutorial}{$room->tutorial->id}{else}new{/if}/edit" class="btn btn-primary">Edit Tutorial</a>
	</div>
</div>
{/if}
<br>

<div class="row">
<div class="col-xs-12">
<ul class="nav nav-pills nav-justified room-pills">
	<li {if $mode == 'basic'}class="active"{/if}>
		<a data-toggle="pill" href="rooms/{$room->id}?mode=basic#basicInfo">Basic Info</a>
	</li>
	<li {if $mode == 'tutorial'}class="active"{/if}>
		<a data-toggle="pill" href="rooms/{$room->id}?mode=tutorial#tutorial">Room Tutorial</a>
	</li>
	<li {if $mode == 'software'}class="active"{/if}>
		<a data-toggle="pill" href="rooms/{$room->id}?mode=software#software">Software</a>
	</li>
	{if $notes && $canEdit}
		<li {if $mode == 'notes'}class="active"{/if}>
			<a data-toggle="pill" href="rooms/{$room->id}?mode=notes#notes">Notes</a>
		</li>
	{/if}
</ul>

<div class="tab-content">
<div id="basicInfo" class="tab-pane fade {if $mode == 'basic'}in active{/if}">
	<h3>Basic info</h3>
	<div class="view-room-details">
		<dl class="dl-horizontal">
			<dt>Type</dt>
			<dd>{$room->type->name}</dd>
			<dt>Building</dt>
			<dd>{$room->building->name}</dd>
			<dt>Description</dt>
			<dd>{$room->description}</dd>
		</dl>
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
	</table>
</div>

<div id="tutorial" class="tab-pane fade {if $mode == 'tutorial'}in active{/if}">
  	<h3>Room Tutorial</h3>
	{if $room->tutorial}
		{if $room->tutorial->name}
			<h2>{$room->tutorial->name}</h2>
		{/if}

		{include file="partial:_view.tutorial.html.tpl"}
	{/if}
</div>

<div id="software" class="tab-pane fade {if $mode == 'software'}in active{/if}">
 	<h3>Software in this room</h3>
	<div class="">
		<div class="row">
			{if $trackUrl}
			<div class="col-sm-12">
				<a href="{$trackUrl}" target="_blank">View all computers and hardware in this room</a>
			</div>
			<br>
			{/if}
	{foreach $room->configurations as $config}
		{if $config->softwareLicenses->count() > 0}
			{if $config@index != 0 && $config@index % 2 == 0}
				</div><div class="row">
			{/if}
			<div class="col-sm-{if $config@total > 1}6{else}12{/if}">
				<div class="panel panel-default">
					<div class="panel-heading"><strong>Configuration: {$config->model}</strong></div>
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

{if $notes && $canEdit}
<div id="notes" class="tab-pane fade {if $mode == 'notes'}in active{/if}">
  	<h3>Notes</h3>
	<hr>
	{include file="partial:_view.notes.html.tpl"}
</div>
{/if}

</div>
</div>

</div>
