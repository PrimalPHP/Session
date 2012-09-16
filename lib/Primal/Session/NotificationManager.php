<?php 

namespace Primal\Session;

/**
 * Session based user notification system for displaying status alerts
 * Provided as an example of a SessionAbstraction implementation
 * Notifications are stored as simple StdClass objects with two properties:
 *   `->message` contains the contents of the notification
 *   `->className` contains the css classes to be applied to the notification
 *
 * @package Primal.Session
 */

class NotificationManager extends SessionAbstraction  {
	protected $session_key = 'NotificationManager';
	

	/**
	 * Static initialization function.  
	 * Not strictly a singleton, as state is saved in the session, but named such for consistency with other Primal packages.
	 *
	 * @return Request
	 */
	static function Singleton() {
		return new static();
	}
	
	/**
	 * Class constructor: Initializes the session and creates the notifications array if it doesn't exist
	 *
	 */
	public function __construct() {
		
		parent::__construct(true); //make sure the session has started, as we're immediately writing to it
		
		if (!isset($this['Notifications']) || !is_array($this['Notifications'])) {
			$this['Notifications'] = array();
		}
		
	}
	
	/**
	 * Pulls the oldest notification off the stack and returns it 
	 *
	 * @return void
	 */
	public function shift() {
		return array_shift($this['Notifications']);
	}
	
	
	/**
	 * Pulls the most recent notification off the stack and returns it
	 *
	 * @return StdClass
	 */
	public function pop() {
		return array_pop($this['Notifications']);
	}

	/**
	 * Returns the entire notification stack and resets the contents
	 *
	 * @return array
	 */
	public function popAll() {
		$list = $this['Notifications'];
		$this['Notifications'] = array();
		return $list;
	}
	
	
	/**
	 * Appends a new notification onto the stack
	 *
	 * @return $this
	 */
	public function push($message, $class = 'notice') {
		$this['Notifications'][] = (object)array('message'=>$message, 'className'=>$class);
		return $this;
	}
	
	
}