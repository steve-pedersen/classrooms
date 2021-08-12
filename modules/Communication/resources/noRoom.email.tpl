{$intro}
<ul>
	{foreach $rooms as $info}
	<li>
		{if $info.course->fullDisplayName}{$info.course->fullDisplayName}{/if}
	</li>
	{/foreach}
</ul>