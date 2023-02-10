<?php if (MOBILE_PRODUCT_SHOW_COLOR==1 && count($productInfo['color'])>1) { ?>
<div id="product-color-wrapper" class="product-colors">
	<dl>
		<dt>
			<label class="required"><em>*</em><?php echo __('Color'); ?></label>
			<span class="required">* <?php echo __('Required Fields'); ?></span>
		</dt>
		<dd>
			<div class="input-box">
				<ul class="color-list">
				<?php foreach ($productInfo['color'] as $_color) {?>
					<li<?php if ($_color['product_id']==$productInfo['product_id']) { ?> class="active"<?php } ?>>
						<a href="<?php echo href_link(FILENAME_PRODUCT, 'pID=' . $_color['product_id']); ?>"><img width="<?php echo MOBILE_COLOR_IMAGE_WIDTH; ?>" height="<?php echo MOBILE_COLOR_IMAGE_HEIGHT; ?>" src="<?php echo get_small_image($_color['image'], MOBILE_COLOR_IMAGE_WIDTH, MOBILE_COLOR_IMAGE_HEIGHT); ?>" /></a>
					</li>
				<?php } ?>
				</ul>
			</div>
		</dd>
	</dl>
</div>
<?php } ?>
