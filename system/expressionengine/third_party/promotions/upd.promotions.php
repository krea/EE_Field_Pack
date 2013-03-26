<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine - by KREA SK s.r.o.
 *
 * @package			ExpressionEngine
 * @author			Krea.com <krea@krea.com>
 * @copyright		Copyright (c) 2012 Krea.com
 * @since			Version 0.5.0
 */

class Promotions_upd {
	
/**
 * Version
 *  
 * @access public
 * @var string
 */	
	var $version = '0.5.0';
	
	
/************************************ FUNCTIONS LIST ******************************************/	
	
/**
 * CONSTRUCTOR
 *  
 * @access public
 */		
	function Promotions_upd()
	{
		$this->EE =& get_instance();
		$this->EE->lang->loadfile('promotions');
	}	
	
/**
 * Install
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
			'module_name' => 'Promotions' ,
			'module_version' => $this->version,
			'has_cp_backend' => 'y',
			'has_publish_fields' => 'n'
		);
		
		$this->EE->db->insert('modules', $data);
		
		$queries = array();
		   
		$queries[] = "				
			CREATE TABLE IF NOT EXISTS `exp_promotions_custom_fields` (
			  `field_id` int(11) NOT NULL AUTO_INCREMENT,
			  `site_id` int(4) NOT NULL,
			  `field_name` varchar(50) NOT NULL,
			  `field_label` varchar(255) NOT NULL,
			  `field_type` varchar(50) NOT NULL,
			  `sort` int(11) NOT NULL DEFAULT 0,			  
			  PRIMARY KEY (`field_id`)
			);";	
			
		$queries[] = "				
			CREATE TABLE IF NOT EXISTS `exp_promotions_campaign_entries` (
			  `campaign_id` int(11) NOT NULL AUTO_INCREMENT,
			  `site_id` int(4) NOT NULL,
			  `campaign_addon` varchar(20) NULL,
			  `campaign_addon_settings` TEXT NULL,
			  `draft` tinyint(1) NOT NULL DEFAULT '1', 
			  `paused` tinyint(1) NOT NULL DEFAULT '0',
			  `start_date` int(11) NULL,
			  `end_date` int(11) NULL,
			  `campaign_title` varchar(200) NOT NULL,	  
			  `campaign_url_title` varchar(200) NOT NULL,
			  `head_title` TEXT NULL,	
			  `head_image` TEXT NULL,			  
			  `head_note` TEXT NULL,
			  `foot_note` TEXT NULL,
			  `terms` TEXT NULL,	
			  `image` VARCHAR(200) NULL,
			  `use_captcha` INT NOT NULL DEFAULT '0',
			  `use_email` ENUM( 'unique' , 'required', 'not_required', 'optional' ) NOT NULL DEFAULT 'not_required',
			  `use_terms_of_service` ENUM( 'required', 'not_required' ) NOT NULL DEFAULT 'not_required',  
			  `return_url` TEXT NULL,		  
			  `winners_announced` tinyint(1) NOT NULL DEFAULT '0',
			  `winners_announced_report` TEXT NULL,		
			  `entry_date` int(11) NOT NULL DEFAULT 0,
			  `author_id` int(11) NOT NULL,
			  PRIMARY KEY (`campaign_id`)
			);";	
			
		$queries[] = "				
			CREATE TABLE IF NOT EXISTS `exp_promotions_campaign_lists` (
			  `campaign_id` int(11) NOT NULL,
			  `site_id` int(4) NOT NULL,
			  `list_id` int(11) NOT NULL,
			  `sort` int(11) NOT NULL DEFAULT 0,
			  PRIMARY KEY (`campaign_id`,`list_id`)
			);";	
			
		$queries[] = "				
			CREATE TABLE IF NOT EXISTS `exp_promotions_campaign_fields` (
			  `campaign_id` int(11) NOT NULL,
			  `site_id` int(4) NOT NULL,
			  `field_id` int(11) NOT NULL,
			  `sort` int(11) NOT NULL DEFAULT 0,
			  `required` int(11) NOT NULL DEFAULT 0,
			  PRIMARY KEY (`campaign_id`,`field_id`)
			);";						
			
		$queries[] = "				
			CREATE TABLE IF NOT EXISTS `exp_promotions_campaign_data` (
			  `data_id` int(11) NOT NULL AUTO_INCREMENT,		   		  	  			  
			  `campaign_id` int(11) NOT NULL,
			  `site_id` int(4) NOT NULL,
			  `member_id` int(11) NOT NULL DEFAULT 0,
			  `ip_address` varchar(200) NOT NULL,
			  `entry_date` int(11) NOT NULL DEFAULT 0,
			  `valid` tinyint(1) NOT NULL DEFAULT '0',
			  `email` TEXT NULL,
			  `campaign_addon_label` TEXT NULL,				  
			  `campaign_addon_data` TEXT NULL,	
			  `campaign_addon_note` TEXT NOT NULL,
			  `campaign_addon_flag` int(1) NOT NULL DEFAULT '0',				  
			  PRIMARY KEY (`data_id`),
			  KEY (`campaign_id`)
			);";	
			
		$queries[] = "				
			CREATE TABLE IF NOT EXISTS `exp_promotions_settings` (
			  `site_id` int(4) NOT NULL,
			  `var_param` varchar(20) NOT NULL,
			  `var_value` TEXT NULL,			  
			  PRIMARY KEY (`site_id`,`var_param`)
			);";			
	
		foreach ($queries as $query)
		{
			$this->EE->db->query($query);
		}    	    
		   
		//actions 
		    
		$action = array(
			'class' => 'Promotions',
			'method' => 'do_campaign_form'
		);								
		$this->EE->db->insert('actions', $action);		
			
		return TRUE;		
	}	
	
/**
 * Uninstal
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
		$query = $this->EE->db->get_where('modules', array('module_name' => 'Promotions'));
		
		$this->EE->db->where('module_id', $query->row('module_id'));
		$this->EE->db->delete('module_member_groups');

		$this->EE->db->where('module_name', 'Promotions');
		$this->EE->db->delete('modules');

		$this->EE->db->where('class', 'Promotions');
		$this->EE->db->delete('actions');
		
		$this->EE->db->where('class', 'Promotions_mcp');
		$this->EE->db->delete('actions');		
	

		$queries[] = "DROP TABLE IF EXISTS `exp_promotions_custom_fields`";	
		$queries[] = "DROP TABLE IF EXISTS `exp_promotions_campaign_entries`";
		$queries[] = "DROP TABLE IF EXISTS `exp_promotions_campaign_lists`";
		$queries[] = "DROP TABLE IF EXISTS `exp_promotions_campaign_fields`";
		$queries[] = "DROP TABLE IF EXISTS `exp_promotions_campaign_data`";		
		$queries[] = "DROP TABLE IF EXISTS `exp_promotions_settings`";	
		
		
		foreach ($queries as $query)
		{
			$this->EE->db->query($query);
		} 
	
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
