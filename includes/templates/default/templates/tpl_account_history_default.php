<div class="my-account">
	<div class="page-title">
        <h1><?php echo __('My Orders'); ?></h1>
    </div>
    <?php if (count($orderList)>0) { ?>
    <?php require($template->get_template_dir('tpl_modules_pager.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_pager.php'); ?>
	<table class="data-table" id="my-orders-table">
    <colgroup>
        <col width="1" />
        <col width="1" />
        <col />
        <col width="1" />
        <col width="1" />
        <col width="1" />
    </colgroup>
    <thead>
        <tr>
            <th><?php echo __('Order #'); ?></th>
            <th><?php echo __('Date'); ?></th>
            <th><?php echo __('Ship To'); ?></th>
            <th><span class="nobr"><?php echo __('Order Total'); ?></span></th>
            <th><?php echo __('Status'); ?></th>
            <th>&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($orderList as $_order) { ?>
            <tr>
                <td><span class="nobr"><?php echo put_orderNO($_order['order_id']); ?></span></td>
                <td><?php echo date_short($_order['date_added']); ?></td>
                <td><?php echo $_order['shipping_country']; ?>, <?php echo $_order['shipping_name']; ?></td>
                <td><?php echo $_order['order_total']; ?></td>
                <td><?php echo __($_order['order_status_name']); ?></td>
                <td><span class="nobr"><a href="<?php echo href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'oID='.$_order['order_id'], 'SSL'); ?>"><?php echo __('View Order'); ?></a></span></td>
            </tr>
        <?php } ?>
    </tbody>
    </table>
	<script type="text/javascript">decorateTable($('#my-orders-table'));</script>
	<?php require($template->get_template_dir('tpl_modules_pager.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_pager.php'); ?>
	<?php } else { ?>
		<p><?php echo __('You have placed no orders.'); ?></p>
	<?php }?>
	<div class="buttons-set">
    	<p class="back-link"><a href="<?php echo href_link(FILENAME_ACCOUNT, '', 'SSL'); ?>"><small>« </small><?php echo __('Back'); ?></a></p>
	</div>
</div>
