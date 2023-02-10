<div class="block block-category">
	<div class="block-title">
        <strong><span><?php echo __('Category'); ?></span></strong>
    </div>
    <div class="block-content">
    	<?php echo $category_tree->buildAllTree(); ?>
    </div>
</div>
<script type="text/javascript">decorateList($('.block-category ul li'));</script>
