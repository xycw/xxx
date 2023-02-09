<div class="my-account">
	<div class="account-top">
		<div class="page-title">
			<h1><?php echo __('My Orders') ?><a class="f-right account-more visible-xs"><i class="iconfont">&#xe609;</i></a></h1>
		</div>
		<div class="account-menu">
			<ul>
				<li><a href="<?php echo href_link(FILENAME_ACCOUNT, '', 'SSL') ?>"><?php echo __('Account') ?></a></li>
				<li><a href="<?php echo href_link(FILENAME_ACCOUNT_EDIT, '', 'SSL') ?>"><?php echo __('Account Information') ?></a></li>
				<li><a href="<?php echo href_link(FILENAME_ADDRESS, '', 'SSL') ?>"><?php echo __('Address Book') ?></a></li>
				<li><a href="<?php echo href_link(FILENAME_ACCOUNT_REVIEW, '', 'SSL') ?>"><?php echo __('My Product Reviews') ?></a></li>
				<li><a href="<?php echo href_link(FILENAME_ACCOUNT_NEWSLETTER, '', 'SSL') ?>"><?php echo __('Newsletter Subscriptions') ?></a></li>
			</ul>
		</div>
	</div>
	<div class="my-account-content">
		<?php if (count($orderList)>0) { ?>
			<?php require($template->get_template_dir('tpl_modules_pager.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_pager.php'); ?>
			<table class="table table-bordered data-table hidden-xs" id="my-orders-table">
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
						<td><?php echo put_orderNO($_order['order_id']); ?></td>
						<td><?php echo date_short($_order['date_added']); ?></td>
						<td><?php echo $_order['shipping_country']; ?>, <?php echo $_order['shipping_name']; ?></td>
						<td><?php echo $_order['order_total']; ?></td>
						<td><?php echo __($_order['order_status_name']); ?></td>
						<td><span class="nobr"><a href="<?php echo href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'oID='.$_order['order_id'], 'SSL'); ?>"><?php echo __('View Order'); ?></a></span></td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
			<table class="table table-bordered data-table visible-xs">
				<colgroup>
					<col width="1" />
					<col />
					<col width="1" />
				</colgroup>
				<tbody>
				<?php foreach ($orderList as $_order) { ?>
					<tr>
						<td>
							<span class="nobr"><?php echo put_orderNO($_order['order_id']); ?></span><br>
							<span class="nobr"><a href="<?php echo href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'oID='.$_order['order_id'], 'SSL'); ?>"><?php echo __('View Order'); ?></a></span>
						</td>
						<td>
							<span class="a-thead"><?php echo __('Ship To'); ?> :</span>
							<?php echo $_order['shipping_country']; ?>, <?php echo $_order['shipping_name']; ?><br>
							<span class="a-thead"><?php echo __('Date'); ?> :</span>
							<?php echo date_short($_order['date_added']); ?><br>
							<span class="a-thead"><?php echo __('Status'); ?> :</span>
							<?php echo __($_order['order_status_name']); ?><br>
							<span class="a-thead"><?php echo __('Order Total'); ?> :</span>
							<?php echo $_order['order_total']; ?>
						</td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
			<script type="text/javascript">decorateTable($('#my-orders-table'));</script>
		<?php require($template->get_template_dir('tpl_modules_pager.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_pager.php'); ?>
		<?php } else { ?>
			<p><?php echo __('You have placed no orders.'); ?></p>
		<?php }?>
	</div>
	<div class="buttons-set">
    	<p class="back-link"><a href="<?php echo href_link(FILENAME_ACCOUNT, '', 'SSL'); ?>"><small>« </small><?php echo __('Back'); ?></a></p>
	</div>
</div>
<script type="text/javascript">
	$(function () {
		$('.account-more').click(function(){
			if ($('.account-menu').is(':hidden')) {
				$('.account-menu').slideDown();
				$(this).children("i").html('&#xe643;');
			} else {
				$('.account-menu').slideUp();
				$(this).children("i").html('&#xe609;');
			}
		});
	})
</script>
