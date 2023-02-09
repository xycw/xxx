<div class="my-account">
	<div class="account-top">
		<div class="page-title">
			<h1><?php echo __('Account Information') ?><a class="f-right account-more visible-xs"><i class="iconfont">&#xe609;</i></a></h1>
		</div>
		<div class="account-menu">
			<ul>
				<li><a href="<?php echo href_link(FILENAME_ACCOUNT, '', 'SSL') ?>"><?php echo __('Account') ?></a></li>
				<li><a href="<?php echo href_link(FILENAME_ADDRESS, '', 'SSL') ?>"><?php echo __('Address Book') ?></a></li>
				<li><a href="<?php echo href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL') ?>"><?php echo __('My Orders') ?></a></li>
				<li><a href="<?php echo href_link(FILENAME_ACCOUNT_REVIEW, '', 'SSL') ?>"><?php echo __('My Product Reviews') ?></a></li>
				<li><a href="<?php echo href_link(FILENAME_ACCOUNT_NEWSLETTER, '', 'SSL') ?>"><?php echo __('Newsletter Subscriptions') ?></a></li>
			</ul>
		</div>
	</div>
	<div class="my-account-content">
		<?php if ($message_stack->size('account_edit') > 0) echo $message_stack->output('account_edit'); ?>
		<form id="form-validate" method="post" action="<?php echo href_link(FILENAME_ACCOUNT_EDIT, '', 'SSL'); ?>">
			<div class="no-display">
				<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
				<input type="hidden" value="process" name="action" />
			</div>
			<div class="row">
				<div class="form-group col-md-6">
					<label for="exampleInputEmail1">Email address</label>
					<input type="email" class="form-control" id="exampleInputEmail1" placeholder="Email">
				</div>
				<div class="form-group col-md-6">
					<label class="required" for="customer-firstname"><em>*</em><?php echo __('First Name'); ?></label>
						<input type="text" class="form-control input-text required-entry" value="<?php echo isset($customer['firstname'])?$customer['firstname']:''; ?>" name="customer[firstname]" id="customer-firstname" />
				</div>
				<div class="form-group col-md-6">
					<label class="required" for="customer-lastname"><em>*</em><?php echo __('Last Name'); ?></label>
					<input type="text" class="form-control input-text required-entry" value="<?php echo isset($customer['lastname'])?$customer['lastname']:''; ?>" name="customer[lastname]" id="customer-lastname" />
				</div>
				<div class="form-group col-md-6">
					<label class="required" for="customer-email_address"><em>*</em><?php echo __('Email Address'); ?></label>
					<input type="text" class="form-control input-text required-entry validate-email" value="<?php echo isset($customer['email_address'])?$customer['email_address']:''; ?>" name="customer[email_address]" id="customer-email_address" />
				</div>
			</div>
			<div class="checkbox">
				<label for="change_password">
					<input type="checkbox" title="<?php echo __('Change Password'); ?>" class="checkbox" value="1"<?php if (isset($change_password)&&$change_password==1) { ?> checked="checked"<?php } ?> onclick="setPasswordForm();" id="change_password" name="change_password" />
					<?php echo __('Change Password'); ?>
				</label>
			</div>
			<div id="password-form" class="row">
				<div class="form-group col-md-6">
					<label class="required" for="current_password"><em>*</em><?php echo __('Current Password'); ?></label>
					<input type="password" class="form-control input-text required-entry validate-password" name="current_password" id="current_password" />
				</div>
				<div class="form-group col-md-6">
					<label class="required" for="password"><em>*</em><?php echo __('New Password'); ?></label>
					<input type="password" class="form-control input-text required-entry validate-password" name="password" id="password" />
				</div>
				<div class="form-group col-md-6">
					<label class="required" for="confirm"><em>*</em><?php echo __('Confirm New Password'); ?></label>
					<input type="password" class="form-control input-text required-entry validate-cpassword" name="confirm" id="confirm" />
				</div>
			</div>
			<div class="buttons-set">
				<p class="required">* <?php echo __('Required Fields'); ?></p>
				<p class="back-link"><a href="<?php echo href_link(FILENAME_ACCOUNT, '', 'SSL'); ?>"><small>« </small><?php echo __('Back'); ?></a></p>
				<button class="button" title="<?php echo __('Save'); ?>" type="submit"><span><span><?php echo __('Save'); ?></span></span></button>
			</div>
		</form>
	</div>
<script type="text/javascript"><!--
function setPasswordForm(){
	if($('#change_password').prop('checked')==true){
		$('#password-form').show();
	} else {
		$('#password-form').hide();
	}
}
	
$(function () {
	setPasswordForm();
	$('#form-validate').validate();
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

//--></script>
</div>
