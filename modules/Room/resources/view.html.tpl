<h1>
	Room <small>{$room->building->code} {$room->number}</small>
</h1>

{if $room->type->isLab}
<p class="alert alert-info">
	<img style="max-width:30px;display:inline;" src="assets/images/laboratory.png" class="img-responsive" alt="Icon of microscope"> <strong style="display:inline;">This is a Lab type room.</strong> 
	{if $room->hasSoftwareOrHardware()}
		<span style="display:inline;">Select the <em>Software/Equipment</em> tab below to view additional information about any computers, software, and/or lab equipment in this room.</span>
	{/if}
</p>
{/if}

{if $pEdit || $pSupport}
<div class="row pull-right" style="margin-bottom:10px;">
	<div class="col-sm-12">
		{if $pSupport && $roomSchedules}<a  href="schedules?room={$room->id}" class="btn btn-primary">Schedules for this room</a>{/if}
		{if $pEdit}<a href="rooms/{$room->id}/edit" class="btn btn-info">Edit Room</a>{/if}
	</div>
</div>
{/if}
<br>

<!-- tab navs -->
<div class="row">
	<div class="col-xs-12">
		<ul class="nav nav-pills nav-justified room-pills" style="margin-top:1em;margin-bottom:2em;">
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
				<a data-toggle="pill" href="{$room->roomUrl}?mode=software#software">
					<strong>Software/Equipment</strong>
				</a>
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
	</div>
</div>

<div class="tab-content">

<div id="basicInfo" class="tab-pane fade {if $mode == 'basic'}in active{/if}" style="margin-top:1em;">
	<h3 style="font-weight:600;">Basic Information</h3>
	<div class="view-room-details">
		<dl class="dl-horizontal">
				<dt>Room Type</dt>
				<dd>{if $room->type}
						{if $room->type->isLab}<u>Lab</u> - {/if}
						{$room->type->name}
					{else}
						Unknown
					{/if}
				</dd>
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
			
			{if $trackUrl && ($pEdit || $pSupport)}
				<dt>Computer/Hardware info from <em style="font-weight:500;text-decoration:underline;">https://track.sfsu.edu</em></dt>
				<dd>
					<a href="{$trackUrl}" target="_blank" class="">View all computers and hardware in this room
						<i class="glyphicon glyphicon-new-window"></i>
					</a>
				</dd>
			{/if}
		</dl>

{if count($room->images) > 0}
	<div class="image-gallery" style="margin: 30px 0 40px 0;">
		<h3 style="font-weight:600;">Images</h3>
		<div class="row">
			{foreach $room->images as $image}
				<div class="col-xs-3">
					<a href="#" class="view-image-modal" data-toggle="modal" data-target="#viewImageModal" data-image-src="{$image->getRoomImageSrc($room->id)}" data-filename="{$image->remoteName}" data-id="{$image->id}">
						<img src="{$image->getRoomImageSrc($room->id)}" class="img-responsive">
					</a>
				</div>
			{/foreach}
		</div>
	</div>
{/if}


{assign var="avEquipment" value=unserialize($room->avEquipment)}
{assign var=hasHDMI value=isset($avEquipment.hdmi)}
{assign var=hasVGA value=isset($avEquipment.vga)}

{if $room->configured && ($hasHDMI || $hasVGA)}

<div style="margin: 40px 0;">
	<h3 style="font-weight:600;">Computer Ports & Adapters</h3>
	<p class="">
		In this room, you can connect your computer to {if $hasHDMI && $hasVGA}both{/if} {if $hasHDMI}<strong>HDMI</strong>{if $hasVGA} and {/if}{/if}{if $hasVGA}<strong>VGA</strong>{/if} ports.
		Make sure you have the right adapter(s) before coming to class.
		<em>Please note that the adapters in this table are just examples. Adapters vary by brand, color, shape, etc.</em>
	</p>
	<table class="table table-condensed table-responsive table-bordered">
		<!-- <caption style="font-size:1.7rem;text-align:center;">Common computer ports and adapters needed</caption> -->
		<thead>
			<tr>
				<th style="vertical-align:middle;font-size:1.6rem;">If your computer has...</th>
				{if $hasHDMI}<th style="font-size:1.6rem;">Adapter for HDMI</th>{/if}
				{if $hasVGA}<th style="font-size:1.6rem;">Adapter for VGA</th>{/if}
			</tr>
		</thead>
		<tbody>
			<tr>
				<td style="vertical-align:middle;padding:6px;">
					<img src="assets/images/ports-usbc.jpg" class="img-responsive" style="max-width:50px;padding:3px;display:inline;">
					<span style="margin-left:15px;font-weight:bold">USB-C</span>
					<!-- <img src="assets/images/cables-usbc.jpg" class="img-responsive" style="max-width:80px;padding:3px;display:inline;"> -->
				</td>
			{if $hasHDMI}
				<td style="vertical-align:middle;">
					<img src="assets/images/adapters-usbc-hdmi.jpg" class="img-responsive" style="max-height:100px;display:inline;">
					<span style="margin-left:15px;font-weight:bold">USB-C to HDMI</span>
				</td>
			{/if}
			{if $hasVGA}
				<td style="vertical-align:middle;">
					<img src="assets/images/adapters-usbc-vga.jpg" class="img-responsive" style="max-height:100px;display:inline;">
					<span style="margin-left:35px;font-weight:bold">USB-C to VGA</span>
				</td>
			{/if}
			</tr>
			<tr>
				<td style="vertical-align:middle;padding:6px;">
					<img src="assets/images/ports-minidp.jpg" class="img-responsive" style="max-width:50px;padding:3px;display:inline;">
					<span style="margin-left:15px;font-weight:bold">Mini DisplayPort</span>
					<!-- <img src="assets/images/cables-minidp.jpg" class="img-responsive" style="max-width:80px;padding:3px;display:inline;"> -->
				</td>
			{if $hasHDMI}
				<td style="vertical-align:middle;">
					<img src="assets/images/adapters-minidp-hdmi.jpg" class="img-responsive" style="max-height:100px;display:inline;">
					<span style="margin-left:15px;font-weight:bold">MiniDP to HDMI</span>
				</td>
			{/if}
			{if $hasVGA}
				<td style="vertical-align:middle;">
					<img src="assets/images/adapters-minidp-vga.jpg" class="img-responsive" style="max-height:100px;display:inline;">
					<span style="margin-left:15px;font-weight:bold">MiniDP to VGA</span>
				</td>
			{/if}
			</tr>
			<tr>
				<td style="vertical-align:middle;padding:6px;">
					<img src="assets/images/ports-hdmi.jpg" class="img-responsive" style="max-width:50px;padding:3px;display:inline;">
					<span style="margin-left:15px;font-weight:bold">HDMI</span>
					<!-- <img src="assets/images/cables-hdmi.jpg" class="img-responsive" style="max-width:80px;padding:3px;display:inline;"> -->
				</td>
			{if $hasHDMI}
				<td style="vertical-align:middle;font-weight:bold;text-align:center;background-color:#fafafa;height:100px;color:#999;">&mdash; None &mdash;</td>
			{/if}
			{if $hasVGA}
				<td style="vertical-align:middle;">
					<img src="assets/images/adapters-hdmi-vga.jpg" class="img-responsive" style="max-height:100px;display:inline;">
					<span style="margin-left:15px;font-weight:bold">HDMI to VGA</span>
				</td>
			{/if}
			</tr>
			<tr>
				<td style="vertical-align:middle;padding:6px;">
					<img src="assets/images/ports-vga.jpg" class="img-responsive" style="max-width:50px;padding:3px;display:inline;">
					<span style="margin-left:15px;font-weight:bold">VGA</span>
					<!-- <img src="assets/images/cables-vga.jpg" class="img-responsive" style="max-width:80px;padding:3px;display:inline;"> -->
				</td>
			{if $hasHDMI}
				<td style="vertical-align:middle;">
					<img src="assets/images/adapters-vga-hdmi.jpg" class="img-responsive" style="max-height:100px;display:inline;">
					<span style="margin-left:15px;font-weight:bold">VGA to HDMI</span>
				</td>
			{/if}
			{if $hasVGA}
				<td style="vertical-align:middle;font-weight:bold;text-align:center;background-color:#fafafa;height:100px;color:#999;">&mdash; None &mdash;</td>
			{/if}
			</tr>
		</tbody>
	</table>
</div>
{/if}

</div> <!-- end view-room-details -->

{if $room->configured}
	<div style="margin: 40px 0;">
		<h3 style="font-weight:600;">Audio & Visual Equipment</h3>
		<table class="table table-bordered table-condensed table-responsive">
			<thead>
				<tr>
					<th style="font-size:1.6rem;">A/V Equipment</th>
					<th style="font-size:1.6rem;">In Room?</th>
					<th style="font-size:1.6rem;width:25%;">Notes</th>
				</tr>
			</thead>
			<tbody>
				
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
						{if $avEquipment[$key]}
							{$avEquipmentNotes[$key]}
						{/if}
					</td>
				</tr>			
			{/foreach}
			</tbody>
		{if $room->type->isLab && $room->hasSoftwareOrHardware()}
			<tfoot>
				<tr>
					<td colspan="3">
						Select the <em>Software/Equipment</em> tab at the top of the page to view additional information about any computers, software, and/or lab equipment in this room.
					</td>
				</tr>
			</tfoot>
		{/if}
		</table>
	</div>
{/if}
</div> <!-- end basic info -->

	{if $room->tutorial}
	<div id="tutorial" class="tab-pane fade {if $mode == 'tutorial'}in active{/if}" style="margin-top:3em;">
		{include file="partial:_view.tutorial.html.tpl"}	
	</div>
	{/if}

	{if $room->hasSoftwareOrHardware()}
	<div id="software" class="tab-pane fade {if $mode == 'software'}in active{/if}" style="margin-top:3em;">
	 	<h3>Software/Equipment in this room</h3>

		<div class="row">
		{if $trackUrl && ($pEdit || $pSupport)}
			<div class="col-sm-12">
				<a href="{$trackUrl}" target="_blank" class="">View all computers and hardware in this room
					<i class="glyphicon glyphicon-new-window"></i>
				</a>
			</div>
			<br><br>
		{/if}
		</div>
		
		{foreach $room->configurations as $config}
			{if !$config->isBundle && !$config->deleted}
			<div class="row">
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
					{/if}
					</div>
				</div>
			</div>
			</div>
			{/if}
		{/foreach}
		
		
		{foreach $room->configurations as $config}
			{if !$config->deleted}
				{if $config->isBundle}
				<div class="row">
				<div class="col-sm-12">
					<div class="panel panel-default">
						<div class="panel-heading"><strong>Config Bundle: {$config->model}</strong></div>
						<div class="panel-body">
						{if $config->deviceQuantity || $config->deviceType}
						<p><strong>Hardware:</strong> {$config->deviceQuantity} {$config->deviceType}</p>
						{/if}
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
					{/if}	
						</div>
					</div>
				</div>
				</div>
				{/if}
			{/if}
		{/foreach}
		
		{if $pRequest}
			<div class="row" style="">
				<div class="col-sm-12">
					<a href="#" class="btn btn-primary">Request Software</a>
				</div>
			</div>
		{/if}

	</div> <!-- end software tab -->
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
	
	</div> <!-- end notes panel -->
	{/if}


</div>

</div> <!-- end tab-content -->


<div id="viewImageModal" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"></h4>
      </div>
      <div class="modal-body">
        <img src="" class="img-responsive">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>