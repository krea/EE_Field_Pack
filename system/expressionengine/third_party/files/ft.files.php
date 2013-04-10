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
class Files_ft extends EE_Fieldtype {

	public $info = array(
		'name' => 'Files',
		'version' => '1.0'
	);
	// Parser Flag (preparse pairs?)
	public $has_array_data = TRUE;
	public static $cache = array(
		"includes" => array(),
		"upload_preferences" => array()
	);

	/**
	 * Constructor
	 *
	 * @return void
	 */
	function Files_ft() {
		parent::__construct();

		// Create addon_name from class name
		$this->addon_name = strtolower(substr(__CLASS__, 0, -3));

		//fetch language		
		$this->EE->lang->loadfile($this->addon_name);
	}

	/**
	 * Constructor
	 *
	 * @return void
	 */
	function __construct() {
		return $this->Files_ft();
	}

	/**
	 * Display field
	 *
	 * @param array/string
	 * @param string [matrix, content_elements, or standard EE field]
	 * @return void
	 */
	function display_field($data, $advanced = FALSE) {
		//first time... load css & js	
		$theme_url = rtrim(URL_THIRD_THEMES, '/') . '/files/';

		//diferent EE version has different trigger for upload files
		if (version_compare(APP_VER, '2.2.0', '>=')) {
			$this->EE->cp->add_to_head('<script type="text/javascript">var files_add_file_trigger_version = 2;</script>');
		} else {
			$this->EE->cp->add_to_head('<script type="text/javascript">var files_add_file_trigger_version = 1;</script>');
		}

		//add styles to head			
		$this->EE->cp->add_to_head('<link rel="stylesheet" href="' . $theme_url . 'styles.css" type="text/css" media="screen" />');

		//add scripts to foot			
		$this->EE->cp->add_to_foot('<script type="text/javascript" src="' . $theme_url . 'files.js"></script>');

		//if call with element_name
		if ($advanced == 'content_elements') {
			$this->EE->cp->add_to_foot('<script type="text/javascript" src="' . $theme_url . 'publish_ce.js"></script>');
		}

		//if call with element_name
		if ($advanced == 'content_elements') {
			$this->EE->cp->add_to_foot('<script type="text/javascript" src="' . $theme_url . 'publish_matrix.js"></script>');
		}

		if (!$data) {
			#######################
			#
			#	NEW ENTRY
			#
			#######################

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
			$files = array();
		} else {
			#######################
			#
			#	EDIT ENTRY
			#
			#######################
			//if data can not be unserialize, just resave it!
			if (!is_array(@unserialize(html_entity_decode($data)))) {
				$data = $this->save($data);
			}
			$data = unserialize(html_entity_decode($data));

			//fetch vars
			$files_id = $data["files_id"];
			$files = array();

			//if files saved
			if (isset($data["files"]["dir"])) {
				//loop files
				foreach ($data["files"]["dir"] as $file_id => $dir_id) {
					//only if directory is valid
					if ($data["files"]["dir"][$file_id]) {
						//load thumb					
						if (version_compare(APP_VER, '2.2.0', '<')) {
							$upload_directory_data = $this->EE->db->query("SELECT * FROM exp_upload_prefs WHERE id='" . (int) $data["files"]["dir"][$file_id] . "'");
							$upload_directory_server_path = $upload_directory_data->row('server_path');
							$upload_directory_url = $upload_directory_data->row('url');

							if (file_exists($upload_directory_server_path . '_thumbs/thumb_' . $data["files"]["name"][$file_id])) {
								$thumb = $upload_directory_url . '_thumbs/thumb_' . $data["files"]["name"][$file_id];
							} else {
								$thumb = PATH_CP_GBL_IMG . 'default.png';
							}
						} else {
							$this->EE->load->library('filemanager');
							$thumb_info = $this->EE->filemanager->get_thumb($data["files"]["name"][$file_id], $data["files"]["dir"][$file_id]);
							$thumb = $thumb_info['thumb'];
						}

						$files[] = array(
							"dir" => $data["files"]["dir"][$file_id],
							"name" => $data["files"]["name"][$file_id],
							"caption" => $data["files"]["caption"][$file_id],
							"thumb" => $thumb,
						);
					}
				}
			}
		}

		$vars = array(
			"files" => $files,
			"field_name" => ($advanced == 'matrix') ? $this->cell_name : $this->field_name,
			"files_id" => $files_id,
			"files_limit" => $this->settings["files_limit"],
		);

		$this->EE->load->add_package_path(dirname(__FILE__));
		return $this->EE->load->view('files', $vars, TRUE);
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

		//files is required, but no file was submit...
		if ($this->settings['field_required'] == 'y' && !isset($_POST["files"][$data])) {
			return $this->EE->lang->line('required');
		}

		//success
		return TRUE;
	}

	/**
	 * Save process
	 *
	 * @param array/string
	 * @return string
	 */
	function save($data) {
		$save_data = array(
			"files_id" => $data,
			"files" => $_POST["files"][$data]
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
		//build output data
		$data = $this->_prepare_tagdata($data, $tagdata);

		//parse tagdata with this data
		return $this->_parse_variables($tagdata, array($data));
	}

	/**
	 * Display settings
	 *
	 * @param array
	 * @return void
	 */
	function display_settings($data) {
		$this->EE->table->add_row(
				lang('files_limit', 'files_limit'), form_input(
						array(
							'id' => 'files_limit',
							'name' => 'files_limit',
							'size' => 4,
							'value' => isset($data['files_limit']) ? $data['files_limit'] : '10',
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
			'files_limit' => (int) $this->EE->input->post('files_limit'),
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
			array(lang('files_limit'), form_input('files_limit', @$data['files_limit'], 'class="matrix-textarea"')),
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
		if ($this->settings['col_required'] == 'y' && !isset($_POST["files"][$data])) {
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
				lang('files_limit', 'files_limit'),
				form_input(
						array(
							'id' => 'files_limit',
							'name' => 'files_limit',
							'size' => 4,
							'value' => isset($data['files_limit']) ? $data['files_limit'] : '10',
						)
				)
			)
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
		$tagdata = file_get_contents(PATH_THIRD . 'files/views/preview.php');
		return $this->replace_element_tag($data, array(), file_get_contents(PATH_THIRD . 'files/views/preview.php'));
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
		//build output data
		$data = $this->_prepare_tagdata($data, $tagdata);

		//append element name	
		$data["element_name"] = $this->element_name;

		return $this->_parse_variables($tagdata, array($data));
	}

#####################################################
#----------------------------------------------------
#	TOOLS
#----------------------------------------------------
#####################################################		

	/**
	 * Prepar(s)e tagdata
	 *
	 * @param array data
	 * @param string tagdata
	 * @return void
	 * @access private
	 */
	function _prepare_tagdata($data, $tagdata) {
		//load upload preferences
		if (empty(self::$cache["upload_preferences"])) {
			$this->EE->db->from('upload_prefs');
			$this->EE->db->order_by('name');

			foreach ($this->EE->db->get()->result_array() as $row) {
				self::$cache["upload_preferences"][$row["id"]] = $row;
			}
		}
		$upload_preferences = self::$cache["upload_preferences"];

		//validate input
		if (!is_array(@unserialize($data))) {
			return false;
		} else {
			$data = unserialize($data);
		}

		//no input			
		if (!isset($data["files_id"])) {
			return false;
		}

		$files["file"] = array();

		//available sizes	
		$sizes = array();
		preg_match_all('%{file:(.*)}%', $tagdata, $matches);
		if (isset($matches[0])) {
			foreach ($matches[0] as $match_index => $match) {
				$sizes[] = array(
					"pattern" => $matches[0][$match_index],
					"replacement" => $matches[1][$match_index],
				);
			}
		}

		//each

		if (isset($data["files"]["dir"]))
			foreach ($data["files"]["dir"] as $file_id => $dir_id)
				if ($data["files"]["dir"][$file_id]) {
					$cell["name"] = $data["files"]["name"][$file_id];

					//get file_ext

					$ext_parts = (explode(".", $cell["name"]));
					$ext = (count($ext_parts) > 1) ? end($ext_parts) : '';

					$cell["extension"] = str_replace('jpeg', 'jpg', strtolower($ext));

					//fetch preferences				

					if (isset($upload_preferences[$data["files"]["dir"][$file_id]])) {
						$cell["dir"] = $upload_preferences[$data["files"]["dir"][$file_id]]["url"];
						$cell["server_path"] = $upload_preferences[$data["files"]["dir"][$file_id]]["server_path"];
						$cell["file"] = $cell["dir"] . $cell["name"];
						$cell["url"] = $cell["dir"] . $cell["name"];

						//get file size

						if (strpos($tagdata, '{size}') !== FALSE) {

							$cell["size"] = filesize($upload_preferences[$data["files"]["dir"][$file_id]]["server_path"] . $cell["name"]);

							if ($cell["size"] > 1024 * 1024 * 1024) {
								$cell["size"] = round($cell["size"] / (1024 * 1024 * 1024), 2) . 'GB';
							}
							if ($cell["size"] > 1024 * 1024) {
								$cell["size"] = round($cell["size"] / (1024 * 1024), 2) . 'MB';
							}
							if ($cell["size"] > 1024) {
								$cell["size"] = round($cell["size"] / 1024, 2) . 'kB';
							} else {
								$cell["size"] = $cell["size"] . 'B';
							}
						} else {
							$cell["size"] = "0B";
						}
					} else {
						$cell["dir"] = "";
						$cell["server_path"] = "";
						$cell["file"] = "";
						$cell["size"] = "0B";
					}

					$cell["caption"] = @$data["files"]["caption"][$file_id];

					//support for multisizes

					foreach ($sizes as $size) {
						$cell[trim($size["pattern"], '{ }')] = $cell["dir"] . "_" . $size["replacement"] . "/" . $cell["name"];
					}

					//thumb			
					if (version_compare(APP_VER, '2.2.0', '<')) {
						if (file_exists($cell["server_path"] . '_thumbs/thumb_' . $cell["name"])) {
							$thumb = $cell["dir"] . '_thumbs/thumb_' . $cell["name"];
						} else {
							$thumb = PATH_CP_GBL_IMG . 'default.png';
						}
					} else {
						$this->EE->load->library('filemanager');
						$thumb_info = $this->EE->filemanager->get_thumb($cell["name"], $data["files"]["dir"][$file_id]);
						$thumb = $thumb_info['thumb'];
					}


					$cell["thumb"] = $thumb;

					//append

					$files["file"][] = $cell;
				}

		return $files;
	}

	/**
	 * Parse variables (one of my favorite recycled functions)
	 *
	 * @param string tagdata
	 * @param array variables
	 * @return string
	 * @access private
	 */
	private function _parse_variables($_tagdata, $vars) {

		$output = ''; //output	
		$count = 0; //counter

		foreach ($vars as $list) {
			$count++;

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
			  /**  Others tag
			  /** ---------------------------------------- */
			if (is_array($list))
				foreach ($list as $tag => $value) {
					//if array ...

					if (is_array($value)) {
						preg_match_all('~{' . $tag . '([^}]*?)}(.*?){/' . $tag . '}~s', $tagdata, $matches);

						foreach ($matches[0] as $i => $match) {
							//fetch params for variable_pairs tag

							$paramsString = str_replace('"', "'", $matches[1][$i]);

							$params = array();
							preg_match_all("/([^']*?)='([^']*?)'/", $paramsString, $paramsMatches);

							if (isset($paramsMatches[0]))
								foreach ($paramsMatches[0] as $pm_index => $pm) {
									if (trim($paramsMatches[1][$pm_index])) {
										$params[trim($paramsMatches[1][$pm_index])] = trim($paramsMatches[2][$pm_index]);
									}
								}

							//offset (rebuild array)

							if ((int) @$params["offset"]) {
								//rebuild array

								$new_value = array();
								$skipped = 0;

								foreach ($value as $k => $v) {
									$skipped++;

									if ($skipped >= (int) @$params["offset"]) {
										$new_value[] = $v;
									}
								}

								$value = $new_value;
							}

							//limit (rebuild array)

							if ((int) @$params["limit"]) {
								//rebuild array

								$new_value = array();
								$printed = 0;

								foreach ($value as $k => $v) {
									$printed++;

									if ($printed <= (int) @$params["limit"]) {
										$new_value[] = $v;
									}
								}

								$value = $new_value;
							}

							//recursive call	...		
							$pattern = $this->_parse_variables($matches[2][$i], $value);

							//... apply recursive data
							$tagdata = str_replace($matches[0][$i], $pattern, $tagdata);
						}
					}

					//... or single variable
					else {
						$tagdata = str_replace('{' . $tag . '}', $value, $tagdata);
					}
				}

			//count

			$tagdata = str_replace('{count}', $count, $tagdata);
			$tagdata = str_replace('{cnt}', $count, $tagdata);
			$tagdata = str_replace('{total_count}', count($vars), $tagdata);

			//conds

			$conds = array();

			if (is_array($list))
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

		//cleanup

		/*
		  $output = preg_replace('~<ul([^>])*'.'>\s*</ul>~s', '', $output);
		 */

		return $output;
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

}

// END Password_Ft class

/* End of file ft.text.php */
/* Location: ./system/expressionengine/third_party/files/ft.files.php */