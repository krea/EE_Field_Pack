<?php $this->load->view('header') ?>


<div class="KreaLayoutPg">
	<div class="KreaLayoutBx">
		<div class="hdBx">
			<h1><?= $TITLE; ?></h1>
		</div><!-- end .hdBx -->
		
		<?= form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=promotions'.AMP.'method=campaignsCompletitionSubmit', 'class="vldF"'); ?>
		
		<input type="hidden" name="campaign_id" value="<?= (int)@$record['campaign_id'] ?>" />
		
		<table cellpadding="0" cellspacing="0">		

		<tr>
			<td width="20%">
				<label for="use_email"><?= lang('campaign_label_use_email') ?></label>
			</td>
			<td>
				<?= form_dropdown('use_email', array(
						'unique' => lang("field_unique"),
						'required' => lang("field_required"),
						'not_required' => lang("field_not_required"),
						'optional' => lang("field_optional")
					), $record['use_email'], 'id="use_email"'); ?>
			</td>
		</tr>					
		<tr>
			<td width="20%">
				<label><?= lang('campaign_label_custom_fields') ?></label>
			</td>
			<td>
				<?php if (!empty($fields)): ?>
								
				<table cellpadding="0" cellspacing="0" class="checkBoxFieldTable">
					<tbody>

						<?php foreach ($fields as $f): ?>
					
						<tr>
							<td><label><input <?= isset($campaign_fields[$f['field_id']])?'checked="checked"':'' ?> type="checkbox" name="custom_field[<?= $f['field_id'] ?>]" value="1"/> <?= htmlspecialchars($f['field_label']); ?></label></td>
							<td><label><input type="radio" <?= isset($campaign_fields_required[$f['field_id']])?'checked="checked"':'' ?> name="custom_field_required[<?= $f['field_id'] ?>]" value="1" /> <?= lang('custom_field_required'); ?></label> &nbsp; <label><input type="radio" <?= !isset($campaign_fields_required[$f['field_id']])?'checked="checked"':'' ?> name="custom_field_required[<?= $f['field_id'] ?>]" value="0" /> <?= lang('custom_field_not_required'); ?></label></td>						
						</tr>
							
						<?php endforeach; ?>
														
					</tbody>					
				</table>
				<?php else: ?>	
					<?= lang('campaign_label_custom_fields_empty'); ?>
				<?php endif;?>	
			</td>
		</tr>
			
		<tr>
			<td width="20%">
				<label><?= lang('campaign_label_mailing_lists') ?></label>
			</td>
			<td>
		
				<?php if (!empty($mailing_lists)): ?>
				<table cellpadding="0" cellspacing="0" class="checkBoxFieldTable">
					<tbody>
						
					
						<?php foreach ($mailing_lists as $list_id=>$list_title): ?>
					
						<tr>
							<td><label><input <?= isset($lists[$list_id])?'checked="checked"':'' ?>  type="checkbox" name="campaign_list[<?= $list_id ?>]" value="1"/> <?= htmlspecialchars($list_title); ?></label></td>					
						</tr>
							
						<?php endforeach; ?>
														
					</tbody>					
				</table>
				<?php else: ?>	
					<?= lang('campaign_label_mailing_lists_empty'); ?>
				<?php endif;?>	
			</td>
		</tr>			
		<tr>
			<td width="20%">
				<label for="use_terms_of_service"><?= lang('campaign_label_use_terms_of_service') ?></label>
			</td>
			<td>
				<?= form_dropdown('use_terms_of_service', array(
						'not_required' => lang("no"),				
						'required' => lang("yes"),
					), $record['use_terms_of_service'], 'id="use_terms_of_service"'); ?>
			</td>
		</tr>	
		<tr>
			<td width="20%">
				<label for="use_captcha"><?= lang('campaign_label_use_captcha') ?></label>
			</td>
			<td>
				<?= form_dropdown('use_captcha', array(
						0 => lang("no"),
						1 => lang("yes"),
					), $record['use_captcha'], 'id="use_captcha"'); ?>
			</td>
		</tr>		
		<tr>
			<td width="20%">
				<label for="return_url"><?= lang('campaign_label_return_url') ?></label>
			</td>
			<td>
				<?= form_input('return_url', htmlspecialchars(@$record['return_url']), 'class="url" id="return_url"') ?>
			</td>
		</tr>
		<tr>
			<td width="20%">
				<label for="return_url"><?= lang('campaign_label_options') ?></label>
			</td>
			<td>
				<table cellpadding="0" cellspacing="0" class="checkBoxFieldTableNosort">
					<tbody>
						<tr>
							<td><label><input <?= @$record['paused']?'checked="checked"':'' ?>  type="checkbox" name="paused" value="1"/> <?= lang('campaign_label_paused') ?></label></td>					
						</tr>
						<tr>
							<td><label><input <?= @$record['winners_announced']?'checked="checked"':'' ?>  type="checkbox" name="winners_announced" value="1"/> <?= lang('campaign_label_winners_announced') ?></label>
							
							<br />
							<?= form_textarea('winners_announced_report', htmlspecialchars(@$record['winners_announced_report']), 'id="winners_announced_report" style="display:none"'); ?>
							</td>					
						</tr>						
					</tbody>
				</table>
			</td>
		</tr>			
								
		</table>
		
		<p class="submitButtons">
			<input type="button" onclick="location.href='<?= $SOFT_BASE.'&' ?>method=campaignsAddon&campaign_id=<?= (int)$_GET['campaign_id'] ?>'" name="campaing_content_button_back" value="<?= lang('campaing_content_button_back') ?>" class="submit" />
			<input type="submit" name="campaing_content_button_continue" value="<?= lang('campaing_completition_button_continue') ?>" class="submit" />
		
			&nbsp;&nbsp;<a class="rstBtn" href="<?= $BASE.AMP ?>method=campaigns"><?= lang('cancel') ?></a>				
		</p>		
		
		<?= form_close(); ?>
		
	</div><!-- end .KreaLayoutBx -->
	
</div>
