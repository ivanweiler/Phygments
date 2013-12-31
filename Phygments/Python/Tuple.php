<?php
//@author: Jelmer Schreuder

//http://forrst.com/posts/Tuples_for_PHP-O3A
//https://github.com/dhotson/bert-php/blob/master/classes/Bert/Tuple.php
class Tuple implements ArrayAccess, Iterator, Countable
{
	/**
	 * @var  array  has the same number of values as $tuple but contains a string for
	 *     each describing the type or class, use "mixed" as wildcard and "|" as "or"
	 */
	protected $prototype = array();

	/**
	 * @var  array  array representing the tuple
	 */
	protected $tuple = array();

	public function __construct($prototype, $tuple = array())
	{
		// Prototype may be just a length, in that case all possible values are of type "mixed"
		if (is_int($prototype))
		{
			$prototype = array_pad(array(), $prototype, 'mixed');
		}
		// Otherwise it must be an array
		elseif ( ! is_array($prototype))
		{
			throw new Exception('Tuple prototype must be either length or array.');
		}

		// set prototype, create empty tuple and add given values
		$this->prototype  = $prototype;
		$this->tuple      = array_pad(array(), count($prototype), null);
		foreach ($tuple as $t)
		{
			$this->add($t);
		}
	}

	/**
	 * Add value to the end of the current filling
	 *
	 * @param   mixed      value to insert
	 * @throws  Exception  when tuple is already full
	 */
	public function add($value)
	{
		// find the first empty value from the back
		end($this->tuple);
		while (current($this->tuple) === null)
		{
			prev($this->tuple);
		}

		// if current key is not valid we went past the start, reset to start
		$this->valid() and next($this->tuple);
		! $this->valid() and reset($this->tuple);

		// throw exception if current value isn't empty, this method can't replace
		if (current($this->tuple) !== null)
		{
			throw new Exception('Tuple is already full');
		}

		return $this->add_to($value, key($this->tuple));
	}

	/**
	 * @param   mixed      value to insert
	 * @param   int        position to put value at
	 * @throws  Exception  when offset is invalid
	 * @throws  Exception  when value is of wrong type according to prototype
	 */
	public function add_to($value, $offset)
	{
		if ( ! $this->offsetExists($offset))
		{
			throw new Exception('Invalid offset for tuple, value couldn\'t be added.');
		}

		if ( ! $this->check_type($value, $offset))
		{
			throw new Exception('Invalid content type for tuple at position '.$offset.', must be '.
				$this->prototype[$offset]);
		}

		$this->tuple[$offset] = $value;
	}

	/**
	 * Checks prototype if given value of of correct type for position
	 *
	 * @param   mixed
	 * @param   int
	 * @return  bool
	 */
	protected function check_type($value, $offset)
	{
		$type = explode('|', $this->prototype[$offset]);

		foreach ($type as $t)
		{
			if ($t === 'mixed' or (class_exists($t, false) and $value instanceof $t) or gettype($value) === $t)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Implement ArrayAccess
	 */

	public function offsetSet($offset, $value)
	{
		if (is_null($offset))
		{
			$this->add($value);
		}
		else
		{
			$this->add_to($value, $offset);
		}
	}

	public function offsetExists($offset)
	{
		return is_int($offset) and $offset >= 0 and $offset < count($this->prototype);
	}

	public function offsetUnset($offset)
	{
		if ($this->offsetExists($offset))
		{
			$this->tuple[$offset] = null;
		}
	}

	public function offsetGet($offset)
	{
		if ($this->offsetExists($offset))
		{
			return $this->tuple[$offset];
		}

		throw new Exception('Invalid offset');
	}

	/**
	 * Implement Iterator
	 */

	public function rewind()
	{
		return reset($this->tuple);
	}

	public function current()
	{
		return current($this->tuple);
	}

	public function key()
	{
		return key($this->tuple);
	}

	public function next()
	{
		return next($this->tuple);
	}

	public function valid()
	{
		return $this->current() !== false;
	}

	/**
	 * Implement countable
	 */

	public function count()
	{
		return count($this->prototype);
	}
}