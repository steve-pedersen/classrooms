<h1>Configuration Bundle <small>{$config->model}</small></h1>

<div class="row pull-right" style="">
	<div class="col-sm-12">
		<a href="configurations/{$config->id}/edit" class="btn btn-primary">Edit</a>
	</div>
</div>
<br>
<div class="view-bundle-details">

	<dl class="dl-horizontal">
		<dt>Model</dt>
		<dd>{$config->model}</dd>
		<dt>Description</dt>
		<dd>{$config->description}</dd>
		<br>
		<dt>Software</dt>
		<dd>
			<table class="table table-bordered table-condensed table-striped">
				<thead>
					<tr>
						<th>Title</th>
						<th>Version</th>
						<th>License #</th>
						<th>Expires</th>
						<th>Seats</th>
						<th>Description</th>
					</tr>
				</thead>
				<tbody>
			{foreach $config->softwareLicenses as $license}
				<tr>
					<th>{$license->version->title->name}</th>
					<td>{$license->version->number}</td>
					<td>{$license->number}</td>
					<td>{$license->expirationDate->format('m/d/Y')}</td>
					<td>{$license->seats}</td>
					<td>{$license->description|truncate:100}</td>
				</tr>
			{/foreach}
				</tbody>
			</table>
		</dd>
	</dl>

</div>