<h1>Download unconfigured rooms</h1>

<form class="form-horizontal" method="post" action="{$smarty.server.REQUSET_URI}">
    <div class="data-form">

        <div class="form-group commands">
            <div class="col-xs-12">
            {generate_form_post_key}
            <input class="btn btn-primary" type="submit" name="command[download]" value="Download" />
            <a class="btn btn-link" href="{$app->baseUrl('')}">Cancel</a>
            </div>
        </div>

    </div>
</form>