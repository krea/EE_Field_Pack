<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Hyperlink_ft fieldtype Class - by KREA SK s.r.o.
 *
 * @package		Hyperlink
 * @author		KREA SK s.r.o.
 * @copyright	Copyright (c) 2012, KREA SK s.r.o.
 * @link		http://www.krea.com/docs/hyperlink
 * @since		Version 1.0.0
 */
class Hyperlink_ft extends EE_Fieldtype {

	var $info = array(
		'name' => 'Hyperlink',
		'version' => '1.0.0'
	);
	var $cache = array(
		'post_data' => array()
	);
	var $has_array_data = TRUE;

	/**
	 * Constructor (inicialize session & language)
	 *
	 * @return void
	 */
	function Hyperlink_ft() {
		parent::__construct();

		// Create addon_name from class name
		$this->addon_name = strtolower(substr(__CLASS__, 0, -3));

		$this->EE->lang->loadfile($this->addon_name);

		//init caching management
		global $__HYPERLINK_CACHE;

		if (!isset($__HYPERLINK_CACHE)) {
			$__HYPERLINK_CACHE = array(
				"includes" => array()
			);
		}

		$this->cache = &$__HYPERLINK_CACHE;
	}

	/**
	 * Constructor (inicialize session & language)
	 *
	 * @return void
	 */
	function __construct() {
		return $this->Hyperlink_ft();
	}

	/**
	 *  Install
	 *
	 * @return array
	 */
	function install() {
		$queries = array();

		$queries[] = "CREATE TABLE IF NOT EXISTS `exp_hyperlink` (
			  `hyperlink_id` int(11) NOT NULL auto_increment,
			  `site_id` int(11) NOT NULL,
			  `entry_id` int(11) NULL,
			  `special` tinyint(4) NOT NULL default '1',
			  `special_id` varchar(200) NULL,
			  `hyperlink_url` varchar(200) NOT NULL,
			  `hyperlink_title` varchar(200) NULL,
			  `hyperlink_alt` varchar(200) NULL,	
			  `hyperlink_nofollow` tinyint(11) NOT NULL default '0',			  		  
			  `hyperlink_status` tinyint(11) NOT NULL default '1',
			  `hyperlink_http_status` int(11) NULL,
			  `hyperlink_http_status_date` int(11) NULL,			  
			  `screenshot_id` varchar(200) default NULL,
			  `screenshot_dir` int(11) default NULL,
			  `screenshot_name` varchar(100) default NULL,
			  `screenshot_status` varchar(20) default NULL,
			  `screenshot_error` varchar(200) default NULL,
			  `report_status` tinyint(11) NOT NULL default '0',
			  `report_date` int(11) default NULL,
			  `hits` int(11) NOT NULL default '0',		  
			  `entry_date` int(11) NOT NULL,
			  `edit_date` int(11) default NULL,
			  PRIMARY KEY  (`hyperlink_id`)
		)";

		foreach ($queries as $query) {
			$this->EE->db->query($query);
		}
	}

	/**
	 *  Uninstall
	 *
	 * @return array
	 */
	function uninstall() {
		$queries = array();
		$queries[] = "DROP TABLE IF EXISTS `exp_hyperlink`";

		foreach ($queries as $query) {
			$this->EE->db->query($query);
		}
	}

	/**
	 * Load hyperlink lib
	 *
	 * @return string
	 */
	private function _load_hyperlink_lib() {
		if (!isset($this->EE->hyperlink_lib)) {
			require_once(dirname(__FILE__) . '/libraries/Hyperlink_lib.php');
			$this->EE->hyperlink_lib = new Hyperlink_lib();
		}
	}

	/**
	 * Return CP theme URL
	 *
	 * @return string
	 */
	private function _theme_url() {
		$this->cache['theme_url'] = $this->EE->{$this->addon_name . '_lib'}->define_theme_url($this->addon_name);
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
			$this->EE->cp->add_to_head('<script type="text/javascript">' . $js . '</script>');
		}
	}

	/**
	 * Prepare data before display on PUBLISH page
	 *
	 * @return string
	 */
	private function _display_field($data) {
		$this->_load_hyperlink_lib();

		//saved

		if ($data && !is_array($data) && is_numeric($data)) {
			$data = @$this->EE->db->select("*")
							->from("exp_hyperlink")
							->where("hyperlink_id", $data)
							->get()
							->row_array();

			if (!is_array($data)) {
				$data = array();
			}

			if (@$data['screenshot_dir'] && @$data['screenshot_name']) {
				$upload_preferences = $this->EE->hyperlink_lib->upload_preferences();
				$data['screenshot'] = rtrim(@$upload_preferences[$data['screenshot_dir']]['url'], '/') . '/' . $data['screenshot_name'];
			}
		} else {
			if ($data) {
				global $__HYPERLINK_STATUSES;

				if (isset($__HYPERLINK_STATUSES[$data['hyperlink_url']])) {
					$data['hyperlink_http_status'] = $__HYPERLINK_STATUSES[$data['hyperlink_url']];
				}
			}
		}

		return $data;
	}

	/**
	 * Save Field
	 *  
	 * @return void 
	 */
	function _save($entry_id, $data, $special, $special_id = null, $return_array = false) {

		$old_data_query = $this->EE->db->select('*')->from('exp_hyperlink')
				->where("site_id", $this->EE->config->item('site_id'))
				->where("entry_id", $entry_id)
				->where("special", $special)
				->where("special_id", $special_id)
				->get();

		if ($old_data_query->num_rows) {
			$old_data = $old_data_query->row_array();
		}

		//-----------------------------------
		//	No Link = No Reason to Save
		//-----------------------------------		

		if ($this->_is_url_set($data['hyperlink_url']) == FALSE) {
			if ($old_data_query->num_rows) {
				$this->EE->db->query("DELETE FROM exp_hyperlink WHERE hyperlink_id = '" . (int) $old_data['hyperlink_id'] . "'");
			}

			return NULL;
		}

		//----------------------------
		//	Get status
		//----------------------------	

		global $__HYPERLINK_STATUSES;

		if (!isset($__HYPERLINK_STATUSES)) {
			$__HYPERLINK_STATUSES = array();
		}

		if (isset($__HYPERLINK_STATUSES[$data['hyperlink_url']])) {
			$status = $__HYPERLINK_STATUSES[$data['hyperlink_url']];
		} else {
			$status = $this->EE->hyperlink_lib->get_http_status($data['hyperlink_url']);
			$__HYPERLINK_STATUSES[$data['hyperlink_url']] = $status;
		}

		//----------------------------
		//	Get status
		//----------------------------

		$data = array(
			"site_id" => $this->EE->config->item('site_id'),
			"entry_id" => $entry_id,
			"special" => $special,
			"special_id" => $special_id,
			"hyperlink_url" => $data["hyperlink_url"],
			"hyperlink_title" => $data["hyperlink_title"],
			"hyperlink_alt" => $data["hyperlink_alt"],
			"hyperlink_nofollow" => isset($data["hyperlink_nofollow"]) ? 1 : 0,
			"hyperlink_http_status" => $__HYPERLINK_STATUSES[$data['hyperlink_url']],
			"hyperlink_http_status_date" => $this->EE->localize->now,
			"entry_date" => $this->EE->localize->now,
			"edit_date" => $this->EE->localize->now,
			"screenshot_id" => null,
			"screenshot_dir" => null,
			"screenshot_name" => null,
			"screenshot_status" => $this->EE->hyperlink_lib->settings["screenshot_service"] ? 'take' : null,
			"screenshot_error" => null,
		);

		//----------------------------
		//	Inser OR Update
		//----------------------------

		if ($old_data_query->num_rows) {
			//Keep OLd entry DATE

			unset($data["entry_date"]);

			//screenshot exists && URL do not CHANGE

			if ($old_data["screenshot_id"] && ( $old_data["hyperlink_url"] == $data["hyperlink_url"] )) {
				unset($data["screenshot_id"]);
				unset($data["screenshot_dir"]);
				unset($data["screenshot_name"]);
				unset($data["screenshot_status"]);
				unset($data["screenshot_error"]);
			}

			if ($return_array) {
				return $data;
			}

			$this->EE->db->update('exp_hyperlink', $data, 'hyperlink_id = ' . (int) $old_data["hyperlink_id"]);

			return $old_data["hyperlink_id"];
		} else {
			if ($return_array) {
				return $data;
			}

			$this->EE->db->insert('exp_hyperlink', $data);

			return $this->EE->db->insert_id();
		}
	}

	/**
	 * Is URL set
	 *  
	 * @access private 
	 * @return bool 
	 */
	private function _is_url_set($url) {
		if (trim($url) == ''
				|| trim(strtolower($url)) == 'https://'
				|| trim(strtolower($url)) == 'http://'
		) {
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Display settings
	 *
	 * @param array settings 
	 * @return string
	 */
	function display_global_settings() {
		$url = BASE . AMP . 'C=addons_modules' . AMP . 'M=show_module_cp' . AMP . 'module=hyperlink' . AMP . 'method=settings';
		$form = '<script type="text/javascript">location.href="' . str_replace(AMP, '&', $url) . '"</script>';
		return $form;
	}

	/**
	 * Save
	 *
	 * @param array data
	 * @return string
	 */
	function save($data) {
		$this->cache['post_data'][$this->field_id] = $data;
		return '';
	}

	/**
	 * Save After Entry Created
	 *  
	 * @return void 
	 */
	function post_save() {
		$this->_load_hyperlink_lib();
		$data = $this->cache['post_data'][$this->field_id];

		//-----------------------------------
		//	Create hyperlink
		//-----------------------------------		

		$hyperlink_id = $this->_save($this->settings['entry_id'], $data, HYPERLINK_TYPE_FIELDTYPE, $this->field_id);

		$data = array(
			'field_id_' . $this->field_id => $hyperlink_id
		);

		$this->EE->db->where('entry_id', $this->settings['entry_id'])
				->update('exp_channel_data', $data);
	}

	/**
	 * Validate fieldtype on publish page
	 *
	 * @param array data
	 * @return string
	 */
	function validate($data) {
		$this->_load_hyperlink_lib();

		if ($this->_is_url_set($data['hyperlink_url']) == FALSE) {
			//----------------------------
			//	if URL is not set
			//----------------------------	

			if ($this->settings['field_required'] == 'y') {
				return $this->EE->lang->line('required');
			}
		} else {
			//----------------------------
			//	Get status
			//----------------------------	

			global $__HYPERLINK_STATUSES;

			if (!isset($__HYPERLINK_STATUSES)) {
				$__HYPERLINK_STATUSES = array();
			}

			if (isset($__HYPERLINK_STATUSES[$data['hyperlink_url']])) {
				$status = $__HYPERLINK_STATUSES[$data['hyperlink_url']];
			} else {
				$status = $this->EE->hyperlink_lib->get_http_status($data['hyperlink_url']);
				$__HYPERLINK_STATUSES[$data['hyperlink_url']] = $status;
			}

			//----------------------------
			//	Check status
			//----------------------------

			$status = $this->EE->hyperlink_lib->get_http_status($data['hyperlink_url']);

			if ((int) $status < 200 || (int) $status >= 400) {
				if (!$this->EE->hyperlink_lib->settings["publish_entry_with_invalid_links"]) {
					if ($status == -1) {
						return sprintf($this->EE->lang->line('msg_error_http_status_validation_failed'), $data['hyperlink_url']);
					} else {
						return sprintf($this->EE->lang->line('msg_error_http_status_validation'), $data['hyperlink_url']);
					}
				}
			}
		}

		return TRUE;
	}

	/**
	 * Show fieldtype on PUBLISH page
	 *
	 * @return string
	 */
	function display_field($data) {
		$this->_include_theme_css('publish.css');
		$this->_include_theme_js('publish.js');

		$vars = array(
			"field_name" => $this->field_name,
			"data" => $this->_display_field($data),
			"theme_url" => $this->_theme_url(),
			"settings" => $this->EE->hyperlink_lib->settings,
		);

		// address must by defined as ../../ becouse low_variables...
		return $this->EE->load->view('../../hyperlink/views/publish', $vars, TRUE);
	}

	/**
	 * Before show (nothing special)
	 *
	 * @param array data
	 * @return string
	 */
	function pre_process($data) {
		return $data;
	}

	/**
	 * Replace frontend tag
	 *
	 * @param array data for content
	 * @param array fetch params 
	 * @param string html
	 * @return string
	 */
	function replace_tag($data, $params = array(), $tagdata = FALSE) {
		if (!$data) {
			return FALSE;
		}

		if (!is_numeric($data)) {
			$data = @unserialize(@base64_decode($data));

			$hyperlink["hyperlink_id"] = 0;
			$hyperlink["url"] = @$data["hyperlink_url"];
			$hyperlink["title_tag"] = @$data["hyperlink_title"];
			$hyperlink["alt_tag"] = @$data["hyperlink_alt"];
			$hyperlink["nofollow"] = isset($data["hyperlink_nofollow"]) ? true : false;
			$hyperlink["http_status"] = '';
			$hyperlink["hyperlink_http_status"] = '';
			$hyperlink["hits"] = 0;
			$hyperlink["screenshot_id"] = '';
			$hyperlink["screenshot_dir"] = '';
			$hyperlink["screenshot_name"] = '';
		} else {
			$query = $this->EE->db
							->select("hyperlink_id")
							->select("hyperlink_url 			as url")
							->select("hyperlink_title 			as title_tag")
							->select("hyperlink_alt 			as alt_tag")
							->select("hyperlink_nofollow 		as nofollow")
							->select("hyperlink_http_status 	as http_status")
							->select("hits 						as clicks")
							->select("screenshot_id")
							->select("screenshot_dir")
							->select("screenshot_name")
							->from("exp_hyperlink")->where("hyperlink_id", $data)->get();

			if (!$query->num_rows) {
				return FALSE;
			}

			$hyperlink = $query->row_array();
		}

		if (!$hyperlink["url"]) {
			return FALSE;
		}

		global $__HYPERLINK_CLICK_ACT_ID__;

		if (!isset($__HYPERLINK_CLICK_ACT_ID__)) {
			$__HYPERLINK_CLICK_ACT_ID__ = (int) @$this->EE->db
							->select('action_id')
							->from('exp_actions')
							->where('class', 'Hyperlink')
							->where('method', 'hyperlink_click')
							->get()
							->row()
					->action_id;
		}

		$hyperlink["url_tracked"] = $this->EE->functions->create_url('/') . '?ACT=' . $__HYPERLINK_CLICK_ACT_ID__ . AMP . 'hyperlink_id=' . $hyperlink["hyperlink_id"];

		//-------------------

		if (!$tagdata) {
			$output = '<a target="_blank" href="' . $hyperlink["url_tracked"] . '"';
			$output .= $hyperlink["nofollow"] ? ' rel="nofollow"' : '';
			$output .= $hyperlink["alt_tag"] ? ' alt="' . $hyperlink["alt_tag"] . '"' : '';
			$output .= $hyperlink["title_tag"] ? ' title="' . $hyperlink["title_tag"] . '"' : '';
			$output .= '>';
			$output .= $hyperlink["url"];
			$output .= '</a>';

			return $output;
		} else {
			if (!isset($__HYPERLINK_UPLOAD_PREFERENCES__)) {
				$this->_load_hyperlink_lib();
				$__HYPERLINK_UPLOAD_PREFERENCES__ = $this->EE->hyperlink_lib->upload_preferences();
			}

			$hyperlink["preview"] = '';

			if (count($__HYPERLINK_UPLOAD_PREFERENCES__) && $hyperlink["screenshot_dir"] && $hyperlink["screenshot_name"]) {
				$hyperlink["preview"] = @$__HYPERLINK_UPLOAD_PREFERENCES__[(int) $hyperlink["screenshot_dir"]]["url"];
				$hyperlink["preview"] .= $hyperlink["screenshot_name"];

				//sizes support

				$sizes = array();
				preg_match_all('%{preview:(.*)}%', $tagdata, $matches);
				if (isset($matches[0])) {
					foreach ($matches[0] as $match_index => $match) {
						$sizes[] = array(
							"pattern" => $matches[0][$match_index],
							"replacement" => $matches[1][$match_index],
						);
					}
				}

				foreach ($sizes as $size) {
					$hyperlink[trim($size["pattern"], '{ }')] =
							$__HYPERLINK_UPLOAD_PREFERENCES__[(int) $hyperlink["screenshot_dir"]]["url"] .
							"_" . $size["replacement"] . "/" . $hyperlink["screenshot_name"];
				}
			}

			//parse	

			$output = $tagdata;
			$output = $this->EE->functions->prep_conditionals($output, $hyperlink);

			//after conds

			$hyperlink["nofollow"] = $hyperlink["nofollow"] ? 'rel="nofollow"' : '';
			$hyperlink["rel_tag"] = $hyperlink["nofollow"] ? 'nofollow' : '';

			foreach ($hyperlink as $k => $v) {
				$output = str_replace(LD . $k . RD, $v, $output);
			}

			return $output;
		}
	}

	/**
	 * Display settings
	 *
	 * @param array settings
	 * @return void
	 */
	function display_settings($settings) {
		return false;

		/*
		  $settings_url = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=hyperlink'.AMP.'method=settings';

		  $this->EE->table->add_row(
		  lang('label_settings_link_label'),
		  '<a href="'.BASE.'" target="_blank" href="'.$settings_url.'">'.lang('label_settings_link_title').'</a>'
		  );
		 */
	}

	/**
	 * Save settings
	 *
	 * @param array submit data
	 * @return array save data 
	 */
	function save_settings($data) {
		return $data;
	}

	/* ====================================================================================================
	  XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
	  ------------------------------------------------------------------------------------------------------

	  MATRIX

	  ------------------------------------------------------------------------------------------------------
	  XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
	  ==================================================================================================== */

	/**
	 * Validate fieldtype on publish page
	 *
	 * @param array data
	 * @return string
	 */
	function validate_cell($data) {
		return $this->validate($data);
	}

	/**
	 * Save
	 *
	 * @param array data
	 * @return string
	 */
	function save_cell($data) {
		$this->cache['post_data']['matrix'][$this->field_id][] = $data;

		if ($this->_is_url_set($data['hyperlink_url'])) {
			return base64_encode(serialize($data));
		} else {
			return '';
		}
	}

	/**
	 * Save After Matrix Row Created
	 *  
	 * @return void 
	 */
	function post_save_cell() {
		$cell_data = array_shift($this->cache['post_data']['matrix'][$this->field_id]);
		$special_id = $this->settings["row_id"] . ';' . $this->settings["col_id"];

		//-----------------------------------
		//	Create hyperlink
		//-----------------------------------		

		$hyperlink_id = $this->_save($this->settings['entry_id'], $cell_data, HYPERLINK_TYPE_MATRIX, $special_id);

		$data = array(
			'col_id_' . $this->settings["col_id"] => $hyperlink_id
		);

		$this->EE->db->where('row_id', $this->settings["row_id"])
				->update('exp_matrix_data', $data);
	}

	/**
	 * Show fieldtype on PUBLISH page
	 *
	 * @return string
	 */
	function display_cell($data) {
		$this->_include_theme_css('publish.css');
		$this->_include_theme_js('publish.js');
		$this->_include_theme_js('publish_matrix.js');

		$vars = array(
			"field_name" => $this->cell_name,
			"data" => $this->_display_field($data),
			"theme_url" => $this->_theme_url(),
			"settings" => $this->EE->hyperlink_lib->settings,
		);

		return $this->EE->load->view('publish', $vars, TRUE);
	}

	/**
	 * Display settings
	 *
	 * @param array settings
	 * @return array
	 */
	function display_cell_settings($settings) {
		return array();

		/*
		  $settings_url = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=hyperlink'.AMP.'method=settings';

		  return array(
		  array(
		  lang('label_settings_link_label'),
		  '<a href="'.BASE.'" target="_blank" href="'.$settings_url.'">'.lang('label_settings_link_title').'</a>'
		  )
		  );
		 */
	}

	/**
	 * When MATRIX row is Deleted
	 *
	 * @param array settings
	 * @return array
	 */
	function delete_rows($rows_ids) {
		$this->_load_hyperlink_lib();

		foreach ($rows_ids as $row_id) {
			$this->EE->db->query("DELETE FROM exp_hyperlink WHERE special=" . (int) HYPERLINK_TYPE_MATRIX . " AND special_id LIKE '" . $row_id . ";%'");
		}
	}

	/* ====================================================================================================
	  XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
	  ------------------------------------------------------------------------------------------------------

	  LOW VARIABLES

	  ------------------------------------------------------------------------------------------------------
	  XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
	  ==================================================================================================== */

	/**
	 * Display var settings
	 *
	 * @param array settings
	 * @return array
	 */
	function display_var_settings($settings) {
		return array();

		/*
		  $settings_url = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=hyperlink'.AMP.'method=settings';

		  return array(
		  array(
		  lang('label_settings_link_label'),
		  '<a href="'.BASE.'" target="_blank" href="'.$settings_url.'">'.lang('label_settings_link_title').'</a>'
		  )
		  );
		 */
	}

	/**
	 * Replace Low Variables tag
	 *
	 * @param array data for content
	 * @param array fetch params 
	 * @param string html
	 * @return string
	 */
	function display_var_tag($data, $params = '', $tagdata = '') {
		return $this->replace_tag($data, $params, $tagdata);
	}

	/**
	 * Show fieldtype on PUBLISH page
	 *
	 * @return string
	 */
	function display_var_field($data) {
		return $this->display_field($data);
	}

	/**
	 * Save
	 *
	 * @param array data
	 * @return string
	 */
	function save_var_field($data) {
		$this->_load_hyperlink_lib();
		$hyperlink_id = $this->_save(null, $data, HYPERLINK_TYPE_LOW_VARIABLE, $this->var_id);
		return $hyperlink_id;
	}

	/* ====================================================================================================
	  XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
	  ------------------------------------------------------------------------------------------------------

	  EP BETTER WORKFLOW

	  ------------------------------------------------------------------------------------------------------
	  XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
	  ==================================================================================================== */

	/**
	 * Draft Save (http://betterworkflow.electricputty.co.uk/api.html)
	 *
	 * @param array Field data as submitted from publish form
	 * @param string Either 'create' or 'update'
	 * @return string
	 */
	function draft_save($data, $draft_action) {
		$this->_load_hyperlink_lib();

		// We are creating a new draft

		return $this->_save($this->settings['entry_id'], $data, HYPERLINK_TYPE_EP_BETTER_WORKFLOW, $this->settings['field_id']);
	}

	/**
	 * In BWF publishing a draft involves replacing the current live entry's data with the draft data and then deleting the draft record. 
	 * In the content of a third party FieldType that means deleting all the data where the is_draft flag is currently set to false (as in the live entry's data) and then updating the remaining data, switching the the is_draft flags from true to false.
	 *
	 * @return string
	 */
	public function draft_discard() {
		$this->EE->db->query("DELETE FROM exp_hyperlink WHERE special=" . (int) HYPERLINK_TYPE_EP_BETTER_WORKFLOW . " AND entry_id = '" . $this->settings['entry_id'] . "' AND special_id = '" . $this->settings['field_id'] . "'");
		return;
	}

	/**
	 * This method is called when BWF discards a draft and should be used to delete all content for the field which is flagged as draft.
	 *
	 * @return string
	 */
	public function draft_publish() {
		$this->EE->db->query("DELETE FROM exp_hyperlink WHERE special=" . (int) HYPERLINK_TYPE_FIELDTYPE . " AND entry_id = '" . $this->settings['entry_id'] . "' AND special_id = '" . $this->settings['field_id'] . "'");

		$this->EE->db->query("UPDATE exp_hyperlink SET special=" . (int) HYPERLINK_TYPE_FIELDTYPE . " WHERE special=" . (int) HYPERLINK_TYPE_EP_BETTER_WORKFLOW . " AND entry_id = '" . $this->settings['entry_id'] . "' AND special_id = '" . $this->settings['field_id'] . "'");
		return;
	}

}
