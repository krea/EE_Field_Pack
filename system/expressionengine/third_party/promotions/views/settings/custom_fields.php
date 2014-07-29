<?php $this->load->view('header') ?>

<div class="KreaLayoutPg">

	<div class="KreaLayoutTlb">	
		<a href="<?= $BASE.AMP ?>method=settings" class="btn dsbl"><?= lang('button_back_to_settings') ?></a>
	</div><!-- end .KreaLayoutTlb -->

	<div class="KreaLayoutBx">
		<div class="hdBx">
			<div class="boxL">
				<h1><?= $TITLE ?></h1>
			</div>
			<div class="boxR">
				<a href="<?= $BASE ?>&amp;method=settingsCustomFieldsNew" class="btn"><?= lang('settings_custom_fields_new') ?></a>
			</div>
		</div><!-- end .hdBx -->
		
		<?php if (!count($fields)): ?>
		
		<em><?= lang('settings_custom_fields_no_fields') ?></em><br /><br /></td>
		
		<?php else: ?>
		
			<table cellpadding="0" cellspacing="0" class="sortable">
			<?php foreach ($fields as $f): ?>
			<tr class="sortable_item">
				<td><span class="dragArw"></span> <strong><?= htmlspecialchars($f['field_label']); ?><input type="hidden" name="sort[]" value="<?= $f['field_id'] ?>"/></strong></td>
				<td><?= htmlspecialchars($f['field_name']); ?></td>
				<td class="algR"><a class="link" href="<?= $BASE.AMP ?>method=settingsCustomFieldsEdit&amp;field_id=<?= $f['field_id'] ?>"><?= lang('edit') ?></a> | <a  class="link" href="<?= $BASE.AMP ?>method=settingsCustomFieldsDel&amp;field_id=<?= $f['field_id'] ?>" onclick="return confirm('<?= lang('delete_confirm') ?>')"><?= lang('delete') ?></a></td>	
			</tr> 
			<?php endforeach; ?>
			</table>
		
		<?php endif; ?>


	</div>
</div>



