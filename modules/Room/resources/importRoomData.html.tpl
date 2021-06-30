<div class="row">
    <div class="col-xs-12">
        <h1>Import LCA Inventory</h1>
        <p>This page allows you to import locations from a CSV.</p>
    </div>
</div>


<div class="row">
    <div class="col-xs-12">
        <form method="post" action="" enctype="multipart/form-data">
            <div>
                <label for="csv">CSV File</label>
                <input type="file" name="csv" id="csv">
            </div>

            <div class="controls">
                {generate_form_post_key}
                <button type="submit" class="btn btn-primary" name="command[upload]">Import</button>
                <a href="admin" class="btn btn-link pull-right">Cancel</a>
            </div>
        </form>
    </div>
</div>