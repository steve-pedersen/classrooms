<h1>Edit License <small>{$license->number} for {$title->developer->name} {$title->name} version {$license->version->number}</small></h1>

<form action="" method="post">
	<div class="container"> 

		<div class="row">
			<div class="col-xs-12 edit-details">
				<h2>License Title Details</h2>

				<div class="form-horizontal">
					<div class="form-group">
						<label for="number" class="col-sm-2 control-label">License Number</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="number" value="{$license->number}">
						</div>
					</div>	
					<div class="form-group">
						<label for="expirationDate" class="col-sm-2 control-label">Expiration Date</label>
						<div class="col-sm-10">
							<input type="text" class="form-control datepicker" name="expirationDate" value="{$license->expirationDate->format('m/d/Y')}">
						</div>
					</div>	
					<div class="form-group">
						<label for="description" class="col-sm-2 control-label">Description</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="description" value="{$license->description}">
						</div>
					</div>	
					<div class="form-group">
						<label for="seats" class="col-sm-2 control-label">Seats</label>
						<div class="col-sm-4">
							<input type="text" class="form-control" name="seats" value="{$license->seats}">
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="controls">
			{generate_form_post_key}
			<input type="hidden" name="licenseId" value="{$license->id}">
			<button type="submit" name="command[save]" class="btn btn-primary">Save License</button>
			<button type="submit" name="command[delete]" class="btn btn-danger">Delete</button>
			<a href="software/{$title->id}" class="btn btn-link pull-right">Cancel</a>
		</div>
	</div>
</form>