<div class="password-forgotten">
    <div class="page-title">
        <h1><?php echo __('Password Forgotten'); ?></h1>
    </div>
    <?php if ($message_stack->size('password_forgotten') > 0) echo $message_stack->output('password_forgotten'); ?>
    <form id="form-validate" method="post" action="<?php echo href_link(FILENAME_PASSWORD_FORGOTTEN, '', 'SSL'); ?>">
        <div class="box">
            <div class="box-content">
                <p class="tips"><?php echo __('Enter your email address below and we will send you an email message containing your new password.'); ?></p>
                <ul class="form-list">
                    <li>
                        <div class="input-box">
                            <label class="required" for="forget_password_email"><em>*</em><?php echo __('Email Address'); ?></label>
                            <input type="text" class="input-text required-entry validate-email f-left" value="" name="email_address" id="forget_password_email" autocomplete="off" />
                        </div>
                    </li>
                </ul>
                <div class="buttons-set">
                    <a class="f-left" href="<?php echo href_link(FILENAME_LOGIN, '', 'SSL'); ?>"><small>« </small><?php echo __('Back'); ?></a>
                    <button type="submit" title="<?php echo __('Submit'); ?>" class="button f-right"><span><span><?php echo __('Submit'); ?></span></span></button>
                </div>
            </div>

        </div>
    </form>
    <script type="text/javascript"><!--
        $('#form-validate').validate();
        //--></script>
</div>