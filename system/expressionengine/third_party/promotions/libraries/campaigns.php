<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Campaigns Class
 *
 * @package   Content_campaigns
 * @author    Michal Varga
 * @copyright Copyright (c) 2012 KREA SK s.r.o.
 */
 
 
class Campaigns { 

	//----------------------------------------------------
	//	CONSTRUCT
	//----------------------------------------------------
	
	var $this = array();

	function Campaigns()
	{
		$this->EE = &get_instance();
	}
	
	//----------------------------------------------------
	//	LOAD BASE INFO ABOUT INSTALLED CAMPAIGNS
	//----------------------------------------------------
	
	function get_avaiable_campaigns()
	{
		//cached return
	
		if (isset($this->avaiable_campaigns))
		{
			return $this->avaiable_campaigns;
		}
	
		$this->avaiable_campaigns = array();
	
		foreach (glob(PATH_THIRD.'promotions/campaigns/*') as $campaign_dir)
		{
			//get campaign name
		
			preg_match('%campaigns/([^/]*)$%', $campaign_dir, $matches);
			
			if (isset($matches[1]))
			{
				$campaign_name = $matches[1];
			}
			else
			{
				continue;
			}		

			//load campaign
			
			$this->load($campaign_name);
			
			$this->avaiable_campaigns[] = array(
				"name"				=> $campaign_name,
				"title" 			=> $this->lang($campaign_name.'_campaign_name'),
				"version" 			=> @$this->$campaign_name->info["version"],
				"priority"			=> (int)@$this->$campaign_name->info["priority"],					
			);
		}
		
		//bubblesort
		
		foreach ($this->avaiable_campaigns as $k1=>$v1)
		{
			foreach ($this->avaiable_campaigns as $k2=>$v2)
			{
				if ($this->avaiable_campaigns[$k1]["priority"] < $this->avaiable_campaigns[$k2]["priority"])
				{
					$avaiable_campaigns_backup 	= $this->avaiable_campaigns[$k1];
					$this->avaiable_campaigns[$k1] = $this->avaiable_campaigns[$k2];
					$this->avaiable_campaigns[$k2] = $avaiable_campaigns_backup;
					unset($avaiable_campaigns_backup);
				}
			}
		}
					
		return $this->avaiable_campaigns;								
	}
	
	//----------------------------------------------------
	//	LOAD CAMPAIGN LANGUAGE FILE
	//----------------------------------------------------	
	
	function load_langfile($campaign_name)
	{
		//allready loaded
	
		if (isset($this->EE->lang->language[$campaign_name.'_campaign_name']))
		{
			return '';
		}	
		
		//load from directories
	
		$campaign_lang_custom 		= PATH_THIRD.'promotions/campaigns/'.$campaign_name.'/language/'.$this->EE->session->userdata["language"].'/lang.'.$campaign_name.'.php';
		$campaign_lang_default 	= PATH_THIRD.'promotions/campaigns/'.$campaign_name.'/language/english/lang.'.$campaign_name.'.php';
		
		if (is_file($campaign_lang_custom))
		{
			require($campaign_lang_custom);
		}
		elseif (is_file($campaign_lang_default))
		{
			require($campaign_lang_default);
		}
		else
		{
			//no language?
		
			die('File '.basename($campaign_lang_custom).' not found');
		}
		
		//merge to EE
		
		$this->EE->lang->language = array_merge($this->EE->lang->language, $lang);
	}

	//----------------------------------------------------	
	//	TRANSLATE LANG TERM 
	//----------------------------------------------------	
	
	function lang($term)
	{
		if (isset($this->EE->lang->language[$term]))
		{
			return $this->EE->lang->language[$term];
		}
		else
		{
			return $term;
		}
	}
	
	//----------------------------------------------------	
	//	LOAD CAMPAIGN 
	//----------------------------------------------------	
	
	function load($campaign_name)
	{
		//load language
		
		$this->load_langfile($campaign_name);
				
		if ($this->lang($campaign_name.'_campaign_name') == $campaign_name.'_campaign_name')
		{				
			die('Get name of campaign "'.$campaign_name.'" failed');
		}
		
		//load object
	
		if (isset($this->$campaign_name))
		{
			return $this->$campaign_name;
		}
	
		$campaign_class = ucfirst($campaign_name).'_campaign';
			
		if (!class_exists($campaign_class))
		{
			require(PATH_THIRD.'promotions/campaigns/'.$campaign_name.'/campaign.'.$campaign_name.'.php');				
		}
		
		$this->$campaign_name = new $campaign_class;
		$this->$campaign_name->name = $this->lang($campaign_name.'_campaign_name');
			
		return $this->$campaign_name;
	}
	
/**
 * Tool: parse variables (most usefull)
 *  
 * @access private
 */	
	
	function parse_variables($_tagdata, $vars)
	{	
	
		$output = ''; 
		$count = 0;
	
		//step by step				
		foreach ($vars as $list)
		{		
			$count++;
			
			//store input
			$tagdata = $_tagdata;		
				
			//kazdy riadok sa sklada z tagov
			foreach ($list as $tag => $value)
			{							
				//if is tag array (variable pairs) ...
				if (is_array($value))
				{	
					preg_match_all('~{'.$tag.'}(.*?){/'.$tag.'}~s', $tagdata, $matches);
					
					
					//change pattern $matches[0] to final $matches[1]						
					foreach ($matches[0] as $i => $match)
					{	
						//call recursion
						$pattern = $this->parse_variables($matches[1][$i], $value);
												
						//apply recursion
						$tagdata = str_replace($matches[0][$i], $pattern, $tagdata);		
					}
				}
				//... or parse single variables
				else
				{				
					$tagdata = str_replace('{'.$tag.'}', $value, $tagdata);			
				}	
			}
			
			//count
			$tagdata = str_replace('{count}', $count, $tagdata);
			$tagdata = str_replace('{cnt}', $count, $tagdata);
			$tagdata = str_replace('{total_count}', count($vars), $tagdata);		
			
			//conditions
			$conds = array();
			
			foreach ($list as $tag => $value)
			{
				if (is_array($value))
				{
					$conds[$tag] = count($value)?1:0;
				}
				else{
					$conds[$tag] = ($value)?$value:0;
				}
				$conds['count'] = $count;
				$conds['cnt'] = $count;
				$conds['total_count'] = count($vars);
				$conds['first'] = (int)$count===1?1:0;
				$conds['last'] = (int)$count===(int)count($vars)?1:0;
			}
			
			$output .= $this->EE->functions->prep_conditionals($tagdata, $conds);	
		}	
		
		//clean output from <ul>s
		$output = preg_replace('~<ul([^>])*'.'>\s*</ul>~s', '', $output);
		
		return $output;
	}		
}
