<h1>Categories</h1>

<a href="categories/new/edit" class="btn btn-success">Add New Category</a>
<br><br>

<ul>
{foreach $categories as $category}
	<li><a href="categories/{$category->id}/edit">{$category->name}</a></li>
{/foreach}
</ul>
