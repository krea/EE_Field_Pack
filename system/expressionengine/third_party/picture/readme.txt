Picture v1.0.0
ExpressionEngine Fieldtype
======================================

Usage:
{picture}
	<p>
		This is your description:
		{description}
	</p>
	<p>
		This is your alignment:
		{alignment}
	</p>	
	<p>
		This is your image in size chosen during upload (on the publish page):
		<img src="{image}">
	</p>
	<p>
		This is your original image in required size:
		<img src="{original}">
	</p>
	<p>
		This is your thumbnail:
		<img src="{image:thumb}">
	</p>						
	<p>
		This is your URL:
		{url}
	</p>	
	<p>
		Image size: {size}<br />
		Extension: {extension}<br />
		Server path: {server_path}<br />
	</p>
{/picture}