<div class="block block-logo">
	<?php if ($this_is_home_page) { ?>
		<h1 class="logo"><strong><?php echo HEADER_LOGO_ALT; ?></strong><a href="<?php echo href_link(FILENAME_INDEX); ?>" title="<?php echo HEADER_LOGO_ALT; ?>" class="logo"><img src="<?php echo $template->get_template_dir(HEADER_LOGO_SRC, DIR_WS_TEMPLATE, $current_page, 'images') . HEADER_LOGO_SRC; ?>" alt="<?php echo HEADER_LOGO_ALT; ?>" /></a></h1>
		<?php } else { ?>
		<a href="<?php echo href_link(FILENAME_INDEX); ?>" title="<?php echo HEADER_LOGO_ALT; ?>" class="logo"><strong><?php echo HEADER_LOGO_ALT; ?></strong><img src="<?php echo $template->get_template_dir(HEADER_LOGO_SRC, DIR_WS_TEMPLATE, $current_page, 'images') . HEADER_LOGO_SRC; ?>" alt="<?php echo HEADER_LOGO_ALT; ?>" /></a>
	<?php } ?>			
</div>
