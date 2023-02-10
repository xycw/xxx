<?php include(DIR_FS_CATALOG_MODULES . get_module_directory('sidebar/subcategory.php')); ?>
<div class="block block-category">
	<div class="block-title">
        <strong><span><?php echo $sideberCategoryID==0?__('Category'):$category_tree->getCategoryName($sideberCategoryID); ?></span></strong>
    </div>
    <div class="block-content">
    	<?php echo $category_tree->buildTree($sideberCategoryID); ?>
    </div>
</div>
<script type="text/javascript">decorateList($('.block-category ul li'));</script>
