<?php if (isset($message)) if ($message != ''):

	if (!isset($message_type)) $message_type = "success";
	
	if (strpos(strtolower($message), strtolower(lang('error'))) !== FALSE)
	{
		$message_type = "error";
	}
?>
	<script type="text/javascript">
		jQuery(function($){
			$.ee_notice('<?= addslashes($message);?>',{open: false, type:"<?= $message_type;?>"});
			//setTimeout(function(){ $.ee_notice.destroy(); }, 6000);
		});
	</script>
<?php endif;?>


<?php if (isset($alert)) if ($alert != ''):?>
	<script type="text/javascript">
		jQuery(function($){
			$.ee_notice('<?php echo addslashes($alert);?>',{open: true, type:"error"});
			//setTimeout(function(){ $.ee_notice.destroy(); }, 6000);
		});
	</script>
<?php endif;?>


<script type="text/javascript">
	$('.contents').children('.heading').remove();
</script>

