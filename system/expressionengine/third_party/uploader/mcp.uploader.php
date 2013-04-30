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
class Uploader_mcp {

	/**
	 * Version
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
	function Uploader_mcp() {
		$this->EE = & get_instance();
		$this->EE->lang->loadfile('uploader');
	}

	function index() {
		$this->EE->cp->set_variable('cp_page_title', lang('uploader_module_name'));
		return lang('uploader_cp_only_for_script');
	}

//--------------------------------------------------------------------
//
//	Tato funkcia sa spusta vzdialene, ked:
//	1 - uzivatel nahral obrazok
//  2 - uzivatel dal prikaz na odstranenie obrazka
//	3 - aplikacia pozaduje load existujucich obrazkov 
//
//--------------------------------------------------------------------

	function do_upload_file() {
		if (session_id() == '') {
			session_start();
		}

		if (function_exists('header_remove')) {
			header_remove();
		}

		//	(1) nahrat
		//--------------------------------------------------------------------

		if (is_array($_FILES)) {
			foreach ($_FILES as $key => $value) {
				if ($key == 'ft_uploader_file' AND @$_GET['dir']) {
					return $this->_upload_file((int) $_GET['dir']);
				}
			}
		}

		//	(3) zmazat - todo
		//--------------------------------------------------------------------

		if (@$_GET['filename'] AND @$_GET['hash'] AND @$_GET['filename']) {
			$prefs = $this->get_upload_prefs((int) $_GET['dir']);
			if ($prefs == false)
				return '';

			if ($_GET['hash'] == md5($_GET['filename'] . $_GET['dir'] . serialize($this->EE->config->config['site_url']) . 'del')) {
				$this->EE->load->model('file_model');

				if ($_GET['dir']) {
					// Dont't delete, you must confirm the form! TODO.
					// $this->EE->file_model->delete_raw_file($_GET['filename'], $_GET['dir']);
				}
			}
			exit;
		}


		//	(2) zobrazit
		//--------------------------------------------------------------------	

		if (isset($_GET['entry_id']) AND isset($_GET['dir']) AND isset($_GET['field_id'])) {
			$this->EE->load->library('filemanager');

			$prefs = $this->get_upload_prefs((int) $_GET['dir']);
			if ($prefs == false)
				return '';

			if (isset($_SESSION['Ft_uploader']['cache'][$_GET['entry_id']][$_GET['field_id']])) {
				$cache = $_SESSION['Ft_uploader']['cache'][$_GET['entry_id']][$_GET['field_id']];

				$data = array();

				if (isset($cache['file'])) {
					foreach ($cache['file'] as $f => $v) {
						$data[] = array(
							"file" => $cache['file'][$f],
							"label_1" => $cache['label_1'][$f],
							"label_2" => $cache['label_2'][$f],
							"label_3" => $cache['label_3'][$f],
							"label_4" => $cache['label_4'][$f],
							"label_5" => $cache['label_5'][$f],
						);
					}
				}
			} else {
				//$prefs = $prefs->server_path; url, allowed_types

				$query = $this->EE->db->query("SELECT * FROM exp_channel_data WHERE entry_id='" . (int) $_GET['entry_id'] . "'");
				if ($query->num_rows == 0)
					return '';
				$data = ((array) $query->row());
				$data = ($data['field_id_' . $_GET['field_id']]);
				$data = @unserialize($data);
			}


			if (!$data)
				return '';
			if (!is_array($data))
				return '';

			$files = array();

			foreach ($data as $d) {
				if (preg_match('/{filedir_([0-9]+)}/', $d['file'], $matches)) {
					$filedir = $matches[1];
					$filename = str_replace($matches[0], '', $d['file']);
				}

				$thumb_info = $this->EE->filemanager->get_thumb($filename, $filedir);

				$info = array(
					"name" => $filename,
					"size" => filesize($prefs->server_path . $filename),
					"type" => mime_content_type($prefs->server_path . $filename),
					"url" => $prefs->url . $filename,
					"thumbnail_url" => is_file($thumb_info["thumb_path"]) ? $thumb_info["thumb"] : "/themes/cp_global_images/default.png",
					"delete_url" => $this->_create_delete_url($filedir, $filename),
					"delete_type" => "GET",
					"text_1" => @$d['label_1'],
					"text_2" => @$d['label_2'],
					"text_3" => @$d['label_3'],
					"text_4" => @$d['label_4'],
					"text_5" => @$d['label_5'],
				);

				$files[] = $info;
			}

			/*
			  header('Pragma: no-cache');
			  header('Cache-Control: private, no-cache');
			  header('Content-Disposition: inline; filename="files.json"');
			  header('X-Content-Type-Options: nosniff');
			  header('Content-type: application/json');
			 */

			echo '[' . json_encode($files) . ']';
			exit;
		}
		exit;
	}

	function _upload_file($key) {

		$this->EE->load->library('filemanager');
		$data = $this->EE->filemanager->upload_file($key, 'ft_uploader_file');

		if (@$data["error"]) {
			$data["name"] = $_FILES['ft_uploader_file']['name'];
			$data["size"] = filesize($_FILES['ft_uploader_file']['tmp_name']);
			$data["error"] = strip_tags($data["error"]);

			//header('Content-type: application/json');
			echo '[' . json_encode($data) . ']';
			exit;
		} else {
			/*
			  [name] => 01 zjednodušenie.jpg
			  [size] => 630820
			  [type] => image/jpeg
			  [url] => /develop/fileupload/example/files/01%20zjednodus%CC%8Cenie.jpg
			  [thumbnail_url] => /develop/fileupload/example/thumbnails/01%20zjednodus%CC%8Cenie.jpg
			  [delete_url] => /develop/fileupload/example/upload.php?file=01%20zjednodus%CC%8Cenie.jpg
			  [delete_type] => DELETE
			 */

			$info = array(
				"name" => $data['file_name'],
				"size" => filesize($data['rel_path']),
				"type" => $data['mime_type'],
				"url" => $data['upload_directory_prefs']['url'] . $data['file_name'],
				"thumbnail_url" => @$data['file_thumb'],
				"delete_url" => $this->_create_delete_url($key, $data['file_name']),
				"delete_type" => "GET",
				"label_1" => "label1",
				"label_2" => "label2"
			);

			/*
			  header('Pragma: no-cache');
			  header('Cache-Control: private, no-cache');
			  header('Content-Disposition: inline; filename="files.json"');
			  header('X-Content-Type-Options: nosniff');
			  header('Content-type: application/json');
			 */

			echo '[' . json_encode($info) . ']';
			exit;
		}
	}

	function get_upload_prefs($id) {
		$iQuery = $this->EE->db->query("SELECT * FROM exp_upload_prefs WHERE id='" . (int) $id . "'");
		if ($iQuery->num_rows) {
			return $iQuery->row();
		} else {
			return false;
		}
	}

	//generuj linku potrebnu pre zmazanie prispevku

	function _create_delete_url($dirname, $filename) {
		if (defined('BASE')) {
			return str_replace(AMP, '&', BASE) . '&C=addons_modules&M=show_module_cp&module=uploader&method=do_upload_file&filename=' . $filename . '&dir=' . $dirname . '&hash=' . md5($filename . $dirname . serialize($this->EE->config->config['site_url']) . 'del');
		} else {
			return $this->EE->functions->create_url('/') . '?ACT=' . @$_GET['ACT'] . '&filename=' . $filename . '&dir=' . $dirname . '&hash=' . md5($filename . $dirname . serialize($this->EE->config->config['site_url']) . 'del');
		}
	}

}

//END Class
