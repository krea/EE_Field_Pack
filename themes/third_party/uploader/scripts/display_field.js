/*
$(document).ready(function() {

	//--------------------------------------------------
	//	Skry/Zobraz tlacidlo pre pridavanie
	//--------------------------------------------------
	
	var ft_gallery_display_add_buttons = function()
	{
		$('.ft_gallery').each(
			function (k,v){
				$(v).find('.ft_gallery_add').each(
					function(k1,v1)
					{
						gallery_id = ($(this).parent().parent().parent().parent().attr("id"));
						
						//maximalny pocet riadkov
						gallery_limit = ft_gallery_config[gallery_id].gallery_files_limit;
						
						//pocet riadkov teraz
						gallery_count = ($('#' + gallery_id + ' .ft_gallery_item').size());
						
						if (gallery_limit && (gallery_count >= gallery_limit))
						{
							$(v1).css('visibility', 'hidden');
						}
						else
						{
							$(v1).css('visibility', 'visible');
						}		//--------------------------------------------------
		//	Sortable
		//--------------------------------------------------	
	
		$(function() {
			$( ".ft_gallery" ).sortable({
				helper: function(e, ui) {
					ui.children().each(function() {
						$(this).width($(this).width());
					});
					return ui;
				},
				axis: "y",
				items: '.ft_gallery_item',
				opacity: 0,
				cursor: 'move',
				update: function(e, ui) {
					ft_gallery_fields_rename();
				}
			}).disableSelection();
		});
					}
				);
			}
		);
	}	

	//--------------------------------------------------
	//	Daj pre vsetky inputy korektne indexi
	//--------------------------------------------------
	
	var ft_gallery_fields_rename = function()
	{
		$('.ft_gallery').each(
			function (k,v){
				$(v).find('.ft_gallery_item').each(
					function (k1,v1)
					{
						$(v1).find('input').each(
							function (k2,v2)
							{
								if ($(v2).attr("class") == 'ft_gallery_input_text')
								{
									$(v2).attr("name", $(v2).attr("name").replace(/\[(.*)\]\[(.*)\]/,"["+k1+"][$2]"));
								}	
							}
						);
					}
				);
			}
		);
	}
	
	//--------------------------------------------------
	//	Live
	//--------------------------------------------------	
	
	var ft_gallery_fields_live = function()
	{
		//--------------------------------------------------
		//	Sortable
		//--------------------------------------------------	
	
		$(function() {
			$( ".ft_gallery" ).sortable({
				helper: function(e, ui) {
					ui.children().each(function() {
						$(this).width($(this).width());
					});
					return ui;
				},
				axis: "y",
				items: '.ft_gallery_item',
				opacity: 0,
				cursor: 'move',
				update: function(e, ui) {
					ft_gallery_fields_rename();
				}
			}).disableSelection();
		});

		//--------------------------------------------------
		//	Delete row
		//--------------------------------------------------	
		
		$('.ft_gallery_delete').unbind('click');
		$('.ft_gallery_delete').click(
			function(){
				$(this).parent().parent().parent().remove();
				
				ft_gallery_display_add_buttons();
				ft_gallery_fields_rename();
				
				return false;
			}
		);
		
		//--------------------------------------------------
		//	Add row
		//--------------------------------------------------	
		
		$('.ft_gallery_add').unbind('click');
		$('.ft_gallery_add').click(
			function(){
			
				//vygeneruj novy riadok
			
				current_table_id 		= ($(this).parent().parent().parent().parent().attr("id"));
				prototype_table_id 		= current_table_id + '_prototype';
				prototype_row_html 		= $("#" + prototype_table_id + ' tbody').html();
				$('#' + current_table_id + ' tbody').append(prototype_row_html);
				
				//vygeneruj id pre novy upload prvok
				
				var randomnumber = Math.floor(Math.random()*11);
				($('#' + current_table_id + ' tbody').find('.ft_gallery_input_file_prototype').attr("id", "id" + randomnumber));
				
				ft_gallery_fields_live();		
				return false;
			}
		);	
		
		//--------------------------------------------------
		//	Cotnrols
		//--------------------------------------------------
		
		ft_gallery_fields_rename();	
		ft_gallery_display_add_buttons();	
	}
	
	ft_gallery_fields_live();
});	
*/

