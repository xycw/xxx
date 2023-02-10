<div class="page-title">
	<h1><?php echo __($cmsPageInfo['name']); ?></h1>
</div>
<?php if ($message_stack->size('cms_page') > 0) echo $message_stack->output('cms_page'); ?>
<div class="std">
<?php echo $cmsPageInfo['content']; ?>
</div>
