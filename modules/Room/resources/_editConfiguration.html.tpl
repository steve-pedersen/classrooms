<h1>Edit Configuration <small>{$config->model} for {$config->room->building->name} {$config->room->number}</small></h1>

<form action="" method="post">
<div class="form-horizontal">
	<div class="form-group">
		<label for="model" class="col-sm-2 control-label">Model</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" name="model" value="{$config->model}" placeholder="">
		</div>
	</div>

	<div class="form-group">
		<label for="location" class="col-sm-2 control-label">Location</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" name="location" value="{$config->location}" placeholder="">
		</div>
	</div>

	<div class="form-group">
		<label for="managementType" class="col-sm-2 control-label">Management Type</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" name="managementType" value="{$config->managementType}" placeholder="">
		</div>
	</div>

	<div class="form-group">
		<label for="imageStatus" class="col-sm-2 control-label">Image Status</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" name="imageStatus" value="{$config->imageStatus}" placeholder="">
		</div>
	</div>

	<div class="form-group">
		<label for="vintages" class="col-sm-2 control-label">Vintages</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" name="vintages" value="{$config->vintages}" placeholder="">
		</div>
	</div>

	<div class="form-group">
		<label for="uniprintQueue" class="col-sm-2 control-label">Uniprint Queue</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" name="uniprintQueue" value="{$config->uniprintQueue}" placeholder="">
		</div>
	</div>

	<div class="form-group">
		<label for="releaseStationIp" class="col-sm-2 control-label">Release Station IP</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" name="releaseStationIp" value="{$config->releaseStationIp}" placeholder="">
		</div>
	</div>

	<div class="form-group">
		<label for="adBound" class="col-sm-2 control-label">Ad Bound</label>
		<div class="col-sm-2">
			<input type="checkbox" class="checkbox" name="adBound" value="{$config->adBound}" {if $config->adBound}checked{/if}>
		</div>
	</div>

	<h5>TODO: Software for this configuration {if $selectedConfiguration->id}({$selectedConfiguration->model}){/if}</h5>
<!-- 	<div class="form-group">
		<label for="config" class="col-sm-2 control-label">Available Titles</label>
		<div class="col-sm-10">
		{foreach $softwareLicenses as $licenses}
			{foreach $licenses as $license}
				{assign var=checked value=false}
				{foreach $selectedConfiguration->softwareLicenses as $l}
					{if $l->id == $license->id}{assign var=checked value=true}{/if}
				{/foreach}
			<div class="checkbox">
				<label>
					<input type="checkbox" name="licenses[{$license->id}]" {if $checked}checked{/if}>
					{$license->version->title->name} | 
					v{$license->version->number} | 
					License {$license->number} expiring {$license->expirationDate->format('m/d/Y')}
					{if $license->description} | {$license->description|truncate:100}{/if}
				</label>
			</div>
			{/foreach}
		{/foreach}
		</div>
	</div> -->
</div>
<div class="controls">
	{generate_form_post_key}
	<input type="hidden" name="configurationId" value="{$configuration->id}">
	<button type="submit" name="command[save]" class="btn btn-primary">Save Configuration</button>
	<button type="submit" name="command[delete]" class="btn btn-danger">Delete</button>
	<a href="software/{$title->id}" class="btn btn-link pull-right">Cancel</a>
</div>
</form>