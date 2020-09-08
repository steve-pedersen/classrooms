<h1>Welcome to the Workstation Request Application</h1>

<div id="welcome-text">
{if $welcomeText}
{$welcomeText}
{else}
<p>Workstation purchase requests can be made here.</p>
{/if}
</div>


{if !$userContext->account}
<div class="welcome-module">
    <a href="{$app->baseUrl('login?returnTo=/purchase')}" class="btn btn-primary">Log In</a>
</div>
{else}
<div class="welcome-module">
	<a href="{$app->baseUrl('purchase')}" class="btn btn-primary">Go To Requests</a>
</div>
{/if}