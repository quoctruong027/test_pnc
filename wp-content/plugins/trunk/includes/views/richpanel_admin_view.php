<style>
	a:link{
	color: #1a5792;
	}
	.rp-landing {
	width: 100%;
	justify-content: center;
	display: flex;
	padding: 20px;
	font-family: Montserrat,sans-serif;
	text-decoration: none;
	background-color: #f1f1f1;
	height: 100vh;
	}
	.rp-plugin-form {
	width: 450px;
	}
	.rp-logo {
	text-align: center;
	}
	.rp-title {
	text-align: center;
	font-size: 30px;
	font-weight: 600;
	margin-top: 16px;
	color: #1b1b1b;
	}
	.rp-getting-started-text {
	font-size: 16px;
	margin-top: 20px;
	color: #7e8f9f;
	text-align: center;
	}
	.rp-registration-text {
	font-size: 14px;
	margin-top: 4px;
	color: #7e8f9f;
	text-align: center;
	}
	.rp-api-key-input {
	margin-top: 40px;
	}
	.rp-input-title {
	color: #4d4d4d;
	font-size: 13px;
	font-weight: 500;
	line-height: 30px;
	}
	.rp-api-key-input > input, .rp-api-secret-input > input {
	width: 100%;
	height: 50px;
	border-radius: 4px;
	border: 1px solid #dcdfe6;
	padding: 0 15px;
	font-weight: 300;
	color: #4f5d6a;
	font-size: 16px;
	margin-bottom: 16px;
	}
	.rp-save-keys-button {
	text-align: center;
	}
	.rp-save-keys-button > button {
	height: 50px;
	font-size: 14px;
	padding: 0px 35px;
	font-weight: 400;
	margin: 0;
	border: 0;
	border-radius: 5px;
	background-color: #004e96;
	border: 0px;
	color: #fff;
	cursor: pointer;
	width: 100%;
	}
	.rp-register-button {
	margin-top: 20px;
	text-align: center;
	font-size: 14px;
	}

	.rp-loader {
		height: calc(100% - 120px);
		width: 100%;
		display: flex;
		justify-content: center;
		align-items: center;
		font-size: 30px;
		color: #636363;
	}

	.rp-save-keys-button {
		display: none;
	}

	.rp-api-edit-form {
		display: none;
	}
	.rp-api-view-form {
		display: none;
	}

	.rp-key-details {
	margin-top: 40px;
	display: flex;
	justify-content: space-around;
	border-top: 1px solid #ccc;
	border-bottom: 1px solid #ccc;
	/*   padding-top: 10px; */
	}
	.rp-key-box {
	display: flex;
	justify-content: space-between;
	align-items: center;
	}
	.rp-edit {
	color: #999;
	cursor: pointer;
	}
	.rp-key-view {
	width: 100%;
	}
	.rp-api-key {
	border-bottom: 1px solid #ccc;
	}
	.rp-api-key, .rp-api-secret {
	/*   margin-bottom: 10px; */
	font-size: 14px;
	padding: 20px 10px;
	}
	.rp-api-key > i, .rp-api-secret > i {
	color: gray;
	}
	.rp-key-edit-button {
	justify-content: center;
	display: flex;
	flex-direction: column;
	}
	.rp-key-edit-button > button {
	height: 30px;
	font-size: 12px;
	padding: 0px 20px;
	font-weight: 400;
	margin: 0;
	border: 0;
	border-radius: 5px;
	background-color: #004e96;
	border: 1px solid #004e96;
	color: #fff;
	cursor: pointer;
	}

	.rp-data-sync {
	margin-top: 40px;
	text-align: center;
	}
	.rp-data-sync--text {
	font-weight: 600;
	font-size: 20px;
	}
	.rp-data-sync--actions {
	display: flex;
	margin-top: 20px;
	justify-content: space-between;
	}
	.rp-order-sync-button > button {
	height: 50px;
	font-size: 14px;
	padding: 0px 30px;
	font-weight: 400;
	margin: 0;
	border: 0;
	border-radius: 5px;
	background-color: #fff;
	border: 1px solid #ccc;
	color: #333;
	cursor: pointer;
	}
	.rp-order-sync-button > button:hover{
	/*   border: 1px solid #004e96; */
	background-color: #f2f2f2;
	}
	.rp-primary-button {
	height: 50px;
	font-size: 14px;
	padding: 0px 35px;
	font-weight: 400;
	margin: 0;
	border: 0;
	border-radius: 5px;
	background-color: #004e96;
	border: 0px;
	color: #fff;
	cursor: pointer;
	}
	.rp-secondary-button {
	height: 50px;
	font-size: 14px;
	padding: 0px 30px;
	font-weight: 400;
	margin: 0;
	border: 0;
	border-radius: 5px;
	background-color: #fff;
	border: 1px solid #ccc;
	color: #333;
	cursor: pointer;
	}
	.rp-save-cancel-keys-button{
	justify-content: space-between;
	display: flex;
	}
	.rp-save-cancel-keys-button > * {
	width: 48%;
	}
	.rp-data-sync--subtext {
	margin-top: 10px;
	font-size: 14px;
	color: #444;
	}
	.rp-data-sync--success {
		margin-top: 15px;
		color: green;
		font-size: 14px;
		display: none;
	}
</style>
<?php
wp_enqueue_style( 'richpanel-plugin-font', 'https://fonts.googleapis.com/css?family=Montserrat&display=swap', array(), 'default' );
wp_enqueue_style( 'richpanel-plugin-m-icon', 'https://fonts.googleapis.com/icon?family=Material+Icons', array(), 'default' );
wp_enqueue_style( 'richpanel-plugin-f-icon', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.1.0/css/font-awesome.min.css', array(), '4.1.0' );
?>
<div class="rp-landing">
  <div class="rp-plugin-form">
	<div class="rp-logo">
	  <img src="https://richpanel-assets.s3-us-west-2.amazonaws.com/Richpanel-icon_without_around_space.png" style="width: 56px; height: 56px;">
	</div>
	<div class="rp-title">
	  Richpanel Helpdesk
	</div>
	<div class="rp-loader">
	  <i class='fa fa-circle-o-notch fa-spin'></i>
	</div>
	<div class="rp-api-edit-form">
	  <div class="rp-getting-started-text">
		Enter your API Keys to Get Started
	  </div>
	  <div class="rp-registration-text">
		(Don't have API Keys ? 
		<a href="https://app.richpanel.com/user/signup" target="_blank">Register here</a>)
	  </div>
		<form method="post" id="rp_key_form">
			<div class="rp-api-key-input">
				<div class="rp-input-title">
				API Key*
				</div>
				<input id="rp_api_key_i" type="text" name="woocommerce_richpanel-woo-analytics_api_key" placeholder="API Key"/>
			</div>
			<div class="rp-api-secret-input">
				<div class="rp-input-title">
				API Secret*
				</div>
				<input id="rp_api_secret_i" placeholder="API Secret" name="woocommerce_richpanel-woo-analytics_api_secret" type="password"/>
			</div>
			<?php
				// settings_fields( 'richpanel' );
				// do_settings_sections( 'richpanel' );
			?>
		</form>
	  <div class="rp-save-keys-button">
		<?php wp_nonce_field( 'woocommerce_richpanel-woo-analytics_api_key' ); ?>
		<button type="submit" form="rp_key_form" style="font-family: Montserrat,sans-serif;">Save Keys</button>
	  </div>
	  <div class="rp-save-cancel-keys-button">
		<?php 
			// $other_attributes = array( 'id' => 'wpdocs-button-id', 'form' => 'rp_key_form', 'class' => 'rp-primary-button', 'style'=>"font-family: Montserrat,sans-serif;" );
			// submit_button('Update Keys', 'primary', 'richpanel-save-keys', false, $other_attributes); 
		?>
		<button type="submit" form="rp_key_form" class="rp-primary-button" style="font-family: Montserrat,sans-serif;">Update Keys</button>
		<button class="rp-secondary-button" id="edit_cancel_button" style="font-family: Montserrat,sans-serif;">Cancel</button>
	  </div>
	  <div class="rp-register-button">
		<a href="https://app.richpanel.com/user/signup" target="_blank">
		Click here to Register & Get API Keys
		</a>
	  </div>
	</div>
	<div class="rp-api-view-form">
	  <div class="rp-getting-started-text">
		<button id="rp-open-dashboard" class="rp-primary-button" style="font-family: Montserrat,sans-serif;">View Dashboard</button>
	  </div>
	  <div class="rp-key-details">
		<div class="rp-key-view">
		  <div class="rp-api-key rp-key-box">
			<div class="rp-api--text">
			  API Key: <i id="rp_api_key_o"></i>
			</div>
			<div class="rp-edit">
			  <i class="material-icons">edit</i>
			</div>
		  </div>
		  <div class="rp-api-secret rp-key-box">
			<div class="rp-api--text">
			  API Secret: <i>********</i>
			</div>
			<div class="rp-edit">
			  <i class="material-icons">edit</i>
			</div>
		  </div>
		</div>
	  </div>
	  <div class="rp-data-sync">
		<div class="rp-data-sync--text">
		  Sync Orders & Subcriptions with Richpanel
		</div>
		<div class="rp-data-sync--subtext">
		  We Sync orders of last 24 months
		</div>
		<div class="rp-data-sync--actions">
		  <div class="rp-order-sync-button">
			<button style="font-family: Montserrat,sans-serif;"  id="orderSyncButton">
				Sync Orders (<?php echo esc_attr($this->orders_total); ?>)
			</button>
		  </div>
		  <div class="rp-order-sync-button">
			<button style="font-family: Montserrat,sans-serif;" id="subscriptionSyncButton">
				Sync Subscription (<?php echo esc_attr($this->subscription_total); ?>)
			</button>
		  </div>
		</div>
		<div class="rp-data-sync--success">
		  Done! Please expect up to 30 minutes for your historical data to appear in Richpanel.
		</div>
	  </div>
	</div>
  </div>
</div>
<script>
	const $ = jQuery
	let apiKey = '<?php echo esc_attr($this->api_key); ?>';
	let apiSecret = '<?php echo esc_attr($this->api_secret); ?>';

	const toggleLoader = () => {
		$('.rp-loader').toggle()
	}

	const showUpdateCancelButtons = () => {
		$('.rp-save-cancel-keys-button').show()
	}

	const hideUpdateCancelButtons = () => {
		$('.rp-save-cancel-keys-button').hide()
	}

	const showSaveButton = () => {
		$('.rp-save-keys-button').show()
	}

	const hideSaveButton = () => {
		$('.rp-save-keys-button').hide()
	}

	const showAPIEditForm = () => {
		$('.rp-api-edit-form').show()
	}

	const hideAPIEditForm = () => {
		$('.rp-api-edit-form').hide()
	}

	const showAPIViewForm = () => {
		$('.rp-api-view-form').show()
	}

	const hideAPIViewForm = () => {
		$('.rp-api-view-form').hide()
	}

	const setOutputAPIKey = (val) => {
		$('#rp_api_key_o').text(val)   
	}

	const setInputAPIKey = (val) => {
		$('#rp_api_key_i').val(val)
	}

	const setInputAPISecret = (val) => {
		$('#rp_api_secret_i').val(val)
	}

	const onEditClick = () => {
		toggleLoader()
		hideAPIViewForm()
		showAPIEditForm()
		hideSaveButton()
		showUpdateCancelButtons()
		setInputAPIKey(apiKey)
		setInputAPISecret(apiSecret)
		toggleLoader()
	}

	const onCancelClick = () => {
		toggleLoader()
		showAPIViewForm()
		hideAPIEditForm()
		setOutputAPIKey(apiKey)
		toggleLoader()
	}

	const disableSyncButtons = () => {
		$("#orderSyncButton").attr("disabled", true)
		$("#subscriptionSyncButton").attr("disabled", true)
	}

	const enableSyncButtons = () => {
		$("#orderSyncButton").attr("disabled", false)
		$("#subscriptionSyncButton").attr("disabled", false)
	}

	const resetSyncButtons = () => {
		$("#orderSyncButton").text(`Sync Orders (<?php echo esc_attr($this->orders_total); ?>)`);
		$("#subscriptionSyncButton").text(`Sync Subscription (<?php echo esc_attr($this->subscription_total); ?>)`);
		enableSyncButtons()
	}

	const setOrderSyncProgress = (val) => {
		$("#orderSyncButton").html(`<i class='fa fa-circle-o-notch fa-spin'></i> ${val}% Processed`)
	}

	const setSubscriptionSyncProgress = (val) => {
		$("#subscriptionSyncButton").html(`<i class='fa fa-circle-o-notch fa-spin'></i> ${val}% Processed`)
	}

	const updateSyncSucessMessgae = () => {
		$(".rp-data-sync--success").show()
		setTimeout(() => {
			location.href = "<?php echo esc_url( get_admin_url(null, 'admin.php?page=richpanel-admin' )); ?>"
		}, 1000);
	}
	
	toggleLoader()

	if (apiKey && apiSecret) {
		setOutputAPIKey(apiKey)
		showAPIViewForm()
	} else {
		showAPIEditForm()
		hideUpdateCancelButtons()
		showSaveButton()
	}

	$('.rp-edit').off().on('click', () => onEditClick())
	$('#edit_cancel_button').off().on('click', () => onCancelClick())
	$('#rp-open-dashboard').off().on('click', () => window.open('https://app.richpanel.com', '_blank'))
	$('#orderSyncButton').off().on('click', () => location.href = "<?php echo esc_url( get_admin_url(null, 'admin.php?page=richpanel-admin' )); ?>&import=1")
	$('#subscriptionSyncButton').off().on('click', () => location.href = "<?php echo esc_url( get_admin_url(null, 'admin.php?page=richpanel-admin' )); ?>&simport=1")

	<?php if ($this->importing) : ?>
		disableSyncButtons()

		const chunk_pages = <?php echo esc_html_e($this->chunk_pages_order); ?>;
		let chunk_percentage = 100;
		if(chunk_pages > 0){
			chunk_percentage = (100 / chunk_pages);
		}
		const sync_chunk = function(chunk_page){
			let progress_percents = Math.round(chunk_page * chunk_percentage);
			setOrderSyncProgress(progress_percents)

			$.post("<?php echo esc_html_e(admin_url('admin-ajax.php')); ?>", {'action': 'richpanel_chunk_sync', 'chunk_page': chunk_page}, function(response) {

				const new_chunk_page = chunk_page + 1;
				if(new_chunk_page <= chunk_pages){
					setTimeout(function(){
						sync_chunk(new_chunk_page);
					}, 900);
				}else{
					resetSyncButtons()
					updateSyncSucessMessgae()
				}

			});

		}

		sync_chunk(0);

	<?php elseif ($this->s_importing) : ?>
		disableSyncButtons()

		const chunk_pages = <?php echo esc_html_e($this->chunk_pages_subscription); ?>;
		let chunk_percentage = 100;
		if(chunk_pages > 0){
			chunk_percentage = (100 / chunk_pages);
		}
		const sync_chunk = function(chunk_page){
			let progress_percents = Math.round(chunk_page * chunk_percentage);
			setSubscriptionSyncProgress(progress_percents > 100 ? 100 : progress_percents)

			$.post("<?php echo esc_html_e(admin_url('admin-ajax.php')); ?>", {'action': 'richpanel_subscriptions_sync', 'chunk_page': chunk_page}, function(response) {

				const new_chunk_page = chunk_page + 1;
				if(new_chunk_page <= chunk_pages){
					setTimeout(function(){
						sync_chunk(new_chunk_page);
					}, 900);
				}else{
					resetSyncButtons()
					updateSyncSucessMessgae()
				}

			});

		}

		sync_chunk(0);
	<?php else : ?>
		resetSyncButtons()
	<?php endif; ?>

</script>
