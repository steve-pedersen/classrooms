<h1>
	Room <small>{$room->building->code} {$room->number}</small>
</h1>

{if $pAdmin}
<div class="row pull-right" style="">
	<div class="col-sm-12">
		<a href="rooms/{$room->id}/edit" class="btn btn-primary">Edit Room</a>
		<a href="rooms/{$room->id}/tutorials/{if $room->tutorial}{$room->tutorial->id}{else}new{/if}/edit" class="btn btn-primary">Edit Tutorial</a>
	</div>
</div>
{/if}
<br>
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

<div class="">
	<h2>Software in this room</h2>
	<div class="row">
{foreach $room->configurations as $config}
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
{/foreach}
	</div>
	<div class="row" style="">
		<div class="col-sm-12">
			<a href="#" class="btn btn-primary">Request Software</a>
		</div>
	</div>
</div>


{if $room->tutorial}
	{if $room->tutorial->name}
		<h2>{$room->tutorial->name}</h2>
	{/if}

	{include file="partial:_view.tutorial.html.tpl"}
{/if}