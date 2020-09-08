<div class="col-12 col-sm-6 col-sm-offset-3 col-md-6 col-md-offset-3 col-lg-6 col-lg-offset-3">
    <div class="panel-padding panel panel-primary">
        <div class="panel-heading">Login</div>
        <div class="panel-body">
                <form method="post" action="{$postAction|escape}" class="prominent data">
                    <div class="form-group">
                        <input type="text" class="form-control" id="password-username" name="username" placeholder="Username/Email" alt="Username/Email">
                    </div>
    
                    <div class="form-group">
                        <input type="password" class="form-control" id="password-password" name="password" placeholder="Password" alt="password">
                    </div>
    
                    {if $error}<div class="alert alert-warning"><p>Login attempt failed</p></div>{/if}
    
                    <div class="form-group">
                        <button class="command-button btn btn-primary" type="submit" name="command[login]">Login</button>
                        
                        <a href="forgot" class="btn btn-link" target="_blank">Forgot your password?</a>

                    </div>
                </form>
        </div>
    </div>
</div>