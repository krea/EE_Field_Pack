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
class Uploader_ext {

	/**
	 * Settings
	 *  
	 * @access public
	 * @var array
	 */
	var $settings = array();

	/**
	 * Nazov modulu
	 *  
	 * @access public
	 * @var string
	 */
	var $name = '';

	/**
	 * Popis modulu
	 *  
	 * @access public
	 * @var string
	 */
	var $description = '';

	/**
	 * Administracia rozsirenia
	 *  
	 * @access public
	 * @var string
	 */
	var $settings_exist = 'n';

	/**
	 * Verzia
	 *  
	 * @access public
	 * @var string
	 */
	var $version = '1.1.0';

	/**
	 * Docs
	 *  
	 * @access public
	 * @var string
	 */
	var $docs_url = 'www.krea.com';

	/*	 * ********************************** FUNCTIONS LIST ***************************************** */

	/**
	 * CONSTRUCTOR
	 *  
	 * @access public
	 */
	function Uploader_ext($settings = '') {
		$this->EE = & get_instance();

		/** --------------------------------------------
		  /**  jazyk v extensionoch ma vyznam nacitavat,
		  /**  len ak uz na to existuje session
		  /** -------------------------------------------- */
		if (isset($this->EE->session->userdata["language"])) {
			$this->EE->lang->loadfile('uploader');
			$this->name = $this->EE->lang->line('uploader_module_name');
		}
	}

	/**
	 * Activate extensions
	 *  
	 * @access public
	 */
	function activate_extension() {
		$this->EE->load->dbforge();

		$data = array(
			'class' => __class__,
			'method' => 'sessions_start',
			'hook' => 'sessions_start',
			'settings' => '',
			'priority' => 10,
			'version' => $this->version,
			'enabled' => 'y'
		);

		$this->EE->db->insert('extensions', $data);
	}

	/**
	 * Deactivate extensions
	 *  
	 * @access public
	 */
	function disable_extension() {
		$this->EE->load->dbforge();

		$this->EE->db->where('class', __class__);
		$this->EE->db->delete('extensions');
	}

	/** ----------------------------------------------------------------------------------------------------
	  /**
	  /** 	Ak bol odoslany formular so suborom, je potrebne zabranit odoslaniu parametra ACT
	  /** 	... v inom pripade zbehne publikacna funkcia, ktora moze vratit chybovu stanku napr. nie je zadane "title"
	  /**
	  /** ---------------------------------------------------------------------------------------------------- */
	function sessions_start($data) {
		if (isset($_GET['ACT']) AND isset($_POST['ACT']) AND isset($_POST['FILEUPLOAD'])) {
			unset($_POST['ACT']);
			unset($_POST['FILEUPLOAD']);
		}
		return $data;
	}

}

// END CLASS