<div class="header-container">
	<p class="welcome-msg"><?php echo STORE_WELCOME; ?></p>
	<div class="header">
		<div class="nav nav-menu"><a id="modalOpen" href="javascript:;" rel="external nofollow"><i class="iconfont f-25">&#xe64c;</i></a></div>
		<div class="nav nav-logo"><a class="logo" href="<?php echo href_link(FILENAME_INDEX); ?>"><img src="<?php echo DIR_WS_TEMPLATE_IMAGES; ?>logo.png" alt="logo" title="easyshop"/></a></div>
		<div class="nav nav-cart"><a href="<?php echo href_link(FILENAME_SHOPPING_CART, '', 'SSL'); ?>" rel="external nofollow"><i class="iconfont f-25">&#xe600;</i><?php if ($_SESSION['shopping_cart']->getItems()>0) { ?><span class="badge"><?php echo $_SESSION['shopping_cart']->getItems(); ?></span><?php } ?></a></div>
	</div>
</div>
<div class="aside" id="aside">
	<div class="layer" id="layer"></div>
	<div class="modal">
		<div class="modal-header">
			<div class="header-search" id="header-search">
				<form method="get" action="<?php echo href_link(FILENAME_SEARCH); ?>" id="search_mini_form">
					<div class="form-search">
						<?php if (USE_URL_REWRITE == 0){ ?>
							<input type="hidden" value="search" name="main_page">
						<?php } ?>
						<input type="text" class="form-control" value="" name="q" id="search" maxlength="100" placeholder="Search" />
						<button class="btn" title="<?php echo __('Go'); ?>" type="submit" onclick=""><i class="iconfont f-25">&#xe630;</i></button>
					</div>
				</form>
			</div>
			<div class="modal-close"><i class="iconfont f-25" id="modalClose">&#xe601;</i></div>
		</div>
		<div class="modal-content" id="firstScroller">
			<ul class="level1">
				<li><a title="<?php echo __('Order Check'); ?>" href="<?php echo href_link(FILENAME_SEARCH_ORDER, '', 'SSL'); ?>" rel="external nofollow"><?php echo __('Order Check'); ?></a></li>
				<?php if (isset($_SESSION['customer_id'])) { ?>
					<li><a title="<?php echo __('Log Out'); ?>" href="<?php echo href_link(FILENAME_LOGOUT, '', 'SSL'); ?>"><?php echo __('Log Out'); ?></a></li>
					<li><a title="<?php echo __('My Account'); ?>" href="<?php echo href_link(FILENAME_ACCOUNT, '', 'SSL'); ?>" rel="external nofollow"><?php echo __('My Account'); ?></a></li>
				<?php } else { ?>
					<li><a title="<?php echo __('Log In'); ?>" href="<?php echo href_link(FILENAME_LOGIN, '', 'SSL'); ?>"><?php echo __('Log In'); ?></a></li>
					<li><a title="<?php echo __('Create Account'); ?>" href="<?php echo href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL'); ?>"><?php echo __('Create Account'); ?></a></li>
				<?php } ?>
				<li><?php require($template->get_template_dir('tpl_modules_currency.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_modules_currency.php'); ?></li>
				<?php
				$categoryTree = $category_tree->getData();
				ksort($categoryTree);
				?>
				<?php if (isset($categoryTree[0])) { ?>
					<?php foreach ($categoryTree[0] as $val) { ?>
						<?php if (isset($categoryTree[$val['id']])) { ?>
							<li class="category-top">
								<a class="level1-title oneline" href="<?php echo href_link(FILENAME_CATEGORY, 'cID=' . $val['id']); ?>"><?php echo $val['name']; ?></a>
								<a class="btn-more" href="javascript:;" onclick=""><i class="iconfont f-20">&#xe69b;</i></a>
								<ul class="level2">
									<li class="category-title"><a href="javascript:;" onclick="$(this).parents('ul.level2').animate({left:-500},300).hide(400);"><i class="iconfont f-20">&#xe69a;</i><?php echo $val['name']; ?></a></li>
									<?php foreach ($categoryTree[$val['id']] as $v) { ?>
										<li class="category-product">
											<a class="level2-title oneline" href="<?php echo href_link(FILENAME_CATEGORY, 'cID=' . $v['id']); ?>"><?php echo $v['name']; ?></a>
										</li>
									<?php } ?>
								</ul>
							</li>
						<?php } else { ?>
							<li class="category-top">
								<a class="oneline" href="<?php echo href_link(FILENAME_CATEGORY, 'cID=' . $val['id']); ?>"><?php echo $val['name']; ?></a>
							</li>
						<?php } ?>
					<?php } ?>
				<?php } ?>
			</ul>
		</div>
	</div>
</div>
<script type="text/javascript"><!--
$(function(){
	// header fixed
	var offtop = $('.header').offset().top;
	$(window).scroll(function(){
		var scrolltop =  $(document).scrollTop();
		if(scrolltop>offtop){$('.header').addClass('header-fixed');}else{$('.header').removeClass('header-fixed');}
	});

	$('#modalOpen').on('click', function(){
		$('#aside').addClass('active');
		$('html').addClass('noscroll');
		scrollHack('aside', '#firstScroller');
	});

	$('#modalClose').on('click', function(){
		$('#aside').removeClass('active');
		$('html').removeClass('noscroll');
		$('ul.level2').animate({left:-500},300).hide();
	});

	$('.btn-more').on('click', function(){$(this).next('ul.level2').show().animate({left:0},300);});
});
//--></script>
