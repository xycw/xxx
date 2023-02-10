<div class="my-account">
    <div class="my-account-content account-review">
        <?php if (count($reviewList)>0) { ?>
            <?php require($template->get_template_dir('tpl_modules_pager.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_pager.php'); ?>
            <table id="my-reviews-table" class="data-table">
                <tbody>
                <?php foreach ($reviewList as $val) { ?>
                    <tr>
                        <td>
                            <h2 class="product-name"><a href="<?php echo href_link(FILENAME_PRODUCT, 'pID=' . $val['product_id']); ?>"><?php echo $val['product_name']; ?></a></h2>
                            <span class="nobr"><?php echo date_short($val['date_added']); ?></span><br>
                            <span class="star star<?php echo $val['rating']; ?>"></span><br>
                            <strong class="reviews-table"><?php echo __('Review'); ?> :</strong><br>
                            <?php echo $val['content']; ?>
                        </td>
                        <td></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            <?php require($template->get_template_dir('tpl_modules_pager.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_pager.php'); ?>
        <?php } else { ?>
            <p><?php echo __('You have submitted no reviews.'); ?></p>
        <?php } ?>
        <div class="buttons-set">
            <p class="back-link"><a class="btn btn-default" href="<?php echo href_link(FILENAME_ACCOUNT, '', 'SSL'); ?>"><small>« </small><?php echo __('Back'); ?></a></p>
        </div>
    </div>
</div>
