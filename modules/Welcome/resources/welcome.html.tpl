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
    <a href="{$app->baseUrl('login?returnTo=/purchase')}" class="btn btn-primary">Log In</a>
</div>
{else}
<div class="welcome-module">
	<a href="{$app->baseUrl('/rooms')}" class="btn btn-primary">View Rooms</a>
</div>
{/if}