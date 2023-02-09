<div class="my-account">
	<div class="account-top">
		<div class="page-title">
			<h1><?php echo __('Address Book') ?><a class="f-right account-more visible-xs"><i class="iconfont">&#xe609;</i></a></h1>
		</div>
		<div class="account-menu">
			<ul>
				<li><a href="<?php echo href_link(FILENAME_ACCOUNT, '', 'SSL') ?>"><?php echo __('Account') ?></a></li>
				<li><a href="<?php echo href_link(FILENAME_ACCOUNT_EDIT, '', 'SSL') ?>"><?php echo __('Account Information') ?></a></li>
				<li><a href="<?php echo href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL') ?>"><?php echo __('My Orders') ?></a></li>
				<li><a href="<?php echo href_link(FILENAME_ACCOUNT_REVIEW, '', 'SSL') ?>"><?php echo __('My Product Reviews') ?></a></li>
				<li><a href="<?php echo href_link(FILENAME_ACCOUNT_NEWSLETTER, '', 'SSL') ?>"><?php echo __('Newsletter Subscriptions') ?></a></li>
			</ul>
		</div>
	</div>
	<div class="my-account-content">
		<?php if ($message_stack->size('address') > 0) echo $message_stack->output('address'); ?>
		<div class="row">
			<div class="col-sm-6 col-md-6 col-xs-12 addresses-primary">
				<div class="box">
					<div class="box-title">
						<h2><?php echo __('Default Addresses'); ?></h2>
					</div>
					<div class="box-content">
						<ol>
							<li class="item">
								<h4><?php echo __('Default Billing Address'); ?></h4>
								<?php if (isset($billingAddress['address_id'])) { ?>
									<address><?php echo address_format($billingAddress); ?></address>
									<p><a href="<?php echo href_link(FILENAME_ADDRESS_EDIT, 'aID='.$billingAddress['address_id'], 'SSL'); ?>"><?php echo __('Change Billing Address'); ?></a></p>
								<?php } else { ?>
									<p><?php echo __('None'); ?></p>
								<?php } ?>
							</li>
							<li class="item">
								<h4><?php echo __('Default Shipping Address'); ?></h4>
								<?php if (isset($shippingAddress['address_id'])) { ?>
									<address><?php echo address_format($shippingAddress); ?></address>
									<p><a href="<?php echo href_link(FILENAME_ADDRESS_EDIT, 'aID='.$shippingAddress['address_id'], 'SSL'); ?>"><?php echo __('Change Shipping Address'); ?></a></p>
								<?php } else { ?>
									<p><?php echo __('None'); ?></p>
								<?php } ?>
							</li>
						</ol>
					</div>
				</div>
			</div>
			<div class="col-sm-6 col-md-6 col-xs-12 addresses-additional">
				<div class="box">
					<div class="box-title">
						<h2><?php echo __('Additional Address'); ?></h2>
					</div>
					<div class="box-content">
						<ol>
							<?php if (count($additionalAddressList)>0) { ?>
								<?php foreach ($additionalAddressList as $_address) { ?>
									<li class="item">
										<address><?php echo address_format($_address); ?></address>
										<p><a href="<?php echo href_link(FILENAME_ADDRESS_EDIT, 'aID='.$_address['address_id'], 'SSL'); ?>"><?php echo __('Edit Address'); ?></a> <span class="separator">|</span> <a class="link-remove" onclick="return confirm('<?php echo __('Are you sure you want to delete this address?'); ?>');" href="<?php echo href_link(FILENAME_ADDRESS, 'delete=' . $_address['address_id']); ?>"><?php echo __('Delete Address'); ?></a></p>
									</li>
								<?php } ?>
							<?php } else { ?>
								<li class="item empty">
									<p><?php echo __('You have no additional address entries in your address book.'); ?></p>
								</li>
							<?php } ?>
						</ol>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="buttons-set">
		<p class="back-link"><a href="<?php echo href_link(FILENAME_ACCOUNT, '', 'SSL'); ?>"><small>« </small><?php echo __('Back'); ?></a></p>
		<button class="button" title="Add New Address" type="button" onclick="setLocation('<?php echo href_link(FILENAME_ADDRESS_NEW, '', 'SSL'); ?>');"><span><span><?php echo __('Add New Address'); ?></span></span></button>
	</div>
</div>
<script type="text/javascript">
	$('.account-more').click(function(){
		if ($('.account-menu').css("display") == "none") {
			$('.account-menu').slideDown();
			$(this).children("i").html('&#xe643;');
		}
		else {
			$('.account-menu').slideUp();
			$(this).children("i").html('&#xe609;');
		}
	});
</script>
