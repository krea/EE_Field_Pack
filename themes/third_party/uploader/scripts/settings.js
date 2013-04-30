$(document).ready(function () {

	function refresh_addon_fields()
	{
		var number_of_addon_fields = $('#uploader_addon_fields').val();
	
		$('.uploader_addon_field').each(
			function (k,v)
			{
				if (k >= number_of_addon_fields)
				{
					$(v).parent().parent().hide();
				}
				else
				{
					$(v).parent().parent().show();
				}
			}
		);
	}
	
	$('#uploader_addon_fields').change(
		function() {
			refresh_addon_fields();
		}
	);
	
	refresh_addon_fields();
});

