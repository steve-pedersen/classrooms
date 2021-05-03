<h1>Welcome to the Classrooms Database Application</h1>

<div id="welcome-text">
{if $welcomeText}
{$welcomeText}
{else}
<p>Classrooms Database info here.</p>
{/if}
</div>


{if !$userContext->account}
<div class="welcome-module">
    <a href="{$app->baseUrl('login?returnTo=/')}" class="btn btn-primary">Log In</a>
</div>
{else}
<div class="welcome-module">
	<div class="row">
		<div class="col-md-3 col-sm-6 col-xs-12">
			<a href="{$app->baseUrl('/rooms')}" class="">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h2 style="font-size:2rem">Rooms</h2>
				</div>
				<div class="panel-body">
					<img style="max-height:100px;" src="assets/images/rooms.png" class="img-responsive">
				</div>
			</div>
			</a>
		</div>
		<div class="col-md-3 col-sm-6 col-xs-12">
			<a href="{$app->baseUrl('/schedules')}" class="">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h2 style="font-size:2rem">Room Schedules</h2>
				</div>
				<div class="panel-body">
					<img style="max-height:100px;" src="assets/images/schedules.png" class="img-responsive">
				</div>
			</div>
			</a>
		</div>
		<div class="col-md-3 col-sm-6 col-xs-12">
			<a href="{$app->baseUrl('/software')}" class="">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h2 style="font-size:2rem">Software</h2>
				</div>
				<div class="panel-body">
					<img style="max-height:100px;" src="assets/images/software.png" class="img-responsive">
				</div>
			</div>
			</a>
		</div>
		<div class="col-md-3 col-sm-6 col-xs-12">
			<a href="{$app->baseUrl('/configurations')}" class="">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h2 style="font-size:2rem">Configurations</h2>
				</div>
				<div class="panel-body">
					<img style="max-height:100px;" src="assets/images/bundles.png" class="img-responsive">
				</div>
			</div>
			</a>
		</div>
	</div>

	<!-- <a href="{$app->baseUrl('/software')}" class="btn btn-primary"></a> -->
	<!-- <a href="{$app->baseUrl('/configurations')}" class="btn btn-primary">View Configurations</a> -->
	<!-- <a href="{$app->baseUrl('/departments')}" class="btn btn-primary">View Departments</a> -->
</div>
{/if}