<?php if (PRODUCT_SHOW_COLOR==1 && count($productInfo['color'])>1) { ?>
<div id="product-color-wrapper" class="product-colors form-group">
	<label class="required"><em>*</em><?php echo __('Color'); ?></label>
	<ul class="color-list">
	<?php foreach ($productInfo['color'] as $_color) {?>
		<li<?php if ($_color['product_id']==$productInfo['product_id']) { ?> class="active"<?php } ?>>
			<a href="<?php echo href_link(FILENAME_PRODUCT, 'pID=' . $_color['product_id']); ?>"><img width="<?php echo COLOR_IMAGE_WIDTH; ?>" height="<?php echo COLOR_IMAGE_HEIGHT; ?>" src="<?php echo get_small_image($_color['image'], COLOR_IMAGE_WIDTH, COLOR_IMAGE_HEIGHT); ?>" /></a>
		</li>
	<?php } ?>
	</ul>
	<p class="required">* <?php echo __('Required Fields'); ?></p>
</div>
<?php } ?>
