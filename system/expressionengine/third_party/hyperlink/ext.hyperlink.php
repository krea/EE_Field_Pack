<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Hyperlink_ext Class - by KREA SK s.r.o.
 *
 * @package		Hyperlink
 * @author		KREA SK s.r.o.
 * @copyright	Copyright (c) 2012, KREA SK s.r.o.
 * @link		http://www.krea.com/docs/hyperlink
 * @since		Version 1.0.0
 */

class Hyperlink_ext {

/**
 * Settings
 *  
 * @access public
 * @var array
 */	
	var $settings	= array();
	
/**
 * Module name
 *  
 * @access public
 * @var string
 */		
	var $name = '';
	
/**
 * Module description
 *  
 * @access public
 * @var string
 */		
	var $description = '';
	
/**
 * Settings not exists
 *  
 * @access public
 * @var string
 */		
	var $settings_exist = 'n';
	
/**
 * Version
 *  
 * @access public
 * @var string
 */			
	var $version = '1.0.0';
	
/**
 * Docs
 *  
 * @access public
 * @var string
 */		
	var $docs_url = 'http://www.krea.com';
	
	
/************************************ FUNCTIONS LIST ******************************************/		


/**
 * Constructor
 *  
 * @access public
 */
	function Hyperlink_ext($settings = '') 
	{
		$this->EE =& get_instance();	
		
		if (isset($this->EE->session->userdata["language"]))
		{
			$this->EE->lang->loadfile('hyperlink');
			$this->name = $this->EE->lang->line('hyperlink_module_name');
		}						
	}	
	
/**
 * Activate extensions
 *  
 * @access public
 */		
	function activate_extension()
	{				
		$this->EE->load->dbforge();	
					
		$data = array(
			'class' 	=> __class__,
			'method' 	=> 'entry_submission_end',
			'hook' 	=> 'entry_submission_end',
			'settings' 	=> '',
			'priority' 	=> 10,
			'version' 	=> $this->version,
			'enabled' 	=> 'y'
		);	
		
		$this->EE->db->insert('extensions', $data);	
		
		$data = array(
			'class' 	=> __class__,
			'method' 	=> 'delete_entries_loop',
			'hook' 	=> 'delete_entries_loop',
			'settings' 	=> '',
			'priority' 	=> 10,
			'version' 	=> $this->version,
			'enabled' 	=> 'y'
		);	
		
		$this->EE->db->insert('extensions', $data);				
	}		
	
/**
 * Deactivate extensions
 *  
 * @access public
 */	 	
	function disable_extension()
	{				
		$this->EE->load->dbforge();	
		
		$this->EE->db->where('class', __class__);
		$this->EE->db->delete('extensions');
	}		

/**
 * Delete entries
 *  
 * @access public
 */	 
	function delete_entries_loop ( $val, $channel_id )
	{
		$this->EE->db->where('entry_id', $val);
		$this->EE->db->delete('exp_hyperlink');					
		return $val;
	}
	
/**
 * After entry submit, take screenshots
 *  
 * @access public
 */	 
	function entry_submission_end ( $entry_id, $meta, $data )
	{
		/*
		$this->EE->load->library('hyperlink_lib');
		
		$query = 
			$this->EE->db->select('hyperlink_id')
				->from('exp_hyperlink')
				->where('entry_id', $entry_id)
				->where_in('screenshot_status', array('take','expired'))	
				->get()
				->result_array();
		
		foreach ($query as $row)
		{
			$this->EE->hyperlink_lib->take_screenshot( $row['hyperlink_id'] );
		}		
		return $entry_id;
		*/
	}	

} //END Class
