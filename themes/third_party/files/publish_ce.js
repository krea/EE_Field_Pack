﻿$(document).ready(function(){
	ContentElements.bind('files', 'display', function(data)
	{
		//get random string using CE function
	
		elm_hash_key = ContentElements.randomString();
		
		//create new DOM object with unique index
			
		data.html(data.html().replace(/\__files_index__/g, elm_hash_key));
		ft_files_live(); 
	});
});




