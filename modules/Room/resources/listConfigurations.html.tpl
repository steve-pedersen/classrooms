
<h1>Configuration Bundles</h1>

{if $pEdit}
<div class="container-fluid">
<div class="row pull-right" style="margin-bottom: 1em;">
	<div class="col-sm-12">
		<a href="configurations/new/edit" class="btn btn-primary">Add New Configuration Bundle</a>
	</div>
</div>
</div>
{/if}

<table class="table table-bordered table-striped table-condensed">
	<thead>
		<tr>
			<th scope="">Model</th>
			<th scope="">Created</th>
			<th scope="">Modified</th>
			<th scope=""># of rooms used in</th>
		</tr>
	</thead>
	<tbody>
	{foreach $configurations as $configuration}
		<tr>
			<th scope="col" class="">
				<a href="configurations/{$configuration->id}/edit">{$configuration->model}</a>
			</th>
			<td>{$configuration->createdDate->format('Y-m-d')}</td>
			<td>{$configuration->modifiedDate->format('Y-m-d')}</td>
			<td>{$configuration->rooms->count()}</td>
		</tr>
	{foreachelse}
		<tr>
			<td colspan="4">There are no configuration bundles.</td>
		</tr>
	{/foreach}
	</tbody>
</table>