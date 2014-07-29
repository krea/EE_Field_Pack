<?php $this->load->view('header') ?>


<div class="KreaLayoutPg">
	<div class="KreaLayoutBx">
		<div class="hdBx">
			<h1><?= $TITLE; ?></h1>
		</div><!-- end .hdBx -->
		
		<?= form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=promotions'.AMP.'method=campaignsContentSubmit', 'enctype="multipart/form-data" class="vldF"'); ?>
		
		<input type="hidden" name="campaign_id" value="<?= (int)@$record['campaign_id'] ?>" />
		
		<table cellpadding="0" cellspacing="0">	
		<tr>
			<td width="20%">
				<label for="head_image"><?= lang('campaign_label_image') ?></label>
			</td>
			<td class="redLinks">
				<?php if ($image_upload_dir): ?>
					<?= $image_upload_field ?>
				<?php else: ?>
					<a href="<?= $BASE.AMP.'method=settingsGeneral' ?>" target="_blank"><?= lang('settings_upload_settings_failed'); ?></a>
				<?php endif; ?>				
			</td>
		</tr>		
		<tr>
			<td width="20%">
				<label for="head_title"><?= lang('campaign_label_head_title') ?></label>
			</td>
			<td>
				<?= form_input('head_title', (@$record['head_title']===NULL)?@$record['campaign_title']:htmlspecialchars(@$record['head_title']), 'id="head_title"') ?>
			</td>
		</tr>		
		<tr>
			<td width="20%">
				<label for="head_note"><?= lang('campaign_label_head_note') ?></label>
			</td>
			<td>
				<?= form_textarea('head_note', htmlspecialchars(@$record['head_note']), 'id="head_note"') ?>
			</td>
		</tr>	
		<tr>
			<td width="20%">
				<label for="foot_note"><?= lang('campaign_label_foot_note') ?></label>
			</td>
			<td>
				<?= form_textarea('foot_note', htmlspecialchars(@$record['foot_note']), 'id="foot_note"') ?>
			</td>
		</tr>
		<tr>
			<td width="20%">
				<label for="terms"><?= lang('campaign_label_terms') ?></label>
			</td>
			<td>
				<?= form_textarea('terms', htmlspecialchars(@$record['terms']), 'id="terms"') ?>
			</td>
		</tr>							
		</table>
		
		<p class="submitButtons">
			<input type="button" onclick="location.href='<?= $SOFT_BASE.'&' ?>method=campaignsOverview&campaign_id=<?= (int)$_GET['campaign_id'] ?>'" name="campaing_content_button_back" value="<?= lang('campaing_content_button_back') ?>" class="submit" />
			<input type="submit" name="campaing_content_button_continue" value="<?= lang('campaing_content_button_continue') ?>" class="submit" />
		
			&nbsp;&nbsp;<a class="rstBtn" href="<?= $BASE.AMP ?>method=campaigns"><?= lang('cancel') ?></a>				
		</p>		
		
		<?= form_close(); ?>
		
	</div><!-- end .KreaLayoutBx -->
	
</div>
