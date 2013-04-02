<h1>EE_addons 1.2</h1>
Modules and field types for ExpressionEngine.

<h2>Version changelog</h2>
- <a href="http://gotolow.com/addons/low-variables">Low variables</a> compatibility
- <a href="http://www.devdemon.com/updater/">Updater compatibility</a>
- Fixed small bugs (PHP Notices...)

<h2>List of add-on's</h2>
- Content Elements
- <a href="#embed-video---basic-usage">Embed video</a>
- Files
- Helpdesk
- Hyperlink
- Link
- Promotions

<h2><a href="https://docs.google.com/a/krea.com/document/d/1tn0OCl73jdfGb60ouDJJONkyZ9QcYWbfoDeQrD0JFYA/edit#">TODO list</a> before new release</h2>

<h2 id="files">Files - basic usage</h2>

<code>
<pre>
	{exp:channel:entries channel="test_channel_1" limit="10"}

		<h1>{title}</h1>

		{!-- Simplified case --}
		{content_elements}

			{files}
				Attachments:
				{file}
					<table>
						<tr><td>Caption</td><td>{caption}</td></tr>
						<tr><td>Dir</td><td>{dir}</td></tr>
						<tr><td>Size</td><td>{size}</td></tr>
						<tr><td>Server path</td><td>{server_path}</td></tr>
						<tr><td>URL</td><td>{url}</td></tr>
						<tr><td>Extension</td><td>{extension}</td></tr>
						<tr><td>Thumb</td><td>{thumb}</td></tr>
					</table>
				{/file}
			{/files}

		{/content_elements}

	{/exp:channel:entries}
</pre>
</code>

<h2 id="embed_video">Embed video - basic usage</h2>

Content elements field type usage:
<code>
<pre>
{exp:channel:entries channel="your_channel_name" limit="10"}
	&lt;h2&gt;{title}&lt;/h2&gt;
	{content_elements}
		{embed_video}
			{output}
		{/embed_video}
	{/content_elements}
{/exp:channel:entries}
</pre>
</code>

Standalone field type usage:
<code>
<pre>
{exp:channel:entries channel="your_channel_name" limit="10"}
	&lt;h2&gt;{title}&lt;/h2&gt;
	{your_name_for_embed_video}
		{output}
	{/your_name_for_embed_video}
{/exp:channel:entries}
</pre>
</code>

<h3>Supported formats</h3>
- Youtube URL: www.youtube.com/embed/xyz123456
- Youtube URL: www.youtube.com/watch?v=xyz123456
- Flash file type: application/x-shockwave-flash