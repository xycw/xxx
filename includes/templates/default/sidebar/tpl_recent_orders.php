<?php include(DIR_FS_CATALOG_MODULES . get_module_directory('sidebar/recent_orders.php')); ?>
<?php if (count($recentOrdersSidebarList)>0) { ?>
<div class="block block-recent-orders">
	<div class="block-title">
        <strong><span><?php echo __('Recent Shopping'); ?></span></strong>
    </div>
    <div class="block-content">
    	<ol id="recent-orders-items" class="mini-recent-orders-list">
    		<?php foreach ($recentOrdersSidebarList as $_product) { ?>
    		<li class="item">
    			<a class="product-image" href="<?php echo href_link(FILENAME_PRODUCT, 'pID=' . $_product['product_id']); ?>" title="<?php echo $_product['nameAlt']; ?>"><?php echo $_product['name']; ?></a>
    			<p><?php echo __('Ship to') . ' ' . $_product['shipping_country']; ?></p>
    		</li>
    		<?php } ?>
    	</ol>
    	<script type="text/javascript">decorateList($('#recent-orders-items'))</script>
    </div>
</div>
<?php } ?>
