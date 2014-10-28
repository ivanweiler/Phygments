<?php
namespace Phygments\Python\Re;

use \Phygments\Python\Exception;

class MatchObject
{
	public function __construct($matches, $offset=0)
	{
		$this->_matches = $matches;
		$this->_offset = $offset;
	}
	
	public function start($arg=0)
	{
		if(!isset($this->_matches[$arg])) {
			throw new Exception\IndexError('No such group');
		}
		
		return $this->_matches[$arg][1];
	}
	
	public function end($arg=0)
	{
		if(!isset($this->_matches[$arg])) {
			throw new Exception\IndexError('No such group');
		}
				
		return $this->_matches[$arg][1] + strlen($this->_matches[$arg][0]);
	}
	
	public function group($arg=0)
	{
		if(!isset($this->_matches[$arg])) {
			throw new Exception\IndexError('No such group');
		}
				
		return $this->_matches[$arg][0];
	}
	
	public function groups()
	{
		$matches = array();
		foreach($this->_matches as $match) {
			$matches[] = $match[0];
		}
		
		return $matches;
	}
	
}