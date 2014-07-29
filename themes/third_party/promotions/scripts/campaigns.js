$(".chckAll input").click(function(){
	if ($(this).is(":checked"))
	{
		$('.chck input').attr("checked", "checked");
	}
	else
	{
		$('.chck input').attr("checked", false);
	}
});