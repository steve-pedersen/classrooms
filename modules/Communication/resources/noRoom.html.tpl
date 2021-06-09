{$intro}
<ul>
	{foreach $rooms as $info}
	<li>
		{$info.course->fullDisplayName}
	</li>
	{/foreach}
</ul>