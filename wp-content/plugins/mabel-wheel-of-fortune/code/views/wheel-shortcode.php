<?php
/** @var \MABEL_WOF\Code\Models\Wheel_Shortcode_VM $model */

if(!isset($model->wheel)) return;
?>

<div class="wof-wheel-standalone">
	<?php
		echo \MABEL_WOF\Core\Common\Html::view('wheel',$model->wheel);
	?>
</div>