<?php include(DIR_FS_CATALOG_MODULES . get_module_directory('sidebar/news.php')); ?>
<?php if (count($newsSidebarList) > 0) {?>
<div class="block block-news">
	<div class="block-title">
        <strong><span><?php echo __('News'); ?></span></strong>
    </div>
    <div class="block-content">
    	<ul>
    		<?php foreach ($newsSidebarList as $val) { ?>
    		<li><a href="<?php echo href_link(FILENAME_NEWS_INFO, 'nID=' . $val['news_id']); ?>" rel="external nofollow"><?php echo $val['name']; ?></a></li>
    		<?php } ?>
		</ul>
    </div>
</div>
<?php } ?>
