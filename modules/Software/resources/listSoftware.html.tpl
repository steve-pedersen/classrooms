
<h1>Software Titles</h1>

<div class="well multiselect-filter">
	<h2>Filter</h2>
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
		<select id="filter-expirations" class="singleselect form-control" name="expiration" label="Has license expiring">
			<option value="" default>any time</option>
			<option value="1 day" {if $expiration == '1 day'}selected{/if}>1 day</option>
			<option value="1 week" {if $expiration == '1 week'}selected{/if}>1 week</option>
			<option value="1 month" {if $expiration == '1 month'}selected{/if}>1 month</option>
			<option value="3 months" {if $expiration == '3 months'}selected{/if}>3 months</option>
			<option value="6 months" {if $expiration == '6 months'}selected{/if}>6 months</option>
			<option value="1 year" {if $expiration == '1 year'}selected{/if}>1 year</option>
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
			<th scope="Expiration">License Expiration</th>
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
			<td>
				<!-- <button data-toggle="collapse" data-target="#licenses{$title->id}" class="btn btn-sm btn-default">Show all</button> -->
				<!-- <div id="licenses{$title->id}" class="collapse"> -->
				{assign var=licenseCount value=0}
				<ul class="list-unstyled" style="margin-bottom:0;">
				{foreach $title->versions as $version}
					{foreach $version->licenses as $license}
						{assign var=licenseCount value=($licenseCount + 1)}
						<li>
							<a href="software/{$title->id}/licenses/{$license->id}/edit">
								{$license->expirationDate->format('Y-m-d h:ia')}
							</a>
						</li>
					{/foreach}
				{/foreach}
				</ul>
				<!-- </div> -->
			</td>
			<td>{$title->versions->count()}</td>

			<td>{$licenseCount}</td>

		</tr>
	{foreachelse}
		<tr>
			<td colspan="6">There are no software titles that meet your search criteria.</td>
		</tr>
	{/foreach}
	</tbody>
</table>