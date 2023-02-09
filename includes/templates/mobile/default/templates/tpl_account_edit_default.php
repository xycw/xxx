<div class="my-account">
    <?php if ($message_stack->size('account_edit') > 0) echo $message_stack->output('account_edit'); ?>
    <div class="my-account-content">
        <form id="form-validate" method="post" action="<?php echo href_link(FILENAME_ACCOUNT_EDIT, '', 'SSL'); ?>">
            <div class="no-display">
                <input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
                <input type="hidden" value="process" name="action" />
            </div>
            <ul class="form-list">
                <li class="fields">
                    <div class="field">
                        <label class="required" for="customer-firstname"><em>*</em><?php echo __('First Name'); ?></label>
                        <div class="input-box">
                            <input type="text" class="form-control required-entry" value="<?php echo isset($customer['firstname'])?$customer['firstname']:''; ?>" name="customer[firstname]" id="customer-firstname" />
                        </div>
                    </div>
                </li>
                <li class="fields">
                    <div class="field">
                        <label class="required" for="customer-lastname"><em>*</em><?php echo __('Last Name'); ?></label>
                        <div class="input-box">
                            <input type="text" class="form-control required-entry" value="<?php echo isset($customer['lastname'])?$customer['lastname']:''; ?>" name="customer[lastname]" id="customer-lastname" />
                        </div>
                    </div>
                </li>
                <li class="fields">
                    <div class="field">
                        <label class="required" for="customer-email_address"><em>*</em><?php echo __('Email Address'); ?></label>
                        <div class="input-box">
                            <input type="text" class="form-control required-entry validate-email" value="<?php echo isset($customer['email_address'])?$customer['email_address']:''; ?>" name="customer[email_address]" id="customer-email_address" />
                        </div>
                    </div>
                </li>
                <li class="control">
                    <input type="checkbox" title="<?php echo __('Change Password'); ?>" class="checkbox" value="1"<?php if (isset($change_password)&&$change_password==1) { ?> checked="checked"<?php } ?> onclick="setPasswordForm();" id="change_password" name="change_password" /><label for="change_password"><?php echo __('Change Password'); ?></label>
                </li>
            </ul>
            <ul id="password-form" class="form-list">
                <li class="fields">
                    <div class="field">
                        <label class="required" for="current_password"><em>*</em><?php echo __('Current Password'); ?></label>
                        <div class="input-box">
                            <input type="password" class="form-control required-entry validate-password" name="current_password" id="current_password" />
                        </div>
                    </div>
                </li>
                <li class="fields">
                    <div class="field">
                        <label class="required" for="password"><em>*</em><?php echo __('New Password'); ?></label>
                        <div class="input-box">
                            <input type="password" class="form-control required-entry validate-password" name="password" id="password" />
                        </div>
                    </div>
                    <div class="field">
                        <label class="required" for="confirm"><em>*</em><?php echo __('Confirm New Password'); ?></label>
                        <div class="input-box">
                            <input type="password" class="form-control required-entry validate-cpassword" name="confirm" id="confirm" />
                        </div>
                    </div>
                </li>
            </ul>
            <div class="buttons-set">
                <p class="back-link"><a class="btn btn-default" href="<?php echo href_link(FILENAME_ACCOUNT, '', 'SSL'); ?>"><small>« </small><?php echo __('Back'); ?></a></p>
                <p class="required">* <?php echo __('Required Fields'); ?></p>
                <button class="btn btn-block btn-black" title="<?php echo __('Save'); ?>" type="submit"><?php echo __('Save'); ?></button>
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
setPasswordForm();
$('#form-validate').validate();
//--></script>
</div>
