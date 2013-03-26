<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Hyperlink Class - by KREA SK s.r.o.
 *
 * @package		Hyperlink
 * @author		KREA SK s.r.o.
 * @copyright	Copyright (c) 2012, KREA SK s.r.o.
 * @link		http://www.krea.com/docs/hyperlink
 * @since		Version 1.0.0
 */
class Hyperlink {

	/**
	 * Version
	 *  
	 * @access public
	 * @var string
	 */
	var $version = '1.0.0';

	/*	 * ********************************** FUNCTIONS LIST ***************************************** */

	/**
	 * Contruct
	 *  
	 * @access public
	 * @return void
	 */
	function Hyperlink() {
		$this->EE = &get_instance();

		// Create addon_name from class name
		$this->addon_name = strtolower(__CLASS__);
	}

	/**
	 * Contruct
	 *  
	 * @access public
	 * @return void
	 */
	function __construct() {
		return $this->Hyperlink();
	}
	
	/**
	 * ACTION: take screenshot callback
	 *  
	 * @access public
	 * @return void
	 */
	function hyperlink_take_screenshot_callback() {
		//disable caching trick
		if (session_id() == "")
			session_start();

		$this->EE->lang->loadfile('hyperlink');
		$this->EE->load->library('hyperlink_lib');
		$queue_list = $this->EE->hyperlink_lib->load_queue_list();

		foreach ($queue_list as $row) {
			$this->EE->hyperlink_lib->take_screenshot($row['hyperlink_id'], $row['screenshot_id']);
		}

		$vars = array(
			"queue_list" => $this->EE->hyperlink_lib->load_queue_list(),
			"refresh_url" => $this->EE->functions->create_url('/') . '?ACT=' . @$_GET['ACT'],
		);

		$this->EE->load->helper('text');

		echo $this->EE->load->view('queue_list', $vars, TRUE);
		exit;
	}

	/**
	 * ACTION: check URL status
	 *  
	 * @access public
	 * @return void
	 */
	function hyperlink_check_url_status() {
		//disable caching trick
		if (session_id() == "")
			session_start();

		if (isset($_GET['hyperlink_id'])) {
			$row = $this->EE->db->select('*')->from('exp_hyperlink')
					->where('hyperlink_id', (int) $_GET['hyperlink_id'])
					->get()
					->row_array();

			$this->EE->load->library('hyperlink_lib');

			$status = $this->EE->hyperlink_lib->get_http_status($row["hyperlink_url"]);
			$this->EE->db->update('exp_hyperlink', array('hyperlink_http_status' => $status, 'hyperlink_http_status_date' => $this->EE->localize->now), 'hyperlink_id = ' . (int) $row["hyperlink_id"]);

			if ((int) $status < 200 || (int) $status >= 400) {
				if (
						$row["entry_id"]
						&& $this->EE->hyperlink_lib->settings['schedule_validation_of_links']['schedule']
						&& $this->EE->hyperlink_lib->settings['schedule_validation_of_links']['change_status']
				) {
					$this->EE->db->update(
							'exp_channel_titles', array(
						'status' => $this->EE->hyperlink_lib->settings['schedule_validation_of_links']['change_status']
							), 'entry_id = ' . (int) $row["entry_id"]
					);
				}
			}

			echo json_encode(array("status" => $status, "index" => (int) @$_GET['index']));
			exit;
		}
	}

	/**
	 * ACTION: check 
	 *  
	 * @access public
	 * @return void
	 */
	function hyperlink_schedule_validation_of_links() {
		$this->EE->load->library('hyperlink_lib');
		if (!$this->EE->hyperlink_lib->settings['schedule_validation_of_links']['schedule']) {
			return false;
		}

		$query = $this->EE->db->select('*')
				->from('exp_hyperlink')
				->where('site_id', $this->EE->config->item('site_id'))
				->order_by('hyperlink_http_status_date', 'ASC')
				->limit($this->EE->hyperlink_lib->settings['schedule_validation_of_links']['batch_size'])
				->get()
				->result_array();

		$this->EE->load->library('hyperlink_lib');

		$counter_valid = 0;
		$counter_invalid = 0;

		foreach ($query as $row) {
			$status = $this->EE->hyperlink_lib->get_http_status($row["hyperlink_url"]);
			$this->EE->db->update('exp_hyperlink', array('hyperlink_http_status' => $status, 'hyperlink_http_status_date' => $this->EE->localize->now), 'hyperlink_id = ' . (int) $row["hyperlink_id"]);

			if ((int) $status < 200 || (int) $status >= 400) {
				if (
						$row["entry_id"]
						&& $this->EE->hyperlink_lib->settings['schedule_validation_of_links']['schedule']
						&& $this->EE->hyperlink_lib->settings['schedule_validation_of_links']['change_status']
				) {
					$this->EE->db->update(
							'exp_channel_titles', array(
						'status' => $this->EE->hyperlink_lib->settings['schedule_validation_of_links']['change_status']
							), 'entry_id = ' . (int) $row["entry_id"]
					);
				}
				$counter_invalid++;
			} else {
				$counter_valid++;
			}
		}

		//results

		$this->EE->lang->loadfile('hyperlink');

		$data = array('title' => $this->EE->lang->line('title_schedule_validation_of_links'),
			'heading' => $this->EE->lang->line('title_schedule_validation_of_links'),
			'content' =>
			sprintf($this->EE->lang->line('msg_schedule_validation_valid_links'), $counter_valid) . BR .
			sprintf($this->EE->lang->line('msg_schedule_validation_invalid_links'), $counter_invalid) . BR .
			sprintf($this->EE->lang->line('msg_schedule_validation_tested_links'), $counter_valid + $counter_invalid) . BR .
			sprintf($this->EE->lang->line('msg_schedule_validation_batch_size'), $this->EE->hyperlink_lib->settings['schedule_validation_of_links']['batch_size'])
				,
		);

		$this->EE->output->show_message($data);
	}

	/**
	 * ACTION: check 
	 *  
	 * @access public
	 * @return void
	 */
	function hyperlink_click() {
		//prevent caching
		if (session_id() == "") {
			session_start();
		}

		if (isset($_GET['hyperlink_id'])) {
			$row = $this->EE->db->select('hyperlink_url, hits')->from('exp_hyperlink')
					->where('hyperlink_id', (int) $_GET['hyperlink_id'])
					->get()
					->row_array();

			$hits = $row['hits'] + 1;
			$this->EE->db->update('exp_hyperlink', array("hits" => $hits), 'hyperlink_id = ' . (int) $_GET['hyperlink_id']);

			if (@$row["hyperlink_url"]) {
				header("Location: " . $row["hyperlink_url"]);
				exit;
			}
		}

		header("HTTP/1.0 404 Not Found");
		header("Location: /404");
		die('Wrong request');
		exit;
	}

}

//END Class
