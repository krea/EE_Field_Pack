<div class="hyperlink_ft">
	<div class="rw">
		<div class="fld bold"><input type="text" data-validation="<?= lang('msg_wrong_url_format') ?>" name="<?= $field_name ?>[hyperlink_url]" value="<?= @$data['hyperlink_url'] ?>" class="parse_link" /></div>
		<div class="lnkSts">
			
			<?php if (isset($data['hyperlink_http_status'])): ?>
				<?php if ( (int)$data['hyperlink_http_status'] < 200 || (int)$data['hyperlink_http_status'] >= 400 ): ?>
					<?php if ( (int)$data['hyperlink_http_status'] == -1 ): ?>
						<div class="stsBlt stsBltV2" title="<?= lang('hyperlink_http_connection_timeout') ?>"></div>
					<?php else: ?>
						<div class="stsBlt stsBltV2" title="<?= lang('hyperlink_http_status')?> <?= $data['hyperlink_http_status'] ?>"></div>
					<?php endif; ?>
				<?php else: ?>
				<div class="stsBlt stsBltV1" title="<?= lang('hyperlink_http_status')?> <?= $data['hyperlink_http_status'] ?>"></div>
				<?php endif; ?>
			<?php endif; ?>
			
			<?php if (isset($data['screenshot'])): ?>
			<div class="preview" title="Preview">
				<span class="icon"></span>
				<div class="tltpBx">
					<img src="<?= $data['screenshot'] ?>" />
					<span></span>
				</div><!-- end .tltpBx -->
			</div><!-- end .preview -->
			<?php endif; ?>			
		</div><!-- end .lnkSts -->
	</div>
	
	<div class="rw js_hide" rel="opt_box_title">
		<div class="fld"><input type="text" name="<?= $field_name ?>[hyperlink_title]" value="<?= @$data['hyperlink_title'] ?>" /> <span class="lbl">Title</span></div>
	</div>
	
	<div class="rw js_hide" rel="opt_box_alt">
		<div class="fld"><input type="text" name="<?= $field_name ?>[hyperlink_alt]" value="<?= @$data['hyperlink_alt'] ?>" /> <span class="lbl">Alt</span></div>
	</div>
	
	<div class="rw chckRw js_hide" rel="opt_box_rel">
		<input type="checkbox" <?= @$data['hyperlink_nofollow']?'checked="checked"':'' ?> name="<?= $field_name ?>[hyperlink_nofollow]" id="ft_hprlnk_rel" value="nofollow"/>
		<label for="ft_hprlnk_rel">rel="nofollow"</label>
	</div>
	
	<div class="rw addPnl">
		<a href="#" class="frst" rel="opt_title"><?= lang('hyperlink_btn_add_title_tag') ?></a>	
		<a href="#" rel="opt_alt"><?= lang('hyperlink_btn_add_alt_tag') ?></a>
		<a href="#" rel="opt_rel"><?= lang('hyperlink_btn_add_rel') ?></a>
	</div><!-- end .addPnl -->
</div><!-- end .hyperlink box -->