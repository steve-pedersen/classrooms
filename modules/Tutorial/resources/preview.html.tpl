<div class="view-tutorial">
	{if $tutorial->headerImageUrl}
	<div class="row">
		<div class="col-sm-12">
			<img src="{$tutorial->headerImageUrl}" class="img-responsive">	
		</div>
	</div>
	{/if}
	{if $tutorial->youtubeEmbedCode}
	<div class="row">
		<div class="col-sm-12">
			<h3>Video Tutorial</h3>
			{$tutorial->youtubeEmbedCode}
		</div>
	</div>
	{/if}
	<div class="tutorial-body">
		{$tutorial->description}
	</div>

	<div class="well">
		<p><strong>Request Additional Help:&nbsp;</strong></p>
		<p>M-Th 7:45am-10:30pm</p>
		<p>F 7:45am-5pm</p>
		<p>(415) 405-5555</p>
		<p>LIB 80</p>
	</div>
</div>