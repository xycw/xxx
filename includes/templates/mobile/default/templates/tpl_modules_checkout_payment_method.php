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
			var shippingPaymentMethodJSON = <?php echo json_encode($shippingPaymentMethodJSON); ?>;
			$("input[name='shipping_method']").click(function(){
				var shipping_method_id = $(this).val(),
					payment_method_id = $('input[name="payment_method"]:checked').val();
				if(typeof(payment_method_id) == "undefined") payment_method_id = 0;
				order_review_value(shipping_method_id,payment_method_id);
			});
			$('input[name="payment_method"]').change(function(){
				var id = $('input[name="payment_method"]:checked').attr('id'),
					shipping_method_id = $('input[name="shipping_method"]:checked').val(),
					payment_method_id = $('input[name="payment_method"]:checked').val();
				if(typeof(shipping_method_id) == "undefined") shipping_method_id = 0;
				order_review_value(shipping_method_id,payment_method_id);
				$('.inside-list').hide();
				$('#'+id+'inside').show();
			})
			function order_review_value(shipping_method_id,payment_method_id){
				$('#grand_total').html(shippingPaymentMethodJSON[shipping_method_id][payment_method_id].order_total);
				$('#order_review_shipping_method_fee').html(shippingPaymentMethodJSON[shipping_method_id][payment_method_id].shipping_method_fee);
				$('#order_review_shipping_method_insurance_fee').html(shippingPaymentMethodJSON[shipping_method_id][payment_method_id].shipping_method_insurance_fee);
				$('#order_review_payment_method_fee').html(shippingPaymentMethodJSON[shipping_method_id][payment_method_id].payment_method_fee);
				$('#order_review_grand_total').html(shippingPaymentMethodJSON[shipping_method_id][payment_method_id].order_total);
			}
		</script>
	<?php } else { ?>
		<p class="error-msg"><?php echo __('Not Available At This Time'); ?></p>
	<?php } ?>
</div>
