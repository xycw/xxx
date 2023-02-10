<div class="footer-container">
    <div class="footer-nav">
        <div class="col2-set">
            <div class="col-1">
                <h4><?php echo __('Company Info'); ?></h4>
                <ul class="links">
                    <li><a title="<?php echo __('About Us'); ?>" href="<?php echo href_link(FILENAME_CMS_PAGE, 'cpID=1'); ?>" rel="external nofollow"><?php echo __('About Us'); ?></a></li>
                    <li><a title="<?php echo __('Contact Us'); ?>" href="<?php echo href_link(FILENAME_CMS_PAGE, 'cpID=2'); ?>" rel="external nofollow"><?php echo __('Contact Us'); ?></a></li>
                    <li><a title="<?php echo __('Privacy & Security'); ?>" href="<?php echo href_link(FILENAME_CMS_PAGE, 'cpID=3'); ?>" rel="external nofollow"><?php echo __('Privacy & Security'); ?></a></li>
                    <li class="last"><a title="<?php echo __('Faq'); ?>" href="<?php echo href_link(FILENAME_CMS_PAGE, 'cpID=7'); ?>" rel="external nofollow"><?php echo __('Faq'); ?></a></li>
                </ul>
            </div>
            <div class="col-2">
                <h4><?php echo __('My Account'); ?></h4>
                <ul class="links">
                    <li><a title="<?php echo __('My Account'); ?>" href="<?php echo href_link(FILENAME_ACCOUNT, '', 'SSL'); ?>" rel="external nofollow"><?php echo __('My Account'); ?></a></li>
                    <li><a title="<?php echo __('My Orders'); ?>" href="<?php echo href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL'); ?>" rel="external nofollow"><?php echo __('My Orders'); ?></a></li>
                    <?php if (isset($_SESSION['customer_id'])) { ?>
                        <li class="last"><a title="<?php echo __('Log Out'); ?>" href="<?php echo href_link(FILENAME_LOGOUT, '', 'SSL'); ?>" rel="external nofollow"><?php echo __('Log Out'); ?></a></li>
                    <?php } else { ?>
                        <li class="last"><a title="<?php echo __('Log In'); ?>" href="<?php echo href_link(FILENAME_LOGIN, '', 'SSL'); ?>" rel="external nofollow"><?php echo __('Log In'); ?></a></li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </div>
	<div class="footer">
        <address><?php echo FOOTER_COPYRIGHT; ?></address>
        <img src="<?php echo DIR_WS_TEMPLATE_IMAGES; ?>pay-type.png" />
		<div id="backTop"><span><i class="iconfont">&#xe69c;</i></span></div>
    </div>
</div>
<script language="javascript" type="text/javascript"><!--
$(function(){jQuery(window).scroll(function(){if(jQuery(this).scrollTop()>100){jQuery('#backTop').fadeIn();}else{jQuery('#backTop').fadeOut();}});
$('#backTop').click(function(){jQuery('body,html').stop(false,false).animate({scrollTop:0},300);return false;});});
--></script>
<?php echo FOOTER_ABSOLUTE_FOOTER; ?>
