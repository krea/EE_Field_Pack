<?php $this->load->view('_shared/cp_header.php') ?>

<div class="modPg">
	<div class="hdBxT ac">
		<div class="boxR">
			<a href="<?= BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=hyperlink'.AMP.'method=settings' ?>" class="btn grey"><?= lang('bnt_settings') ?></a>
		</div>
	</div><!-- end .hdBx -->
	
	<div class="shCrtBx">
		<div class="hdBx">
			<div class="boxL">
				<h1><?= lang('title_hyperlinks') ?></h1>
			</div>
			<div class="boxR">
				<a href="#" class="btn btn_check_links"><?= lang('btn_check_links') ?></a>
			<!--	<a href="#" class="btn refresh" title="Reload">&nbsp;<i class="icn"></i></a> -->
			</div>
		</div><!-- end .hdBx -->
	
		<div class="box">
			<?= form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$_GET['module'], "id=\"fltrForm\"") ?>
				<fieldset>
					<div class="row">
						<div class="fld">
							<?= form_dropdown("filter[status]", $status_options, $filter['status'], ' onchange="$(\'#fltrForm\').submit();" ' ); ?>
						</div>
						<div class="fld">
							<?= form_dropdown("filter[sort_by]", $sort_by_options, $filter['sort_by'], ' onchange="$(\'#fltrForm\').submit();" ' ); ?>
						</div>
						<div class="fld">
							<?= form_dropdown("filter[results]", $results_options, $filter['results'], ' onchange="$(\'#fltrForm\').submit();" ' ); ?>			
						</div>
						
						<div class="fld">
							<input type="text" name="filter[keyword]" title="<?= lang('label_url_or_title') ?>" value="<?= $filter['keyword']?htmlspecialchars($filter['keyword']):lang('label_url_or_title') ?>" />
						</div>
						<div class="fld">
							<button class="btn" type="submit"><?= lang('btn_show') ?></button>
						</div>
						
						<a class="rstBtn" href="<?= BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$_GET['module'].AMP.'filter=reset' ?>"><?= lang('label_reset_filter') ?></a>
					</div>
				</fieldset>
			</form>
		</div><!-- end .box -->
		
		<?php if (empty($links)): ?>
		
		<div class="box">
			<br /><em><?= lang('msg_no_links') ?></em><br />&nbsp;
		</div>
		
		<?php else: ?>
		
		<div class="box">
			<div class="prlLst">
				<table>
					<thead>
						<th class="algL"><?= lang('table_hyperlink') ?></th>
						<th class="algL"><?= lang('table_entry') ?></th>         
						<th class="algL"><?= lang('table_clicks') ?></th>
						<th class="algC fix01"><?= lang('table_status') ?></th>
					</thead>						
					<tbody>
						<?php foreach ($links as $link): ?>
						<tr>
							<td class="algL hyperlink_url" rel_id="<?= $link["hyperlink_id"] ?>">
								<a href="<?= htmlspecialchars($link["hyperlink_url"]) ?>" target="_blank"><?= htmlspecialchars($link["hyperlink_url"]) ?></a>
							</td>
							<td class="algL">
								<?php if ($link["entry_id"] !== NULL): ?>
									<a href="<?= BASE.AMP.'C=content_publish'.AMP.'M=entry_form'.AMP.'entry_id='.$link["entry_id"]; ?>" target="_blank"><?= htmlspecialchars($link["entry"]) ?></a>
								<?php else: ?>
									<a href="<?= BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=low_variables'.AMP.'group_id=all' ?>" target="_blank">
									<?= lang('label_low_variables')?>
									</a>
								<?php endif; ?>
							</td>
							<td class="algL"><?= number_format($link['hits'],0,'.',',') ?></td>
							<td class="algC fix01 hyperlink_status">							
								
								<?php if (isset($link['hyperlink_http_status'])): ?>
									<?php if ( (int)$link['hyperlink_http_status'] < 200 || (int)$link['hyperlink_http_status'] >= 400 ): ?>
										<?php if ( (int)$link['hyperlink_http_status'] == -1 ): ?>
											<div class="stsBlt stsBltV2" title="<?= lang('hyperlink_http_connection_timeout') ?>"></div>
										<?php else: ?>
											<div class="stsBlt stsBltV2" title="<?= lang('hyperlink_http_status')?> <?= $link['hyperlink_http_status'] ?>"></div>
										<?php endif; ?>
									<?php else: ?>
									<div class="stsBlt stsBltV1" title="<?= lang('hyperlink_http_status')?> <?= $link['hyperlink_http_status'] ?>"></div>
									<?php endif; ?>
								<?php endif; ?>
								
								<?php /* if (isset($link['screenshot'])): ?>
								<div class="preview" title="Preview">
									<span class="icon"></span>
									<div class="tltpBx">
										<img src="<?= $link['screenshot'] ?>" width="200" height="200" />
										<span></span>
									</div><!-- end .tltpBx -->
								</div><!-- end .preview -->
								<?php endif; */ ?>				
											
							</td>
						</tr>					
						
	  					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div><!-- end .box -->
		
		<div clas="box">
			<?= $pagination ?>
		</div>
		
		<?php endif; ?>
		
	</div><!-- end .shCrtBx -->
</div><!-- end .modPg -->

<script type="text/javascript" charset="utf-8">
$('.btn_check_links').click(function()
{
	$('.hyperlink_status .stsBlt')
		.removeClass('stsBltV1')
		.removeClass('stsBltV2')	
		.addClass('stsLoad');

	$('.hyperlink_url').each(function(k,v)
	{
		$status = $(v).closest('tr').find('.hyperlink_status .stsBlt');
			
		$hyperlink_id = $(v).attr('rel_id');		
		
		$.ajax({
			url: '<?= $hyperlink_check_url_status ?>&hyperlink_id=' + $hyperlink_id + '&index=' + k,
			async: true,
			success: function(data) {
				
				$data = $.parseJSON(data)
						
				$status = $('.hyperlink_status .stsBlt').eq($data.index);
				
				$status.removeClass('stsLoad');		
								
				if (parseInt($data.status) >= 200 && parseInt($data.status) < 400)
				{
					$status.addClass('stsBltV1');
				}
				else
				{
					$status.addClass('stsBltV2');
				}
			}
		});	

	});
	return false;
});
</script>