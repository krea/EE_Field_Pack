<?php $this->load->view('header') ?>

<div class="KreaLayoutPg">

	<div class="KreaLayoutTlb">	
		<a href="<?= $BASE.AMP ?>method=settingsCustomFields" class="btn dsbl"><?= lang('button_back_to_custom_fields') ?></a>
	</div><!-- end .KreaLayoutTlb -->

	<div class="KreaLayoutBx">
		<div class="hdBx">

				<h1><?= $TITLE ?></h1>
		</div><!-- end .hdBx -->

		<?php if (!isset($record)): ?>
			<?= form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=promotions'.AMP.'method=settingsCustomFieldsNewSubmit', 'class="vldF"'); ?>
		<?php else: ?>
			<?= form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=promotions'.AMP.'method=settingsCustomFieldsEditSubmit'.AMP.'field_id='.$record['field_id'], 'class="vldF"'); ?>
		<?php endif; ?>
		
		<table cellspacing="0" cellpadding="0" border="0">

		<tbody>
		<tr>
			<td width="40%">
				<em class='required'>* </em>
				<label for="field_type"><?= lang('settings_custom_fields_label_field_type') ?></label>
			</td>
			<td>
				<?= form_dropdown('field_type', array('text' => 'Text Input', 'textarea' => 'Textarea'), @$record['field_type'], 'id="field_type"') ?>
			</td>
		</tr>
		<tr>
			<td width="40%">
				<em class='required'>* </em>
				<label for="field_label"><span><?= lang('settings_custom_fields_label_field_label') ?></span>
					<small><?= lang('settings_custom_fields_label_field_label_desc') ?></small>
				</label>
			</td>
			<td>
				<input type="text" name="field_label" id="field_label" class="fullfield" value="<?= @$record['field_label'] ?>"/>
			</td>
		</tr>
		<tr>
			<td>
				<em class='required'>* </em>
				<label for="field_name"><span><?= lang('settings_custom_fields_label_field_name') ?></span>
					<small><?= lang('settings_custom_fields_label_field_name_desc') ?></small>
				</label>
			</td>
			<td>
				<input type="text" name="field_name" id="field_name" class="fullfield" value="<?= @$record['field_name'] ?>"/>
			</td>	
		</tr>				
		</tbody>
		</table>
		
		<p>
			<?php if (!isset($record)): ?>
				<input type="submit" name="field_edit_submit" value="<?= lang('settings_custom_fields_button_create_field') ?>" class="submit" />
			<?php else: ?>
				<input type="submit" name="field_edit_submit" value="<?= lang('settings_custom_fields_button_update_field') ?>" class="submit" />
			<?php endif; ?>
			
			&nbsp;&nbsp;<a class="rstBtn" href="<?= $BASE.AMP ?>method=settingsCustomFields"><?= lang('cancel') ?></a>				
		</p>
		
		<?= form_close() ?>
	</div>
</div>

