tinyMCE.init({
	
	mode     : "textareas",
	theme    : "advanced",
	plugins  : "paste,table",
	language : "ru",
	content_css : "/css/tinymce.css",
	
	theme_advanced_buttons1 : "styleselect,bold,italic,|,bullist,numlist,|,hr,|,undo,redo",
	theme_advanced_buttons2 : "tablecontrols",
	theme_advanced_buttons3 : "",
	paste_auto_cleanup_on_paste : true,
	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "left",
	theme_advanced_statusbar_location : "bottom",
	theme_advanced_resizing : false,
	
	style_formats : [
		{title : "Заголовок", block : "h3"}
	],
	
	cleanup : true,
	verify_html : true,
	cleanup_on_startup : true
	
});