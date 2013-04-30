<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * ExpressionEngine - by KREA SK s.r.o.
 *
 * @package		ExpressionEngine
 * @author		KREA SK s.r.o.
 * @copyright	Copyright (c) 2011, KREA SK s.r.o.
 * @license		http://www.krea.com/user_guide/license.html
 * @link		http://www.krea.com
 * @since		Version 1.1.0
 * @filesource
 */
class Uploader_upd {

	/**
	 * Verzia modulu
	 *  
	 * @access public
	 * @var string
	 */
	var $version = '1.1.0';

	/*	 * ********************************** FUNCTIONS LIST ***************************************** */

	/**
	 * CONSTRUCTOR
	 *  
	 * @access public
	 */
	function Uploader_upd() {
		$this->EE = & get_instance();

		$this->EE->lang->loadfile('uploader');
	}

	/**
	 * Instalacia modulu
	 *  
	 * @return boolean
	 * @access public
	 */
	function install() {
		$this->EE->load->dbforge();

		/** ------------------------------------
		  /**  Modul register
		  /** ------------------------------------ */
		$data = array(
			'module_name' => 'Uploader',
			'module_version' => $this->version,
			'has_cp_backend' => 'y',
			'has_publish_fields' => 'n'
		);

		$this->EE->db->insert('modules', $data);

		$action = array(
			'class' => 'Uploader_mcp',
			'method' => 'do_upload_file'
		);

		$this->EE->db->insert('actions', $action);

		return TRUE;
	}

	/**
	 * Deinstalacia modulu
	 *
	 * @return boolean
	 * @access public
	 */
	function uninstall() {
		$this->EE->load->dbforge();

		/** -----------------------------------------
		  /**  Unregister
		  /** ---------------------------------------- */
		$this->EE->db->select('module_id');
		$query = $this->EE->db->get_where('modules', array('module_name' => 'Uploader'));

		$this->EE->db->where('module_id', $query->row('module_id'));
		$this->EE->db->delete('module_member_groups');

		$this->EE->db->where('module_name', 'Uploader');
		$this->EE->db->delete('modules');

		$this->EE->db->where('class', 'Uploader');
		$this->EE->db->delete('actions');

		$this->EE->db->where('class', 'Uploader_mcp');
		$this->EE->db->delete('actions');

		return TRUE;
	}

	/**
	 * Module Updater
	 *
	 * @access	public
	 * @return	bool
	 */
	function update($current = '') {
		if ($current == '' OR $current == $this->version) {
			return FALSE;
		}
	}

}

//END Class
