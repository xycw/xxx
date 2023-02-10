<?php include(DIR_FS_CATALOG_MODULES . get_module_directory('category_list.php')); ?>
<?php if (isset($indexCategoryList) && !empty($indexCategoryList)) { ?>
<div class="banner-block2 col5-set">
	<?php $i = 1; ?>
	<?php foreach ($indexCategoryList as $val) { ?>
	<div class="col-<?php echo $i ?>">
		<a class="title" href="<?php echo href_link(FILENAME_CATEGORY, 'cID=' . $val['category_id']); ?>"></a>
		<ul>
			<?php foreach ($val['children'] as $children) { ?>
			<li><a title="<?php echo __($children['nameAlt']); ?>" href="<?php echo href_link(FILENAME_CATEGORY, 'cID=' . $children['category_id']); ?>" rel="external nofollow"><?php echo trunc_string($children['name'], 30); ?></a></li>
			<?php } ?>
			<li class="last"><a title="<?php echo __('All Teams'); ?>" href="<?php echo href_link(FILENAME_CATEGORY, 'cID=' . $val['category_id']); ?>" rel="external nofollow"><?php echo __('All Teams'); ?></a></li>
		</ul>
	</div>
	<?php $i++; ?>
	<?php } ?>
	<div class="clearer"></div>
</div>
<?php } ?>
