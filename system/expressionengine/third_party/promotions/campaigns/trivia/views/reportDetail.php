<?php $this->load->view('header') ?>
<div class="KreaLayoutPg">

	<div class="KreaLayoutTlb">	
		<a href="<?= $BASE.AMP ?>method=campaignsReport<?= AMP ?>campaign_id=<?= $respond['campaign_id'] ?>" class="btn dsbl"><?= lang('button_back_to_report') ?></a>
	</div><!-- end .KreaLayoutTlb -->

	<div class="KreaLayoutBx">
		<div class="hdBx">
			<div class="boxL">
				<h1><?= $TITLE ?> #<?= $respond['data_id'] ?></h1>
			</div>
			<div class="boxR">			
				<?php if ($respond['valid']): ?>
					<span class="flg actCmpgn"><?= lang('trivia_flag_valid') ?></span>
				<?php else: ?>
					<span class="flg inactCmpgn"><?= lang('trivia_flag_invalid') ?></span>
				<?php endif; ?>				
			</div>	
		</div><!-- end .hdBx -->
		
		<div class="box">

			<table>	
				<tr>
					<td style="background-color: #8F9A9C; color: white; font-weight: bold" colspan="3"><?= lang('trivia_member_data') ?></td>
				</tr>
				<?php if ($respond['email']): ?>
				<tr>
					<td width="20%"><?= lang('email') ?></td>
					<td><a href="mailto:<?= $respond['email'] ?>"><?= $respond['email'] ?></a></td>
					<td>&nbsp;</td>
				<tr>			
				<?php endif; ?>
				<?php foreach ($custom_fields as $cf): ?>					
				<tr>
					<td width="20%"><?= $cf["field_label"] ?></td>
					<td><?= htmlspecialchars($respond['field_id_'.$cf["field_id"]]); ?></td>
					<td>&nbsp;</td>					
				</tr>
				<?php endforeach; ?>
				<tr>
					<td width="20%"><?= lang('ip_address') ?></td>
					<td><?= $respond['ip_address']; ?></td>
					<td>&nbsp;</td>					
				</tr>				
				<tr>
					<td style="background-color: #8F9A9C; color: white; font-weight: bold" colspan="3"><?= lang('trivia_anwer_data') ?></td>
				</tr>			
				<?php if (is_array($respond["campaign_addon_data"]["questions"])) foreach ($respond["campaign_addon_data"]["questions"] as $cad): ?>			
				<tr>
					<td width="20%"><?php print_r($cad["question"]) ?></td>
					<td><?php print_r($cad["answer"]) ?></td>					
					<td>
								<?php if ($cad['correct']): ?>
									<span class="flg actCmpgn"><?= lang('trivia_flag_valid') ?></span>
								<?php else: ?>
									<span class="flg inactCmpgn"><?= lang('trivia_flag_invalid') ?></span>
								<?php endif; ?>					
					
					</td>
				</tr>
				<?php endforeach; ?>
			</table>
			
		</div><!-- end .box -->
	
	
	</div><!-- end .KreaLayoutBx -->	
	
</div>

<script type="text/javascript">
//<![CDATA[
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
//]>
</script>
