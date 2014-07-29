var submitButtonsLocked = false;

function helpdesk_add_validate_event()
{
$('.vldF').ajaxForm(
	{
		beforeSubmit: function(){
			//lock submit button
		
			if (submitButtonsLocked)
			{
				return false;
			}
			else
			{
				submitButtonsLocked = true;
				return true;
			}
		},
	
		beforeSerialize: function(){
		
			//clear titled values
	
			/*
			$('.vldF').find('input').each(function(k,v)
			{
				if ($(this).attr('title')){
					if ($(this).attr('title') == $(this).val()) {
						$(this).val('');
					}
				}
			});
			$('.vldF').find('textarea').each(function(k,v)
			{
				if ($(this).attr('title') == $(this).val()){
					$(this).val('');
				}
			});	
			*/
							
			return true;	
		},

		error: function(resultData)
		{
			alert("Unknown error");
		},
		
		success: function(resultData){	
				
			response = eval('('+ resultData + ')');
	
			$('input.error').removeClass('error');
			$('select.error').removeClass('error');
			$('textarea.error').removeClass('error');
			$('label.errorCheck').removeClass('errorCheck');
			
			isFocused = false;	//zameriam prvu hodnotu co nesedi
				
			if (response['result'] == 'error')
			{
				for (var fieldArrIndex in response['fields']) {
				
					var fieldIndex = response['fields'][fieldArrIndex];
					
					/*------------------------------
						je to typ input text?
					-------------------------------*/
					
					if ($('input[name=\''+fieldIndex+'\'][type=text]').size())
					{
						$('input[name=\''+fieldIndex+'\'][type=text]').addClass('error');
						if (!isFocused) $('input[name=\''+fieldIndex+'\'][type=text]').focus();
						isFocused = true;					
					}
					
					/*------------------------------
						je to typ select?
					-------------------------------*/
					
					if ($('select[name=\''+fieldIndex+'\']').size())
					{
						$('select[name=\''+fieldIndex+'\']').addClass('error');
						if (!isFocused) $('select[name=\''+fieldIndex+'\']').focus();
						isFocused = true;
					}
					
					/*------------------------------
						je to typ textarea?
					-------------------------------*/
					
					if ($('textarea[name=\''+fieldIndex+'\']').size())
					{
						$('textarea[name=\''+fieldIndex+'\']').addClass('error');
						if (!isFocused) $('textarea[name=\''+fieldIndex+'\']').focus();
						isFocused = true;
					}
					
					/*------------------------------
						je to typ checkbox?
					-------------------------------*/
	
					if ($('input[name=\''+fieldIndex+'\'][type=checkbox]').size())
					{
						$('input[name=\''+fieldIndex+'\'][type=checkbox]').parent('div').children('label').addClass('errorCheck');
						$('input[name=\''+fieldIndex+'\'][type=checkbox]').parent('label').addClass('errorCheck');
						if (!isFocused) $('input[name=\''+fieldIndex+'\'][type=checkbox]:first').focus();
						isFocused = true;
					}												
					/*------------------------------
						je to typ radio?
					-------------------------------*/
	
					if ($('input[name=\''+fieldIndex+'\'][type=radio]').size())
					{
						$('input[name=\''+fieldIndex+'\'][type=radio]').parent('div').children('label').addClass('errorCheck');
						if (!isFocused) $('input[name=\''+fieldIndex+'\'][type=radio]:first').focus();
						isFocused = true;
					}				
				}
				
				//showMsg(response['message']);
				
			}		
			
			// redirect if success
			
			if (response['result'] == 'success')
			{
				if (response['redirect'])
				{
					redirectUrl = response['redirect'].replace(/\&amp;/g,'&');
					location.href = redirectUrl;
				}
				else
				{
					if (response['load'])
					{
						$.ajax({
						  url: response['load'].replace(/\&amp;/g,'&'),
						  cache: false,
						  success: function(html){

						  	contentStream = $(html).find(response['content']);
						  	$(response['content']).html(contentStream.html());
						  	
						  	if (response['eval'])
						  	{
						  		eval(response['eval']);
						  	}
						  }
						});
					}

					jQuery(function($){
						$.ee_notice(response['message'],{open: true, type:response['result']});
						setTimeout(function(){ $.ee_notice.destroy(); }, 2000);
					});						
				}
			}
			else
			{
					jQuery(function($){
						$.ee_notice(response['message'],{open: true, type:response['result']});
						setTimeout(function(){ $.ee_notice.destroy(); }, 5000);
					});				
			}		

			//reset titled values
	
			/*
			$('.vldF').find('input').each(function(k,v)
			{

				if ($(this).attr('title')){
					if ($(this).val() == '' && !$(this).is(':focus')) {
						$(this).val($(this).attr('title'));
					}
				}
			});
			$('.vldF').find('textarea').each(function(k,v)
			{
				if ($(this).attr('title')){
					if ($(this).val() == '' && !$(this).is(':focus')) {
						$(this).val($(this).attr('title'));
					}
				}
			});	
			*/
			
			// unlock submit button
			
			submitButtonsLocked = false;
			
			return true;
		}
	});	
}

helpdesk_add_validate_event();