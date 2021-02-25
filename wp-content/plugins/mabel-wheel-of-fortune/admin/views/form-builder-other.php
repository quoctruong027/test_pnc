<div id="wof-form-builder-modal" style="display: none;">
	<table class="form-table add-form-builder-field-table">
		<tr>
			<th><?php _e('Type','mabel-wheel-of-fortune');?></th>
			<td>
				<select name="wof-form-builder-type">
					<option value="text"><?php _e('Text','mabel-wheel-of-fortune');?></option>
					<option value="email"><?php _e('Email','mabel-wheel-of-fortune');?></option>
					<option value="consent_checkbox"><?php _e('Consent checkbox','mabel-wheel-of-fortune');?></option>
				</select>
			</td>
		</tr>
		<tr>
			<th><?php _e('Label','mabel-wheel-of-fortune');?></th>
			<td>
				<input type="text" name="wof-form-builder-placeholder" placeholder="<?php _e('Your label','mabel-wheel-of-fortune');?>" />
			</td>
		</tr>
		<tr class="formbuilder-add-field-required-row">
			<th><?php _e('Required','mabel-wheel-of-fortune');?></th>
			<td>
				<input type="checkbox" name="wof-form-builder-required" id="wof-fb-cb"/> <label for="wof-fb-cb"><?php _e('This field is required','mabel-wheel-of-fortune');?></label>
			</td>
		</tr>
	</table>
	<div class="mabel-modal-button-row">
		<a href="#" class="btn-add-form-builder-field mabel-btn"><?php _e('Add','mabel-wheel-of-fortune');?></a>
	</div>
</div>

<table class="form-table wof-styled-table wof-field-builder-table">
	<thead>
		<tr>
			<th colspan="10" style="text-align: right;">
				<a title="<?php _e('Add a field to the opt-in form','mabel-wheel-of-fortune');?>" href="#TB_inline?width=500&height=450&inlineId=wof-form-builder-modal" class="thickbox">
					<?php _e('Add new field','mabel-wheel-of-fortune');?>
				</a>
			</th>
		</tr>
		<tr>
			<th></th>
			<th><?php _e('Label','mabel-wheel-of-fortune');?></th>
			<th><?php _e('Type','mabel-wheel-of-fortune');?></th>
			<th><?php _e('Required','mabel-wheel-of-fortune');?></th>
			<th></th>
		</tr>
		<tr class="form-field-tr" data-field-id="primary_email">
			<td></td>
			<td><input class="widefat mabel-form-element" type="text" name="email_placeholder" placeholder="<?php _e('Your email','mabel-wheel-of-fortune');?>" data-key="email_placeholder"></td>
			<td class="field-type"><?php _e('Primary email','mabel-wheel-of-fortune');?></td>
			<td class="field-required"><?php _e('Yes','mabel-wheel-of-fortune');?></td>
			<td></td>
		</tr>
	</thead>
	<tbody>
	</tbody>
</table>

<script id="tpl-wof-field-builder-other-row" type="text/x-dot-template">
	{{~ it.fields :value}}
	<tr class="form-field-tr" data-field-id="{{=value.id}}">
		<td>
			<span class="sorting-handle">☰</span>
		</td>
		<td class="field-placeholder">
			{{=value.placeholder}}
		</td>
		<td class="field-type" style="text-transform: capitalize;">
			{{=value.type.replace('_',' ')}}
		</td>
		<td class="field-required">
			{{? value.required}}
			<?php _e('Yes','mabel-wheel-of-fortune');?>
			{{??}}
			<?php _e('No','mabel-wheel-of-fortune');?>
			{{?}}
		</td>
		<td style="text-align: right;"><a href="#" class="btn-form-builder-other-remove-field"><?php _e('Remove','mabel-wheel-of-fortune');?></a></td>
	</tr>
	{{~}}
</script>