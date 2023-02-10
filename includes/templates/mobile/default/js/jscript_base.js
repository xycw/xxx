function setLocation(url)
{
    window.location.href = url;
}

function decorateGeneric(elements, options)
{
    if (typeof(elements) == 'undefined') {
        return;
    }
    var allSupportedOptions = ['first', 'last', 'even', 'odd', 'first-child', 'last-child'];
    var _decorateOptions = {};
    var total = elements.size();

    if (!options.length) {
        return;
    }

    for (var k in allSupportedOptions) {
        _decorateOptions[allSupportedOptions[k]] = false;
    }
    for (var k in options) {
        _decorateOptions[options[k]] = true;
    }

    if (_decorateOptions['first']) {
        elements.filter(':first').addClass('first');
    }

    if (_decorateOptions['last'] && total > 1) {
        elements.filter(':last').addClass('last');
    }

    if (_decorateOptions['even']) {
        elements.filter(':even').addClass('even');
    }

    if (_decorateOptions['odd']) {
        elements.filter(':odd').addClass('odd');
    }

    if (_decorateOptions['first-child']) {
        elements.filter(':first-child').addClass('first');
    }

    if (_decorateOptions['last-child']) {
        elements.filter(':last-child').addClass('last');
    }
}

function decorateList(list, nonRecursive)
{
    if (typeof(list) == 'undefined') {
        return;
    }

    var li = list.children('li');
    if (typeof(li) == 'undefined') {
        return;
    }

    if (typeof(nonRecursive) == 'undefined') {
        decorateGeneric(list, ['first', 'last', 'even', 'odd']);
        decorateGeneric(li, ['first-child', 'last-child']);
    } else {
        decorateGeneric(li, ['first', 'last', 'even', 'odd']);
    }
}

function decorateDataList(list)
{
    if (typeof(list) == 'undefined') {
        return;
    }

    decorateGeneric(list, ['first', 'last', 'even', 'odd']);
    //decorateGeneric(list.children('dt'), ['first', 'last', 'even', 'odd']);
    decorateGeneric(list.children('dd'), ['first', 'last']);
}

function decorateTable(table)
{
    if (typeof(table) == 'undefined') {
        return;
    }

    var tbodyTr = table.children('tbody').children('tr');
    var theadTr = table.children('thead').children('tr');
    var tfootTr = table.children('tfoot').children('tr');
    var trTd = table.children().children('tr').children('td');

    decorateGeneric(tbodyTr, ['first', 'last', 'even', 'odd']);
    decorateGeneric(theadTr, ['first', 'last']);
    decorateGeneric(tfootTr, ['first', 'last']);
    decorateGeneric(trTd, ['last-child']);
}
// tab
$.fn.tabs=function(){var selector=this;this.each(function(){var obj=$(this);$(obj.attr('href')).hide();$(obj).click(function(){$(selector).removeClass('selected');$(selector).each(function(i,element){$($(element).attr('href')).hide()});$(this).addClass('selected');$($(this).attr('href')).show();return false})});$(this).show();$(this).first().click()};

// scroll bug fix
function scrollHack(id, selectorScrollable) {
    var container = document.getElementById(id);
    // 如果没有滚动容器选择器，或者已经绑定了滚动时间，忽略
    if (!selectorScrollable || container.getAttribute('data-scroll')) { return; }

    // 是否是搓浏览器
    // 自己在这里添加判断和筛选
    var isSBBrowser;

    var data = {
        posY: 0,
        maxscroll: 0
    };

    container.addEventListener('touchstart', function(event){
        var events = event.touches[0] || event;

        // 先求得是不是滚动元素或者滚动元素的子元素
        var elTarget = $(event.target);

        if (!elTarget.length) { return; }

        var elScroll;

        // 获取标记的滚动元素，自身或子元素皆可
        if (elTarget.is(selectorScrollable)) {
            elScroll = elTarget;
        } else if ((elScroll = elTarget.parents(selectorScrollable)).length == 0) {
            elScroll = null;
        }

        if (!elScroll) { return; }

        // 当前滚动元素标记
        data.elScroll = elScroll;

        // 垂直位置标记
        data.posY = events.pageY;
        data.scrollY = elScroll.scrollTop();
        // 是否可以滚动
        data.maxscroll = elScroll[0].scrollHeight - elScroll[0].clientHeight;
    });

    container.addEventListener('touchmove', function(event){
        // 如果不足于滚动，则禁止触发整个窗体元素的滚动
        if (data.maxscroll <= 0 || isSBBrowser) {
            // 禁止滚动
            event.preventDefault();
        }
        // 滚动元素
        var elScroll = data.elScroll;
        // 当前的滚动高度
        var scrollTop = elScroll.scrollTop();

        // 现在移动的垂直位置，用来判断是往上移动还是往下
        var events = event.touches[0] || event;
        // 移动距离
        var distanceY = events.pageY - data.posY;

        if (isSBBrowser) {
            elScroll.scrollTop(data.scrollY - distanceY);
            elScroll.trigger('scroll');
            return;
        }

        // 上下边缘检测
        if (distanceY > 0 && scrollTop == 0) {
            // 往上滑，并且到头
            // 禁止滚动的默认行为
            event.preventDefault();
            return;
        }

        // 下边缘检测
        if (distanceY < 0 && (scrollTop + 1 >= data.maxscroll)) {
            // 往下滑，并且到头
            // 禁止滚动的默认行为
            event.preventDefault();
            return;
        }
    });

    container.addEventListener('touchend', function(event){ data.maxscroll = 0; });

    // 防止多次重复绑定
    container.setAttribute('data-scroll', 'isBind');
}
