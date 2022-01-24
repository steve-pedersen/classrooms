<h1>Mediasite Presentation Import</h1>
<p>
    You can upload a CSV of mediasite presentation IDs (one per line).
</p>


{if $errorMap}
    <div class="info">
        <p>There are errors in the submission.</p>
        <dl>
        {foreach item='errorList' key='errorKey' from=$errorMap}
            <dt>{$errorKey}</dt>
            {foreach item='errorMessage' from=$errorList}
            <dd>{$errorMessage}</dd>
            {/foreach}
        {/foreach}
        </dl> 
    </div>
{/if}

<form method="post" action="" enctype="multipart/form-data">
    <div class="field">
        <label for="csv" class="field-label">Upload CSV</label>
        <input type="file" id="csv" name="csv">
    </div>
    <div class="controls">
        {generate_form_post_key}
        <input type="submit" class="command-button btn btn-primary" name="command[upload]" value="Upload">
    </div>

    <p></p>

</form>
