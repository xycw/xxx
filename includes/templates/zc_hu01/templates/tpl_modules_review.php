<?php include(DIR_FS_CATALOG_MODULES . get_module_directory('review.php')); ?>
<a class="cosTab" id="customer-review-tab" href="#customer-review"><span class="cos-arrow"></span><?php echo __('Customer Reviews') ?></a>
<div class="box-collateral box-review" id="customer-review">
<?php if (isset($reviewList) && count($reviewList)>0) { ?>
	<div class="pr-contents-wrapper">
		<div class="pr-pagination-top">
			<h2><?php echo __('Customer Reviews') ?></h2>
		</div>
		<?php require($template->get_template_dir('tpl_modules_pager.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_pager.php'); ?>
		<ul class="review-row">
			<?php foreach ($reviewList as $val) { ?>
				<li>
					<p><span class="star star<?php echo $val['rating']; ?>"></span><span class="review-date"><?php echo date_short($val['date_added']); ?></span></p>
					<p class="review-text"><?php echo $val['content']; ?></p>
					<p class="a-right review-name"><?php echo __('By <span>%s</span>', $val['nickname']); ?></p>
				</li>
			<?php } ?>
		</ul>
		<?php require($template->get_template_dir('tpl_modules_pager.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_pager.php'); ?>
	</div>
<?php } ?>
	<div class="form-add">
		<h2><?php echo __('Write Your Own Review') ?></h2>
		<form action="<?php echo href_link(FILENAME_PRODUCT, 'pID=' . $_GET['pID']); ?>" method="post" id="review-form">
		<div class="no-display">
			<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
			<input type="hidden" value="process" name="action" />
		</div>
		<div class="form-group">
			<label class="required"><em>*</em><?php echo __('Rating'); ?></label>
			<div class="input-box">
				<div id="star"></div>
			</div>
		</div>
		<div class="form-group">
			<label class="required" for="review-nickname"><em>*</em><?php echo __('Nickname'); ?></label>
			<input type="text" value="<?php echo isset($_SESSION['customer_firstname'])?$_SESSION['customer_firstname']:'';?>" class="form-control input-text required-entry" id="review-nickname" name="review[nickname]">
		</div>
		<div class="form-group">
			<label class="required" for="review-content"><em>*</em><?php echo __('Review'); ?></label>
			<textarea class="form-control required-entry" rows="5" cols="5" id="review-content" name="review[content]"></textarea>
		</div>
		<div class="buttons-set">
			<button type="submit" class="button" title="<?php echo __('Submit Review') ?>"><span><span><?php echo __('Submit Review') ?></span></span></button>
		</div>
		</form>
	</div>
	<script type="text/javascript">
		$('#star').raty({
			scoreName:'review[rating]',
			score: 5,
			size: 22,
			path:'<?php echo DIR_WS_TEMPLATE_IMAGES; ?>',
			hints:[
				'<?php echo __('Bad'); ?>',
				'<?php echo __('Poor'); ?>',
				'<?php echo __('Regular'); ?>',
				'<?php echo __('Good'); ?>',
				'<?php echo __('Gorgeous'); ?>'
			]
		});
		if(window.location.hash == "#customer-review")
		{
			$('#customer-review').click();
		}
		$('#review-form').validate();
	</script>
</div>