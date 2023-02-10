<div class="account-login">
    <div class="page-title">
        <h1><?php echo __('Login or Create an Account'); ?></h1>
    </div>
    <?php if ($message_stack->size('login') > 0) echo $message_stack->output('login'); ?>
	<form id="form-validate" method="post" action="<?php echo href_link(FILENAME_LOGIN, '', 'SSL'); ?>">
		<div class="no-display">
			<input type="hidden" value="process" name="action" />
			<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
		</div>
		<div class="col2-set">
			<div class="col-1 new-users">
				<div class="box">
					<div class="box-title">
						<h2><?php echo __('New Customers'); ?></h2>
					</div>
					<div class="box-content">
						<p><?php echo __('By creating an account with our store, you will be able to move through the checkout process faster, store multiple shipping addresses, view and track your orders in your account and more.'); ?></p>
					</div>
				</div>
			</div>
			<div class="col-2 registered-users">
				<div class="box">
					<div class="box-title">
						 <h2><?php echo __('Registered Customers'); ?></h2>
					</div>
					<div class="box-content">
						<p><?php echo __('If you have an account with us, please log in.'); ?></p>
	                    <ul class="form-list">
	                        <li>
	                            <label class="required" for="username"><em>*</em><?php echo __('Email Address'); ?></label>
	                            <div class="input-box">
	                                <input type="text" class="input-text required-entry validate-email" value="<?php echo isset($_POST['username'])?$_POST['username']:''; ?>" name="username" id="username" />
	                            </div>
	                        </li>
	                        <li>
	                            <label class="required" for="password"><em>*</em><?php echo __('Password'); ?></label>
	                            <div class="input-box">
	                                <input type="password" class="input-text required-entry validate-password" name="password" id="password" />
	                            </div>
	                        </li>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<div class="col2-set">
            <div class="col-1">
                <div class="buttons-set">
                    <button type="button" title="<?php echo __('Create an Account'); ?>" class="button" onclick="setLocation('<?php echo href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL'); ?>');"><span><span><?php echo __('Create an Account'); ?></span></span></button>
                </div>
            </div>
            <div class="col-2">
                <div class="buttons-set">
                    <a class="f-left" href="<?php echo href_link(FILENAME_PASSWORD_FORGOTTEN, '', 'SSL'); ?>"><?php echo __('Forgot Your Password?'); ?></a>
                    <button type="submit" title="<?php echo __('Login'); ?>" class="button"><span><span><?php echo __('Login'); ?></span></span></button>
                </div>
            </div>
        </div>
	</form>
<script type="text/javascript"><!--
$('#form-validate').validate();
//--></script>
</div>
