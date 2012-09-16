<?php 

namespace Primal;

/**
 * Primal Session Abstraction base class
 * Abstract class for wrapping a dedicated index of the $_SESSION superglobal as a state store
 * Also handles smart initialization of the session
 *
 * @package Primal.Session
 */


abstract class SessionAbstraction implements \IteratorAggregate, \ArrayAccess, \Serializable, \Countable  {
	
	protected $abstraction_key = 'SessionAbstraction';
	protected $session_name;
	
	/**
	 * Class constructor.  Detects the php session name and initializes the session if desired
	 *     Smart start will initialize the session if a session cookie exists and the $_SESSION super-global is empty
	 *
	 * @param boolean $start Controls the automatic initialization of the session.  False disables, True forces, Null (default) for smart start
	 * @param string $key Optional override of the $_SESSION index to use for the subclass' data store.
	 */
	public function __construct($start = null, $key = null) {
		if ($key !== null) {
			$this->abstraction_key = $key;
		}
		
		$this->session_name = ini_get('session.name');

		//null = autostart, false = do not start, true = always start
		//autostart will trigger if the session global is empty and a session cookie exists
		if ($start !== true && ($start === true || $_COOKIE[$this->session_name])) {
			$this->start();
		}
	}
	
	/**
	 * Starts the PHP session
	 *
	 * @param string $session_id Optional session id to use in place of PHP's randomly generated id
	 * @return $this
	 */
	public function start($session_id = null) {
		//if the session already contains data then it is already started, we don't need to do anything here.
		if (!empty($_SESSION)) {
			return;
		}
		
		
		if ($session_id !== null) {
			session_id($session_id);
		} elseif (isset($_REQUEST[$this->session_name])) {
			//if a session ID is passed in the request, use it.
			session_id($this->session_name);
		}
		
		session_start();

		if (!isset($_SESSION[$this->abstraction_key])) {
			$this->reset();
		}
		
		return $this;
	}
	
	/**
	 * Imports the passed array into the session data store
	 *
	 * @param array $array 
	 * @return $this
	 */
	public function import($array) {
		$_SESSION[$this->abstraction_key] = is_array($_SESSION[$this->abstraction_key]) ? array_merge($_SESSION[$this->abstraction_key], $array) : $array;
		return $this;
	}
	
	/**
	 * Returns the entire contents of the session data store as an array
	 *
	 * @return array
	 */
	public function export() {
		return $_SESSION[$this->abstraction_key];
	}
	
	/**
	 * Resets the session data store to an empty array
	 *
	 * @return $this
	 */
	public function reset() {
		$_SESSION[$this->abstraction_key] = array();
		return $this;
	}
	
/**
	ArrayAccess Implementation
*/

	public function &offsetGet($key){
		return $_SESSION[$this->abstraction_key][$key];
	}

	public function offsetSet($key, $value){
		$_SESSION[$this->abstraction_key][$key] = $value;
	}

	public function offsetExists($key) {
		isset($_SESSION[$this->abstraction_key][$key]);
	}

	public function offsetUnset($key){
		unset($_SESSION[$this->abstraction_key][$key]);
	}
	

/**
	Serializable Implementation (as json)
*/

	public function serialize() {
		return json_encode($_SESSION[$this->abstraction_key]);
	}
	public function unserialize($data) {
		$_SESSION[$this->abstraction_key] = json_decode($data, true);
	}
	public function getData() {
		return $_SESSION[$this->abstraction_key];
	}
	
/**
	Countable Implementation
*/

	function count() {
		return count($_SESSION[$this->abstraction_key]);
	}
	
/**
	IteratorAggrigate Implementation
*/

	public function getIterator() {
		return new ArrayIterator($_SESSION[$this->abstraction_key]);
	}
}