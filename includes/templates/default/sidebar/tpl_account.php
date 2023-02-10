<?php include(DIR_FS_CATALOG_MODULES . get_module_directory('sidebar/account.php')); ?>
<?php if (count($accountSidebarList) > 0) {?>
<div class="block block-account">
	<div class="block-title">
        <strong><span><?php echo __('My Account'); ?></span></strong>
    </div>
    <div class="block-content">
    	<ul>
    		<?php foreach ($accountSidebarList as $_account) { ?>
    			<li><?php echo $_account; ?></li>
    		<?php } ?>
		</ul>
    </div>
</div>
<?php } ?>
