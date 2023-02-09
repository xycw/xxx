<div class="my-account">
	<div class="page-title">
        <h1><?php echo __('My Dashboard'); ?></h1>
    </div>
    <?php if ($message_stack->size('account') > 0) echo $message_stack->output('account'); ?>
    <div class="welcome-msg">
	    <p class="hello"><strong><?php __('Hello, %s!', $_SESSION['customer_firstname'] . ' ' . $_SESSION['customer_lastname']); ?></strong></p>
	    <p><?php __('From your My Account Dashboard you have the ability to view a snapshot of your recent account activity and update your account information. Select a link below to view or edit information.'); ?></p>
	</div>
	<?php if (count($orderList)>0) { ?>
	<div class="box-account box-recent">
	    <div class="box-head">
	        <h2><?php echo __('Recent Orders'); ?></h2>
	        <a href="<?php echo href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL'); ?>"><?php echo __('View All'); ?></a>
	    </div>
	    <table id="my-orders-table" class="data-table">
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
	</div>
	<?php } ?>
	<div class="box-account box-info">
        <div class="box-head">
            <h2><?php echo __('Account Information'); ?></h2>
        </div>
        <div class="col2-set">
		    <div class="col-1">
		        <div class="box">
		            <div class="box-title">
		                <h3><?php echo __('Contact Information'); ?></h3>
		                <a href="<?php echo href_link(FILENAME_ACCOUNT_EDIT, '', 'SSL'); ?>"><?php echo __('Edit'); ?></a>
		            </div>
		            <div class="box-content">
		                <p>
		                	<?php echo $_SESSION['customer_firstname'] . ' ' . $_SESSION['customer_lastname']; ?>
		                	<br>
		                	<?php echo $_SESSION['customer_email_address']; ?>
		                </p>
		            </div>
		        </div>
		    </div>
	        <div class="col-2">
		        <div class="box">
		            <div class="box-title">
		                <h3><?php echo __('Newsletters'); ?></h3>
		                <a href="<?php echo href_link(FILENAME_ACCOUNT_NEWSLETTER, '', 'SSL'); ?>"><?php echo __('Edit'); ?></a>
		            </div>
		            <div class="box-content">
		            	<?php if ($_SESSION['customer_newsletter'] == 1) { ?>
		            	<p><?php echo __('You are currently subscribed.'); ?></p>
		                <?php } else { ?>
		                <p><?php echo __('You are currently not subscribed to any newsletter.'); ?></p>
		                <?php } ?>
		            </div>
		        </div>
	    	</div>
		</div>
		<div class="col2-set">
			<div class="box">
	            <div class="box-title">
					<h3><?php echo __('Address Book'); ?></h3>
	                <a href="<?php echo href_link(FILENAME_ADDRESS, '', 'SSL'); ?>"><?php echo __('Manage Addresses'); ?></a>
	            </div>
	             <div class="box-content">
	                 <div class="col-1">
						 <h4><?php echo __('Default Billing Address'); ?></h4>
						 <?php if (isset($billingAddress['address_id'])) { ?>
							 <address><?php echo address_format($billingAddress); ?></address>
							 <p><a href="<?php echo href_link(FILENAME_ADDRESS_EDIT, 'aID='.$billingAddress['address_id'], 'SSL'); ?>"><?php echo __('Edit Address'); ?></a></p>
						 <?php } else { ?>
							 <p><?php echo __('None'); ?></p>
						 <?php } ?>
	                 </div>
	                 <div class="col-2">
						 <h4><?php echo __('Default Shipping Address'); ?></h4>
						 <?php if (isset($shippingAddress['address_id'])) { ?>
							 <address><?php echo address_format($shippingAddress); ?></address>
							 <p><a href="<?php echo href_link(FILENAME_ADDRESS_EDIT, 'aID='.$shippingAddress['address_id'], 'SSL'); ?>"><?php echo __('Edit Address'); ?></a></p>
						 <?php } else { ?>
							 <p><?php echo __('None'); ?></p>
						 <?php } ?>
	                 </div>
	            </div>
	        </div>
		</div>
	</div>
	<?php if (count($reviewList)>0) { ?>
	<div class="box-account box-Review">
        <div class="box-head">
            <h2><?php echo __('My Recent Reviews'); ?></h2>
            <a href="<?php echo href_link(FILENAME_ACCOUNT_REVIEW, '', 'SSL'); ?>"><?php echo __('View All'); ?></a>
        </div>
        <table id="my-reviews-table" class="data-table">
	    <colgroup>
		    <col width="1" />
		    <col width="1" />
		    <col />
		</colgroup>
		<thead>
			<tr>
				<th><?php echo __('Date'); ?></th>
				<th><?php echo __('Rating'); ?></th>
				<th><?php echo __('Product Name'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($reviewList as $val) { ?>
			<tr>
				<td><span class="nobr"><?php echo date_short($val['date_added']); ?></span></td>
				<td><span class="star star<?php echo $val['rating']; ?>"></span></td>
				<td><h3 class="product-name"><a href="<?php echo href_link(FILENAME_PRODUCT, 'pID=' . $val['product_id']); ?>"><?php echo $val['product_name']; ?></a></h3></td>
			</tr>
			<?php } ?>
		</tbody>
		</table>
    </div>
    <?php } ?>
</div>
