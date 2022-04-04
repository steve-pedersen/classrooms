<div class="container">
	<div class="row">
		<div class="col-xs-12">
		{if $title->id}
			<h1>Edit Software <small><a href="software/{$title->id}">{$title->developer->name}: {$title->name}</a></small></h1>
		{else}
			<h1>New Software</h1>
		{/if}
		</div>
	</div>
</div>

<form action="" method="post">
	<div class="container"> 

		<div class="row">
			<div class="col-xs-12 edit-details">
				<h2>Software Title Details</h2>

				<div class="form-horizontal">
					<div class="form-group">
						<label for="category[existing]" class="col-sm-2 control-label">Category</label>
						<div class="col-sm-4">
							<select name="category[existing]" id="category" class="form-control">
								<option value="">Choose Category</option>
							{foreach $categories as $category}
								<option value="{$category->id}"{if $title->category_id == $category->id} selected{/if}>{$category->name}</option>
							{/foreach}
							</select>
						</div>
						<label for="category[new]" class="col-sm-1 control-label">Or</label>
						<div class="col-sm-5 has-success">
							<input type="text" class="form-control" name="category[new]" value="" placeholder="Add new category...">
						</div>
					</div>

					<div class="form-group">
						<label for="developer[existing]" class="col-sm-2 control-label">Developer</label>
						<div class="col-sm-4">
							<select name="developer[existing]" id="developer" class="form-control">
								<option value="">Choose Developer</option>
							{foreach $developers as $developer}
								<option value="{$developer->id}"{if $title->developer_id == $developer->id} selected{/if}>{$developer->name}</option>
							{/foreach}
							</select>
						</div>
						<label for="developer[new]" class="col-sm-1 control-label">Or</label>
						<div class="col-sm-5 has-success">
							<input type="text" class="form-control" name="developer[new]" value="" placeholder="Add new developer...">
						</div>
					</div>

					<div class="form-group">
						<label for="title[name]" class="col-sm-2 control-label">Software Title</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="title[name]" value="{$title->name}" placeholder="">
						</div>
					</div>

					<div class="form-group">
						<label for="title[description]" class="col-sm-2 control-label">Description</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="title[description]" value="{$title->description}" placeholder="Brief description of software">
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-2 control-label">Compatible Operating Systems</label>
						<div class="col-sm-10">
						{foreach $operatingSystems as $system}
							<div class="col-sm-12">
							{assign var=checked value=false}
							{if is_array($title->compatibleSystems) && in_array($system, $title->compatibleSystems)}
								{assign var=checked value=true}
							{/if}
							<label for="system-{$system}">
								<input type="checkbox" id="system-{$system}" name="title[compatibleSystems][{$system}]" {if $checked}checked{/if}> 
								{$system}
							</label>
							</div>
						{/foreach}
						</div>
					</div>
				</div>

				<hr>

				<div class="form-horizontal">
					<h3>Version</h3>
					<div class="form-group">
					{if $title->versions && $title->versions->count() > 0}
						<label for="version[existing]" class="col-sm-2 control-label">Version Number</label>
						<div class="col-sm-4">
							<select name="version[existing]" id="version" class="form-control">
								<option value="">Choose Version</option>
							{foreach $title->versions as $version}
							{if !$version->deleted}
								<option value="{$version->id}"{if $selectedVersion->id == $version->id} selected{/if}>{$version->number}</option>
							{/if}
							{/foreach}
							</select>
						</div>

						<label for="version[new]" class="col-sm-1 control-label">Or</label>
						<div class="col-sm-5 has-success">
							<input type="text" class="form-control" name="version[new]" value="" placeholder="Add new version number...">
						</div>
					{else}
						<label for="version[new]" class="col-sm-2 control-label">Version Number</label>
						<div class="col-sm-4 has-success">
							<input type="text" class="form-control" name="version[new]" value="" placeholder="Add new version number...">
						</div>						
					{/if}
					</div>
				</div>

				<hr>

				<div class="form-horizontal">
					<h3>License</h3>
					<p>Note, licenses are associated with version numbers.</p>

					{if $selectedVersion->licenses && $selectedVersion->licenses->count() > 0}
					<div class="form-group existing-items">
						<label for="license[existing]" class="col-sm-2 control-label">License</label>
						<div class="col-sm-10">
							<div class="input-group">
							<select name="license[existing]" id="existingLicenses" class="form-control">
								<option value="">Choose License</option>
						{foreach $title->versions as $version}
						{if !$version->deleted}
							{foreach $version->licenses as $license}
							{if !$license->deleted}
								<option value="{$license->id}"{if $selectedLicense->id == $license->id} selected{/if}>
									License {$license->number} | 
									Version {$version->number}
									{if $license->seats} | {$license->seats} seats{/if}
									{if $license->expirationDate} | Expires {$license->expirationDate->format('m/d/Y')}{/if}
									{if $license->description} | {$license->description|truncate:50}{/if}
								</option>
							{/if}
							{/foreach}
						{/if}
						{/foreach}
							</select>
								<span class="input-group-btn">
									<a href="software/{$title->id}/licenses/{$selectedLicense->id}/edit" data-baseurl="software/{$title->id}/licenses/" class="btn btn-info edit-license" type="button">Edit License</a>
								</span>
							</div>
						</div>
<!-- 						<div class="col-sm-1 edit">
							<a href="software/{$title->id}/licenses/{$selectedLicense->id}/edit" data-baseurl="software/{$title->id}/licenses/" class="btn btn-info pull-right">Edit License</a>
						</div> -->
					</div>	
					{/if}	

					<h4 class="">Add new license <small class="text-info">(for selected or new version)</small></h4>
					<p class=""><em>To update license information for a specific version, click Edit License above.</em></p>
					<div class="form-group">
						<label for="license[new][number]" class="col-sm-2 control-label">License Number</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="license[new][number]" value="" placeholder="A123...">
						</div>
					</div>	
					<div class="form-group">
						<label for="license[new][expirationDate]" class="col-sm-2 control-label">Expiration Date</label>
						<div class="col-sm-10">
							<input type="text" class="form-control datepicker" name="license[new][expirationDate]" value="" placeholder="">
						</div>
					</div>	
					<div class="form-group">
						<label for="license[new][description]" class="col-sm-2 control-label">Description</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="license[new][description]" value="" placeholder="">
						</div>
					</div>	
					<div class="form-group">
						<label for="license[new][seats]" class="col-sm-2 control-label">Seats</label>
						<div class="col-sm-4">
							<input type="text" class="form-control" name="license[new][seats]" value="" placeholder="">
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="controls">
			{generate_form_post_key}
			<input type="hidden" name="titleId" value="{$title->id}">
			<button type="submit" name="command[save]" class="btn btn-primary">Save Software</button>
			<a href="software/{$title->id}" class="btn btn-danger pull-right">Cancel</a>
		</div>
	</div>
</form>

{if $notes}
	<hr>
	<h2>Notes</h2>
	{include file="partial:_view.notes.html.tpl"}
{/if}