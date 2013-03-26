<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Hyperlink_mcp Class - by KREA SK s.r.o.
 *
 * @package		Hyperlink
 * @author		KREA SK s.r.o.
 * @copyright	Copyright (c) 2012, KREA SK s.r.o.
 * @link		http://www.krea.com/docs/hyperlink
 * @since		Version 1.0.0
 */
class Hyperlink_mcp {

	/**
	 * Version
	 *  
	 * @access public
	 * @var string
	 */
	var $version = '1.0.0';
	var $cache = array
		(
		'includes' => array()
	);

	/*	 * ********************************** FUNCTIONS LIST ***************************************** */

	/**
	 * Constructor
	 *
	 * @return void
	 */
	function Hyperlink_mcp() {
		if (session_id() == "")
			session_start();

		$this->EE = &get_instance();

		// Create addon_name from class name
		$this->addon_name = strtolower(substr(__CLASS__, 0, -4));

		$this->EE->lang->loadfile($this->addon_name);
	}

	/**
	 * Constructor
	 *
	 * @return void
	 */
	function __construct() {
		return $this->Hyperlink_mcp();
	}

	/**
	 * Return CP theme URL
	 *
	 * @return string
	 */
	private function _theme_url() {
		if (!isset($this->cache['theme_url'])) {
			$theme_folder_url = defined('URL_THIRD_THEMES') ? URL_THIRD_THEMES : $this->EE->config->slash_item('theme_folder_url') . 'third_party/';
			$this->cache['theme_url'] = ltrim($theme_folder_url, '/') . $this->addon_name . '/';
		}
		return $this->cache['theme_url'];
	}

	/**
	 * Include CSS theme to CP header
	 *
	 * @param string CSS file naname
	 * @return string
	 */
	private function _include_theme_css($file) {
		if (!in_array($file, $this->cache['includes'])) {
			$this->cache['includes'][] = $file;
			$this->EE->cp->add_to_head('<link rel="stylesheet" type="text/css" href="' . $this->_theme_url() . 'styles/' . $file . '" />');
		}
	}

	/**
	 * Include CSS file directly to CP header
	 *
	 * @param string CSS file naname
	 * @return string
	 */
	private function _include_css($file) {
		if (!in_array($file, $this->cache['includes'])) {
			$this->cache['includes'][] = $file;
			$this->EE->cp->add_to_head('<link rel="stylesheet" type="text/css" href="' . $file . '" />');
		}
	}

	/**
	 * Include JS theme to CP header
	 *
	 * @param string JS file naname
	 * @return string
	 */
	private function _include_theme_js($file) {
		if (!in_array($file, $this->cache['includes'])) {
			$this->cache['includes'][] = $file;
			$this->EE->cp->add_to_foot('<script type="text/javascript" src="' . $this->_theme_url() . 'scripts/' . $file . '"></script>');
		}
	}

	/**
	 * Include JS theme to CP header
	 *
	 * @param string JS file naname
	 * @return string
	 */
	private function _include_js($file) {
		if (!in_array($file, $this->cache['includes'])) {
			$this->cache['includes'][] = $file;
			$this->EE->cp->add_to_foot('<script type="text/javascript" src="' . $file . '"></script>');
		}
	}

	/**
	 * Include JS stream directly to CP header
	 *
	 * @param string JS file naname
	 * @return string
	 */
	private function _insert_js($js) {
		if (!in_array($js, $this->cache['includes'])) {
			$this->cache['includes'][] = $js;
			$this->EE->cp->add_to_foot('<script type="text/javascript">' . $js . '</script>');
		}
	}

	/**
	 * Create VIEW
	 *  
	 * @access private
	 */
	private function _view($template, $vars, $flag, $title) {
		$this->EE->cp->set_variable('cp_page_title', $title);

		//theme

		$this->_include_theme_css('main.css');
		$this->_include_theme_js('main.js');

		//generate right Nav

		$rightNav = array();

		//messages

		if (isset($_SESSION["hyperlink"]["flash_message"])) {
			$vars['message'] = $_SESSION["helpdesk"]["flash_message"];
			unset($_SESSION["hyperlink"]["flash_message"]);
		}

		if (isset($_SESSION["hyperlink"]["flash_error"])) {
			$vars['alert'] = $_SESSION["helpdesk"]["flash_error"];
			unset($_SESSION["hyperlink"]["flash_error"]);
		}

		return $this->EE->load->view($template, $vars, $flag);
	}

	/**
	 * Module homepage
	 *  
	 * @access public
	 */
	function index() {
		$this->EE->load->library('hyperlink_lib');

		//results

		$results_options = array(
			'50' => '50 ' . lang('label_results_per_page'),
			'100' => '100 ' . lang('label_results_per_page'),
			'500' => '500 ' . lang('label_results_per_page'),
		);

		//statuses

		$status_options = array(
			'' => lang('label_all_links'),
			'valid' => lang('label_valid_links'),
			'invalid' => lang('label_invalid_links'),
		);

		//sort

		$sort_by_options = array(
			'status' => lang('label_sort_by_status'),
			'hyperlink' => lang('label_sort_by_hyperlink'),
			'entry' => lang('label_sort_by_entry'),
			'clicks' => lang('label_sort_by_clicks')
		);

		//---------------------------
		//default

		$filter = array(
			'results' => '50',
			'status' => '',
			'sort_by' => 'status',
			'keyword' => ''
		);

		//not reset

		if (@$_GET["filter"] != "reset") {
			//load
			if (isset($_SESSION[__class__]["filter"])) {
				$filter = $_SESSION[__class__]["filter"];
			}

			//if change
			if (isset($_POST["filter"])) {
				$filter = $_POST["filter"];
			}

			//store
			$_SESSION[__class__]["filter"] = $filter;
		}

		//----------------------------
		//get total results

		$this->EE->db->select('h.hyperlink_id')
				->from('exp_hyperlink h')
				->join('exp_channel_titles t', 't.entry_id = h.entry_id', 'left')
				->where('h.site_id', $this->EE->config->item('site_id'));

		if ($filter['status'] == 'valid') {
			$this->EE->db->where('hyperlink_http_status >= 200');
			$this->EE->db->where('hyperlink_http_status < 400');
		}
		if ($filter['status'] == 'invalid') {
			$this->EE->db->where('( hyperlink_http_status < 200 OR hyperlink_http_status >= 400 )');
		}
		if ($filter['keyword'] && $filter['keyword'] != lang('label_url_or_title')) {
			$this->EE->db->where('(( t.title LIKE "%' . addslashes($filter['keyword']) . '%" ) OR ( hyperlink_url LIKE "%' . addslashes($filter['keyword']) . '%" )  OR ( hyperlink_title LIKE "%' . addslashes($filter['keyword']) . '%" ))');
		}

		$total_results = count($this->EE->db->get()->result_array());

		//get links

		$this->EE->db->select('h.*, t.title as entry')
				->from('exp_hyperlink h')
				->join('exp_channel_titles t', 't.entry_id = h.entry_id', 'left')
				->where('h.site_id', $this->EE->config->item('site_id'));

		if ($filter['status'] == 'valid') {
			$this->EE->db->where('hyperlink_http_status >= 200');
			$this->EE->db->where('hyperlink_http_status < 400');
		}
		if ($filter['status'] == 'invalid') {
			$this->EE->db->where('( hyperlink_http_status < 200 OR hyperlink_http_status >= 400 )');
		}
		if ($filter['keyword'] && $filter['keyword'] != lang('label_url_or_title')) {
			$this->EE->db->where('(( t.title LIKE "%' . addslashes($filter['keyword']) . '%" ) OR ( hyperlink_url LIKE "%' . addslashes($filter['keyword']) . '%" )  OR ( hyperlink_title LIKE "%' . addslashes($filter['keyword']) . '%" ))');
		}


		if ($filter["sort_by"] == 'status') {
			$this->EE->db->ar_orderby[] = " CASE WHEN hyperlink_http_status >= 200 AND hyperlink_http_status < 400 THEN 1 ELSE 0 END ASC ";
		}
		if ($filter["sort_by"] == 'entry') {
			$this->EE->db->ar_orderby[] = " CASE WHEN entry IS null THEN '" . lang('label_low_variables') . "' ELSE entry END ASC ";
		}
		if ($filter["sort_by"] == 'clicks') {
			$this->EE->db->ar_orderby[] = " hits DESC ";
		}
		if ($filter["sort_by"] == 'hyperlink') {
			$this->EE->db->ar_orderby[] = " hyperlink_url ASC ";
		}

		$this->EE->db->limit((int) $filter["results"]);
		$this->EE->db->offset((int) @$_GET['page']);

		$links = $this->EE->db->get()->result_array();

		//paginate

		$pagination_config['base_url'] = BASE . AMP . 'C=addons_modules' . AMP . 'M=show_module_cp' . AMP . 'module=hyperlink';
		$pagination_config['total_rows'] = $total_results;
		$pagination_config['per_page'] = $filter["results"];
		$pagination_config['page_query_string'] = TRUE;
		$pagination_config['query_string_segment'] = 'page';

		$this->EE->load->library('pagination');
		$this->EE->pagination->initialize($pagination_config);

		//act_id

		$act_id = (int) @$this->EE->db
						->select('action_id')
						->from('exp_actions')
						->where('class', 'Hyperlink')
						->where('method', 'hyperlink_check_url_status')
						->get()
						->row()
				->action_id;

		$hyperlink_check_url_status = $this->EE->functions->create_url('/') . '?ACT=' . $act_id;


		$vars = array(
			'results_options' => $results_options,
			'status_options' => $status_options,
			'sort_by_options' => $sort_by_options,
			'filter' => $filter,
			'links' => $links,
			'pagination' => $this->EE->pagination->create_links(),
			'hyperlink_check_url_status' => $hyperlink_check_url_status
		);

		return $this->_view('hyperlinks', $vars, TRUE, lang('title_hyperlinks'));
	}

	/**
	 * Merge settings recursively
	 *
	 * @param array 
	 * @param array  
	 * @return rarray 
	 * @access public
	 */
	private function _settings_merge($default_settings, $settings) {
		foreach ($default_settings as $k => $v) {
			if (is_array($default_settings[$k])) {
				if (!isset($settings[$k]))
					$settings[$k] = array();
				$settings[$k] = $this->_settings_merge($default_settings[$k], $settings[$k]);
			}
			else {
				if (!isset($settings[$k])) {
					$settings[$k] = $default_settings[$k];
				}
			}
		}
		return $settings;
	}

	/**
	 * Module settings
	 *  
	 * @access public
	 */
	function settings() {
		$message = '';
		$message_type = '';

		$this->EE->load->library('hyperlink_lib');

		//save

		if (isset($_POST['button_submit'])) {
			$validation_errors = array();

			//fetch settings

			$settings = array();
			$settings["screenshot_service"] = $this->EE->input->post('screenshot_service');
			$settings["screenshot_services"] = $this->EE->input->post('screenshot_services');
			$settings["screenshot_dir"] = $this->EE->input->post('screenshot_dir');

			$settings["publish_entry_with_invalid_links"] = $this->EE->input->post('publish_entry_with_invalid_links');

			$settings["schedule_validation_of_links"]['schedule'] = isset($_POST['schedule_validation_of_links']['schedule']);
			$settings["schedule_validation_of_links"]['change_status'] = $_POST['schedule_validation_of_links']['change_status'];

			$settings = $this->_settings_merge($this->EE->hyperlink_lib->default_settings, $settings);

			//validate

			$this->EE->lang->load('form_validation');

			if ($settings['screenshot_service'] == 'GrabzIt') {
				if (!$settings["screenshot_services"]["GrabzIt"]["api_key"]) {
					$validation_errors["screenshot_services"]["GrabzIt"]["api_key"] = sprintf(lang("required"), 'API key');
				}

				if (!$settings["screenshot_services"]["GrabzIt"]["api_secret"]) {
					$validation_errors["screenshot_services"]["GrabzIt"]["api_secret"] = sprintf(lang("required"), 'API secret');
				}

				$max_height = $this->EE->hyperlink_lib->grabzit_packages[$settings["screenshot_services"]["GrabzIt"]["service_package"]]['max_height'];
				$max_width = $this->EE->hyperlink_lib->grabzit_packages[$settings["screenshot_services"]["GrabzIt"]["service_package"]]['max_width'];

				if ($max_width < (int) $settings["screenshot_services"]["GrabzIt"]["image_width"]) {
					$validation_errors["screenshot_services"]["GrabzIt"]["image_width"] = sprintf(lang("msg_error_image_width_too_big"), $max_width);
				}

				if ($max_height < (int) $settings["screenshot_services"]["GrabzIt"]["image_height"]) {
					$validation_errors["screenshot_services"]["GrabzIt"]["image_height"] = sprintf(lang("msg_error_image_height_too_big"), $max_height);
				}

				if ((int) $settings["screenshot_services"]["GrabzIt"]["image_width"] < 20) {
					$validation_errors["screenshot_services"]["GrabzIt"]["image_width"] = sprintf(lang("msg_error_image_width_too_small"), 20);
				}

				if ((int) $settings["screenshot_services"]["GrabzIt"]["image_height"] < 20) {
					$validation_errors["screenshot_services"]["GrabzIt"]["image_height"] = sprintf(lang("msg_error_image_height_too_small"), 20);
				}

				if (!$settings["screenshot_dir"]) {
					$validation_errors["screenshot_dir"] = sprintf(lang("required"), lang("label_download_directory"));
				}

				if ($settings["screenshot_services"]["GrabzIt"]["api_key"] && $settings["screenshot_services"]["GrabzIt"]["api_secret"]) {
					include_once("libraries/grabzit/GrabzItClient.class.php");

					try {
						$grabzIt = new GrabzItClient($settings["screenshot_services"]["GrabzIt"]["api_key"], $settings["screenshot_services"]["GrabzIt"]["api_secret"]);
						$id = $grabzIt->TakePicture("http://www.google.com", null, null, 1024, 768, $max_width, $max_height);
					} catch (Exception $e) {
						if (strpos($e->getMessage(), 'Image height too large') !== FALSE) {
							$validation_errors["screenshot_services"]["GrabzIt"]["service_package"] = lang("msg_error_invalid_service_package");
						} elseif (strpos($e->getMessage(), 'Invalid signature detected') !== FALSE) {
							$validation_errors["screenshot_services"]["GrabzIt"]["api_secret"] = $e->getMessage();
						} else {
							$validation_errors["screenshot_services"]["GrabzIt"]["api_key"] = $e->getMessage();
						}
					}
				}
			}

			if (count($validation_errors)) {
				$message = lang('msg_error_settings_validaton_failed');
				$message_type = 'error';
			} else {
				$data = array(
					'settings' => base64_encode(serialize($settings))
				);
				$this->EE->db->update('exp_hyperlink_settings', $data, 'site_id = ' . $this->EE->config->item('site_id'));

				$message = lang('msg_settings_saved');
				$message_type = 'success';
			}
		}

		$sites = $this->EE->db->select("*")->from('exp_sites')->order_by('site_id', 'ASC')->get()->result_array();

		//** -----------------------------
		//**	directory options
		//** -----------------------------

		$directory_options = array('' => lang('label_choose_directory'));

		foreach ($sites as $site) {
			$site_dirs = array();
			foreach ($this->EE->hyperlink_lib->upload_preferences() as $preference) {
				if ($preference["site_id"] == $site["site_id"]) {
					$site_dirs[$preference["id"]] = $preference["name"];
				}
			}
			$directory_options[$site["site_label"]] = $site_dirs;
		}

		//** -----------------------------
		//**	grabzit_packages
		//** -----------------------------		

		$grabzit_packages = array();
		foreach ($this->EE->hyperlink_lib->grabzit_packages as $package_id => $package) {
			$grabzit_packages[$package_id] = $package["name"];
		}

		//** -----------------------------
		//**	statuses
		//** -----------------------------		

		$statuses = array('' => lang('label_status_do_not_change'));
		foreach ($this->EE->db->select("status")->from("exp_statuses")->get()->result() as $status) {
			$statuses[$status->status] = $status->status;
		}

		//** -----------------------------
		//**	get ACT
		//** -----------------------------	

		$act_id = (int) @$this->EE->db
						->select('action_id')
						->from('exp_actions')
						->where('class', 'Hyperlink')
						->where('method', 'hyperlink_schedule_validation_of_links')
						->get()
						->row()
				->action_id;

		$hyperlink_schedule_validation_url = $this->EE->functions->create_url('/') . '?ACT=' . $act_id;

		$vars = array(
			"settings" => isset($_POST['button_submit']) ? $settings : $this->EE->hyperlink_lib->settings,
			"directory_options" => $directory_options,
			"grabzit_packages" => $grabzit_packages,
			"statuses" => $statuses,
			"hyperlink_schedule_validation_url" => $hyperlink_schedule_validation_url,
			"message" => $message,
			"message_type" => $message_type,
			"validation_errors" => isset($validation_errors) ? $validation_errors : null,
		);

		return $this->_view('settings', $vars, TRUE, lang('title_settings'));
	}

}

//END Class
