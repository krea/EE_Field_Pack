<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Uploader Class
 *
 * @package   Uploader
 * @author    Michal Varga
 * @copyright Copyright (c) 2011 KREA SK s.r.o.
 */
class Uploader_ft extends EE_Fieldtype {

	var $info = array(
		'name' => 'Uploader',
		'version' => '1.1.0'
	);
	var $has_array_data = TRUE;
	var $cache = array(
		"includes" => array()
	);

	/**
	 * Theme URL
	 */
	private function _theme_url() {
		if (!isset($this->cache['theme_url'])) {
			$theme_folder_url = $this->EE->config->item('theme_folder_url');
			$theme_folder_url = ltrim($theme_folder_url, '/');
			$this->cache['theme_url'] = $theme_folder_url . 'third_party/uploader/';
		}

		return $this->cache['theme_url'];
	}

	/**
	 * Include Theme CSS
	 */
	private function _include_theme_css($file) {
		if (!in_array($file, $this->cache['includes'])) {
			$this->cache['includes'][] = $file;
			$this->EE->cp->add_to_head('<link rel="stylesheet" type="text/css" href="' . $this->_theme_url() . $file . '" />');
		}
	}

	/**
	 * Include Theme CSS
	 */
	private function _include_css($file) {
		if (!in_array($file, $this->cache['includes'])) {
			$this->cache['includes'][] = $file;
			$this->EE->cp->add_to_head('<link rel="stylesheet" type="text/css" href="' . $file . '" />');
		}
	}

	/**
	 * Include Theme JS
	 */
	private function _include_theme_js($file) {
		if (!in_array($file, $this->cache['includes'])) {
			$this->cache['includes'][] = $file;
			$this->EE->cp->add_to_foot('<script type="text/javascript" src="' . $this->_theme_url() . $file . '"></script>');
		}
	}

	private function _include_js($file) {
		if (!in_array($file, $this->cache['includes'])) {
			$this->cache['includes'][] = $file;
			$this->EE->cp->add_to_foot('<script type="text/javascript" src="' . $file . '"></script>');
		}
	}

	/**
	 * Insert JS
	 */
	private function _insert_js($js) {
		$this->EE->cp->add_to_foot('<script type="text/javascript">' . $js . '</script>');
	}

	/**
	 * Constructor
	 *
	 * @access	public
	 */
	function __construct() {
		parent::__construct();

		if (session_id() == '') {
			session_start();
		}

		$this->EE->load->model('file_upload_preferences_model');
	}

	// --------------------------------------------------------------------

	/**
	 * Save the correct value {fieldir_\d}filename.ext
	 *
	 * @access	public
	 */
	function save($data) {
		$items = array();

		if (isset($data['file'])) {
			foreach ($data['file'] as $k => $value) {
				$item = array(
					'file' => $data['file'][$k],
					'label_1' => $data['label_1'][$k],
					'label_2' => $data['label_2'][$k],
					'label_3' => $data['label_3'][$k],
					'label_4' => $data['label_4'][$k],
					'label_5' => $data['label_5'][$k],
				);

				$items[] = $item;
			}
		}

		if (count($items)) {
			return serialize($items);
		} else {
			return '';
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Validate the upload
	 *
	 * @access	public
	 */
	function validate($data) {
		global $uploader_STORAGE_SAVED;

		//cache v session je vyprazdnena po kazdom requeste

		if (!isset($uploader_STORAGE_SAVED[$_POST['entry_id']][$this->settings['field_id']])) {
			$_SESSION['Ft_uploader']['cache'][$_POST['entry_id']][$this->settings['field_id']] = array();
			$uploader_STORAGE_SAVED[$_POST['entry_id']][$this->settings['field_id']] = 1;
		}


		$_SESSION['Ft_uploader']['cache'][$_POST['entry_id']][$this->settings['field_id']] = $data;


		if ($this->settings['field_required'] == 'y' && !isset($_POST[$this->field_name])) {
			return $this->EE->lang->line('required');
		}

		return array('value' => $data);
	}

	// --------------------------------------------------------------------

	/**
	 * Show the publish field
	 *
	 * @access	public
	 */
	function display_field($data) {

		// --------------------------------------------------------------------
		// Rozlisujem dva pripady:
		// - uzivatel kliklol na PUBLIKOVAT PRISPEVOK
		// - uzivatel prisevok odoslal, ale nepresiel validaciou (zmaz cache)
		// --------------------------------------------------------------------

		if (!isset($_POST['title'])) {
			$_SESSION['Ft_uploader']['cache'] = array();
		}

		//nacitaj jazyk

		$this->EE->lang->loadfile('uploader');
		$this->EE->load->library('javascript');

		//$this->EE->cp->load_package_js('jquery.ui.draggable.min');	
		//$this->EE->cp->load_package_js('jquery.ui.sortable');	

		$this->_include_theme_js('scripts/jquery.ui.sortable.js');

		//jquery libraries

		$this->_include_js('//ajax.aspnetcdn.com/ajax/jquery.templates/beta1/jquery.tmpl.min.js');
		$this->_include_js('//ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js');
		$this->_include_theme_js('scripts/jquery.iframe-transport.js');
		$this->_include_theme_js('scripts/jquery.fileupload.js');
		$this->_include_theme_js('scripts/jquery.fileupload-ui.js');

		//css styles

		$this->_include_theme_css('styles/display_fields.css');
		$this->_include_theme_css('styles/jquery-ui-1.8.16.custom.css');
		$this->_include_theme_css('styles/jquery.fileupload-ui.css');

		if (empty($this->settings["allowed_directories"]) OR $this->settings["allowed_directories"] == '' OR $this->settings["allowed_directories"] == 'none' OR $this->settings["allowed_directories"] == 'all') {
			return lang('uploader_error_no_directories_allowed');
		}

		$prefs = $this->get_upload_prefs((int) $this->settings['allowed_directories']);

		if (!$prefs) {
			return lang('uploader_error_no_directories_allowed');
		}

		//send config to HTML

		$config = array(
			"field_id" => $this->field_id,
			"field_name" => $this->field_name,
			"theme_url" => $this->_theme_url(),
			"uploader_files_limit" => $this->settings['uploader_files_limit'] ? $this->settings['uploader_files_limit'] : 999,
			"uploader_addon_fields" => $this->settings['uploader_addon_fields'],
			"field_content_type" => $this->settings['field_content_type'],
			"allowed_directories" => $this->settings['allowed_directories'],
			"upload_url" => $prefs->url,
			"max_size" => $prefs->max_size ? $prefs->max_size : '99999999',
			"max_height" => $prefs->max_height ? $prefs->max_height : '99999999',
			"max_width" => $prefs->max_height ? $prefs->max_height : '99999999',
			"site_url" => $this->EE->config->config['site_url'],
			"action" => $this->_fetch_action(),
			"file_ext" => $this->settings['field_content_type'] == 'all' ? null : '*.jpg;*.jpeg;*.gif;*.png',
			'session_id' => session_id(),
			'xid' => $this->EE->functions->add_form_security_hash('{XID_HASH}'),
		);

		$vars = $config;

		foreach ($this->EE->lang->language as $k => $v) {
			$vars["uploader_label_add"] = ($this->settings['field_content_type'] == 'all') ? $this->EE->lang->line("uploader_label_add_file") : $this->EE->lang->line("uploader_label_add_image");
			$vars["uploader_label_start_upload"] = $this->EE->lang->line("uploader_label_start_upload");
			$vars["uploader_label_cancel_upload"] = $this->EE->lang->line("uploader_label_cancel_upload");
			$vars["uploader_label_delete_files"] = $this->EE->lang->line("uploader_label_delete_files");

			$vars["uploader_error_fileupload"] = $this->EE->lang->line("uploader_error_fileupload");

			$vars["uploader_error_fileupload_1"] = $this->EE->lang->line("uploader_error_fileupload_1");
			$vars["uploader_error_fileupload_2"] = $this->EE->lang->line("uploader_error_fileupload_2");
			$vars["uploader_error_fileupload_3"] = $this->EE->lang->line("uploader_error_fileupload_3");
			$vars["uploader_error_fileupload_4"] = $this->EE->lang->line("uploader_error_fileupload_4");
			$vars["uploader_error_fileupload_5"] = $this->EE->lang->line("uploader_error_fileupload_5");
			$vars["uploader_error_fileupload_6"] = $this->EE->lang->line("uploader_error_fileupload_6");
			$vars["uploader_error_fileupload_7"] = $this->EE->lang->line("uploader_error_fileupload_7");
			$vars["uploader_error_fileupload_8"] = $this->EE->lang->line("uploader_error_fileupload_8");
			$vars["uploader_error_fileupload_9"] = $this->EE->lang->line("uploader_error_fileupload_9");
			$vars["uploader_error_fileupload_10"] = $this->EE->lang->line("uploader_error_fileupload_10");
			$vars["uploader_error_fileupload_11"] = $this->EE->lang->line("uploader_error_fileupload_11");
			$vars["uploader_error_fileupload_12"] = $this->EE->lang->line("uploader_error_fileupload_12");
			$vars["uploader_error_fileupload_13"] = $this->EE->lang->line("uploader_error_fileupload_13");
			$vars["uploader_error_fileupload_14"] = $this->EE->lang->line("uploader_error_fileupload_14");
			$vars["uploader_error_fileupload_15"] = $this->EE->lang->line("uploader_error_fileupload_15");
			$vars["uploader_error_fileupload_16"] = $this->EE->lang->line("uploader_error_fileupload_16");
			$vars["uploader_error_fileupload_17"] = $this->EE->lang->line("uploader_error_fileupload_17");



			$vars["uploader_label_1"] = $this->settings['uploader_addon_field_1'];
			$vars["uploader_label_2"] = $this->settings['uploader_addon_field_2'];
			$vars["uploader_label_3"] = $this->settings['uploader_addon_field_3'];
			$vars["uploader_label_4"] = $this->settings['uploader_addon_field_4'];
			$vars["uploader_label_5"] = $this->settings['uploader_addon_field_5'];

			if ($this->settings['uploader_addon_fields'] < 5)
				$vars["uploader_label_5_style"] = 'display:none'; else
				$vars["uploader_label_5_style"] = '';
			if ($this->settings['uploader_addon_fields'] < 4)
				$vars["uploader_label_4_style"] = 'display:none'; else
				$vars["uploader_label_4_style"] = '';
			if ($this->settings['uploader_addon_fields'] < 3)
				$vars["uploader_label_3_style"] = 'display:none'; else
				$vars["uploader_label_3_style"] = '';
			if ($this->settings['uploader_addon_fields'] < 2)
				$vars["uploader_label_2_style"] = 'display:none'; else
				$vars["uploader_label_2_style"] = '';
			if ($this->settings['uploader_addon_fields'] < 1)
				$vars["uploader_label_1_style"] = 'display:none'; else
				$vars["uploader_label_1_style"] = '';
		}

		$r = '<script type="text/javascript">' . "\n";
		$r .= '//<![CDATA[' . "\n";
		$r .= 'if (typeof ft_uploader_upload_form_config == "undefined" || !(ft_uploader_upload_form_config instanceof Array)) {var ft_uploader_upload_form_config = new Array()}' . "\n";
		$r .= 'ft_uploader_upload_form_config["ft_uploader_upload_form_' . $this->field_id . '"] = ' . json_encode($config) . ';' . "\n";
		$r .= "\n" . '//]]>' . "\n";
		$r .= '</script>' . "\n";


		$r .= $this->view($this->EE->config->item('theme_folder_path') . 'third_party/uploader/views/display_fields.php', $vars);


		$this->_include_theme_js('scripts/fileupload.js');

		return $r;
	}

	// --------------------------------------------------------------------

	/**
	 * Prep the publish data
	 *
	 * @access	public
	 */
	function pre_process($data) {
		$file_info['path'] = '';

		if ($data) {
			$data = unserialize($data);
		} else {
			$data = array();
		}

		if (!$this->settings['allowed_directories']) {
			$data = array();
			return $data;
		}

		foreach ($data as $k => $v) {
			if (!isset($data[$k]['file'])) {
				unset($data[$k]);
				continue;
			}

			if (preg_match('/^{filedir_(\d+)}/', $data[$k]['file'], $matches)) {
				$path = substr($data[$k]['file'], 0, 10 + strlen($matches[1]));

				$prefs = $this->get_upload_prefs($matches[1]);

				if ($prefs) {
					$data[$k]['file_url'] = str_replace($matches[0], $prefs->url, $data[$k]['file']);
					$data[$k]['file_path'] = str_replace($matches[0], $prefs->server_path, $data[$k]['file']);
				}
			}

			$data[$k]['file_name'] = basename($data[$k]['file_path']);

			$data[$k]['label_name_1'] = $this->settings['uploader_addon_field_1'];
			$data[$k]['label_name_2'] = $this->settings['uploader_addon_field_2'];
			$data[$k]['label_name_3'] = $this->settings['uploader_addon_field_3'];
			$data[$k]['label_name_4'] = $this->settings['uploader_addon_field_4'];
			$data[$k]['label_name_5'] = $this->settings['uploader_addon_field_5'];

			$parts = explode('.', $data[$k]['file_name']);
			$data[$k]['extension'] = $parts[count($parts) - 1];

			unset($data[$k]['file']);
		}

		return $data;
	}

	// --------------------------------------------------------------------

	/**
	 * Replace frontend tag
	 *
	 * @access	public
	 */
	function replace_tag($file_info, $params = array(), $tagdata = FALSE) {
		//dopln parametre: WIDTH, HEIGHT, SIZE (podla potreby)
		//ak subor neexistuje - ignoruj ho

		foreach ($file_info as $k => $v) {
			if (!is_file($file_info[$k]['file_path'])) {
				unset($file_info[$k]);
				continue;
			}

			if (strpos($tagdata, '{size}') OR strpos($tagdata, '{file_size}')) {
				$file_info[$k]['size'] = @filesize($file_info[$k]['file_path']);
			}

			if (strpos($tagdata, '{width}') OR strpos($tagdata, '{height}')) {
				$size = @getimagesize($file_info[$k]['file_path']);

				$file_info[$k]['width'] = $size[0];
				$file_info[$k]['height'] = $size[1];
			}

			if (strpos($tagdata, '{file_size}')) {
				$file_info[$k]['file_size'] = $file_info[$k]['size'] . ' B';

				if ($file_info[$k]['size'] > 1000000000) {
					$file_info[$k]['file_size'] = round($file_info[$k]['size'] / 1000000000, 2) . ' GB';
				} elseif ($file_info[$k]['size'] > 1000000) {
					$file_info[$k]['file_size'] = round($file_info[$k]['size'] / 1000000, 2) . ' MB';
				} elseif ($file_info[$k]['size'] > 1000) {
					$file_info[$k]['file_size'] = round($file_info[$k]['size'] / 1000, 2) . ' kB';
				}
			}

			if (strpos($tagdata, '{file_ext}') OR strpos($tagdata, '{file_ext}')) {
				$file_info[$k]['file_ext'] = strtolower(end(explode(".", $file_info[$k]['file_name'])));
			}

			//fix labels

			$file_info[$k]['label_1'] = str_replace('"', '&quot;', strip_tags($file_info[$k]['label_1']));
			$file_info[$k]['label_2'] = str_replace('"', '&quot;', strip_tags($file_info[$k]['label_2']));
			$file_info[$k]['label_3'] = str_replace('"', '&quot;', strip_tags($file_info[$k]['label_3']));
			$file_info[$k]['label_4'] = str_replace('"', '&quot;', strip_tags($file_info[$k]['label_4']));
			$file_info[$k]['label_5'] = str_replace('"', '&quot;', strip_tags($file_info[$k]['label_5']));
		}

		//offset

		if ((int) @$params['offset']) {
			$count = 1;
			foreach ($file_info as $k => $v) {
				if ($count <= (int) @$params['offset']) {
					unset($file_info[$k]);
				}

				$count++;
			}
		}

		//limit

		if ((int) @$params['limit']) {
			$count = 1;
			foreach ($file_info as $k => $v) {
				if ($count > (int) @$params['limit']) {
					unset($file_info[$k]);
				}

				$count++;
			}
		}

		if ($file_info == '') {
			$file_info = array();
		}

		$tagdata = $this->_parse_variables($tagdata, $file_info);

		//trim

		if (@$params['trim']) {
			$tagdata = trim($tagdata, ' ' . $params['trim']);
		}

		return $tagdata;
	}

	// --------------------------------------------------------------------

	/**
	 * Display settings screen
	 *
	 * @access	public
	 */
	function display_settings($data) {
		//nacitaj jazyk

		$this->EE->lang->loadfile('uploader');

		//odstran smajlikov, sposob pisania textu atd.

		$this->EE->load->model('file_upload_preferences_model');

		//-------------------------------------------------------
		//	Typ suborov
		//-------------------------------------------------------

		$uploader_content_options = array('all' => lang('all'), 'image' => lang('type_image'));

		$this->EE->table->add_row(
				lang('uploader_settings_content_file', 'field_content_file'), form_dropdown('file_field_content_type', $uploader_content_options, !empty($data['field_content_type']) ? $data['field_content_type'] : NULL)
		);

		//------------------------------------------------------
		//	Moznosti nahravania obrazkov
		//------------------------------------------------------

		$directory_options['none'] = lang('-');

		$dirs = $this->EE->file_upload_preferences_model->get_upload_preferences(1);

		foreach ($dirs->result_array() as $dir) {
			$directory_options[$dir['id']] = $dir['name'];
		}

		$allowed_directories = (!isset($data['allowed_directories'])) ? 'none' : $data['allowed_directories'];

		$this->EE->table->add_row(
				lang('uploader_settings_allowed_dirs_file', 'allowed_dirs_file'), form_dropdown('file_allowed_directories', $directory_options, $allowed_directories, 'id="file_allowed_directories"')
		);

		//------------------------------------------------------
		//	Maximalny pocet nahratych obrazkov
		//------------------------------------------------------

		$this->EE->table->add_row(
				lang('uploader_settings_files_limit', 'uploader_files_limit'), form_input('uploader_files_limit', (int) @$data['uploader_files_limit'])
		);

		//------------------------------------------------------
		//	Doplnkove polia
		//------------------------------------------------------	

		$this->EE->table->add_row(
				lang('uploader_settings_addon_fields', 'allowed_dirs_file'), form_dropdown('uploader_addon_fields', array(0, 1, 2, 3, 4, 5), (int) @$data['uploader_addon_fields'], 'id="uploader_addon_fields"')
		);

		for ($i = 1; $i <= 5; $i++) {
			if (!isset($data['uploader_addon_field_' . $i]))
				$data['uploader_addon_field_' . $i] = lang('uploader_settings_addon_field_' . $i . '_example');

			$this->EE->table->add_row(
					lang('uploader_addon_field_number') . ' ' . $i, form_input('uploader_addon_field_' . $i, htmlspecialchars($data['uploader_addon_field_' . $i]), ' class="uploader_addon_field"')
			);
		}

		$this->_include_theme_js('scripts/settings.js');
	}
	
	

	/**
	 * Display settings in channel field.
	 * 
	 * @param array $data
	 * @return string
	 */
	public function display_settings_1(Array $data = array()) {
		return;
		dump_var($data, 1);
		$settings = '';
		foreach ($this->display_element_settings($data) as $row) {
			$settings .= '<tr><td width="30%">'.$row[0].'</td><td>'.$row[1].'</td></tr>';
		}
		
		return (!empty($settings) ? '<table>'.$settings.'</table>' : '');
	}

	// --------------------------------------------------------------------

	function save_settings($data) {
		return array(
			'field_content_type' => $this->EE->input->post('file_field_content_type'),
			'allowed_directories' => $this->EE->input->post('file_allowed_directories'),
			'uploader_files_limit' => $this->EE->input->post('uploader_files_limit'),
			'uploader_addon_field_1' => $this->EE->input->post('uploader_addon_field_1'),
			'uploader_addon_field_2' => $this->EE->input->post('uploader_addon_field_2'),
			'uploader_addon_field_3' => $this->EE->input->post('uploader_addon_field_3'),
			'uploader_addon_field_4' => $this->EE->input->post('uploader_addon_field_4'),
			'uploader_addon_field_5' => $this->EE->input->post('uploader_addon_field_5'),
			'uploader_addon_fields' => $this->EE->input->post('uploader_addon_fields'),
			'field_fmt' => 'none'
		);
	}

	function _parse_variables($_tagdata, $vars) {

		$output = ''; //vystup	
		$count = 0; //pocitadlo
		//prechadzam riadok za riadkom				
		foreach ($vars as $list) {
			$count++;

			//kazdy riadok sa aplikuje na tento kus kodu
			$tagdata = $_tagdata;

			/** ----------------------------------------
			  /**  parse {switch} variable
			  /** ---------------------------------------- */
			if (preg_match('#{(switch(.*?))}#s', $tagdata, $_match) == TRUE) {
				$sparam = $this->EE->functions->assign_parameters($_match[1]);

				$sw = '';

				if (isset($sparam['switch'])) {
					$sopt = @explode("|", $sparam['switch']);

					$sw = $sopt[($count + count($sopt) - 1) % count($sopt)];
				}

				$tagdata = $this->EE->TMPL->swap_var_single($_match[1], $sw, $tagdata);
			}

			/** ----------------------------------------
			  /**  OSTATNE ZNACKY
			  /** ---------------------------------------- */
			//kazdy riadok sa sklada z tagov
			foreach ($list as $tag => $value) {
				//tag moze byt bud pole ...
				if (is_array($value)) {
					preg_match_all('~{' . $tag . '}(.*?){/' . $tag . '}~s', $tagdata, $matches);


					//nahradzam zhody $matches[0] za preparsovne $matches[1]						
					foreach ($matches[0] as $i => $match) {
						//volaj rekurziu na kusok vypichnuteho textu	...		
						$pattern = $this->_parse_variables($matches[1][$i], $value);

						//... aplikuj ju do spracovavaneho kodu
						$tagdata = str_replace($matches[0][$i], $pattern, $tagdata);
					}
				}
				//... alebo aj single variable
				else {
					$tagdata = str_replace('{' . $tag . '}', $value, $tagdata);
				}
			}

			//count
			$tagdata = str_replace('{count}', $count, $tagdata);
			$tagdata = str_replace('{cnt}', $count, $tagdata);
			$tagdata = str_replace('{total_count}', count($vars), $tagdata);

			//podmienky
			$conds = array();

			foreach ($list as $tag => $value) {
				if (is_array($value)) {
					$conds[$tag] = count($value) ? 1 : 0;
				} else {
					$conds[$tag] = ($value) ? $value : 0;
				}
				$conds['count'] = $count;
				$conds['cnt'] = $count;
				$conds['total_count'] = count($vars);
				$conds['first'] = (int) $count === 1 ? 1 : 0;
				$conds['last'] = (int) $count === (int) count($vars) ? 1 : 0;
				$conds['odd'] = ((int) $count % 2 == 0) ? 1 : 0;
			}

			$output .= $this->EE->functions->prep_conditionals($tagdata, $conds);
		}

		//ocisti vystup od prazdnych ul-liek
		$output = preg_replace('~<ul([^>])*' . '>\s*</ul>~s', '', $output);

		return $output;
	}

	function view($path, $arr) {
		$template = file_get_contents($path);

		if (defined('BASE')) {
			$content = $this->_parse_variables($template, array($arr));

			$content = str_replace('[IMG_DESCRIPTION]', $this->EE->lang->line('IMG_DESCRIPTION'), $content);
			$content = str_replace('[FILE_DESCRIPTION]', $this->EE->lang->line('FILE_DESCRIPTION'), $content);
			$content = str_replace('[NAME]', $this->EE->lang->line('NAME'), $content);
			$content = str_replace('[WEBSITE_URL]', $this->EE->lang->line('WEBSITE_URL'), $content);

			return $content;
		} else {
			$mark = '[[' . md5(rand(0, 1000) . rand(0, 1000) . rand(0, 1000) . rand(0, 1000)) . ']]';
			$r = $this->_parse_variables($template, array($arr));

			if (!function_exists('ft_uploader_shuttdown')) {

				function ft_uploader_shuttdown($marker, $html) {
					$EE = & get_instance();

					$content = ob_get_contents();
					$content = str_replace($marker, $html, $content);

					$content = str_replace('[IMG_DESCRIPTION]', $EE->lang->line('IMG_DESCRIPTION'), $content);
					$content = str_replace('[FILE_DESCRIPTION]', $EE->lang->line('FILE_DESCRIPTION'), $content);
					$content = str_replace('[NAME]', $EE->lang->line('NAME'), $content);
					$content = str_replace('[WEBSITE_URL]', $EE->lang->line('WEBSITE_URL'), $content);

					//ob_flush();
					ob_end_clean();
					echo $content;
				}

			}

			ob_start();
			register_shutdown_function('ft_uploader_shuttdown', $mark, $r);

			return $mark;
		}
	}

	function get_upload_prefs($id) {
		if (isset($this->get_upload_prefs[$id])) {
			return $this->get_upload_prefs[$id];
		} else {
			$iQuery = $this->EE->db->query("SELECT * FROM exp_upload_prefs WHERE id='" . (int) $id . "'");
			if ($iQuery->num_rows) {
				$this->get_upload_prefs[$id] = $iQuery->row();
				return $this->get_upload_prefs[$id];
			} else {
				return false;
			}
		}
	}

	function _fetch_action_id() {
		//zisti id akcie pre nahravanie

		$aQuery = $this->EE->db->query("SELECT action_id FROM exp_actions WHERE class='Uploader_mcp' AND method='do_upload_file'");
		if ($aQuery->num_rows) {
			$action_id = $aQuery->row()->action_id;
		} else {
			$action_id = 0;
		}

		return $action_id;
	}

	function _fetch_action() {
		if (defined('BASE')) {
			return str_replace(AMP, '&', BASE) . '&C=addons_modules&M=show_module_cp&module=uploader&method=do_upload_file';
		} else {
			return $this->EE->functions->create_url('/') . '?ACT=' . $this->_fetch_action_id();
		}
	}
	
	/**
	 * Content elements compatibility
	 * 
	 */
	/**
	 * Display Element.
	 */
	public function display_element($data) {
		return $this->display_field($data);
	}

	/**
	 * Display element settings.
	 *
	 * @param array
	 * @param array
	 * @return void
	 */
	public function display_element_settings(Array $data = array()) {
		return $this->display_settings($data);
	}

	/**
	 * Save element.
	 *
	 * @param array
	 * @return void
	 */
	public function save_element($data) {
		return $this->save($data);
	}

	/**
	 * Replace template tag
	 *
	 * @param string
	 * @param array
	 * @param string   
	 * @return string Template
	 */
	public function replace_element_tag($data, $params, $tagdata) {
		return $this->replace_tag($data, $params, $tagdata);
	}

	/**
	 * Validate element.
	 *
	 * @param array
	 * @return string
	 */
	public function validate_element($data) {
		return $this->validate($data);
	}

}
