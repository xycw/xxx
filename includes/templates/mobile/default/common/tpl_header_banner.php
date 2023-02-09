<div class="header-banner">
	<div id="swipe" class="swipe">
        <div class="swipe-box">
            <ul>
                <li><a href="javascript:;"><img src="<?php echo DIR_WS_TEMPLATE_IMAGES; ?>banners/slide1.jpg" alt="" /></a></li>
                <li><a href="javascript:;"><img src="<?php echo DIR_WS_TEMPLATE_IMAGES; ?>banners/slide2.jpg" alt="" /></a></li>
            </ul>
        </div>
        <div class="position">
            <ul></ul>
        </div>
	</div>
</div>
<script type="text/javascript"><!--
$(function() {
    TouchSlide({
        slideCell:"#swipe",
        titCell:".position ul", //开启自动分页 autoPage:true ，此时设置 titCell 为导航元素包裹层
        mainCell:".swipe-box ul",
        effect:"leftLoop",
        autoPage:true,//自动分页
        autoPlay:true //自动播放
    });
});
//--></script>
