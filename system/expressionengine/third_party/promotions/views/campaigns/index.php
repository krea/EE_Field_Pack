<?php $this->load->view('header') ?>

<div class="KreaLayoutPg">
	<div class="KreaLayoutBx">
		<div class="hdBx">
			<div class="boxL">
				<h1><?= $TITLE; ?></h1>
			</div>
			<div class="boxR">
				<a href="<?= $BASE.AMP.'method=campaignsOverview'?>" class="btn"><?= lang('campaigns_button_new') ?></a>
			</div>
		</div><!-- end .hdBx -->
		
		<div class="box">
			<?= form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=promotions'.AMP.'method=campaigns', 'class="fltr" id="fltrForm"') ?>
				<fieldset>
					<div class="row">
						<div class="fld">
							<?= form_dropdown('status', $statuses, $filter['status'], 'onchange="$(\'#fltrForm\').submit();"') ?>
						</div>					
					
						<div class="fld">	
							<?= form_dropdown('start_date', $dates, $filter['start_date'], 'onchange="$(\'#fltrForm\').submit();"') ?>
						</div>
						
						<?php /* if (!empty($sites)): ?>
						<div class="fld">	
							<?= form_dropdown('site_id', $sites, $filter['site_id'], 'onchange="$(\'#fltrForm\').submit();"') ?>
						</div>
						<?php endif; */ ?>
						
						<div class="fld">
							<?= form_dropdown('limit', $limits, $filter['limit'], 'onchange="$(\'#fltrForm\').submit();"') ?>
						</div>
					</div>
					<div class="row">
						<div class="fld">
							<input type="text" value="<?= $filter['keyword']?$filter['keyword']:lang('campaigns_filter_keyword') ?>" name="keyword" title="<?= lang('campaigns_filter_keyword') ?>" onclick="if ($(this).val()==$(this).attr('title')) $(this).val('');" onblur="if ($(this).val()=='') $(this).val($(this).attr('title'));" />
						</div>
						<div class="fld">
							<button type="submit" class="btn"><?= lang('campaigns_button_search') ?></button>
						</div>
						
						<a href="<?= $BASE.AMP ?>method=campaigns&amp;reset=1" class="rstBtn"><?= lang('reset_filter') ?></a>
					</div>
				</fieldset>
			<?= form_close() ?>
		</div><!-- end .box -->
		
		<?php if (count($entries)): ?>
		
		<div class="box">
					
			<?= form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=promotions'.AMP.'method=campaigns', 'class="cmpngLst"') ?>
			
					<table>
						<?php foreach ($entries as $c): ?>
						
						<tr>
							<td><strong><?= htmlspecialchars($c['campaign_title']) ?></strong> (<?= @$campaign_addon[$c['campaign_addon']] ?>)</a></td>
					
							
							<td class="algR"><?= typography_time($c['start_date']) ?></td>
							<td class="algL"><?= typography_time($c['end_date']) ?></td>		
							<td><?= campaign_status($c) ?></td>							
							<td class="algR redLinks">
								<?php if (!$c['draft']): ?>
								<a href="<?= $BASE.AMP ?>method=campaignsReport&amp;campaign_id=<?= $c['campaign_id'] ?>"><?= lang('report') ?></a>&nbsp;&nbsp;&nbsp;
								<?php endif; ?>
								<?php if ($live_look_url && !$c['draft']): ?>
								<a target="_blank" href="<?= $live_look_url.$c['campaign_id'] ?>"><?= lang('preview') ?></a>&nbsp;&nbsp;&nbsp;
								<?php endif; ?>
								<a href="<?= $BASE.AMP ?>method=campaignsOverview&amp;campaign_id=<?= $c['campaign_id'] ?>"><?= lang('edit') ?></a>&nbsp;&nbsp;&nbsp;
								<a href="<?= $BASE.AMP ?>method=campaignsDelete&amp;campaign_id=<?= $c['campaign_id'] ?>" onclick="return confirm('<?= addslashes(lang('delete_confirm')) ?>')"><?= lang('delete') ?></a></td>	
							<td class="chck"><input name="campaign[]" type="checkbox" value="<?= $c['campaign_id'] ?>" /></td>
					      </tr>
					      
					      <?php endforeach; ?>

					</table>
					
					
					<div class="boxL">
						<?= $pagination ?>
					</div>					
					<div class="boxR">
						<div class="fld">
							<select name="mass_action">
								<option value=""><?= lang('campaigns_label_mass_action') ?></option>
								<optgroup label="<?= lang('campaigns_label_mass_action_status') ?>">
									<option value="paused_1"><?= lang('campaigns_label_mass_action_paused_on') ?></option>
									<option value="paused_0"><?= lang('campaigns_label_mass_action_paused_off') ?></option>
								</optgroup>																									
							</select>
						</div>
						
						<button type="submit" class="btn"><?= lang('campaigns_button_commit') ?></button>
						
						<div class="chckAll">
						<input name="all_campaign" type="checkbox" value="1" />
						</div>
						
					</div>      
				
			<?= form_close() ?>
			
			<?php else: ?>
			
			<p><em><?= lang('message_campaigns_no_campaign_match_that_criteria') ?></em></p>
			
			<?php endif; ?>
			
		</div><!-- end .box -->
		
	</div><!-- end .KreaLayoutBx -->
	
</div>
