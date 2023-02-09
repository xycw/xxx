<?php if (count($productInfo['product_group'])>1) { ?>
<div id="product-group-wrapper" class="product-colors form-group">
	<ul class="color-list">
	<?php foreach ($productInfo['product_group'] as $product_group) {?>
		<li class="<?php echo ($product_group['product_id'] == $productInfo['product_id']) ? 'active' : '';?>">
			<a href="<?php echo ($product_group['product_id'] == $productInfo['product_id']) ? 'javascript:;' : href_link(FILENAME_PRODUCT, 'pID=' . $product_group['product_id']);?>"><img width="<?php echo COLOR_IMAGE_WIDTH; ?>" height="<?php echo COLOR_IMAGE_HEIGHT; ?>" src="<?php echo get_small_image($product_group['image'], COLOR_IMAGE_WIDTH, COLOR_IMAGE_HEIGHT); ?>" /></a>
		</li>
	<?php } ?>
	</ul>
</div>
<?php } ?>
