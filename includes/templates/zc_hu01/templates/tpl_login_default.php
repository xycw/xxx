<div class="account-login">
	<?php if ($message_stack->size('login') > 0) echo $message_stack->output('login'); ?>
	<form id="form-validate" method="post" action="<?php echo href_link(FILENAME_LOGIN, '', 'SSL'); ?>">
		<div class="no-display">
			<input type="hidden" value="process" name="action" />
			<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
		</div>
		<div class="row">
			<div class="col-md-6 col-sm-12 col-xs-12 registered-users">
				<div class="page-title">
					<h2><?php echo __('Registered Customers'); ?></h2>
				</div>
				<div class="box-content">
					<p><?php echo __('If you have an account with us, please log in.'); ?></p>
					<div class="form-group">
						<input type="text" placeholder="<?php echo __('Email Address'); ?>" class="form-control input-text required-entry validate-email" value="<?php echo isset($_POST['username'])?$_POST['username']:''; ?>" name="username" id="username" />
					</div>
					<div class="form-group">
						<input type="password" placeholder="<?php echo __('Password'); ?>" class="form-control input-text required-entry validate-password" name="password" id="password" />
					</div>
				</div>
				<div class="buttons-set">
					<a class="f-left" href="<?php echo href_link(FILENAME_PASSWORD_FORGOTTEN, '', 'SSL'); ?>"><?php echo __('Forgot Your Password?'); ?></a>
					<button type="submit" title="<?php echo __('Login'); ?>" class="button"><span><span><?php echo __('Login'); ?></span></span></button>
				</div>
			</div>
			<div class="col-md-6 col-sm-12 col-xs-12 new-users">
				<div class="page-title">
					<h2><?php echo __('New Customers'); ?></h2>
				</div>
				<div class="box-content">
					<p><?php echo __('By creating an account with our store, you will be able to move through the checkout process faster, store multiple shipping addresses, view and track your orders in your account and more.'); ?></p>
				</div>
				<div class="buttons-set">
					<button type="button" title="<?php echo __('Create an Account'); ?>" class="button" onclick="setLocation('<?php echo href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL'); ?>');"><span><span><?php echo __('Create an Account'); ?></span></span></button>
				</div>
			</div>
		</div>
	</form>
<script type="text/javascript"><!--
$('#form-validate').validate();
//--></script>
</div>
