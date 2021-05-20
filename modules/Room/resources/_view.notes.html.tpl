
<ul class="list-unstyled">

{foreach $notes as $note}
	<li>
		<strong style="margin-right:1em;">{$note->createdBy->fullName} ({$note->createdDate->format('Y-m-d h:i a')}):</strong>
		{$note->message}
		
		{if $note->oldValues}
		<a class="collapse-button collapsed" data-toggle="collapse" data-parent="#accordion" href="#noteHistory{$note->id}" aria-expanded="true" aria-controls="noteHistory{$note->id}" style="margin-left: 2em; font-weight: bold;float:right;">
			Show History &nbsp;
		</a>
		<div id="accordion">
			<div class="panel-collapse collapse" role="tabpanel" id="noteHistory{$note->id}">
				<table class="table table-bordered">
					<thead>
						<tr>
							<th scope="Field">Field</th>
							<th scope="Old">Old</th>
							<th scope="New">New</th>
						</tr>
					</thead>
					<tbody>
						{foreach $note->oldValues as $key => $value}
						<tr>
							<td scope="col">{$key|ucfirst}</td>
							<td>
								{if $value == 'checked'}
									<i class="glyphicon glyphicon-ok text-success"></i>
								{elseif $value == 'unchecked'}
									<i class="glyphicon glyphicon-remove text-danger"></i>
								{else}
									{if $value}{$value}{else}--{/if}
								{/if}
							</td>
							<td>
								{if $note->newValues[$key] == 'checked'}
									<i class="glyphicon glyphicon-ok text-success"></i>
								{elseif $note->newValues[$key] == 'unchecked'}
									<i class="glyphicon glyphicon-remove text-danger"></i>
								{else}
									{$note->newValues[$key]}
								{/if}
							</td>
						</tr>
						{/foreach}
					</tbody>
				</table>
			</div>
		</div>
		{/if}
	</li>
{/foreach}

</ul>