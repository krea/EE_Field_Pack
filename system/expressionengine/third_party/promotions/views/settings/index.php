<?php $this->load->view('header') ?>
<div class="KreaLayoutPg">

	<div class="KreaLayoutTlb">	
		<a href="<?= $BASE.AMP ?>method=campaigns" class="btn dsbl"><?= lang('button_back_to_campaigns') ?></a>
	</div><!-- end .KreaLayoutTlb -->

	<div class="KreaLayoutBx">
		<div class="hdBx">
			<h1><?= $TITLE ?></h1>
		</div><!-- end .hdBx -->

		<table cellpadding="0" cellspacing="0">
		<tr>
			<td><strong><?= lang('settings_general_settings') ?></strong></td>
			<td><?= lang('settings_general_settings_desc') ?></td>
			<td align="right"><a href="<?= $BASE.AMP ?>method=settingsGeneral" class="link"><?= lang('setup') ?></a></td>
		</tr>	
		<tr>	
			<td><strong><?= lang('settings_custom_fields') ?></strong></td>
			<td><?= lang('settings_custom_fields_desc') ?></td>
			<td align="right"><a href="<?= $BASE.AMP ?>method=settingsCustomFields" class="link"><?= lang('setup') ?></a></td>
		</tr>		
		</table>

	</div>
</div>
