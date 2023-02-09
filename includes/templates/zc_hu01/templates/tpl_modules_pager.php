<?php if (isset($pager)) { ?>
<div class="pager hidden-xs">
	<p class="amount">
	<?php if ($pager->getPageNum()>1) { ?>
		<?php echo __('Items <span>%s</span> to <span>%s</span> of <span>%s</span> total', $pager->getFirstNum(), $pager->getLastNum(), $pager->getTotalNum()); ?>
	<?php } else { ?>
		<strong><span><?php echo $pager->getTotalNum(); ?></span> <?php echo __('Item(s)'); ?></strong>
	<?php } ?>
	</p>
	<div class="limiter">
		<label><?php echo __('Show'); ?></label>
		<select onchange="setLocation(this.value);">
        <?php foreach ($pager->getAvailableLimit() as  $_limit) { ?>
            <option value="<?php echo $pager->getLimitUrl($_limit); ?>"<?php if ($pager->isLimitCurrent($_limit)) { ?> selected="selected"<?php } ?>>
                <?php echo $_limit; ?>
            </option>
        <?php } ?>
        </select>
	</div>
	<?php if (isset($toolbarSort['available']) && count($toolbarSort['available']) > 0) { ?>
		<div class="sort-by">
			<label><?php echo __('Sort By'); ?></label>
			<select onchange="setLocation(this.value);">
				<?php foreach ($toolbarSort['available'] as $key => $val) { ?>
					<option<?php if ($val['selected']) { ?> selected="selected"<?php } ?> value="<?php echo href_link($current_page, get_all_get_params(array('page', 'sort')) . 'sort=' . $key); ?>"><?php echo $val['name']; ?></option>
				<?php } ?>
			</select>
		</div>
	<?php } ?>
	<?php if ($pager->getPageNum()>1) { ?>
	<div class="pages">
		<strong><?php echo __('Page'); ?>:</strong>
		<ol>
        <?php if (!$pager->isFirstPage()) { ?>
            <li><a class="previous" href="<?php echo $pager->getPreviousPageUrl(); ?>" title="<?php echo __('Previous'); ?>"><?php echo __('Previous'); ?></a>
        <?php } ?>
        
        <?php if ($pager->canShowFirst()) { ?>
            <li><a class="first" href="<?php echo $pager->getFirstPageUrl(); ?>">1</a></li>
        <?php } ?>
        
        <?php foreach ($pager->getPages() as $_page) { ?>
            <?php if ($pager->isPageCurrent($_page)) { ?>
                <li class="current"><?php echo $_page; ?></li>
            <?php } else { ?>
                <li><a href="<?php echo $pager->getPageUrl($_page); ?>"><?php echo $_page; ?></a></li>
            <?php } ?>
        <?php } ?>
        
        <?php if ($pager->canShowLast()) { ?>
          <li><a class="last" href="<?php echo $pager->getLastPageUrl(); ?>"><?php echo $pager->getPageNum(); ?></a></li>
        <?php } ?>
        
        <?php if (!$pager->isLastPage()) { ?>
        	<li><a class="next" href="<?php echo $pager->getNextPageUrl(); ?>" title="<?php echo __('Next'); ?>"><?php echo __('Next'); ?></a>
        <?php } ?>
        </ol>
	</div>
	<?php } ?>
</div>
<?php } ?>
<?php if (isset($pager) && $pager->getPageNum()>1) { ?>
	<div class="m-pager visible-xs">
		<?php if (!$pager->isFirstPage()) { ?>
			<a class="previous" href="<?php echo $pager->getPreviousPageUrl(); ?>" title="<?php echo __('Previous'); ?>"><i class="iconfont">&#xe69a;</i> <?php echo __('Previous'); ?></a>
		<?php } else { ?>
			<a class="previous" href="javascript:;" title="<?php echo __('Previous'); ?>"><i class="iconfont">&#xe69a;</i> <?php echo __('Previous'); ?></a>
		<?php } ?>
		<span class="page-num"><?php echo $pager->getCurrentPage(); ?> <strong>/ <?php echo $pager->getPageNum(); ?></strong></span>
		<?php if (!$pager->isLastPage()) { ?>
			<a class="next" href="<?php echo $pager->getNextPageUrl(); ?>" title="<?php echo __('Next'); ?>"><?php echo __('Next'); ?> <i class="iconfont">&#xe69b;</i></a>
		<?php } else { ?>
			<a class="next" href="javascript:;" title="<?php echo __('Next'); ?>"><?php echo __('Next'); ?> <i class="iconfont">&#xe69b;</i></a>
		<?php } ?>
	</div>
<?php } ?>
