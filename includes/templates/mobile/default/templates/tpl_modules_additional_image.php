<?php if (isset($productInfo['additional_image'])) { ?>
	<?php if (count($productInfo['additional_image']) > 1) { ?>
		<div id="J_product" class="swipe">
			<div class="swipe-box">
				<ul>
					<?php foreach ($productInfo['additional_image'] as $_image) { ?>
						<li>
							<a href="javascript:;">
								<img alt="<?php echo $productInfo['nameAlt']; ?>" src="<?php echo get_small_image($_image, MOBILE_THUMBNAIL_IMAGE_WIDTH, MOBILE_THUMBNAIL_IMAGE_HEIGHT); ?>" />
							</a>
						</li>
					<?php } ?>
				</ul>
			</div>
			<div class="detail">
				<ul>
					<?php foreach ($productInfo['additional_image'] as $_image) { ?>
						<li><img alt="<?php echo $productInfo['nameAlt']; ?>" src="<?php echo get_small_image($_image, 50, 50); ?>" /></li>
					<?php } ?>
				</ul>
			</div>
		</div>
		<script type="text/javascript">
		$(function() {
			TouchSlide({
				slideCell:"#J_product",
				titCell:".detail ul li", //开启自动分页 autoPage:true ，此时设置 titCell 为导航元素包裹层
				mainCell:".swipe-box ul",
				effect:"leftLoop",
				autoPlay:false //自动播放
			});
		});
		</script>
	<?php } else { ?>
		<a href="javascript:;"><img alt="<?php echo $productInfo['nameAlt']; ?>" src="<?php echo get_large_image($productInfo['image'], MOBILE_THUMBNAIL_IMAGE_WIDTH, MOBILE_THUMBNAIL_IMAGE_HEIGHT); ?>" /></a>
	<?php } ?>
<?php } ?>
