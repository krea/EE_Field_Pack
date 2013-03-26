$(document).ready(function(){
	// forms input placeholder
	$("input, textarea").focus(function(){ $(this).addClass('write'); if (this.value==this.title){ this.value=''; } });
	$("input, textarea").blur(function(){ $(this).removeClass('write'); if (this.value!=this.title){ $(this).addClass('write'); } if (this.value==''){ this.value=this.title; $(this).removeClass('write'); } });
	
	$("input").each(function(){
		if (this.value!=this.title){ $(this).addClass('write'); }
	});
});