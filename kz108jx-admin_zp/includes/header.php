<div class="header-container">
	<div class="header">
	    <a href="<?php echo href_link('index.php'); ?>"><img class="logo" alt="Easyshop Logo" src="images/logo.png" /></a>
	    <div class="header-right"></div>
	</div>
	<div class="clear"></div>
	<div class="nav-container">
		<div id="nav">
			<ul class="level1">
				<li>
					<a href="<?php echo href_link(FILENAME_INDEX); ?>"><span>商店首页</span></a>
				</li>
				<li>
					<a onclick="return false" href="#"><span>商店管理</span></a>
					<ul class="level2">	
					<?php $sql = "SELECT configuration_group_id AS cgID, configuration_group_title AS cgTitle FROM " . TABLE_CONFIGURATION_GROUP . " ORDER BY sort_order"; ?>
					<?php $result = $db->Execute($sql); ?>
					<?php while (!$result->EOF) { ?>
						<li><a href="<?php echo href_link(FILENAME_CONFIGURATION, 'gID=' . $result->fields['cgID']); ?>"><?php echo $result->fields['cgTitle']; ?></a></li>
					<?php 	$result->MoveNext(); ?>
					<?php } ?>
					</ul>
				</li>
				<li>
					<a onclick="return false" href="#"><span>产品目录</span></a>
					<ul class="level2">
						<li><a href="<?php echo href_link(FILENAME_CATEGORY); ?>"><span>分类管理</span></a></li>
						<li><a href="<?php echo href_link(FILENAME_PRODUCT); ?>"><span>产品管理</span></a></li>
						<li><a href="<?php echo href_link(FILENAME_PRODUCT_OPTION); ?>"><span>产品选项</span></a></li>
						<li><a href="<?php echo href_link(FILENAME_PRODUCT_REVIEW); ?>"><span>产品评论</span></a></li>
						<li><a href="<?php echo href_link(FILENAME_BATCH); ?>"><span>批量管理</span></a></li>
						<li><a href="<?php echo href_link(FILENAME_IMPORT); ?>"><span>导入/导出</span></a></li>
						<li><a href="<?php echo href_link(FILENAME_IMPORT_OLD); ?>"><span>导入/导出(老版)</span></a></li>
					</ul>
				</li>
				<li>
					<a onclick="return false" href="#"><span>客户管理</span></a>
					<ul class="level2">
						<li><a href="<?php echo href_link(FILENAME_CUSTOMER); ?>"><span>客户</span></a></li>
						<li><a href="<?php echo href_link(FILENAME_ORDER); ?>"><span>订单</span></a></li>
						<li><a href="<?php echo href_link(FILENAME_ORDER_REVIEW); ?>"><span>订单评论</span></a></li>
					</ul>
				</li>
				<li>
					<a onclick="return false" href="#"><span>模块管理</span></a>
					<ul class="level2">
						<li><a href="<?php echo href_link(FILENAME_CMS_PAGE); ?>"><span>CMS页面</span></a></li>
						<li><a href="<?php echo href_link(FILENAME_COUPON); ?>"><span>优惠券</span></a></li>
						<li><a href="<?php echo href_link(FILENAME_SHIPPING_METHOD); ?>"><span>运送方式</span></a></li>
						<li><a href="<?php echo href_link(FILENAME_PAYMENT_METHOD); ?>"><span>支付方式</span></a></li>
					</ul>
				</li>
				<li>
					<a onclick="return false" href="#"><span>参数管理</span></a>
					<ul class="level2">
						<li><a href="<?php echo href_link(FILENAME_CURRENCY); ?>"><span>货币</span></a></li>
						<li><a href="<?php echo href_link(FILENAME_LANGUAGE); ?>"><span>语言包</span></a></li>
						<li><a href="<?php echo href_link(FILENAME_COUNTRY); ?>"><span>国家</span></a></li>
						<li><a href="<?php echo href_link(FILENAME_REGION); ?>"><span>省份/地区</span></a></li>
						<li><a href="<?php echo href_link(FILENAME_ORDER_STATUS); ?>"><span>订单状态</span></a></li>
					</ul>
				</li>
				<li>
					<a onclick="return false" href="#"><span>统计报表</span></a>
					<ul class="level2">
						<li><a href="<?php echo href_link(FILENAME_POULAR_SEARCH); ?>"><span>搜索热度</span></a></li>
						<li><a href="<?php echo href_link(FILENAME_PRODUCT_VIEWED); ?>"><span>产品浏览</span></a></li>
						<li><a href="<?php echo href_link(FILENAME_PRODUCT_ORDERED); ?>"><span>产品销售</span></a></li>
						<li><a href="<?php echo href_link(FILENAME_IP_HISTORY); ?>"><span>IP历史</span></a></li>
					</ul>
				</li>
				<li><a href="<?php echo href_link(FILENAME_INDEX, 'action=clearImg'); ?>">清除图片缓存</a></li>
				<li><a href="<?php echo href_link(FILENAME_INDEX, 'action=clearSql'); ?>">清除数据缓存</a></li>
				<li class="f-right"><a href="<?php echo href_link(FILENAME_LOGOUT); ?>">退出系统</a></li>
				<li class="f-right"><a target="_blank" href="<?php echo HTTP_SERVER . DIR_WS_CATALOG; ?>">商店前台</a></li>
			</ul>
		</div>
	</div>
</div>
<script type="text/javascript"><!--
$("#nav>ul.level1>li").hover(function(){
	$(this).addClass("active");
},function(){
	$(this).removeClass("active");
});
//--></script>