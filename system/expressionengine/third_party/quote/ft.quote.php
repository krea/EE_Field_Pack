<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Quote_ft fieldtype Class - by KREA SK s.r.o.
 *
 * @package		Quote
 * @author		KREA SK s.r.o.
 * @copyright	Copyright (c) 2013, KREA SK s.r.o.
 * @link		http://www.krea.com/docs/content-elements
 * @since		Version 1.0
 */
 
class Quote_ft extends EE_Fieldtype {

	public $info = array(
		'name' => 'Quote',
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
	function Quote_ft() {
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
		return $this->Quote_ft();
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
			$quote_id = md5(uniqid() . rand(1, 99999));
			$quote_value = "";			
			$quote_author = "";
			
			if ($advanced == "content_elements")
			{
				$quote_id = "__quote_index__";
			}			
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
			$quote_id = $data["quote_id"];
			$quote_value = $data["quote_value"];			
			$quote_author = $data["quote_author"];	
		}
		
		$vars = array(
			"value" => '',
			"field_name" => ($advanced == 'matrix') ? $this->cell_name : $this->field_name, // prepare for matrix 
			"quote_id" => $quote_id,
			"quote_value" => $quote_value,			
			"quote_author" => $quote_author,			
			"quote_rows" => array_key_exists("quote_rows", $this->settings)?$this->settings["quote_rows"]:6
		);

		$this->EE->load->add_package_path(dirname(__FILE__));
		return $this->EE->load->view('quote', $vars, TRUE);
	}

	/**
	 * Validate data
	 *
	 * @param array/string
	 * @return void
	 */
	function validate($data) {
	
		$quote_id = $data;
		
		//data must exists
		if (!$quote_id || empty($quote_id))
		{
			return TRUE;
		}
	
		//if quote is required
		if ($this->settings['field_required'] == 'y' &&@$_POST["quote"][$quote_id]["quote_value"]) {
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
		$quote_id = $data;
		$save_data = array(
			"quote_id" => $data,
			"quote_value" => $_POST["quote"][$quote_id]["quote_value"],			
			"quote_author" => $_POST["quote"][$quote_id]["quote_author"],						
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
		if (!is_array($data))
		{
			$data = $this->_prepare_tagdata($data, $tagdata);
		}		

		// {char_limit}
		if ((int) @$params['char_limit']) {
			if (mb_strlen($data["quote"], 'UTF-8') > (int) $params['char_limit']) {
				$data["quote"] = trim(mb_strcut($data["quote"], 0, (int) @$params['char_limit'], 'UTF-8'), '. ') . '...';
			}
		}
		
		// Settings: content type
		if (!defined("BASE")) {
			if ($this->settings["quote_content_format"] == "blockquote") 
			{
				$data["quote"] = "<blockquote>".htmlspecialchars($data["quote"])."</blockquote>";
				$data["quote"] = str_replace("\n", "<br />", $data["quote"]);				
			}
			else
			{
				$data["quote"] = $this->EE->typography->parse_type($data["quote"], array(
					'text_format' => $this->settings["quote_content_format"],
					'html_format' => !empty($this->row['channel_html_formatting']) ? $this->row['channel_html_formatting'] : 'safe',
					'auto_links' => (@$this->row['channel_auto_link_urls'] == 'y') ? 'y' : 'n',
					'allow_img_url' => (@$this->row['channel_allow_img_urls'] == 'y') ? 'y' : 'n'
					)
				);
			}
		}

		// Replace EE entities
		$data = preg_replace("/{([_a-zA-Z]*)}/u", "&#123;$1&#125;", $data);
		
		$data["value"] = $data["quote"];

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
		$quote_content_formats = array(
			'none' => htmlspecialchars($this->EE->lang->line('quote_format_none')),		
			'br' => htmlspecialchars($this->EE->lang->line('quote_format_br')),
			'xhtml' => htmlspecialchars($this->EE->lang->line('quote_format_xhtml')),
			'blockquote' => htmlspecialchars($this->EE->lang->line('quote_format_blockquote')),			
		);	
	
		$this->EE->table->add_row(
				lang('quote_rows', 'quote_rows'), form_input(
						array(
							'id' => 'quote_rows',
							'name' => 'quote_rows',
							'size' => 4,
							'value' => isset($data['quote_rows']) ? $data['quote_rows'] : '4',
						)
				)
		);
	
		$this->EE->table->add_row(
				lang('quote_format', 'quote_format'), 
				form_dropdown('quote_content_format', $quote_content_formats, @$data['quote_content_format'])
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
			'quote_rows' 		=> (int) $this->EE->input->post('quote_rows'),
			'quote_content_format'	=> $this->EE->input->post('quote_content_format'),
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
		$quote_content_formats = array(
			'none' => htmlspecialchars($this->EE->lang->line('quote_format_none')),		
			'br' => htmlspecialchars($this->EE->lang->line('quote_format_br')),
			'xhtml' => htmlspecialchars($this->EE->lang->line('quote_format_xhtml')),
			'blockquote' => htmlspecialchars($this->EE->lang->line('quote_format_blockquote')),	
		);		
	
		return array(
			array(
				lang('quote_rows', 'quote_rows'),
				form_input(
						array(
							'id' => 'quote_rows',
							'name' => 'quote_rows',
							'size' => 4,
							'value' => isset($data['quote_rows']) ? $data['quote_rows'] : '4',
						)
				)
			),
			array(
				lang('quote_format', 'quote_format'), 
				form_dropdown('quote_content_format', $quote_content_formats, @$data['quote_content_format'])
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
		$tagdata = file_get_contents(PATH_THIRD . 'quote/views/preview.php');
		return $this->replace_element_tag($data, array(), file_get_contents(PATH_THIRD . 'quote/views/preview.php'));
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

		return $this->replace_tag($data, $params, $tagdata);
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
		$data = unserialize($data);		
		$quote = array(
			"author" => @$data["quote_author"],
			"quote"	=> @$data["quote_value"],
		);
		return $quote;
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
	 * Tool: get theme url
	 *  
	 * @access public
	 */
	public function _theme_url() {
		$this->cache['theme_url'] = $this->define_theme_url($this->addon_name);
		return $this->cache['theme_url'];
	}

	public function define_theme_url($addon_name = 'content_elements') {

		if (defined('QUOTE_THEME_URL'))
			return QUOTE_THEME_URL;

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

		define('QUOTE_THEME_URL', $theme_url . $addon_name . '/');

		return QUOTE_THEME_URL;
	}

}

// END Password_Ft class

/* End of file ft.text.php */
/* Location: ./system/expressionengine/third_party/quote/ft.quote.php */