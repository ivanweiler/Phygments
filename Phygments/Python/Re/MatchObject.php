<?php
namespace Phygments\Python\Re;

//just an idea

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
		return $this->_start + strlen($this->_matches[0][0]);
	}
	
	public function group($arg)
	{
		return $this->_matches[0][$arg];
	}
	
	public function groups()
	{
		return $this->_matches;
	}
	
}