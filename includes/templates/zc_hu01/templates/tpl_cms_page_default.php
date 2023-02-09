<div class="account-top">
	<div class="page-title">
		<h1><?php echo __($cmsPageInfo['name']); ?><a class="f-right account-more visible-xs"><i class="iconfont">&#xe609;</i></a></h1>
	</div>
	<div class="account-menu">
		<ul>
			<?php foreach ($cmsPageSidebarList as $val) { ?>
				<?php if(isset($_GET['cpID'])&&$_GET['cpID']!=$val['cms_page_id']) { ?>
					<li><a href="<?php echo href_link(FILENAME_CMS_PAGE, 'cpID=' . $val['cms_page_id']); ?>" rel="external nofollow"><?php echo __($val['name']); ?></a></li>
				<?php } ?>
			<?php } ?>
		</ul>
	</div>
</div>
<?php if ($message_stack->size('cms_page') > 0) echo $message_stack->output('cms_page'); ?>
<div class="std">
<?php echo $cmsPageInfo['content']; ?>
</div>
<script type="text/javascript">
	$('.account-more').click(function(){
		if ($('.account-menu').css("display") == "none") {
			$('.account-menu').slideDown();
			$(this).children("i").html('&#xe643;');
		}
		else {
			$('.account-menu').slideUp();
			$(this).children("i").html('&#xe609;');
		}
	});
</script>