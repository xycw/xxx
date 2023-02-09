<?php include(DIR_FS_CATALOG_MODULES . get_module_directory('payment_method.php')); ?>
<?php require_once(DIR_FS_CATALOG_CLASSES . 'payment_method.php'); ?>
<div class="step-title">
	<span class="number">4</span>
	<h2><?php echo __('Payment Method'); ?></h2>
</div>
<div class="step" id="checkout-step-payment_method">
	<?php if (count($paymentMethodList) > 0) { ?>
		<ul class="form-list" id="payment_method-list">
			<?php foreach ($paymentMethodList as $key => $val) { ?>
				<li class="control">
					<input type="radio" class="radio" title="<?php echo $val['name']; ?>" value="<?php echo $key; ?>" id="<?php echo $val['code']; ?>" name="payment_method"<?php echo $val['is_default']? ' checked="checked"':''; ?> />
					<label for="<?php echo $val['code']; ?>"><?php echo $val['name']; ?></label>
					<p onclick="$('#<?php echo $val['code']; ?>').click();"><?php echo $val['description']; ?></p>
				</li>
				<?php if ($val['is_inside'] == 1) { ?>
				<li id="<?php echo $val['code']; ?>inside" class="inside-list" style="<?php echo $val['is_default']? '':'display: none'; ?>">
					<?php $payment_method = new payment_method($val['code']); ?>
					<?php echo $payment_method->before(); ?>
				</li>
				<?php } ?>
			<?php } ?>
		</ul>
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
