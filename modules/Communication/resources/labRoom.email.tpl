{$intro}
<ul>
	{foreach $rooms as $info}
	<li>
		<a href="{$info.room->roomUrl}">{$info.room->codeNumber}</a> - 
		{$info.course->fullDisplayName}
	</li>
	{/foreach}
</ul>