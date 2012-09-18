<?php
/**
 * PHP client for IronMQ
 * IronMQ is a scalable, reliable, high performance message queue in the cloud.
 *
 * @link		  https://github.com/iron-io/iron_mq_php
 * @link		  http://www.iron.io/products/mq
 * @link		  http://dev.iron.io/
 * @version	  1.1.1
 * @package	  IronMQPHP
 * @copyright Feel free to copy, steal, take credit for, or whatever you feel like doing with this code. ;)
 */

require_once "IronCore.class.php";
require_once "IronMQ_Message.php";

class IronMQ extends IronCore
{

	protected $client_version = '1.1.1';
	protected $client_name = 'iron_mq_php';
	protected $product_name = 'iron_mq';
	protected $default_values = array(
		'protocol'			 => 'https',
		'host'				 => 'mq-aws-us-east-1.iron.io',
		'port'				 => '443',
		'api_version'		 => '1',
	);

	/**
	 * @param string|array $config_file_or_options
	 *				Array of options or name of config file.
	 *				Fields in options array or in config:
	 *
	 * Required:
	 * - token
	 * - project_id
	 * Optional:
	 * - protocol
	 * - host
	 * - port
	 * - api_version
	 */
	function __construct($config_file_or_options = null)
	{
		$this->getConfigData($config_file_or_options);
		$this->url = "{$this->protocol}://{$this->host}:{$this->port}/{$this->api_version}/";
	}

	/**
	 * Switch active project
	 *
	 * @param string $project_id Project ID
	 *
	 * @throws InvalidArgumentException
	 */
	public function setProjectId($project_id)
	{
		if (!empty($project_id)) {
			$this->project_id = $project_id;
		}
		if (empty($this->project_id)) {
			throw new InvalidArgumentException("Please set project_id");
		}
	}

	public function getQueues($page = 0)
	{
		$url = "projects/{$this->project_id}/queues";
		$params = array();
		if ($page > 0) {
			$params['page'] = $page;
		}
		$this->setJsonHeaders();
		return self::json_decode($this->apiCall(self::GET, $url, $params));
	}

	/**
	 * Get information about queue.
	 * Also returns queue size.
	 *
	 * @param string $queue_name
	 *
	 * @return mixed
	 */
	public function getQueue($queue_name)
	{
		$queue = rawurlencode($queue_name);
		$url = "projects/{$this->project_id}/queues/$queue";
		$this->setJsonHeaders();
		return self::json_decode($this->apiCall(self::GET, $url));
	}

	/**
	 * Push a message on the queue
	 *
	 * Examples:
	 * <code>
	 * $ironmq->postMessage("test_queue", "Hello world");
	 * </code>
	 * <code>
	 * $ironmq->postMessage("test_queue", array(
	 *		"body" => "Test Message"
	 *		"timeout" => 120,
	 *		'delay' => 2,
	 *		'expires_in' => 2*24*3600 # 2 days
	 * ));
	 * </code>
	 *
	 * @param string		  $queue_name Name of the queue.
	 * @param array|string $message
	 *
	 * @return mixed
	 */
	public function postMessage($queue_name, $message)
	{
		$msg = new IronMQ_Message($message);
		$req = array(
			"messages" => array($msg->asArray())
		);
		$this->setCommonHeaders();
		$queue = rawurlencode($queue_name);
		$url = "projects/{$this->project_id}/queues/$queue/messages";
		$res = $this->apiCall(self::POST, $url, $req);
		return self::json_decode($res);
	}

	/**
	 * Push multiple messages on the queue
	 *
	 * @param string $queue_name Name of the queue.
	 * @param array  $messages	  array of messages, each message same as for postMessage() method
	 *
	 * @return mixed
	 */
	public function postMessages($queue_name, $messages)
	{
		$req = array(
			"messages" => array()
		);
		foreach ($messages as $message) {
			$msg = new IronMQ_Message($message);
			array_push($req['messages'], $msg->asArray());
		}
		$this->setCommonHeaders();
		$queue = rawurlencode($queue_name);
		$url = "projects/{$this->project_id}/queues/$queue/messages";
		$res = $this->apiCall(self::POST, $url, $req);
		return self::json_decode($res);
	}

	/**
	 * Get multiplie messages from queue
	 *
	 * @param string $queue_name Queue name
	 * @param int	  $count
	 *
	 * @return array|null array of messages or null
	 */
	public function getMessages($queue_name, $count = 1)
	{
		$queue = rawurlencode($queue_name);
		$url = "projects/{$this->project_id}/queues/$queue/messages";
		$params = array();
		if ($count > 1) {
			$params['n'] = $count;
		}
		$this->setJsonHeaders();
		$response = $this->apiCall(self::GET, $url, $params);
		$result = self::json_decode($response);
		if (count($result->messages) < 1) {
			return null;
		} else {
			return $result->messages;
		}
	}

	/**
	 * Get single message from queue
	 *
	 * @param string $queue_name Queue name
	 *
	 * @return mixed|null single message or null
	 */
	public function getMessage($queue_name)
	{
		$messages = $this->getMessages($queue_name, 1);
		if ($messages) {
			return $messages[0];
		} else {
			return null;
		}
	}

	public function deleteMessage($queue_name, $message_id)
	{
		$this->setCommonHeaders();
		$queue = rawurlencode($queue_name);
		$url = "projects/{$this->project_id}/queues/$queue/messages/{$message_id}";
		return $this->apiCall(self::DELETE, $url);
	}

	/* PRIVATE FUNCTIONS */

	private function setJsonHeaders()
	{
		$this->setCommonHeaders();
	}

	private function setPostHeaders()
	{
		$this->setCommonHeaders();
		$this->headers['Content-Type'] = 'multipart/form-data';
	}

}
