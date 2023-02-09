<div class="my-account">
	<div class="page-title">
        <h1><?php echo __('My Product Reviews'); ?></h1>
    </div>
    <?php if (count($reviewList)>0) { ?>
    <?php require($template->get_template_dir('tpl_modules_pager.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_pager.php'); ?>
    <table id="my-reviews-table" class="data-table">
    <colgroup>
	    <col width="1" />
	    <col width="210" />
		<col width="1" />
		<col />
	</colgroup>
	<thead>
		<tr>
			<th><?php echo __('Date'); ?></th>
			<th><?php echo __('Product Name'); ?></th>
			<th><?php echo __('Rating'); ?></th>
			<th><?php echo __('Review'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($reviewList as $val) { ?>
		<tr>
			<td><span class="nobr"><?php echo date_short($val['date_added']); ?></span></td>
			<td><h2 class="product-name"><a href="<?php echo href_link(FILENAME_PRODUCT, 'pID=' . $val['product_id']); ?>"><?php echo $val['product_name']; ?></a></h2></td>
			<td><span class="star star<?php echo $val['rating']; ?>"></span></td>
			<td><?php echo $val['content']; ?></td>
		</tr>
		<?php } ?>
	</tbody>
	</table>
    <?php require($template->get_template_dir('tpl_modules_pager.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_pager.php'); ?>
    <?php } else { ?>
    <p><?php echo __('You have submitted no reviews.'); ?></p>
    <?php } ?>
    <div class="buttons-set">
    	<p class="back-link"><a href="<?php echo href_link(FILENAME_ACCOUNT, '', 'SSL'); ?>"><small>« </small><?php echo __('Back'); ?></a></p>
	</div>
</div>
