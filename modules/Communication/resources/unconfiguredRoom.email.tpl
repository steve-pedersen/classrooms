{$intro}
<ul>
	{foreach $rooms as $info}
	<li>
		<a href="{$info.room->url}">{$info.room->codeNumber}</a> - 
		{$info.course->fullDisplayName}
	</li>
	{/foreach}
</ul>