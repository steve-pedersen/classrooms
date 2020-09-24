{assign var="config" value=$selectedConfiguration}


<h3>Configuration 
	<small>
	{if $config->id && $location->configurations && $location->configurations->count() > 1}
		({$config->model} {$model->location})
	{else}
		(default)
	{/if}
	</small>
</h3>
<div class="form-horizontal">

	<div class="form-group">
		<label for="model" class="col-sm-2 control-label">Model</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" name="config[model]" value="{$config->model}" placeholder="">
		</div>
	</div>

	<div class="form-group">
		<label for="location" class="col-sm-2 control-label">Location</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" name="config[location]" value="{if $config->location}{$config->location}{else}{$location->building->name} {$location->number}{/if}" placeholder="">
		</div>
	</div>

	<div class="form-group">
		<label for="managementType" class="col-sm-2 control-label">Management Type</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" name="config[managementType]" value="{$config->managementType}" placeholder="">
		</div>
	</div>

	<div class="form-group">
		<label for="imageStatus" class="col-sm-2 control-label">Image Status</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" name="config[imageStatus]" value="{$config->imageStatus}" placeholder="">
		</div>
	</div>

	<div class="form-group">
		<label for="vintages" class="col-sm-2 control-label">Vintages</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" name="config[vintages]" value="{$config->vintages}" placeholder="">
		</div>
	</div>

	<div class="form-group">
		<label for="uniprintQueue" class="col-sm-2 control-label">Uniprint Queue</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" name="config[uniprintQueue]" value="{$config->uniprintQueue}" placeholder="">
		</div>
	</div>

	<div class="form-group">
		<label for="releaseStationIp" class="col-sm-2 control-label">Release Station IP</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" name="config[releaseStationIp]" value="{$config->releaseStationIp}" placeholder="">
		</div>
	</div>

	<div class="form-group">
		<label for="adBound" class="col-sm-2 control-label">Ad Bound</label>
		<div class="col-sm-2">
			<input type="checkbox" class="checkbox" name="config[adBound]" value="{$config->adBound}" {if $config->adBound}checked{/if}>
		</div>
		

	</div>


</div>