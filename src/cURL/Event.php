<?php
namespace cURL;

use Symfony\Component\EventDispatcher\Event as SymfonyEvent;

class Event extends SymfonyEvent
{
  /** @var Response $response */
	public $response = null;
}
