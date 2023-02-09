<div class="std">
	<div class="page-title">
		<h1><?php echo __('Page Not Found'); ?></h1>
	</div>
	<dl>
		<dt><?php echo __('The page you requested was not found, and we have a fine guess why.'); ?></dt>
		<dd>
			<ul class="disc">
				<li><?php echo __('If you typed the URL directly, please make sure the spelling is correct.'); ?></li>
				<li><?php echo __('If you clicked on a link to get here, the link is outdated.'); ?></li>
			</ul>
		</dd>
	</dl>
	<dl>
		<dt><?php echo __('What can you do?'); ?></dt>
		<dd><?php echo __('Have no fear, help is near! There are many ways you can get back on track with Our Store.'); ?></dd>
		<dd>
			<ul class="disc">
				<li><?php echo __('<a onclick="history.go(-1); return false;" href="#">Go back</a> to the previous page.'); ?></li>
				<li><?php echo __('Use the search bar at the top of the page to search for your products.'); ?></li>
				<li><?php echo __('Follow these links to get you back on track!'); ?><br><a href="<?php echo href_link(FILENAME_INDEX); ?>"><?php echo __('Store Home'); ?></a> <span class="separator">|</span> <a href="<?php echo href_link(FILENAME_ACCOUNT, '', 'SSL'); ?>"><?php echo __('My Account'); ?></a></li>
			</ul>
		</dd>
	</dl>
</div>
