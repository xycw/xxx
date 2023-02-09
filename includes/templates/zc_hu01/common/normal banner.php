<div class="header-banner">
	<div class="banner-block1" id="J_owl">
		<a class="item" href="<?php echo href_link(FILENAME_CATEGORY, 'cID=1'); ?>">
			<img src="<?php echo DIR_WS_TEMPLATE_IMAGES; ?>banners/slide1.jpg" alt="" />
		</a>
		<a class="item" href="<?php echo href_link(FILENAME_CATEGORY, 'cID=1'); ?>">
			<img src="<?php echo DIR_WS_TEMPLATE_IMAGES; ?>banners/slide2.jpg" alt="" />
		</a>
	</div>
</div>
<script type="text/javascript">
$(function () {
	$('#J_owl').owlCarousel({
		items:1,
		dots:true,
		loop:true,
		nav:true,
		autoplay:true,
		autoplayTimeout:3000,
		autoplayHoverPause:true
	});
});
</script>

<script>
	$(function(){
		$('.banner-block1').on('mousemove',function(mouseevent){
			var moveX = mouseevent.clientY/100;
			var moveY = mouseevent.clientX/100;
			console.log(moveX)
			$('.banner-block1').css({
				"transform":'rotateX('+moveX+'deg)'+'rotateY('+moveY+'deg)',
			});
		}).on('mouseleave',function(){
			$('.banner-block1').css({
				"transform":'rotateX(0deg) rotateY(0deg)'
			});
		});
	});
</script>