<?php $this->load->view('header') ?>


<div class="KreaLayoutPg">
	<div class="KreaLayoutBx">
		<div class="hdBx">
			<h1><?= $TITLE; ?></h1>
		</div><!-- end .hdBx -->
		
		<?= form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=promotions'.AMP.'method=campaignsOverviewSubmit', 'class="vldF"'); ?>
		
		<input type="hidden" name="campaign_id" value="<?= (int)@$record['campaign_id'] ?>" />
		
		<table cellpadding="0" cellspacing="0">
		<tr>
			<td width="20%">
				<em class='required'>* </em>
				<label for="campaign_addon"><?= lang('campaign_label_campaign_type') ?></label>
			</td>
			<td>
				<?php if (@$record['campaign_addon']): ?>
					<?= form_dropdown('campaign_addon_disabled', $campaign_types, @$record['campaign_addon'], 'id="campaign_addon" disabled="disabled"') ?>
					<?= form_hidden('campaign_addon', @$record['campaign_addon']) ?>
				<?php else: ?>			
					<?= form_dropdown('campaign_addon', $campaign_types, @$record['campaign_addon'], 'id="campaign_addon"') ?>
				<?php endif; ?>
			</td>
		</tr>	
		<tr>
			<td width="20%">
				<em class='required'>* </em>
				<label for="title"><?= lang('campaign_label_campaign_title') ?></label>
			</td>
			<td>
				<?= form_input('campaign_title', htmlspecialchars(@$record['campaign_title']), 'id="title"'.((isset($record))?'':' onkeyup="liveUrlTitle()"')) ?>
			</td>
		</tr>		
		<tr>
			<td width="20%">
				<em class='required'>* </em>
				<label for="url_title"><?= lang('campaign_label_campaign_url_title') ?></label>
			</td>
			<td>
				<?= form_input('campaign_url_title', htmlspecialchars(@$record['campaign_url_title']), 'id="url_title"') ?>
			</td>
		</tr>	
		<tr>
			<td width="20%">
				<em class='required'>* </em>
				<label for="start_date"><?= lang('campaign_label_start_date') ?></label>
			</td>
			<td>
				<?= form_input('start_date', htmlspecialchars(@$record['start_date']), 'id="start_date"') ?>
			</td>
		</tr>
		<tr>
			<td width="20%">
				<em class='required'>* </em>
				<label for="end_date"><?= lang('campaign_label_end_date') ?></label>
			</td>
			<td>
				<?= form_input('end_date', htmlspecialchars(@$record['end_date']), 'id="end_date"') ?>
			</td>
		</tr>						
		</table>
		
		<p class="submitButtons">
			<input type="submit" name="field_edit_submit" value="<?= lang('campaing_overview_button_continue') ?>" class="submit" />
		
			&nbsp;&nbsp;<a class="rstBtn" href="<?= $BASE.AMP ?>method=campaigns"><?= lang('cancel') ?></a>				
		</p>		
		
		<?= form_close(); ?>
		
	</div><!-- end .KreaLayoutBx -->
	
</div>
