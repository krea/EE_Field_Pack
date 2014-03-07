<input type="hidden" name="<?= $field_name ?>" value="<?= $files_id ?>" />
<div class="ft_files">

	<div id="placeholder_<?= $files_id ?>" style="display:none">
		<div class="ft_files_item">
			<div class="ft_files_img_wrapper">
				<a class="ft_files_img" href="#">
					<div class="ft_files_img_remove"></div>
				</a>
			</div>
			<div class="ft_files_caption">
				<input type="hidden" 	class="ft_files_dir" 		name="files[<?= $files_id ?>][dir][]" />
				<input type="hidden" 	class="ft_files_name" 		name="files[<?= $files_id ?>][name][]" />
				<input type="text" 		class="ft_files_caption" 	name="files[<?= $files_id ?>][caption][]" placeholder="<?= lang('files_caption') ?>" />
			</div>
		</div>
	</div>
	
	<div class="ft_files_item_wrapper" id="<?= $files_id ?>">
	
		<?php foreach ($files as $file): ?>
		
		<div class="ft_files_item">
			<div class="ft_files_img_wrapper">
				<a class="ft_files_img" style="background-image: url(<?= htmlspecialchars($file["thumb"]) ?>)" href="#">
					<div class="ft_files_img_remove"></div>
				</a>
			</div>
			<div class="ft_files_caption">
				<input type="hidden" 	class="ft_files_dir" 		name="files[<?= $files_id ?>][dir][]" 		value="<?= htmlspecialchars($file["dir"]) ?>" />
				<input type="hidden" 	class="ft_files_name" 		name="files[<?= $files_id ?>][name][]" 		value="<?= htmlspecialchars($file["name"]) ?>" />
				<input type="text" 		class="ft_files_caption" 	name="files[<?= $files_id ?>][caption][]" 	placeholder="<?= lang('files_caption') ?>" 	value="<?= htmlspecialchars($file["caption"]) ?>"/>
			</div>
		</div>
		
		<?php endforeach; ?>
		
		<div class="ft_files_item_btn_add_wrapper">
			<span class="files_limit js_hide"><?= $files_limit ?></span>
			<a href="#" class="ft_files_btn_add" href="#">
				<span><?= lang('files_add'); ?></span>
			</a>
		</div>			
		
		<div class="ft_files_clr"></div>
	</div>

</div>
