<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Content_elements_ft fieldtype Class - by KREA SK s.r.o.
 *
 * @package		Content_elements
 * @author		KREA SK s.r.o.
 * @copyright	Copyright (c) 2012, KREA SK s.r.o.
 * @link		http://www.krea.com/docs/content-elements
 * @since		Version 1.0
 */
class Link_ft extends EE_Fieldtype {

	var $info = array(
		'name' => 'Link',
		'version' => '1.0'
	);
	// Parser Flag (preparse pairs?)

	var $has_array_data = TRUE;

	/**
	 * Constructor
	 *
	 * @return void
	 */
	function Link_ft() {
		parent::__construct();

		// Create addon_name from class name
		$this->addon_name = strtolower(substr(__CLASS__, 0, -3));

		// Fetch language
		$this->EE->lang->loadfile('link');
	}

	/**
	 * Constructor
	 *
	 * @return void
	 */
	function __construct() {
		return $this->Link_ft();
	}

	/**
	 * Display field
	 *
	 * @param array/string
	 * @param string [matrix, content_elements, or standard EE field]
	 * @return void
	 */
	function display_field($data, $advanced = FALSE) {
		$this->EE->load->helper(array('form'));

		//get data 

		if (!$data) {
			$data = array(
				'label' => '',
				'url' => '',
			);
		} else {
			//if data can not be unserialize, just resave it!

			if (!is_array(@unserialize(html_entity_decode($data)))) {
				$data = $this->save($data);
			}
			$data = unserialize(html_entity_decode($data));
		}

		return form_input(
						array(
							'name' => (($advanced == 'matrix') ? $this->cell_name : $this->field_name) . '[label]',
							'value' => $data['label'],
							'style' => 'width: 33%; margin-right:1%;',
							'maxlength' => $this->settings['link_label_maxlength'],
							'placeholder' => lang('link_label'),
						)
				) . form_input(
						array(
							'name' => (($advanced == 'matrix') ? $this->cell_name : $this->field_name) . '[url]',
							'value' => $data['url'],
							'style' => 'width: 66%',
							'maxlength' => $this->settings['link_url_maxlength'],
							'placeholder' => lang('link_url'),
						)
		);
	}

	/**
	 * Validate data
	 *
	 * @param array/string
	 * @return void
	 */
	function validate($data) {
		//data must exist

		if ($data == '') {
			return TRUE;
		}

		if ($this->settings['field_required'] == 'y' && ($data["label"] == "") && ($data["label"] == "")) {
			return $this->EE->lang->line('required');
		}

		//success

		return TRUE;
	}

	/**
	 * Save process
	 *
	 * @param array
	 * @return string
	 */
	function save($data) {
		$save_data = array(
			"label" => $data['label'],
			"url" => $data['url'],
		);

		return serialize($save_data);
	}

	/**
	 * Replace tag
	 * 
	 * @param array/string Saved data
	 * @param array Params 
	 * @param string Tagdata 
	 * @return string Template
	 */
	function replace_tag($data, $params = array(), $tagdata) {
		$data = @unserialize($data);

		if (!is_array($data)) {
			$data = array(
				'label' => '',
				'url' => '',
			);
		}

		$tagdata = $this->EE->functions->prep_conditionals($tagdata, $data);
		$tagdata = $this->EE->TMPL->parse_variables($tagdata, array($data));

		return $tagdata;
	}

	/**
	 * Display settings
	 *
	 * @param array
	 * @return void
	 */
	function display_settings($data) {
		$this->EE->table->add_row(
				lang('link_label_maxlength', 'link_label_maxlength'), form_input(
						array(
							'id' => 'link_label_maxlength',
							'name' => 'link_label_maxlength',
							'size' => 4,
							'value' => isset($data['link_label_maxlength']) ? $data['link_label_maxlength'] : '50',
						)
				)
		);

		$this->EE->table->add_row(
				lang('link_url_maxlength', 'link_url_maxlength'), form_input(
						array(
							'id' => 'link_url_maxlength',
							'name' => 'link_url_maxlength',
							'size' => 4,
							'value' => isset($data['link_url_maxlength']) ? $data['link_url_maxlength'] : '100',
						)
				)
		);
	}

	/**
	 * Save settings
	 *
	 * @param array
	 * @return void
	 */
	function save_settings($data) {
		return array(
			'link_label_maxlength' => (int) $this->EE->input->post('link_label_maxlength'),
			'link_url_maxlength' => (int) $this->EE->input->post('link_url_maxlength'),
		);
	}

#####################################################
#----------------------------------------------------
#	MATRIX FUNCTIONS
#----------------------------------------------------
#####################################################	

	/**
	 * Display Cell Settings
	 *
	 * @param array
	 * @return array	
	 */
	function display_cell_settings($data) {
		return array(
			array(
				lang('link_label_maxlength', 'link_label_maxlength'),
				form_input(
						array(
							'id' => 'link_label_maxlength',
							'name' => 'link_label_maxlength',
							'size' => 4,
							'value' => isset($data['link_label_maxlength']) ? $data['link_label_maxlength'] : '50',
							'class' => 'matrix-textarea'
						)
				)
			),
			array(
				lang('link_url_maxlength', 'link_url_maxlength'),
				form_input(
						array(
							'id' => 'link_url_maxlength',
							'name' => 'link_url_maxlength',
							'size' => 4,
							'value' => isset($data['link_url_maxlength']) ? $data['link_url_maxlength'] : '100',
							'class' => 'matrix-textarea'
						)
				)
			),
		);
	}

	/**
	 * Display Cell
	 *
	 * @param string
	 * @return HMTL	
	 */
	function display_cell($data) {
		return $this->display_field($data, 'matrix');
	}

	/**
	 * Validate Cell
	 *
	 * @param string
	 * @return boolen/string 
	 */
	function validate_cell($data) {
		// is this a required column?
		if ($this->settings['col_required'] == 'y' && ($data["label"] == "") && ($data["label"] == "")) {
			return lang('col_required');
		}

		return TRUE;
	}

	/**
	 * Save cell
	 *
	 * @param array
	 * @return void
	 */
	function save_cell($data) {
		return $this->save($data);
	}

#####################################################
#----------------------------------------------------
#	CONTENT ELEMENT FUNCTIONS
#----------------------------------------------------
#####################################################	

	/**
	 * Display Element
	 */
	function display_element($data) {
		return $this->display_field($data, 'content_elements');
	}

	/**
	 * Display element settings
	 *
	 * @param array
	 * @return void
	 */
	function display_element_settings($data) {
		return array(
			array(
				lang('link_label_maxlength', 'link_label_maxlength'),
				form_input(
						array(
							'id' => 'link_label_maxlength',
							'name' => 'link_label_maxlength',
							'size' => 4,
							'value' => isset($data['link_label_maxlength']) ? $data['link_label_maxlength'] : '50',
						)
				)
			),
			array(
				lang('link_url_maxlength', 'link_url_maxlength'),
				form_input(
						array(
							'id' => 'link_url_maxlength',
							'name' => 'link_url_maxlength',
							'size' => 4,
							'value' => isset($data['link_url_maxlength']) ? $data['link_url_maxlength'] : '100',
						)
				)
			),
		);
	}

	/**
	 * Save element
	 *
	 * @param array
	 * @return void
	 */
	function save_element($data) {
		return $this->save($data);
	}

	/**
	 * Preview element
	 *
	 * @param array
	 * @return void
	 */
	function preview_element($data) {
		$data = unserialize($data);
		return '<a href="' . $data['url'] . '">' . $data['label'] . '</a>';
	}

	/**
	 * Replace template tag
	 *
	 * @param string
	 * @param array
	 * @param string   
	 * @return void
	 */
	function replace_element_tag($data, $params = array(), $tagdata) {
		$data = @unserialize($data);

		if (!is_array($data)) {
			$data = array(
				'label' => '',
				'url' => '',
			);
		}

		$data["element_name"] = $this->element_name;

		$tagdata = $this->EE->functions->prep_conditionals($tagdata, $data);
		$tagdata = $this->EE->TMPL->parse_variables($tagdata, array($data));

		return $tagdata;
	}

	/**
	 * Validate element
	 *
	 * @param array
	 * @return boolean/string
	 */
	function validate_element($data) {
		if ($data["label"] == "" && $data["label"] == "") {
			return sprintf(lang('required'), $this->element_name);
		}

		return TRUE;
	}

	/**
	 * Tool: get theme url
	 *  
	 * @access public
	 */
	public function _theme_url() {
		$this->cache['theme_url'] = $this->define_theme_url($this->addon_name);
		return $this->cache['theme_url'];
	}

	public function define_theme_url($addon_name = 'content_elements') {

		if (defined('LINK_THEME_URL'))
			return LINK_THEME_URL;

		if (defined('URL_THIRD_THEMES') === TRUE) {
			$theme_url = URL_THIRD_THEMES;
		} else {
			$theme_url = $this->EE->config->item('theme_folder_url') . 'third_party/';
		}

		// Are we working on SSL?
		if (isset($_SERVER['HTTP_REFERER']) == TRUE AND strpos($_SERVER['HTTP_REFERER'], 'https://') !== FALSE) {
			$theme_url = str_replace('http://', 'https://', $theme_url);
		} elseif (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off') {
			$theme_url = str_replace('http://', 'https://', $theme_url);
		}

		$theme_url = str_replace(array('https://', 'http://'), '//', $theme_url);

		define('LINK_THEME_URL', $theme_url . $addon_name . '/');

		return LINK_THEME_URL;
	}

}

// END Link_Ft class

/* End of file ft.link.php */
/* Location: ./system/expressionengine/third_party/files/ft.link.php */