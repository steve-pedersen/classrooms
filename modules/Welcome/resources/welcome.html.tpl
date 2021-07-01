<h1>Welcome to the Classrooms Database Application</h1>
<br>
<div id="welcome-text">
{if $welcomeText}
{$welcomeText}
{else}
<!-- <p>Classrooms Database info here.</p> -->
{/if}
</div>

{if !$userContext->account}
<div class="welcome-module">
    <a href="{$app->baseUrl('login?returnTo=/')}" class="btn btn-primary">Log In</a>
</div>
<br><br>
{/if}

<div class="welcome-module">
	<div class="row">
		<div class="col-md-3 col-sm-6 col-xs-12">
			<a href="{$app->baseUrl('/rooms')}" class="">
			<div class="panel panel-default">
				<div class="panel-heading text-center">
					<h2 style="font-size:2rem">Rooms</h2>
				</div>
				<div class="panel-body">
					<img style="max-width:100px;margin-left:auto;margin-right:auto;" src="assets/images/rooms.png" class="img-responsive">
				</div>
			</div>
			</a>
		</div>
		{if $userContext->account}
		<div class="col-md-3 col-sm-6 col-xs-12">
			<a href="{$app->baseUrl('/schedules')}" class="">
			<div class="panel panel-default">
				<div class="panel-heading text-center">
					<h2 style="font-size:2rem">Room Schedules</h2>
				</div>
				<div class="panel-body">
					<img style="max-width:100px;margin-left:auto;margin-right:auto;" src="assets/images/schedules.png" class="img-responsive">
				</div>
			</div>
			</a>
		</div>
		{/if}
	{if $pEdit}
		<div class="col-md-3 col-sm-6 col-xs-12">
			<a href="{$app->baseUrl('/software')}" class="">
			<div class="panel panel-default">
				<div class="panel-heading text-center">
					<h2 style="font-size:2rem">Software</h2>
				</div>
				<div class="panel-body">
					<img style="max-width:100px;margin-left:auto;margin-right:auto;" src="assets/images/software.png" class="img-responsive">
				</div>
			</div>
			</a>
		</div>
		<div class="col-md-3 col-sm-6 col-xs-12">
			<a href="{$app->baseUrl('/configurations')}" class="">
			<div class="panel panel-default">
				<div class="panel-heading text-center">
					<h2 style="font-size:2rem">Configurations</h2>
				</div>
				<div class="panel-body">
					<img style="max-width:100px;margin-left:auto;margin-right:auto;" src="assets/images/bundles.png" class="img-responsive">
				</div>
			</div>
			</a>
		</div>
	</div>

	<div class="row">

		<div class="col-md-3 col-sm-6 col-xs-12">
			<a href="{$app->baseUrl('/tutorials')}" class="">
			<div class="panel panel-default">
				<div class="panel-heading text-center">
					<h2 style="font-size:2rem">Tutorials</h2>
				</div>
				<div class="panel-body">
					<img style="max-width:100px;margin-left:auto;margin-right:auto;" src="assets/images/tutorials.png" class="img-responsive">
				</div>
			</div>
			</a>
		</div>	


		<div class="col-md-3 col-sm-6 col-xs-12">
			<a href="{$app->baseUrl('/admin/communications')}" class="">
			<div class="panel panel-default">
				<div class="panel-heading text-center">
					<h2 style="font-size:2rem">Communications</h2>
				</div>
				<div class="panel-body">
					<img style="max-width:100px;margin-left:auto;margin-right:auto;" src="assets/images/email.png" class="img-responsive">
				</div>
			</div>
			</a>
		</div>	

		<div class="col-md-3 col-sm-6 col-xs-12">
			<a href="{$app->baseUrl('/rooms/metadata')}" class="">
			<div class="panel panel-default">
				<div class="panel-heading text-center">
					<h2 style="font-size:2rem">Room Settings</h2>
				</div>
				<div class="panel-body">
					<img style="max-width:100px;margin-left:auto;margin-right:auto;" src="assets/images/metadata.png" class="img-responsive">
				</div>
			</div>
			</a>
		</div>	

		<div class="col-md-3 col-sm-6 col-xs-12">
			<a href="{$app->baseUrl('/software/settings')}" class="">
			<div class="panel panel-default">
				<div class="panel-heading text-center">
					<h2 style="font-size:2rem">Software Settings</h2>
				</div>
				<div class="panel-body">
					<img style="max-width:100px;margin-left:auto;margin-right:auto;" src="assets/images/settings.png" class="img-responsive">
				</div>
			</div>
			</a>
		</div>	
	</div>
	{/if}

</div>
