<?php include(DIR_FS_CATALOG_MODULES . get_module_directory('shipping_method.php')); ?>
<div class="step-title">
	<span class="icon"><i class="iconfont">&#xe92c;</i></span>
	<h2><?php echo __('Shipping Method'); ?></h2>
</div>
<div class="step" id="checkout-step-shipping_method">
<?php if (count($shippingMethodList) > 0) { ?>
	<?php foreach ($shippingMethodList as $key => $val) { ?>
		<div class="radio">
			<label for="<?php echo $val['code']; ?>">
				<input type="radio" class="radio" title="<?php echo $val['name']; ?>" value="<?php echo $key; ?>" name="shipping_method"<?php echo $val['is_default']? ' checked="checked"':''; ?> id="<?php echo $val['code']; ?>" />
				<?php echo $val['name']; ?> <span class="price"><?php echo $currencies->display_price($val['fee']); ?></span>
			</label>
			<p><?php echo $val['description']; ?></p>
		</div>
	<?php } ?>
<script type="text/javascript"><!--
$("input[name='shipping_method']").click(function(){
	var shippingMethodJSON = <?php echo json_encode($shippingMethodJSON); ?>;
	$('#shipping_method_fee').html(shippingMethodJSON[$(this).val()].fee);
	$('#grand_total').html(shippingMethodJSON[$(this).val()].total);
	$('#order_review_shipping_method_fee').html(shippingMethodJSON[$(this).val()].fee);
	$('#order_review_shipping_method_insurance_fee').html(shippingMethodJSON[$(this).val()].insurance_fee);
	$('#order_review_grand_total').html(shippingMethodJSON[$(this).val()].total);
	
});
//--></script>
<?php } else { ?>
	<p class="error-msg"><?php echo __('Not Available At This Time'); ?></p>
<?php } ?>
</div>
