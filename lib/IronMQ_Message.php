<?php
/**
 * PHP client for IronMQ
 * IronMQ is a scalable, reliable, high performance message queue in the cloud.
 *
 * @link      https://github.com/iron-io/iron_mq_php
 * @link      http://www.iron.io/products/mq
 * @link      http://dev.iron.io/
 * @version   1.1.1
 * @package   IronMQPHP
 * @copyright Feel free to copy, steal, take credit for, or whatever you feel like doing with this code. ;)
 */

class IronMQ_Message
{
	private $body;
	private $timeout;
	private $delay;
	private $expires_in;

	const max_expires_in = 2592000;

	/**
	 * Create a new message.
	 *
	 * @param array|string $message
	 *          An array of message properties or a string of the message body.
	 *          Fields in message array:
	 *          Required:
	 * - body: The message data, as a string.
	 *          Optional:
	 * - timeout: Timeout, in seconds. After timeout, item will be placed back on queue. Defaults to 60.
	 * - delay: The item will not be available on the queue until this many seconds have passed. Defaults to 0.
	 * - expires_in: How long, in seconds, to keep the item on the queue before it is deleted. Defaults to 604800 (7 days). Maximum is 2592000 (30 days).
	 */
	function __construct($message)
	{
		if (is_string($message)) {
			$this->setBody($message);
		} elseif (is_array($message)) {
			$this->setBody($message['body']);
			if (array_key_exists("timeout", $message)) {
				$this->setTimeout($message['timeout']);
			}
			if (array_key_exists("delay", $message)) {
				$this->setDelay($message['delay']);
			}
			if (array_key_exists("expires_in", $message)) {
				$this->setExpiresIn($message['expires_in']);
			}
		}
	}

	public function getBody()
	{
		return $this->body;
	}

	public function setBody($body)
	{
		if (empty($body)) {
			throw new InvalidArgumentException("Please specify a body");
		} else {
			$this->body = $body;
		}
	}

	public function getTimeout()
	{
		// 0 is considered empty, but we want people to be able to set a timeout of 0
		if (!empty($this->timeout) || $this->timeout === 0) {
			return $this->timeout;
		} else {
			return null;
		}
	}

	public function setTimeout($timeout)
	{
		$this->timeout = $timeout;
	}

	public function getDelay()
	{
		// 0 is considered empty, but we want people to be able to set a delay of 0
		if (!empty($this->delay) || $this->delay == 0) {
			return $this->delay;
		} else {
			return null;
		}
	}

	public function setDelay($delay)
	{
		$this->delay = $delay;
	}

	public function getExpiresIn()
	{
		return $this->expires_in;
	}

	public function setExpiresIn($expires_in)
	{
		if ($expires_in > self::max_expires_in) {
			throw new InvalidArgumentException("Expires In can't be greater than " . self::max_expires_in . ".");
		} else {
			$this->expires_in = $expires_in;
		}
	}

	public function asArray()
	{
		$array = array();
		$array['body'] = $this->getBody();
		if ($this->getTimeout() != null) {
			$array['timeout'] = $this->getTimeout();
		}
		if ($this->getDelay() != null) {
			$array['delay'] = $this->getDelay();
		}
		if ($this->getExpiresIn() != null) {
			$array['expires_in'] = $this->getExpiresIn();
		}
		return $array;
	}
}
