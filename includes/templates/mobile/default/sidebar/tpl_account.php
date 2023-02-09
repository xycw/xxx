<?php include(DIR_FS_CATALOG_MODULES . get_module_directory('sidebar/account.php')); ?>
<?php if (count($accountSidebarList) > 0) {?>
<div class="title-list account-list" id="accountList">
	<ul>
		<?php foreach ($accountSidebarList as $_account) { ?>
			<li><?php echo $_account; ?></li>
		<?php } ?>
	</ul>
	<i id="expandBtn" class="iconfont">&#xe609;</i>
</div>
<script type="text/javascript">
$(function(){
	$('.title-list li').addClass('other');
	$('.title-list strong').parent('li').addClass('current').removeClass('other');

	$('#expandBtn').on('click', function(){
		var $this = $(this);
		if ($this.hasClass('on')){
			$this.removeClass('on').html('&#xe609;');
			$('#accountList').find('li.other').stop(true, true).slideUp(100);
		} else {
			$(this).addClass('on').html('&#xe643;');
			$('#accountList').find('li.other').stop(true, true).slideDown(100);
		}
	});
});
</script>
<?php } ?>
