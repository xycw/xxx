<?php if (PRODUCT_LIST_SHOW_FILTER==2&&isset($productFilter)&&($productFilterCurrentCount > 0||$productFilterListCount > 0)) { ?>
<div class="block block-filter">
	<div class="block-title">
        <strong><span><?php echo __('Shop By'); ?></span></strong>
    </div>
    <div class="block-content">
    	<?php if ($productFilterCurrentCount>0) { ?>
    	<div class="currently">
	    	<p class="block-subtitle"><?php echo __('Currently Shopping by'); ?>:</p>
	    	<ol>
	    		<?php foreach ($productFilter as $key => $val) { ?>
	    		<?php if (not_null($val['current'])) { ?>
	    		<li>
	    			<a class="btn-remove" title="<?php echo __('Remove This Item'); ?>" href="<?php echo href_link($current_page, get_all_get_params(array('page', 'limit', 'mode', 'sort', $key))); ?>" rel="external nofollow"><?php echo __('Remove This Item'); ?></a>
					<span class="label"><?php echo $val['title']; ?>:</span> <span class="value"><?php echo $val['current']; ?></span>
				</li>
				<?php } ?>
	    		<?php } ?>
	    	</ol>
	    	<div class="actions">
	    		<a href="<?php echo href_link($current_page, get_all_get_params(array_merge(array('page', 'limit', 'mode', 'sort'), array_keys($productFilter)))); ?>" rel="external nofollow"><?php echo __('Clear All'); ?></a>
	    	</div>
    	</div>
	    <?php } ?>
	    
	    <?php if ($productFilterListCount>0) { ?>
    	<p class="block-subtitle"><?php echo __('Shopping Options'); ?></p>
	    <dl>
		    <?php foreach ($productFilter as $key => $val) { ?>
			<?php if (count($val['list'])>0) { ?>
			<dt>
				<?php echo $val['title']; ?>
			</dt>
	    	<dd>
	    		<ol>
			        <?php foreach ($val['list'] as $_key => $_val) { ?>
			        <li><a href="<?php echo href_link($current_page, get_all_get_params(array('page', 'limit', 'mode', 'sort', $key)) . $key . '=' . urlencode($_key)); ?>" rel="external nofollow"><?php echo $_key; ?></a> <span>(<?php echo $_val; ?>)</span></li>
			        <?php } ?>
			    </ol>
	    	</dd>
			<?php } ?>
			<?php } ?>
	    </dl>
	    <?php } ?>
    </div>
</div>
<?php } ?>
