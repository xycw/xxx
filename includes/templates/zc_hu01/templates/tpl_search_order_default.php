<div class="search-order">
    <?php if ($message_stack->size('search_order') > 0) echo $message_stack->output('search_order'); ?>
    <form id="form-validate" method="post">
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12 registered-users">
                <div class="page-title">
                    <h2><?php echo __('Order Check'); ?></h2>
                </div>
                <div class="box-content">
                    <div class="form-group">
                        <input type="text" class="form-control required-entry validate-order-id" value="" name="order_id" placeholder="<?php echo __('Order Number'); ?>">
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control required-entry validate-email" value="" name="email_address" placeholder="<?php echo __('Email Address'); ?>">
                    </div>
                </div>
                <div class="buttons-set">
                    <button type="submit" title="<?php echo __('Search'); ?>" class="button"><span><span><?php echo __('Search'); ?></span></span></button>
                </div>
            </div>
        </div>
    </form>
<script type="text/javascript"><!--
$('#form-validate').validate();
//--></script>
</div>