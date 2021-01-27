
<h1>Software Titles</h1>

<div class="well multiselect-filter">
	<h2>Filter Titles</h2>
	<form class="form-inline" role="form" id="filterForm">
    <div class="form-group">
		<select id="filter-categories" name="categories[]" class="multiselect form-control" multiple label="Categories">
		{foreach $categories as $category}
			<option value="{$category->id}" {if $selectedCategories && in_array($category->id, $selectedCategories)}selected{/if}>
				{$category->name}
			</option>
		{/foreach}	
		</select>		        
    </div>
    <div class="form-group">
		<select id="filter-developers" class="multiselect form-control" name="developers[]" multiple label="Developers">
		{foreach $developers as $developer}
			<option value="{$developer->id}" {if $selectedDevelopers && in_array($developer->id, $selectedDevelopers)}selected{/if}>
				{$developer->name}
			</option>
		{/foreach}
		</select>     
    </div>
    <div class="form-group">    
        <button type="submit" class="btn btn-info filter-col">
            Apply
        </button>
        <a href="software" class="btn btn-link filter-col" id="clearFilters">
            Clear filters
        </a> 
    </div>
	{if $canEdit}
    <div class="form-group" id="addNew">
		<a href="software/new/edit" class="btn btn-success">Add New Software</a>
    </div>
	{/if}
	</form>
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