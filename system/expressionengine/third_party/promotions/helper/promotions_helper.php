<?php

function typography_time($time)
{
	$EE = &get_instance();
	
	return $EE->localize->set_human_time($time);
	
	/*
	return ($time < strtotime(date('Y-m-d')) OR $time > strtotime(date('Y-m-d').' + 1 day'))?date('M j', $EE->localize->set_localized_time($time)):date('g:i A', $EE->localize->set_localized_time($time));
	*/
}

function _helpdes_str_replace_first($search, $replace, $subject) {
    return implode($replace, explode($search, $subject, 2));
}

function make_clicable_links($text)
{
	$text = preg_replace('"(((f|ht){1}tp://)[-a-zA-Z0-9@:%_\+.~#?&//=]+)"',	
	'<a target="_blank" href="\\1">\\1</a>', $text);	
	$text = preg_replace('"([[:space:]()[{}])(www.[-a-zA-Z0-9@:%_\+.~#?&//=]+)"',	
	'\\1<a target="_blank" href="http://\\2">\\2</a>', $text);	
	$text = preg_replace('"([_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,3})"',	
	'<a href="mailto:\\1">\\1</a>', $text);
	
	return $text;
}

function campaign_status($c)
{
	$out = array();
	$EE = &get_instance();
	
	//paused, active, sheduled, ended
		
	if ($c['start_date'] > $EE->localize->now)
	{
		$out[] = '<span class="flg inactCmpgn">'.$EE->lang->line('campaigns_status_scheduled').'</span>';
	}	
	elseif ($c['end_date'] < $EE->localize->now)
	{
		$out[] = '<span class="flg inactCmpgn">'.$EE->lang->line('campaigns_status_ended').'</span>';
	}
	elseif ($c['start_date'] < $EE->localize->now && $c['end_date'] > $EE->localize->now)
	{
		$out[] = '<span class="flg actCmpgn">'.$EE->lang->line('campaigns_status_active').'</span>';
	}	
	
	//paused
	
	if ($c['paused'])
	{
		$out[] = '<span class="flg inactCmpgn">'.$EE->lang->line('campaigns_status_paused').'</span>';
	}	
	
	//draft
	
	if ($c['draft'])
	{
		$out[] = '<span class="flg drftCmpgn">'.$EE->lang->line('campaigns_status_draft').'</span>';
	}		
	
	//winners_announced

	if ($c['winners_announced'])
	{
		$out[] = '<span class="flg inactCmpgn">'.$EE->lang->line('campaigns_status_winners_announced').'</span>';
	}
	
	return implode(' ', $out);
}




