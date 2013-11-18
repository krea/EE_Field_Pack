var ft_picture_live = function()
{
	$(".jsPicture").each(function(){
	
		$fieldtype = $(this);
	
		if (!$fieldtype.data("is-picture"))
		{
			$fieldtype.data("is-picture", 1);
			
			$button_picture_add = $fieldtype.find(".jsPictureUpload");
			$button_picture_remove = $fieldtype.find(".jsPictureRemove");			
			$input_url = $fieldtype.find(".jsPictureUrl");				
			
			//set directory
			$button_picture_add.click(function()
			{
				$("#dir_choice").val($(this).data("upload_dir"));		
			});
			
			$button_picture_remove.click(function(){
			
				$fieldtype = $(this).closest($(".jsPicture"));			
				$fieldtype.find(".jsImage").val('');				
				$fieldtype.find(".jsImageUploadDir").val('');
				
				//refresh sizes
				$input_sizes = $fieldtype.find(".jsPictureSize")				
				
				old_size = $input_sizes.val();
				$input_sizes.find("option").remove();			

				for (i in picture_default_sizes)
				{
					$input_sizes.append($("<option>").text(picture_default_sizes[i]).val(i));
				}
				$input_sizes.val(old_size);	

				$fieldtype.find(".jsPicturePlaceholder").hide();				
				$fieldtype.find(".jsPictureUpload").show();													
			});
			
			//url
			$input_url.blur(function(){			
				if ($(this).val().toLowerCase().indexOf("http://") == -1 && $(this).val().toLowerCase().indexOf("https://") == -1 && $(this).val().toLowerCase().indexOf("/") == -1)
				{
					$(this).val("http://" + $(this).val());
				}			
			});
			
			//display upload
			$.ee_filebrowser.add_trigger($button_picture_add, 'upload_file', {content_type: 'images', directory: 'all'}, function(a){
						
				$fieldtype = $(this).closest($(".jsPicture"));
				$input_sizes = $fieldtype.find(".jsPictureSize");
				if (!a.is_image) { 
					alert($button_picture_add.data("error_file_is_not_image"));
					return false;
				}				
			
				//refresh sizes
				old_size = $input_sizes.val();
				$input_sizes.find("option").remove();			

				for (i in picture_sizes[a.upload_location_id])
				{
					$input_sizes.append($("<option>").text(picture_sizes[a.upload_location_id][i].value).val(picture_sizes[a.upload_location_id][i].id));
				}
				$input_sizes.val(old_size);
				
				if (typeof(a.name) !== 'undefined'){file_name = a.name;}
				if (typeof(a.title) !== 'undefined'){file_name = a.title;}	
				
				$fieldtype.find(".jsImage").val(file_name);				
				$fieldtype.find(".jsImageUploadDir").val(a.upload_location_id);	
				
				$fieldtype.find('.jsPicturePlaceholder').css("background-image","url(" + a.thumb + ")").show();
				$fieldtype.find(".jsPictureUpload").hide();	
				
				return false;				
			});
		}
	});
}

$(document).ready(function()
{
	ft_picture_live();
});