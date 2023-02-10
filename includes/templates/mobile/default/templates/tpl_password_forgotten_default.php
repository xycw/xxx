<div class="password-forgotten">
    <div class="page-title">
        <h1><?php echo __('Password Forgotten'); ?></h1>
    </div>
    <div class="my-account-content">
        <?php if ($message_stack->size('password_forgotten') > 0) echo $message_stack->output('password_forgotten'); ?>
        <form id="form-validate" method="post" action="<?php echo href_link(FILENAME_PASSWORD_FORGOTTEN, '', 'SSL'); ?>">
            <div class="box-content">
                <p class="tips mg-b10"><?php echo __('Enter your email address below and we will send you an email message containing your new password.'); ?></p>
                <ul class="form-list">
                    <li>
                        <div class="input-box">
                            <input type="text" class="form-control required-entry validate-email" value="" name="email_address" placeholder="<?php echo __('Email Address'); ?>" autocomplete="off" />
                        </div>
                    </li>
                </ul>
                <div class="buttons-set">
                    <p class="back-link"><a class="btn btn-default" href="<?php echo href_link(FILENAME_LOGIN, '', 'SSL'); ?>"><small>« </small><?php echo __('Back'); ?></a></p>
                    <button type="submit" class="btn btn-block btn-black" title="<?php echo __('Submit'); ?>"><?php echo __('Submit'); ?></button>
                </div>
            </div>
        </form>
    </div>
<script type="text/javascript"><!--
$('#form-validate').validate();
//--></script>
</div>