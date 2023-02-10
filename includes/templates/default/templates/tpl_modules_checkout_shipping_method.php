<?php include(DIR_FS_CATALOG_MODULES . get_module_directory('shipping_method.php')); ?>
<div class="step-title">
	<span class="number">3</span>
	<h2><?php echo __('Shipping Method'); ?></h2>
</div>
<div class="step" id="checkout-step-shipping_method">
<?php if (count($shippingMethodList) > 0) { ?>
	<ul class="form-list" id="shipping_method-list">
	<?php foreach ($shippingMethodList as $key => $val) { ?>
		<li class="control">
			<input type="radio" class="radio" title="<?php echo $val['name']; ?>" value="<?php echo $key; ?>" name="shipping_method"<?php echo $val['is_default']? ' checked="checked"':''; ?> id="<?php echo $val['code']; ?>" />
			<label for="<?php echo $val['code']; ?>"><?php echo $val['name']; ?> <span class="price"><?php echo $currencies->display_price($val['fee']); ?></span></label>
			<p><?php echo $val['description']; ?></p>
		</li>
	<?php } ?>
	</ul>
<?php } else { ?>
	<p class="error-msg"><?php echo __('Not Available At This Time'); ?></p>
<?php } ?>
</div>
