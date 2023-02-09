<div class="sorter<?php if (MOBILE_PRODUCT_LIST_SHOW_FILTER==1&&isset($productFilter)&&($productFilterCurrentCount > 0||$productFilterListCount > 0)) { ?> toolbar-filter<?php } ?>">
	<ul>
	<?php if (MOBILE_PRODUCT_LIST_SHOW_FILTER==1&&isset($productFilter)&&($productFilterCurrentCount > 0||$productFilterListCount > 0)) { ?>
 	<li>
        <a href="javascript:void(0)" class="btn btn-black btn-block" id="fixFilter"><i class="iconfont btn-icon">&#xe6aa;</i> <?php echo __('Filter') ?></a>
		<a href="javascript:void(0)" class="btn btn-black btn-filter" id="floatFilter"><?php echo __('Filter') ?></a>
	</li>
	<?php } ?>
	<?php if (count($toolbarSort['available']) > 0) { ?>
	<li class="sort-by">
		<select class="form-control" onchange="setLocation(this.value);">
			<option value=""><?php echo __('Sort By'); ?></option>
			<?php foreach ($toolbarSort['available'] as $key => $val) { ?>
				<option<?php if ($val['selected']) { ?> selected="selected"<?php } ?> value="<?php echo href_link($current_page, get_all_get_params(array('page', 'sort')) . 'sort=' . $key); ?>"><?php echo $val['name']; ?></option>
			<?php } ?>
		</select>
	</li>
	<?php } ?>
	</ul>
</div>