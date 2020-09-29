<div class="view-tutorial">
	{if $room->tutorial->headerImageUrl}
	<div class="row">
		<div class="col-sm-12">
			<img src="{$room->tutorial->headerImageUrl}" class="img-responsive">	
		</div>
	</div>
	{/if}
	<div class="tutorial-body">
		{$room->tutorial->description}
	</div>
</div>