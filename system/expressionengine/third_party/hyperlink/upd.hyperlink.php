<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Hyperlink_upd Class - by KREA SK s.r.o.
 *
 * @package		Hyperlink
 * @author		KREA SK s.r.o.
 * @copyright	Copyright (c) 2012, KREA SK s.r.o.
 * @link		http://www.krea.com/docs/hyperlink
 * @since		Version 1.0.0
 */

class Hyperlink_upd {
	
/**
 * Version
 *  
 * @access public
 * @var string
 */	
	var $version = '1.0.0';
	
	
/************************************ FUNCTIONS LIST ******************************************/	
	
/**
 * CONSTRUCTOR
 *  
 * @access public
 */		
	function Hyperlink_upd()
	{
		$this->EE =& get_instance();
		
		$this->EE->lang->loadfile('hyperlink');
	}	
	
/**
 * Instal
 *  
 * @return boolean
 * @access public
*/	
	function install()
	{
		$this->EE->load->dbforge();
		
		/** ------------------------------------
		/**  Modul register
		/** ------------------------------------*/
		
		$data = array(
			'module_name' => 'Hyperlink' ,
			'module_version' => $this->version,
			'has_cp_backend' => 'y',
			'has_publish_fields' => 'n'
		);
		$this->EE->db->insert('modules', $data);
		
		/** ------------------------------------
		/**  Actions
		/** ------------------------------------*/		
		
		$action = array(
			'class' => 'Hyperlink',
			'method' => 'hyperlink_take_screenshot_callback'
		);										
		$this->EE->db->insert('actions', $action);
		
		$action = array(
			'class' => 'Hyperlink',
			'method' => 'hyperlink_schedule_validation_of_links'
		);									
		$this->EE->db->insert('actions', $action);		
		
		$action = array(
			'class' => 'Hyperlink',
			'method' => 'hyperlink_check_url_status'
		);									
		$this->EE->db->insert('actions', $action);	
		
		$action = array(
			'class' => 'Hyperlink',
			'method' => 'hyperlink_click'
		);										
		$this->EE->db->insert('actions', $action);				
		
		/** ------------------------------------
		/**  Queries
		/** ------------------------------------*/		
			
		$query = "CREATE TABLE IF NOT EXISTS `exp_hyperlink_settings` (
			  `site_id` int(11) NOT NULL,
			  `settings` TEXT NOT NULL,
			  PRIMARY KEY  (`site_id`)
		)";	
		$this->EE->db->query($query);		
		
		return TRUE;		
	}	
	
/**
 * Deinstal
 *
 * @return boolean
 * @access public
 */		
	function uninstall()
	{
		$this->EE->load->dbforge();

		/** -----------------------------------------
		/**  Unregister
		/** ----------------------------------------*/
		
		$this->EE->db->select('module_id');
		$query = $this->EE->db->get_where('modules', array('module_name' => 'Hyperlink'));
		
		$this->EE->db->where('module_id', $query->row('module_id'));
		$this->EE->db->delete('module_member_groups');

		$this->EE->db->where('module_name', 'Hyperlink');
		$this->EE->db->delete('modules');

		$this->EE->db->where('class', 'Hyperlink');
		$this->EE->db->delete('actions');
		
		$this->EE->db->where('class', 'Hyperlink_mcp');
		$this->EE->db->delete('actions');		
			
		/** -----------------------------------------
		/**  Tables
		/** ----------------------------------------*/		
		
		$this->EE->db->query("DROP TABLE IF EXISTS `exp_hyperlink_settings`");
	
		return TRUE;
	}
	
/**
 * Module Updater
 *
 * @access	public
 * @return	bool
 */		
	function update($current = '')
	{
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}		
	}	

} //END Class
