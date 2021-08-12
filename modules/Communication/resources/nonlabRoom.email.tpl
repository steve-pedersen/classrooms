{$intro}
<ul>
	{foreach $rooms as $info}
	<li>
		<a href="{$info.room->roomUrl}">{$info.room->codeNumber}</a> - 
		{if $info.course && $info.course->fullDisplayName}$info.course->fullDisplayName}{/if}
	</li>
	{/foreach}
</ul>