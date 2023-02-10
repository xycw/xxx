<?php include(DIR_FS_CATALOG_MODULES . get_module_directory('review.php')); ?>
<div class="tab-title"><h2><?php echo __('Customer Reviews') ?></h2><i class="iconfont">&#xe609;</i></div>
<div class="tab-content box-review" id="customer-review">
	<?php if (count($reviewList)>0) { ?>
	<?php require($template->get_template_dir('tpl_modules_pager.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_pager.php'); ?>
	<ul>
	<?php foreach ($reviewList as $val) { ?>
		<li>
			<p class="of-hidden mg-b5"><span class="star star<?php echo $val['rating']; ?> f-left"></span><span class="f-right"><?php echo date_short($val['date_added']); ?></span></p>
			<p class="mg-b5 f-bold c-black"><?php echo $val['content']; ?></p>
			<p class="a-right"><?php echo __('By <span>%s</span>', $val['nickname']); ?></p>
		</li>
	<?php } ?>
	</ul>
	<?php require($template->get_template_dir('tpl_modules_pager.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_pager.php'); ?>
<script type="text/javascript"><!--
decorateDataList($("#customer-review dl"));
//--></script>
	<?php } ?>
	<div class="form-add">
		<h2><?php echo __('Write Your Own Review') ?></h2>
		<form action="<?php echo href_link(FILENAME_PRODUCT, 'pID=' . $_GET['pID']); ?>" method="post" id="review-form">
		<div class="no-display">
			<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
			<input type="hidden" value="process" name="action" />
		</div>
		<ul class="form-list">
			<li>
				<label class="required"><em>*</em><?php echo __('Rating'); ?></label>
				<div class="input-box">
					<div id="star"></div>
				</div>
			</li>
			<li>
				<label class="required" for="review-nickname"><em>*</em><?php echo __('Nickname'); ?></label>
				<div class="input-box">
					<input type="text" value="<?php echo isset($_SESSION['customer_firstname'])?$_SESSION['customer_firstname']:'';?>" class="form-control required-entry" id="review-nickname" name="review[nickname]">
				</div>
			</li>
			<li>
				<label class="required" for="review-content"><em>*</em><?php echo __('Review'); ?></label>
				<div class="input-box">
					<textarea class="form-control required-entry" rows="3" cols="5" id="review-content" name="review[content]"></textarea>
				</div>
			</li>
		</ul>
		<div class="buttons-set">
			<button type="submit" class="btn btn-block btn-black" title="<?php echo __('Submit Review') ?>"><?php echo __('Submit Review') ?></button>
		</div>
		</form>
	</div>
</div>
<script type="text/javascript"><!--
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
//--></script>
<script type="text/javascript"><!--
$('#review-form').validate();
//--></script>
