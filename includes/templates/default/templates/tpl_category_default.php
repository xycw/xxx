<div class="page-title category-title">
    <h1><?php echo $categoryInfo['name']; ?></h1>
</div>
<?php if (not_null($categoryInfo['banner_image']) && CATEGORY_IMAGE_SHOW==1) { ?>
<p class="category-image">
	<img width="<?php echo CATEGORY_IMAGE_WIDTH; ?>" height="<?php echo CATEGORY_IMAGE_HEIGHT; ?>" alt="<?php echo $categoryInfo['nameAlt']; ?>" src="<?php echo get_small_image($categoryInfo['banner_image'], CATEGORY_IMAGE_WIDTH, CATEGORY_IMAGE_HEIGHT); ?>">
</p>
<?php } ?>
<?php if (not_null($categoryInfo['description'])) { ?>
<div class="category-description std">
	<?php echo $categoryInfo['description']; ?>
</div>
<?php } ?>
<?php if ($subcategoryListCount = count($subcategoryList)) { ?>
<div class="subcategory">
	<?php $column_count = CATEGORY_LIST_PER_ROW; ?>
	<?php $i=0; ?>
	<?php foreach ($subcategoryList as $_category) { ?>
	<?php if ($i++%$column_count==0) { ?>
	<ul class="category-list">
	<?php } ?>
		<li><a title="<?php echo $_category['nameAlt']?>" href="<?php echo href_link(FILENAME_CATEGORY, 'cID=' . $_category['category_id'])?>"><img width="<?php echo SUBCATEGORY_IMAGE_WIDTH; ?>" height="<?php echo SUBCATEGORY_IMAGE_HEIGHT; ?>" alt="<?php echo $_category['nameAlt']?>" src="<?php echo get_small_image($_category['image'], SUBCATEGORY_IMAGE_WIDTH, SUBCATEGORY_IMAGE_HEIGHT); ?>" /><br><?php echo $_category['name']?></a></li>
	<?php if ($i%$column_count==0 || $i==$subcategoryListCount) { ?>
	</ul>
	<?php } ?>
	<?php } ?>
	<script type="text/javascript">decorateList($('ul.category-list'));</script>
</div>
<?php } ?>
<?php require($template->get_template_dir('tpl_modules_category_bestsellers.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_category_bestsellers.php'); ?>
<?php if (not_null($productListQuery)) { ?>
<?php require($template->get_template_dir('tpl_modules_product_list.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_product_list.php'); ?>
<?php } ?>