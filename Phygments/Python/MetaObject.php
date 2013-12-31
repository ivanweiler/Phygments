<?php
namespace Phygments\Python;
abstract class MetaObject
{
	protected $__metaclass__;
	protected  static $_initialized = false;
	
	final public function __construct()
	{
		//once, but per "this" class?
		if(!self::$_initialized) {
			//get and pass arguments
			$__metaclass__->__new__();
			$__metaclass__->__init__();
			
			self::$_initialized = true;
		}
		
		$__metaclass__->__call__();
		
		$this->_construct();
	}
	
	public function _construct()
	{
	}

}