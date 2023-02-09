<div class="footer-container">
	<div class="footer">
		<div class="container">
			<div class="footer-flex">
				<div class="col-md-4 col-sm-12 col-xs-12">
					<ul class="footer-logo">
						<li>
							<?php if(IS_ZP == 0){ ?>
								<a href="<?php echo href_link(FILENAME_INDEX); ?>" title="<?php echo HEADER_LOGO_ALT; ?>" class="logo">
                                    <svg class="logo-desktop" width="147px" height="31px" x="0px" y="0px" viewBox="0 0 147 31" style="enable-background:new 0 0 147 31;" xml:space="preserve">
                                        <path  d="M66,8l14.7,14.7l-2,2L66,12v17h-6V6l-2-2l2-2l0,0l0,0h6V8z M81,29h6V2h-6V29z M39,8h18V2H39V8z M90,29h27v-6H97.9H90V29z
                                             M30,29h6V2h-6V29z M39,29h18v-6H39V29z M51,12.5H39v6h12V12.5z M11.5,13.5l2,2l-2,2L23,29h4v-4l-9.5-9.5L27,6l-4-4L11.5,13.5z
                                             M6,11.5V2H0v27h6v-9.5l4-4L6,11.5z M113,0l-2,2H90v6h15L94,19l3.9,4l15-15h4.1V3.9V2h-2L113,0z M144,7.1l-4.3,4.3
                                            c0.8,1.2,1.2,2.6,1.2,4.1c0,4.1-3.4,7.5-7.5,7.5c-1.5,0-2.9-0.5-4.1-1.2l-4.3,4.3c2.3,1.8,5.2,3,8.4,3c7.5,0,13.5-6,13.5-13.5
                                            C147,12.3,145.9,9.4,144,7.1z M126,15.5c0-4.1,3.4-7.5,7.5-7.5c1.5,0,2.9,0.5,4.1,1.2l4.3-4.3c-2.3-1.8-5.2-3-8.4-3
                                            C126,2,120,8,120,15.5c0,3.2,1.1,6.1,3,8.4l4.3-4.3C126.5,18.4,126,17,126,15.5z">
                                        </path>
								    </svg>
                                </a>
							<?php }else{ ?>
								<a class="logo" href="<?php echo href_link(FILENAME_INDEX); ?>"><img src="<?php echo DIR_WS_TEMPLATE_IMAGES; ?>logo_zp.png" alt="<?php echo HEADER_LOGO_ALT; ?>" title="<?php echo HEADER_LOGO_ALT; ?>"/></a>
							<?php } ?>
						</li>
                        <li><a title="<?php echo __('Newsletter'); ?>" href="<?php echo href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL'); ?>"><?php echo __('Newsletter'); ?></a></li>
                    </ul>
				</div>
				<div class="col-md-8 col-sm-12 col-xs-12">
					<div class="col-md-4 col-sm-12 col-xs-12">
                        <h4 class="hidden-md hidden-lg"><?php echo __('Company Info'); ?><i class="iconfont icon-jiantou-copy-copy3 hidden-lg hidden-md"></i></h4>
                        <ul class="links">
							<li><a title="<?php echo __('Contact Us'); ?>" href="<?php echo href_link(FILENAME_CMS_PAGE, 'cpID=2'); ?>" rel="external nofollow"><?php echo __('Contact Us'); ?></a></li>
							<li><a title="<?php echo __('About Us'); ?>" href="<?php echo href_link(FILENAME_CMS_PAGE, 'cpID=1'); ?>" rel="external nofollow"><?php echo __('About Us'); ?></a></li>
							<li><a title="<?php echo __('Site Map'); ?>" href="<?php echo href_link(FILENAME_SITE_MAP); ?>"><?php echo __('Site Map'); ?></a></li>
							<li><a title="<?php echo __('Condition Of Use'); ?>" href="<?php echo href_link(FILENAME_CMS_PAGE, 'cpID=7'); ?>" rel="external nofollow"><?php echo __('Condition Of Use'); ?></a></li>
						</ul>
					</div>
					<div class="col-md-4 col-sm-12 col-xs-12">
                        <h4 class="hidden-md hidden-lg"><?php echo __('Customer Service'); ?><i class="iconfont icon-jiantou-copy-copy3 hidden-lg hidden-md"></i></h4>
                        <ul class="links">
							<li class="first"><a title="<?php echo __('Privacy Policy'); ?>" href="<?php echo href_link(FILENAME_CMS_PAGE, 'cpID=3'); ?>" rel="external nofollow" ><?php echo __('Privacy Policy'); ?></a></li>
							<li><a title="<?php echo __('Shipping & Delivery'); ?>" href="<?php echo href_link(FILENAME_CMS_PAGE, 'cpID=4'); ?>" rel="external nofollow" ><?php echo __('Shipping & Delivery'); ?></a></li>
							<li><a title="<?php echo __('Returns & Refunds'); ?>" href="<?php echo href_link(FILENAME_CMS_PAGE, 'cpID=5'); ?>" rel="external nofollow"><?php echo __('Returns & Refunds'); ?></a></li>
							<li class="last"><a title="<?php echo __('Faq'); ?>" href="<?php echo href_link(FILENAME_CMS_PAGE, 'cpID=6'); ?>" rel="external nofollow" ><?php echo __('Faq'); ?></a></li>
						</ul>
					</div>
					<div class="col-md-4 col-sm-12 col-xs-12">
                        <h4 class="hidden-md hidden-lg"><?php echo __('My Account'); ?><i class="iconfont icon-jiantou-copy-copy3 hidden-lg hidden-md"></i></h4>
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
				</div>
			</div>
		</div>
	</div>
	<div class="footer-bottom">
		<div class="container">
			<div class="row">
                <?php if (IS_ZP == 0) { ?>
                    <address class="col-md-12 col-sm-12 col-xs-12"><?php echo FOOTER_COPYRIGHT; ?></address>
                <?php } else { ?>
                    <div style="width:100%;height:100%;margin: 10px 0 30px;text-align: center;">
                        <address class="img-responsive_zp" style="text-align: center;margin: 0 auto;" class="col-md-6 col-sm-6 col-xs-12"><?php echo FOOTER_COPYRIGHT_ZP; ?></address>
                    </div>
                <?php } ?>
			</div>
		</div>
	</div>
</div>
	<p id="back-top"><a href="#top"><span><i class="iconfont">&#xe66c;</i></span></a></p>
<script language="javascript" type="text/javascript">
$(function(){
jQuery(window).scroll(function(){
    if(jQuery(this).scrollTop()>100){jQuery('#back-top').fadeIn();}else{jQuery('#back-top').fadeOut();}});
    $('#back-top a').click(function(){jQuery('body,html').stop(false,false).animate({scrollTop:0},800);return false;});
    $('.footer h4').on('click', function () {
        $(this).siblings('.links').slideToggle().end().find('i.iconfont').toggleClass('icon-jiantou-copy-copy3 icon-jiantou-copy-copy1');
    });
});
</script>
<?php echo FOOTER_ABSOLUTE_FOOTER; ?>
<script id="mcertify" type="text/javascript">
var wsid='804b72bb74b83e8aca3143b5774b1eb0';
var s = document.getElementById('mcertify'); 
if (s){var exScript = document.createElement('script'); 
exScript.type = 'text/javascript'; 
exScript.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'cert.verifystore.com/certs/js/xj_t.php?wsid=804b72bb74b83e8aca3143b5774b1eb0';
s.parentNode.insertBefore(exScript, s);}
</script>