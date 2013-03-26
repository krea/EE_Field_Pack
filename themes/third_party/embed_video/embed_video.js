function ft_embed_video_live()
{
	// Add trigger to add button
	$('form .ft_embed_video_btn_add').not('.triggered').each(function(k,v){

		//$(v).unbind('mousedown');
		//$(v).mousedown(function(){		
		$(v).live('mousedown', function(){
		
			if (embed_video_add_file_trigger_version == 2)
			{				
				$.ee_filebrowser.add_trigger($(v), 'upload_file', {
					content_type: 'images', 
					directory: 'all'
				}, function(a){
				
					// Fetch title
					if (typeof(a.name) !== 'undefined'){
						file_name = a.name;
					}
					
					if (typeof(a.title) !== 'undefined'){
						file_name = a.title;
					}
					
					var ft_embed_video_item = $(this).closest('.ft_embed_video_item');
									
					$(ft_embed_video_item).find('.ft_embed_video_dir').val(a.upload_location_id);
					$(ft_embed_video_item).find('.ft_embed_video_name').val(file_name);
					$(ft_embed_video_item).find('.ft_embed_video_file').removeClass('js_hide');
					$(ft_embed_video_item).find('.ft_embed_video_file_name').html(file_name);
					$(ft_embed_video_item).find('.ft_embed_video_add').addClass('js_hide');
					
					ft_embed_video_live();
			
				});
			}
			else
			{
				$.ee_filebrowser.add_trigger($(v), 'upload_file', function(a){
				
					// Fetch title
					if (typeof(a.name) !== 'undefined'){
						file_name = a.name;
					}
					
					if (typeof(a.title) !== 'undefined'){
						file_name = a.title;
					}
					
					var ft_embed_video_item = $(this).closest('.ft_embed_video_item');
									
					$(ft_embed_video_item).find('.ft_embed_video_dir').val(a.upload_location_id);
					$(ft_embed_video_item).find('.ft_embed_video_name').val(file_name);
					$(ft_embed_video_item).find('.ft_embed_video_file').removeClass('js_hide');
					$(ft_embed_video_item).find('.ft_embed_video_file_name').html(file_name);
					$(ft_embed_video_item).find('.ft_embed_video_add').addClass('js_hide');
					
					ft_embed_video_live();
					
				});
			}
			
	
		});

		$(v).addClass('triggered');
		$(v).click(function() {
			return false;
		});
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

$('.ft_embed_video_remove_button').live("click", function(){
	
	var ft_embed_video_item = $(this).closest('.ft_embed_video_item');
	
	$(ft_embed_video_item).find('.ft_embed_video_add').removeClass('js_hide');
	$(ft_embed_video_item).find('.ft_embed_video_dir').val('');
	$(ft_embed_video_item).find('.ft_embed_video_name').val('');
	$(ft_embed_video_item).find('.ft_embed_video_file').addClass('js_hide');
	
	return false;
});