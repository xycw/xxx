<?php if (isset($productInfo['additional_image'])) { ?>
<?php array_shift($productInfo['additional_image']); ?>
<?php if (count($productInfo['additional_image']) > 0) { ?>
<div class="more-views">
	<h2><?php echo __('More Views'); ?></h2>
	<ul>
		<?php foreach ($productInfo['additional_image'] as $_image) { ?>
			<li>
				<a href="<?php echo get_large_image($_image, POPUP_IMAGE_WIDTH, POPUP_IMAGE_HEIGHT); ?>" data-lightbox="lightbox-images">
					<img width="<?php echo ADDITIONAL_IMAGE_WIDTH; ?>" height="<?php echo ADDITIONAL_IMAGE_HEIGHT; ?>" src="<?php echo get_small_image($_image, ADDITIONAL_IMAGE_WIDTH, ADDITIONAL_IMAGE_HEIGHT); ?>" />
				</a>
			</li>
		<?php } ?>
	</ul>
</div>
<?php } ?>
<?php } ?>