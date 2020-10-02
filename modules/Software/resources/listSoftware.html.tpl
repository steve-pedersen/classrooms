
<h1>Software Titles</h1>

{if $pAdmin}
<div class="container-fluid">
<div class="row pull-right" style="margin-bottom: 1em;">
	<div class="col-sm-12">
		<a href="software/new/edit" class="btn btn-primary">Add New Title</a>
	</div>
</div>
</div>
{/if}

<div class="panel panel-default">
    <div class="panel-heading"><h2>Filter Titles</h2></div>
    <div class="panel-body">
        <form class="form-inline" role="form">
            <div class="form-group">
                <label class="filter-col" for="pref-perpage">Category</label>
				<select class="form-control" name="category" style="margin-right:2em;">
					<option value="">All</option>
				{foreach $categories as $category}
					<option value="{$category->id}" {if $selectedCategory == $category->id}selected{/if}>{$category->name}</option>
				{/foreach}
				</select>                             
            </div>
            <div class="form-group">
                <label class="filter-col" for="pref-perpage">Developer</label>
				<select class="form-control" name="developer" style="margin-right:2em;">
					<option value="">All</option>
				{foreach $developers as $developer}
					<option value="{$developer->id}" {if $selectedDeveloper == $developer->id}selected{/if}>{$developer->name}</option>
				{/foreach}
				</select>                         
            </div>
            <div class="form-group">    
                <button type="submit" class="btn btn-info filter-col">
                    Apply
                </button>
                {if $hasFilters}
                <a href="software" class="btn btn-link filter-col">
                    Clear filters
                </a> 
                {/if}
            </div>
        </form>
    </div>
</div>

<table class="table table-bordered table-striped table-condensed">
	<thead>
		<tr>
			<th scope="Title">Title</th>
			<th scope="Category">Category</th>
			<th scope="Developer">Developer</th>
			<th scope="Version"># of Versions</th>
			<th scope="License"># of Licenses</th>
		</tr>
	</thead>
	<tbody>
	{foreach $titles as $title}
		<tr>
			<th scope="col"><a href="software/{$title->id}">{$title->name}</a></th>
			<td>{$title->category->name}</td>
			<td>{$title->developer->name}</td>
			<td>{$title->versions->count()}</td>
			{assign var=licenseCount value=0}
			{foreach $title->versions as $version}
				{foreach $version->licenses as $license}
					{assign var=licenseCount value=($licenseCount + 1)}
				{/foreach}
			{/foreach}
			<td>{$licenseCount}</td>		
		</tr>
	{foreachelse}
		<tr>
			<td colspan="5">There are no software titles that meet your search criteria.</td>
		</tr>
	{/foreach}
	</tbody>
</table>