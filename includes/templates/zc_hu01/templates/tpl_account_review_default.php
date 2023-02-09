<div class="my-account">
	<div class="account-top">
		<div class="page-title">
			<h1><?php echo __('My Product Reviews') ?><a class="f-right account-more visible-xs"><i class="iconfont">&#xe609;</i></a></h1>
		</div>
		<div class="account-menu">
			<ul>
				<li><a href="<?php echo href_link(FILENAME_ACCOUNT, '', 'SSL') ?>"><?php echo __('Account') ?></a></li>
				<li><a href="<?php echo href_link(FILENAME_ACCOUNT_EDIT, '', 'SSL') ?>"><?php echo __('Account Information') ?></a></li>
				<li><a href="<?php echo href_link(FILENAME_ADDRESS, '', 'SSL') ?>"><?php echo __('Address Book') ?></a></li>
				<li><a href="<?php echo href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL') ?>"><?php echo __('My Orders') ?></a></li>
				<li><a href="<?php echo href_link(FILENAME_ACCOUNT_NEWSLETTER, '', 'SSL') ?>"><?php echo __('Newsletter Subscriptions') ?></a></li>
			</ul>
		</div>
	</div>
	<div class="my-account-content">
		<?php if (count($reviewList)>0) { ?>
			<?php require($template->get_template_dir('tpl_modules_pager.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_pager.php'); ?>
			<table id="my-reviews-table" class="table table-bordered data-table hidden-xs">
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
			<table class="table table-bordered data-table visible-xs">
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
					</tr>
				<?php } ?>
				</tbody>
			</table>
			<?php require($template->get_template_dir('tpl_modules_pager.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_pager.php'); ?>
		<?php } else { ?>
			<p><?php echo __('You have submitted no reviews.'); ?></p>
		<?php } ?>
	</div>
    <div class="buttons-set">
    	<p class="back-link"><a href="<?php echo href_link(FILENAME_ACCOUNT, '', 'SSL'); ?>"><small>« </small><?php echo __('Back'); ?></a></p>
	</div>
</div>
<script type="text/javascript">
	$(function () {
		$('.account-more').click(function(){
			if ($('.account-menu').is(':hidden')) {
				$('.account-menu').slideDown();
				$(this).children("i").html('&#xe643;');
			} else {
				$('.account-menu').slideUp();
				$(this).children("i").html('&#xe609;');
			}
		});
	})
</script>
