<?php include(DIR_FS_CATALOG_MODULES . get_module_directory('sidebar/cms_page.php')); ?>
<?php if (count($cmsPageSidebarList) > 0) {?>
<div class="block block-cms-page">
	<div class="block-title">
        <strong><span><?php echo __('Information'); ?></span></strong>
    </div>
    <div class="block-content">
    	<ul>
    		<?php foreach ($cmsPageSidebarList as $val) { ?>
    		<?php if(isset($_GET['cpID'])&&$_GET['cpID']==$val['cms_page_id']) { ?>
    		<li><strong><?php echo __($val['name']); ?></strong></li>
    		<?php } else {?>
    		<li><a href="<?php echo href_link(FILENAME_CMS_PAGE, 'cpID=' . $val['cms_page_id']); ?>" rel="external nofollow"><?php echo __($val['name']); ?></a></li>
    		<?php } ?>
    		<?php } ?>
		</ul>
    </div>
</div>
<?php } ?>
