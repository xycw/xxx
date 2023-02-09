<div class="header-container">
	<div class="container">
		<?php 
		$STORE_WELCOME_VALUE = explode("_",STORE_WELCOME);
		?>
		<?php

		if (count($STORE_WELCOME_VALUE) > 1){?>
			<div id="WE_product">
				<?php foreach ($STORE_WELCOME_VALUE as $WELCOME_VALUES) { ?>
					<a class="item">
						<?php echo $WELCOME_VALUES;?>
					</a>
				<?php } ?>
			</div>
		<?php }else{?>
			<?php echo STORE_WELCOME;?>
		<?php }?>
		

	</div>
</div>
<div class="pc-header hidden-xs hidden-sm">
	<div class="header">
        <div class="col-md-2 no-padding">
            <ul class="logo-container">
                <li>
                    <?php if(IS_ZP == 0){ ?>
                        <a href="<?php echo href_link(FILENAME_INDEX); ?>" title="<?php echo HEADER_LOGO_ALT; ?>" class="logo">
							<span class="header-logo">
                                <svg class="logo-desktop" width="147px" height="31px" x="0px" y="0px" viewBox="0 0 147 31" style="enable-background:new 0 0 147 31;" xml:space="preserve">
									<path  d="M66,8l14.7,14.7l-2,2L66,12v17h-6V6l-2-2l2-2l0,0l0,0h6V8z M81,29h6V2h-6V29z M39,8h18V2H39V8z M90,29h27v-6H97.9H90V29z
										 M30,29h6V2h-6V29z M39,29h18v-6H39V29z M51,12.5H39v6h12V12.5z M11.5,13.5l2,2l-2,2L23,29h4v-4l-9.5-9.5L27,6l-4-4L11.5,13.5z
										 M6,11.5V2H0v27h6v-9.5l4-4L6,11.5z M113,0l-2,2H90v6h15L94,19l3.9,4l15-15h4.1V3.9V2h-2L113,0z M144,7.1l-4.3,4.3
										c0.8,1.2,1.2,2.6,1.2,4.1c0,4.1-3.4,7.5-7.5,7.5c-1.5,0-2.9-0.5-4.1-1.2l-4.3,4.3c2.3,1.8,5.2,3,8.4,3c7.5,0,13.5-6,13.5-13.5
										C147,12.3,145.9,9.4,144,7.1z M126,15.5c0-4.1,3.4-7.5,7.5-7.5c1.5,0,2.9,0.5,4.1,1.2l4.3-4.3c-2.3-1.8-5.2-3-8.4-3
										C126,2,120,8,120,15.5c0,3.2,1.1,6.1,3,8.4l4.3-4.3C126.5,18.4,126,17,126,15.5z">
                                    </path>
								</svg>
                            </span>
                        </a>
                    <?php }else{ ?>
                        <a class="logo_zp" href="<?php echo href_link(FILENAME_INDEX); ?>">
                            <img src="<?php echo DIR_WS_TEMPLATE_IMAGES; ?>logo_zp.png" alt="<?php echo HEADER_LOGO_ALT; ?>" title="<?php echo HEADER_LOGO_ALT; ?>"/>
                        </a>
                    <?php } ?>
                </li>
            </ul>
        </div>
		<div class="col-md-5 no-padding">
			<div class="nav-container">
				<div id="nav">
					<?php echo $category_tree->buildHeaderTree(0,3); ?>
				</div>
			</div>
		</div>
		<div class="col-md-5 no-padding">
			<ul class="links search-box">
				<li><a title="<?php echo __('Newsletter'); ?>" href="<?php echo href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL'); ?>"><span><?php echo __('Newsletter'); ?></span></a></li>
				<li>
                    <a href="javascript:;" class="toggle-search">
                        <span>
                            <svg t="1620353340835" class="icon" viewBox="0 0 1032 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="1873" width="25.1953125" height="25" xmlns:xlink="http://www.w3.org/1999/xlink"><defs><style type="text/css"></style></defs><path d="M969.975 905.855L842.142 796.532a39.382 39.382 0 1 0-55.686 55.686L914.29 961.54a39.382 39.382 0 1 0 55.686-55.686z" p-id="1874" fill="#000000"></path><path d="M468.41 841.112a381.135 381.135 0 1 0 0-762.27 381.135 381.135 0 0 0 0 762.27z m0 78.763a459.898 459.898 0 1 1 0-919.796 459.898 459.898 0 0 1 0 919.796z" p-id="1875" fill="#000000"></path></svg>
                        </span>
                    </a>
                </li>
				<li><a class="link-account" title="<?php echo __('Account'); ?>" href="<?php echo href_link(FILENAME_ACCOUNT, '', 'SSL'); ?>"><span><i class="iconfont icon-Userpersonavtar f-25"></i></span></a></li>
				<li><a class="link-cart" title="<?php echo __('Cart'); ?>" href="<?php echo href_link(FILENAME_SHOPPING_CART, '', 'SSL'); ?>"><span><i class="iconfont icon-cart f-25"></i><?php echo ($_SESSION['shopping_cart']->getItems()>=0)?__('(%s)', $_SESSION['shopping_cart']->getItems()):__(''); ?></span></a></li>
				<li><?php require($template->get_template_dir('tpl_currency_header.php', DIR_WS_TEMPLATE, $current_page, 'sidebar') . 'tpl_currency_header.php'); ?></li>
			</ul>
		</div>
	</div>
</div>
<div class="pc-search-box">
	<div class="close-pc-search-box"><i class="iconfont icon-close"></i></div>
	<div class="link-search">
		<form method="get" action="<?php echo href_link(FILENAME_SEARCH); ?>" id="pc_search_mini_form">
			<div class="form-search">
				<?php if (USE_URL_REWRITE == 0){ ?>
						<input type="hidden" value="search" name="main_page">
				<?php } ?>
				<div class="search-input"><i class="iconfont">&#xe61d;</i><input type="text" class="input-text" value="<?php echo isset($_GET['q'])?$_GET['q']:__(''); ?>" name="q" id="pcSearch" placeholder="<?php echo __('Search'); ?>" /></div>
				<div class="search-btn">
					<button class="button" title="<?php echo __('Search'); ?>" type="submit"></button>
				</div>
			</div>
		</form>
	</div>
</div>
<?php echo ONLINE_SERVICE; ?>
<div class="mobile-header hidden-md hidden-lg">
	<!-- <div class="header-container hiddem-lg hidden-md">
		<div class="container pd-lr5">
			
		</div>
	</div> -->
	<div class="header">
		<div class="menu-header-logo col-xs-4 col-sm-4">
			<ul>
				<li>
					<?php if(IS_ZP == 0){ ?>
						<a href="<?php echo href_link(FILENAME_INDEX); ?>" title="<?php echo HEADER_LOGO_ALT; ?>" class="logo">
							<span class="header-logo">
                                <svg class="logo-mobile" width="118px" height="26px" x="0px" y="0px" viewBox="0 0 118 26" style="enable-background:new 0 0 118 26;" xml:space="preserve">
									<path d="M29,24h-5V2h5V24z M70,2h-5v22h5V2z M46,19H31v5h15V19z M94,19H72v5h22V19z M46,2H31v5h15V2z M22,5.5L18.5,2l-9.3,9.3
										l0.3,0.3l1.3,1.4l-1.4,1.4l-0.3,0.3l9.3,9.3H22v-3.5L14.5,13L22,5.5z M5,9.7V2H0v22h5v-8l3-3L5,9.7z M41,10.5H31v5h10V10.5z M94,2
										h-2l-1.5-1.5L89,2H72v5h12l-8.5,8.5L79,19L91,7h3V2z M115.5,5 M98.5,22 M118,2 M96,24 M112.2,10c0.5,0.9,0.8,1.9,0.8,3
										c0,3.3-2.7,6-6,6c-1.1,0-2.1-0.3-3-0.8l-3.6,3.6c1.8,1.4,4.1,2.2,6.6,2.2c6.1,0,11-4.9,11-11c0-2.5-0.8-4.8-2.2-6.6L112.2,10z
										 M101,13c0-3.3,2.7-6,6-6c1.1,0,2.1,0.3,3,0.8l3.6-3.6C111.8,2.8,109.5,2,107,2c-6.1,0-11,4.9-11,11c0,2.5,0.8,4.8,2.2,6.6l3.6-3.6
										C101.3,15.1,101,14.1,101,13z M65,19L53,7V2h-5v0l0,0l-1.5,1.5L48,5v19h5V10l10.5,10.5L65,19z"></path>
								</svg>
                            </span>
						</a>
					<?php }else{ ?>
						<a class="logo_zp" href="<?php echo href_link(FILENAME_INDEX); ?>">
                            <img src="<?php echo DIR_WS_TEMPLATE_IMAGES; ?>logo_zp.png" alt="<?php echo HEADER_LOGO_ALT; ?>" title="<?php echo HEADER_LOGO_ALT; ?>"/>
                        </a>
					<?php } ?>
				</li>
			</ul>
		</div>
		<div class="menu-header-right col-xs-8 col-sm-8">
			<ul>
<!--				<li><a class="link-account" title="--><?php //echo __('Account'); ?><!--" href="--><?php //echo href_link(FILENAME_ACCOUNT, '', 'SSL'); ?><!--"><i class="iconfont icon-Userpersonavtar f-25"></i></a></li>-->
                <li class="mobile-search"><i class="iconfont icon--search f-20"></i></li>
                <li><a class="link-cart a-right" href="<?php echo href_link(FILENAME_SHOPPING_CART, '', 'SSL'); ?>" rel="external nofollow"><i class="iconfont icon-cart f-25"></i><?php echo ($_SESSION['shopping_cart']->getItems()>=0)?__('<span>%s</span>', $_SESSION['shopping_cart']->getItems()):__(''); ?></a></li>
                <li><a href="javascript:;" class="category-tree a-left"><i class="iconfont icon-menu f-20"></i></a></li>
            </ul>
		</div>
	</div>
    <div class="left-menu" id="menu" data-scroll="">
        <div class="layer-tree" onclick="hideCategory();"></div>
        <span class="button btn-layer" onclick="hideCategory();"><i class="iconfont f-20">&#xe601;</i></span>
        <div class="left-category">
            <div class="category-list">
                <ul class="level1">
                    <?php
                    $categoryTree = $category_tree->getData();
                    ksort($categoryTree);
                    ?>
                    <?php if (isset($categoryTree[0])) { ?>
                        <?php foreach ($categoryTree[0] as $val) { ?>
                            <?php if (isset($categoryTree[$val['id']])) { ?>
                                <li class="category-top">
                                    <span class="all-category" onclick="$(this).nextAll('ul.mobile-memu').show().animate({left:0},200);"><i class="iconfont">&#xe92e;</i></span>
                                    <a class="oneline" href="<?php echo href_link(FILENAME_CATEGORY, 'cID=' . $val['id']); ?>"><?php echo $val['name']; ?></a>
                                    <ul class="mobile-memu">
                                        <li class="category-title"><a href="javascript:;" class="return" onclick="$(this).closest('ul.mobile-memu').animate({left:'100%'},200).hide(200);"><i class="iconfont">&#xe92d;</i><?php echo $val['name']; ?></a></li>
                                        <?php foreach ($categoryTree[$val['id']] as $v) { ?>
                                            <?php if (isset($categoryTree[$v['id']])) { ?>
                                                <li class="category-top">
                                                    <span class="all-category" onclick="$(this).nextAll('ul.mobile-memu').slideToggle().end().find('i').toggleClass('icon-down icon-up');"><i class="iconfont icon-down"></i></span>
                                                    <a class="oneline" href="<?php echo href_link(FILENAME_CATEGORY, 'cID=' . $v['id']); ?>"><?php echo $v['name']; ?></a>
                                                    <ul class="mobile-memu mobile-memu1">
                                                        <?php foreach ($categoryTree[$v['id']] as $g) { ?>
                                                            <li class="category-product">
                                                                <a class="oneline" href="<?php echo href_link(FILENAME_CATEGORY, 'cID=' . $g['id']); ?>"><?php echo $g['name']; ?></a>
                                                            </li>
                                                        <?php } ?>
                                                    </ul>
                                                </li>
                                            <?php } else { ?>
                                                <li class="category-product">
                                                    <a class="oneline" href="<?php echo href_link(FILENAME_CATEGORY, 'cID=' . $v['id']); ?>"><?php echo $v['name']; ?></a>
                                                </li>
                                            <?php } ?>
                                        <?php } ?>
                                    </ul>
                                </li>
                            <?php } else { ?>
                                <li class="category-product">
                                    <a class="oneline" href="<?php echo href_link(FILENAME_CATEGORY, 'cID=' . $val['id']); ?>"><?php echo $val['name']; ?></a>
                                </li>
                            <?php } ?>
                        <?php } ?>
                    <?php } ?>
                    <li><a href="<?php echo href_link(FILENAME_INDEX); ?>"><?php echo __('Home'); ?></a></li>
                    <?php if (isset($_SESSION['customer_id'])) { ?>
                        <li><a title="<?php echo __('Log Out'); ?>" href="<?php echo href_link(FILENAME_LOGOUT, '', 'SSL'); ?>"><?php echo __('Log Out'); ?></a></li>
                    <?php } else { ?>
                        <li><a title="<?php echo __('Log In'); ?>" href="<?php echo href_link(FILENAME_LOGIN, '', 'SSL'); ?>"><?php echo __('Log In'); ?></a></li>
                    <?php } ?>
                    <?php if (isset($_SESSION['customer_id'])) { ?>
                        <li><a title="<?php echo __('My Account'); ?>" href="<?php echo href_link(FILENAME_ACCOUNT, '', 'SSL'); ?>"><?php echo __('My Account'); ?></a></li>
                    <?php } else { ?>
                        <li><a title="<?php echo __('Creat Account'); ?>" href="<?php echo href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL'); ?>"><?php echo __('Creat Account'); ?></a></li>
                    <?php } ?>
                    <li><a href="<?php echo href_link(FILENAME_SEARCH_ORDER, '', 'SSL'); ?>"><?php echo __('Order Check'); ?></a></li>
                    <li class="cms">
                        <a class="title" href="javascript:;"><?php echo __('Company Info'); ?></a>
                        <ul class="links">
                            <li><a title="<?php echo __('About Us'); ?>" href="<?php echo href_link(FILENAME_CMS_PAGE, 'cpID=1'); ?>" rel="external nofollow"><?php echo __('About Us'); ?></a></li>
                            <li><a title="<?php echo __('Contact Us'); ?>" href="<?php echo href_link(FILENAME_CMS_PAGE, 'cpID=2'); ?>" rel="external nofollow"><?php echo __('Contact Us'); ?></a></li>
                            <li><a title="<?php echo __('Privacy & Security'); ?>" href="<?php echo href_link(FILENAME_CMS_PAGE, 'cpID=3'); ?>" rel="external nofollow"><?php echo __('Privacy & Security'); ?></a></li>
                            <li><a title="<?php echo __('Returns Policy'); ?>" href="<?php echo href_link(FILENAME_CMS_PAGE, 'cpID=5'); ?>" rel="external nofollow"><?php echo __('Returns Policy'); ?></a></li>
                            <li><a title="<?php echo __('Site Map'); ?>" href="<?php echo href_link(FILENAME_SITE_MAP); ?>" rel="external nofollow"><?php echo __('Site Map'); ?></a></li>
                        </ul>
                    </li>
                    <li class="currency"><?php require($template->get_template_dir('tpl_currency_header.php', DIR_WS_TEMPLATE, $current_page, 'sidebar') . 'tpl_currency_header.php'); ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="xxx/includes/templates/zc_hu01/js/owlcarousel/owl.carousel.js"></script>
<script language="javascript" type="text/javascript">
$(function () {
				$('#WE_product').owlCarousel({
					items:1,
					loop:true,
					nav:false,
					dots:false,
					autoplay:true,
					autoplayTimeout:3000,
					autoplayHoverPause:true
				});
			});
	
$(function(){
	var mTop = $('.mobile-header').offset().top;
	var pTop = $('.pc-header').offset().top;
	$(window).scroll(function(){
        let scrolltop =  $(document).scrollTop();
		if ($(window).width() < 992){
            if(scrolltop > mTop){
                $('.mobile-header .header').addClass('header-fixed');
                $('.pc-search-box').css('top','0');
            } else {
                $('.mobile-header .header').removeClass('header-fixed');
                $('.pc-search-box').css('top','106px');
            }
        }else{
            if(scrolltop > pTop){
                $('.pc-header .header').addClass('header-fixed');
                $('.header-fixed-logo').addClass('remove-transparent');
                $('.pc-search-box').css('top','0');
            } else {
                $('.pc-header .header').removeClass('header-fixed');
                $('.header-fixed-logo').removeClass('remove-transparent');
                $('.pc-search-box').css('top','50px');
            }
        }
	});

    $('.category-tree').on('click',function(){
        $('.left-menu').fadeIn();
        $('html').addClass('noscroll');
        $.smartScroll('menu','.category-list');
    });

    $('.cms').on('click',function(){
        $(this).find('.links').slideToggle(250);
        $(this).toggleClass('active');
    });

	
	$('.toggle-search,.mobile-search').on('click',function(){
		$('.pc-search-mask').fadeIn();
		$('.pc-search-box').fadeIn();
		$('.left-category').removeClass('show-left-category');
		$('.category-tree').find('i.iconfont').addClass('icon-menu').removeClass('icon-close');
		$('html').removeClass('noscroll');
	});
	
	$('.pc-search-mask').on('click',function(){
		$('.pc-search-mask').fadeOut();
		$('.pc-search-box').fadeOut();
	});
	
	$('.close-pc-search-box').on('click',function(){
		$('.pc-search-mask').fadeOut();
		$('.pc-search-box').fadeOut();
	});
	
	$('.close-welcome').on('click',function(){
		$('.header-container').fadeOut();
	});

});
function hideCategory() {
    $('.left-menu').fadeOut();
    $('html').removeClass('noscroll');
}
</script>