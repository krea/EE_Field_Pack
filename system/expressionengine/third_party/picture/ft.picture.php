<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Picture_ft fieldtype Class - by KREA SK s.r.o.
 *
 * @package		Picture
 * @required	EE 2.2.0+
 * @author		KREA SK s.r.o.
 * @copyright	Copyright (c) 2013, KREA SK s.r.o.
 * @link		http://www.krea.com/docs/content-elements
 * @since		Version 1.0
 */

class Picture_ft extends EE_Fieldtype {

	public $info = array(
		'name' => 'Picture',
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
	function Picture_ft() {
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
		return $this->Picture_ft();
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
		$theme_url = $this->_theme_url();

		//add styles to head
		$this->EE->cp->add_to_head('<link rel="stylesheet" href="' . $theme_url . 'styles.css" type="text/css" media="screen" />');

		//add scripts to foot
		$this->EE->cp->add_to_foot('<script type="text/javascript" src="' . $theme_url . 'publish.js"></script>');

		//if call with element_name
		if ($advanced == 'content_elements') {
			$this->EE->cp->add_to_foot('<script type="text/javascript" src="' . $theme_url . 'publish_ce.js"></script>');
		}

		if (!$data) {
			#######################
			#
			#	NEW ENTRY
			#
			#######################
			$picture_id = md5(uniqid() . rand(1, 9999999));

			if ($advanced == "content_elements")
			{
				$picture_id = "__picture_index__";
			}

			$picture_image = '';
			$picture_upload_dir = $this->settings['picture_upload_dir'];
			$picture_alignment = '';
			$picture_size = '';
			$picture_url = '';
			$picture_thumb = '';
			$picture_description = '';
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

			$picture_id = $data["picture_id"];
			$picture_image = $data["image"];
			$picture_upload_dir = $data["upload_dir"];
			$picture_alignment = $data["alignment"];
			$picture_size = $data["size"];
			$picture_url = $data["url"];
			$picture_description = $data["description"];
			$picture_thumb = '';

			if ($picture_image && $picture_upload_dir)
			{
				$this->EE->load->library('filemanager');
				$thumb_info = $this->EE->filemanager->get_thumb($picture_image, $picture_upload_dir);
				$picture_thumb = $thumb_info['thumb'];
			}
		}

		//load sizes
		$default_sizes = array(
			"" => $this->EE->lang->line("picture_size_label"),
			"original" => $this->EE->lang->line("picture_size_original"),
			"thumb" => $this->EE->lang->line("picture_size_thumb")
		);
		$current_sizes = $default_sizes;

		$sizes = array();
		$upload_prefs = array();
		foreach ($this->_upload_prefs() as $pref)
		{
			if (!array_key_exists($pref->id, $sizes))
			{
				$sizes[$pref->id] = array();
			}
			foreach ($default_sizes as $k=>$v) $sizes[$pref->id][] = array(
				"id" => $k,
				"value" => $v
			);
		}

		foreach ($this->EE->db->select("id, upload_location_id, title, width, height")->from("exp_file_dimensions")->get()->result() as $size)
		{
			if (!array_key_exists($size->upload_location_id, $sizes))
			{
				$sizes[$size->upload_location_id] = array();
			}
			$sizes[$size->upload_location_id][] = array(
				"id"	=> $size->id,
				"value" => htmlspecialchars($size->title).' ('.$size->width.'x'.$size->height.')'
			);

			if ($picture_upload_dir == $size->upload_location_id)
			{
				$current_sizes[$size->id] = htmlspecialchars($size->title).' ('.$size->width.'x'.$size->height.')';
			}
		}

		$this->EE->cp->add_to_foot('<script type="text/javascript">var picture_sizes = '.json_encode($sizes).'</script>');
		$this->EE->cp->add_to_foot('<script type="text/javascript">var picture_default_sizes = '.json_encode($default_sizes).'</script>');

		//display vars
		$vars = array(
			"settings" => $this->settings,

			"field_name" => ($advanced == 'matrix') ? $this->cell_name : $this->field_name,
			"picture_id" => $picture_id,
			"picture_image" => $picture_image,
			"picture_upload_dir" => $picture_upload_dir,
			"picture_alignment" => $picture_alignment,
			"picture_size" => $picture_size,
			"picture_url" => $picture_url,
			"picture_description" => $picture_description,
			"picture_thumb" => $picture_thumb,
			"alignment_options" => array(
				""			=> $this->EE->lang->line("picture_alignment_label"),
				"left" 		=> $this->EE->lang->line("picture_alignment_left"),
				"center" 	=> $this->EE->lang->line("picture_alignment_center"),
				"right" 	=> $this->EE->lang->line("picture_alignment_right"),
			),
			"sizes_options" => $current_sizes,
		);

		$this->EE->load->add_package_path(dirname(__FILE__));
		return $this->EE->load->view('picture', $vars, TRUE);
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

		$picture = $_POST["picture"][$data];

		//files is required, but no file was submit...
		if ($this->settings['field_required'] == 'y' && !@$picture["image"]) {
			return $this->EE->lang->line('required');
		}

		//files is required, but no file was submit...
		if (!@$picture["image"] && (@$picture["description"] OR @$picture["url"])) {
			return $this->EE->lang->line('error_empty_image');
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

		$picture_id = $data;

		$save_data = array(
			"picture_id" => $data,
			"upload_dir" => $_POST["picture"][$picture_id]["upload_dir"],
			"image" => $_POST["picture"][$picture_id]["image"],
			"description" => $_POST["picture"][$picture_id]["description"],
			"url" => $_POST["picture"][$picture_id]["url"],
			"size" => $_POST["picture"][$picture_id]["size"],
			"alignment" => $_POST["picture"][$picture_id]["alignment"],
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
	 * Get upload preferences
	 *
	 * @access private
	 * @return array
	 */
	private function _upload_prefs()
	{
		$upload_prefs = $query = $this->EE->db->select("*")
			->from("upload_prefs")
			->where("site_id", $this->EE->config->item('site_id'))
			->get()->result();

		return $upload_prefs;
	}

	/**
	 * Display settings
	 *
	 * @param array
	 * @return void
	 */
	function display_settings($data) {

		$upload_prefs = array();
		foreach ($this->_upload_prefs() as $pref)
		{
			$upload_prefs[$pref->id] = $pref->name;
		}

		$upload_dir = form_dropdown("picture_upload_dir", $upload_prefs, @$data["picture_upload_dir"]);
		if (empty($upload_prefs))
		{
			$upload_dir = "<p class='notice'>".$this->EE->lang->line("error_no_upload_locations_available")."</p>".form_hidden('picture_upload_dir', '');
		}

		$this->EE->table->add_row(
			lang('picture_upload_dir', 'picture_upload_dir'), $upload_dir
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
			'picture_upload_dir' => (int) $this->EE->input->post('picture_upload_dir'),
		);
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
		$upload_prefs = array();
		foreach ($this->_upload_prefs() as $pref)
		{
			$upload_prefs[$pref->id] = $pref->name;
		}

		$upload_dir = form_dropdown("picture_upload_dir", $upload_prefs, @$data["picture_upload_dir"]);
		if (empty($upload_prefs))
		{
			$upload_dir = "<p class='notice'>".$this->EE->lang->line("error_no_upload_locations_available")."</p>".form_hidden('picture_upload_dir', '');
		}

		return array(
			array(
				lang('picture_upload_dir', 'picture_upload_dir'), $upload_dir
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
		$tagdata = file_get_contents(PATH_THIRD . 'picture/views/preview.php');
		return $this->replace_element_tag($data, array(), file_get_contents(PATH_THIRD . 'picture/views/preview.php'));
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

		//load file dimensions
		if (empty(self::$cache["file_dimensions"])) {
			$this->EE->db->from('file_dimensions');

			foreach ($this->EE->db->get()->result_array() as $row) {
				self::$cache["file_dimensions"][$row["id"]] = $row;
			}
		}
		if (!empty(self::$cache["file_dimensions"])) {
			$file_dimensions = self::$cache["file_dimensions"];
		} else {
			$file_dimensions = NULL;
		}

		//validate input
		if (!is_array(@unserialize($data))) {
			return false;
		} else {
			$data = unserialize($data);
		}

		//no input
		if (!isset($data["picture_id"])) {
			return false;
		}

		$picture = array();

		//name
		$picture["name"] = $data["image"];

		//extension
		$ext_parts = (explode(".", $picture["name"]));
		$ext = (count($ext_parts) > 1) ? end($ext_parts) : '';

		$picture["extension"] = str_replace('jpeg', 'jpg', strtolower($ext));

		if (isset($upload_preferences[$data["upload_dir"]])) {
			$picture["dir"] = $upload_preferences[$data["upload_dir"]]["url"];
			$picture["server_path"] = $upload_preferences[$data["upload_dir"]]["server_path"];
			$picture["file"] = $picture["dir"] . $picture["name"];
			$picture["url"] = $picture["dir"] . $picture["name"];

			//get file size
			if (strpos($tagdata, '{size}') !== FALSE) {

				$picture["size"] = filesize($upload_preferences[$data["upload_dir"]]["server_path"] . $picture["name"]);

				if ($picture["size"] > 1024 * 1024 * 1024) {
					$picture["size"] = round($picture["size"] / (1024 * 1024 * 1024), 2) . 'GB';
				}
				if ($picture["size"] > 1024 * 1024) {
					$picture["size"] = round($picture["size"] / (1024 * 1024), 2) . 'MB';
				}
				if ($picture["size"] > 1024) {
					$picture["size"] = round($picture["size"] / 1024, 2) . 'kB';
				} else {
					$picture["size"] = $picture["size"] . 'B';
				}
			} else {
				$picture["size"] = "0B";
			}
		} else {
			$picture["dir"] = "";
			$picture["server_path"] = "";
			$picture["file"] = "";
			$picture["size"] = "0B";
		}

		$picture["alignment"] = $data["alignment"];
		$picture["url"] = $data["url"];

		$picture["alt"] = str_replace("\n", " ", $data["description"]);
		$picture["description"] = str_replace("\n", "<br />", $data["description"]);

		if ($data["image"])
		{
			$this->EE->load->library('filemanager');
			$thumb_info = $this->EE->filemanager->get_thumb($data["image"], $data["upload_dir"]);
			$picture["thumb"] = $thumb_info['thumb'];
		}
		else
		{
			$picture["thumb"] = "";
		}

		$picture["image:thumb"] = $picture['thumb'];

		if (is_array($file_dimensions)) {
			foreach ($file_dimensions as $dimension)
			{
				$picture["image:".$dimension["short_name"]] = $picture["dir"] . "_" . $dimension["short_name"] . "/" . $picture["name"];
			}
		}

		if ($data["size"] == "thumb")
		{
			$picture["image"] = $picture["thumb"];
			$picture["image_size"] = "thumb";
		}
		elseif ((!is_null($file_dimensions)) AND (array_key_exists($data["size"], $file_dimensions)))
		{
			$picture["image"] = $picture["image:".$file_dimensions[$data["size"]]["short_name"]];
			$picture["image_size"] = "sized";
		}
		else
		{
			$picture["image"] = $picture["dir"] . $picture["name"];
			$picture["image_size"] = "original";
		}

		$picture["original"] = $picture["dir"] . $picture["name"];

		return $picture;
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

		if (defined('PICTURE_THEME_URL'))
			return PICTURE_THEME_URL;

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

		define('PICTURE_THEME_URL', $theme_url . $addon_name . '/');

		return PICTURE_THEME_URL;
	}

}

// END Password_Ft class

/* End of file ft.picture.php */
/* Location: ./system/expressionengine/third_party/files/ft.picture.php */
