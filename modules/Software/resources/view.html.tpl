<h1>
	Software Title <small>{$title->name}</small>
</h1>

{if $pAdmin}
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
		<dd>{$title->description}</dd>
		<br>
		<dt>Versions & Licenses</dt>
		<dd>
		{foreach $title->versions as $version}
			<strong>Version {$version->number}</strong>
			<ul>
			{foreach $version->licenses as $license}
				<li>
					{$license->number}
					{if $license->seats} | {$license->seats} seats{/if}
					{if $license->expirationDate} | Expires {$license->expirationDate->format('m/d/Y')}{/if}
					{if $license->description} | {$license->description}{/if}
				</li>
			{/foreach}
			</ul>
		{/foreach}
		</dd>
	</dl>

</div>

{if $title->roomsUsedIn}
<h2>Rooms used in</h2>
<ul>
{foreach $title->roomsUsedIn as $room}
	<li><a href="rooms/{$room->id}">{$room->codeNumber}</a></li>
{/foreach}
</ul>
{/if}

{if $notes}
	<hr>
	<h2>Notes</h2>
	{include file="partial:_view.notes.html.tpl"}
{/if}