<?php
namespace cURL;

use Symfony\Component\EventDispatcher\Event as SymfonyEvent;

class Event extends SymfonyEvent
{
  	/**
	 * @var Response
	 */
	public $response;

	/**
	 * @var Request
	 */
	public $request;

	/**
	 * @var RequestsQueue
	 */
	public $queue;
}
