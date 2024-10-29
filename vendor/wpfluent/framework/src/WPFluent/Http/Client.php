<?php

namespace NinjaTables\Framework\Http;

use Exception;
use BadMethodCallException;
use NinjaTables\Framework\Support\Arr;
use NinjaTables\Framework\Foundation\App;
use NinjaTables\Framework\Http\Request\File;

/**
 * @method mixed get(string $url, $params = []) Send a GET request.
 * @method mixed post(string $url, $params = []) Send a POST request.
 * @method mixed put(string $url, $params = []) Send a PUT request.
 * @method mixed delete(string $url, $params = []) Send a DELETE request.
 * @method Client asynGet(string $url, $params = []) Send an async GET request.
 * @method Client asyncPost(string $url, $params = []) Send an async POST request.
 * @method Client asyncPut(string $url, $params = []) Send an async PUT request.
 * @method Client asyncDelete(string $url, $params = []) Send an async DELETE request.
 * @method File download(string|File $url) Download a remote file.
 */
class Client
{
	/**
	 * Base URl for the request.
	 * 
	 * @var string
	 */
	protected $baseUrl = '';
	
	/**
	 * Cookies to send with the request.
	 * 
	 * @var array
	 */
	protected $cookies = [];
	
	/**
	 * Headers to send with the request.
	 * 
	 * @var array
	 */
	protected $headers = [];
	
	/**
	 * Options to set for the request.
	 * 
	 * @var array
	 */
	protected $options = [];

	/**
	 * Request body|Data|params to set in the request.
	 * 
	 * @var array
	 */
	protected $body = [];

	/**
	 * Request query params to pass with the url.
	 * 
	 * @var array
	 */
	protected $query = [];

	/**
	 * Stores args temporarily for then().
	 * 
	 * @var null|array
	 */
	private $args = null;

	/**
	 * Create a new HTTP client.
	 *
	 * @param string $baseUrl
	 * @param array $args
	 */
	public function __construct($baseUrl = '', $args = [])
	{
		$this->baseUrl = $baseUrl;
		$this->cookies = $args['cookies'] ?? [];
		$this->headers = $args['headers'] ?? [];
		$this->options = $args['options'] ?? [];
	}

	/**
	 * Create a new HTTP client.
	 * 
	 * @param string $baseUrl
	 * @param array $args
	 */
	public static function make($baseUrl = '', $args = [])
	{
		$args['cookies'] = $args['cookies'] ?? [];
		$args['headers'] = $args['headers'] ?? [];
		$args['options'] = $args['options'] ?? [];
		return new static($baseUrl, $args);
	}

	/**
	 * Sets one or more options.
	 * 
	 * @return self
	 */
	public function withOption($key, $value = null)
	{
		$options = is_array($key) ? $key : [$key => $value];

		foreach ($options as $key => $value) {
			$this->options[$key] = $value;
		}

		return $this;
	}

	/**
	 * Sets the blocking option to false (non-blocking).
	 * 
	 * @return self
	 */
	public function async()
	{
		return $this->withOption('blocking', false);
	}

	/**
	 * Sets the sslverify option.
	 * 
	 * @return self
	 */
	public function secure($verify = true)
	{
		return $this->withOption('sslverify', $verify);
	}

	/**
	 * Sets one or more headers.
	 * 
	 * @return self
	 */
	public function withHeader($key, $value = null)
	{
		$headers = is_array($key) ? $key : [$key => $value];

		foreach ($headers as $key => $value) {
			$this->headers[$key] = $value;
		}

		return $this;
	}

	/**
	 * Sets one or more cookies.
	 * 
	 * @return self
	 */
	public function withCookie($key, $value = null)
	{
		$cookies = is_array($key) ? $key : [$key => $value];

		foreach ($cookies as $key => $value) {
			$this->cookies[$key] = $value;
		}

		return $this;
	}

	/**
	 * Sets one or more request body param.
	 * 
	 * @return self
	 */
	public function withData($key, $value = null)
	{
		$data = is_array($key) ? $key : [$key => $value];

		foreach ($data as $key => $value) {
			$this->body[$key] = $value;
		}

		return $this;
	}

	/**
	 * Sets one or more request body param.
	 * 
	 * @return self
	 */
	public function withBody($key, $value = null)
	{
		return $this->withData($key, $value);
	}

	/**
	 * Sets one or more request body param.
	 * 
	 * @return self
	 */
	public function withParam($key, $value = null)
	{
		return $this->withData($key, $value);
	}

	/**
	 * Sets one or more request body param.
	 * 
	 * @return self
	 */
	public function withQuery($key, $value = null)
	{
		$data = is_array($key) ? $key : [$key => $value];
		
		foreach ($data as $key => $value) {
			$this->query[$key] = $value;
		}

		return $this;
	}

	/**
	 * Build the request arguments.
	 * 
	 * @param  array  $params
	 * @param  string $method 
	 * @return array
	 */
	protected function buildRequestArgs($params, $method)
	{
		$defaultParams = [
	        'body' => [],
	        'cookies' => [],
	        'headers' => [],
	    ];

	    $callback = isset($params[1]) ? $params[1] : null;

	    $params = wp_parse_args(reset($params), $defaultParams);

	    if ($callback) {
	    	$params['callback'] = $callback;
	    }

	    $params = [
	        'method' => strtoupper($method),
	        'body' => array_merge($this->body, $params['body']),
	        'cookies' => array_merge($this->cookies, $params['cookies']),
	        'headers' => array_merge($this->headers, $params['headers']),
	    	'callback' => $params['callback'] ?? null,
	    ];

	    $options = array_merge($this->options, $params['options'] ?? []);

	    foreach($options as $key => $value) {
	        $params[$key] = $value;
	    }

	    return $params;
	}

	/**
	 * Send the request.
	 * 
	 * @param  string $url
	 * @param  array  $args
	 * @return Response object from anonymous class.
	 */
	protected function request($url, $args = [])
	{
		if ($query = http_build_query($this->query)) {
			$url .= '?' . $query;
		}

		$response = wp_remote_request($url, $args);

		if (is_wp_error($response)) {
			throw new Exception($response->get_error_message(), 500);
		}
		
		$this->cookies = array_merge(
			$this->cookies,
			wp_remote_retrieve_cookies($response)
		);

		return $this->makeResponse($response);
	}

	/**
	 * Send the request.
	 * 
	 * @param  string $url
	 * @param  array  $args
	 * @return Response object from anonymous class.
	 */
	protected function asyncRequest($url, $args = [])
	{
		$args['url'] = $url;

		if ($query = http_build_query($this->query)) {
			$args['url'] .= '?' . $query;
		}
		
		$this->args = $args;
		
		if (isset($args['callback'])) {
			$this->then($args['callback']);
		}

		return $this;
	}

	/**
	 * Add the callback for handling the response.
	 * 
	 * @param  callable $callback
	 * @return void
	 */
	public function then($callback)
	{
		if ($callback instanceof \Closure) {
			throw new Exception(
				'The callback must not be a closure', 500
			);
		}

		if (is_string($callback) && function_exists($callback)) {
			throw new Exception(
				'The callback must not be a function', 500
			);
		}

		// Normalize [Example::class, method] to 'Example@method'
		if (is_array($callback) && is_string(reset($callback))) {
			$callback = implode('@', $callback);
		}

		if (is_string($callback)) {
			if (str_contains($callback, '@')) {
				[$class, $method] = explode('@', $callback);
				$callback = [App::make($class), $method];
			} elseif (method_exists($callback, '__invoke')) {
				$callback = App::make($callback);
			}
		}

		if (!is_callable($callback)) {
			throw new Exception('Callback must be callable', 500);
		}

		if (is_callable($callback)) {
			$this->args['callback'] = $callback;
			$this->registerShutdownHandler($this->args);
		}
	}

	/**
	 * Register the shutdown handler.
	 * 
	 * @param  array $args
	 * @return void
	 */
	protected function registerShutdownHandler($args)
	{
		$this->serializeCallback($args);

		add_action('shutdown', function() use ($args) {
			$action = static::makeAsyncRequestAction();
		    wp_remote_post(admin_url('admin-post.php'), [
		        'timeout'   => 1,
		        'blocking'  => false,
		        'sslverify' => false,
		        'body'      => [
		            'args'   => $args,
		            'action' => $action
		        ],
		    ]);
		});
	}

	/**
	 * Serializes the callback.
	 * 
	 * @param  array &$args
	 * @return void
	 */
	protected function serializeCallback(&$args)
	{
		$args['callback'] = base64_encode(serialize($args['callback']));
	}

	/**
	 * Get the closure.
	 * 
	 * @param  Array &$params
	 * @return \Closure
	 */
	protected static function getCallback(&$params)
	{
		$callback = unserialize(base64_decode($params['callback']));

		unset($params['callback']);

		return $callback;
	}

	/**
	 * Register the main async request handler.
	 * 
	 * @return void
	 */
	public static function registerAsyncRequestHandler()
	{
		$action = static::makeAsyncRequestAction();

		App::addAction("admin_post_nopriv_{$action}", function() {
			
			$request = App::make('request');
			
			$requestUrl = $request->get('args.url');
			
			$requestMethod = $request->get('args.method');
			
			$client = Client::make($requestUrl);
			
			$params = $request->except(
				'action', 'args.url', 'args.method',
			)['args'];

			
			$callback = static::getCallback($params);

			$response = $client->{$requestMethod}('', $params);

			if (is_wp_error($response)) {
				$exception = new Exception(
					$response->get_error_message(), 500
				);
			}

			return $callback($response, $exception ?? null);
		});
	}

	/**
	 * Make the action for async request.
	 * 
	 * @return string
	 */
	protected static function makeAsyncRequestAction()
	{
		return 'wpf-async-request-'.sha1(
			App::config()->get('app.slug')
		);
	}

	/**
	 * Download a remote file.
	 * 
	 * @param  string $url
	 * @return \NinjaTables\Framework\Http\Request\File
	 * @throws \Exception
	 */
	public function downloadFile($url)
	{
		if (!function_exists('download_url')) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		$parsed = parse_url($url);

		if (!isset($parsed['scheme'])) {
			$url = trim($this->baseUrl, '/') . '/' . trim($url, '/');
		}

		if (is_wp_error($file = download_url($url))) {
			throw new Exception($file->get_error_message(), 500);
		}

		add_action('shutdown', function () use ($file) {
			@unlink($file);
		});

		return new File(
			$file,
			basename($url),
			mime_content_type($file) ?: 'application/octet-stream',
			filesize($file),
			UPLOAD_ERR_OK
		);
	}

	/**
	 * Build a response object from an anonymous class.
	 * 
	 * @param  array $response
	 * @return @return Response object from anonymous class.
	 */
	protected function makeResponse($response)
	{
		return new Response($response);
	}

	protected function checkIfValidHttpMethod($method)
	{
		$validHttpMethods = [
			'get', 'post', 'put', 'delete', 'patch', 'options', 'head'
		];

		if (!in_array(strtolower($method), $validHttpMethods)) {
			throw new BadMethodCallException("Method $method does not exist.");
		}
	}

	/**
	 * Handles the dynamic calls.
	 * 
	 * @param  string $method
	 * @param  array  $args
	 * @return mixed
	 */
	public function __call($method, $args)
	{
		if ($method === 'download') {
			return $this->downloadFile(...$args);
		}
		
		// Handles dynamic method calls like:
		// asyncGet, asyncPost and so on
		// get, post and so on
		$url = array_shift($args);

		$parsed = parse_url($url);

		if (!isset($parsed['scheme'])) {
			$url = trim($this->baseUrl, '/') . '/' . trim($url, '/');
		}

		if (str_starts_with($method, 'async')) {
			$method = substr($method, strlen('async'));
			$this->checkIfValidHttpMethod($method);
			return $this->asyncRequest(
				$url, $this->buildRequestArgs($args, $method)
			);
		}
		
		$this->checkIfValidHttpMethod($method);
		return $this->request(
			$url, $this->buildRequestArgs($args, $method)
		);
	}

	/**
	 * Handle the static dynamic calls.
	 * 
	 * @param  string $method
	 * @param  array  $args
	 * @return self
	 */
	public static function __callStatic($method, $args)
	{
		return static::make()->$method(...$args);
	}
}
