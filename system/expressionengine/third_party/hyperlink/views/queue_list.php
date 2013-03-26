<?php if (count($queue_list)): ?>
<div rel="hyperlink_queue_list">
	<ul>
	<?php foreach ($queue_list as $link): ?>
			<li><?= ellipsize($link["hyperlink_url"], 40) ?></li>
	<?php endforeach; ?>
	</ul>
	<br />
	<small style="color: #8F9A9C"><?= lang('label_last_reload') ?> <?= date('H:i:s') ?></small>
</div>
<script type="text/javascript">
//<![CDATA[

var reload_queue_list_stop = false;
$('#accessoryTabs a.hyperlink')
	.css("padding-left", "20px")
	.css("background-image", "url(/themes/third_party/hyperlink/images/load.gif)")
	.css("background-repeat", "no-repeat")
	.css("background-position", "0 2px");

function reload_queue_list()
{
	setTimeout(function(){

		data = $.get('<?= $refresh_url ?>', function(data){
			$data = $(data).html();
			$('div[rel=hyperlink_queue_list]').html($data);
			
			if ($(data).find('ul').size())
			{
				reload_queue_list();
			}
			else
			{
				$('#accessoryTabs a.hyperlink')
					.css("padding-left", "0")
					.css("background-image", "none");
			}
		});

	},3000);
}
reload_queue_list();
	

//]]>
</script>

<?php else: ?>
<div rel="hyperlink_queue_list">
	<?= lang('label_all_screenshots_processed') ?>
</div>
<?php endif; 