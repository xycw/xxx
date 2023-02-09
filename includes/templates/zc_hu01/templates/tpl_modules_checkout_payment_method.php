<?php include(DIR_FS_CATALOG_MODULES . get_module_directory('payment_method.php')); ?>
<?php require_once(DIR_FS_CATALOG_CLASSES . 'payment_method.php'); ?>
<div class="step-title">
	<span class="icon"><i class="iconfont">&#xe656;</i></span>
	<h2><?php echo __('Payment Method'); ?></h2>
</div>
<div class="step" id="checkout-step-payment_method">
<?php if (count($paymentMethodList) > 0) { ?>
	<?php foreach ($paymentMethodList as $key => $val) { ?>
		<div class="radio">
			<label for="<?php echo $val['code']; ?>">
				<input type="radio" class="radio" title="<?php echo $val['name']; ?>" value="<?php echo $key; ?>" id="<?php echo $val['code']; ?>" name="payment_method"<?php echo $val['is_default']? ' checked="checked"':''; ?> />
				<?php echo $val['name']; ?>
			</label>
			<p onclick="$('#<?php echo $val['code']; ?>').click();"><?php echo $val['description']; ?></p>
		</div>
		<?php if ($val['is_inside'] == 1) { ?>
		<div id="<?php echo $val['code']; ?>inside" class="inside-list" style="<?php echo $val['is_default']? '':'display: none'; ?>">
			<?php $payment_method = new payment_method($val['code']); ?>
			<?php echo $payment_method->before(); ?>
		</div>
		<?php } ?>
	<?php } ?>
	<script type="text/javascript">
		$('input[name="payment_method"]').change(function(){
			var id = $('input[name="payment_method"]:checked').attr('id');
			$('.inside-list').hide();
			$('#'+id+'inside').show();
		})
	</script>
<?php } else { ?>
	<p class="error-msg"><?php echo __('Not Available At This Time'); ?></p>
<?php } ?>
</div>
