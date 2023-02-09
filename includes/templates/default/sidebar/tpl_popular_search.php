<?php include(DIR_FS_CATALOG_MODULES . get_module_directory('sidebar/popular_search.php')); ?>
<?php if (count($popularSearchSidebarList)>0) { ?>
<div class="block block-popular-search">
	<div class="block-title">
        <strong><span><?php echo __('Popular Searches'); ?></span></strong>
    </div>
    <div class="block-content">
	<?php foreach ($popularSearchSidebarList as $_popular) { ?>
		<a href="<?php echo href_link(FILENAME_SEARCH, 'q=' . urlencode($_popular['search'])); ?>"><?php echo $_popular['search']; ?></a>, 
	<?php } ?>
    </div>
</div>
<?php } ?>
