<h1>
{if $room->tutorial->name}
	{$room->tutorial->name}
{else}
	Room <small>{$room->building->code} {$room->number}</small>
{/if}
</h1>

{if $pAdmin}
<div class="row pull-right" style="margin-bottom: 1em;">
	<div class="col-sm-12">
		<a href="rooms/{$room->id}/edit" class="btn btn-primary">Edit Room</a>
		<a href="rooms/{$room->id}/tutorials/{if $room->tutorial}{$room->tutorial->id}{else}new{/if}/edit" class="btn btn-primary">Edit Tutorial</a>
	</div>
</div>
{/if}

<div class="view-room-details">
	<p><strong>Type:</strong> {$room->type->name}</p>
	<p><strong>Building:</strong> {$room->building->name}</p>
	<p>{$room->description}</p>
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
				<a href="rooms/{$room->id}">{if $room->building->code}{/if}{$room->number}</a>
			</th>
		{foreach $allFacets as $key => $facet}
			<td>{if $facets[$key]}<i class="glyphicon glyphicon-ok text-success"></i>{else}<i class="glyphicon glyphicon-remove text-danger"></i>{/if}</td>
		{/foreach}	
			<td>{$room->capacity}</td>			
		</tr>
	</tbody>
</table>

{if $room->tutorial}
	{include file="partial:_view.tutorial.html.tpl"}
{/if}