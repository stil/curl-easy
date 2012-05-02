<?php
namespace cURL;

class EventManager {
	protected $listeners=array();
	
	/**
	 * Summary
	 * 
	 * @param unknown $id       Description
	 * @param unknown $callback Description
	 * 
	 * @return Type    Description
	 */
	public function attach($id,$callback) {
		$this->listeners[$id][]=$callback;
	}
	
	public function notify($id,$parameters) {
		if(!isset($this->listeners[$id]) or empty($this->listeners[$id])) return;
		
		foreach($this->listeners[$id] as $callback) {
			call_user_func_array($callback,$parameters);
		}
	}
	
}