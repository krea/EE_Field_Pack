<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Content_blocks Class
 *
 * @package   Content_blocks
 * @author    Michal Varga
 * @copyright Copyright (c) 2011 KREA SK s.r.o.
 */
 
 
class Trivia_campaign { 

	var $info = array(
		'version'			=> '1.0.0',
		'priority'			=> 2,		
	);
	
	var $name = ''; //set automatic, if load
	
	//CONSTRUCT
	
	function Trivia_campaign()
	{
		$this->EE = &get_instance();
	}
	
//----------------------------------------------------------------------------------------------------------------------------
//
// CAMPAIGN CORE FUNCTIONS
//
//----------------------------------------------------------------------------------------------------------------------------		
	

/**
 * Frontent: Parse template
 *  
 * @access public
 */		
	
	function display(&$vars)
	{		
		if (is_array(@unserialize($vars['campaign_addon_settings'])))
		{
			$trivia_addon_settings = unserialize($vars['campaign_addon_settings']);
			if (isset($trivia_addon_settings[__CLASS__]))
			{
				$questions = $trivia_addon_settings[__CLASS__];
				
				foreach ($questions as $question_id => $question)
				{
					$questions[$question_id]["question_id"] = $question_id;
				
					foreach ($question["answers"] as $answer_id => $answer)
					{	
						$questions[$question_id]["answers"][$answer_id]["answer_title"] = $answer;
						$questions[$question_id]["answers"][$answer_id]["answer_id"] = $answer_id;
						$questions[$question_id]["answers"][$answer_id]["answer_name"] = 'answer['.$question_id.']';
					}
				}
				
				$vars["trivia"] = array(
					array(
						"questions" => $questions
					)
				);
			};	
		};
	}	
	
/**
 * Frontent: Validate submitted data
 *  
 * @access public
 */	
		
	function validate($vars, &$required_fields, &$wrong_fields, &$other_errors)
	{		
		if (is_array(@unserialize($vars['campaign_addon_settings'])))
		{
			$trivia_addon_settings = unserialize($vars['campaign_addon_settings']);
			if (isset($trivia_addon_settings[__CLASS__]))
			{
				$questions = $trivia_addon_settings[__CLASS__];
				
				foreach ($questions as $question_id => $question)
				{	
					if (!isset($_POST["answer"][$question_id]))
					{	
						$error = $this->EE->lang->line("error_trivia_all_questions_must_be_answered");
						
						if (!in_array($error, $other_errors)) $other_errors[] = $error;
					}
				}
		
			};	
		};
	}	
		

/**
 * Frontent: Save submitted data
 *  
 * @access public
 */
 
	function save(&$data, $campaign)
	{
		if (is_array(@unserialize($campaign['campaign_addon_settings'])))
		{
			$trivia_addon_settings = unserialize($campaign['campaign_addon_settings']);
			
			if (isset($trivia_addon_settings[__CLASS__]))
			{
				$questions = $trivia_addon_settings[__CLASS__];
				
				$saved_answers = array(
					"questions" => array(),
					"score" => 0,
					"max_score" => 0,
				);
				
				foreach ($questions as $question_id => $question)
				{	
					/*
					print_r($_POST);
					echo $_POST['answer'][$question_id];
					*/
					
					$answer_id 	= @$_POST['answer'][$question_id];
					$answer 	= @$question['answers'][$answer_id]['answer'];
					$correct 	= @$question['answers'][$answer_id]['correct'];
							
					$saved_answers["questions"][$question_id] = array(
						"question" 	=> @$question["question"],
						"answer"  	=> $answer,
						"answer_id" => $answer_id,
						"correct"	=> $correct 
					);

					$saved_answers["max_score"]++;
					if ($correct)
					{
						$saved_answers["score"]++;
					}	
				}
				
				if ($saved_answers["max_score"] == $saved_answers["score"])
				{
					$data["valid"] = 1;
				}
				else
				{
					$data["valid"] = 0;
				}
				
				$data["campaign_addon_note"] = $saved_answers["score"].' / '.$saved_answers["max_score"];
				$data["campaign_addon_data"] = serialize($saved_answers);
			};	

		};
	} 	
	
/**
 * CP: Show tab during create campaign
 *  
 * @access public
 */	
	
	function cp($mcp, $campaign_id, $data)
	{
		if (@$_POST['action'] == 'add_answer')
		{
			return $this->cpAddAnswer($_POST['field_id']);
		}
		if (@$_POST['action'] == 'add_question')
		{
			return $this->cpAddQuestion();
		}			
	
		$this->EE->cp->set_variable('cp_breadcrumbs', array(
			$mcp->moduleBase => lang('promotions_module_name'),
			$mcp->moduleBase.AMP.'method=campaigns' => lang('title_campaigns'),			
		));	
				
		if ($data)
		{
			$data = @unserialize($data);
		}
		
		if (isset($data[__CLASS__]))
		{
			$data = $data[__CLASS__];
		}
		else
		{
			$data = array(
				array(
					'question' => '',
					'answers' => false
				)
			);
		}
		
		$vars["trivia_form_fields"] = '';
		foreach ($data as $question)
		{
			$vars["trivia_form_fields"] .= $this->cpAddQuestion($question['question'], $question['answers'], false);
		}
		
		$mcp->_include_theme_js('jquery.ui.sortable.js');
		$mcp->_include_theme_js('jquery.form.js');			
		$mcp->_include_theme_js('jquery.elastic.js');
		$mcp->_include_theme_js('validate.js');			
		$mcp->_include_theme_css('validate.css');							
		
		return $mcp->_view('../campaigns/trivia/views/index', $vars, TRUE, lang('title_campaign_trivia'), FALSE);			
	}
	
/**
 * CP: Process cp tab during create campaign
 *  
 * @access public
 */		
		
	function cp_process($mcp, $campaign_id)
	{
		$status = "success";
		$fields = array();
		$message = '';

		$mcp->_clean_input($_POST);	
		
		//parse POST DATA
		
		$data = array();
	
		if (isset($_POST['question'])) foreach ($_POST['question'] as $question_id => $question)
		{
			//is question filled?
			
			if (!$question)
			{
				$status = "error";
				$fields[] = "question[".$question_id."]";
				$message = lang('error_validate_marked_field_is_required');
			}
			
			$data_anser_items = array();
			$correct_setup = false;
			
			if (isset($_POST['answer'][$question_id])) foreach ($_POST['answer'][$question_id] as $answer_id => $answer)
			{
				$correct = isset($_POST['answer_correct'][$question_id][$answer_id])?1:0;
				if ($correct)
				{
					$correct_setup = true;
				}
			
				$data_anser_items[] = array(
						"answer" => $answer,
						"correct" => $correct,						
				);
				
				if (!$answer)
				{
					$status = "error";
					$fields[] = "answer[".$question_id."][".$answer_id."]";
					$message = lang('error_validate_marked_field_is_required');
				}				
			}
			
			if ($status != "error" && !$correct_setup)
			{
				if (isset($_POST['answer'][$question_id])) foreach ($_POST['answer'][$question_id] as $answer_id => $answer)
				{
					$status = "error";
					$fields[] = "answer_correct[".$question_id."][".$answer_id."]";
					$message = lang('error_min_one_answer_must_be_correct');
				}
			}			
			
			$data_item = array(
				"question" => $question,
				"answers" => $data_anser_items
			);
		
			$data[] = $data_item;
		}
		
		if ($status == "error")
		{
			echo json_encode(array(
				"result" 	=> $status,
				"fields" 	=> $fields,
				"message" 	=> $message
			));
		}
		else		
		{
			$data = array
			(
				"campaign_addon_settings" => serialize(array(__CLASS__ => $data))															
			);
		
			$this->EE->db->update('exp_promotions_campaign_entries', $data, 'campaign_id = '.(int)$campaign_id);
		
			echo json_encode(array(
				"result" 	=> "success",
				"redirect"	=> str_replace(AMP, '&', $mcp->moduleBase.AMP.'method=campaignsCompletition'.AMP.'campaign_id='.(int)$campaign_id),
				"message" 	=> $message
			));		
		}
		
		exit;
	}

/**
 * CP: Show tab during create campaign
 *  
 * @access public
 */	
	
	function report($mcp, $campaign_id, $vars)
	{
		if (@$_GET['action']=='detail' && is_numeric(@$_GET['data_id']))
		{
			return $this->reportDetail($mcp, $campaign_id, @$_GET['data_id'], $vars);
		}
		if (@$_GET['action']=='delete' && is_numeric(@$_GET['data_id']))
		{
			return $this->reportDelete($mcp, $campaign_id, @$_GET['data_id'], $vars);
		}		
		
		
		$vars['record'] = $vars; 
	
		$this->EE->cp->set_variable('cp_breadcrumbs', array(
			$mcp->moduleBase => lang('promotions_module_name'),
			$mcp->moduleBase.AMP.'method=campaigns' => lang('title_campaigns'),			
		));
		
		//custom fields
		
		$campaign_fields = array();
		foreach ($this->EE->db->query("SELECT * FROM exp_promotions_campaign_fields caf LEFT JOIN exp_promotions_custom_fields cuf 
			ON cuf.field_id=caf.field_id WHERE campaign_id='$campaign_id' ORDER BY caf.sort asc")->result_array() as $cf)
		{
			$campaign_fields[] = $cf;
		}
		
		//mass action
		
		if (isset($_POST['mass_action']) && isset($_POST['data']) && $_POST['mass_action'])
		{
			list($action_name, $action_value) = explode('_', trim($_POST['mass_action']), 2);
			
			switch ($action_name)
			{
				case 'valid':
					foreach ($_POST['data'] as $mass_action_data_id)
					{
						$this->EE->db->update('exp_promotions_campaign_data', array('valid'=>(int)$action_value), 'data_id = '.(int)$mass_action_data_id);
					}
					$_SESSION["promotions"]["flash_message"] = lang('message_operation_success');
				break;
			}
		}
					
		//default filter values
		
		if (isset($_GET['reset'])) $_SESSION['trivia_report']['filter'] = array();
		
		if (!isset($_SESSION['trivia_report']['filter']['keyword']))		$_SESSION['trivia_report']['filter']['keyword'] = ''; 
		if (!isset($_SESSION['trivia_report']['filter']['valid']))			$_SESSION['trivia_report']['filter']['valid'] = ''; 
		if (!isset($_SESSION['trivia_report']['filter']['limit']))			$_SESSION['trivia_report']['filter']['limit'] = '50'; 
		if (!isset($_SESSION['trivia_report']['filter']['entry_date']))		$_SESSION['trivia_report']['filter']['entry_date'] = '0--';		
		
		if (isset($_POST['keyword']))
		{
			if ($_POST['keyword']!=$this->EE->lang->line('trivia_filter_keyword'))
			{
				$_SESSION['trivia_report']['filter']['keyword'] = $_POST['keyword'];				
			}
			else
			{
				$_SESSION['trivia_report']['filter']['keyword'] = '';	
			}
		}
		
		if (isset($_POST['limit'])) 		$_SESSION['trivia_report']['filter']['limit'] = $_POST['limit'];	
		if (isset($_POST['valid']))			$_SESSION['trivia_report']['filter']['valid'] = $_POST['valid'];		
		if (isset($_POST['entry_date']))	$_SESSION['trivia_report']['filter']['entry_date'] = $_POST['entry_date'];
		
	
		$_SESSION['trivia_report']['filter']['offset'] = (int)@$_GET['page'];
		
		//redirect fix (if you click to back button, browser show dummy warning)
		
		if (isset($_GET['reset']) OR isset($_POST['keyword'])) 
		{
			$this->EE->functions->redirect($mcp->moduleBase.AMP.'method=campaignsReport'.AMP.'campaign_id='.@$_GET['campaign_id']);
		}
		
		$vars['filter'] = $_SESSION['trivia_report']['filter'];		
		
		//load campaign status
		
		$vars['valid_options'] = array(
			'' 					=> lang('trivia_filter_all'),
			'valid' 			=> lang('trivia_filter_valid'),
			'invalid' 			=> lang('trivia_filter_invalid'),								
		);		
				
		//load date intervals
		
		$vars['dates']['0--']											= lang('campaigns_filter_date_all');
		$vars['dates']['1-'.strtotime(date('Y-m-d 00:00:00')).'-'.strtotime(date('Y-m-d 00:00:00').' + 1 day')] 		= lang('campaigns_filter_date_today');
		$vars['dates']['2-'.strtotime(date('Y-m-d 00:00:00').' - 1 day').'-'.strtotime(date('Y-m-d 00:00:00'))] = lang('campaigns_filter_date_yesterday');
		$vars['dates']['3-'.strtotime(date('Y-m-01 00:00:00')).'-'.strtotime(date('Y-m-d 00:00:00').' + 1 month')] 	= lang('campaigns_filter_date_this_month');
		$vars['dates']['4-'.strtotime(date('Y-m-01 00:00:00').' - 1 month').'-'.strtotime(date('Y-m-01 00:00:00'))] = lang('campaigns_filter_date_last_month');	
		$vars['dates']['5-'.strtotime(date('Y-01-01 00:00:00')).'-'.strtotime(date('Y-01-01 00:00:00').' + 1 year')] 	= lang('campaigns_filter_date_this_year');
		$vars['dates']['6-'.strtotime(date('Y-01-01 00:00:00').' - 1 year').'-'.strtotime(date('Y-01-01 00:00:00'))] = lang('campaigns_filter_date_last_year');		
		
		//limit intervals
		
		$vars['limits']['50']	= '50 '.$this->EE->lang->line('campaigns_filter_per_page');
		$vars['limits']['100']	= '100 '.$this->EE->lang->line('campaigns_filter_per_page');
		$vars['limits']['250']	= '250 '.$this->EE->lang->line('campaigns_filter_per_page');
		$vars['limits']['500']	= '500 '.$this->EE->lang->line('campaigns_filter_per_page');			
			
		function _add_where_conditions($dbHandler, $filter, $siteId, $campaign_fields, $campaign_id)
		{
			$EE = &get_instance();
		
			//filter by date
		
			$filterDate = explode('-', $filter['entry_date']);
		
			if ($filterDate[1] && $filterDate[2])
			{
				$dbHandler->where('exp_promotions_campaign_data.entry_date < "'.(int)$filterDate[2].'"');
				$dbHandler->where('exp_promotions_campaign_data.entry_date > "'.(int)$filterDate[1].'"');			
			}
			
			//filter by keyword
			
			if ($filter['keyword'])
			{
				$sql_string = "(";
			
				//todo...
			
				$sql_string .= "exp_promotions_campaign_data.email LIKE '%".addslashes($filter['keyword'])."%' ";
				
				foreach ($campaign_fields as $cf)
				{
					$sql_string .= "OR exp_promotions_campaign_data.field_id_".$cf['field_id']." LIKE '%".addslashes($filter['keyword'])."%' ";
				}
				
				$sql_string .= ")";
				
				$dbHandler->where($sql_string);
			}

			//filter status
			
			if ($filter['valid'] == 'valid')
			{
				$dbHandler->where('exp_promotions_campaign_data.valid', 1);
			}
			elseif ($filter['valid'] == 'invalid')
			{
				$dbHandler->where('exp_promotions_campaign_data.valid', 0);
			}			
			
			$dbHandler->where('exp_promotions_campaign_data.site_id = '.(int)$siteId);
			$dbHandler->where('exp_promotions_campaign_data.campaign_id = '.(int)$campaign_id);
			
			return $dbHandler;
		}
		
		//get total count (for pagination)
		
		$dbHandler = $this->EE->db->select('count(*) as cnt')->from('exp_promotions_campaign_data');
		$vars['total_count'] = _add_where_conditions($this->EE->db, $vars['filter'], $mcp->siteId, $campaign_fields, $campaign_id)
			->get()
			->row('cnt');
		
		//get entries (for display)
		
		$dbHandler = $this->EE->db->select('*')
			->from('exp_promotions_campaign_data');
			
		$vars['entries'] = _add_where_conditions($this->EE->db, $vars['filter'], $mcp->siteId, $campaign_fields, $campaign_id)
			->order_by('exp_promotions_campaign_data.entry_date', 'desc')
			->limit($vars['filter']['limit'])
			->offset($vars['filter']['offset'])
			->get()
			->result_array();	
			
		foreach ($vars['entries'] as $k=>$v)	
		{	
			$cfs = '';
			foreach ($campaign_fields as $cf)
			{
				if ($v["field_id_".$cf['field_id']])
				{
					$cfs .= $v["field_id_".$cf['field_id']].', ';
				}
			}	
			$cfs = trim($cfs, ', ');		
			
			/*
			if (mb_strlen($cfs,'UTF-8') > 100)
			{
				$cfs = mb_strcut($cfs, 0, 100, 'UTF-8').'...';
			}
			*/
			
			$vars['entries'][$k]['custom_fields'] = $cfs;
		}

		//pagination
		
		$configPager['base_url'] = $mcp->moduleBase.AMP.'method=campaignsReport'.AMP.'campaign_id='.$campaign_id;
		$configPager['total_rows'] = $vars['total_count'];
		$configPager['per_page'] = $vars['filter']['limit'];
		$configPager['page_query_string'] = TRUE;
		$configPager['query_string_segment'] = 'page';
			
		$this->EE->pagination->initialize($configPager);
		$vars['pagination'] = $this->EE->pagination->create_links();	
			
		return $mcp->_view('../campaigns/trivia/views/report', $vars, TRUE, $vars['campaign_title'], FALSE);			
	}
	
/**
 * CP: Show tab during create campaign
 *  
 * @access public
 */	
	
	function reportDetail($mcp, $campaign_id, $data_id, $vars)
	{
		$this->EE->cp->set_variable('cp_breadcrumbs', array(
			$mcp->moduleBase => lang('promotions_module_name'),
			$mcp->moduleBase.AMP.'method=campaigns' => lang('title_campaigns'),		
			$mcp->moduleBase.AMP.'method=campaignsReport'.AMP.'campaign_id='.$campaign_id => $vars['campaign_title'],		
		));
		
		//addon data
		
		$vars["respond"] = $this->EE->db->select('*')->from('exp_promotions_campaign_data')->where('data_id', $data_id)->get()->row_array();
		$vars["respond"]["campaign_addon_data"] = @unserialize($vars["respond"]["campaign_addon_data"]);
		
		//custom field data
		
		$vars["custom_fields"] = array();
		foreach ($this->EE->db->query("SELECT * FROM exp_promotions_campaign_fields caf LEFT JOIN exp_promotions_custom_fields cuf 
			ON cuf.field_id=caf.field_id WHERE campaign_id='$campaign_id' ORDER BY caf.sort asc")->result_array() as $cf)
		{
			$vars["custom_fields"][] = $cf;
		}
		
		return $mcp->_view('../campaigns/trivia/views/reportDetail', $vars, TRUE, $this->EE->lang->line('trivia_title_report_detail'), FALSE);			
	}	
	
/**
 * CP: Report delete process
 *  
 * @access public
 */		
	
	function reportDelete($mcp, $campaign_id, $data_id, $vars)
	{
		$this->EE->db->query("DELETE FROM exp_promotions_campaign_data WHERE data_id='$data_id' AND campaign_id='$campaign_id'");
		$_SESSION["promotions"]["flash_message"] = lang('message_operation_success');
		return $this->EE->functions->redirect($mcp->moduleBase.AMP.'method=campaignsReport'.AMP.'campaign_id='.$campaign_id);
	}	

//----------------------------------------------------------------------------------------------------------------------------
//
// AJAX CP FUNCTIONS
//
//----------------------------------------------------------------------------------------------------------------------------
	
	//create HTML for question
	
	function cpAddAnswer($field_id, $value = '', $correct = '0', $exit = true)
	{
		$answer_id = 'ans_'.uniqid();
	
		$html = '
		<tr class="answer_box">
			<td class="anser_field">
				<span class="dragArw"></span>'.form_input('answer['.$field_id.']['.$answer_id.']', $value).'
			</td>
			<td>
				<label>'.form_checkbox('answer_correct['.$field_id.']['.$answer_id.']', '1', $correct).' '.lang('trivia_label_correct').'</label>
			</td>
			<td align="right"><a class="button_answer_remove" href="#" >'.lang('trivia_button_remove_answer').'</a></td>
		</tr>';
		
		if ($exit)
		{
			echo $html;
			exit;
		}
		else
		{
			return $html;
		}
	}
	
	//create HTML for answer
	
	function cpAddQuestion($question = '', $answers = FALSE, $exit = true)
	{
		if (!$answers)
		{
			$answers = array(
				array(
						"answer" => '',
						"correct" => 0,						
					),
				array(
						"answer" => '',
						"correct" => 0,						
					),	
				array(
						"answer" => '',
						"correct" => 0,						
					),										
			);
		}
	
		$field_id = 'que_'.uniqid();
		
		$html = '	
			<div class="question_box">
							
				<table cellpadding="0" cellspacing="0">  
				<tbody>
					<tr class="question_header">
						<td class="col1">Question #<span class="question_order">1</span></th>
						<td align="right"><a class="button_question_remove btn" href="#" >'.lang('trivia_button_remove_question').'</a></td>
					</tr>
					<tr class="question_input">
						<td class="col1">
							<em class="required">* </em>
							<label for="'.$field_id.'">'.lang('trivia_label_question').'</label>
						</td>
						<td style="padding-left: 22px">
							'.form_input('question['.$field_id.']', $question, 'id="'.$field_id.'"').'
						</td>
					</tr>
					<tr class="redLinks">
						<td class="col1">
							<em class="required">* </em>
							<label>'.lang('trivia_label_answers').'</label>
						</td>
						<td style="padding-bottom: 0">
							<table cellpading="0" cellspacing="0" class="answer_table">
							<tbody>
							';
			
		foreach ($answers as $answer)
		{	
			$html .= $this->cpAddAnswer($field_id, $answer["answer"], $answer["correct"], false);
		}	
							
		$html .= 					
							'</tbody>							
							</table>
						</td>		
					</tr>		
					<tr class="redLinks">	
						<td>&nbsp;</td>
						<td style="padding-top: 0; padding-left: 22px">
							<a class="button_answer_add" href="#">'.lang('trivia_button_add_answer').'</a>
						</td>											
					</tr>
				</tbody>		
				</table>
			</div>';
		
		if ($exit)
		{
			echo $html;
			exit;
		}
		else
		{
			return $html;
		}
	}	
}