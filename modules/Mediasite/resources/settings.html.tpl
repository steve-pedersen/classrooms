<form class="form-horizontal" action="{$smarty.server.REQUEST_URI}" method="post">

    <div class="form-group">
        <div class="col-xs-12">
            <h1>Mediasite Backup Settings</h1>
            <p>Set the dates to start migration and deletion.</p>
        </div>
    </div>
        
    <div class="form-group">
        <div class="col-xs-12">
            <label for="migration-date">Migration Date</label>
            <input type="text" class="form-control text-field" name="migration-date" id="migration-date" value="{$migrationDate}">
        </div>
    </div>

    <div class="form-group">
        <div class="col-xs-12">
            <label for="deletion-date">Deletion Date</label>
            <input type="text" class="form-control text-field wysiwyg" name="deletion-date" id="deletion-date" value="{$deletionDate}">
        </div>
    </div>
       
    <div class="form-group">
        <div class="col-xs-12">
            <div class="controls">
                {generate_form_post_key}
                <button class="btn btn-primary" type="submit" name="command[save]">Save</button>
                <a class="cancel btn btn-link" href="">Cancel</a>
        </div>
        </div>
    </div>
</form>
