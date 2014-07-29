<?php if (isset($message)) if ($message != ''):

	if (!isset($messageType)) $messageType = "success";
	
	if (strpos(strtolower($message), strtolower(lang('error'))) !== FALSE)
	{
		$messageType = "error";
	}
?>
	<script type="text/javascript">
		jQuery(function($){
			$.ee_notice('<?= addslashes($message);?>',{open: false, type:"<?= $messageType;?>"});
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

