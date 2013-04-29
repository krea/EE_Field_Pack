<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Elements Class - by KREA SK s.r.o.
 *
 * @package		Content_elements
 * @author		KREA SK s.r.o.
 * @copyright	Copyright (c) 2012, KREA SK s.r.o.
 * @since		Version 1.0.0
 */
 
define( "HYPERLINK_TYPE_FIELDTYPE", 			1 );
define( "HYPERLINK_TYPE_MATRIX", 				2 );
define( "HYPERLINK_TYPE_CONTENT_ELEMENT", 		3 );
define( "HYPERLINK_TYPE_LOW_VARIABLE", 			4 );
define( "HYPERLINK_TYPE_EP_BETTER_WORKFLOW", 	5 );
 
class Hyperlink_lib { 

	var $version	= '1.0.0';
	var $cache 		= array();
	var $errors		= array();
	
	var $default_settings = array(
	
		//
		//	Generate Link Preview
		//
		
		'screenshot_service'	=> '',	
		'screenshot_services' => array(
			''		=> array(
				'service_name' 		=> 'None',
			),
			'GrabzIt' 	=> array(
				'service_name' 		=> 'GrabzIt',
				'service_package'	=> 'free',		
				'api_key' 			=> '',
				'api_secret' 		=> '',	
				'package'			=> 'free',						
				'browser_width'		=> 800,
				'browser_height'	=> 800,
				'image_width'		=> 200,
				'image_height'		=> 200,				
			)				
		),
		'screenshot_dir'	=> '',		

		//
		//	Link Valiadation
		//	
		
		'publish_entry_with_invalid_links'	=> FALSE,			
		'schedule_validation_of_links'	=> array(
			'schedule' 		=> FALSE,
			'batch_size'	=> 50,
			'change_status'	=> ''
		),
		
		//
		//	Others
		//		
		
		'default_socket_timeout'		=> 10,
	);
	
	var $grabzit_packages = array(
		'free' => 
			array(
				'name' 		=> 'Free',
				'max_width' 	=> '200',
				'max_height' 	=> '200',
				'screenshot_per_month' 	=> '1000',	
				'no_watermark' 	=> FALSE,	
				'formats' 	=> array('jpg'),																			
			),
		'entry' => 	
			array(
				'name' 		=> 'Entry',
				'max_width' 	=> '400',
				'max_height' 	=> '400',
				'screenshot_per_month' 	=> '5000',	
				'no_watermark' 	=> TRUE,	
				'formats' 	=> array('jpg','gif','png'),																			
			),
		'professional' => 	
			array(
				'name' 		=> 'Professional',
				'max_width' 	=> '1000',
				'max_height' 	=> '1000',
				'screenshot_per_month' 	=> '50000',	
				'no_watermark' 	=> TRUE,	
				'formats' 	=> array('jpg','gif','png'),																			
			),
		'bussiness' => 									
			array(
				'name' 		=> 'Business',
				'max_width' 	=> '3000',
				'max_height' 	=> '3000',
				'screenshot_per_month' 	=> '250000',	
				'no_watermark' 	=> TRUE,	
				'formats' 	=> array('jpg','gif','png'),																			
			),								
		'enterprise' => 									
			array(
				'name' 		=> 'Enterprise',
				'max_width' 	=> '3000',
				'max_height' 	=> '3000',
				'screenshot_per_month' 	=> '500000',	
				'no_watermark' 	=> TRUE,	
				'formats' 	=> array('jpg','gif','png'),																			
			),													
	);	
	
/**
 * Constructor
 *
 * @return	void
 */	
	
	function __construct()
	{
		$this->EE = &get_instance();
		$this->EE->load->helper('text', 'security');
		
		$this->_load_settings();
	}
/**
 * Load Settings
 *
 * @return boolean/array
 * @access private 
 */	

	function _load_settings( $site_id = null )
	{
		if ($site_id == null)
		{
			$site_id = $this->EE->config->item('site_id');
		}
	
		$query = $this->EE->db
			->select('settings')
			->from('exp_hyperlink_settings')
			->where('site_id', $site_id)
			->get();
		
		if ($query->num_rows == 0)
		{
			$this->settings = $this->default_settings;
		
			$data = array(
				'site_id'	=> $this->EE->config->item('site_id'),
				'settings'  => base64_encode(serialize($this->settings))
			);
			$this->EE->db->insert('exp_hyperlink_settings', $data);
		}
		else
		{	
			$this->settings = unserialize(base64_decode($query->row()->settings));
		}
		
		//proportionaly corrections
		
		$this->settings['screenshot_services']['GrabzIt']['browser_height'] = 
			round (($this->settings['screenshot_services']['GrabzIt']['image_height'] / $this->settings['screenshot_services']['GrabzIt']['image_width'])
			* $this->settings['screenshot_services']['GrabzIt']['browser_width']);
		
		return $this->settings;
	}
	
/**
 * Catch error
 *
 * @return boolean/array
 */	
	
	function catch_error()
	{
		if (count($this->errors))
		{
			return $this->errors;
		}
		else
		{
			return false;
		}
	}
	
/**
 * Get Upload Preferences
 *
 * @return	array
 */
	 
	function upload_preferences()
	{
		if (!isset($this->cache['upload_preferences']))
		{	
			$this->cache['upload_preferences'] = array();
		
			$this->EE->db->from('upload_prefs');
			$this->EE->db->order_by('site_id','asc');				
			$this->EE->db->order_by('name','asc');	
			
			foreach	($this->EE->db->get()->result_array() as $row)
			{
				$this->cache['upload_preferences'][$row["id"]] = $row;	
			}
		}
		
		return $this->cache['upload_preferences'];
	}
	
/**
 * Take screenshot
 *
 * @return	
 */
 	
	function take_screenshot( $hyperlink_id, $screenshot_id = FALSE )
	{
		//**
		//**	Get info about link
		//**
	
		$query = $this->EE->db
			->select('*')
			->from('exp_hyperlink')
			->where('hyperlink_id', $hyperlink_id)
			->where('hyperlink_http_status >=', 200)
			->where('hyperlink_http_status <', 400)
			->get();
	
		if ($query->num_rows == 0)
		{ 
			return FALSE;
		}
	
		$hyperlink = $query->row_array();
	
		//**
		//**	Load settings
		//**
	
		$this->_load_settings($hyperlink["site_id"]);
	
		//**
		//**	No Dir, No Screenshot
		//**
	
		if (!$this->settings['screenshot_dir'])	
		{
			return FALSE;
		}
		
		//**
		//**	No Service, No Screenshot
		//**				
		
		if (!$this->settings['screenshot_service'])
		{
			return FALSE;
		}
		
		//**	---------------------------------------------
		//**	GrabzIt
		//**	---------------------------------------------				
		
		if ($this->settings['screenshot_service'] == 'GrabzIt')
		{		
			include_once("grabzit/GrabzItClient.class.php");						
			
			//if EXPIRED, get new request	
			
			if ($hyperlink['screenshot_status'] == 'expired')
			{
				$screenshot_id = false;
			}		
						
			try
			{	
				//one connect is ok
			
				global $__GRABZIT_CLIENT__;
				
				if (!isset($__GRABZIT_CLIENT__))
				{
					$__GRABZIT_CLIENT__ = new GrabzItClient(
						$this->settings['screenshot_services']['GrabzIt']['api_key'],
						$this->settings['screenshot_services']['GrabzIt']['api_secret']
					);
				}
				
				if ( !$screenshot_id )
				{
					$screenshot_id = (string)$__GRABZIT_CLIENT__->TakePicture($hyperlink['hyperlink_url'], 
							null, 			//callbackurl
							null, 
							$this->settings['screenshot_services']['GrabzIt']['browser_width'],
						 	$this->settings['screenshot_services']['GrabzIt']['browser_height'],
							$this->settings['screenshot_services']['GrabzIt']['image_width'],
						 	$this->settings['screenshot_services']['GrabzIt']['image_height']
						);
						
					if ($screenshot_id)
					{
	    				$data = array(
	    					'screenshot_id' 		=> $screenshot_id,
							'screenshot_status' 	=> 'send',  
						);
					}
					else
					{
	    				$data = array(
	    					'screenshot_error' 		=> 'GrabzIt request failed',
							'screenshot_status' 	=> 'error', 
						);
					}
					$this->EE->db->update('exp_hyperlink', $data, 'hyperlink_id = '.(int)$hyperlink_id);
				}
				
				$screenshotTaked = 0;
				
				//get status
		
				if ($screenshot_id)
				{
					$status = $__GRABZIT_CLIENT__->GetStatus($screenshot_id);
															
					if ($status->Processing)
					{											
						$data = array(
							'screenshot_id' 		=> $screenshot_id,
							'screenshot_status' 	=> 'processing',  
						);
						$this->EE->db->update('exp_hyperlink', $data, 'hyperlink_id = '.(int)$hyperlink_id);							
					}
					
					if ($status->Cached)
					{	
						$data = array(
							'screenshot_id' 		=> $screenshot_id,
							'screenshot_status' 	=> 'cached',  
						);
						$this->EE->db->update('exp_hyperlink', $data, 'hyperlink_id = '.(int)$hyperlink_id);
						$screenshotTaked = 1;
					}
					
					if ($status->Expired)
					{
			    			$data = array(
							'screenshot_status' 	=> 'expired',
							'screenshot_error' 	=> $status->Message,    
						);
						$this->EE->db->update('exp_hyperlink', $data, 'hyperlink_id = '.(int)$hyperlink_id);				
						
						//log errors
					    	$this->errors[] = $status->Message;
					}					
				}

				if ($screenshotTaked)
				{
					$screenshot 		= $__GRABZIT_CLIENT__->GetPicture($screenshot_id);
					$screenshot_name	= 'screenshot-grabzit-'.uniqid().'.jpg';
					$screenshot_dir		= $this->settings['screenshot_dir'];
					
					if ( $this->save_image( $screenshot_name, $screenshot, $screenshot_dir) )
					{
			    		$data = array(
			    			'screenshot_name'		=> $screenshot_name,
			    			'screenshot_dir'		=> $screenshot_dir,			    				
							'screenshot_status' 	=> 'ok',
							'screenshot_error' 		=> null,    
						);
						$this->EE->db->update('exp_hyperlink', $data, 'hyperlink_id = '.(int)$hyperlink_id);
					}	
				}		
			}
			
			catch (Exception $e)
			{
	    			$this->errors[] = 'Caught exception: '.  $e->getMessage();
	    			
	    			$data = array(
					'screenshot_status' => 'error',
					'screenshot_error' => $e->getMessage(),    
				);
				
				$this->EE->db->update('exp_hyperlink', $data, 'hyperlink_id = '.(int)$hyperlink_id);
				    			
	    		return FALSE;
			}
		}	
	}

	
/**
 * Take picture
 *
 * @return	
 */
 	/*
	function take_picture($target_url, $api_key = '', $api_secret = '', $callback = null)
	{
		include_once("grabzit/GrabzItClient.class.php");
		
		try
		{
			$grabzIt = new GrabzItClient($api_key, $api_secret, $target_url);
			$id = $grabzIt->TakePicture($target_url, $callback, null, 1024, 768, 200, 200);
			$this->grabzit_id = $id;
						
			$waitingToProcess = 1;
			
			while ($waitingToProcess)
			{			
				$status = $grabzIt->GetStatus($id);
	
				if ($status->Processing)
				{
					//echo "processing";
					sleep(1);
				    //screenshot has not yet been processed
				}
				
				if ($status->Cached)
				{	
					//echo "cached";
				    //screenshot is still cached by GrabzIt
				    $waitingToProcess = 0;
				}
				
				if ($status->Expired)
				{
					//echo "expired";
				    $this->errors[] = $status->Message;
				    return FALSE;
				}
				
				$picture = $grabzIt->GetPicture($id);				
			}

		}
		catch (Exception $e)
		{
    		$this->errors[] = 'Caught exception: '.  $e->getMessage();
    		return FALSE;
		}
	
		return $picture;	
	}
	*/
	
/**
 * Save image
 *
 * @param binary raw_data
 * @param string filename
 * @param int directory_id
 * @return	
 */	
	
	function save_image( $file_name, $raw_data, $dir_id = 0 )
	{
		$directories = $this->upload_preferences();
		
		if ( !isset( $directories[$dir_id]) )
		{
			$this->errors[] = 'Wrong target directory';
			return FALSE;
		}
	
		//1. Prepare image to directory
		
		$image_path = rtrim($directories[$dir_id]['server_path'],'/').'/'.$file_name;
		
		if (is_file($image_path))
		{
			unlink($image_path);
		}
		
		//2. Save original
		
		file_put_contents($image_path, $raw_data);
		@chmod($image_path, 0777);
		
		//3. Fetch information
		
		$file = array();
		
		$file['name']			= $file_name;
		$file['dir_id']			= $dir_id;
		$file['dir_name']		= $directories[$dir_id]['server_path'];
		$file['rel_path']		= $image_path;		
		$file['short_name'] 	= ellipsize($file['name'], 16, .5);
		$file['mime'] 			= @mime_content_type($file['name']);
		$file['size'] 			= filesize($image_path);

		if (function_exists('getimagesize')) 
		{
			if ($D = @getimagesize($file['relative_path'].$file['name']))
			{
				$file['dimensions'] = $D[3];
			}
		}
		else
		{
			// We can't give dimensions, so return a blank string
			$file['dimensions'] = '';
		}
		
		//4. Database
		
		$this->EE->load->library('filemanager');

		if ( method_exists($this->EE->filemanager,'save_file') ) //EE 2.1.5+
		{							
			$image_dimensions = $this->EE->filemanager->get_image_dimensions($file['rel_path']);
			
			$file_data = array(
				'upload_location_id'	=> $file['dir_id'],
				'site_id'				=> $this->EE->config->item('site_id'),
				'rel_path'				=> $file['rel_path'], // this will vary at some point
				'mime_type'				=> $file['mime'],
				'file_name'				=> $file['name'],
				'file_size'				=> $file['size'],
				'uploaded_by_member_id'	=> $this->EE->session->userdata('member_id'),
				'modified_by_member_id' => $this->EE->session->userdata('member_id'),
				'file_hw_original'		=> $image_dimensions['height'] . ' ' . $image_dimensions['width'],
				'upload_date'			=> $this->EE->localize->now,
				'modified_date'			=> $this->EE->localize->now
			);
			
			if (version_compare(APP_VER, '2.2.0', '>='))
			{		
				$file_data['dimensions'] = $this->EE->db->select("*")
					->from('exp_file_dimensions')
					->where("upload_location_id", $file_data['upload_location_id'])
					->get()
					->row_array();
			}
	
			//$prefs['dimensions']
			$saved = $this->EE->filemanager->save_file($file['rel_path'], $file['dir_id'], $file_data, FALSE);
		}
		else
		{
			$saved['status'] = is_file($image_path);
			if (!$saved['status'])
			{
				$this->errors[] = "Directory is not writable.";
			}
		}
		
		if ( ! $saved['status'])
		{
			$this->errors[] = $saved['message'];
		}
		else
		{
			return TRUE;
		}	
	}
	
/**
 * Get HTTP status
 *
 * @param string URL
 * @return void	
 */	
	
	function get_http_status($url)
	{
		$curl = curl_init(); 
		
		$header[] = "Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
		$header[] = "Cache-Control: max-age=0";
		$header[] = "Connection: keep-alive";
		$header[] = "Keep-Alive: 300";
		$header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
		$header[] = "Accept-Language: en-us,en;q=0.5";
		$header[] = "Pragma: ";		
		
		curl_setopt($curl, CURLOPT_URL,           	$url); 
		curl_setopt($curl, CURLOPT_HEADER,        	true); 
		curl_setopt($curl, CURLOPT_HTTPHEADER, 		$header);
		curl_setopt($curl, CURLOPT_NOBODY,        	true); 
		curl_setopt($curl, CURLOPT_RETURNTRANSFER,	true); 
		curl_setopt($curl, CURLOPT_TIMEOUT,       	$this->settings['default_socket_timeout']); 
		curl_setopt($curl, CURLOPT_USERAGENT,		'Mozilla/5.0 (Windows; U; MSIE 7.0; Windows NT 6.0; en-US)'); 
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION,	true);
		
		$headers = curl_exec($curl); 
		$headers = explode("\n", $headers); 
		
		$status = -1; //default
		
		foreach ( $headers as $k=>$v )
		{
			if (substr($v, 0, 5) == "HTTP/")
			{
				$params = explode(" ", $v );
				
				$status = (int)@$params[1];
			}
		} 
    
    		return $status;	
	}

/**
 * Load queue list
 *
 * @param string URL
 * @return void	
 */
	
	function load_queue_list()
	{
		$queue_list = $this->EE->db->select('*')
			->from('exp_hyperlink')
			->where_in('screenshot_status', array('take', 'processing'))
			->order_by('hyperlink_id', 'ASC')
			->where('hyperlink_http_status >=', 200)
			->where('hyperlink_http_status <', 400)
			->order_by('hyperlink_id', 'ASC')
			->get()
			->result_array();
		
		return $queue_list;
	}

	public function define_theme_url($addon_name = 'content_elements') {

		if (defined('HYPERLINK_THEME_URL'))
			return HYPERLINK_THEME_URL;

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

		define('HYPERLINK_THEME_URL', $theme_url . $addon_name . '/');

		return HYPERLINK_THEME_URL;
	}
}
