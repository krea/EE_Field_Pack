//live

function ft_files_live()
{
	//add trigger to add button
	
	$('form .ft_files_item_wrapper .ft_files_btn_add').each(function(k,v){
	
		$(v).unbind('mousedown');
		$(v).mousedown(function(){			
		
			if (files_add_file_trigger_version == 2)
			{				
				$.ee_filebrowser.add_trigger($(v), 'upload_file', {content_type: 'images', directory: 'all'}, function(a){
				
					files_id_id = $(this).parents('.ft_files_item_wrapper').attr("id");
					
					//fetch title
					
					if (typeof(a.name) !== 'undefined'){file_name = a.name;}
					if (typeof(a.file_name) !== 'undefined'){file_name = a.file_name;}				
				
					//load placeholder
					
					placeholder_html = $("#placeholder_" + files_id_id).html();			
					placeholder = $(placeholder_html);
					
					placeholder.find('input').each(function(pk,pv){
						$(pv).attr("name",$(pv).attr("name").split('placeholder_')[1]);
					});
					
					placeholder.find('input.ft_files_dir').val(a.upload_location_id);
					placeholder.find('input.ft_files_name').val(file_name);				
					
					placeholder.find('.ft_files_img').css("background-image","url(" + a.thumb + ")");
					
					//insert						
					
					$("#" + files_id_id + " .ft_files_btn_add").parent().before(placeholder);
					
					//set focus
					
					$("#" + files_id_id + " .ft_files_btn_add").parent().parent().find('input[type=text]:last').focus();
					
					ft_files_live();
				});
			}
			else
			{
				$.ee_filebrowser.add_trigger($(v), 'upload_file', function(a){
				
					files_id_id = $(this).parents('.ft_files_item_wrapper').attr("id");
					
					//fetch title
					
					if (typeof(a.name) !== 'undefined'){file_name = a.name;}
					if (typeof(a.file_name) !== 'undefined'){file_name = a.file_name;}				
				
					//load placeholder
					
					placeholder_html = $("#placeholder_" + files_id_id).html();			
					placeholder = $(placeholder_html);
					
					placeholder.find('input').each(function(pk,pv){
						$(pv).attr("name",$(pv).attr("name").split('placeholder_')[1]);
					});
					
					placeholder.find('input.ft_files_dir').val(a.directory);
					placeholder.find('input.ft_files_name').val(file_name);				
					
					placeholder.find('.ft_files_img').css("background-image","url(" + a.thumb + ")");
					//placeholder.find('.ft_files_img').css("background-repeat","no-repeat");	
					//placeholder.find('.ft_files_img').css("background-position","center");								
					
					$("#" + files_id_id + " .ft_files_btn_add").parent().before(placeholder);
					ft_files_live();			
				});
			}	
			
	
		});

		$(v).click(function() { return false; });
	});	
		
	$('.ft_files_item_wrapper .ft_files_img_remove').each(function(k,v){
		$(v).unbind('click');
		$(v).click(function() { 
			$(v).parent().parent().parent('.ft_files_item').remove();
			ft_files_live();
			return false;
		});
	});	
	
	//show - hide add button
	
	$('.ft_files_item_wrapper').each(function(k,v){
		var files_limit = parseInt($(v).find('.files_limit').text());		
		if ($(v).find('.ft_files_item').size() >= files_limit)
		{
			$(v).find('.ft_files_item_btn_add_wrapper').addClass('js_hide');	
		}
		else
		{
			$(v).find('.ft_files_item_btn_add_wrapper').removeClass('js_hide');	
		}
	});

	files_id_sortable_script();	
}


function files_id_sortable_script()
{
	$( ".ft_files_item_wrapper" ).sortable({
		handle: '.ft_files_img',
		cursor: 'move',
		items: '.ft_files_item',
		stop: function() {}
	}); 
}

ft_files_live();