<?php include(DIR_FS_CATALOG_MODULES . get_module_directory('sidebar/cms_page.php')); ?>
<div class="page-title">
	<h1><?php echo __($cmsPageInfo['name']); ?></h1>
    <i class="iconfont" id="expandBtn">&#xe609;</i>
</div>
<div class="cms-list">
    <ul>
        <?php foreach ($cmsPageSidebarList as $val) { ?>
            <?php if(isset($_GET['cpID'])&&$_GET['cpID']==$val['cms_page_id']) { ?>
                <li><strong><?php echo __($val['name']); ?></strong></li>
            <?php } else {?>
                <li><a href="<?php echo href_link(FILENAME_CMS_PAGE, 'cpID=' . $val['cms_page_id']); ?>" rel="external nofollow"><?php echo __($val['name']); ?></a></li>
            <?php } ?>
        <?php } ?>
    </ul>
</div>
<div class="std">
<?php echo $cmsPageInfo['content']; ?>
</div>
<script type="text/javascript">
    $('#expandBtn').on('click', function(){
        var $this = $(this);
        if ($this.hasClass('on')){
            $this.removeClass('on').html('&#xe609;');
            $('.cms-list').stop(true, true).slideUp(300);
        } else {
            $(this).addClass('on').html('&#xe643;');
            $('.cms-list').stop(true, true).slideDown(300);
        }
    });
</script>
