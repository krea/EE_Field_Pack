<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * ExpressionEngine - by KREA SK s.r.o.
 *
 * @package		ExpressionEngine
 * @author		Krea.com <support@krea.com>
 * @copyright		Copyright (c) 2012 Krea.com
 * @link		http://www.krea.com/helpdesk/documents
 * @since			Version 1.0.0
 */
class Promotions {
	/*
	 * Version
	 *  
	 * @access public
	 * @var string
	 */

	var $version = '0.5.0';
	var $cache = array(
		'includes' => array(),
	);
	var $breakedTags = array(
		'head_note',
		'foot_note',
		'terms'
	); //when displayed, "\n" convert to "<br />"

	function Promotions($site_id = false) {
		// before start
		if (session_id() == "")
			session_start();
		
		$this->EE = & get_instance();

		// Create addon_name from class name
		$this->addon_name = strtolower(__CLASS__);

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

		$this->EE->load->library('pagination');
		$this->EE->lang->loadfile('promotions');

		$this->globalSettings = $this->_load_settings();
	}

	/**
	 * Constructor
	 *
	 * @return void
	 */
	function __construct($site_id = false) {
		return $this->Promotions($site_id);
	}

	/**
	 * Private: fixed explode function
	 *  
	 * @access public
	 */
	function _params_explode($string) {
		$output = array();

		foreach (explode("|", $string) as $k => $v) {
			if (trim((string) $v) !== '') {
				$output[] = trim($v);
			}
		}

		return $output;
	}

	/**
	 * Private: load settings
	 *  
	 * @access public
	 */
	function _load_settings() {
		//one time

		global $_loaded_promotions_settings;
		if (isset($_loaded_promotions_settings)) {
			return $_loaded_promotions_settings;
		}

		$query = $this->EE->db->query("SELECT * FROM exp_promotions_settings")->result_array();

		if (empty($query)) {
			$this->EE->output->show_message(array('title' => $this->EE->lang->line('error_module_settings_failed_title'),
				'heading' => $this->EE->lang->line('error_module_settings_failed_title'),
				'content' => $this->EE->lang->line('error_module_settings_failed_body'),
			));
		} else {
			$loaded_promotions_settings = array();

			foreach ($query as $row) {
				$loaded_promotions_settings[$row['site_id']][$row['var_param']] = $row['var_value'];
			}
		}

		return $loaded_promotions_settings;
	}

	/**
	 * Private: load EE upload dirss
	 *  
	 * @access public
	 */
	function _fetch_upload_dirs_settings() {
		$this->EE->db->select('id, url');
		$query = $this->EE->db->from('upload_prefs')->get()->result_array();
		$dirs = array();
		foreach ($query as $dir) {
			$dirs[$dir['id']] = $dir['url'];
		}
		return $dirs;
	}

	/**
	 * Private: create pagination layout
	 *  
	 * @access public
	 */
	function _create_pagination(&$vars, $count, $limit, $offset) {
		$base = $this->EE->uri->uri_string;

		if (!empty($this->EE->uri->segments)) {
			preg_match("/P([1-9][0-9]*)/", end($this->EE->uri->segments), $match);

			if (isset($match[1]) && $match[1]) {
				$offset = (int) $match[1];
				$base = rtrim(preg_replace("/P([1-9][0-9]*)(.*)$/", '', $base), '/');
			}
		}

		$this->EE->load->library('pagination');

		$base = $this->EE->functions->create_url($base);

		$config['first_url'] = $base;
		$config['base_url'] = $base;
		$config['prefix'] = 'P';
		$config['total_rows'] = $count;
		$config['per_page'] = $limit;
		$config['cur_page'] = $offset;
		$config['current_page'] = $offset;

		if (version_compare(APP_VER, '2.3.0', '>')) {
			//EE 2.3.0

			$this->EE->pagination->initialize($config);
			$links = $this->EE->pagination->create_link_array();

			$total_pages = ceil($count / $limit);
			if ($total_pages == 1) {
				$vars['paginate'] = array();
				return;
			}

			$this->EE->pagination->initialize($config);
			$vars["paginate"] = array("0" => array(
					"current_page" => (1 + ceil($offset / $limit)),
					"total_pages" => $total_pages,
					"pagination_links" => $this->EE->pagination->create_links(),
					));

			if (!empty($links["next_page"][0])) {
				$vars["paginate"][0]["next_page"] = 1;
				$vars["paginate"][0]["next_page_url"] = @$links["next_page"][0]["pagination_url"];
			} else {
				$vars["paginate"][0]["next_page"] = 0;
				$vars["paginate"][0]["next_page_url"] = '';
			}

			if (!empty($links["first_page"][0])) {
				$vars["paginate"][0]["first_page"] = 1;
				$vars["paginate"][0]["first_page_url"] = @$links["first_page"][0]["pagination_url"];
			} else {
				$vars["paginate"][0]["first_page"] = 0;
				$vars["paginate"][0]["first_page_url"] = '';
			}

			if (!empty($links["previous_page"][0])) {
				$vars["paginate"][0]["previous_page"] = 1;
				$vars["paginate"][0]["previous_page_url"] = @$links["previous_page"][0]["pagination_url"];
			} else {
				$vars["paginate"][0]["previous_page"] = 0;
				$vars["paginate"][0]["previous_page_url"] = '';
			}

			if (!empty($links["last_page"][0])) {
				$vars["paginate"][0]["last_page"] = 1;
				$vars["paginate"][0]["last_page_url"] = @$links["last_page"][0]["pagination_url"];
			} else {
				$vars["paginate"][0]["last_page"] = 0;
				$vars["paginate"][0]["last_page_url"] = '';
			}
		} else {
			//EE 2.2.0

			$this->EE->pagination->initialize($config);

			$total_pages = ceil($count / $limit);

			if ($total_pages == 1) {
				$vars['paginate'] = array();
				return;
			}

			$this->EE->pagination->initialize($config);
			$vars["paginate"] = array("0" => array(
					"current_page" => (1 + ceil($offset / $limit)),
					"total_pages" => $total_pages,
					"pagination_links" => $this->EE->pagination->create_links(),
					));
		}
	}

	/**
	 * Tool: replace formated date variable
	 *  
	 * @access public
	 */
	function _replace_date_variable($tag, $value, $tagdata) {
		$localized_time = strtotime($this->EE->localize->set_human_time($value));

		//	$tagdata = '{start_date  format=\'123\'}';

		preg_match_all('#{' . $tag . '.([^}]*)(format="([^"]*)")}#', $tagdata, $matches);

		if (empty($matches[0])) {
			preg_match_all("#{" . $tag . ".([^}]*)(format='([^']*)')}#", $tagdata, $matches);
		}

		foreach ($matches[3] as $match_id => $format_string) {
			preg_match_all('/%(.)/', $format_string, $format_matches);

			foreach ($format_matches[0] as $format_match_id => $format_date) {
				$format_string = str_replace($format_date, date($format_matches[1][$format_match_id], $localized_time), $format_string);
			}

			$tagdata = str_replace($matches[0][$match_id], $format_string, $tagdata);
		}

		return $tagdata;
	}

	/**
	 * Tool: parse template
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
					if (in_array($tag, $this->breakedTags)) {
						$value = str_replace("\n", "<br />", $value); //break-lines
					}

					if (in_array($tag, array('entry_date', 'start_date', 'end_date'))) {
						$tagdata = $this->_replace_date_variable($tag, $value, $tagdata);
						$tagdata = str_replace('{' . $tag . '}', $value, $tagdata);
					} else {
						$tagdata = str_replace('{' . $tag . '}', $value, $tagdata);
					}
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

	/**
	 * Tool: detect custom fields to parse
	 *  
	 * @access public
	 */
	function _parse_custom_fields(&$vars) {
		$cf_query = $this->EE->db->query("SELECT required, field_name, field_label, field_type FROM exp_promotions_campaign_fields caf LEFT JOIN exp_promotions_custom_fields cuf ON cuf.field_id=caf.field_id WHERE caf.campaign_id='" . (int) $vars['campaign_id'] . "' ORDER BY caf.sort asc")->result_array();

		if (!empty($cf_query)) {
			$vars["custom_fields"] = $cf_query;
		} else {
			$vars["custom_fields"] = array();
		}
	}

	/**
	 * Tool: detect mailing_lists fields to parse
	 *  
	 * @access public
	 */
	function _parse_mailinglists(&$vars) {
		$vars["mailinglists"] = array();

		$mQyery = $this->EE->db->query("SELECT * FROM exp_modules WHERE module_name='Mailinglist'");
		if ($mQyery->num_rows) {
			$mailing_lists = $this->EE->db->query("SELECT ml.list_id, list_name, list_title 
				FROM exp_promotions_campaign_lists cl LEFT JOIN exp_mailing_lists ml 
				ON cl.list_id=ml.list_id 
				WHERE campaign_id='" . @$vars['campaign_id'] . "'
				ORDER BY cl.sort ASC
			")->result_array(); // WHERE site_id='".$this->siteId."'"
			foreach ($mailing_lists as $m) {
				$vars['mailinglists'][] = array(
					"list_id" => $m["list_id"],
					"list_title" => $m["list_title"],
				);
			}
		}
	}

	/**
	 * Tool: detect addon tags a call addon to parse
	 *  
	 * @access public
	 */
	function _parse_campaign_addon(&$vars) {
		$this->EE->load->library('Campaigns');

		//clean template data from all campaign addons

		$campaign_addons_names = array();
		$campaign_addons = $this->EE->campaigns->get_avaiable_campaigns();

		foreach ($campaign_addons as $campaign_addon) {
			$campaign_addons_names[] = $campaign_addon["name"];
			$vars[$campaign_addon["name"]] = array();
		}

		//load addon

		if (in_array($vars["campaign_addon"], $campaign_addons_names)) {
			$campaign_addon = $vars["campaign_addon"];
			$this->EE->campaigns->load($campaign_addon);
			$this->EE->campaigns->$campaign_addon->display($vars);
		}
	}

	/**
	 * Tool: trim input data
	 *  
	 * @access public
	 */
	function _clean_input(&$input) {
		if (is_array($input)) {
			foreach ($input as $k => $v)
				$this->_clean_input($input[$k]);
		} else {
			$input = trim($input);
		}
		return $input;
	}

	/**
	 * Tool: error form
	 *  
	 * @access public
	 */
	function _error_message($required_fields, $wrong_fields, $other_errors) {

		$message = "";

		if (!empty($required_fields)) {
			$message .= "<p>" . $this->EE->lang->line('error_message_required_fields') . ":</p>";
			$message .= "<ul>";

			foreach ($required_fields as $e) {
				$message .= "<li>" . $e . '</li>';
			}

			$message .= "</ul>";
		}

		if (!empty($wrong_fields)) {
			$message .= "<p>" . $this->EE->lang->line('error_message_invalid_fields') . ":</p>";
			$message .= "<ul>";

			foreach ($wrong_fields as $e) {
				$message .= "<li>" . $e . '</li>';
			}

			$message .= "</ul>";
		}

		if (!empty($other_errors)) {
			foreach ($other_errors as $e) {
				$message .= "<p>" . $e . '</p>';
			}
		}

		$this->EE->output->show_user_error('general', array($message));

		/*
		  $this->EE->output->show_message(
		  array(
		  'title'   	=> $this->EE->lang->line('error_message_header'),
		  'heading'	=> $this->EE->lang->line('error_message_header'),
		  'content'	=> $message,
		  'link'    	=> '<a href="javascript: history.go(-1)">'.$this->EE->lang->line('return').'</a>'
		  ),false
		  );
		 */
	}

	/**
	 * Tool: join email to mailinglists
	 *  
	 * @access public
	 */
	function _set_mailinglist($email, $list_id = 1) {
		$query = $this->EE->db->query(
				"SELECT DISTINCT 	list_id, list_title 
			 FROM 				exp_mailing_lists 
			 WHERE 				list_id = $list_id"
		);
		$row = $query->row();

		//if not already joined...

		$cnt = $this->EE->db->select('count(*) as cnt')->from('exp_mailing_list')->where('list_id', $list_id)->where('email', $this->EE->db->escape_str($email))->get()->row()->cnt;
		if ($cnt) {
			return true;
		}

		$code = $this->EE->functions->random('alpha', 10);
		$this->EE->db->query(
				"INSERT INTO exp_mailing_list_queue (email, list_id, authcode, date) 
			 VALUES ('" . $this->EE->db->escape_str($email) . "', '" .
				$this->EE->db->escape_str($list_id) . "', '" .
				$this->EE->db->escape_str($code) . "', '" . time() . "')"
		);

		//parse mail template

		$action_id = $this->EE->functions->insert_action_ids($this->EE->functions->fetch_action_id('Mailinglist', 'authorize_email'));

		$swap = array(
			'activation_url' => $this->EE->functions->fetch_site_index(0, 0) . '?ACT=' . $action_id . '&id=' . $code,
			'site_name' => stripslashes($this->EE->config->item('site_name')),
			'site_url' => $this->EE->config->item('site_url'),
			'mailing_list' => $row->list_title
		);

		foreach ($row as $key => $val) {
			$swap[$key] = $val;
		}

		$template = $this->EE->functions->fetch_email_template('mailinglist_activation_instructions');
		$email_tit = $this->EE->functions->var_swap($template['title'], $swap);
		$email_msg = $this->EE->functions->var_swap($template['data'], $swap);

		//send email

		$this->EE->load->library('email');
		$this->EE->email->initialize();
		$this->EE->email->wordwrap = true;
		$this->EE->email->mailtype = 'plain';
		$this->EE->email->priority = '3';
		$this->EE->email->from($this->EE->config->item('webmaster_email'), $this->EE->config->item('webmaster_name'));
		$this->EE->email->to($email);
		$this->EE->email->subject($email_tit);
		$this->EE->email->message($email_msg);
		$this->EE->email->Send();
		$this->EE->email->clear(TRUE);
	}

	/**
	 * Tool: universal pareser for campaigns module tag
	 *  
	 * @access public
	 */
	function _load_campaigns() {
		$this->uploadDirs = $this->_fetch_upload_dirs_settings();

		//---------------------------------------------------------
		//=========================================================
		//	Fetch Params
		//=========================================================		
		//---------------------------------------------------------		
		//** -----------------------
		//** dynamic = "on"; on,off
		//** -----------------------

		if (in_array($this->EE->TMPL->fetch_param("dynamic"), array('yes', 'y', 'on'))) {
			$param['dynamic'] = 'yes';
		} elseif (in_array($this->EE->TMPL->fetch_param("dynamic"), array('no', 'n', 'off'))) {
			$param['dynamic'] = 'no';
		} else {
			$param['dynamic'] = 'yes';
		}

		//** -----------------------
		//** orderby = "start_date" sort="asc"; campaign_id, start_date, end_date, title, random / asc,desc
		//** -----------------------

		$param['orderby'] = array();

		$order_by = $this->_params_explode($this->EE->TMPL->fetch_param("orderby"));
		$sort = $this->_params_explode($this->EE->TMPL->fetch_param("sort"));

		if (!empty($order_by)) {
			foreach ($order_by as $index => $order_value) {
				if (in_array($order_value, array('campaign_id', 'start_date', 'end_date', 'title'))) {
					$param['orderby'][] = array(
						$order_value,
						(@$sort[$index] == 'asc' OR @$sort[$index] == 'desc' ) ? $sort[$index] : 'asc'
					);
				}
			}
		} else {
			$param['orderby'] = array(
				array('end_date', 'desc')
			);
		}

		//** -----------------------
		//** limit = "10"; integer
		//** -----------------------

		if ((int) $this->EE->TMPL->fetch_param("limit") > 0) {
			$param['limit'] = (int) $this->EE->TMPL->fetch_param("limit");
		} else {
			$param['limit'] = '10';
		}

		//** -----------------------
		//** offset = "0"; integer
		//** -----------------------

		if ((int) $this->EE->TMPL->fetch_param("offset") > 0) {
			$param['offset'] = (int) $this->EE->TMPL->fetch_param("offset");
		} else {
			$param['offset'] = '0';
		}

		//** -----------------------
		//** type = ""; trivia, quiz...
		//** -----------------------		

		$type = $this->_params_explode($this->EE->TMPL->fetch_param("type"));

		if (!empty($type)) {
			$param['type'] = array();
			foreach ($type as $type_value) {
				$param['type'][] = trim($type_value);
			}
		} else {
			$param['type'] = array();
		}

		//** -----------------------
		//** campaign_id = ""; integer(s)
		//** -----------------------		

		$campaign_ids = $this->_params_explode($this->EE->TMPL->fetch_param("campaign_id"));

		if (!empty($campaign_ids)) {
			$param['campaign_id'] = array();
			foreach ($campaign_ids as $campaign_item) {
				$param['campaign_id'][] = (int) trim($campaign_item);
			}
		} else {
			$param['campaign_id'] = array();
		}

		//** -----------------------
		//** campaign_url_title = ""; string(s)
		//** -----------------------		

		$campaign_urls = $this->_params_explode($this->EE->TMPL->fetch_param("url_title"));

		if (!empty($campaign_urls)) {
			$param['campaign_url_title'] = array();
			foreach ($campaign_urls as $campaign_item) {
				$param['campaign_url_title'][] = trim($campaign_item);
			}
		} else {
			$param['campaign_url_title'] = array();
		}

		//** -----------------------
		//** not_campaign_url_title = ""; string(s)
		//** -----------------------		

		$not_campaign_urls = $this->_params_explode($this->EE->TMPL->fetch_param("not_url_title"));

		if (!empty($not_campaign_urls)) {
			$param['not_campaign_url_title'] = array();
			foreach ($not_campaign_urls as $not_campaign_item) {
				$param['not_campaign_url_title'][] = trim($not_campaign_item);
			}
		} else {
			$param['not_campaign_url_title'] = array();
		}


		//** -----------------------
		//** status = ""; active|scheduled|ended
		//** -----------------------			

		$param['status'] = array();

		$status = $this->_params_explode($this->EE->TMPL->fetch_param("status"));

		if (!empty($status)) {
			foreach ($status as $index => $status_value) {
				if (in_array($status_value, array('active', 'scheduled', 'ended'))) {
					$param['status'][] = $status_value;
				}
			}
		} else {
			$param['status'] = array();
		}

		//** -----------------------
		//** paused = ""; on,off
		//** -----------------------

		if (in_array($this->EE->TMPL->fetch_param("paused"), array('yes', 'y', 'on'))) {
			$param['paused'] = 'yes';
		} elseif (in_array($this->EE->TMPL->fetch_param("paused"), array('no', 'n', 'off'))) {
			$param['paused'] = 'no';
		} else {
			$param['paused'] = '';
		}

		//** -----------------------
		//** winners_announced = ""; on,off
		//** -----------------------

		if (in_array($this->EE->TMPL->fetch_param("winners_announced"), array('yes', 'y', 'on'))) {
			$param['winners_announced'] = 'yes';
		} elseif (in_array($this->EE->TMPL->fetch_param("winners_announced"), array('no', 'n', 'off'))) {
			$param['winners_announced'] = 'no';
		} else {
			$param['winners_announced'] = '';
		}

		//** -----------------------
		//** winners_announced = ""; on,off
		//** -----------------------

		if (in_array($this->EE->TMPL->fetch_param("paused"), array('yes', 'y', 'on'))) {
			$param['paused'] = 'yes';
		} elseif (in_array($this->EE->TMPL->fetch_param("paused"), array('no', 'n', 'off'))) {
			$param['paused'] = 'no';
		} else {
			$param['paused'] = '';
		}

		//** -----------------------
		//** site_id = ""; integer(s)
		//** -----------------------		

		$site_ids = $this->_params_explode($this->EE->TMPL->fetch_param("site_id"));

		if (!empty($site_ids)) {
			$param['site_id'] = array();
			foreach ($site_ids as $site_item) {
				$param['site_id'][] = (int) trim($site_item);
			}
		} else {
			$param['site_id'] = array($this->siteId);
		}

		//** -----------------------
		//** paginate = "bottom"; top|bottom|both
		//** -----------------------			

		$paginate = $this->EE->TMPL->fetch_param("paginate");

		if (in_array($paginate, array('top', 'bottom', 'both'))) {
			$param['paginate'] = $paginate;
		} else {
			$param['paginate'] = 'bottom';
		}

		//---------------------------------------------------------
		//=========================================================
		//	Create select (count)
		//=========================================================		
		//---------------------------------------------------------

		if (!function_exists('_add_where_conds')) {

			function _add_where_conds(&$handler, $param) {
				$ee = &get_instance();

				//draft

				$handler->where('draft', 0);

				//dynamic

				$segments = $ee->uri->segments;
				if ($param['dynamic'] && !empty($segments)) {
					//clean pagination segment from segments

					preg_match_all("/P([1-9][0-9]*)/", end($segments), $matches);
					if (isset($matches[1][0])) {
						foreach ($segments as $k => $v) {
							if ($segments[$k] == $matches[0][0])
								unset($segments[$k]);
						}
					}
				}

				if ($param['dynamic'] == 'yes' && !empty($segments)) {
					$dynamic_campaign_id = 0;
					$dynamic_campaign_url_title = '';

					//test: is segment_1 group_name?

					if ($ee->db->query("SELECT count(*) as cnt FROM exp_template_groups WHERE site_id='" . $ee->config->item('site_id') . "' AND group_name='" . @$segments[1] . "'")->row('cnt')) {
						if ($ee->db->query("SELECT count(*) as cnt FROM exp_templates WHERE site_id='" . $ee->config->item('site_id') . "' AND template_name='" . @$segments[2] . "'")->row('cnt')) {
							$dynamic_segment = 3;
						} else {
							$dynamic_segment = 2;
						}
					} else {
						$dynamic_segment = 1;
					}

					//SELECT 

					if (isset($ee->uri->segments[$dynamic_segment])) {
						if (is_numeric($ee->uri->segments[$dynamic_segment])) {
							$handler->where('campaign_id', (int) $ee->uri->segments[$dynamic_segment]);
						} else {
							$handler->where('campaign_url_title', $ee->uri->segments[$dynamic_segment]);
						}
					}
				}

				//type

				if (!empty($param['type'])) {
					if (count($param['type']) == 1) {
						$handler->where('campaign_addon', end($param['type']));
					} else {
						$handler->where_in('campaign_addon', $param['type']);
					}
				}

				//campaign_id

				if (!empty($param['campaign_id'])) {
					if (count($param['campaign_id']) == 1) {
						$handler->where('campaign_id', end($param['campaign_id']));
					} else {
						$handler->where_in('campaign_id', $param['campaign_id']);
					}
				}

				//campaign_url_title

				if (!empty($param['campaign_url_title'])) {
					if (count($param['campaign_url_title']) == 1) {
						$handler->where('campaign_url_title', end($param['campaign_url_title']));
					} else {
						$handler->where_in('campaign_url_title', $param['campaign_url_title']);
					}
				}

				//not_campaign_url_title

				if (!empty($param['not_campaign_url_title'])) {
					if (count($param['not_campaign_url_title']) == 1) {
						$handler->where('campaign_url_title != "' . addslashes(end($param['not_campaign_url_title'])) . '"');
					} else {
						$handler->where_not_in('campaign_url_title', $param['not_campaign_url_title']);
					}
				}

				//paused

				if ($param['paused'] == "yes") {
					$handler->where('paused', 1);
				} elseif ($param['paused'] == "no") {
					$handler->where('paused', 0);
				}

				//winners_announced

				if ($param['winners_announced'] == "yes") {
					$handler->where('winners_announced', 1);
				} elseif ($param['winners_announced'] == "no") {
					$handler->where('winners_announced', 0);
				}

				//site_id		

				if (!empty($param['site_id'])) {
					if (count($param['site_id']) == 1) {
						$handler->where('site_id', end($param['site_id']));
					} else {
						$handler->where_in('site_id', $param['site_id']);
					}
				}

				//status

				if (!empty($param['status'])) {
					$where_parts = array();

					if (in_array('active', $param['status'])) {
						$where_parts[] = '(start_date < ' . $ee->localize->now . ' AND end_date > ' . $ee->localize->now . ')';
					}
					if (in_array('ended', $param['status'])) {
						$where_parts[] = '(end_date <= ' . $ee->localize->now . ')';
					}
					if (in_array('scheduled', $param['status'])) {
						$where_parts[] = '(start_date >= ' . $ee->localize->now . ')';
					}

					$handler->where('(' . implode(' OR ', $where_parts) . ')');
				}
			}

		}

		$query = $this->EE->db->select("count(*) as cnt")->from("exp_promotions_campaign_entries");
		_add_where_conds($query, $param);
		$total_count = $query->get()->row('cnt');

		//full query

		$query = $this->EE->db->select("*")->from("exp_promotions_campaign_entries");
		_add_where_conds($query, $param);

		//get offset

		if (!$param["offset"]) {
			if (!empty($this->EE->uri->segments)) {
				$pagination_segment = end($this->EE->uri->segments);
				preg_match_all("/P([1-9][0-9]*)/", $pagination_segment, $matches);
				if (isset($matches[1][0])) {
					$param["offset"] = $matches[1][0];
				}
			}
		}
		$query->limit($param["limit"]);
		$query->offset($param["offset"]);

		foreach ($param["orderby"] as $orderby) {
			$query->order_by($orderby[0], $orderby[1]);
		}

		$result = $query->get()->result_array();

		$vars = array();

		foreach ($result as $cnt_index => $row) {
			$row['title'] = $row['campaign_title'];
			$row['url_title'] = $row['campaign_url_title'];

			if ($row['start_date'] < $this->EE->localize->now AND $row['end_date'] > $this->EE->localize->now) {
				$row['status'] = 'active';
			} elseif ($row['start_date'] >= $this->EE->localize->now) {
				$row['status'] = 'scheduled';
			} else {
				$row['status'] = 'ended';
			}

			if ($row['use_terms_of_service'] == 'not_required')
				$row['use_terms_of_service'] = 0;
			if ($row['use_email'] == 'not_required')
				$row['use_email'] = 0;

			if (in_array($row['use_email'], array('required', 'unique'))) {
				$row['email_required'] = 1;
			} else {
				$row['email_required'] = 0;
			}

			if ($row['use_captcha']) {
				//	$row['captcha'] 					= 1;			
			} else {
				//	$row['captcha'] 					= 0;
			}

			//get upload_url

			$row['upload_url'] = @$this->uploadDirs[$this->globalSettings[$row['site_id']]['image_upload_dir']];

			//add paginate			

			if (
					(($cnt_index == 0) AND ($param["paginate"] == 'top' OR $param["paginate"] == 'both'))
					OR
					(($cnt_index == (count($result) - 1)) AND ($param["paginate"] == 'bottom' OR $param["paginate"] == 'both'))) {
				$this->_create_pagination($row, $total_count, $param["limit"], $param["offset"]);
			} else {
				$row["paginate"] = array();
			}

			$vars[] = $row;
		}

		return $vars;
	}

	/**
	 * tag: {exp:promotions:campaigns}
	 *  
	 * @access public
	 */
	function campaigns() {
		$vars = $this->_load_campaigns();

		if (empty($vars)) {
			//no results

			return $this->EE->TMPL->no_results();
		} else {
			return $this->_parse_variables($this->EE->TMPL->tagdata, $vars);
		}
	}

	/**
	 * tag: {exp:promotions:campaigns}
	 *  
	 * @access public
	 */
	function campaign_form() {
		//---------------------------------------------------------
		//=========================================================
		//	Fetch Params
		//=========================================================		
		//---------------------------------------------------------	

		$param["return_url"] = $this->EE->functions->create_url($this->EE->TMPL->fetch_param('return'));

		$vars = $this->_load_campaigns();

		if (empty($vars)) {
			//no results

			return $this->EE->TMPL->no_results();
		} else {
			$tagdata = '{form_header}' . $this->EE->TMPL->tagdata . '{form_footer}';

			foreach ($vars as $var_key => $var_value) {
				if (!$vars[$var_key]["return_url"])
					$vars[$var_key]["return_url"] = $param["return_url"];
				$this->_parse_custom_fields($vars[$var_key]);
				$this->_parse_mailinglists($vars[$var_key]);
				$this->_parse_campaign_addon($vars[$var_key]);

				$vars[$var_key]["UID"] = md5(rand(0, 100000) . uniqid());

				if ($vars[$var_key]['use_captcha']) {
					//system capcha BACKUP

					$config_backup = $this->EE->config->config['captcha_require_members'];

					//system capcha TURN ON

					$this->EE->config->config['captcha_require_members'] = 'y';

					$captcha_img = $this->EE->functions->create_captcha();
					if ($captcha_img) {
						$_SESSION[__CLASS__][$vars[$var_key]["UID"]]['use_captcha'] = 'yes';
						$vars[$var_key]['captcha'] = $captcha_img;
					} else {
						$_SESSION[__CLASS__][$vars[$var_key]["UID"]]['use_captcha'] = 'no';
						$vars[$var_key]['captcha'] = 0;
						$vars[$var_key]['use_captcha'] = 0;
					}

					//system capcha RESTORE

					$this->EE->config->config['captcha_require_members'] = $config_backup;
				} else {
					$vars[$var_key]['use_captcha'] = 0;
				}

				//hidden data	

				$data['hidden_fields'] = array(
					'ACT' => $this->EE->functions->fetch_action_id(__CLASS__, 'do_campaign_form'),
					'XID' => '',
					'UID' => $vars[$var_key]['UID'],
					'return_url' => $vars[$var_key]["return_url"],
					'campaign_id' => $vars[$var_key]["campaign_id"],
					'campaign_addon' => $vars[$var_key]["campaign_addon"],
					'site_id' => ($this->EE->TMPL->fetch_param('site_id')) ? $this->EE->TMPL->fetch_param('site_id') : $this->siteId,
				);

				//** -----------------------------------------------
				//**	forms typical tags: id, class, name
				//** -----------------------------------------------
				//form:name
				if ($this->EE->TMPL->fetch_param('name') !== FALSE && preg_match("/^[a-zA-Z0-9_\-]+$/i", $this->EE->TMPL->fetch_param('name'))) {
					$data['name'] = $this->EE->TMPL->fetch_param('name');
				} elseif ($this->EE->TMPL->fetch_param('form:name') !== FALSE && preg_match("/^[ a-zA-Z0-9_\-]+$/i", $this->EE->TMPL->fetch_param('form:name'))) {
					$data['name'] = $this->EE->TMPL->fetch_param('form:name');
				}

				//form:id				
				if ($this->EE->TMPL->fetch_param('id') !== FALSE && preg_match("/^[a-zA-Z0-9_\-]+$/i", $this->EE->TMPL->fetch_param('id'))) {
					$data['id'] = $this->EE->TMPL->fetch_param('id');
				} elseif ($this->EE->TMPL->fetch_param('form:id') !== FALSE && preg_match("/^[ a-zA-Z0-9_\-]+$/i", $this->EE->TMPL->fetch_param('form:id'))) {
					$data['id'] = $this->EE->TMPL->fetch_param('form:id');
				}

				//form:class		
				if ($this->EE->TMPL->fetch_param('class') !== FALSE && preg_match("/^[ a-zA-Z0-9_\-]+$/i", $this->EE->TMPL->fetch_param('class'))) {
					$data['class'] = $this->EE->TMPL->fetch_param('class');
				} elseif ($this->EE->TMPL->fetch_param('form:class') !== FALSE && preg_match("/^[ a-zA-Z0-9_\-]+$/i", $this->EE->TMPL->fetch_param('form:class'))) {
					$data['class'] = $this->EE->TMPL->fetch_param('form:class');
				} else {
					$data['class'] = 'helpdesk_upload_form';
				}

				$vars[$var_key]["form_header"] = $this->EE->functions->form_declaration($data);
				$vars[$var_key]["form_footer"] = '</form>';
			}

			return $this->_parse_variables($tagdata, $vars);
		}
	}

	/**
	 * process: {exp:promotions:campaigns}
	 *  
	 * @access public
	 */
	function do_campaign_form() {
		$_POST = $this->_clean_input($_POST);
		$campaign_id = (int) @$_POST['campaign_id'];

		$errors = array();

		// ** ----------------------------------------------
		// **	validate
		// ** ----------------------------------------------
		//Test 1: campaign must be actived

		$campaign = $this->EE->db->select("*")
				->from("exp_promotions_campaign_entries")
				->where('campaign_id', $campaign_id)
				->where('draft', 0)
				->where('paused', 0)
				->where('start_date < ' . $this->EE->localize->now)
				->where('end_date > ' . $this->EE->localize->now)
				->get()
				->row_array();

		$required_fields = array();
		$wrong_fields = array();
		$other_errors = array();

		if (empty($campaign)) {
			$other_errors[] = $this->EE->lang->line("error_message_campaign_is_not_active");
			return $this->_error_message($required_fields, $wrong_fields, $other_errors);
		}


		//Test 2: required fields	
		//? email

		$email_pattern = "/^([a-zA-Z0-9])+([\.a-zA-Z0-9_-])*@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-]+)*\.([a-zA-Z]{2,6})$/";

		switch ($campaign['use_email']) {
			case 'unique':
				if (!@$_POST['email']) {
					$required_fields[] = $this->EE->lang->line('email');
				} elseif (!preg_match($email_pattern, @$_POST['email'])) {
					$wrong_fields[] = $this->EE->lang->line('email');
				} else {
					//unique test

					$email_in_use = $this->EE->db->select("count(*) as cnt")
									->from("exp_promotions_campaign_data")
									->where('campaign_id', $campaign_id)
									->where('email', $_POST['email'])
									->get()->row('cnt');

					if ($email_in_use) {
						$other_errors[] = $this->EE->lang->line("error_message_email_used");
					}
				}
				break;

			case 'required':
				if (!@$_POST['email']) {
					$required_fields[] = $this->EE->lang->line('email');
				} elseif (!preg_match($email_pattern, @$_POST['email'])) {
					$wrong_fields[] = $this->EE->lang->line('email');
				}
				break;

			default:
				if (@$_POST['email'] && !preg_match($email_pattern, @$_POST['email'])) {
					$wrong_fields[] = $this->EE->lang->line('email');
				}
				break;
		}

		//? custom fields

		$cf_query = $this->EE->db->query("SELECT required, field_name, field_label, field_type FROM exp_promotions_campaign_fields caf LEFT JOIN exp_promotions_custom_fields cuf ON cuf.field_id=caf.field_id WHERE caf.campaign_id='" . $campaign_id . "' ORDER BY caf.sort asc")->result_array();

		foreach ($cf_query as $cf) {
			if ($cf["required"]) {
				if (@$_POST[$cf['field_name']] == '') {
					$required_fields[] = $cf['field_label'];
				}
			}
		}

		//? trivia

		if ($campaign['campaign_addon']) {
			$this->EE->load->library('Campaigns');
			$campaign_addons = $this->EE->campaigns->get_avaiable_campaigns();

			foreach ($campaign_addons as $campaign_addon) {
				$campaign_addons_names[] = $campaign_addon["name"];
			}

			//load addon

			if (in_array($campaign["campaign_addon"], $campaign_addons_names)) {
				$this->EE->campaigns->load($campaign["campaign_addon"]);
				$this->EE->campaigns->{$campaign["campaign_addon"]}->validate($campaign, $required_fields, $wrong_fields, $other_errors);
			}
		}

		//? captcha

		if ($campaign['use_captcha'] && $_SESSION[__CLASS__][@$_POST["UID"]]['use_captcha'] !== 'no') {
			$requireCaptcha = true;

			$captchaQuery = $this->EE->db->select("captcha_id")
							->from("exp_captcha")
							->where('word = "' . addslashes(@$_POST['captcha']) . '"')
							->where('ip_address = "' . $_SERVER['REMOTE_ADDR'] . '"')
							->where('date > "' . ($this->EE->localize->now - (12 * 60 * 60)) . '"')->get(); //12 hours.

			if ($captchaQuery->num_rows) {
				$captchaId = $captchaQuery->row()->captcha_id;

				$this->EE->db->query("DELETE FROM exp_captcha WHERE captcha_id=$captchaId");
			} else {
				$other_errors[] = $this->EE->lang->line("error_message_wrong_captcha");
			}
		}

		//? accept terms

		if ($campaign['use_terms_of_service'] == 'required') {
			if (!isset($_POST['accept_terms'])) {
				$other_errors[] = $this->EE->lang->line('error_message_accept_terms');
			}
		}

		//show error message

		if (!empty($required_fields) OR !empty($wrong_fields) OR !empty($other_errors)) {
			return $this->_error_message($required_fields, $wrong_fields, $other_errors);
		}

		// ** ----------------------------------------------
		// **	save
		// ** ---------------------------------------------- 		

		$campaign_data = array();
		$campaign_data['campaign_id'] = $campaign_id;
		$campaign_data['site_id'] = $campaign['site_id'];
		$campaign_data['member_id'] = $this->EE->session->userdata('member_id');
		$campaign_data['ip_address'] = $this->EE->session->userdata('ip_address');
		$campaign_data['entry_date'] = $this->EE->localize->now;
		$campaign_data['valid'] = 1;
		$campaign_data['email'] = @$_POST['email'] ? $_POST['email'] : NULL;
		$campaign_data['campaign_addon_label'] = $campaign['campaign_title'];
		$campaign_data['campaign_addon_data'] = NULL;

		$cf_query = $this->EE->db->query("SELECT caf.field_id, required, field_name, field_label, field_type FROM exp_promotions_campaign_fields caf LEFT JOIN exp_promotions_custom_fields cuf ON cuf.field_id=caf.field_id WHERE caf.campaign_id='" . $campaign_id . "' ORDER BY caf.sort asc")->result_array();

		foreach ($cf_query as $cf) {
			$campaign_data['field_id_' . $cf['field_id']] = @$_POST[$cf['field_name']];
		}

		//save data before addon call

		$this->EE->db->insert('exp_promotions_campaign_data', $campaign_data);
		$campaign_data['data_id'] = $this->EE->db->insert_id();

		//addon call

		$this->EE->load->library('Campaigns');
		$campaign_addons_names = array();
		$campaign_addons = $this->EE->campaigns->get_avaiable_campaigns();

		foreach ($campaign_addons as $campaign_addon) {
			$campaign_addons_names[] = $campaign_addon["name"];
		}

		if (in_array($campaign["campaign_addon"], $campaign_addons_names)) {
			$campaign_addon = $campaign["campaign_addon"];
			$this->EE->campaigns->load($campaign_addon);
			$this->EE->campaigns->$campaign_addon->save($campaign_data, $campaign);
		}

		//change data after addon call

		if (isset($campaign_data['data_id'])) { //if data still exists!
			$this->EE->db->update('exp_promotions_campaign_data', $campaign_data, "data_id = " . (int) $campaign_data['data_id']);
		}

		// ** ----------------------------------------------
		// **	mailing lists
		// ** ----------------------------------------------
		//check, if mailinglist installed

		if (isset($_POST['mailinglist']) && isset($_POST['email']))
			$mQyery = $this->EE->db->query("SELECT * FROM exp_modules WHERE module_name='Mailinglist'");
		if ($mQyery->num_rows) {
			if (!is_array($_POST['mailinglist'])) {
				$_POST['mailinglist'] = array((int) $_POST['mailinglist']);
			}

			foreach ($_POST['mailinglist'] as $list_id) {
				$this->_set_mailinglist($_POST['email'], (int) $list_id);
			}
		}

		// ** ----------------------------------------------
		// **	redirect
		// ** ---------------------------------------------- 	

		return $this->EE->functions->redirect($_POST['return_url']);
	}

}