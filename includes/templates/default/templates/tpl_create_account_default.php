<div class="create-account">
    <div class="page-title">
        <h1><?php echo __('Create an Account'); ?></h1>
    </div>
    <?php if ($message_stack->size('create_account') > 0) echo $message_stack->output('create_account'); ?>
    <form id="form-validate" method="post" action="<?php echo href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL'); ?>">
    	<div class="no-display">
    		<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
			<input type="hidden" value="process" name="action" />
		</div>
		<ul class="form-list">
			<li class="fields">
				<div class="field">
					<label class="required" for="customer-firstname"><em>*</em><?php echo __('First Name'); ?></label>
					<div class="input-box">
						<input type="text" class="input-text required-entry" minlength="2" value="<?php echo isset($customer['firstname'])?$customer['firstname']:''; ?>" name="customer[firstname]" id="customer-firstname" />
					</div>
				</div>
				<div class="field">
					<label class="required" for="customer-lastname"><em>*</em><?php echo __('Last Name'); ?></label>
					<div class="input-box">
						<input type="text" class="input-text required-entry" minlength="2" value="<?php echo isset($customer['lastname'])?$customer['lastname']:''; ?>" name="customer[lastname]" id="customer-lastname" />
					</div>
				</div>
			</li>
			<li class="wide">
				<div class="field">
					<label class="required" for="customer-email_address"><em>*</em><?php echo __('Email Address'); ?></label>
					<div class="input-box">
						<input type="text" class="input-text required-entry validate-email" minlength="10" value="<?php echo isset($customer['email_address'])?$customer['email_address']:''; ?>" name="customer[email_address]" id="customer-email_address" />
					</div>
				</div>
			</li>
			<li class="fields">
			    <div class="field">
			        <label class="required" for="password"><em>*</em><?php echo __('Password'); ?></label>
			        <div class="input-box">
			        <input type="password" class="input-text required-entry validate-password" name="password" id="password" />
			        </div>
			    </div>
			    <div class="field">
			        <label class="required" for="confirm"><em>*</em><?php echo __('Confirm Password'); ?></label>
			        <div class="input-box">
			        <input type="password" class="input-text required-entry validate-cpassword" name="confirm" id="confirm" />
			        </div>
			    </div>
			</li>
			<li class="control">
				<input type="checkbox" class="checkbox" title="<?php echo __('Sign Up for Newsletter'); ?>" value="1"<?php if (isset($customer['newsletter'])&&$customer['newsletter']==1) { ?> checked="checked"<?php } ?> name="customer[newsletter]" id="customer-newsletter" /><label for="customer-newsletter"><?php echo __('Sign Up for Newsletter'); ?></label>
			</li>
		</ul>
		<div class="buttons-set">
            <p class="required">* <?php echo __('Required Fields'); ?></p>
            <p class="back-link"><a href="<?php echo href_link(FILENAME_LOGIN, '', 'SSL'); ?>"><small>« </small><?php echo __('Back'); ?></a></p>
            <button class="button" title="<?php echo __('Submit'); ?>" type="submit"><span><span><?php echo __('Submit'); ?></span></span></button>
        </div>
    </form>
<script type="text/javascript"><!--
updateRegion('customer', '<?php echo isset($customer['region_id'])?$customer['region_id']:''; ?>');
$('#form-validate').validate();
//--></script>
</div>
