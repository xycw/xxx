<body class="<?php echo str_replace('_', '', $current_page) . 'Body'; ?>">
<div class="wrapper">
	<?php require($template->get_template_dir('tpl_noscript.php', DIR_WS_TEMPLATE, $current_page, 'common') . 'tpl_noscript.php'); ?>
	<div class="page">
    	<?php require($template->get_template_dir('tpl_header.php', DIR_WS_TEMPLATE, $current_page, 'common') . 'tpl_header.php'); ?>
    	<div class="main-container container">
    		<div class="row">
				<div class="col-md-3 col-sm-3 hidden-xs">
					<?php require($template->get_template_dir('tpl_col_left.php', DIR_WS_TEMPLATE, $current_page, 'common') . 'tpl_col_left.php'); ?>
				</div>
	        	<div class="col-md-9 col-sm-9 col-xs-12">
					<?php require($template->get_template_dir('tpl_' . $current_page . '_default.php', DIR_WS_TEMPLATE, $current_page, 'templates') . 'tpl_' . $current_page . '_default.php'); ?>
	            </div>
			</div>
        </div>
        <?php require($template->get_template_dir('tpl_footer.php', DIR_WS_TEMPLATE, $current_page, 'common') . 'tpl_footer.php'); ?>
    </div>
</div>
</body>