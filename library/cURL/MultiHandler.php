<?php
namespace cURL;

class MultiHandler
{
	protected $eventManager;
	protected $mh;
	protected $closed = false;
	protected $handles = array();
	
	/**
	 * Constructor of MultiHandler
	 * Equivalent to curl_multi_init().
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->eventManager = new EventManager;
		$this->mh = curl_multi_init();
	}
	
	/**
	 * Destructor of MultiHandler
	 * Equivalent to curl_multi_close().
	 *
	 * @return Type    Description
	 */
	public function __destruct()
	{
		$this->close();
	}
	
	/**
	 * On request complete callback.
	 *
	 * @param callback $callback
	 *
	 * @return void
	 */
	public function onComplete($callback)
	{
		$this->eventManager->attach('complete', $callback);
	}
	
	/**
	 * Before handle attaching.
	 *
	 * @param callback $callback
	 *
	 * @return void
	 */
	public function onBeforeAttach($callback)
	{
		$this->eventManager->attach('before.attach', $callback);
	}
	
	/**
	 * After handle attaching.
	 *
	 * @param callback $callback
	 *
	 * @return void
	 */
	public function onAfterAttach($callback)
	{
		$this->eventManager->attach('after.attach', $callback);
	}
	
	/**
	 * Before handle detaching.
	 *
	 * @param callback $callback
	 *
	 * @return void
	 */
	public function onBeforeDetach($callback)
	{
		$this->eventManager->attach('before.detach', $callback);
	}
	
	/**
	 * After handle detaching.
	 *
	 * @param callback $callback
	 *
	 * @return void
	 */
	public function onAfterDetach($callback)
	{
		$this->eventManager->attach('after.detach', $callback);
	}
	
	/**
	 * Attach cURL\Handler to queue.
	 * Equivalent to curl_multi_add_handle().
	 *
	 * @param Handler $ch
	 *
	 * @return void
	 */
	public function attach(Handler $ch)
	{
		$this->eventManager->notify('before.attach', array($this, $ch));
		$this->handles[$ch->getResourceID() ] = $ch;
		$result = curl_multi_add_handle($this->mh, $ch->getResource());
		$this->eventManager->notify('after.attach', array($this, $ch));
		return $result;
	}
	
	/**
	 * Detach cURL\Handler from queue.
	 * Equivalent to curl_multi_remove_handle().
	 *
	 * @param Handler $ch
	 *
	 * @return void
	 */
	public function detach(Handler $ch)
	{
		$this->eventManager->notify('before.detach', array($this, $ch));
		unset($this->handles[$ch->getResourceID() ]);
		$result = curl_multi_remove_handle($this->mh, $ch->getResource());
		$this->eventManager->notify('after.detach', array($this, $ch));
		return $result;
	}
	
	/**
	 * Processes handles which are ready.
	 *
	 * @return void
	 */
	protected function readAll()
	{
		while ($info = curl_multi_info_read($this->mh)) {
			$ch = $info['handle'];
			$handle = $this->handles[(int)$ch];
			$this->eventManager->notify('complete', array($this, $handle));
			$this->detach($handle);
		}
	}
	
	/**
	 * Removes timeout handles from queue.
	 *
	 * @return void
	 */
	protected function clearTimeoutHandles()
	{
		foreach ($this->handles as $handle) {
			if ($handle->timeout > 0 && (microtime(true) - $handle->timeStart) >= $handle->timeout) {
				$this->eventManager->notify('complete', array($this, $handle));
				$this->detach($handle);
			}
		}
	}
	
	/**
	 * Returns count of handles in queue.
	 * 
	 * @return int
	 */
	public function activeHandlesCount() {
		return count($this->handles);
	}
	
	/**
	 * Starts execution of requests.
	 * A lot of magic here as it's hard to find reliable documentation.
	 *
	 * @return void
	 */
	public function execute()
	{
		while (count($this->handles) > 0) {
			do {
				curl_multi_exec($this->mh, $running);
				/* Initialize starting time for timeout control */
				foreach ($this->handles as $ch) {
					if (!isset($ch->timeStart)) $ch->timeStart = microtime(true);
				}
				/* Remove timeout requests */
				$this->clearTimeoutHandles();
				$ready = curl_multi_select($this->mh);
				/* There are ready requests, process it */
				if ($ready > 0) $this->readAll();
			}
			while ($running > 0 && $ready != - 1);
			/* There are failed requests */
			$this->readAll();
		}
	}
	
	/**
	 * Closes cURL multi handle.
	 * Equivalent to curl_multi_close().
	 *
	 * @return void
	 */
	public function close()
	{
		if ($this->closed === false) {
			$this->closed = true;
			curl_multi_close($this->mh);
		}
	}
} 