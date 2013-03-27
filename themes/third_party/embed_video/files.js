//live

function ft_embed_video_live()
{
	// Add trigger to add button
	$('form .ft_embed_video_item_wrapper .ft_embed_video_btn_add').not('.triggered').each(function(k,v){

		//$(v).unbind('mousedown');
		$(v).mousedown(function(){		
		
			if (embed_video_add_file_trigger_version == 2)
			{				
				$.ee_filebrowser.add_trigger($(v), 'upload_file', {
					content_type: 'images', 
					directory: 'all'
				}, function(a){
				
					files_id_id = $(this).parents('.ft_embed_video_item_wrapper').attr("id");
					
					// Fetch title
					if (typeof(a.name) !== 'undefined'){
						file_name = a.name;
					}
					if (typeof(a.title) !== 'undefined'){
						file_name = a.title;
					}				
				
					// Load placeholder					
					placeholder_html = $("#placeholder_" + files_id_id).html();			
					placeholder = $(placeholder_html);
					
					placeholder.find('input').each(function(pk,pv){
						$(pv).attr("name",$(pv).attr("name").split('placeholder_')[1]);
					});
					
					placeholder.find('input.ft_embed_video_dir').val(a.upload_location_id);
					placeholder.find('input.ft_embed_video_name').val(file_name);					
					placeholder.find('.ft_embed_video_img').css("background-image","url(" + a.thumb + ")");
					
					// Insert
					$("#" + files_id_id + " .ft_embed_video_btn_add").parent().before(placeholder);
					
					// Set focus
					$("#" + files_id_id + " .ft_embed_video_btn_add").parent().parent().find('input[type=text]:last').focus();
					
					ft_embed_video_live();
				});
			}
			else
			{
				$.ee_filebrowser.add_trigger($(v), 'upload_file', function(a){
				
					files_id_id = $(this).parents('.ft_embed_video_item_wrapper').attr("id");
					
					//fetch title
					
					if (typeof(a.name) !== 'undefined'){
						file_name = a.name;
					}
					if (typeof(a.title) !== 'undefined'){
						file_name = a.title;
					}				
				
					//load placeholder
					
					placeholder_html = $("#placeholder_" + files_id_id).html();			
					placeholder = $(placeholder_html);
					
					placeholder.find('input').each(function(pk,pv){
						$(pv).attr("name",$(pv).attr("name").split('placeholder_')[1]);
					});
					
					placeholder.find('input.ft_embed_video_dir').val(a.directory);
					placeholder.find('input.ft_embed_video_name').val(file_name);				
					
					placeholder.find('.ft_embed_video_img').css("background-image","url(" + a.thumb + ")");								
					
					$("#" + files_id_id + " .ft_embed_video_btn_add").parent().before(placeholder);
					ft_embed_video_live();			
				});
			}	
			
	
		});

		$(v).addClass('triggered');
		$(v).click(function() {
			return false;
		});
	});	
		
	$('.ft_embed_video_item_wrapper .ft_embed_video_img_remove').not('.triggered').each(function(k,v){
		//$(v).unbind('click');
		$(v).click(function() { 
			$(v).parent().parent().parent('.ft_embed_video_item').remove();
			ft_embed_video_live();
		});
		$(v).addClass('triggered');
	});	
	
	// Show - hide add button
	$('.ft_embed_video_item_wrapper').each(function(k,v){
		var files_limit = parseInt($(v).find('.files_limit').text());		
		if ($(v).find('.ft_embed_video_item').size() >= 1)
		{
			$(v).find('.ft_embed_video_item_btn_add_wrapper').addClass('js_hide');	
		}
		else
		{
			$(v).find('.ft_embed_video_item_btn_add_wrapper').removeClass('js_hide');	
		}
	});

	files_id_sortable_script();	
}


function files_id_sortable_script()
{
	$( ".ft_embed_video_item_wrapper" ).sortable({
		handle: '.ft_embed_video_img',
		cursor: 'move',
		items: '.ft_embed_video_item',
		stop: function() {}
	}); 
}

ft_embed_video_live();