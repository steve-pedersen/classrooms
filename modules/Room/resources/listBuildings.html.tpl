<h1>Buildings</h1>

<a href="buildings/new/edit" class="btn btn-success">Add New Building</a>
<br><br>

{foreach $buildings as $building}

<div class="panel panel-default room-card" id="{$room->id}">
  <div class="panel-body">
    <div class="row equal" style="min-height: 8rem;">
    	
    	<div class="col-sm-12" style="display:inline;">	

			<div class="media">
			  <div class="media-left media-middle">
			  	<a href="buildings/{$building->id}/edit" class="room-link">
			    <img src="{$building->image}" class="media-object img-responsive" style="max-width:150px;" alt="{$building->code} building">
							</a>
			  </div>
			  <div class="media-body media-middle">
			    <h4 class="media-heading"><a href="buildings/{$building->id}/edit" class="room-link">{$building->code} {$building->name}</a></h4>
			  </div>
			</div>
    	</div>
    </div>
  </div>
</div>

{/foreach}

