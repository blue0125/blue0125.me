//$Id$
function createKindEditor(id, settings) {
	var defaultSettings = {
		newlineTag:'br',
		resizeType:1,
		pasteType:1,
		items : [ 
			'formatblock', 'fontname', 'fontsize', '|', 
			'forecolor', 'hilitecolor', 'bold', 'italic', 'underline', 'strikethrough', '|', 
			'justifyleft', 'justifycenter', 'justifyright', 'insertorderedlist', 'insertunorderedlist', '|', 
			'table', 'emoticons', 'image', 'insertfile', 'link', 'unlink', 'fullscreenResizeHandlerreen', '|',
			'code', 'source', 'preview' ]
	};

	if (settings) $.extend(defaultSettings, settings);
	return KindEditor.create('#'+id, defaultSettings);
}

