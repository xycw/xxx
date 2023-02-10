<?php include(DIR_FS_CATALOG_MODULES . get_module_directory('product_prev_next.php')); ?>
<div class="product-switch">
	<p class="show-num"><?php echo __('Product'); ?> <?php echo $productPrevNextList['current'];?>/<?php echo $productPrevNextList['count'];?></p>
	<div class="action-bar">
		<a href="<?php echo href_link(FILENAME_PRODUCT, 'pID=' . $productPrevNextList['prev']); ?>"><?php echo __('Previous'); ?></a>
		<a href="<?php echo href_link(FILENAME_CATEGORY, 'cID=' . $productInfo['master_category_id']); ?>"><?php echo __('Product List'); ?></a>
		<a href="<?php echo href_link(FILENAME_PRODUCT, 'pID=' . $productPrevNextList['next']); ?>"><?php echo __('Next'); ?></a>
	</div>
</div>