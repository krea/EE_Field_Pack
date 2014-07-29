<?php $this->load->view('header') ?>


<div class="KreaLayoutPg">

	<div class="KreaLayoutTlb">	
		<a href="<?= $BASE.AMP ?>method=campaigns" class="btn dsbl"><?= lang('button_back_to_campaigns') ?></a>
	</div><!-- end .KreaLayoutTlb -->

	<div class="KreaLayoutBx">
		<div class="hdBx">
			<div class="boxL">
				<h1><?= $TITLE ?></h1>
			</div>
			<div class="boxR">			
				<?= campaign_status($record) ?>				
			</div>	
			
				
			
		</div><!-- end .hdBx -->
		
		<div class="box">
			<?= form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=promotions'.AMP.'method=campaignsReport'.AMP.'campaign_id='.(int)$_GET['campaign_id'], 'class="fltr" id="fltrForm"') ?>
				<fieldset>
					<div class="row">
						<div class="fld">
							<?= form_dropdown('valid', $valid_options, $filter['valid'], 'onchange="$(\'#fltrForm\').submit();"') ?>
						</div>					
					
						<div class="fld">	
							<?= form_dropdown('entry_date', $dates, $filter['entry_date'], 'onchange="$(\'#fltrForm\').submit();"') ?>
						</div>

						<div class="fld">
							<?= form_dropdown('limit', $limits, $filter['limit'], 'onchange="$(\'#fltrForm\').submit();"') ?>
						</div>
					</div>
					<div class="row">
						<div class="fld">
							<input type="text" value="<?= $filter['keyword']?$filter['keyword']:lang('trivia_filter_keyword') ?>" name="keyword" title="<?= lang('trivia_filter_keyword') ?>" onclick="if ($(this).val()==$(this).attr('title')) $(this).val('');" onblur="if ($(this).val()=='') $(this).val($(this).attr('title'));" />
						</div>
						<div class="fld">
							<button type="submit" class="btn"><?= lang('trivia_button_search') ?></button>
						</div>
						<a href="<?= $BASE.AMP ?>campaign_id=<?= (int)$_GET['campaign_id'] ?>&amp;method=campaignsReport&amp;reset=1" class="rstBtn"><?= lang('reset_filter') ?></a>
					</div>
				</fieldset>
			<?= form_close() ?>
		
		</div><!-- end .box -->
	
		<?php if (count($entries)): ?>
		
		<div class="box">
					
			<?= form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=promotions'.AMP.'method=campaignsReport'.AMP.'campaign_id='.(int)$_GET['campaign_id'], 'class="cmpngLst"') ?>
			
					<table>
						<?php foreach ($entries as $c): ?>
						
						<tr>
							<td width="150px"><?= typography_time($c['entry_date']) ?></td>	
							<td width="200px;">
								<?php if ($c['email']): ?>
									<?= $c['email'] ?>
								<?php else: ?>	
									<span style="color: #999;"><?= lang('trivia_unknown_respondent') ?></span>
								<?php endif; ?>								
							</td>
							<td style="overflow:hidden; color: #999; "><?= $c['custom_fields'] ?></td>
							<td width="150px;">
								<?php $addon_data = unserialize($c['campaign_addon_data']); 
								if (@$addon_data['score'] == @$addon_data['max_score'])
								{
									echo lang('trivia_valid_all_answers_are_correct');
								}
								else
								{
									printf(lang('trivia_valid_x_answers_of_y'), @$addon_data['score'], @$addon_data['max_score']);
								}
								?>
							</td>
													
							<td width="100px">
								<?php if ($c['valid']): ?>
									<span class="flg actCmpgn"><?= lang('trivia_flag_valid') ?></span>
								<?php else: ?>
									<span class="flg inactCmpgn"><?= lang('trivia_flag_invalid') ?></span>
								<?php endif; ?>								
							</td>
							<td width="120px" align="right" class="redLinks">
								<a href="<?= $BASE.AMP ?>method=campaignsReport&amp;campaign_id=<?= $c['campaign_id'] ?>&amp;action=detail&amp;data_id=<?= $c['data_id'] ?>"><?= lang('show_respond') ?></a>&nbsp;&nbsp;&nbsp;
								<a href="<?= $BASE.AMP ?>method=campaignsReport&amp;campaign_id=<?= $c['campaign_id'] ?>&amp;action=delete&amp;data_id=<?= $c['data_id'] ?>" onclick="return confirm('<?= addslashes(lang('delete_confirm')) ?>')"><?= lang('delete') ?></a></td>	
							<td class="chck"><input name="data[]" type="checkbox" value="<?= $c['data_id'] ?>" /></td>
					      </tr>
					      
					      <?php endforeach; ?>

					</table>
					
					
					<div class="boxL">
						<?= $pagination ?>
					</div>					
					<div class="boxR">
						<div class="fld">
							<select name="mass_action">
								<option value=""><?= lang('trivia_label_mass_action') ?></option>
								<optgroup label="<?= lang('trivia_label_mass_action_valid') ?>">
									<option value="valid_1"><?= lang('trivia_label_mass_action_valid_1') ?></option>
									<option value="valid_0"><?= lang('trivia_label_mass_action_valid_0') ?></option>
								</optgroup>																									
							</select>
						</div>
						
						<button type="submit" class="btn"><?= lang('trivia_button_commit') ?></button>
						
						<div class="chckAll">
						<input name="all_campaign" type="checkbox" value="1" />
						</div>
						
					</div>      
				
			<?= form_close() ?>
			
			<?php else: ?>
			
			<p><em><?= lang('message_no_responds_match_that_criteria') ?></em></p>
			
			<?php endif; ?>
			
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
