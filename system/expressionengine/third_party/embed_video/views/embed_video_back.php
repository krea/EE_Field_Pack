<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');
?>

<div class="ft_embed_video">

	<?php
	echo form_input(
			array(
				'name' => $prefix . '[embed_video_label]',
				'value' => isset($data['embed_video_label']) ? $data['embed_video_label'] : '',
				'class' => 'ft_embed_video_label',
				'maxlength' => (int) $settings['embed_video_label_maxlength'],
				'placeholder' => lang('embed_video_default_label'),
			)
	);

	$counter = 0;
	if (!empty($data['files']) and is_array($data['files']) and count($data['files']) > 0) {
		for ($i = 0; $i < count(current($data['files'])); $i++) {

			if ($i >= $files_limit)
				continue;

			$file_v = array(
				'dir' => $data['files']['dir'][$i],
				'name' => $data['files']['name'][$i],
			);

			if (!empty($file_v['name'])) {
				?>

				<span class="ft_embed_video_item" id="<?= $files_id ?>">

					<input type="hidden" name="<?= $prefix ?>[files_id]" value="<?= $files_id ?>" />

					<span class="ft_embed_video_file">
						<span class="ft_embed_video_file_label">
							<?php echo lang('Video file'); ?>:
							[<strong class="ft_embed_video_file_name"><?= $file_v['name'] ?></strong>]
						</span>
						<button class="ft_embed_video_remove_button"><?= lang('Remove file') ?></button>
						<input type="hidden" class="ft_embed_video_dir" name="<?php echo $prefix; ?>[files][<?= $files_id ?>][dir][]" value="<?= @htmlspecialchars($file_v['dir']) ?>" />
						<input type="hidden" class="ft_embed_video_name" name="<?php echo $prefix; ?>[files][<?= $files_id ?>][name][]" value="<?= @htmlspecialchars($file_v['name']) ?>" />
					</span>

					<span class="ft_embed_video_add js_hide">
						<?php
						echo form_input(
								array(
									'name' => $prefix . '[embed_video_url]',
									'value' => isset($data['embed_video_url']) ? $data['embed_video_url'] : '',
									'class' => 'ft_embed_video_url',
									'placeholder' => lang('embed_video_default_url'),
								)
						) . '&nbsp;' . lang('or') . '&nbsp;';
						?>
						<a href="#" class="ft_embed_video_btn_add" id="<?= $files_id ?>"><?= lang('upload flash video'); ?></a>
						<span class="files_limit js_hide"><?= $files_limit ?></span>
					</span>

				</span>

				<?php
				$counter++;
			}
		}
	}

	if ($counter < $files_limit) {
		// Hidden file remove
		$random_files_id = md5(uniqid() . rand(1, 99999));
		?>

		<span class="ft_embed_video_item" id="<?= $random_files_id ?>">

			<input type="hidden" name="<?= $prefix ?>[files_id]" value="<?= $random_files_id ?>" />

			<span class="ft_embed_video_file js_hide">
				<span class="ft_embed_video_file_label">
					<?php echo lang('Video file'); ?>:
					[<strong class="ft_embed_video_file_name">&nbsp;</strong>]
				</span>
				<button class="ft_embed_video_remove_button"><?= lang('Remove file') ?></button>
				<input type="hidden" class="ft_embed_video_dir" name="<?php echo $prefix; ?>[files][<?= $random_files_id ?>][dir][]" value="" />
				<input type="hidden" class="ft_embed_video_name" name="<?php echo $prefix; ?>[files][<?= $random_files_id ?>][name][]" value="" />
			</span>

			<span class="ft_embed_video_add">
				<?php
				echo form_input(
						array(
							'name' => $prefix . '[embed_video_url]',
							'value' => isset($data['embed_video_url']) ? $data['embed_video_url'] : '',
							'class' => 'ft_embed_video_url',
							'placeholder' => lang('embed_video_default_url'),
						)
				) . '&nbsp;' . lang('or') . '&nbsp;';
				?>
				<a href="#" class="ft_embed_video_btn_add"><?= lang('upload flash video'); ?></a>
			</span>

		</span>

		<?php
	}


	if (empty($settings['embed_video_fixed_dimensions']) or (bool) $settings['embed_video_fixed_dimensions'] === FALSE) {
		?>
		<span class="ft_embed_video_dimensions">

			<strong><?php echo lang('Thumbnail size') ?></strong>:
			<?php
			echo form_input(
					array(
						'name' => $prefix . '[embed_video_width]',
						'value' => isset($data['embed_video_width']) ? $data['embed_video_width'] : $settings['embed_video_default_width'],
						'style' => 'width: 50px; text-align: center;',
						'placeholder' => $settings['embed_video_default_width'],
					)
			) . '&nbsp;x&nbsp;' . form_input(
					array(
						'name' => $prefix . '[embed_video_height]',
						'value' => isset($data['embed_video_height']) ? $data['embed_video_height'] : $settings['embed_video_default_height'],
						'style' => 'width: 50px; text-align: center;',
						'placeholder' => $settings['embed_video_default_height'],
					)
			) . '&nbsp;px';
			?>
		</span>

		<?php
	}
	?>

</div>