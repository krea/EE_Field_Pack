$(document).ready(function()
{
	Matrix.bind('embed_video', 'display', function(data)
	{
		// Get random string using CE function
		elm_hash_key = ContentElements.randomString();
		
		// Create new DOM object with unique index
		data.dom.$td.html(data.dom.$td.html().replace(/\__files_index__/g, elm_hash_key));				
		
		ft_embed_video_live(); 
	});	
});


