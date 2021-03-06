<div class="ft-picture jsPicture">

	<input type="hidden" name="<?= $field_name ?>" value="<?= $picture_id ?>" />
	<table cellpadding="0" cellspacing="0">
		<tr>
			<td class="ft-picture-col-file">
				<a href="#" data-error_file_is_not_image="<?= lang('error_file_is_not_image') ?>" class="ft-picture-add jsPictureUpload" data-upload_dir="<?= $settings["picture_upload_dir"] ?>" <?= ($picture_thumb && $picture_image)?'style="display:none"':'' ?>><span><?= lang("picture_add") ?></span></a>
				
				<div class="ft-picture-placeholder jsPicturePlaceholder" style="<?= ($picture_thumb && $picture_image)?"background-image:url(".$picture_thumb.")":"display:none;" ?>" >
					<div class="ft-picture-remove jsPictureRemove"></div>
				</div>
				
				<input type="hidden" name="<?= 'picture['.$picture_id.'][upload_dir]' ?>" class="jsImageUploadDir" value="<?= htmlspecialchars($picture_upload_dir) ?>" />
				<input type="hidden" name="<?= 'picture['.$picture_id.'][image]' ?>" class="jsImage" value="<?= htmlspecialchars($picture_image) ?>" />								
			</td>
			<td class="ft-picture-col">
				<div class="ft-picture-dt">
					<textarea rows="3" style="width: 40%;" name="picture[<?= $picture_id ?>][description]" placeholder="<?= lang('picture_description_placeholder') ?>"><?= htmlspecialchars($picture_description) ?></textarea>
				</div>			
				<div class="ft-picture-dt">
					<?= form_dropdown('picture['.$picture_id.'][alignment]', $alignment_options, $picture_alignment) ?>					
					<?= form_dropdown('picture['.$picture_id.'][size]', $sizes_options, $picture_size, 'class="jsPictureSize"') ?>
					<div style="clear: both"></div>										
				</div>	
				<div class="ft-picture-dt">				
					<input class="jsPictureUrl" type="text" style="width: 40%;" name="picture[<?= $picture_id ?>][url]" placeholder="http://" value="<?= htmlspecialchars($picture_url) ?>" />						
				</div>
				<div style="clear: both"></div>										
			</td>		
		</tr>
	</table>
</div>



<style>

</style>


