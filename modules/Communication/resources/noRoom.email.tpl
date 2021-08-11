{$intro}
<ul>
	{foreach $rooms as $info}
	<li>
		{$info.course->getFullDisplayName()}
	</li>
	{/foreach}
</ul>