<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');


// If $link is not valid, do not render video.
if (strlen($link) > 3) {
	?>
	<div class="embed_video">

		<div>
			<iframe class="embed_video_iframe" width="<?php echo $embed_video['embed_video_width']; ?>" 
					height="<?php echo $embed_video['embed_video_height']; ?>" 
					src="<?php echo $link; ?>" 
					frameborder="0" allowfullscreen></iframe>
		</div>

		<?php
		if (!empty($embed_video['embed_video_label'])) {
			?>
			<div>
				<label class="embed_video_label">
					<?php echo $embed_video['embed_video_label']; ?>
				</label>
			</div>
			<?php
		}
		?>

	</div>
	<?php
} elseif (!empty($files)) {
	foreach ($files as $file) {

		if (function_exists('fxinfo_open')) {
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			if (finfo_file($finfo, $file['server_path']) != 'application/x-shockwave-flash')
				continue;
			finfo_close($finfo);
		} else {
			if (mime_content_type($file['server_path']) != 'application/x-shockwave-flash')
				continue;
		}
		?>

		<div class="embed_video_flash">
			<!--[if !IE]> -->
			<object type="application/x-shockwave-flash"
					data="<?php echo $file['url']; ?>" 
					width="<?php echo $embed_video['embed_video_width']; ?>" 
					height="<?php echo $embed_video['embed_video_height']; ?>">
				<!-- <![endif]-->

				<!--[if IE]>
				<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
				  codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0"
				  width="<?php echo $embed_video['embed_video_width']; ?>" height="<?php echo $embed_video['embed_video_height']; ?>">
				  <param name="movie" value="<?php echo $file['url']; ?>" />
				<!-->
				<param name="loop" value="true" />
				<param name="menu" value="false" />

				<p><?php echo $embed_video['embed_video_label']; ?></p>
			</object>
			<!-- <![endif]-->

			<?php
			if (!empty($embed_video['embed_video_label'])) {
				?>
				<div>
					<label class="embed_video_label">
						<?php echo $embed_video['embed_video_label']; ?>
					</label>
				</div>
				<?php
			}
			?>
		</div>
		<?php
	}
}