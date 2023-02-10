<div class="header-container">
	<div class="top-container">
		<p class="welcome-msg"><?php echo STORE_WELCOME; ?></p>
		<ul class="links">
			<li class="first"><a title="<?php echo __('Order Check'); ?>" href="<?php echo href_link(FILENAME_SEARCH_ORDER, '', 'SSL'); ?>" rel="external nofollow"><?php echo __('Order Check'); ?></a></li>
			<li><a title="<?php echo __('My Account'); ?>" href="<?php echo href_link(FILENAME_ACCOUNT, '', 'SSL'); ?>" rel="external nofollow"><?php echo __('My Account'); ?></a></li>
			<li>
				<a class="top-link-cart" title="<?php echo __('My Cart'); ?>" href="<?php echo href_link(FILENAME_SHOPPING_CART, '', 'SSL'); ?>" rel="external nofollow"><?php echo ($_SESSION['shopping_cart']->getItems()>0)?__('My Cart (<span>%s</span> Items)', $_SESSION['shopping_cart']->getItems()):__('My Cart'); ?></a>
			</li>
			<?php if (isset($_SESSION['customer_id'])) { ?>
			<li class="last"><a title="<?php echo __('Log Out'); ?>" href="<?php echo href_link(FILENAME_LOGOUT, '', 'SSL'); ?>" rel="external nofollow"><?php echo __('Log Out'); ?></a></li>
			<?php } else { ?>
			<li class="last"><a title="<?php echo __('Log In'); ?>" href="<?php echo href_link(FILENAME_LOGIN, '', 'SSL'); ?>" rel="external nofollow"><?php echo __('Log In'); ?></a></li>
			<?php } ?>
		</ul>
	</div>
	<div class="header">
    	<?php if ($this_is_home_page) { ?>
    	<h1 class="logo"><strong><?php echo HEADER_LOGO_ALT; ?></strong><a href="<?php echo href_link(FILENAME_INDEX); ?>" title="<?php echo HEADER_LOGO_ALT; ?>" class="logo"><img src="<?php echo $template->get_template_dir(HEADER_LOGO_SRC, DIR_WS_TEMPLATE, $current_page, 'images') . HEADER_LOGO_SRC; ?>" alt="<?php echo HEADER_LOGO_ALT; ?>" /></a></h1>
    	<?php } else { ?>
    	<a href="<?php echo href_link(FILENAME_INDEX); ?>" title="<?php echo HEADER_LOGO_ALT; ?>" class="logo"><strong><?php echo HEADER_LOGO_ALT; ?></strong><img src="<?php echo $template->get_template_dir(HEADER_LOGO_SRC, DIR_WS_TEMPLATE, $current_page, 'images') . HEADER_LOGO_SRC; ?>" alt="<?php echo HEADER_LOGO_ALT; ?>" /></a>
		<?php } ?>
    	<div class="quick-access">
    		<form method="get" action="<?php echo href_link(FILENAME_SEARCH); ?>" id="search_mini_form">
    		<div class="form-search">
        		<label for="search"><?php echo __('Search'); ?></label>
				<?php if (USE_URL_REWRITE == 0){ ?>
					<input type="hidden" value="search" name="main_page">
				<?php } ?>
        		<input type="text" class="input-text" value="<?php echo isset($_GET['q'])?$_GET['q']:__('Search entire store here...'); ?>" name="q" id="search" onblur="if($(this).val()=='') $(this).val('<?php echo __('Search entire store here...'); ?>');" onfocus="if($(this).val()=='<?php echo __('Search entire store here...'); ?>') $(this).val('');" />
        		<button class="button" title="<?php echo __('Search'); ?>" type="submit" onclick="if($('#search').val()=='<?php echo __('Search entire store here...'); ?>') return false;"><span><span><?php echo __('Search'); ?></span></span></button>
    		</div>
			</form>
    		<?php require($template->get_template_dir('tpl_currency_header.php', DIR_WS_TEMPLATE, $current_page, 'sidebar') . 'tpl_currency_header.php'); ?>
    		<div class="nav-container">
				<div id="nav">
					<ul class="level1">
						<li><a href="<?php echo href_link(FILENAME_INDEX); ?>"><span><?php echo __('Home'); ?></span></a></li>
						<li><a href="<?php echo href_link(FILENAME_NEW_PRODUCTS); ?>"><span><?php echo __('New Arrivals'); ?></span></a></li>
						<li><a href="<?php echo href_link(FILENAME_FEATURED); ?>"><span><?php echo __('Featured'); ?></span></a></li>
						<li><a href="<?php echo href_link(FILENAME_ALL_PRODUCTS); ?>"><span><?php echo __('All Products'); ?></span></a></li>
					</ul>
					<?php //echo $category_tree->buildHeaderTree(); ?>
				</div>
			</div>
    	</div>
    </div>
    <?php echo ONLINE_SERVICE; ?>
</div>
