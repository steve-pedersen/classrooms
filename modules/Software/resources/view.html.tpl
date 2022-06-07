<h1>
	Software Title <small>{$title->name}</small>
</h1>

{if $pEdit}
<div class="row pull-right" style="">
	<div class="col-sm-12">
		<a href="software/{$title->id}/edit" class="btn btn-primary">Edit Software</a>
	</div>
</div>
{/if}
<br>
<div class="view-software-details">

	<dl class="dl-horizontal">
		<dt>Title</dt>
		<dd>{$title->name}</dd>
		<dt>Developer</dt>
		<dd>{$title->developer->name}</dd>
		<dt>Category</dt>
		<dd>{if $title->parentCategory}{$title->parentCategory->name} > {/if}{$title->category->name}</dd>
		<dt>Description</dt>
		<dd>{if $title->description}{$title->description}{else}--{/if}</dd>
	{if $title->compatibleSystems}
		<dt>Compatible Operating Systems</dt>
		<dd>
		{foreach $title->compatibleSystems as $system}
			{$system}{if !$system@last}<br>{/if}
		{/foreach}
		</dd>
	{/if}
	{if $pEdit && $title->internalNotes}
		<dt class="text-muted">Internal Notes</dt>
		<dd class="text-muted">{$title->internalNotes}</dd>
	{/if}
		<br>
		<dt>Versions & Licenses</dt>
		<dd>
			<table class="table table-bordered table-condensed table-striped">
				<thead>
					<tr>
						<th>Version</th>
						<th>License #</th>
						<th>Expires</th>
						<th>Seats</th>
						<th>Description</th>
					</tr>
				</thead>
				<tbody>
		{foreach $title->versions as $version}
			{if $version->licenses->count() > 0}
			{foreach $version->licenses as $license}
				{assign var=checked value=false}
				{foreach $config->softwareLicenses as $l}
					{if $l->id == $license->id}{assign var=checked value=true}{/if}
				{/foreach}
				<tr>
					<th>{$license->version->number}</th>
					<td>{$license->number}</td>
					<td>
						{if $license->expirationDate}
							{$license->expirationDate->format('m/d/Y')}
						{else}
							N/A
						{/if}
					</td>
					<td>{$license->seats}</td>
					<td>{$license->description|truncate:100}</td>
				</tr>
			{/foreach}
			{else}
				<tr>
					<th>{$version->number}</th>
					<td>N/A</td>
					<td>N/A</td>
					<td>N/A</td>
					<td>N/A</td>
				</tr>
			{/if}
		{/foreach}
				</tbody>
			</table>
		</dd>
	</dl>

</div>

{if $title->roomsUsedIn}
<h2>Rooms used in</h2>
<ul>
{foreach $title->roomsUsedIn as $room}
	<li><a href="{$room->roomUrl}">{$room->codeNumber}</a></li>
{/foreach}
</ul>
{/if}

{if $notes && $pEdit}
	<hr>
	<h2>Notes</h2>
	{include file="partial:_view.notes.html.tpl"}
{/if}