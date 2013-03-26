<?php $this->load->view('header') ?>

<style>
	.question_box table {border: 1px solid #D0D7DF;}
	.question_box table tr.question_header td {background-color: #8F9A9C; color: white; padding: 5px 10px; font-weight: bold; cursor: move; }
	.question_box table td a.btn { color: white !important; }
	
	.question_box table tr td {border: 0px;}
	.question_box table tr.question_input td {border-bottom: 1px solid #D0D7DF; }
	.question_box table tr td.col1 {width: 20% !important; }	
	.answer_table {border: 0px solid red !important; margin: 0 !important; padding: 0}
	.answer_table td {padding: 0 0 5px 0  }
	.answer_table td label {font-weight: normal; }
	.answer_table td.anser_field {width: 265px}
</style>


<div class="KreaLayoutPg">
	<div class="KreaLayoutBx">
		<div class="hdBx">
			<div class="boxL">
				<h1><?= $TITLE; ?></h1>
			</div>
			<div class="boxR">
				<a href="#" class="btn button_question_add"><?= lang('trivia_button_add_question') ?></a>
			</div>			
		</div><!-- end .hdBx -->
		
		
			<?= form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=promotions'.AMP.'method=campaignsAddonProcess'.AMP.'campaign_id='.(int)$_GET['campaign_id'], 'class="vldF"') ?>			<div class="box" id="questions">
				<?= $trivia_form_fields ?>
			</div><!-- end .box -->
	
		<p class="submitButtons">
			<input type="button" onclick="location.href='<?= $SOFT_BASE.'&' ?>method=campaignsContent&campaign_id=<?= (int)$_GET['campaign_id'] ?>'" name="campaing_content_button_back" value="<?= lang('campaing_content_button_back') ?>" class="submit" />
			<input type="submit" name="campaing_content_button_continue" value="<?= lang('campaing_content_button_continue') ?>" class="submit" />
		
			&nbsp;&nbsp;<a class="rstBtn" href="<?= $BASE.AMP ?>method=campaigns"><?= lang('cancel') ?></a>				
		</p>	
	
			<?= form_close() ?>
			
		
	
	</div><!-- end .KreaLayoutBx -->
	
	
<script type="text/javascript">
//<![CDATA[

function remove_buttons_enabling()
{
	$(".question_box").each(function(k1,v1){
	
		btn_cnt = 0;
	
		$(v1).find(".button_answer_remove").each(function(k2,v2)
		{
			btn_cnt++;
		});
		
		if (btn_cnt > 1)
		{
			$(v1).find(".button_answer_remove").show();
		}
		else
		{
			$(v1).find(".button_answer_remove").hide();
		}
		
		/*
		$(v1).find(".button_answer_remove").show();
		$(v1).find(".button_answer_remove:first").hide();
		*/
	});
	
	if ($(".button_question_remove").size() > 1)
	{
		$(".button_question_remove").show();
	}
	else
	{
		$(".button_question_remove:first").hide();		
	}
}

function reorder_questions()
{
	$('.question_order').each(function(k,v){
		$(v).html(k+1);
	});
}

function assing_events()
{
	var answerTarget = false;

	$(".button_answer_add").unbind("click");
	$(".button_answer_add").click(function()
	{
		answerTarget = $(this).parent().parent().parent().find('.answer_table tbody');			
		xid_param = $("input[name=XID]").val();
		
		question_id = $(this).parent().parent().parent().find('input[name^=question]').attr("id");

		$.post(location.href, {'XID': xid_param, 'action' : 'add_answer', 'field_id': question_id}, function(data)
			{
				answerTarget.append(data);
				assing_events();
			}
		);
		return false;
	});
	
	$(".button_answer_remove").unbind("click");
	$(".button_answer_remove").click(function()
	{
		$(this).parent().parent().remove();
		assing_events();
		return false;
	});
	
	$(".button_question_add").unbind("click");
	$(".button_question_add").click(function()
	{	
		xid_param = $("input[name=XID]").val();
		$.post(location.href, {'XID': xid_param, 'action' : 'add_question'}, function(data)
			{
				$('#questions').append(data);
				assing_events();
			}
		);
		return false;
	});	
	
	$(".button_question_remove").unbind("click");
	$(".button_question_remove").click(function()
	{
		$(this).parent().parent().parent().parent().remove();
		assing_events();
		return false;
	});	
	
	
	remove_buttons_enabling();
	reorder_questions();
	
	$(function() {
		$( ".answer_table" ).sortable({
			helper: function(e, ui) {
				ui.children().each(function(k,v) {
					$(this).width($(this).width());
				});
				return ui;
			},
			axis: "y",
			items: '.answer_box',
			opacity: 0,
			cursor: 'move',
			stop: function(e, ui) {
				assing_events();
				$( ".answer_table td" ).removeAttr('style');
			},
		});	
	});			
}
assing_events();

$(function() {
	$( "#questions" ).sortable({
		helper: function(e, ui) {
			ui.children().each(function() {
				$(this).width($(this).width());
			});
			return ui;
		},
		axis: "y",
		items: '.question_box',
		opacity: 0,
		cursor: 'move',
		handle: '.question_header',
		stop: function(e, ui) {
			assing_events();
		},
	});
});


//]]>
</script>
	
</div>
