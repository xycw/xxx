function popWin(url, win, para)
{
	var win = window.open(url,win,para);
	win.focus();
}

function setLocation(url)
{
	window.location.href = url;
}

function setPLocation(url, setFocus)
{
	if (setFocus) {
		window.opener.focus();
	}
	window.opener.location.href = url;
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
