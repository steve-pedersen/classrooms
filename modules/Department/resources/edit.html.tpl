<div class="container">
	<div class="row">
		<div class="col-xs-12">
		{if $department->id}
			<h1>Edit Department <small>{$department->name}</small></h1>
		{else}
			<h1>New Department</h1>
		{/if}
		</div>
	</div>
</div>

<form action="" method="post">
	<div class="container"> 

		<div class="row">
			<div class="col-xs-12 edit-details">
				<h2>Department Details</h2>

				<div class="form-horizontal">

					<div class="form-group">
						<label for="name" class="col-sm-2 control-label">Department Name</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="name" value="{$department->name}" placeholder="">
						</div>
					</div>

					<div class="form-group">
						<label for="code" class="col-sm-2 control-label">Code</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="code" value="{$department->code}" placeholder="">
						</div>
					</div>
				
				</div>

			</div>
		</div>

		<div class="row">
			<div class="col-xs-12 edit-details">
				<h3>New User</h3>

				<div class="form-horizontal">

					<div class="form-group">
						<label for="sfStateId" class="col-sm-2 control-label">SF State ID</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="sfStateId" placeholder="" required>
						</div>
					</div>

					<div class="form-group">
						<label for="emailAddress" class="col-sm-2 control-label">Email Address</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="emailAddress" placeholder="">
						</div>
					</div>

					<div class="form-group">
						<label for="firstName" class="col-sm-2 control-label">First Name</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="firstName" placeholder="">
						</div>
					</div>

					<div class="form-group">
						<label for="lastName" class="col-sm-2 control-label">Last Name</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="lastName" placeholder="">
						</div>
					</div>

					<div class="form-group">
						<label for="position" class="col-sm-2 control-label">Position</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="position" placeholder="E.g. Department Chair">
						</div>
					</div>

				</div>

			</div>
		</div>

		<div class="controls">
			{generate_form_post_key}
			<input type="hidden" name="departmentId" value="{$department->id}">
			<button type="submit" name="command[save]" class="btn btn-primary">Save Department</button>
			<button type="submit" name="command[delete]" class="btn btn-danger">Delete Department</button>
			<a href="rooms" class="btn btn-link pull-right">Cancel</a>
		</div>
	</div>
</form>