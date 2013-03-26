//save if reeorder

$(function() {
	$( "table.sortable" ).sortable({
		helper: function(e, ui) {
			ui.children().each(function() {
				$(this).width($(this).width());
			});
			return ui;
		},
		axis: "y",
		items: '.sortable_item',
		opacity: 0,
		cursor: 'move',
		stop: function(e, ui) {
			
			sortString = '';
		
			$('input[name^=sort]').each(function(k,v)
			{
				sortString = sortString + ($(v).val()) + '|';
			});
			
			$.get(moduleBase + '&method=settingsCustomFieldsSort' + '&sortString=' + sortString, function(data)
			{
				var response = eval('(' + data + ')');
				
				//show me message
				
				jQuery(function($){
					$.ee_notice(response.message,{open: false, type:response.status});
					//setTimeout(function(){ $.ee_notice.destroy(); }, 2000);
				});				

			});
		},
	}).disableSelection();
});