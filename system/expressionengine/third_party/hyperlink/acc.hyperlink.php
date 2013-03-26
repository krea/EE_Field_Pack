<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Hyperlink_acc Class - by KREA SK s.r.o.
 *
 * @package		Hyperlink
 * @author		KREA SK s.r.o.
 * @copyright	Copyright (c) 2012, KREA SK s.r.o.
 * @link		http://www.krea.com/docs/hyperlink
 * @since		Version 1.0.0
 */
 
class Hyperlink_acc {

	var $version		= '1.0.0';
	
	var $name			= 'Hyperlink';
	var $id				= 'hyperlink';
	var $extension		= '';
	var $description	= '';	
	var $sections		= array();	

/**
 * CONSTRUCTOR
 *  
 * @access public
 */	
	function Hyperlink_acc()
	{
		$this->EE = & get_instance();	
		$this->EE->lang->loadfile('hyperlink');
		
		$this->name = $this->EE->lang->line('title_accessories_tab');		
	}	
	
	function set_sections()
	{
		$this->EE->load->library('hyperlink_lib');	
		$queue_list = $this->EE->hyperlink_lib->load_queue_list();
		
		if (empty($queue_list))
		{
			$this->sections['<script type="text/javascript">$("#accessoryTabs .'.$this->id.'").parent("li").hide();</script>'] = "This is not the accessory you're looking for.";
		}
		else
		{
			$callback_act_id = (int)@$this->EE->db
			->select('action_id')
			->from('exp_actions')
			->where('class', 'Hyperlink')
			->where('method', 'hyperlink_take_screenshot_callback')			
			->get()
			->row()
			->action_id;			
			$vars = array(
				"queue_list" 	=> $queue_list,
				"refresh_url"	=> $this->EE->functions->create_url('/').'?ACT='.$callback_act_id,
			);
		
			$this->EE->load->helper('text');		
			$this->sections[lang('label_queue_list')] = $this->EE->load->view('queue_list', $vars, TRUE);
		}	
	}
	
}// END CLASS
