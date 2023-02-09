<?php if (PRODUCT_LIST_SHOW_FILTER==1&&isset($productFilter)&&($productFilterCurrentCount > 0||$productFilterListCount > 0)) { ?>
<div class="pc-filter filter hidden-xs">
	<?php if ($productFilterCurrentCount > 0) { ?>
	<dl class="currently">
		<dd>
			<?php foreach ($productFilter as $key => $val) { ?>
			<?php if (not_null($val['current'])) { ?>
    			<a title="<?php echo __('Remove This Item'); ?>" href="<?php echo href_link($current_page, get_all_get_params(array('page', 'limit', 'mode', 'sort', $key))); ?>" rel="external nofollow">
    			<span><span><strong><?php echo $val['title']; ?>:</strong> <?php echo $val['current']; ?></span></span>
    			</a>
				<?php } ?>
			<?php }?>
			<a href="<?php echo href_link($current_page, get_all_get_params(array_merge(array('page', 'limit', 'mode', 'sort'), array_keys($productFilter)))); ?>" rel="external nofollow"><?php echo __('Clear All'); ?></a>
		</dd>
	</dl>
	<?php } ?>

	<?php if ($productFilterListCount > 0) { ?>
	<dl>
	<?php foreach ($productFilter as $key => $val) { ?>
	<?php if (count($val['list'])>0) { ?>
		<dd>
		<select class="form-control" onchange="setLocation(this.value);">
		<option value=""><?php echo $val['title']; ?></option>
		<?php foreach ($val['list'] as $_key => $_val) { ?>
			<option value="<?php echo href_link($current_page, get_all_get_params(array('page', 'limit', 'mode', 'sort', $key)) . $key . '=' . urlencode($_key)); ?>"<?php if (isset($val['current']) && $val['current'] == $_key) { ?> selected="selected"<?php } ?>>
                <?php echo $_key; ?> (<?php echo $_val; ?>)
            </option>
		<?php } ?>
		</select>
		</dd>
	<?php } ?>
	<?php } ?>
	</dl>
	<?php } ?>
</div>
<?php } ?>

<div class="m-filter visible-xs">
	<div class="sorter<?php if (MOBILE_PRODUCT_LIST_SHOW_FILTER==1&&isset($productFilter)&&($productFilterCurrentCount > 0||$productFilterListCount > 0)) { ?> toolbar-filter<?php } ?>">
		<ul>
			<?php if (MOBILE_PRODUCT_LIST_SHOW_FILTER==1&&isset($productFilter)&&($productFilterCurrentCount > 0||$productFilterListCount > 0)) { ?>
				<li>
					<a href="javascript:void(0)" class="sorter-filter" id="fixFilter"><i class="iconfont">&#xe6aa;</i> <?php echo __('Filter') ?><i class="iconfont">&#xe69b;</i></a>
					<a href="javascript:void(0)" class="sorter-filter" id="floatFilter"><?php echo __('Filter') ?></a>
				</li>
				<script language="javascript" type="text/javascript">
					$(function () {
						var floatFil = $('#floatFilter');
						var filterCon = $('#filterModal');
						var offsetTop = $('#fixFilter').offset().top;
						floatFil.click(function(){
							$('html,body').animate({scrollTop: offsetTop-20});
							if (filterCon.is(':hidden')){
								filterCon.slideDown('slow');
							}
						});

						$('#fixFilter').click(function(){
							filterCon.slideToggle('slow');
						});

						$(window).scroll(function(){
							if (filterCon.is(':hidden')) {
								if ($(this).scrollTop() > offsetTop){
									floatFil.fadeIn();
								} else {
									floatFil.fadeOut();
								}
							}else {
								var hidden = $('#filHidden').offset().top;
								if ($(this).scrollTop() > hidden) {
									floatFil.fadeIn();
								}
								else {
									floatFil.fadeOut();
								}
							}
						});
					});
				</script>
			<?php } ?>
			<?php if (count($toolbarSort['available']) > 0) { ?>
				<li class="sort-by">
					<select class="form-control" onchange="setLocation(this.value);">
						<option value=""><?php echo __('Sort By'); ?></option>
						<?php foreach ($toolbarSort['available'] as $key => $val) { ?>
							<option<?php if ($val['selected']) { ?> selected="selected"<?php } ?> value="<?php echo href_link($current_page, get_all_get_params(array('page', 'sort')) . 'sort=' . $key); ?>"><?php echo $val['name']; ?></option>
						<?php } ?>
					</select>
				</li>
			<?php } ?>
		</ul>
	</div>
	<?php if (MOBILE_PRODUCT_LIST_SHOW_FILTER==1&&isset($productFilter)&&($productFilterCurrentCount > 0||$productFilterListCount > 0)) { ?>
		<div class="filter-modal" id="filterModal">
			<form method="get" action="">
				<?php if ($current_page == 'search') { ?>
					<?php if (USE_URL_REWRITE == 0){ ?>
						<input type="hidden" value="search" name="main_page">
					<?php } ?>
					<div class="no-display">
						<input type="hidden" value="<?php echo $_GET['q']; ?>" name="q" />
					</div>
				<?php } ?>
				<?php if ($current_page == 'category') { ?>
					<?php if (USE_URL_REWRITE == 0){ ?>
						<input type="hidden" value="category" name="main_page">
						<input type="hidden" value="<?php echo $categoryInfo['category_id']; ?>" name="cID">
					<?php } ?>
				<?php } ?>
				<div class="modal-header">
					<h3><?php echo __('Filter By'); ?>
						<button class="filter-apply btn btn-default" type="submit"><?php echo __('Apply'); ?></button>
					</h3>
				</div>
				<div class="modal-body">
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
									<li>
										<a class="btn btn-black" title="<?php echo __('Remove This Item'); ?>" href="<?php echo href_link($current_page, get_all_get_params(array('page', 'limit', 'mode', 'sort', $key))); ?>" rel="external nofollow">
											<?php echo $val['title']; ?>: <?php echo $val['current']; ?> <i class="iconfont">&#xe601;</i>
										</a>
									</li>
								<?php } ?>
							<?php }?>
							<li><a class="btn btn-black btn-block" href="<?php echo href_link($current_page, get_all_get_params(array_merge(array('page', 'limit', 'mode', 'sort'), array_keys($productFilter)))); ?>" rel="external nofollow"><?php echo __('Clear All'); ?></a></li>
						</ul>
					<?php } ?>
					<div class="filter-bottom" id="filHidden"></div>
				</div>
			</form>
		</div>
	<?php } ?>
</div>
