<?php if (PRODUCT_LIST_SHOW_FILTER==1&&isset($productFilter)&&($productFilterCurrentCount > 0||$productFilterListCount > 0)) { ?>
<div class="filter">
	<?php if ($productFilterCurrentCount > 0) { ?>
	<dl class="currently">
		<dt><?php echo __('Currently Shopping by'); ?>:</dt>
		<dd>
			<?php foreach ($productFilter as $key => $val) { ?>
			<?php if (not_null($val['current'])) { ?>
    			<a title="<?php echo __('Remove This Item'); ?>" href="<?php echo href_link($current_page, get_all_get_params(array('page', 'limit', 'mode', 'sort', $key))); ?>" rel="external nofollow">
    			<span><span><strong><?php echo $val['title']; ?>:</strong> <?php echo $val['current']; ?></span></span>
    			</a>
				<?php } ?>
			<?php }?>
			<a href="<?php echo href_link($current_page, get_all_get_params(array_merge(array('page', 'limit', 'mode', 'sort'), array_keys($productFilter)))); ?>" rel="external nofollow"><?php echo __('Clear All'); ?></a>
		</dd>
	</dl>
	<?php } ?>
	
	<?php if ($productFilterListCount > 0) { ?>
	<?php foreach ($productFilter as $key => $val) { ?>
	<?php if (count($val['list'])>0) { ?>
	<dl>
		<dt><?php echo $val['title']; ?>:</dt>
		<dd>
			<?php foreach ($val['list'] as $_key => $_val) { ?>
			<a href="<?php echo href_link($current_page, get_all_get_params(array('page', 'limit', 'mode', 'sort', $key)) . $key . '=' . urlencode($_key)); ?>" rel="external nofollow"><?php echo $_key; ?> <span>(<?php echo $_val; ?>)</span></a>
			<?php } ?>
		</dd>
	</dl>
	<?php } ?>
	<?php } ?>
	<?php } ?>
</div>
<?php } ?>
