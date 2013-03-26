var Hyperlink;

(function($) {

	//** -------------------------
	//**	HYPERLINK
	//** -------------------------	

	Hyperlink = function() {
	
		//constructor
		
		$('.hyperlink_ft').each(function(k,v)
		{
			Hyperlink.bindFuctions($(v));
		});
	}
	
	//** -------------------------
	//**	HYPERLINK
	//** -------------------------	
	
	Hyperlink.bindFuctions = function(fieldtype) {
		
		//functions
		
		function updateSeparators(obj)
		{
			$(obj).find('.addPnl .frst').removeClass('frst');
			$(obj).find('.addPnl a:visible:first').addClass('frst');		
		}
		
		function showOptAlt(obj)
		{
			$(obj).find("div[rel=opt_box_alt]").removeClass('js_hide').removeAttr('style');
			$(obj).find("a[rel=opt_alt]").hide();
			updateSeparators(obj);	
		}
		
		function showOptTitle(obj)
		{
			$(obj).find("div[rel=opt_box_title]").removeClass('js_hide').removeAttr('style');
			$(obj).find("a[rel=opt_title]").hide();
			updateSeparators(obj);	
		}
		
		function showOptRel(obj)
		{
			$(obj).find("div[rel=opt_box_rel]").removeClass('js_hide').removeAttr('style');
			$(obj).find("a[rel=opt_rel]").hide();
			updateSeparators(obj);	
		}			
		
		function isEmpty(val)
		{
			if (val != 'http://' && val != 'https://' && val != '')
			{
				return false;
			}
			else
			{
				return true;
			}
		}	
		
		//buttons
		
		fieldtype.find("a[rel=opt_alt]").click(function()
		{
			showOptAlt($(this).closest(".hyperlink_ft"));
			return false;	
		});
	
		fieldtype.find("a[rel=opt_title]").click(function()
		{
			showOptTitle($(this).closest(".hyperlink_ft"));	
			return false;						
		});	
	
		fieldtype.find("a[rel=opt_rel]").click(function()
		{
			showOptRel($(this).closest(".hyperlink_ft"));
			return false;				
		});	
		
		//inputs
		
		if (isEmpty(fieldtype.find('.parse_link').val()))
		{
			fieldtype.find('.parse_link').val('http://');
			fieldtype.find('.parse_link').addClass('unwrite');		    
		}
		else
		{
			fieldtype.find('.parse_link').removeClass('unwrite');
		}
		
		fieldtype.find('.parse_link').blur(function()
		{		
			if ($(this).val().substr(0,7).toLowerCase() != 'http://' && $(this).val().substr(0,8).toLowerCase() != 'https://')
			{
				$(this).val( 'http://' + $(this).val() );
			}	
			if (!isEmpty($(this).val()))
			{
				$(this).removeClass('unwrite');
				
				//regular chcek
				
				re = new RegExp("^[A-Za-z]+://[A-Za-z0-9-_]+\\.[A-Za-z0-9-_%&\?\/.=]+$");
		
				if ( ! re.test($(this).val()) ) 
				{
					alert($(this).attr("data-validation") + ' "' + $(this).val() + '"');
					$(this).focus();
				}
			}
			else
			{
				$(this).addClass('unwrite');
			}	
		});	
		
		fieldtype.find('.parse_link').focus(function()
		{	
			$(this).removeClass('unwrite');
		});			
		
		if (fieldtype.find("div[rel=opt_box_title] input").val() != '')
		{
			showOptTitle(fieldtype.find("div[rel=opt_box_title]").closest(".hyperlink_ft"));
		}
		
		if (fieldtype.find("div[rel=opt_box_alt] input").val() != '')
		{
			showOptAlt(fieldtype.find("div[rel=opt_box_alt]").closest(".hyperlink_ft"));
		}	

		if (fieldtype.find("div[rel=opt_box_rel] input").is(':checked'))
		{
			showOptRel(fieldtype.find("div[rel=opt_box_rel]").closest(".hyperlink_ft"));
		}			
	}	
	
	//** -------------------------
	//**	MATRIX: display cell
	//** -------------------------	

	Hyperlink.displayCell = function(cell) {
	
		//when matrix display cell
		
		Hyperlink.bindFuctions(cell);
	}	
	
	//** -------------------------
	//**	GET RANDOM STRING
	//** -------------------------	
	
	Hyperlink.randomString = function()
	{
		var chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz";
		var string_length = 16;
		var randomstring = '';
		for (var i=0; i<string_length; i++) {
			var rnum = Math.floor(Math.random() * chars.length);
			randomstring += chars.substring(rnum,rnum+1);
		}
		return randomstring;
	}
		
})(jQuery);

Hyperlink();