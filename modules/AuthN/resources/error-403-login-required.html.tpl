<h1>Login Required</h1>
<p>
    You must <a href="login{if $selectedIdentityProvider}?idp={$selectedIdentityProvider|escape}{/if}">login to access the requested resource</a>.
</p>

{if !$soleProvider}{include file="partial:_wayf"}{/if}
