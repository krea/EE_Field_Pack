<h1>EE_addons</h1>
Modules and field types for ExpressionEngine by <a href="http://www.krea.com/ee">Krea.com</a>.

<h2>List of add-on's</h2>
- <a href="#embed-video---basic-usage">Embed video</a>
- <a href="#files---basic-usage">Files</a>
- Hyperlink
- Link
- Picture
- Quote

<h2 id="files">Files - basic usage</h2>
<pre>
{exp:channel:entries channel="test_channel_1" limit="10"}

	&lt;h1&gt;{title}&lt;/h1&gt;

	{!-- Simplified case --}
	{content_elements}

		{files}
			Attachments:
			{file}
				&lt;table&gt;
					&lt;tr&gt;&lt;td&gt;Caption&lt;/td&gt;&lt;td&gt;{caption}&lt;/td&gt;&lt;/tr&gt;
					&lt;tr&gt;&lt;td&gt;Dir&lt;/td&gt;&lt;td&gt;{dir}&lt;/td&gt;&lt;/tr&gt;
					&lt;tr&gt;&lt;td&gt;Size&lt;/td&gt;&lt;td&gt;{size}&lt;/td&gt;&lt;/tr&gt;
					&lt;tr&gt;&lt;td&gt;Server path&lt;/td&gt;&lt;td&gt;{server_path}&lt;/td&gt;&lt;/tr&gt;
					&lt;tr&gt;&lt;td&gt;URL&lt;/td&gt;&lt;td&gt;{url}&lt;/td&gt;&lt;/tr&gt;
					&lt;tr&gt;&lt;td&gt;Extension&lt;/td&gt;&lt;td&gt;{extension}&lt;/td&gt;&lt;/tr&gt;
					&lt;tr&gt;&lt;td&gt;Thumb&lt;/td&gt;&lt;td&gt;{thumb}&lt;/td&gt;&lt;/tr&gt;
				&lt;/table&gt;
			{/file}
		{/files}

	{/content_elements}

{/exp:channel:entries}
</pre>

<h2 id="embed_video">Embed video - basic usage</h2>

Content elements field type usage:
<pre>
{exp:channel:entries channel="your_channel_name" limit="10"}
	&lt;h2&gt;{title}&lt;/h2&gt;
	{content_elements}
		{embed_video}
			{output} or use YouTube video ID: {video_id}
		{/embed_video}
	{/content_elements}
{/exp:channel:entries}
</pre>

Standalone field type usage:
<code>
<pre>
{exp:channel:entries channel="your_channel_name" limit="10"}
	&lt;h2&gt;{title}&lt;/h2&gt;
	{your_name_for_embed_video}
		{output} or use YouTube video ID: {video_id}
	{/your_name_for_embed_video}
{/exp:channel:entries}
</pre>
</code>

<h3>Supported formats</h3>
- Youtube URL: www.youtube.com/embed/xyz123456
- Youtube URL: www.youtube.com/watch?v=xyz123456
- Flash file type: application/x-shockwave-flash
