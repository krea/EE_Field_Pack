<?php

/**
 * ExpressionEngine - by KREA SK s.r.o.
 *
 * @package		ExpressionEngine
 * @author		Krea.com <krea@krea.com>
 * @copyright		Copyright (c) 2012 Krea.com
 * @since			Version 0.5.0
 */
class Promotions_mcp {

	/**
	 * Variables
	 *  
	 * @access public
	 * @var mixed
	 */
	var $version = '0.5.0';
	var $cache = array(
		'includes' => array(),
	);

	/**
	 * Constructor
	 *  
	 * @access public
	 */
	function Promotions_mcp($site_id = false) {
		//before start

		if (session_id() == "")
			session_start();

		$this->EE = & get_instance();

		// Create addon_name from class name
		$this->addon_name = strtolower(substr(__CLASS__, 0, -4));

		//assign top global variables

		if (!$site_id) {
			$this->siteId = $this->EE->config->config['site_id'];
		} else {
			$this->siteId = $site_id;
		}

		$this->siteName = $this->EE->config->config['site_name'];
		$this->memberId = $this->EE->session->userdata['member_id'];
		$this->groupId = $this->EE->session->userdata['group_id'];
		$this->memberEmail = $this->EE->session->userdata['email'];
		$this->memberName = $this->EE->session->userdata['screen_name'];

		if (defined('BASE')) {
			$this->moduleBase = BASE . AMP . 'C=addons_modules' . AMP . 'M=show_module_cp' . AMP . 'module=promotions';
		} else {
			$this->moduleBase = basename($this->EE->config->item('cp_url')) . '?D=cp' . AMP . 'C=addons_modules' . AMP . 'M=show_module_cp' . AMP . 'module=promotions';
		}

		$this->fullModuleBase = $this->EE->config->item('cp_url') . '?D=cp' . AMP . 'C=addons_modules' . AMP . 'M=show_module_cp' . AMP . 'module=promotions';

		//load helper & language & libraries

		$this->EE->load->helper('../third_party/promotions/helper/promotions');
		$this->EE->lang->loadfile('promotions');
		$this->EE->load->library('pagination');
	}

	/**
	 * Constructor
	 *
	 * @return void
	 */
	function __construct($site_id = false) {
		return $this->Promotions_mcp($site_id);
	}

	/*	 * ********************************** INCLUDES FUNCTIONS LIST ***************************************** */

	/**
	 * Tool: parse template with vars
	 *  
	 * @access public
	 */
	function _parse_variables($_tagdata, $vars) {

		$output = '';
		$count = 0;

		//step by step				
		foreach ($vars as $list) {
			$count++;

			//store input
			$tagdata = $_tagdata;

			//kazdy riadok sa sklada z tagov
			foreach ($list as $tag => $value) {
				//if is tag array (variable pairs) ...
				if (is_array($value)) {
					preg_match_all('~{' . $tag . '}(.*?){/' . $tag . '}~s', $tagdata, $matches);


					//change pattern $matches[0] to final $matches[1]						
					foreach ($matches[0] as $i => $match) {
						//call recursion
						$pattern = $this->_parse_variables($matches[1][$i], $value);

						//apply recursion
						$tagdata = str_replace($matches[0][$i], $pattern, $tagdata);
					}
				}
				//... or parse single variables
				else {
					$tagdata = str_replace('{' . $tag . '}', $value, $tagdata);
				}
			}

			//count
			$tagdata = str_replace('{count}', $count, $tagdata);
			$tagdata = str_replace('{cnt}', $count, $tagdata);
			$tagdata = str_replace('{total_count}', count($vars), $tagdata);

			//conditions
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
			}

			$output .= $this->EE->functions->prep_conditionals($tagdata, $conds);
		}

		//clean output from <ul>s
		$output = preg_replace('~<ul([^>])*' . '>\s*</ul>~s', '', $output);

		return $output;
	}

	public function define_theme_url() {
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

		if (!defined('CE_THEME_URL'))
			define('CE_THEME_URL', $theme_url . $this->addon_name . '/');

		return CE_THEME_URL;
	}

	/**
	 * Tool: get theme url
	 *  
	 * @access public
	 */
	public function _theme_url() {
		$this->cache['theme_url'] = $this->define_theme_url();
		return $this->cache['theme_url'];
	}

	/**
	 * Tool: include theme css
	 *  
	 * @access public
	 */
	public function _include_theme_css($file) {
		if (!in_array($file, $this->cache['includes'])) {
			$this->cache['includes'][] = $file;
			$this->EE->cp->add_to_head('<link rel="stylesheet" type="text/css" href="' . $this->_theme_url() . 'styles/' . $file . '" />');
		}
	}

	/**
	 * Tool: include non theme css
	 *  
	 * @access public
	 */
	public function _include_css($file) {
		if (!in_array($file, $this->cache['includes'])) {
			$this->cache['includes'][] = $file;
			$this->EE->cp->add_to_head('<link rel="stylesheet" type="text/css" href="' . $file . '" />');
		}
	}

	/**
	 * Tool: include theme js
	 *  
	 * @access public
	 */
	public function _include_theme_js($file) {
		if (!in_array($file, $this->cache['includes'])) {
			$this->cache['includes'][] = $file;
			$this->EE->cp->add_to_foot('<script type="text/javascript" src="' . $this->_theme_url() . 'scripts/' . $file . '"></script>');
		}
	}

	/**
	 * Tool: include js
	 *  
	 * @access public
	 */
	public function _include_js($file) {
		if (!in_array($file, $this->cache['includes'])) {
			$this->cache['includes'][] = $file;
			$this->EE->cp->add_to_foot('<script type="text/javascript" src="' . $file . '"></script>');
		}
	}

	/**
	 * Tool: direct insert js into foot
	 *  
	 * @access public
	 */
	public function _insert_js($js) {
		$this->EE->cp->add_to_foot('<script type="text/javascript">' . $js . '</script>');
	}

	/**
	 * Tool: trim input data
	 *  
	 * @access public
	 */
	public function _clean_input(&$input) {
		if (is_array($input)) {
			foreach ($input as $k => $v)
				$this->_clean_input($input[$k]);
		} else {
			$input = trim($input);
		}
		return $input;
	}

	/**
	 * Tool: create upload field for older EE
	 *  
	 * @access public
	 */
	public function _create_ee_upload_field($field_name, $filename, $allowed_file_dirs, $content_type = 'image') {
		//set end-point

		if (version_compare(APP_VER, '2.1.0', '<=')) {
			$endpoint_url = 'C=content_publish&M=filemanager_endpoint';
		} else {
			$endpoint_url = 'C=content_publish&M=filemanager_actions';
		}

		$this->EE->lang->loadfile('content');

		$this->EE->javascript->set_global(array(
			'filebrowser' => array(
				'publish' => TRUE
			)
		));

		$this->EE->cp->add_js_script('plugin', array('tmpl', 'ee_table'));

		// Include dependencies
		$this->EE->cp->add_js_script(array(
			'file' => array(
				'underscore',
				'files/publish_fields'
			),
			'plugin' => array(
				'scrollable',
				'scrollable.navigator',
				'ee_filebrowser',
				'ee_fileuploader',
				'tmpl'
			)
		));

		$this->EE->load->helper('html');

		$this->EE->javascript->set_global(array(
			'lang' => array(
				'resize_image' => lang('resize_image'),
				'or' => lang('or'),
				'return_to_publish' => lang('return_to_publish')
			),
			'filebrowser' => array(
				'endpoint_url' => $endpoint_url,
				'window_title' => lang('file_manager'),
				'next' => anchor(
						'#', img(
								$this->EE->cp->cp_theme_url . 'images/pagination_next_button.gif', array(
							'alt' => lang('next'),
							'width' => 13,
							'height' => 13
								)
						), array(
					'class' => 'next'
						)
				),
				'previous' => anchor(
						'#', img(
								$this->EE->cp->cp_theme_url . 'images/pagination_prev_button.gif', array(
							'alt' => lang('previous'),
							'width' => 13,
							'height' => 13
								)
						), array(
					'class' => 'previous'
						)
				)
			),
			'fileuploader' => array(
				'window_title' => lang('file_upload'),
				'delete_url' => 'C=content_files&M=delete_files'
			)
		));

		if (version_compare(APP_VER, '2.2.0', '<')) {
			$this->EE->load->library('filemanager');
			$this->EE->filemanager->filebrowser($endpoint_url);

			$this->EE->javascript->ready("
				 	$.ee_filebrowser();
				 	
					$.ee_filebrowser.endpoint_request = function (b, c, d) {
						
						//hide page with content of other dirs
						
						$('div[id^=page_]').hide();
						$('#page_0').show();
						$('#page_$allowed_file_dirs').show();		
						
						//clear buttons linked to other dirs				
						
						$('#main_navi li').hide();		
						$('li#main_navi_0').show();
						$('li#main_navi_$allowed_file_dirs').show();
						
						//select directory and hide select field
						
						$('select#upload_dir').find('option[value=$allowed_file_dirs]').attr('selected', 'selected');
						$('select#upload_dir').parent().hide();
					
						//standard function body
					
				        if (!d && $.isFunction(c)) {
				            d = c;
				            c = {}
				        }
				        c = $.extend(c, {
				            action: b
				        });
				        $.getJSON(EE.BASE + '&' + EE.filebrowser.endpoint_url + '&' + $.param(c), d)
				    }		 	
				 
					$.ee_filebrowser.add_trigger($('.choose_file'), '$field_name', function(a, file){	 
						if (a.directory == $allowed_file_dirs)
						{
							var a_name = '';
							if (typeof(a.name) !== 'undefined'){a_name = a.name;}
							if (typeof(a.title) !== 'undefined'){a_name = a.title;}
						
							$('#" . $field_name . "_box .filename').html('<img src=\"'+a.thumb+'\" alt=\"'+a.title+'\"><br>'+a_name);			
												
							$('#" . $field_name . "_box input[name=\"" . $field_name . "_hidden\"]').val(a_name);
							$('#" . $field_name . "_box input[name=\"" . $field_name . "_hidden_dir\"]').val($allowed_file_dirs);
							$('#" . $field_name . "_box .file_set').removeClass('js_hide');					   
							$.ee_filebrowser.reset();
						}
						else
						{
							alert('" . $this->EE->lang->line('Directory not allowed') . "');
							$.ee_filebrowser.reset();
						}
					});
			        $('#" . $field_name . "_box .remove_file').click(function(){
						$('#" . $field_name . "_box input[name=\"" . $field_name . "_hidden\"]').val('');
						$('#" . $field_name . "_box .file_set').addClass('js_hide');
				   });
				   
				   
			");
		} else {
			$this->EE->cp->add_to_head($this->EE->view->head_link('css/file_browser.css'));

			$this->EE->javascript->ready("
			        $.ee_filebrowser();
			        $.ee_filebrowser.add_trigger($('.choose_file'), '$field_name', {content_type: \"$content_type\", directory: \"$allowed_file_dirs\"}, function(a){
						var a_name = '';	
						if (typeof(a.name) !== 'undefined'){a_name = a.name;}
						if (typeof(a.title) !== 'undefined'){a_name = a.title;}
						
						$('#" . $field_name . "_box .filename').html('<img src=\"'+a.thumb+'\" alt=\"'+a.title+'\"><br>'+a_name);			
												
						$('#" . $field_name . "_box input[name=\"" . $field_name . "_hidden\"]').val(a_name);
						$('#" . $field_name . "_box input[name=\"" . $field_name . "_hidden_dir\"]').val($allowed_file_dirs);
						$('#" . $field_name . "_box .file_set').removeClass('js_hide');
			        });
			        $('#" . $field_name . "_box .remove_file').click(function(){
						$('#" . $field_name . "_box input[name=\"" . $field_name . "_hidden\"]').val('');
						$('#" . $field_name . "_box .file_set').addClass('js_hide');
				   });
			");
		}

		$filedir = $allowed_file_dirs;
		$specified_directory = $allowed_file_dirs;

		if (version_compare(APP_VER, '2.2.0', '<')) {
			$upload_directory_info = $this->EE->db->query("SELECT * FROM exp_upload_prefs WHERE id='$filedir'");
		} else {
			$this->EE->load->model('file_upload_preferences_model');
			$upload_directory_info = $this->EE->file_upload_preferences_model->get_upload_preferences(1, $filedir);
		}

		$upload_directory_server_path = $upload_directory_info->row('server_path');
		$upload_directory_url = $upload_directory_info->row('url');

		// let's look for a thumb

		if (version_compare(APP_VER, '2.2.0', '<')) {
			if (file_exists($upload_directory_server_path . '_thumbs/thumb_' . $filename)) {
				$thumb = '<img src="' . $upload_directory_url . '_thumbs/thumb_' . $filename . '" />';
			} else {
				$thumb = '<img src="' . PATH_CP_GBL_IMG . 'default.png" alt="default thumbnail" />';
			}
		} else {
			$this->EE->load->library('filemanager');
			$this->EE->load->helper('html');
			$thumb_info = $this->EE->filemanager->get_thumb($filename, $filedir);
			$thumb = img(array(
				'src' => $thumb_info['thumb'],
				'alt' => $filename
					));
		}

		$hidden = form_hidden($field_name . '_hidden', $filename);
		$hidden .= form_hidden($field_name . '_hidden_dir', $filedir);
		$upload = form_upload(array(
			'name' => $field_name,
			'value' => $filename,
			'data-content-type' => $content_type,
			'data-directory' => $specified_directory
				));
		$dropdown = form_dropdown($field_name . '_directory', array($filedir), $filedir);

		$upload_link = '<a href="#" class="choose_file" data-directory="' . $specified_directory . '">' . $this->EE->lang->line('add_file') . '</a>';

		$newf = $upload_link;
		$remf = '<a href="#" class="remove_file">' . $this->EE->lang->line('remove_file') . '</a>';

		$set_class = $filename ? '' : 'js_hide';

		$r = '<div id="' . $field_name . '_box">';
		$r .= '<div class="file_set ' . $set_class . '">';
		$r .= "<p class='filename'>$thumb<br />$filename</p>";
		$r .= "<p class='sub_filename'>$remf</p>";
		$r .= "<p>$hidden</p>";
		$r .= '</div>';

		$r .= '<div class="no_file js_hide">';
		$r .= "<p class='sub_filename'>$upload</p>";
		$r .= "<p>$dropdown</p>";
		$r .= '</div>';

		$r .= '<div class="modifiers js_show">';
		$r .= "<p class='sub_filename'>$newf</p>";
		$r .= '</div>';
		$r .= '</div>';

		return $r;
	}

	/**
	 * Tool: parse template data
	 *  
	 * @access public
	 */
	function _view($template, $vars, $flag, $title, $rNav = FALSE) {
		//update jquery and jquery-ui directly in html header (EE 2.1.X)

		/* 	

		  if (version_compare(APP_VER, '2.2.0', '<'))
		  {
		  ob_start();

		  function show_cp_template()
		  {
		  $EE = &get_instance();

		  $html = ob_get_contents();
		  ob_end_clean();

		  //update jquery from 1.4.1 to 1.7 (EE)

		  $html = str_replace(
		  BASE.AMP.'C=javascript'.AMP.'v=',
		  'http://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js" rel="'.BASE.AMP.'C=javascript'.AMP.'v=',
		  $html
		  );

		  //update jquery-ui stylesheet from 1.7.2 ti 1.8 (EE 2.1.X)

		  $jqueryUiCssPattern = $EE->config->item('theme_folder_url').'jquery_ui/'.$EE->config->item('cp_theme').'/jquery-ui-1.7.2.custom.css';

		  $html = str_replace(
		  $jqueryUiCssPattern,
		  'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/ui-lightness/jquery-ui.css" rel="'.$jqueryUiCssPattern,
		  $html
		  );

		  //jquery_ui/default/jquery-ui-1.7.2.custom.css

		  echo $html;
		  }

		  register_shutdown_function('show_cp_template');
		  }

		  //update jquery and jquery-ui directly in html header (EE 2.2.X)

		  if (version_compare(APP_VER, '2.2.0', '>=') && version_compare(APP_VER, '2.3.0', '<'))
		  {
		  ob_start();

		  function show_cp_template()
		  {
		  $EE = &get_instance();

		  $html = ob_get_contents();
		  ob_end_clean();

		  //update jquery-ui stylesheet from 1.7.2 ti 1.8 (EE 2.2.X)

		  $jqueryUiCssPattern = $EE->config->item('theme_folder_url').'cp_themes/'.$EE->config->item('cp_theme').'/css/jquery-ui-1.7.2.custom.css';

		  $html = str_replace(
		  $jqueryUiCssPattern,
		  'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/ui-lightness/jquery-ui.css" rel="'.$jqueryUiCssPattern,
		  $html
		  );

		  echo $html;
		  }

		  register_shutdown_function('show_cp_template');
		  }

		 */

		//add must useful variables

		$vars['BASE'] = $this->moduleBase; //(with aperstand)
		$vars['SOFT_BASE'] = str_replace(AMP, '&', $this->moduleBase); //(without aperstand, e in javascript links)
		$vars['TITLE'] = $title;
		$vars['MEMBER_ID'] = $this->memberId;

		$this->EE->cp->set_variable('cp_page_title', $title);

		//generate right Nav

		$rightNav = array();

		if ($rNav) {
			if ((int) $this->groupId === 1) {
				$rightNav["right_nav_button_settings"] = $this->moduleBase . AMP . 'method=settings';
			}
		}

		//messages

		if (isset($_SESSION["promotions"]["flash_message"])) {
			$vars['message'] = $_SESSION["promotions"]["flash_message"];
			unset($_SESSION["promotions"]["flash_message"]);
		}

		if (isset($_SESSION["promotions"]["flash_error"])) {
			$vars['alert'] = $_SESSION["promotions"]["flash_error"];
			unset($_SESSION["promotions"]["flash_error"]);
		}

		$this->EE->cp->set_right_nav($rightNav);

		//scripts

		$this->_insert_js("var moduleBase = '" . $vars['SOFT_BASE'] . "'");

		//styles

		$this->_include_theme_css('promotions.css');
		$this->_include_theme_css('validate.css');

		return $this->EE->load->view($template, $vars, $flag);
	}

	/**
	 * Private: load upload preferences
	 *  
	 * @access public
	 */
	function _get_general_settings($force = FALSE) {
		if (!$force && isset($this->generalSettings)) {
			return $this->generalSettings;
		}

		$settings = $this->EE->db->select("*")
						->from("exp_promotions_settings")
						->where("site_id", $this->siteId)
						->where("var_param IN ('image_upload_dir', 'live_look_template')")
						->get()->result_array();

		foreach ($settings as $row) {
			$settings[$row["var_param"]] = $row["var_value"];
		}

		if (!isset($settings['image_upload_dir'])) {
			$settings['image_upload_dir'] = 0;
		}

		if (!isset($settings['live_look_template'])) {
			$settings['live_look_template'] = 0;
		}

		$this->generalSettings = $settings;

		return $this->generalSettings;
	}

	/*	 * ********************************** CP FUNCTIONS LIST ***************************************** */

	/**
	 * Homepage - redirect to campaigns
	 *  
	 * @access public
	 */
	function index() {
		//no index, just follow the compaigns

		$this->EE->functions->redirect($this->moduleBase . AMP . 'method=campaigns');
	}

	/**
	 * Campaign list
	 *  
	 * @access public
	 */
	function campaigns() {
		$this->EE->cp->set_variable('cp_breadcrumbs', array(
			$this->moduleBase => lang('promotions_module_name'),
		));

		//mass action

		if (isset($_POST['mass_action']) && isset($_POST['campaign']) && $_POST['mass_action']) {
			list($action_name, $action_value) = explode('_', trim($_POST['mass_action']), 2);

			switch ($action_name) {
				case 'paused':
					foreach ($_POST['campaign'] as $mass_action_campaign_id) {
						$this->EE->db->update('exp_promotions_campaign_entries', array('paused' => (int) $action_value), 'campaign_id = ' . (int) $mass_action_campaign_id);
					}
					$_SESSION["promotions"]["flash_message"] = lang('message_operation_success');
					break;
			}
		}

		//default filter values

		if (isset($_GET['reset']))
			$_SESSION['campaigns']['filter'] = array();

		if (!isset($_SESSION['campaigns']['filter']['keyword']))
			$_SESSION['campaigns']['filter']['keyword'] = '';
		if (!isset($_SESSION['campaigns']['filter']['status']))
			$_SESSION['campaigns']['filter']['status'] = '';
		if (!isset($_SESSION['campaigns']['filter']['limit']))
			$_SESSION['campaigns']['filter']['limit'] = '50';
		//if (!isset($_SESSION['campaigns']['filter']['site_id']))		$_SESSION['campaigns']['filter']['site_id'] = $this->EE->config->item('site_id');	
		if (!isset($_SESSION['campaigns']['filter']['start_date']))
			$_SESSION['campaigns']['filter']['start_date'] = '0--';

		if (isset($_POST['keyword'])) {
			if ($_POST['keyword'] != $this->EE->lang->line('campaigns_filter_keyword')) {
				$_SESSION['campaigns']['filter']['keyword'] = $_POST['keyword'];
			} else {
				$_SESSION['campaigns']['filter']['keyword'] = '';
			}
		}

		if (isset($_POST['limit']))
			$_SESSION['campaigns']['filter']['limit'] = $_POST['limit'];
		if (isset($_POST['limit']))
			$_SESSION['campaigns']['filter']['limit'] = $_POST['limit'];
		if (isset($_POST['status']))
			$_SESSION['campaigns']['filter']['status'] = $_POST['status'];
		//if (isset($_POST['site_id']))		$_SESSION['campaigns']['filter']['site_id'] = $_POST['site_id'];
		if (isset($_POST['start_date']))
			$_SESSION['campaigns']['filter']['start_date'] = $_POST['start_date'];


		$_SESSION['campaigns']['filter']['offset'] = (int) @$_GET['page'];

		//redirect fix (if you click to back button, browser show dummy warning)

		if (isset($_GET['reset']) OR isset($_POST['keyword'])) {
			$this->EE->functions->redirect($this->moduleBase . AMP . 'method=campaigns');
		}

		$vars['filter'] = $_SESSION['campaigns']['filter'];

		//load campaign status

		$vars['statuses'] = array(
			'' => lang('campaigns_filter_status_all'),
			'scheduled' => lang('campaigns_filter_status_scheduled'),
			'active' => lang('campaigns_filter_status_active'),
			'paused' => lang('campaigns_filter_status_paused'),
			'ended' => lang('campaigns_filter_status_ended'),
			'winners-announced' => lang('campaigns_filter_status_winners_announced'),
			'draft' => lang('campaigns_filter_status_draft'),
		);

		//load date intervals

		$vars['dates']['0--'] = lang('campaigns_filter_date_all');
		$vars['dates']['1-' . strtotime(date('Y-m-d 00:00:00')) . '-' . strtotime(date('Y-m-d 00:00:00') . ' + 1 day')] = lang('campaigns_filter_date_today');
		$vars['dates']['2-' . strtotime(date('Y-m-d 00:00:00') . ' - 1 day') . '-' . strtotime(date('Y-m-d 00:00:00'))] = lang('campaigns_filter_date_yesterday');
		$vars['dates']['3-' . strtotime(date('Y-m-01 00:00:00')) . '-' . strtotime(date('Y-m-d 00:00:00') . ' + 1 month')] = lang('campaigns_filter_date_this_month');
		$vars['dates']['4-' . strtotime(date('Y-m-01 00:00:00') . ' - 1 month') . '-' . strtotime(date('Y-m-01 00:00:00'))] = lang('campaigns_filter_date_last_month');
		$vars['dates']['5-' . strtotime(date('Y-01-01 00:00:00')) . '-' . strtotime(date('Y-01-01 00:00:00') . ' + 1 year')] = lang('campaigns_filter_date_this_year');
		$vars['dates']['6-' . strtotime(date('Y-01-01 00:00:00') . ' - 1 year') . '-' . strtotime(date('Y-01-01 00:00:00'))] = lang('campaigns_filter_date_last_year');
		//limit intervals

		$vars['limits']['50'] = '50 ' . $this->EE->lang->line('campaigns_filter_per_page');
		$vars['limits']['100'] = '100 ' . $this->EE->lang->line('campaigns_filter_per_page');
		$vars['limits']['250'] = '250 ' . $this->EE->lang->line('campaigns_filter_per_page');
		$vars['limits']['500'] = '500 ' . $this->EE->lang->line('campaigns_filter_per_page');

		//sites

		/*
		  $vars['sites'] = array();

		  if ($this->EE->config->item('multiple_sites_enabled') == 'y')
		  {
		  $vars['sites'] = array("" => lang('campaigns_filter_sites_all'));
		  foreach ($this->EE->session->userdata('assigned_sites') as $site_id=>$site_name)
		  {
		  $vars['sites'][$site_id] = $site_name;
		  }
		  }
		 */

		//create query

		function _add_where_conditions($dbHandler, $filter, $siteId) {
			$EE = &get_instance();

			//filter by date

			$filterDate = explode('-', $filter['start_date']);

			if ($filterDate[1] && $filterDate[2]) {
				$dbHandler->where('exp_promotions_campaign_entries.start_date < "' . (int) $filterDate[2] . '"');
				$dbHandler->where('exp_promotions_campaign_entries.end_date > "' . (int) $filterDate[1] . '"');
				$dbHandler->where('exp_promotions_campaign_entries.start_date < exp_promotions_campaign_entries.end_date');
			}

			//filter by keyword

			if ($filter['keyword']) {
				$dbHandler->where("(
					exp_promotions_campaign_entries.head_note LIKE '%" . $filter['keyword'] . "%' OR 
					exp_promotions_campaign_entries.foot_note LIKE '%" . $filter['keyword'] . "%' OR 
					exp_promotions_campaign_entries.campaign_title LIKE '%" . $filter['keyword'] . "%' OR 
					exp_promotions_campaign_entries.terms LIKE '%" . $filter['keyword'] . "%' OR 
					exp_promotions_campaign_entries.head_title LIKE '%" . $filter['keyword'] . "%'
				)");
			}

			//filter status

			if ($filter['status'] == 'draft') {
				$dbHandler->where('exp_promotions_campaign_entries.draft', 1);
			} else {
				$dbHandler->where('exp_promotions_campaign_entries.draft', 0);
			}

			if ($filter['status'] == 'winners_announced') {
				$dbHandler->where('exp_promotions_campaign_entries.winners_announced', 1);
			}

			if ($filter['status'] == 'paused') {
				$dbHandler->where('exp_promotions_campaign_entries.paused', 1);
			}

			if ($filter['status'] == 'active') {
				$dbHandler->where('exp_promotions_campaign_entries.paused', 0);
				$dbHandler->where('exp_promotions_campaign_entries.start_date < ' . $EE->localize->now);
				$dbHandler->where('exp_promotions_campaign_entries.end_date > ' . $EE->localize->now);
			}

			if ($filter['status'] == 'scheduled') {
				$dbHandler->where('exp_promotions_campaign_entries.paused', 0);
				$dbHandler->where('exp_promotions_campaign_entries.start_date > ' . $EE->localize->now);
			}

			if ($filter['status'] == 'ended') {
				$dbHandler->where('exp_promotions_campaign_entries.paused', 0);
				$dbHandler->where('exp_promotions_campaign_entries.end_date < ' . $EE->localize->now);
			}

			if ($filter['status'] == 'winners-announced') {
				$dbHandler->where('exp_promotions_campaign_entries.winners_announced', 1);
			}

			/*
			  if ($filter['site_id'])
			  {
			  $dbHandler->where('exp_promotions_campaign_entries.site_id = '.(int)$filter['site_id']);
			  }
			 */

			$dbHandler->where('exp_promotions_campaign_entries.site_id = ' . (int) $siteId);

			return $dbHandler;
		}

		//get total count (for pagination)

		$dbHandler = $this->EE->db->select('count(*) as cnt')->from('exp_promotions_campaign_entries');
		$vars['total_count'] = _add_where_conditions($this->EE->db, $vars['filter'], $this->siteId)
				->get()
				->row('cnt');

		//get entries (for display)

		$dbHandler = $this->EE->db->select('*')
				->from('exp_promotions_campaign_entries');

		$vars['entries'] = _add_where_conditions($this->EE->db, $vars['filter'], $this->siteId)
				->order_by('exp_promotions_campaign_entries.start_date', 'desc')
				->order_by('exp_promotions_campaign_entries.entry_date', 'desc') //newcampaigns is top
				->limit($vars['filter']['limit'])
				->offset($vars['filter']['offset'])
				->get()
				->result_array();

		//pagination

		$configPager['base_url'] = $this->moduleBase . AMP . 'method=' . __FUNCTION__;
		$configPager['total_rows'] = $vars['total_count'];
		$configPager['per_page'] = $vars['filter']['limit'];
		$configPager['page_query_string'] = TRUE;
		$configPager['query_string_segment'] = 'page';

		//campaign addon types

		$this->EE->load->library('Campaigns');
		$vars['campaign_addon'] = array();

		foreach ($this->EE->campaigns->get_avaiable_campaigns() as $campaign_type) {
			$vars['campaign_addon'][$campaign_type["name"]] = $campaign_type["title"];
		}

		//live look template

		$this->_get_general_settings();
		$template_id = $this->generalSettings['live_look_template'];
		$vars['live_look_url'] = '';
		if ($template_id) {
			$this->EE->load->model('template_model');
			$tempate_info = $this->EE->template_model->get_templates($this->siteId, array('is_site_default'), array('template_id' => $template_id))->row_array();
			if (!empty($tempate_info)) {
				if ($tempate_info['is_site_default'] == 'y') {
					if ($tempate_info['template_name'] == 'index') {
						$vars['live_look_url'] = $this->EE->functions->create_url('/');
					} else {
						$vars['live_look_url'] = $this->EE->functions->create_url($tempate_info['template_name']);
					}
				} else {
					if ($tempate_info['template_name'] == 'index') {
						$vars['live_look_url'] = $this->EE->functions->create_url($tempate_info['group_name']);
					} else {
						$vars['live_look_url'] = $this->EE->functions->create_url($tempate_info['group_name'] . '/' . $tempate_info['template_name']);
					}
				}
			}
			$vars['live_look_url'] = rtrim($vars['live_look_url'], '/') . '/';
		}


		//$this->paginationReset();

		$this->EE->pagination->initialize($configPager);
		$vars['pagination'] = $this->EE->pagination->create_links();

		$this->_include_theme_js('campaigns.js');
		return $this->_view('campaigns/index', $vars, TRUE, lang('title_campaigns'), TRUE);
	}

	function campaignsDelete() {
		$campaign_id = $_GET['campaign_id'];

		$this->EE->db->query("DELETE FROM exp_promotions_campaign_data WHERE campaign_id='$campaign_id'");
		$this->EE->db->query("DELETE FROM exp_promotions_campaign_entries WHERE campaign_id='$campaign_id'");
		$this->EE->db->query("DELETE FROM exp_promotions_campaign_fields WHERE campaign_id='$campaign_id'");
		$this->EE->db->query("DELETE FROM exp_promotions_campaign_lists WHERE campaign_id='$campaign_id'");

		$_SESSION["promotions"]["flash_message"] = lang('message_operation_success');
		$this->EE->functions->redirect($this->moduleBase . AMP . 'method=campaigns');
		exit;
	}

	/**
	 * Settings: homepage
	 *  
	 * @access public
	 */
	function settings() {
		$this->EE->cp->set_variable('cp_breadcrumbs', array(
			$this->moduleBase => lang('promotions_module_name'),
		));

		return $this->_view('settings/index', array(), TRUE, lang('title_settings'), FALSE);
	}

	/**
	 * Settings: custom fields: show fields
	 *  
	 * @access public
	 */
	function settingsCustomFields() {
		$this->EE->cp->set_variable('cp_breadcrumbs', array(
			$this->moduleBase => lang('promotions_module_name'),
			$this->moduleBase . AMP . 'method=settings' => lang('title_settings'),
		));

		$this->_include_theme_js('jquery.ui.sortable.js');
		$this->_include_theme_js('settings_custom_fields.js');

		$vars['fields'] = $this->EE->db->select('*')
				->from('exp_promotions_custom_fields')
				->where('site_id = ' . $this->siteId)
				->order_by('sort', 'asc')
				->get()
				->result_array();

		return $this->_view('settings/custom_fields', $vars, TRUE, lang('title_settings_custom_fields'), FALSE);
	}

	/**
	 * Settings: reorder entries
	 *  
	 * @access public
	 */
	function settingsCustomFieldsSort() {
		$sortArray = explode('|', trim(@$_GET['sortString'], '| '));

		foreach ($sortArray as $sort => $field_id) {
			$this->EE->db->update('exp_promotions_custom_fields', array('sort' => (int) $sort), 'field_id = ' . (int) $field_id);
		}

		echo json_encode(array(
			'status' => 'success',
			'message' => lang('message_reeorder_success'),
		));

		exit;
	}

	/**
	 * Settings: new field
	 *  
	 * @access public
	 */
	function settingsCustomFieldsNew() {
		$this->EE->cp->set_variable('cp_breadcrumbs', array(
			$this->moduleBase => lang('promotions_module_name'),
			$this->moduleBase . AMP . 'method=settings' => lang('title_settings'),
			$this->moduleBase . AMP . 'method=settingsCustomFields' => lang('title_settings_custom_fields'),
		));

		$this->_include_theme_js('jquery.form.js');
		$this->_include_theme_js('jquery.elastic.js');
		$this->_include_theme_js('validate.js');

		return $this->_view('settings/custom_fields_form', array(), TRUE, lang('title_settings_custom_fields_new'));
	}

	/**
	 * Settings: new field submit
	 *  
	 * @access public
	 */
	function settingsCustomFieldsNewSubmit() {
		$status = "success";
		$fields = array();
		$message = '';

		//is field label filled?

		if (!trim($_POST['field_label'])) {
			$status = "error";
			$fields[] = "field_label";
			$message = lang("error_validate_marked_field_is_required");
		}

		//is field name filled?	

		if (!trim($_POST['field_name'])) {
			$status = "error";
			$fields[] = "field_name";
			$message = lang("error_validate_marked_field_is_required");
		} else {
			if (!preg_match('/^([a-z0-9\-_]+)$/ui', $_POST['field_name'])) {
				$status = "error";
				$fields[] = "field_name";
				$message = lang("error_validate_wrong_format");
			} else {
				//is name unique?

				$isUsed = (int) $this->EE->db->query("SELECT count(*) as cnt FROM exp_promotions_custom_fields WHERE field_name LIKE '" . trim($_POST['field_name']) . "'")->row()->cnt;

				if ($isUsed OR in_array($_POST['field_name'], array('campaign_id', 'site_id', 'member_id', 'ip_address', 'entry_date', 'valid', 'email', 'campaign_addon_data'))) {
					$status = "error";
					$fields[] = "field_name";
					$message = lang("error_validate_field_is_not_unique");
				}
			}
		}

		if ($status == "error") {
			echo json_encode(array(
				"result" => $status,
				"fields" => $fields,
				"message" => $message
			));
		} else {
			//create new field !!!

			$data = array(
				"site_id" => $this->siteId,
				"field_name" => trim($_POST['field_name']),
				"field_label" => trim($_POST['field_label']),
				"field_type" => trim($_POST['field_type']),
				"sort" => 0
			);

			$this->EE->db->insert('exp_promotions_custom_fields', $data);

			//set sort attribute

			$field_id = $this->EE->db->insert_id();
			$this->EE->db->update('exp_promotions_custom_fields', array('sort' => $field_id), 'field_id = ' . (int) $field_id);

			//alter table

			$this->EE->load->dbforge();
			$this->EE->dbforge->add_column('promotions_campaign_data', array('field_id_' . $field_id => array('type' => 'text', 'null' => TRUE)));

			$message = lang('message_operation_success');

			echo json_encode(array(
				"result" => "success",
				"redirect" => str_replace(AMP, '&', $this->moduleBase . AMP . 'method=settingsCustomFields'),
				"message" => $message
			));

			$_SESSION["promotions"]["flash_message"] = $message;
		}
		exit;
	}

	function settingsCustomFieldsEdit() {
		$this->EE->cp->set_variable('cp_breadcrumbs', array(
			$this->moduleBase => lang('promotions_module_name'),
			$this->moduleBase . AMP . 'method=settings' => lang('title_settings'),
			$this->moduleBase . AMP . 'method=settingsCustomFields' => lang('title_settings_custom_fields'),
		));

		$rQuery = $this->EE->db->query("SELECT * FROM exp_promotions_custom_fields WHERE site_id='" . $this->siteId . "' AND field_id='" . (int) @$_GET['field_id'] . "'");
		if (!$rQuery->num_rows) {
			return 'Field does not exists';
		}

		$vars['record'] = $rQuery->row_array();

		$this->_include_theme_js('jquery.form.js');
		$this->_include_theme_js('jquery.elastic.js');
		$this->_include_theme_js('validate.js');

		return $this->_view('settings/custom_fields_form', $vars, TRUE, lang('title_settings_custom_fields_edit'));
	}

	/**
	 * Settings: new field submit
	 *  
	 * @access public
	 */
	function settingsCustomFieldsEditSubmit() {
		$status = "success";
		$fields = array();
		$message = '';

		//is field label filled?

		if (!trim($_POST['field_label'])) {
			$status = "error";
			$fields[] = "field_label";
			$message = lang("error_validate_marked_field_is_required");
		}

		//is field name filled?	

		if (!trim($_POST['field_name'])) {
			$status = "error";
			$fields[] = "field_name";
			$message = lang("error_validate_marked_field_is_required");
		} else {
			if (!preg_match('/^([a-z0-9\-_]+)$/ui', $_POST['field_name'])) {
				$status = "error";
				$fields[] = "field_name";
				$message = lang("error_validate_wrong_format");
			} else {
				//is name unique?

				$isUsed = (int) $this->EE->db->query("SELECT count(*) as cnt FROM exp_promotions_custom_fields WHERE field_id!='" . (int) $_GET['field_id'] . "' AND field_name LIKE '" . trim($_POST['field_name']) . "'")->row()->cnt;
				if ($isUsed OR in_array($_POST['field_name'], array('campaign_id', 'site_id', 'entry_date', 'status', 'responsible_agent', 'campaign_priority', 'client_name', 'client_email', 'summary', 'issue', 'attachment'))) {
					$status = "error";
					$fields[] = "field_name";
					$message = lang("error_validate_field_is_not_unique");
				}
			}
		}

		if ($status == "error") {
			echo json_encode(array(
				"result" => $status,
				"fields" => $fields,
				"message" => $message
			));
		} else {
			//edit field

			$data = array(
				"field_name" => $_POST['field_name'],
				"field_label" => $_POST['field_label'],
				"field_type" => $_POST['field_type'],
			);

			//set sort attribute

			$this->EE->db->update('exp_promotions_custom_fields', $data, 'field_id = ' . (int) $_GET['field_id']);
			$message = lang('message_operation_success');

			echo json_encode(array(
				"result" => "success",
				"redirect" => str_replace(AMP, '&', $this->moduleBase . AMP . 'method=settingsCustomFields'),
				"message" => $message
			));

			$_SESSION["promotions"]["flash_message"] = $message;
		}
		exit;
	}

	/**
	 * Settings: remove field
	 *  
	 * @access public
	 */
	function settingsCustomFieldsDel() {
		$field_id = (int) $_GET['field_id'];

		$this->EE->db->delete('exp_promotions_custom_fields', 'field_id = ' . $field_id);

		$this->EE->load->dbforge();
		$this->EE->dbforge->drop_column('promotions_campaign_data', 'field_id_' . $field_id);

		$_SESSION["promotions"]["flash_message"] = lang('message_operation_success');

		$this->EE->functions->redirect($this->moduleBase . AMP . 'method=settingsCustomFields');
	}

	/**
	 * Settings: upload preferences settings		
	 *  
	 * @access public
	 */
	function settingsGeneral() {
		$this->_include_theme_js('jquery.form.js');
		$this->_include_theme_js('validate.js');

		//get fileupload preferences

		$directory_options = array();
		$directory_options[0] = lang('-');

		if (version_compare(APP_VER, '2.2.0', '<')) {
			$dirs = $this->EE->db->query("SELECT * FROM exp_upload_prefs");
		} else {
			$this->EE->load->model('file_upload_preferences_model');
			$dirs = $this->EE->file_upload_preferences_model->get_upload_preferences(1);
		}

		foreach ($dirs->result_array() as $dir) {
			$directory_options[$dir['id']] = $dir['name'];
		}

		//get templates

		$this->EE->load->model('template_model');

		$templates_options = array();
		$templates_options[0] = lang('-');

		foreach ($this->EE->template_model->get_template_groups()->result_array() as $group) {
			$template_items = array();

			foreach ($this->EE->template_model->get_templates($this->siteId, array(), array('exp_template_groups.group_id' => (int) $group['group_id']))->result_array() as $template)
				$template_items[$template['template_id']] = $template['template_name'];

			$templates_options[$template['group_name']] = $template_items;
		}

		$vars["templates_options"] = $templates_options;

		$vars["record"] = $this->_get_general_settings();
		$vars['directory_options'] = $directory_options;

		$this->EE->cp->set_variable('cp_breadcrumbs', array(
			$this->moduleBase => lang('promotions_module_name'),
			$this->moduleBase . AMP . 'method=settings' => lang('title_settings'),
		));

		return $this->_view('settings/general', $vars, TRUE, lang('title_settings_general'), FALSE);
	}

	/**
	 * Settings: upload preferences submit process
	 *  
	 * @access public
	 */
	function settingsGeneralSubmit() {
		foreach ($_POST as $k => $v)
			if (!is_array($_POST[$k]))
				$_POST[$k] = trim($_POST[$k]);

		$status = "success";
		$message = lang('message_operation_success');

		//space for future formulas
		//validation formulas	

		if ($status == "error") {
			echo json_encode(array(
				"result" => $status,
				"fields" => $fields,
				"message" => $message
			));
		} else {
			$data = array(
				'image_upload_dir' => (int) $_POST['image_upload_dir'],
				'live_look_template' => (int) $_POST['live_look_template'],
			);

			foreach ($data as $k => $v) {
				$this->EE->db->query("REPLACE INTO exp_promotions_settings (site_id, var_param, var_value) VALUES ('" . $this->siteId . "', '" . addslashes($k) . "', '" . addslashes($v) . "')");
			}

			echo json_encode(array(
				"result" => "success",
				"redirect" => str_replace(AMP, '&', $this->moduleBase . AMP . 'method=settings'),
				"message" => $message
			));
			$_SESSION["promotions"]["flash_message"] = $message;
		}
		exit;
	}

	/**
	 * Campaigns: new campaign
	 *  
	 * @access public
	 */
	function campaignsOverview() {
		$this->EE->load->library('Campaigns');

		$campaign_types = array();
		$campaign_types[""] = "";

		foreach ($this->EE->campaigns->get_avaiable_campaigns() as $campaign_type) {
			$campaign_types[$campaign_type["name"]] = $campaign_type["title"];
		}

		$this->EE->cp->set_variable('cp_breadcrumbs', array(
			$this->moduleBase => lang('promotions_module_name'),
			$this->moduleBase . AMP . 'method=campaigns' => lang('title_campaigns'),
		));

		$vars['fields'] = $this->EE->db->select('*')
				->from('exp_promotions_custom_fields')
				->where('site_id = ' . $this->siteId)
				->order_by('sort', 'asc')
				->get()
				->result_array();

		$vars['campaign_types'] = $campaign_types;

		//update? 

		if (@$_GET['campaign_id']) {
			$vars['record'] = $this->EE->db->query("SELECT * FROM exp_promotions_campaign_entries WHERE site_id='" . $this->siteId . "' AND campaign_id='" . (int) $_GET['campaign_id'] . "'")->row_array();
			$vars['record']['start_date'] = $this->EE->localize->set_human_time($vars['record']['start_date']);
			$vars['record']['end_date'] = $this->EE->localize->set_human_time($vars['record']['end_date']);
		}

		//styles & scripts	

		$this->EE->jquery->ui(BASE . AMP . 'C=javascript' . AMP . 'M=load' . AMP . 'ui=datepicker', TRUE);
		$this->EE->javascript->compile();

		$this->EE->javascript->output('
			date_obj = new Date();
			date_obj_hours = date_obj.getHours();
			date_obj_mins = date_obj.getMinutes();

			if (date_obj_mins < 10) { date_obj_mins = "0" + date_obj_mins; }

			if (date_obj_hours > 11) {
				date_obj_hours = date_obj_hours - 12;
				date_obj_am_pm = " PM";
			} else {
				date_obj_am_pm = " AM";
			}

			date_obj_time = " \'"+date_obj_hours+":"+date_obj_mins+date_obj_am_pm+"\'";			
		');

		$this->EE->javascript->output('
			$("#start_date").datepicker({
				dateFormat: $.datepicker.W3C  + date_obj_time, 
				defaultDate: new Date(' . ($this->EE->localize->set_localized_time() * 1000) . ')
			});
			$("#end_date").datepicker({
				dateFormat: $.datepicker.W3C  + date_obj_time, 
				defaultDate: new Date(' . ($this->EE->localize->set_localized_time() * 1000) . ')
			});			
		');

		$this->_include_theme_js('live_url_title.js');
		$this->_include_theme_js('jquery.form.js');
		$this->_include_theme_js('jquery.elastic.js');
		$this->_include_theme_js('validate.js');
		$this->_include_theme_css('validate.css');

		return $this->_view('campaigns/overview', $vars, TRUE, lang('title_campaign_overview'), FALSE);
	}

	/**
	 * Campaigns: campaign overview submit process
	 *  
	 * @access public
	 */
	function campaignsOverviewSubmit() {
		$status = "success";
		$fields = array();
		$message = '';

		$this->_clean_input($_POST);

		//all files are required

		if (!trim($_POST['campaign_addon'])) {
			$status = "error";
			$fields[] = "campaign_addon";
			$message = lang("error_validate_marked_field_is_required");
		}

		if (!trim($_POST['campaign_title'])) {
			$status = "error";
			$fields[] = "campaign_title";
			$message = lang("error_validate_marked_field_is_required");
		}

		//is field name filled?	

		if (!trim($_POST['campaign_url_title'])) {
			$status = "error";
			$fields[] = "campaign_url_title";
			$message = lang("error_validate_marked_field_is_required");
		} else {
			if (!preg_match('/^([a-z0-9\-_]+)$/ui', $_POST['campaign_url_title'])) {
				$status = "error";
				$fields[] = "campaign_url_title";
				$message = lang("error_validate_wrong_format");
			} else {
				//is name unique?

				$isUsed = (int) $this->EE->db->query("SELECT count(*) as cnt FROM exp_promotions_campaign_entries WHERE campaign_id!='" . (int) $_POST['campaign_id'] . "' AND campaign_url_title LIKE '" . trim($_POST['campaign_url_title']) . "'")->row()->cnt;
				if ($isUsed) {
					$status = "error";
					$fields[] = "campaign_url_title";
					$message = lang("error_validate_field_is_not_unique");
				}
			}
		}

		if (!trim($_POST['start_date'])) {
			$status = "error";
			$fields[] = "start_date";
			$message = lang("error_validate_marked_field_is_required");
		}

		if (!trim($_POST['end_date'])) {
			$status = "error";
			$fields[] = "end_date";
			$message = lang("error_validate_marked_field_is_required");
		}

		if (trim($_POST['start_date']) && trim($_POST['end_date'])) {
			if ($_POST['end_date'] <= $_POST['start_date']) {
				$status = "error";
				$fields[] = "start_date";
				$fields[] = "end_date";
				$message = lang("error_validate_wrong_format");
			}
		}

		if ($status == "error") {
			echo json_encode(array(
				"result" => $status,
				"fields" => $fields,
				"message" => $message
			));
		} else {

			$_POST['start_date'] = $this->EE->localize->convert_human_date_to_gmt($_POST['start_date']);
			$_POST['end_date'] = $this->EE->localize->convert_human_date_to_gmt($_POST['end_date']);

			$data = array(
				"site_id" => $this->siteId,
				"start_date" => $_POST['start_date'],
				"end_date" => $_POST['end_date'],
				"campaign_title" => $_POST['campaign_title'],
				"campaign_url_title" => $_POST['campaign_url_title'],
				"campaign_addon" => $_POST['campaign_addon'],
				"author_id" => $this->memberId
			);

			if (!$_POST['campaign_id']) {
				$data["entry_date"] = $this->EE->localize->now;
				$data["draft"] = 1;

				$this->EE->db->insert('exp_promotions_campaign_entries', $data);
				$campaign_id = $this->EE->db->insert_id();
			} else {
				$this->EE->db->update('exp_promotions_campaign_entries', $data, 'campaign_id = ' . (int) $_POST['campaign_id']);
				$campaign_id = $_POST['campaign_id'];
			}

			//$message = lang('message_operation_success');
			//$_SESSION["promotions"]["flash_message"] = $message;

			echo json_encode(array(
				"result" => "success",
				"redirect" => str_replace(AMP, '&', $this->moduleBase . AMP . 'method=campaignsContent' . AMP . 'campaign_id=' . $campaign_id),
				"message" => $message
			));
		}

		exit;
	}

	/**
	 * Campaigns: campaign content
	 *  
	 * @access public
	 */
	function campaignsContent() {
		$this->EE->cp->set_variable('cp_breadcrumbs', array(
			$this->moduleBase => lang('promotions_module_name'),
			$this->moduleBase . AMP . 'method=campaigns' => lang('title_campaigns'),
		));

		$vars = array();
		$vars['record'] = $this->EE->db->query("SELECT * FROM exp_promotions_campaign_entries WHERE site_id='" . $this->siteId . "' AND campaign_id='" . (int) $_GET['campaign_id'] . "'")->row_array();
		$vars['record']['start_date'] = $this->EE->localize->set_human_time($vars['record']['start_date']);
		$vars['record']['end_date'] = $this->EE->localize->set_human_time($vars['record']['end_date']);

		//ee file upload field

		if ($upload_settings = $this->_get_general_settings()) {
			$allowed_file_dirs = $upload_settings['image_upload_dir'];
		} else {
			$allowed_file_dirs = 0;
		}

		$vars['image_upload_dir'] = $allowed_file_dirs;

		if (version_compare(APP_VER, '2.4.0', '<')) {

			$vars['image_upload_field'] = $this->_create_ee_upload_field(
					'image', //field_name
					$vars['record']['image'] ? $vars['record']['image'] : '', $allowed_file_dirs, 'image' //image or all
			);
		} else {
			$this->EE->load->library('file_field');
			$this->EE->file_field->browser();

			$vars['image_upload_field'] = $this->EE->file_field->field(
					'image', //field_name
					$vars['record']['image'] ? $this->EE->file_field->format_data($vars['record']['image'], $allowed_file_dirs) : '', $allowed_file_dirs, 'image' //image or all
			);
		}

		$this->_include_theme_js('jquery.form.js');
		$this->_include_theme_js('jquery.elastic.js');
		$this->_include_theme_js('validate.js');
		$this->_include_theme_css('validate.css');

		return $this->_view('campaigns/content', $vars, TRUE, lang('title_campaign_content'), FALSE);
	}

	/**
	 * Campaigns: campaign content submit process
	 *  
	 * @access public
	 */
	function campaignsContentSubmit() {
		$status = "success";
		$fields = array();
		$message = '';

		$this->_clean_input($_POST);

		//standard upload field

		/*
		  if (version_compare(APP_VER, '2.4.0', '<'))
		  {
		  if (isset($_FILES['image']['name']) && $_FILES['image']['name'])
		  {
		  $this->EE->load->library('filemanager');
		  $data = $this->EE->filemanager->upload_file($_POST['image_hidden_dir'], 'image');

		  if (@$data["error"])
		  {
		  echo json_encode(array(
		  "result" 	=> "error",
		  "fields" 	=> "image",
		  "message" 	=> strip_tags($data["error"])
		  ));
		  exit;
		  }
		  else
		  {
		  $_POST['image_hidden'] = $data["file_name"];
		  }
		  }
		  }
		 */

		//It's easy, none of fields is require...

		if ($status == "error") {
			echo json_encode(array(
				"result" => $status,
				"fields" => $fields,
				"message" => $message
			));
		} else {
			$data = array
				(
				"image" => @$_POST['image_hidden'],
				"head_title" => $_POST['head_title'],
				"head_note" => $_POST['head_note'],
				"foot_note" => $_POST['foot_note'],
				"terms" => $_POST['terms'],
			);

			$this->EE->db->update('exp_promotions_campaign_entries', $data, 'campaign_id = ' . (int) $_POST['campaign_id']);

			echo json_encode(array(
				"result" => "success",
				"redirect" => str_replace(AMP, '&', $this->moduleBase . AMP . 'method=campaignsAddon' . AMP . 'campaign_id=' . (int) $_POST['campaign_id']),
				"message" => $message
			));
		}

		exit;
	}

	/**
	 * Campaigns: campaign addon
	 *  
	 * @access public
	 */
	function campaignsAddon() {
		//Load Addon

		$vars['record'] = $this->EE->db->query("SELECT campaign_id, campaign_addon, campaign_addon_settings FROM exp_promotions_campaign_entries WHERE  site_id='" . $this->siteId . "' AND campaign_id='" . (int) $_GET['campaign_id'] . "'")->row_array();

		//Data protect

		if (empty($vars['record']))
			die('Campaign #' . (int) @$_GET['campaign_id'] . ' not exists.');

		$addon = $vars['record']['campaign_addon'];
		if (!$addon)
			die('Campaign addon failed');

		$this->EE->load->library('Campaigns');
		$this->EE->campaigns->load($addon);

		return $this->EE->campaigns->$addon->cp($this, (int) @$_GET['campaign_id'], $vars['record']['campaign_addon_settings']);
	}

	/**
	 * Campaigns: campaign addon submit process
	 *  
	 * @access public
	 */
	function campaignsAddonProcess() {
		$vars['record'] = $this->EE->db->query("SELECT campaign_id, campaign_addon FROM exp_promotions_campaign_entries WHERE campaign_id='" . (int) $_GET['campaign_id'] . "'")->row_array();

		//Data protect

		if (empty($vars['record']))
			die('Campaign #' . (int) @$_GET['campaign_id'] . ' not exists.');

		$addon = $vars['record']['campaign_addon'];
		if (!$addon)
			die('Campaign addon failed');

		$this->EE->load->library('Campaigns');
		$this->EE->campaigns->load($addon);

		return $this->EE->campaigns->$addon->cp_process($this, (int) @$_GET['campaign_id']);
	}

	/**
	 * Campaigns: campaign completition
	 *  
	 * @access public
	 */
	function campaignsCompletition() {
		$this->EE->cp->set_variable('cp_breadcrumbs', array(
			$this->moduleBase => lang('promotions_module_name'),
			$this->moduleBase . AMP . 'method=campaigns' => lang('title_campaigns'),
		));

		$vars = array();
		$vars['record'] = $this->EE->db->query("SELECT * FROM exp_promotions_campaign_entries WHERE site_id='" . $this->siteId . "' AND campaign_id='" . (int) $_GET['campaign_id'] . "'")->row_array();

		//get saved data	

		$vars['campaign_fields'] = array();
		$vars['campaign_fields_required'] = array();

		foreach ($this->EE->db->select('field_id, required')
				->from('exp_promotions_campaign_fields')
				->where('campaign_id', (int) $_GET['campaign_id'])
				->order_by('sort', 'asc')
				->get()
				->result_array() as $row) {

			$vars['campaign_fields'][$row['field_id']] = $row['field_id'];

			if ($row['required']) {
				$vars['campaign_fields_required'][$row['field_id']] = 1;
			}
		}

		//custom fields

		$vars['fields'] = array();

		foreach ($this->EE->db->select('*')
				->from('exp_promotions_custom_fields')
				->where('site_id = ' . $this->siteId)
				->order_by('sort', 'asc')
				->get()
				->result_array() as $row) {
			$vars['fields'][$row['field_id']] = $row;
		}

		//reeorder layout custom fields	

		foreach (array_reverse($vars['campaign_fields']) as $field_id) {
			if (isset($vars['fields'][$field_id])) {
				$new_array = array();
				$new_array[$field_id] = $vars['fields'][$field_id];

				unset($vars['fields'][$field_id]);

				foreach ($vars['fields'] as $k => $v) {
					$new_array[$k] = $v;
				}


				$vars['fields'] = $new_array;
			}
		}

		//get saved mailinglists data	

		$vars['lists'] = array();

		foreach ($this->EE->db->select('list_id')
				->from('exp_promotions_campaign_lists')
				->where('campaign_id', (int) $_GET['campaign_id'])
				->order_by('sort', 'asc')
				->get()
				->result_array() as $row) {

			$vars['lists'][$row['list_id']] = $row['list_id'];
		}

		//mailinglists

		$vars['mailing_lists'] = array();

		$mQyery = $this->EE->db->query("SELECT * FROM exp_modules WHERE module_name='Mailinglist'");
		if ($mQyery->num_rows) {
			$mailing_lists = $this->EE->db->query("SELECT list_id, list_name, list_title FROM exp_mailing_lists ORDER BY list_id ASC")->result_array(); // WHERE site_id='".$this->siteId."'"
			foreach ($mailing_lists as $m) {
				$vars['mailing_lists'][$m['list_id']] = $m['list_title'];
			}
		}

		//reeorder layout mailinglists fields	

		foreach (array_reverse($vars['lists']) as $list_id) {
			if (isset($vars['mailing_lists'][$list_id])) {
				$new_array = array();
				$new_array[$list_id] = $vars['mailing_lists'][$list_id];

				unset($vars['mailing_lists'][$list_id]);

				foreach ($vars['mailing_lists'] as $k => $v) {
					$new_array[$k] = $v;
				}

				$vars['mailing_lists'] = $new_array;
			}
		}



		$this->_include_theme_js('jquery.form.js');
		$this->_include_theme_js('validate.js');
		$this->_include_theme_js('campaign_completition.js');
		$this->_include_theme_css('validate.css');

		return $this->_view('campaigns/completition', $vars, TRUE, lang('title_campaign_completition'), FALSE);
	}

	/**
	 * Campaigns: campaign completition submit process
	 *  
	 * @access public
	 */
	function campaignsCompletitionSubmit() {
		$status = "success";
		$fields = array();
		$message = '';

		$this->_clean_input($_POST);

		//It's easy, none of fields is require...

		if ($status == "error") {
			echo json_encode(array(
				"result" => $status,
				"fields" => $fields,
				"message" => $message
			));
		} else {
			//sava DATA

			$data = array
				(
				"use_email" => $_POST['use_email'],
				"use_terms_of_service" => $_POST['use_terms_of_service'],
				"use_captcha" => $_POST['use_captcha'],
				"return_url" => $_POST['return_url'],
				"draft" => 0,
				"paused" => isset($_POST['paused']) ? 1 : 0,
				"winners_announced" => isset($_POST['winners_announced']) ? 1 : 0, "winners_announced_report" => isset($_POST['winners_announced']) ? $_POST['winners_announced_report'] : '',
			);
			$this->EE->db->update('exp_promotions_campaign_entries', $data, 'campaign_id = ' . (int) $_POST['campaign_id']);

			//save CUSTOM FIELD

			$sort = 0;
			$this->EE->db->update('exp_promotions_campaign_fields', array('sort' => -1), 'campaign_id = ' . (int) $_POST['campaign_id']);

			if (isset($_POST['custom_field']))
				foreach ($_POST['custom_field'] as $field_id => $value) {
					$sort++;

					$cf_data = array(
						'campaign_id' => (int) $_POST['campaign_id'],
						'field_id' => (int) $field_id,
						'required' => (int) @$_POST['custom_field_required'][$field_id],
						'sort' => (int) $sort,
						'site_id' => (int) $this->siteId,
					);

					$cnt = $this->EE->db->select('count(*) as cnt')->from('exp_promotions_campaign_fields')
									->where('campaign_id', $cf_data['campaign_id'])
									->where('field_id', $cf_data['field_id'])
									->get()->row('cnt');

					if (!$cnt) {
						$this->EE->db->insert('exp_promotions_campaign_fields', $cf_data);
					} else {
						$this->EE->db->update('exp_promotions_campaign_fields', $cf_data, 'campaign_id = ' . $cf_data['campaign_id'] . ' AND field_id = ' . $cf_data['field_id']);
					}
				}

			$this->EE->db->where('sort', '-1')->delete('exp_promotions_campaign_fields');

			//save MAILING LISTS

			$sort = 0;
			$this->EE->db->update('exp_promotions_campaign_lists', array('sort' => -1), 'campaign_id = ' . (int) $_POST['campaign_id']);

			if (isset($_POST['campaign_list']))
				foreach ($_POST['campaign_list'] as $list_id => $value) {
					$sort++;

					$cl_data = array(
						'campaign_id' => (int) $_POST['campaign_id'],
						'list_id' => (int) $list_id,
						'sort' => (int) $sort,
						'site_id' => (int) $this->siteId,
					);

					$cnt = $this->EE->db->select('count(*) as cnt')->from('exp_promotions_campaign_lists')
									->where('campaign_id', $cl_data['campaign_id'])
									->where('list_id', $cl_data['list_id'])
									->get()->row('cnt');

					if (!$cnt) {
						$this->EE->db->insert('exp_promotions_campaign_lists', $cl_data);
					} else {
						$this->EE->db->update('exp_promotions_campaign_lists', $cl_data, 'campaign_id = ' . $cl_data['campaign_id'] . ' AND list_id = ' . $cl_data['list_id']);
					}
				}

			$this->EE->db->where('sort', '-1')->delete('exp_promotions_campaign_lists');

			$message = lang('message_operation_success');
			$_SESSION["promotions"]["flash_message"] = $message;

			echo json_encode(array(
				"result" => "success",
				"redirect" => str_replace(AMP, '&', $this->moduleBase . AMP . 'method=campaigns'),
				"message" => $message
			));
		}

		exit;
	}

	/**
	 * Campaigns: campaign report data
	 *  
	 * @access public
	 */
	function campaignsReport() {
		//Load Addon

		$vars['record'] = $this->EE->db->query("SELECT * FROM exp_promotions_campaign_entries WHERE  site_id='" . $this->siteId . "' AND campaign_id='" . (int) $_GET['campaign_id'] . "'")->row_array();

		//Data protect

		if (empty($vars['record']))
			die('Campaign #' . (int) @$_GET['campaign_id'] . ' not exists.');

		$addon = $vars['record']['campaign_addon'];
		if (!$addon)
			die('Campaign addon failed');

		$this->EE->load->library('Campaigns');
		$this->EE->campaigns->load($addon);

		return $this->EE->campaigns->$addon->report($this, (int) @$_GET['campaign_id'], $vars['record']);
	}

}
