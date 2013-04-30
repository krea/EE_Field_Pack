<div id="ft_gallery_upload_form_{field_id}" class="ft_gallery_upload_form">
	<input type="hidden" name="FILEUPLOAD[{field_id}]" value="y"/>
	<input type="hidden" name="field_id_{field_id}[field]"/><!-- if not exist a all files is deleted, do not delete db record -->

      <div class="fileupload-buttonbar">

            <label class="fileinput-button">
                <span>{gallery_label_add}</span>
                <input type="file" name="ft_gallery_file" multiple />
            </label>
            <!-- <button type="submit" class="start">{gallery_label_start_upload}</button>
            <button type="reset" class="cancel">{gallery_label_cancel_upload}</button> -->
            <button type="button" class="delete">{gallery_label_delete_files}</button>

    </div>

    <div class="fileupload-content">
        <table class="files" width="100%"></table>
        <div class="fileupload-progressbar"></div>
    </div>
</div>
<script id="template-upload-ft_gallery_upload_form_{field_id}" type="text/x-jquery-tmpl">
    <tr class="template-upload{{if error}} ui-state-error{{/if}}">
        <td class="preview" width="100"></td>
        <td class="name" width="200">${name}</td>
        <td class="size" >${sizef}</td>
        {{if error}}
            <td class="error" colspan="2">{gallery_error_fileupload}:
                {{if error === 'maxFileSize'}}{gallery_error_fileupload_1}
                {{else error === 'minFileSize'}}{gallery_error_fileupload_2}
                {{else error === 'acceptFileTypes'}}{gallery_error_fileupload_3}
                {{else error === 'maxNumberOfFiles'}}{gallery_error_fileupload_4}
                {{else}}${error}
                {{/if}}
            </td>
        {{else}}
            <td class="progress"><div></div></td>
            <td class="start"><button>{gallery_label_start_upload}</button></td>
        {{/if}}
        <td class="cancel" width="40"><button>{gallery_label_cancel_upload}</button></td>
    </tr>
</script>
<script id="template-download-ft_gallery_upload_form_{field_id}" type="text/x-jquery-tmpl">
    <tr class="template-download{{if error}} ui-state-error{{/if}}">
        {{if error}}
            <td></td>
            <td class="name">${name}</td>
            <td class="size">${sizef}</td>
            <td class="error" colspan="2">{gallery_error_fileupload}:
                {{if error === 1}}{gallery_error_fileupload_5}
                {{else error === 2}}{gallery_error_fileupload_6}
                {{else error === 3}}{gallery_error_fileupload_7}
                {{else error === 4}}{gallery_error_fileupload_8}
                {{else error === 5}}{gallery_error_fileupload_9}
                {{else error === 6}}{gallery_error_fileupload_10}
                {{else error === 7}}{gallery_error_fileupload_11}
                {{else error === 'maxFileSize'}}{gallery_error_fileupload_12}
                {{else error === 'minFileSize'}}{gallery_error_fileupload_13}
                {{else error === 'acceptFileTypes'}}{gallery_error_fileupload_14}
                {{else error === 'maxNumberOfFiles'}}{gallery_error_fileupload_15}
                {{else error === 'uploadedBytes'}}{gallery_error_fileupload_16}
                {{else error === 'emptyResult'}}{gallery_error_fileupload_17}
                {{else}}${error}
                {{/if}}
            </td>
        {{else}}
            <td class="preview" width="100">
                {{if thumbnail_url}}
                    <a href="${url}" target="_blank"><img src="${thumbnail_url}"></a>
                {{/if}}
            </td>
            <td class="name" width="200">
                <a href="${url}"{{if thumbnail_url}} target="_blank"{{/if}}>${name}</a>
                <input type="hidden" name="field_id_{field_id}[file][]" MAX_FILE_SIZE="{max_size}" value="{filedir_{allowed_directories}}${name}" />
            </td>
            <td class="labels">
                <table class="ft_label" cellpadding="0" cellspacing="0">
                	<tr>
                		<td class="label_1" style="{gallery_label_1_style}">
                			{gallery_label_1}<br />
               				<div class="fld"><input type="text" class="gallery_label_1 write" name="field_id_{field_id}[label_1][]" value="" /></div>
               			</td>
                		<td class="label_2" style="{gallery_label_2_style}">
                			{gallery_label_2}<br />
               				<div class="fld"><input type="text" class="gallery_label_2 write" name="field_id_{field_id}[label_2][]" value="" /></div>
               			</td>               			
                		<td class="label_3" style="{gallery_label_3_style}">
                			{gallery_label_3}<br />
               				<div class="fld"><input type="text"  class="gallery_label_3 write" name="field_id_{field_id}[label_3][]" value="" /></div>
               			</td>
                		<td class="label_4" style="{gallery_label_4_style}">
                			{gallery_label_4}<br />
               				<div class="fld"><input type="text"  class="gallery_label_4 write" name="field_id_{field_id}[label_4][]" value="" /></div>
               			</td>
                		<td class="label_5" style="{gallery_label_5_style}">
                			{gallery_label_5}<br />
               				<div class="fld"><input type="text" class="gallery_label_5 write" name="field_id_{field_id}[label_5][]" value="" /></div>
               			</td>
               		</tr>	                		              		
               	</table>             		
            </td>
            <td class="size" width="100">${sizef}</td>
            <td colspan="1" width="0"></td>
        {{/if}}
        <td class="delete" width="40">
            <button data-type="${delete_type}" data-url="${delete_url}">Delete</button>
        </td>
    </tr>
</script>