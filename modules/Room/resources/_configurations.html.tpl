{assign var="config" value=$selectedConfiguration}


<h3>Custom Configurations 
	<small>
	{if $config->id && $location->configurations && $location->configurations->count() > 1}
		({$config->model} {$model->location})
	{else}
		(default)
	{/if}
	</small>
</h3>

{if $config->id}
<div class="form-horizontal">
	<div class="form-group">
		<label for="model" class="col-sm-2 control-label">Model</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" name="config[existing][model]" value="{$config->model}" placeholder="">
		</div>
	</div>

	<div class="form-group">
		<label for="location" class="col-sm-2 control-label">Location</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" name="config[existing][location]" value="{$config->location}" placeholder="">
		</div>
	</div>

	<div class="form-group">
		<label for="managementType" class="col-sm-2 control-label">Management Type</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" name="config[existing][managementType]" value="{$config->managementType}" placeholder="">
		</div>
	</div>

	<div class="form-group">
		<label for="imageStatus" class="col-sm-2 control-label">Image Status</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" name="config[existing][imageStatus]" value="{$config->imageStatus}" placeholder="">
		</div>
	</div>

	<div class="form-group">
		<label for="vintages" class="col-sm-2 control-label">Vintages</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" name="config[existing][vintages]" value="{$config->vintages}" placeholder="">
		</div>
	</div>

	<div class="form-group">
		<label for="uniprintQueue" class="col-sm-2 control-label">Uniprint Queue</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" name="config[existing][uniprintQueue]" value="{$config->uniprintQueue}" placeholder="">
		</div>
	</div>

	<div class="form-group">
		<label for="releaseStationIp" class="col-sm-2 control-label">Release Station IP</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" name="config[existing][releaseStationIp]" value="{$config->releaseStationIp}" placeholder="">
		</div>
	</div>

	<div class="form-group">
		<label for="adBound" class="col-sm-2 control-label">Ad Bound</label>
		<div class="col-sm-2">
			<input type="checkbox" class="checkbox" name="config[existing][adBound]" value="{$config->adBound}" {if $config->adBound}checked{/if}>
		</div>
	</div>

	<h5>Software for this configuration {if $selectedConfiguration->id}({$selectedConfiguration->model}){/if}</h5>
	<div class="form-group">
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
					<input type="checkbox" name="config[existing][licenses][{$license->id}]" {if $checked}checked{/if}>
					{$license->version->title->name} | 
					v{$license->version->number} | 
					License {$license->number} expiring {$license->expirationDate->format('m/d/Y')}
					{if $license->description} | {$license->description|truncate:100}{/if}
				</label>
			</div>
			{/foreach}
		{/foreach}
		</div>
	</div>
</div>
{/if}


<div id="accordion">

	{if $location->configurations->count() > 0}
	<div class="container-fluid">
		<div class="row">
			<a role="button" class="btn btn-default pull-right" data-toggle="collapse" data-parent="#accordion" href="#newConfig" aria-expanded="true" aria-controls="newConfig">
				+ Add New Configuration
			</a>
		</div>	
	</div>
	{/if}

	<div class="panel-collapse collapse {if $location->configurations->count() == 0}in{/if}" role="tabpanel" id="newConfig">
		<div class="form-horizontal">
			{if $location->configurations->count() > 0}
			<h4 class="">Add new configuration</h4>
			{/if}
			<div class="form-group">
				<label for="model" class="col-sm-2 control-label">Model</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" name="config[new][model]" value="" placeholder="">
				</div>
			</div>

			<div class="form-group">
				<label for="location" class="col-sm-2 control-label">Location</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" name="config[new][location]" value="" placeholder="">
				</div>
			</div>

			<div class="form-group">
				<label for="managementType" class="col-sm-2 control-label">Management Type</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" name="config[new][managementType]" value="" placeholder="">
				</div>
			</div>

			<div class="form-group">
				<label for="imageStatus" class="col-sm-2 control-label">Image Status</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" name="config[new][imageStatus]" value="" placeholder="">
				</div>
			</div>

			<div class="form-group">
				<label for="vintages" class="col-sm-2 control-label">Vintages</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" name="config[new][vintages]" value="" placeholder="">
				</div>
			</div>

			<div class="form-group">
				<label for="uniprintQueue" class="col-sm-2 control-label">Uniprint Queue</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" name="config[new][uniprintQueue]" value="" placeholder="">
				</div>
			</div>

			<div class="form-group">
				<label for="releaseStationIp" class="col-sm-2 control-label">Release Station IP</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" name="config[new][releaseStationIp]" value="" placeholder="">
				</div>
			</div>

			<div class="form-group">
				<label for="adBound" class="col-sm-2 control-label">Ad Bound</label>
				<div class="col-sm-2">
					<input type="checkbox" class="checkbox" name="config[new][adBound]" value="">
				</div>
			</div>

			<h5>Software for this configuration (new)</h5>
			<div class="form-group">
				<label for="config" class="col-sm-2 control-label">Available Titles</label>
				<div class="col-sm-10">
				{foreach $softwareLicenses as $licenses}
					{foreach $licenses as $license}
						{assign var=checked value=false}
						{foreach $selectedConfiguration->licenses as $l}
							{if $l->id == $license->id}{/if}
						{/foreach}
					<div class="checkbox">
						<label>
							<input type="checkbox" name="config[new][licenses][{$license->id}]">
							{$license->version->title->name} | 
							v{$license->version->number} | 
							License {$license->number} expiring {$license->expirationDate->format('m/d/Y')}
							{if $license->description} | {$license->description|truncate:100}{/if}
						</label>
					</div>
					{/foreach}
				{/foreach}
				</div>
			</div>
		</div>
	</div>

</div>