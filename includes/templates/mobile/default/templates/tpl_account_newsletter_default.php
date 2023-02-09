<div class="my-account">
    <?php if ($message_stack->size('account_newsletter') > 0) echo $message_stack->output('account_newsletter'); ?>
    <div class="my-account-content">
        <form method="post" action="<?php echo href_link(FILENAME_ACCOUNT_NEWSLETTER, '', 'SSL'); ?>">
            <div class="no-display">
                <input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
                <input type="hidden" value="process" name="action" />
            </div>
            <ul class="form-list">
                <li class="control"><input type="checkbox" class="checkbox" title="<?php echo __('General Subscription'); ?>"<?php if ($_SESSION['customer_newsletter']==1) { ?> checked="checked"<?php } ?> value="1" id="newsletter" name="newsletter" /><label for="newsletter"><?php echo __('General Subscription'); ?></label></li>
            </ul>
            <div class="buttons-set">
                <p class="back-link"><a class="btn btn-default" href="<?php echo href_link(FILENAME_ACCOUNT, '', 'SSL'); ?>"><small>« </small><?php echo __('Back'); ?></a></p>
                <button class="btn btn-block btn-black" title="<?php echo __('Save'); ?>" type="submit"><span><span><?php echo __('Save'); ?></span></span></button>
            </div>
        </form>
    </div>
</div>
