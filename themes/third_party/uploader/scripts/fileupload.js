/*
 * jQuery File Upload Plugin JS Example 5.0.2
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://creativecommons.org/licenses/MIT/
 */

/*jslint nomen: true */
/*global $ */


//--------------------------------------------------
//	Sortable
//--------------------------------------------------	
		
var add_sortable_event = function (element)
{
	$(function() {
		$( element).find('.files').sortable({
			helper: function(e, ui) {
				ui.children().each(function() {
					$(this).width($(this).width());
				});
				return ui;
			},
			axis: "y",
			items: 'tr.template-download',
			opacity: 10,
			cursor: 'move'
		});  //.disableSelection();
	});
}

//--------------------------------------------------
//	Upload
//--------------------------------------------------

$('.ft_uploader_upload_form').each(
	function (k,v)
	{
		ft_upload_form_id 	= ($(v).attr("id"));
		ft_upload_config 	= ft_uploader_upload_form_config[ft_upload_form_id];
		
		add_sortable_event(v);
	
		//'use strict';  
	    	
		// Initialize the jQuery File Upload widget:
		if (ft_upload_config.field_content_type == 'all')
		{
			var ft_upload_pattern = /.+$/i ;
		}
		else
		{
			var ft_upload_pattern = /(gif|jpg|pjpeg|x-png|jpeg|png)$/ ;
		}
	    
		$('#' + ft_upload_form_id).fileupload(
		{
			'maxNumberOfFiles' 	: parseInt(ft_upload_config.uploader_files_limit),
			'autoUpload'		: true,
			'url'				: ft_upload_config.action + '&dir=' + ft_upload_config.allowed_directories,
	    	
			'uploadTemplate'	: $('#template-upload-' + ft_upload_form_id),
			'downloadTemplate'	: $('#template-download-' + ft_upload_form_id),	
        	
			'maxFileSize'		: ft_upload_config.max_size,
        	
			'acceptFileTypes'	: ft_upload_pattern,
			'dropZone'		: undefined	    	
	    	
		});
	
		var entry_id = $('input[name=entry_id]').val();
	
		// Load existing files:
	    
		var load_existing_files = function (ft_upload_form_id,ft_upload_config) {
	    
			$.getJSON(ft_upload_config.action + '&field_id=' + ft_upload_config.field_id + '&entry_id=' + entry_id + '&dir=' + ft_upload_config.allowed_directories, function (files) {
				var fu = $('#' + ft_upload_form_id).data('fileupload');
				if (files != null)
				{
		        
					fu._adjustMaxNumberOfFiles(-files.length);
					fu._renderDownload(files)
					.appendTo($('#' + ft_upload_form_id + ' .files'))
					.fadeIn(function () {
						// Fix for IE7 and lower:
						$(this).show();
		                
		                
						for(var item in files) {	
							var file = files[item];
							for(var f in file) {
								var data = file[f];
			        				        		
								$('#' + ft_upload_form_id + ' .uploader_label_1:first').val(data.text_1);
								$('#' + ft_upload_form_id + ' .uploader_label_1:first').removeClass('uploader_label_1');
		        				 
								$('#' + ft_upload_form_id + ' .uploader_label_2:first').val(data.text_2);
								$('#' + ft_upload_form_id + ' .uploader_label_2:first').removeClass('uploader_label_2');
		        				 
								$('#' + ft_upload_form_id + ' .uploader_label_3:first').val(data.text_3);
								$('#' + ft_upload_form_id + ' .uploader_label_3:first').removeClass('uploader_label_3');
		        				 
								$('#' + ft_upload_form_id + ' .uploader_label_4:first').val(data.text_4);
								$('#' + ft_upload_form_id + ' .uploader_label_4:first').removeClass('uploader_label_4');
		        				 
								$('#' + ft_upload_form_id + ' .uploader_label_5:first').val(data.text_5);
								$('#' + ft_upload_form_id + ' .uploader_label_5:first').removeClass('uploader_label_5');
							}
						}	                
		                
					});
	
				}
			});
	    
		}
	    
		load_existing_files(ft_upload_form_id,ft_upload_config);


		// Open download dialogs via iframes,
		// to prevent aborting current uploads:
	    
		$('#fileupload .files a:not([target^=_blank])').live('click', function (e) {
			e.preventDefault();
			$('<iframe style="display:none;"></iframe>')
			.prop('src', this.href)
			.appendTo('body');
		});
		
		
	}
	);


