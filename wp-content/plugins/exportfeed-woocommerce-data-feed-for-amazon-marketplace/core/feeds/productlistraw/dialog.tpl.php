<?php global $amwcore; ?>

<div class="attributes-mapping">
	<div class="createfeed" id="poststuff">
		<div class="postbox">

			<!-- *************** 
					Page Header 
					****************** -->

			<h3 class="hndle"><?php echo $this->service_name_long; ?></h3>
			<div class="inside export-target">

				<!-- *************** 
						LEFT SIDE 
						****************** -->



				<!-- *************** 
						RIGHT SIDE 
						****************** -->

				<div class="feed-right">

					<!-- ROW 1: Local Categories -->
					<div class="feed-right-row">
						<span class="label"><?php echo $amwcore->cmsPluginName; ?> Category : </span>
						<?php echo $this->localCategoryList; ?>
					</div>

					<!-- ROW 2: Remote Categories -->
					<?php echo $this->line2(); ?>
					<div class="feed-right-row">
						<?php echo $this->categoryList($initial_remote_category); ?>
					</div>
					<!-- ROW 3: Filename -->
					<div class="feed-right-row">
						<span class="label">File name for feed : </span>
						<span ><input type="text" name="feed_filename" id="feed_filename" class="text_big" value="<?php echo $this->initial_filename; ?>" /></span>
					</div>
					
					<!-- ROW 4: Get Feed Button -->
					<div class="feed-right-row">
						<input class="button button-primary" type="button" onclick="amwscp_doGetFeed('Productlistraw')" value="Get Feed" />
						<div class="feed-right-row">
						<label><span style="color: red">*</span> If you use an existing file name, the file will be overwritten.</label>
						</div>

						<div id="feed-error-display">&nbsp;</div>
						<div id="feed-status-display">&nbsp;</div>
					</div>
				</div>

				<!-- *************** 
						Termination DIV
						****************** -->

				<div style="clear: both;">&nbsp;</div>

				<!-- *************** 
						FOOTER
						****************** -->

				<div>
					<label class="un_collapse_label" title="Advanced" id="toggleAdvancedSettingsButton" onclick="amwscp_toggleAdvancedDialog()">[ Open Advanced Commands ]</label>
					<label class="un_collapse_label" title="Erase existing mappings" id="erase_mappings" onclick="amwscp_doEraseMappings('<?php echo $this->service_name; ?>')">[ Reset Attribute Mappings ]</label>
				</div>


				<div class="feed-advanced" id="feed-advanced">
					<textarea class="feed-advanced-text" id="feed-advanced-text"><?php echo $this->advancedSettings; ?></textarea>
					<?php echo $this->cbUnique; ?>
					<button class="navy_blue_button" id="bUpdateSetting" name="bUpdateSetting" onclick="amwscp_doUpdateSetting('feed-advanced-text', 'cp_advancedFeedSetting-<?php echo $this->service_name; ?>'); return false;" >Update</button>
					<div id="updateSettingMessage">&nbsp;</div>
				</div>
			</div>
		</div>
	</div>
</div>
