<?php $this->load->view('header') ?>

<div class="KreaLayoutPg">

	<div class="KreaLayoutTlb">	
		<a href="<?= $BASE.AMP ?>method=settings" class="btn dsbl"><?= lang('button_back_to_settings') ?></a>
	</div><!-- end .KreaLayoutTlb -->

	<div class="KreaLayoutBx">
		<div class="hdBx">
			<h1><?= $TITLE ?></h1>
		</div><!-- end .hdBx -->

		<?= form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=promotions'.AMP.'method=settingsGeneralSubmit', 'class="vldF"'); ?>
		
		<table cellspacing="0" cellpadding="0" border="0">
		<tbody>

		<tr>
			<td width="40%">
				<label for="image_upload_dir"><span><?= lang('settings_general_settings_label_image_upload_dir') ?></span>
				<small><?= lang('settings_general_settings_label_image_upload_dir_desc')?></small>				
				</label>
			</td>
			<td>
				<?= form_dropdown('image_upload_dir', $directory_options, $record['image_upload_dir'], 'id="image_upload_dir"') ?>
			</td>
		</tr>			
		
		<tr>
			<td width="40%">
				<label for="live_look_template"><span><?= lang('settings_general_settings_label_live_look_template') ?></span>
				<small><?= lang('settings_general_settings_label_live_look_template_desc')?></small>				
				</label>
			</td>
			<td>
				<?= form_dropdown('live_look_template', $templates_options, $record['live_look_template'], 'id="live_look_template"') ?>
			</td>
		</tr>			
													
		</tbody>
		</table>
		
		<p>
			<input type="submit" name="save" value="<?= lang('settings_general_button_submit') ?>" class="submit" />
			&nbsp;&nbsp;<a class="rstBtn" href="<?= $BASE.AMP ?>method=settings"><?= lang('cancel') ?></a>			
		</p>
		
		<?= form_close() ?>
	</div>
</div>

