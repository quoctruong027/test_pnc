<?php
/* error_reporting(0);
ini_set('display_errors', 0);

$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
require_once( $parse_uri[0] . 'wp-load.php' ); */

global $wcuf_option_model;
$crop_area_options = $wcuf_option_model->get_crop_area_options();

/*** set the content type header ***/
/* header("Content-type: text/css"); */
/* if($crop_area_options['clip_height'] > $crop_area_options['clip_width']) //$_GET
{
	$ratio = $crop_area_options['clip_width']/$crop_area_options['clip_height'];
	$crop_area_options['clip_height'] = round($crop_area_options['crop_area_height']*2/3);
	$crop_area_options['clip_width'] = round($crop_area_options['clip_height']*$ratio);
} 
else
{
	$ratio = $crop_area_options['clip_height']/$crop_area_options['clip_width'];
	$crop_area_options['clip_width'] = round($crop_area_options['crop_area_width']*2/3);
	$crop_area_options['clip_height'] = round($crop_area_options['clip_height']*$ratio);
}  */	
?>

.woocommerce.single.single-product .entry-summary form button.button.wcuf_zoomin_button,
button.button.wcuf_zoomin_button,
.woocommerce.single.single-product .entry-summary form button.button.wcuf_zoomout_button,
button.button.wcuf_zoomout_button,
.woocommerce.single.single-product .entry-summary form button.button.wcuf_rotate_left,
button.button.wcuf_rotate_left,
.woocommerce.single.single-product .entry-summary form button.button.wcuf_rotate_right,
button.button.wcuf_rotate_right
{
	float: left;
	width: 48%;
	margin-top: 2px;
    margin-right: 1px;
	overflow:hidden;
}
.woocommerce.single.single-product .entry-summary form button.button.wcuf_crop_upload_button,
button.button.wcuf_crop_upload_button,
.woocommerce.single.single-product .entry-summary form button.button.wcuf_crop_cancel_button,
button.button.wcuf_crop_cancel_button
{
	float: left;
	width: 97%;
	margin-top: 2px;
	margin-right: 1px;
}
.wcuf_crop_image_box
{
position: relative;
/* height: <?php echo urldecode($crop_area_options['crop_area_height']);?>px; */ /*old*/
/* width: <?php echo urldecode($crop_area_options['crop_area_width']);?>px;*/ /*old*/
width: 100%;
height: 100%;
border:1px solid #aaa;
background: #fff;
overflow: visible;
background-repeat: no-repeat; 
cursor:move;
}

.wcuf_crop_image_box .wcuf_crop_thumb_box
{
position: absolute;
top: 50%;
left: 50%;
width: 202px;
height: 202px;
margin-top: -101px;
margin-left: -101px;
box-sizing: border-box;
border: 1px solid rgb(102, 102, 102);
box-shadow: 0 0 0 1000px rgba(0, 0, 0, 0.5);
background: none repeat scroll 0% 0% transparent;
}
.wcuf_crop_thumb_box
{
	pointer-events:none;
}

.wcuf_crop_image_box .wcuf_crop_thumb_spinner
{
	position: absolute;
	top: 0;
	left: 0;
	bottom: 0;
	right: 0;
	text-align: center;
	line-height: 400px;
	background: rgba(0,0,0,0.7);
}
.wcuf_crop_container
{
	display:none;
	width: <?php echo urldecode($crop_area_options['crop_area_width']);?>px; 
	height: <?php echo urldecode($crop_area_options['crop_area_height']);?>px;
	/* max-width: 600px; */
}
.wcuf_crop_container_margin_bottom
{
	margin-bottom: 200px;
}
.wcuf_crop_container_actions
{
	display: block;
	clear: both;
	overflow: visible;
	margin-top: 50px;
}
.croppie-container .cr-slider-wrap
{
	width:100%;
}
input.cr-slider
{
	background: transparent !important;
}
