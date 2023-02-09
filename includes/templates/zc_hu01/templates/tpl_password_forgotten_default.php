<div class="password-forgotten">
    <div class="page-title">
        <h1><?php echo __('Password Forgotten'); ?></h1>
    </div>
    <?php if ($message_stack->size('password_forgotten') > 0) echo $message_stack->output('password_forgotten'); ?>
    <form id="form-validate" method="post" action="<?php echo href_link(FILENAME_PASSWORD_FORGOTTEN, '', 'SSL'); ?>">
        <div class="row">
            <div class="col-md-6 col-sm-12 col-xs-12 registered-users">
                <div class="box-content">
                    <p><?php echo __('Enter your email address below and we will send you an email message containing your new password.'); ?></p>
                    <div class="form-group">
                        <input type="text" placeholder="<?php echo __('Email Address'); ?>" class="form-control input-text required-entry validate-email" value="" name="email_address" id="forget_password_email" />
                    </div>
                </div>
                <div class="buttons-set">
                    <a class="f-left" href="<?php echo href_link(FILENAME_LOGIN, '', 'SSL'); ?>"><small>« </small><?php echo __('Back'); ?></a>
                    <button type="submit" title="<?php echo __('Submit'); ?>" class="button"><span><span><?php echo __('Submit'); ?></span></span></button>
                </div>
            </div>
        </div>
    </form>
<script type="text/javascript"><!--
$('#form-validate').validate();
//--></script>
</div>