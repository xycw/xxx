<div class="search-order">
    <div class="page-title">
        <h1><?php echo __('Order Check'); ?></h1>
    </div>
    <?php if ($message_stack->size('search_order') > 0) echo $message_stack->output('search_order'); ?>
    <div class="my-account-content">
        <form id="form-validate" method="post">
            <ul class="form-list">
                <li class="fields">
                    <div class="input-box">
                        <input type="text" class="form-control required-entry validate-order-id" value="" name="order_id" placeholder="<?php echo __('Order Number'); ?>">
                    </div>
                </li>
                <li class="fields">
                    <div class="input-box">
                        <input type="text" class="form-control required-entry validate-email" value="" name="email_address" placeholder="<?php echo __('Email Address'); ?>">
                    </div>
                </li>
             </ul>
             <div class="buttons-set">
                 <button type="submit" title="<?php echo __('Search'); ?>" class="btn btn-block btn-black btn-login"><?php echo __('Search'); ?></button>
             </div>
        </form>
    </div>
<script type="text/javascript"><!--
    $('#form-validate').validate();
    //--></script>
</div>