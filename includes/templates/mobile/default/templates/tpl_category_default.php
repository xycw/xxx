<div class="page-title category-title">
    <h1><?php echo $categoryInfo['name']; ?></h1>
</div>
<?php if (not_null($categoryInfo['banner_image']) && MOBILE_CATEGORY_IMAGE_SHOW==1) { ?>
<p class="category-image">
	<img alt="<?php echo $categoryInfo['nameAlt']; ?>" src="<?php echo get_small_image($categoryInfo['banner_image'], MOBILE_CATEGORY_IMAGE_WIDTH, MOBILE_CATEGORY_IMAGE_HEIGHT); ?>">
</p>
<?php } ?>
<?php if (not_null($categoryInfo['description'])) { ?>
<div class="category-description std">
	<?php echo $categoryInfo['description']; ?>
</div>
<?php } ?>
<?php if ($subcategoryListCount = count($subcategoryList)) { ?>
<div class="subcategory">
	<ul>
		<?php foreach ($subcategoryList as $_category) { ?>
		<li>
			<?php if (!empty($_category['image'])) { ?>
			<img width="<?php echo MOBILE_SUBCATEGORY_IMAGE_WIDTH ?>" height="<?php echo MOBILE_SUBCATEGORY_IMAGE_HEIGHT ?>" src="<?php echo get_small_image($_category['image'], MOBILE_SUBCATEGORY_IMAGE_WIDTH, MOBILE_SUBCATEGORY_IMAGE_HEIGHT); ?>" alt="<?php echo $_category['nameAlt']; ?>" />
			<?php } ?>
			<a title="<?php echo $_category['nameAlt']?>" href="<?php echo href_link(FILENAME_CATEGORY, 'cID=' . $_category['category_id'])?>"><?php echo $_category['name']?></a>
		</li>
		<?php } ?>
	</ul>
	<script type="text/javascript">decorateList($('ul.category-tree'));</script>
</div>
<?php } ?>
<?php if (not_null($productListQuery)) { ?>
<?php require($template->get_template_dir('tpl_modules_product_list.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_product_list.php'); ?>
<?php } ?>