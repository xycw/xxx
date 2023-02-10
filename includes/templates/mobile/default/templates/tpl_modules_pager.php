<?php if (isset($pager) && $pager->getPageNum()>1) { ?>
<div class="pager">
	<?php if ($pager->isFirstPage()) { ?>
		<a class="btn btn-default btn-lg btn-block next" href="<?php echo $pager->getNextPageUrl(); ?>" title="<?php echo __('Next'); ?>"><?php echo __('Next'); ?></a>
	<?php } else if($pager->isLastPage()) { ?>
		<a class="btn btn-default btn-lg btn-block previous" href="<?php echo $pager->getPreviousPageUrl(); ?>" title="<?php echo __('Previous'); ?>"><?php echo __('Previous'); ?></a>
	<?php } else { ?>
		<div class="col2-set">
			<a class="btn btn-default btn-lg previous col-1" href="<?php echo $pager->getPreviousPageUrl(); ?>" title="<?php echo __('Previous'); ?>"><?php echo __('Previous'); ?></a>
			<a class="btn btn-default btn-lg next col-2" href="<?php echo $pager->getNextPageUrl(); ?>" title="<?php echo __('Next'); ?>"><?php echo __('Next'); ?></a>
		</div>
	<?php } ?>
</div>
<?php } ?>
