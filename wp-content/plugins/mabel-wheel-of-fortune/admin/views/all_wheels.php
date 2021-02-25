<div class="wof-all-wheels-wrapper">
	<span class="wof-no-results"><?php _e("You haven't created any wheels yet.", 'mabel-wheel-of-fortune'); ?></span>
	<div class="wof-wheels-list">
	</div>
</div>

<div id="wof-optins-log-modal" style="display: none;">
	<p class="t-c">
		<?php _e('Below is a list of the 30 last logs. You can also export the whole list to CSV. This may take a while depending on the size.','mabel-wheel-of-fortune'); ?>
	</p>
	<table class="wof-optins-log form-table wof-styled-table">
		<thead><tr><th><?php _e('Date','mabel-wheel-of-fortune');?></th><th style="width:60px;"><?php _e('Type','mabel-wheel-of-fortune');?></th><th><?php _e('Log','mabel-wheel-of-fortune');?></th></tr></thead>
		<tbody></tbody>
	</table>
	<div class="mabel-modal-button-row">
		<a href="javascript:tb_remove();" class="mabel-btn"><?php _e('Close','mabel-wheel-of-fortune');?></a>
		<a href="#" class="wof-btn-export-optins-csv mabel-btn"><?php _e('Export all opt-ins','mabel-wheel-of-fortune');?></a>
		<a href="#" class="wof-btn-export-plays-csv mabel-btn"><?php _e('Export all plays','mabel-wheel-of-fortune');?></a>
		<a href="#" class="mabel-btn wof-btn-delete-logs"><?php _e('Delete logs','mabel-wheel-of-fortune');?></a>
	</div>
</div>

<div id="wof-statistics-modal" style="display: none;"></div>

<script id="tpl-wof-statistics-modal" type="text/x-dot-template">
	<div class="wof-stats-wrapper p-t-5">
		<div class="mabel-row">
			<div class="mabel-four mabel-columns">
				<span>{{=it.views}}</span>
				<span><?php _e('Views','mabel-wheel-of-fortune');?></span>
				<span class="wof-stat-info">
					<?php _e('This number shows how many users have seen your popup.','mabel-wheel-of-fortune');?>
				</span>
			</div>
			<div class="mabel-four mabel-columns">
				<span>{{=it.optins}}</span>
				<span><?php _e('Optins','mabel-wheel-of-fortune');?></span>
				<span class="wof-stat-info">
					<?php _e('This number shows how many users have opted in to your email list in order to play.','mabel-wheel-of-fortune');?>
				</span>
			</div>
			<div class="mabel-four mabel-columns">
				<span>
					{{=it.rate}}%
				</span>
				<span><?php _e('Opt-in rate','mabel-wheel-of-fortune');?></span>
				<span class="wof-stat-info">
					<?php _e('This number shows your conversion rate.','mabel-wheel-of-fortune');?>
				</span>
			</div>
		</div>
	</div>

</script>

<script id="tpl-wof-wheels-list" type="text/x-dot-template">
	{{~ it.wheels :value}}
	<div data-id="{{=value.id}}" class="image-tile">

		<div class="tile-header">
			<span class="tag-id">
				{{? value.name}}
					{{=value.name}} ({{=value.id}})
				{{??}}
					{{=value.id}}
				{{?}}
			</span>

			<div class="tile-wheel-preview">
				<div class="tile-wheel-preview" style="margin-left: -90px;">
					<div class="wof-wheel-bg">
						<div>
							<svg lass="wof-fg-svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="-1 -1 2 2">
								<g transform="rotate(-{{=(360/value.amount_of_slices)/2}} 0 0) scale(.89,.89)">
									{{~ value.slices :slice:idx}}
										{{=WOFAdmin.helpers.Wheels.createSlicePath(idx,value.amount_of_slices,slice.bg)}}
									{{~}}

								</g>
							</svg>
						</div>

					</div>

				</div>
			</div>

		</div>
		<div class="tile-footer">
			<div>
				Active <input type="checkbox" name="active" {{! (value.active == 1) ? ' checked="checked" ' : '' }} class="skip-save wof-toggle-active" data-wheel="{{=value.id}}" />
			</div>
			<ul {{? value.list_provider === 'wordpress' || value.log === true}} class="wof-tile-footer-5" {{?}}>
				{{? value.list_provider === 'wordpress' || value.log === true }}
				<li>
					<a href="#" title="View logs" class="wof-view-optins" data-wheel="{{=value.id}}">
						<i class="dashicons dashicons-list-view"></i>
					</a>
				</li>
				{{?}}
				<li>
					<a href="#" title="View statistics" class="wof-get-statistics" data-wheel="{{=value.id}}">
						<i class="dashicons dashicons-chart-bar"></i>
					</a>
				</li>
				<li>
					<a href="#" title="Edit wheel" class="wof-edit-wheel" data-wheel="{{=value.id}}"><i class="dashicons dashicons-edit"></i></a>
				</li>
				<li>
					<a href="#" title="Delete wheel" class="wof-delete-wheel" data-wheel="{{=value.id}}"><i class="dashicons dashicons-trash"></i></a>
				</li>
				<li>
					<a href="#" class="wof-duplicate-wheel" title="Duplicate wheel" data-wheel="{{=value.id}}"><i class="dashicons dashicons-images-alt2"></i></a>
				</li>
			</ul>
		</div>
	</div>
	{{~}}
</script>