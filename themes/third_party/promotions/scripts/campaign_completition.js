$(function() {
	$( "table.checkBoxFieldTable" ).sortable({
		helper: function(e, ui) {
			ui.children().each(function() {
				$(this).width($(this).width());
			});
			return ui;
		},
		axis: "y",
		items: 'tr',
		opacity: 0,
		cursor: 'move',
		stop: function(e, ui) {
		},
	});
});

function refreshCheckboxGroup()
{
	$('.checkBoxFieldTable input[type=radio]').each(function(k,v){
	
		if ($(v).parent().parent().parent().find('input[type=checkbox]').is(':checked'))
		{
			$(v).parent().parent().parent().find('input[type=radio]').parent('label').removeAttr('style');
			$(v).parent().parent().parent().find('input[type=radio]').removeAttr('disabled');
		}
		else
		{
			$(v).parent().parent().parent().find('input[type=radio]').parent('label').css('color','#999');	
			$(v).parent().parent().parent().find('input[type=radio]').attr("disabled", "disabled");
		}
	});
}

$('.checkBoxFieldTable input[type=checkbox]').click(function(){
	refreshCheckboxGroup();
});


refreshCheckboxGroup();

$('input.url').blur(function(){
	if ($(this).val())
	{
		if ($(this).val().split('://').length == 1)
		{
			$(this).val('http://'+$(this).val());
		}
	}
});

function refreshWinnersAnnounced()
{
	if ($('input[name="winners_announced"]').is(':checked'))
	{
		$('#winners_announced_report').show();
	}
	else
	{
		$('#winners_announced_report').hide();
	}
}

$('input[name="winners_announced"]').click(function() {refreshWinnersAnnounced()});
refreshWinnersAnnounced();
