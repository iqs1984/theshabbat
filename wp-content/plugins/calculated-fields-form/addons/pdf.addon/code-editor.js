(function($){
    $(function(){
        if( $('[name="cpcff_pdf_data"]').length && 'codeEditor' in wp) {
            var editorSettings = wp.codeEditor.defaultSettings ? _.clone( wp.codeEditor.defaultSettings ) : {};
            editorSettings.codemirror = _.extend(
                {},
                editorSettings.codemirror,
                {
                    indentUnit: 2,
                    tabSize: 2,
					autoCloseTags: false
                }
            );
			editorSettings['htmlhint']['spec-char-escape'] = false;
			editorSettings['htmlhint']['alt-require'] = false;
			editorSettings['htmlhint']['tag-pair'] = false;
            var editor = wp.codeEditor.initialize( $('[name="cpcff_pdf_data"]'), editorSettings );
        }
    });
 })(jQuery);