<div class="search-order">
    <div class="page-title">
        <h1><?php echo __('Order Check'); ?></h1>
    </div>
    <?php if ($message_stack->size('search_order') > 0) echo $message_stack->output('search_order'); ?>
    <div class="search-con">
        <form id="form-validate" method="post">
            <ul class="form-list">
                <li class="fields">
                    <label class="required"><?php echo __('Order Number'); ?><em>*</em></label>
                    <div class="input-box">
                        <input type="text" class="input-text required-entry validate-order-id" value="" name="order_id">
                    </div>
                </li>
                <li class="fields">
                    <label class="required"><?php echo __('Email Address'); ?><em>*</em></label>
                    <div class="input-box">
                        <input type="text" class="input-text required-entry validate-email" value="" name="email_address">
                    </div>
                </li>
             </ul>
             <div class="buttons-set">
                 <button type="submit" class="button" title="<?php echo __('Search'); ?>"><span><span><?php echo __('Search'); ?></span></span></button>
             </div>
        </form>
    </div>
<script type="text/javascript"><!--
    $('#form-validate').validate();
    //--></script>
</div>