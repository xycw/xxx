<?php include(DIR_FS_CATALOG_MODULES . get_module_directory('product_prev_next.php')); ?>
<div class="product-switch">
	<p class="show-num"><strong><?php echo $productPrevNextList['current'];?></strong> / <?php echo $productPrevNextList['count'];?></p>
	<div class="action-bar">
		<a class="btn btn-default" href="<?php echo href_link(FILENAME_PRODUCT, 'pID=' . $productPrevNextList['prev']); ?>"><i class="iconfont">&#xe69a;</i></a>
		<a class="btn btn-default" href="<?php echo href_link(FILENAME_CATEGORY, 'cID=' . $productInfo['master_category_id']); ?>"><?php echo __('Product List'); ?></a>
		<a class="btn btn-default" href="<?php echo href_link(FILENAME_PRODUCT, 'pID=' . $productPrevNextList['next']); ?>"><i class="iconfont">&#xe69b;</i></a>
	</div>
</div>