<?php if ($breadcrumb_trail = $breadcrumb->trail()) { ?>
<div class="breadcrumbs">
	<ul>
		<?php foreach ($breadcrumb_trail as $_trail) { ?>
			<?php if (not_null($_trail['link'])) { ?>
			<li class="<?php echo $_trail['class']; ?>"><a title="<?php echo $_trail['title']; ?>" href="<?php echo $_trail['link']; ?>"><?php echo $_trail['title']; ?></a><span>/</span></li>
			<?php } else { ?>
			<li class="<?php echo $_trail['class']; ?>"><?php echo $_trail['title']; ?></li>
			<?php } ?>
		<?php } ?>
	</ul>
</div>
<?php } ?>
