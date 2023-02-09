<div class="account-login">
    <div class="page-title">
        <h1><?php echo __('Login or Create an Account'); ?></h1>
    </div>
    <div class="my-account-content">
		<?php if ($message_stack->size('login') > 0) echo $message_stack->output('login'); ?>
		<form id="form-validate" method="post" action="<?php echo href_link(FILENAME_LOGIN, '', 'SSL'); ?>">
			<div class="no-display">
				<input type="hidden" value="process" name="action" />
				<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
			</div>
			<div class="registered-users">
				<div class="box-content">
					<ul class="form-list">
						<li>
							<div class="input-box">
								<input type="text" class="form-control required-entry validate-email" value="<?php echo isset($_POST['username'])?$_POST['username']:''; ?>" name="username" id="username" placeholder="<?php echo __('Email Address'); ?>" />
							</div>
						</li>
						<li>
							<div class="input-box">
								<input type="password" class="form-control required-entry validate-password" name="password" id="password" placeholder="<?php echo __('Password'); ?>" />
							</div>
						</li>
					</ul>
					<div class="action-bar">
						<a class="btn-psw f-left" href="<?php echo href_link(FILENAME_PASSWORD_FORGOTTEN, '', 'SSL'); ?>"><?php echo __('Forgot Your Password?'); ?></a>
						<button type="submit" title="<?php echo __('Login'); ?>" class="btn btn-block btn-black mg-b10 btn-login"><?php echo __('Login'); ?></button>
						<button type="button" title="<?php echo __('Create an Account'); ?>" class="btn btn-block btn-default btn-register" onclick="setLocation('<?php echo href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL'); ?>');"><?php echo __('Create an Account'); ?></button>
					</div>
				</div>
			</div>
		</form>
	</div>
<script type="text/javascript"><!--
$('#form-validate').validate();
//--></script>
</div>
