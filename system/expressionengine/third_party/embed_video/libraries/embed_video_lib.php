<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Embed video - by KREA SK s.r.o.
 *
 * @package		Embed video
 * @author		KREA SK s.r.o. with contributions from Nathan Pitman (Nine Four Ltd)
 * @copyright	Copyright (c) 2014, KREA SK s.r.o.
 * @link		http://www.krea.com/docs/content-elements
 * @since		Version 1.3
 */
class Embed_Video_Lib {

	/**
	 * Constructor
	 *
	 * @return void
	 */
	function __construct() {
		$this->EE = &get_instance();
	}

	/**
	 * Recursive html_entity_decode function.
	 *
	 * @param mixed $data
	 * @return mixed $data
	 */
	public function recursive_html_entity_decode(&$data) {

		if (is_array($data)) {
			foreach ($data as &$data_v) {
				$this->recursive_html_entity_decode($data_v);
			}
		} else {
			$data = html_entity_decode($data, ENT_QUOTES);
		}

		return $data;
	}

	/**
	 * Return whether url is valid embed link.
	 *
	 * @param string $url
	 * @return int
	 */
	public function is_valid_link($url = '') {

		$parsed_url = parse_url($url);
		$parsed_query = array();

		if (!empty($parsed_url['query']))
			parse_str($parsed_url['query'], $parsed_query);

		if (empty($parsed_url['host']))
			$parsed_url['host'] = NULL;

		// Youtube link
		if ((preg_match('/^(?:www\.)?youtube\.com/', $parsed_url['host'])) OR (preg_match('/^(?:www\.)?youtu\.be/', $parsed_url['host']))) {

			if (preg_match('/embed\/[a-zA-Z0-9]+/', $parsed_url['path']))
				return 1;

			if (empty($parsed_query['v']))
				return 0;
		} else
			return -1;

		return 1;
	}

	public function get_video_id($url) {

		if(!$this->is_valid_link($url))
			return FALSE;

		$parsed_url = parse_url($url);
		$parsed_query = array();

		if (!empty($parsed_url['query']))
			parse_str($parsed_url['query'], $parsed_query);

		if (!empty($parsed_query['v']))
			return $parsed_query['v'];

		// Get video ID
		$embed_id = '';
		if (preg_match('/embed\/[a-zA-Z0-9]+/', $parsed_url['path'], $embed_id)) {
			return str_replace('embed/', '', current($embed_id));
		}
	}

	public function get_embed_url($url) {

		$parsed_url = parse_url($url);
		$parsed_query = array();

		if (!empty($parsed_url['query']))
			parse_str($parsed_url['query'], $parsed_query);

		if (empty($parsed_url['host']))
			$parsed_url['host'] = NULL;

		// Youtube link
		if (preg_match('/^(?:www\.)?youtube\.com/', $parsed_url['host'])) {

			$id = '';

			$embed_pattern = '/embed\/[a-zA-Z0-9]+/';

			if (preg_match($embed_pattern, $parsed_url['path'], $matches))
				$id = str_replace('embed/', '', current($matches));

			if (!empty($parsed_query['v']))
				$id = $parsed_query['v'];

			if (empty($id))
				return 0;

			return 'https://www.youtube.com/embed/' . $id;
		} elseif (preg_match('/^(?:www\.)?youtu\.be/', $parsed_url['host'])) {
			$id = $parsed_url['path'];
			return 'https://www.youtube.com/embed/' . $id;

		} else
			return -1;
	}

	/**
	 * Return array of files from $data.
	 *
	 * @param mixed $data
	 * @return array
	 */
	public function get_upload_files($data) {

		$files = array();

		// If files saved
		if (isset($data['files']['dir'])) {
			//loop files
			foreach ($data['files']['dir'] as $file_id => $dir_id) {
				//only if directory is valid
				if ($data['files']['dir'][$file_id]) {
					//load thumb
					if (version_compare(APP_VER, '2.2.0', '<')) {
						$upload_directory_data = $this->EE->db->query("SELECT * FROM exp_upload_prefs WHERE id='" . (int) $data['files']['dir'][$file_id] . "'");
						$upload_directory_server_path = $upload_directory_data->row('server_path');
						$upload_directory_url = $upload_directory_data->row('url');

						if (file_exists($upload_directory_server_path . '_thumbs/thumb_' . $data['files']['name'][$file_id])) {
							$thumb = $upload_directory_url . '_thumbs/thumb_' . $data['files']['name'][$file_id];
						} else {
							$thumb = PATH_CP_GBL_IMG . 'default.png';
						}

						$url = rtrim($upload_directory_url, '/') . '/' . $data['files']['name'][$file_id];
						$server_path = rtrim($upload_directory_server_path, '/') . '/' . $data['files']['name'][$file_id];
					} else {
						$this->EE->load->library('filemanager');
						$thumb_info = $this->EE->filemanager->get_thumb($data['files']['name'][$file_id], $data['files']['dir'][$file_id]);
						$thumb = $thumb_info['thumb'];
						$directory = $this->EE->filemanager->directory($data['files']['dir'][$file_id], FALSE, TRUE);
						$url = rtrim($directory['url'], '/') . '/' . $data['files']['name'][$file_id];
						$server_path = rtrim($directory['server_path'], '/') . '/' . $data['files']['name'][$file_id];
					}

					$files[] = array(
						'dir' => $data['files']['dir'][$file_id],
						'name' => $data['files']['name'][$file_id],
						'caption' => $data['files']['caption'][$file_id],
						'thumb' => $thumb,
						'url' => $url,
						'server_path' => $server_path,
					);
				}
			}
		}

		return $files;
	}

	public function define_theme_url($addon_name = 'content_elements') {

		if (defined('EMBED_VIDEO_THEME_URL'))
			return EMBED_VIDEO_THEME_URL;

		if (defined('URL_THIRD_THEMES') === TRUE) {
			$theme_url = URL_THIRD_THEMES;
		} else {
			$theme_url = $this->EE->config->item('theme_folder_url') . 'third_party/';
		}

		// Are we working on SSL?
		if (isset($_SERVER['HTTP_REFERER']) == TRUE AND strpos($_SERVER['HTTP_REFERER'], 'https://') !== FALSE) {
			$theme_url = str_replace('http://', 'https://', $theme_url);
		} elseif (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off') {
			$theme_url = str_replace('http://', 'https://', $theme_url);
		}

		$theme_url = str_replace(array('https://', 'http://'), '//', $theme_url);

		define('EMBED_VIDEO_THEME_URL', $theme_url . $addon_name . '/');

		return EMBED_VIDEO_THEME_URL;
	}

}