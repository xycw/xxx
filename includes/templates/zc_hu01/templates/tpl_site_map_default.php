<div class="page-sitemap">
    <div class="page-title title-buttons">
        <h1><?php echo __('Categories'); ?></h1>
    </div>
    <?php if (count($siteMapList)>0) { ?>
    <div class="sitemap">
    <?php foreach ($siteMapList as $val) { ?>
		<p><strong><a href="<?php echo $val['link']; ?>"><?php echo $val['name']; ?></a></strong></p>
        <?php foreach ($val['children'] as $v) { ?>
        <p><a href="<?php echo $v['link']; ?>"><?php echo $v['name']; ?></a></p>
        <?php } ?>
	<?php } ?>
    </div>
    <?php } else { ?>
    	<p class="note-msg"><?php echo __('There are no sitemap matching the selection.'); ?></p>
    <?php } ?>
</div>
