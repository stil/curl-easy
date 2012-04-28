<?php
namespace cURL;

class EventManager {
	protected $listeners=array();
	
	public function attach($id,$callback) {
		$this->listeners[$id][]=$callback;
	}
	
	public function notify($id,$parameters) {
		if(!isset($this->listeners) or empty($this->listeners)) return;
		
		foreach($this->listeners[$id] as $callback) {
			call_user_func_array($callback,$parameters);
		}
	}
	
}