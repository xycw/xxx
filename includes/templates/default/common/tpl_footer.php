<div class="footer-container">
	<div class="footer">
		<div class="col4-set">
			<div class="col-1">
				<h4><?php echo __('Company Info'); ?></h4>
				<ul class="links">
					<li class="first"><a title="<?php echo __('About Us'); ?>" href="<?php echo href_link(FILENAME_CMS_PAGE, 'cpID=1'); ?>" rel="external nofollow"><?php echo __('About Us'); ?></a></li>
					<li><a title="<?php echo __('Contact Us'); ?>" href="<?php echo href_link(FILENAME_CMS_PAGE, 'cpID=2'); ?>" rel="external nofollow"><?php echo __('Contact Us'); ?></a></li>
					<li class="last"><a title="<?php echo __('Site Map'); ?>" href="<?php echo href_link(FILENAME_SITE_MAP); ?>"><?php echo __('Site Map'); ?></a></li>
				</ul>
			</div>
			<div class="col-2">
				<h4><?php echo __('Customer Service'); ?></h4>
				<ul class="links">
					<li class="first"><a title="<?php echo __('Privacy & Security'); ?>" href="<?php echo href_link(FILENAME_CMS_PAGE, 'cpID=3'); ?>" rel="external nofollow"><?php echo __('Privacy & Security'); ?></a></li>
                    <li><a title="<?php echo __('Payment Methods'); ?>" href="<?php echo href_link(FILENAME_CMS_PAGE, 'cpID=9'); ?>" rel="external nofollow"><?php echo __('Payment Methods'); ?></a></li>
					<li><a title="<?php echo __('Shipping & Delivery'); ?>" href="<?php echo href_link(FILENAME_CMS_PAGE, 'cpID=4'); ?>" rel="external nofollow"><?php echo __('Shipping & Delivery'); ?></a></li>
					<li><a title="<?php echo __('Returns Policy'); ?>" href="<?php echo href_link(FILENAME_CMS_PAGE, 'cpID=5'); ?>" rel="external nofollow"><?php echo __('Returns Policy'); ?></a></li>
					<li class="last"><a title="<?php echo __('Faq'); ?>" href="<?php echo href_link(FILENAME_CMS_PAGE, 'cpID=7'); ?>" rel="external nofollow"><?php echo __('Faq'); ?></a></li>
				</ul>
			</div>
			<div class="col-3">
				<h4><?php echo __('My Account'); ?></h4>
				<ul class="links">
					<?php if (isset($_SESSION['customer_id'])) { ?>
					<li class="first"><a title="<?php echo __('Log Out'); ?>" href="<?php echo href_link(FILENAME_LOGOUT, '', 'SSL'); ?>" rel="external nofollow"><?php echo __('Log Out'); ?></a></li>
					<?php } else { ?>
					<li class="first"><a title="<?php echo __('Log In'); ?>" href="<?php echo href_link(FILENAME_LOGIN, '', 'SSL'); ?>" rel="external nofollow"><?php echo __('Log In'); ?></a></li>
					<?php } ?>
					<li><a title="<?php echo __('My Orders'); ?>" href="<?php echo href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL'); ?>" rel="external nofollow"><?php echo __('My Orders'); ?></a></li>
					<li><a title="<?php echo __('My Cart'); ?>" href="<?php echo href_link(FILENAME_SHOPPING_CART, '', 'SSL'); ?>" rel="external nofollow"><?php echo __('My Cart'); ?></a></li>
				</ul>
			</div>
			<div class="col-4">
				<h4><?php echo __('Payment & Shipping'); ?></h4>
				<img src="<?php echo DIR_WS_TEMPLATE_IMAGES; ?>payment-shipping.png" />
			</div>
		</div>
		<address><?php echo FOOTER_COPYRIGHT; ?></address>
		<p id="back-top"><a href="#top"><span></span></a></p>
    </div>
</div>
<script language="javascript" type="text/javascript"><!--
$(function(){jQuery(window).scroll(function(){if(jQuery(this).scrollTop()>100){jQuery('#back-top').fadeIn();}else{jQuery('#back-top').fadeOut();}});
$('#back-top a').click(function(){jQuery('body,html').stop(false,false).animate({scrollTop:0},800);return false;});});
--></script>
<?php echo FOOTER_ABSOLUTE_FOOTER; ?>
