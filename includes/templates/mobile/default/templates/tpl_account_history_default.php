<div class="my-account">
    <div class="my-account-content account-history">
        <?php if (count($orderList)>0) { ?>
            <?php require($template->get_template_dir('tpl_modules_pager.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_pager.php'); ?>
            <table class="data-table" id="my-orders-table">
                <colgroup>
                    <col width="1" />
                    <col />
                    <col width="1" />
                </colgroup>
                <tbody>
                <?php foreach ($orderList as $_order) { ?>
                    <tr>
                        <td>
                            <span class="nobr"><?php echo put_orderNO($_order['order_id']); ?></span><br>
                            <span class="nobr"><a href="<?php echo href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'oID='.$_order['order_id'], 'SSL'); ?>"><?php echo __('View Order'); ?></a></span>
                        </td>
                        <td>
                            <span class="a-thead"><?php echo __('Ship To'); ?> :</span>
                            <?php echo $_order['shipping_country']; ?>, <?php echo $_order['shipping_name']; ?><br>
                            <span class="a-thead"><?php echo __('Date'); ?> :</span>
                            <?php echo date_short($_order['date_added']); ?><br>
                            <span class="a-thead"><?php echo __('Status'); ?> :</span>
                            <?php echo __($_order['order_status_name']); ?><br>
                            <span class="a-thead"><?php echo __('Order Total'); ?> :</span>
                            <?php echo $_order['order_total']; ?>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            <script type="text/javascript">decorateTable($('#my-orders-table'));</script>
        <?php require($template->get_template_dir('tpl_modules_pager.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_pager.php'); ?>
        <?php } else { ?>
            <p><?php echo __('You have placed no orders.'); ?></p>
        <?php }?>
        <div class="buttons-set">
            <p class="back-link"><a class="btn btn-default" href="<?php echo href_link(FILENAME_ACCOUNT, '', 'SSL'); ?>"><small>« </small><?php echo __('Back'); ?></a></p>
        </div>
    </div>
</div>
