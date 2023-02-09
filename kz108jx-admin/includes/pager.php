<?php if (isset($pager)) { ?>
<div class="pager">

	<p class="amount">
	<?php if ($pager->getPageNum()>1) { ?>
		<?php echo sprintf('当前%s到%s 总条数%s', $pager->getFirstNum(), $pager->getLastNum(), $pager->getTotalNum()); ?>
	<?php } else { ?>
		<strong><?php echo sprintf('总条数%s', $pager->getTotalNum()); ?></strong>
	<?php } ?>
	</p>
	
	<div class="limiter">
		<label>显示条数</label>
		<select onchange="setLocation(this.value);">
        <?php foreach ($pager->getAvailableLimit() as  $_limit) { ?>
            <option value="<?php echo $pager->getLimitUrl($_limit); ?>"<?php if ($pager->isLimitCurrent($_limit)) { ?> selected="selected"<?php } ?>>
                <?php echo $_limit; ?>
            </option>
        <?php } ?>
        </select>
	</div>
	
	<?php if ($pager->getPageNum()>1) { ?>
	<div class="pages">
		<ol>
        <?php if ($pager->canShowFirst()) { ?>
            <li><a class="first" href="<?php echo $pager->getFirstPageUrl(); ?>">|<</a></li>
        <?php } ?>
        
        <?php if (!$pager->isFirstPage()) { ?>
        	<li><a class="previous" href="<?php echo $pager->getPreviousPageUrl(); ?>" title="Previous"><</a>
        <?php } ?>
        
        <?php foreach ($pager->getPages() as $_page) { ?>
            <?php if ($pager->isPageCurrent($_page)) { ?>
                <li class="current"><span><?php echo $_page; ?></span></li>
            <?php } else { ?>
                <li><a href="<?php echo $pager->getPageUrl($_page); ?>"><?php echo $_page; ?></a></li>
            <?php } ?>
        <?php } ?>
 
        <?php if (!$pager->isLastPage()) { ?>
        	<li><a class="next" href="<?php echo $pager->getNextPageUrl(); ?>" title="Next">></a>
        <?php } ?>
        
        <?php if ($pager->canShowLast()) { ?>
          <li><a class="last" href="<?php echo $pager->getLastPageUrl(); ?>">>|</a></li>
        <?php } ?>
        </ol>
	</div>
	<?php } ?>
	
</div>
<?php } ?>