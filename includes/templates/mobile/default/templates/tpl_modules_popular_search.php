<?php include(DIR_FS_CATALOG_MODULES . get_module_directory('popular_search.php')); ?>
<?php if (count($popularSearchList)>0) { ?>
<div class="popular-search">
	<div class="page-title">
		<h2 class="subtitle"><?php echo __('Popular Searches'); ?></h2>
	</div>
	<?php foreach ($popularSearchList as $_popular) { ?>
 	<a href="<?php echo href_link(FILENAME_SEARCH, 'q=' . urlencode($_popular['search'])); ?>"><?php echo $_popular['search']; ?></a>, 
	<?php } ?>
</div>
<?php } ?>
