<div class="my-account">
	<div class="account-top">
		<div class="page-title">
			<h1><?php echo __('Newsletter Subscriptions') ?><a class="f-right account-more visible-xs"><i class="iconfont">&#xe609;</i></a></h1>
		</div>
		<div class="account-menu">
			<ul>
				<li><a href="<?php echo href_link(FILENAME_ACCOUNT, '', 'SSL') ?>"><?php echo __('Account') ?></a></li>
				<li><a href="<?php echo href_link(FILENAME_ACCOUNT_EDIT, '', 'SSL') ?>"><?php echo __('Account Information') ?></a></li>
				<li><a href="<?php echo href_link(FILENAME_ADDRESS, '', 'SSL') ?>"><?php echo __('Address Book') ?></a></li>
				<li><a href="<?php echo href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL') ?>"><?php echo __('My Orders') ?></a></li>
				<li><a href="<?php echo href_link(FILENAME_ACCOUNT_REVIEW, '', 'SSL') ?>"><?php echo __('My Product Reviews') ?></a></li>
			</ul>
		</div>
	</div>
	<div class="my-account-content">
		<?php if ($message_stack->size('account_newsletter') > 0) echo $message_stack->output('account_newsletter'); ?>
		<form method="post" action="<?php echo href_link(FILENAME_ACCOUNT_NEWSLETTER, '', 'SSL'); ?>">
			<div class="no-display">
				<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
				<input type="hidden" value="process" name="action" />
			</div>
			<div class="checkbox">
				<label for="newsletter">
					<input type="checkbox" class="checkbox" title="<?php echo __('General Subscription'); ?>"<?php if ($_SESSION['customer_newsletter']==1) { ?> checked="checked"<?php } ?> value="1" id="newsletter" name="newsletter" />
					<?php echo __('General Subscription'); ?>
				</label>
			</div>
			<div class="buttons-set">
				<p class="back-link"><a href="<?php echo href_link(FILENAME_ACCOUNT, '', 'SSL'); ?>"><small>« </small><?php echo __('Back'); ?></a></p>
				<button class="button" title="<?php echo __('Save'); ?>" type="submit"><span><span><?php echo __('Save'); ?></span></span></button>
			</div>
		</form>
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
