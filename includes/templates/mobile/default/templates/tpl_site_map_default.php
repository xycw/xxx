<div class="page-sitemap">
    <div class="page-title title-buttons">
        <h1><?php echo __('Categories'); ?></h1>
    </div>
    <?php if (count($siteMapList)>0) { ?>
    <dl class="sitemap">
    <?php foreach ($siteMapList as $val) { ?>
		<dt><a href="<?php echo $val['link']; ?>"><?php echo $val['name']; ?></a></dt>
        <?php foreach ($val['children'] as $v) { ?>
        <dd><a href="<?php echo $v['link']; ?>"><?php echo $v['name']; ?></a></dd>
        <?php } ?>
	<?php } ?>
    </dl>
    <?php } else { ?>
    	<p class="note-msg"><?php echo __('There are no sitemap matching the selection.'); ?></p>
    <?php } ?>
</div>
