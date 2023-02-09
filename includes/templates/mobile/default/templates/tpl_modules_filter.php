<?php if (MOBILE_PRODUCT_LIST_SHOW_FILTER==1&&isset($productFilter)&&($productFilterCurrentCount > 0||$productFilterListCount > 0)) { ?>
<div class="filter" id="filterModal">
    <form method="get" action="">
        <?php if ($current_page == 'search') { ?>
            <div class="no-display">
                <input type="hidden" value="<?php echo $_GET['q']; ?>" name="q" />
            </div>
        <?php } ?>
        <div class="filter-header">
            <h3><?php echo __('Filter By'); ?>
                <button class="btn btn-black" type="submit"><?php echo __('Apply'); ?></button>
            </h3>
        </div>
        <div class="filter-content">
            <?php if ($productFilterListCount > 0) { ?>
                <ul>
                    <?php foreach ($productFilter as $key => $val) { ?>
                        <?php if (!empty($val['current'])) { ?>
                            <li class="no-display"><input type="hidden" name="<?php echo $key; ?>" value="<?php echo $val['current']; ?>" /></li>
                        <?php } ?>
                        <?php if (count($val['list'])>0) { ?>
                            <li>
                                <select class="form-control" name="<?php echo $key; ?>">
                                    <option value=""><?php echo $val['title']; ?></option>
                                    <?php foreach ($val['list'] as $_key => $_val) { ?>
                                        <option value="<?php echo $_key; ?>"<?php if (isset($val['current']) && $val['current'] == $_key) { ?> selected="selected"<?php } ?>>
                                            <?php echo $_key; ?> (<?php echo $_val; ?>)
                                        </option>
                                    <?php } ?>
                                </select>
                            </li>
                        <?php } ?>
                    <?php } ?>
                </ul>
            <?php } ?>
            <?php if ($productFilterCurrentCount > 0) { ?>
                <ul class="selected">
                    <?php foreach ($productFilter as $key => $val) { ?>
                        <?php if (not_null($val['current'])) { ?>
                            <li class="currently">
                                <a class="btn btn-default btn-remove" title="<?php echo __('Remove This Item'); ?>" href="<?php echo href_link($current_page, get_all_get_params(array('page', 'limit', 'mode', 'sort', $key))); ?>" rel="external nofollow">
                                    <?php echo $val['title']; ?>: <?php echo $val['current']; ?> <i class="iconfont">&#xe601;</i>
                                </a>
                            </li>
                        <?php } ?>
                    <?php }?>
                    <li class="currently clear-all"><a class="btn btn-default btn-block" href="<?php echo href_link($current_page, get_all_get_params(array_merge(array('page', 'limit', 'mode', 'sort'), array_keys($productFilter)))); ?>" rel="external nofollow"><?php echo __('Clear All'); ?></a></li>
                </ul>
            <?php } ?>
            <div class="filter-bottom" id="filHidden"></div>
        </div>
    </form>
</div>
<script language="javascript" type="text/javascript">
$(function () {
    var fixFil = $('#fixFilter');
    var floatFil = $('#floatFilter');
    var filterCon = $('#filterModal');
    var offsetTop = fixFil.offset().top-70;

    fixFil.click(function(){filterCon.slideToggle('slow');});
    floatFil.click(function(){
        $('html,body').animate({scrollTop: offsetTop-20});
        if (filterCon.is(':hidden')){
            filterCon.slideDown('slow');
        }
    });

    $(window).scroll(function(){
        if (filterCon.is(':hidden')) {
            if ($(this).scrollTop() > offsetTop){floatFil.fadeIn();}else{floatFil.fadeOut();}
        }else {
            var hidden = $('#filHidden').offset().top;
            if ($(this).scrollTop() > hidden){floatFil.fadeIn();}else{floatFil.fadeOut();}
        }
    });
});
</script>
<?php } ?>
