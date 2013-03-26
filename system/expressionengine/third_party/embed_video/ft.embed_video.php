<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Embeding videos fieldtype. Fieldtype Class - by KREA SK s.r.o.
 *
 * @package		Embed_Video
 * @author		KREA SK s.r.o.
 * @copyright	Copyright (c) 2012, KREA SK s.r.o.
 * @link		http://www.krea.com/docs/content-elements
 * @since		Version 1.2
 */
class Embed_Video_ft extends EE_Fieldtype {

	public $has_array_data = TRUE;
	public $info = array(
		'name' => 'Embed video',
		'version' => '1.2',
	);

	/**
	 * Constructor
	 *
	 * @return void
	 */
	function Embed_Video_ft() {
		parent::__construct();

		// Create addon_name from class name
		$this->addon_name = strtolower(substr(__CLASS__, 0, -3));

		// Fetch language
		$this->EE->lang->loadfile($this->addon_name);

		//Field type libraries
		$this->_load_libraries();
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		return $this->Embed_Video_ft();
	}

	/**
	 * Display field.
	 *
	 * @param array/string
	 * @param string [matrix, content_elements, or standard EE field]
	 * @return string
	 */
	public function display_field($data, $advanced = FALSE) {
		// Load helpers
		$this->EE->load->helper(array('form'));

		$theme_url = rtrim($this->EE->config->item('theme_folder_url'), '/') . '/third_party/' . $this->addon_name . '/';

		//diferent EE version has different trigger for upload files
		if (version_compare(APP_VER, '2.2.0', '>='))
			$this->EE->cp->add_to_head('<script type="text/javascript">var ' . $this->addon_name . '_add_file_trigger_version = 2;</script>');
		else
			$this->EE->cp->add_to_head('<script type="text/javascript">var ' . $this->addon_name . '_add_file_trigger_version = 1;</script>');

		// Add scripts to footer		
		$this->EE->cp->add_to_foot('<script type="text/javascript" src="' . $theme_url . 'embed_video.js"></script>');

		if ($advanced == 'content_elements') {
			$this->EE->cp->add_to_foot('<script type="text/javascript" src="' . $theme_url . 'publish_ce.js"></script>');
		}

		// Add styles to head			
		$this->EE->cp->add_to_head('<link rel="stylesheet" href="' . $theme_url . 'styles.css" type="text/css" media="screen" />');

		// Get data
		if (empty($data)) {
			$data = array(
				'embed_video_label' => '',
				'embed_video_url' => '',
				'embed_video_height' => '',
				'embed_video_width' => '',
				'files' => !empty($data['files']) ? $data['files'] : array(),
			);

			if ($advanced == 'content_elements') {
				//has been preparsed in "publish_ce.js" javascript, when element will be created
				$files_id = '__files_index__';
			} else if ($advanced == 'matrix') {
				//has been preparsed in "publish_matrix.js" javascript, when element will be created
				$files_id = '__files_index__';
			} else {
				//random value
				$files_id = md5(uniqid() . rand(1, 99999));
			}

			$data['files_id'] = $files_id;
		} elseif (!is_array($data)) {
			// Unserialize data
			$data = @unserialize(gzuncompress(base64_decode($data)));

			// Check files_id
			$data['files_id'] = !empty($data['files_id']) ? $data['files_id'] : md5(uniqid() . rand(1, 99999));
		} else {
			// If member provides SAVE & VALIDATION failed
			$data = $this->EE->{$this->addon_name . '_lib'}->recursive_html_entity_decode($data);
			$data['files'] = $data['files'][$data['files_id']];
		}

		$vars = array(
			'data' => $data,
			'advanced' => $advanced,
			'prefix' => ($advanced == 'matrix' ? $this->cell_name : $this->field_name),
			'settings' => $this->settings,
			'files_id' => !empty($data['files_id']) ? $data['files_id'] : 0,
			'thumb' => !empty($data['thumb']) ? $data['thumb'] : $theme_url . 'images/default.png',
			'files_limit' => 1
		);

		return $this->EE->load->view($this->_get_view_path('embed_video_back'), $vars, TRUE);
	}

	/**
	 * Validate data.
	 * 
	 * @param array/string $data
	 * @return boolean
	 */
	public function validate($data) {

		$errors = array();

		// Data must exist
		if (!isset($data)) {
			$errors[] = $this->EE->lang->line('Embed video is in wrong format');
		}

		// Prepare data
		$data['embed_video_width'] = !empty($data['embed_video_width']) ? str_replace(',', '.', $data['embed_video_width']) : '';
		$data['embed_video_height'] = !empty($data['embed_video_height']) ? str_replace(',', '.', $data['embed_video_height']) : '';

		if (!empty($this->settings['field_required']) and $this->settings['field_required'] == 'y') {

			// Label validation
			if (empty($data['embed_video_label'])) {
				$errors[] = $this->EE->lang->line('Embed video label must not be empty');
			}

			// URL validation
			if (empty($data['embed_video_url'])) {
				$errors[] = $this->EE->lang->line('Embed video URL must not be empty');
			}

			//URL validation
			$url_validation_result = $this->EE->{$this->addon_name . '_lib'}->is_valid_link($data['embed_video_url']);

			if ($url_validation_result != 1) {
				$errors[] = $this->EE->lang->line('Embed video URL is not valid');

				if ($url_validation_result == -1) {
					$parsed_url = parse_url($data['embed_video_url']);

					if (empty($parsed_url['host']))
						$parsed_url['host'] = NULL;

					$errors[] = $this->EE->lang->line('Video sevice not supported') . ' (' . $parsed_url['host'] . ')';
				}
			}
		}

		// Width validation
		if (!empty($data['embed_video_width']) and (
				(int) $data['embed_video_width'] < 0
				or (int) $data['embed_video_width'] > 2000
				or !is_numeric($data['embed_video_width']))
		) {
			$errors[] = $this->EE->lang->line('Embed video has wrong width');
		}

		// Height validation
		if (!empty($data['embed_video_height']) and (
				(int) $data['embed_video_height'] < 0
				or (int) $data['embed_video_height'] > 2000
				or !is_numeric($data['embed_video_height']))
		) {
			$errors[] = $this->EE->lang->line('Embed video has wrong height');
		}

		// If errors is not empty. create and return error message
		if (!empty($errors)) {
			$error_message = '';

			foreach ($errors as $error) {
				$error_message .= '<div>' . $error . '</div>';
			}

			return $error_message;
		}

		return TRUE;
	}

	/**
	 * Save process.
	 *
	 * @param mixed
	 * @return string - serialized string
	 */
	public function save($data) {

		// Prepare files
		$data['files'] = $data['files'][$data['files_id']];

		// Return serialized and compressed data
		return base64_encode(gzcompress(serialize($data)));
	}

	/**
	 * Replace tag.
	 * 
	 * @param array/string Saved data
	 * @param array Params 
	 * @param string Tagdata 
	 * @return string Template
	 */
	public function replace_tag($data, $params, $tagdata) {
		$data = @unserialize(gzuncompress(base64_decode($data)));

		if (!is_array($data)) {
			$data = array(
				'embed_video_label' => '',
				'embed_video_url' => '',
				'embed_video_height' => '',
				'embed_video_width' => '',
			);
		}

		if (empty($data['embed_video_height']) or (int) $data['embed_video_height'] == 0)
			$data['embed_video_height'] = $this->settings['embed_video_default_height'];

		if (empty($data['embed_video_width']) or (int) $data['embed_video_width'] == 0)
			$data['embed_video_width'] = $this->settings['embed_video_default_width'];

		$vars['embed_video'] = $data;
		$vars['link'] = $this->EE->{$this->addon_name . '_lib'}->get_embed_url($data['embed_video_url']);
		$vars['files'] = $this->EE->{$this->addon_name . '_lib'}->get_upload_files($data);

		$output = $this->EE->load->view($this->_get_view_path('embed_video_front'), $vars, TRUE);

		$result = array();
		$result['output'] = $output;

		if (!empty($this->element_name))
			$result['element_name'] = $this->element_name;

		return $this->EE->TMPL->parse_variables($tagdata, array($result));
	}

	/**
	 * Display settings in channel field.
	 * 
	 * @param array $data
	 * @return string
	 */
	public function display_settings(Array $data = array()) {
		
		$settings = '';
		foreach ($this->display_element_settings($data) as $row) {
			$settings .= '<tr><td width="30%">'.$row[0].'</td><td>'.$row[1].'</td></tr>';
		}
		
		return (!empty($settings) ? '<table>'.$settings.'</table>' : '');
	}

	/**
	 * Save settings.
	 *
	 * @param array
	 * @return array
	 */
	public function save_settings(Array $data = array()) {
		return array(
			'embed_video_default_height' => (int) $this->EE->input->post('embed_video_default_height'),
			'embed_video_default_width' => (int) $this->EE->input->post('embed_video_default_width'),
			'embed_video_label_maxlength' => (int) $this->EE->input->post('embed_video_label_maxlength'),
			'embed_video_fixed_dimensions' => (bool) $this->EE->input->post('embed_video_fixed_dimensions'),
		);
	}

	/**
	 * Display Cell Settings.
	 *
	 * @param array
	 * @return array	
	 */
	public function display_cell_settings(Array $data = array()) {

		$params['classes']['embed_video_height'] = 'matrix-textarea';
		$params['classes']['embed_video_width'] = 'matrix-textarea';
		$params['classes']['embed_video_label_maxlength'] = 'matrix-textarea';
		$params['classes']['embed_video_fixed_dimensions'] = 'matrix-textarea';

		return $this->display_element_settings($data, $params);
	}

	/**
	 * Display Cell.
	 *
	 * @param string
	 * @return HMTL	
	 */
	public function display_cell($data) {
		return $this->display_field($data, 'matrix');
	}

	/**
	 * Validate Cell.
	 *
	 * @param string
	 * @return boolen/string 
	 */
	public function validate_cell(Array $data = array()) {
		// Is this a required column?
		if ($this->settings['col_required'] == 'y' && !isset($data["embed_video_label"], $data["embed_video_url"])) {
			return lang('col_required');
		}

		return TRUE;
	}

	/**
	 * Save cell.
	 *
	 * @param array
	 * @return string - serialized string
	 */
	public function save_cell($data) {
		return $this->save($data);
	}

	/**
	 * Display Element.
	 */
	public function display_element($data) {
		return $this->display_field($data, 'content_elements');
	}

	/**
	 * Display element settings.
	 *
	 * @param array
	 * @param array
	 * @return void
	 */
	public function display_element_settings(Array $data = array(), Array $params = array()) {
		return array(
			array(
				lang('embed_video_label_maxlength', 'embed_video_label_maxlength'),
				form_input(
						array(
							'id' => 'embed_video_label_maxlength',
							'name' => 'embed_video_label_maxlength',
							'size' => 4,
							'value' => isset($data['embed_video_label_maxlength']) ? $data['embed_video_label_maxlength'] : '100',
							'class' => !empty($params['classes']['embed_video_label_maxlength']) ? $params['classes']['embed_video_label_maxlength'] : '',
						)
				)
			),
			array(
				lang('embed_video_default_width', 'embed_video_default_width'),
				form_input(
						array(
							'id' => 'embed_video_default_width',
							'name' => 'embed_video_default_width',
							'size' => 4,
							'value' => isset($data['embed_video_default_width']) ? $data['embed_video_default_width'] : '320',
							'class' => !empty($params['classes']['embed_video_default_width']) ? $params['classes']['embed_video_default_width'] : '',
						)
				)
			),
			array(
				lang('embed_video_default_height', 'embed_video_default_height'),
				form_input(
						array(
							'id' => 'embed_video_default_height',
							'name' => 'embed_video_default_height',
							'size' => 4,
							'value' => isset($data['embed_video_default_height']) ? $data['embed_video_default_height'] : '180',
							'class' => !empty($params['classes']['embed_video_default_height']) ? $params['classes']['embed_video_default_height'] : '',
						)
				)
			),
			array(
				lang('embed_video_fixed_dimensions', 'embed_video_fixed_dimensions'),
				form_dropdown(
						'embed_video_fixed_dimensions', array(FALSE => lang('No'), TRUE => lang('Yes')), !empty($data['embed_video_fixed_dimensions']) ? (bool) $data['embed_video_fixed_dimensions'] : FALSE, 'id="embed_video_fixed_dimensions"'
				)
			),
		);
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
	 * Preview element.
	 *
	 * @param array
	 * @return string
	 */
	public function preview_element($data) {
		$data = unserialize(gzuncompress(base64_decode($data)));

		if (empty($data['embed_video_url']))
			$data['embed_video_url'] = '#';

		if (empty($data['embed_video_label']))
			$data['embed_video_label'] = 'N/A';

		// Display width, if empty, set default width
		if (!empty($data['embed_video_width']))
			$width = $data['embed_video_width'];
		else
			$width = $this->settings['embed_video_default_width'];

		// Display height, if empty, set default height
		if (!empty($data['embed_video_height']))
			$height = $data['embed_video_height'];
		else
			$height = $this->settings['embed_video_default_height'];

		// Fixed dimensions
		if (empty($data['embed_video_fixed_dimensions']))
			$data['embed_video_fixed_dimensions'] = FALSE;
		else
			$data['embed_video_fixed_dimensions'] = (bool) $this->settings['embed_video_fixed_dimensions'];

		return lang('embed_video') . ': <a href="' . $data['embed_video_url'] . '" target="_blank">' . $data['embed_video_label'] . ' (' . $width . ' x ' . $height . ' px' . ($data['embed_video_fixed_dimensions'] ? ', ' . lang('EMBED_VIDEO_FIXED_DIMENSIONS') : '') . ')</a>';
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

	/**
	 * Load libraries.
	 */
	private function _load_libraries() {

		if (version_compare(APP_VER, '2.2.0', '>=')) {

			// Load package path for third party fieldtype loaders
			$this->EE->load->add_package_path(dirname(__FILE__));

			// Field type library
			$this->EE->load->library($this->addon_name . '_lib');
		} else {
			require_once dirname(__FILE__) . '/libraries/' . $this->addon_name . '_lib.php';
			$library_name = $this->addon_name . '_lib';
			$this->EE->{$this->addon_name . '_lib'} = new $library_name();
		}
	}

	/**
	 * Return view path.
	 */
	private function _get_view_path($name = '') {

		if (version_compare(APP_VER, '2.2.0', '>=')) {

			// Load package path for third party fieldtype loaders
			$this->EE->load->add_package_path(dirname(__FILE__));

			// Field type library
			return $name;
		} else {
			return '../../' . $this->addon_name . '/views/' . $name;
		}
	}
	
	/**
	 * ---------------------------
	 * Low variables compatibility
	 * ---------------------------
	 */
	
	/**
	 * Show fieldtype on PUBLISH page
	 *
	 * @return string
	 */
	public function display_var_field($data) {

		$this->settings["field_id"] = $this->var_id;
		return $this->display_field($data);
	}

	/**
	 * Display var settings
	 *
	 * @param array settings
	 * @return array
	 */
	public function display_var_settings($settings) {

		return array(
			array(
				lang('label_settings_link_label'),
				$this->display_settings($settings)
			)
		);
	}

	/**
	 * Method to catch the settings values before saving them to the database.
	 *
	 * @param array settings
	 * @return array
	 */
	public function save_var_settings($settings) {
		return $this->save_settings($settings);
	}

	/**
	 * Save
	 *
	 * @param array data
	 * @return string
	 */
	public function save_var_field($data) {
		return $this->save($data);
	}

	/**
	 * Replace Low Variables tag
	 *
	 * @param array data for content
	 * @param array fetch params 
	 * @param string html
	 * @return string
	 */
	public function display_var_tag($data, $params = '', $tagdata = '') {
		return $this->replace_tag($data, $params, $tagdata);
	}

}

if (!function_exists('dump_var')) {

	function dump_var($var, $exit = FALSE) {
		echo '<pre>';
		print_r($var);
		echo '</pre>';

		if ($exit)
			exit;
	}

}