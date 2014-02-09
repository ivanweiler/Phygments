<?php
namespace Phygments\Python\Re;

//just an idea, but i think it will complicate everything

class MatchObject
{
	public function __construct($matches, $start=0)
	{
		$this->_matches = $matches;
		$this->_start = $start;
	}
	
	public function start()
	{
		return $this->_start;
	}
	
	public function end()
	{
		//($matches[0][1]==-1) ? -1 : $matches[0][1]+strlen($matches[0][0]);
		
		return $this->_start + strlen($this->_text);
	}
	
	
	public function group($arg)
	{
		return $this->_matches[$arg];
	}
	
	
	public function groups()
	{
		return $this->_matches;
	}
	
}