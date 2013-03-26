<?php $this->load->view('_shared/cp_header.php') ?>

<div class="modPg">
	<div class="hdBxT ac">
		<div class="boxL">
			<a href="<?= BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=hyperlink' ?>" class="btn grey"><?= lang('btn_back_to_hyperlinks') ?></a>
		</div>
	</div><!-- end .hdBx -->

	<div class="shCrtBx">
		<?= form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$_GET['module'].AMP.'method='.$_GET['method']) ?>
			<div class="hdBx ac">
				<div class="boxL">
					<h1><?= lang('label_generate_screenshot') ?></h1>
				</div>
			</div><!-- end .hdBx -->
			
			<div class="box">
				<table cellspacing="0" cellpadding="0" border="0">
					<tbody>
						<tr>
							<td width="300px">
								<label><?= lang('label_generate_screenshot') ?></label>
							</td>
							<td>							
								<input type="checkbox" id="screenshot_service" name="screenshot_service" value="GrabzIt" <?= $settings["screenshot_service"]=='GrabzIt'?'checked="checked"':'' ?>>
								<label for="screenshot_service"><?= lang('yes') ?></label>
							</td>
						</tr>
						<tr class="opt_screenshot opt_grabzit">
							<td>
								<label for="grabzit_api_key"><?= lang('label_grabzit_api_key') ?></label>
							</td>
							<td>
								<?= form_input( "screenshot_services[GrabzIt][api_key]", $settings["screenshot_services"]["GrabzIt"]["api_key"], 'id="grabzit_api_key"' ) ?>

								<?php if($validation_error = @$validation_errors["screenshot_services"]["GrabzIt"]["api_key"]): ?>
								<p class="notice" style="margin:0"><?= $validation_error ?></p>
								<?php endif; ?>										
								
							</td>	
						</tr>
						<tr class="opt_screenshot opt_grabzit">
							<td>
								<label for="grabzit_api_secret"><?= lang('label_grabzit_api_secret') ?></label>
							</td>
							<td>
								<?= form_input( "screenshot_services[GrabzIt][api_secret]", $settings["screenshot_services"]["GrabzIt"]["api_secret"], 'id="grabzit_api_secret"' ) ?>
								
								<?php if($validation_error = @$validation_errors["screenshot_services"]["GrabzIt"]["api_secret"]): ?>
								<p class="notice" style="margin:0"><?= $validation_error ?></p>
								<?php endif; ?>											
							</td>		
						</tr>	
						<tr class="opt_screenshot opt_grabzit">
							<td>
								<label for="grabzit_service_package"><?= lang('label_service_package') ?></label>
							</td>
							<td>	
								<?= form_dropdown( "screenshot_services[GrabzIt][service_package]", $grabzit_packages, $settings["screenshot_services"]["GrabzIt"]["service_package"], 'id="grabzit_service_package"' ) ?>
								
								<?php if($validation_error = @$validation_errors["screenshot_services"]["GrabzIt"]["service_package"]): ?>
								<p class="notice" style="margin:0"><?= $validation_error ?></p>
								<?php endif; ?>								
							</td>	
						</tr>		
						<tr class="opt_screenshot opt_grabzit">
							<td>
								<label for="grabzit_image_width"><?= lang('label_image_width') ?></label>
							</td>
							<td>
								<?= form_input( "screenshot_services[GrabzIt][image_width]", $settings["screenshot_services"]["GrabzIt"]["image_width"], 'id="grabzit_image_width"' ) ?>
								
								<?php if($validation_error = @$validation_errors["screenshot_services"]["GrabzIt"]["image_width"]): ?>
								<p class="notice" style="margin:0"><?= $validation_error ?></p>
								<?php endif; ?>
							</td>	
						</tr>	
						<tr class="opt_screenshot opt_grabzit">
							<td>
								<label for="grabzit_image_height"><?= lang('label_image_height') ?></label>
							</td>
							<td>
								<?= form_input( "screenshot_services[GrabzIt][image_height]", $settings["screenshot_services"]["GrabzIt"]["image_height"], 'id="grabzit_image_height"' ) ?>
							
								<?php if($validation_error = @$validation_errors["screenshot_services"]["GrabzIt"]["image_height"]): ?>
								<p class="notice" style="margin:0"><?= $validation_error ?></p>
								<?php endif; ?>							
							</td>	
						</tr>																		
						<tr class="opt_screenshot">
							<td>
								<label for="screenshot_dir"><?= lang('label_download_directory') ?></label>
							</td>
							<td>
								<?= form_dropdown( "screenshot_dir", $directory_options, $settings["screenshot_dir"], 'id="screenshot_dir"' ) ?>
								
								<?php if($validation_error = @$validation_errors["screenshot_dir"]): ?>
								<p class="notice" style="margin:0"><?= $validation_error ?></p>
								<?php endif; ?>								
							</td>	
						</tr>																	
					</tbody>
				</table>
			</div><!-- end .box -->
			<br />
			
			<div class="hdBx ac">
				<div class="boxL">
					<h2 class="h1"><?= lang('label_link_valiation') ?></h2>
				</div>
			</div><!-- end .hdBx -->
			
			<div class="box">
				<table cellspacing="0" cellpadding="0" border="0">
					<tbody>
						<tr>
							<td width="300px">
								<label><?= lang('label_publish_entry_with_invalid_links') ?></label>
							</td>
							<td>
								<input type="checkbox" id="publish_entry_with_invalid_links" name="publish_entry_with_invalid_links" <?= $settings["publish_entry_with_invalid_links"]?'checked="checked"':'' ?> value="1">
								<label for="publish_entry_with_invalid_links"><?= lang('yes') ?></label>
							</td>
						</tr>
						<tr>
							<td class="vaT">
								<label><?= lang('label_schedule_validation_of_links_schedule') ?></label>
							</td>
							<td>	
								<div>
									<input type="checkbox" id="schedule_validation_of_links" name="schedule_validation_of_links[schedule]" <?= $settings["schedule_validation_of_links"]["schedule"]?'checked="checked"':'' ?> value="yes">
									<label for="schedule_validation_of_links"><?= lang('yes') ?></label>
								</div>
								<div class="hghltBx opt_schedule_validation">
									<label id="cron_url"><?= lang('label_add_this_url_to_cronjobs') ?></label>
									<input type="text" id="cron_url" name="" readonly="readonly" value="<?= $hyperlink_schedule_validation_url ?>" title="" />
								</div>
							</td>
						</tr>
						<tr class="opt_schedule_validation">
							<td>
								<label for="change_status"><?= lang('label_schedule_validation_of_links_change_status') ?></label>
							</td>
							<td>
								<?= form_dropdown( "schedule_validation_of_links[change_status]", $statuses, $settings["schedule_validation_of_links"]["change_status"], 'id="screenshot_dir"' ) ?>
							</td>	
						</tr>
					</tbody>
				</table>
	
				<p>
					<button class="btn" type="submit" name="button_submit"><?= lang('btn_save_changes') ?></button>
					&nbsp;&nbsp;<a class="rstBtn" href="<?= BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=hyperlink' ?>"><?= lang('label_discard_changes') ?></a>
				</p>
			</div><!-- end .box -->
		<?= form_close() ?>
	</div><!-- end .shCrtBx -->
	
	<div class="shCrtFtr ac">
		<div class="boxL">
			<a href="<?= BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=hyperlink' ?>" class="btn grey"><?= lang('btn_back_to_hyperlinks') ?></a>
		</div>
	</div><!-- end .shCrtFtr -->
</div><!-- end .modPg -->

<script type="text/javascript">
function toogle_opt_screenshot()
{
	if ($('#screenshot_service').is(':checked'))
	{
		$('.opt_screenshot').css('display','');
	}
	else
	{
		$('.opt_screenshot').css('display','none');
	}
}
$('#screenshot_service').click(function(){
	toogle_opt_screenshot();
});
toogle_opt_screenshot();
function toogle_opt_schedule_validation()
{
	if ($('#schedule_validation_of_links').is(':checked'))
	{
		$('.opt_schedule_validation').css('display','');
	}
	else
	{
		$('.opt_schedule_validation').css('display','none');
	}
}
$('#schedule_validation_of_links').click(function(){
	toogle_opt_schedule_validation();
});
toogle_opt_schedule_validation();
//

</script>