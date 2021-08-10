<h1>
	Room <small>{$room->building->code} {$room->number}</small>
</h1>


{if $pEdit}
<div class="row pull-right" style="margin-bottom:10px;">
	<div class="col-sm-12">
		<a href="rooms/{$room->id}/edit" class="btn btn-info">Edit Room</a>
	</div>
</div>
{/if}
<br>

<div class="row">
<div class="col-xs-12">
<ul class="nav nav-pills nav-justified room-pills" style="margin-top:2em;">
	<li {if $mode == 'basic'}class="active"{/if}>
		<a data-toggle="pill" href="{$room->roomUrl}?mode=basic#basicInfo">Basic Info</a>
	</li>
	{if $room->tutorial}
	<li {if $mode == 'tutorial'}class="active"{/if}>
		<a data-toggle="pill" href="{$room->roomUrl}?mode=tutorial#tutorial">Room Tutorial</a>
	</li>
	{/if}
	{if $room->hasSoftwareOrHardware()}
	<li {if $mode == 'software'}class="active"{/if}>
		<a data-toggle="pill" href="{$room->roomUrl}?mode=software#software">Software/Equipment</a>
	</li>
	{/if}
	{if $notes && $pEdit}
		<li {if $mode == 'notes'}class="active"{/if}>
			<a data-toggle="pill" href="{$room->roomUrl}?mode=notes#notes">Notes</a>
		</li>
	{/if}
	{if $pFaculty}
		<li>
			<a  href="schedules">My Course Schedules</a>
		</li>
	{/if}
</ul>

<div class="tab-content">
<div id="basicInfo" class="tab-pane fade {if $mode == 'basic'}in active{/if}" style="margin-top:3em;">
	<h3>Basic info</h3>
	<div class="view-room-details">
		<dl class="dl-horizontal">
			<dt>Room Type</dt>
			<dd>{if $room->type}{$room->type->name}{else}Unknown{/if}</dd>
			<dt>Building</dt>
			<dd>{$room->building->name}</dd>
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
		{if $room->description}
			<dt>Description</dt>
			<dd>{$room->description}</dd>
		{/if}

		{if $supportText}
			<dt>Support Information</dt>
			<dd>{$supportText}</dd>
		{/if}

		{if $pEdit}
			{if $room->uniprint}<dt>Uniprint</dt><dd>{$room->uniprint}</dd>{/if}
			{if $room->uniprintQueue}<dt>Uniprint Queue</dt><dd>{$room->uniprintQueue}</dd>{/if}
			{if $room->releaseStationIp}<dt>Release Station IP</dt><dd>{$room->releaseStationIp}</dd>{/if}
			{if $room->printerModel}<dt>Printer Model</dt><dd>{$room->printerModel}</dd>{/if}
			{if $room->printerIp}<dt>Printer IP</dt><dd>{$room->printerIp}</dd>{/if}
			{if $room->printerServer}<dt>Printer Server</dt><dd>{$room->printerServer}</dd>{/if}
		{/if}
		</dl>
	</div>
	{if $trackUrl && ($pEdit || $pSupport)}
	<div class="row">
	<div class="col-sm-12">
		<a href="{$trackUrl}" target="_blank" class="">View all computers and hardware in this room
			<i class="glyphicon glyphicon-new-window"></i>
		</a>
	</div>
	</div>
	<br><br>
	{/if}

{if $room->configured}

	<table class="table table-bordered table-condensed table-responsive">
		<thead>
			<tr>
				<th style="font-size:2rem;">A/V Equipment</th>
				<th style="font-size:2rem;">In Room?</th>
				<th style="font-size:2rem;width:25%;">Notes</th>
			</tr>
		</thead>
		<tbody>

		{assign var="avEquipment" value=unserialize($room->avEquipment)}
			
		{foreach $allAvEquipment as $key => $equipment}
			<tr>
				<td style="vertical-align:middle;padding:6px;">
					<img src="assets/images/avequipment-{$key}.png" class="img-responsive" style="max-width:50px;padding:3px;display:inline;">
					<span style="margin-left:5px;font-weight:bold">{$equipment}</span>
				</td>
				<td class="bg-{if $avEquipment[$key]}success{else}default{/if}" style="vertical-align:middle;padding:6px;">
				{if $avEquipment[$key]}
					<i class="glyphicon glyphicon-ok text-success" style="font-size:1.5rem;"></i>
				{else}
					<i class="glyphicon glyphicon-remove text-danger" style="font-size:1.5rem;"></i>
				{/if}
				</td>
				<td style="vertical-align:middle;">
					{$avEquipmentNotes[$key]}
				</td>
			</tr>			
		{/foreach}
		</tbody>
	</table>
{/if}

</div>

{if $room->tutorial}
<div id="tutorial" class="tab-pane fade {if $mode == 'tutorial'}in active{/if}" style="margin-top:3em;">
	{include file="partial:_view.tutorial.html.tpl"}	
</div>
{/if}

{if $room->hasSoftwareOrHardware()}
<div id="software" class="tab-pane fade {if $mode == 'software'}in active{/if}" style="margin-top:3em;">
 	<h3>Software/Equipment in this room</h3>
	<!-- <div class=""> -->

		<div class="row">
	{if $trackUrl && ($pEdit || $pSupport)}
	<div class="col-sm-12">
		<a href="{$trackUrl}" target="_blank" class="">View all computers and hardware in this room
			<i class="glyphicon glyphicon-new-window"></i>
		</a>
	</div>
	<br><br>
	{/if}
	{foreach $room->configurations as $config}
		{if !$config->isBundle && !$config->deleted}
		<div class="col-sm-12">
			<div class="panel panel-default">
				<div class="panel-heading"><strong>Configuration: {$config->model}</strong></div>
				<div class="panel-body">
					<p><strong>Hardware:</strong> {$config->deviceQuantity} {$config->deviceType}</p>
				{if $pEdit}
					<dl class="dl-horizontal">
						{if $config->location}<dt>Location</dt><dd>{$config->location}</dd>{/if}
						{if $config->managementType}<dt>Management Type</dt><dd>{$config->managementType}</dd>{/if}
						{if $config->imageStatus}<dt>Image Status</dt><dd>{$config->imageStatus}</dd>{/if}
						{if $config->vintages}<dt>Vintages</dt><dd>{$config->vintages}</dd>{/if}
						{if $config->adBound}<dt>AD Bound</dt><dd>{$config->adBound}</dd>{/if}
						{if $config->modifiedDate}<dt>Last Modified</dt><dd>{$config->modifiedDate->format('m/d/Y')}</dd>{/if}
					</dl>
				{/if}
				{if $config->softwareLicenses->count() > 0}
					<p><strong>Software:</strong></p>
					<table class="table table-condensed table-bordered">
						<thead>
							<tr>
								<th>Developer</th>
								<th>Title</th>
								<th>Version</th>
							</tr>
						</thead>
					<tbody>
					{foreach $config->softwareLicenses as $license}
						<tr>
							<td>{$license->version->title->developer->name}</td>
							<td><a href="software/{$license->version->title->id}">{$license->version->title->name}</a></td>
							<td>{$license->version->number}</td>
						</tr>
					{/foreach}
					</tbody>
					</table>
				</div>
				{/if}
			</div>
		</div>
		{/if}
	{/foreach}
		</div>

		<div class="row">
	{foreach $room->configurations as $config}
		{if !$config->deleted}
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
{/if}

{if $notes && $pEdit}
<div id="notes" class="tab-pane fade {if $mode == 'notes'}in active{/if}" style="margin-top:3em;">
  	<h3>Notes</h3>

	{if count($room->internalNotes)}
	<div class="panel panel-default">
		<div class="panel-heading"><h4>Internal Notes</h4></div>
		<div class="panel-body">
			<ul class="list-unstyled">
			{foreach $room->internalNotes as $note}
				<li>
					<strong style="margin-right:1em;">{$note->addedBy->fullName} ({$note->createdDate->format('Y-m-d h:i a')}):</strong> {$note->message}
				</li>
			{/foreach}
			</ul>		
		</div>
	</div>
	{/if}

	<div class="panel panel-default">
		<div class="panel-heading"><h4>System Log Notes</h4></div>
		<div class="panel-body">
			{include file="partial:_view.notes.html.tpl"}			
		</div>
	</div>

</div>
{/if}

</div>
</div>

</div>
