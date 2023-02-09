<div class="toolbar">
	<?php require($template->get_template_dir('tpl_modules_pager.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_pager.php'); ?>
	<div class="sorter">
		<?php if (count($toolbarMode['available']) > 0) { ?>
		<p class="view-mode">
			<label><?php echo __('View as'); ?>:</label>
			<?php foreach ($toolbarMode['available'] as $key => $val) { ?>
            <?php if ($key=='grid') { ?>
            <?php if ($val==true) { ?>
            <strong class="grid" title="<?php echo __('Grid'); ?>"><?php echo __('Grid'); ?></strong>
            <?php } else { ?>
            <a class="grid" title="<?php echo __('Grid'); ?>" href="<?php echo href_link($current_page, get_all_get_params(array('page', 'limit', 'mode')) . 'mode=grid'); ?>"><?php echo __('Grid'); ?></a>
            <?php } ?>
            <?php } elseif ($key=='list') { ?>
            <?php if ($val==true) { ?>
            <strong class="list" title="<?php echo __('List'); ?>"><?php echo __('List'); ?></strong>
            <?php } else { ?>
            <a class="list" title="<?php echo __('List'); ?>" href="<?php echo href_link($current_page, get_all_get_params(array('page', 'limit', 'mode')) . 'mode=list'); ?>"><?php echo __('List'); ?></a>
            <?php } ?>
            <?php } ?>
			<?php } ?>
		</p>
		<?php } ?>
		<?php if (count($toolbarSort['available']) > 0) { ?>
		<div class="sort-by">
			<label><?php echo __('Sort By'); ?></label>  
            <select onchange="setLocation(this.value);">
            <?php foreach ($toolbarSort['available'] as $key => $val) { ?>
            	<option<?php if ($val['selected']) { ?> selected="selected"<?php } ?> value="<?php echo href_link($current_page, get_all_get_params(array('page', 'sort')) . 'sort=' . $key); ?>"><?php echo $val['name']; ?></option>
            <?php } ?>
			</select>
        </div>
        <?php } ?>
    </div>
</div>
