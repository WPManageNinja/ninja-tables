<?php

namespace NinjaTables\Framework\Foundation;

use Exception;
use NinjaTables\Framework\Support\Arr;
use NinjaTables\Framework\Support\Helper;
use InvalidArgumentException;

class Async
{
	/**
	 * The dispatched handlers to stop recursion.
	 * 
	 * @var array
	 */
	protected $dispatched = [];

	/**
	 * The application instance
	 * 
	 * @var \NinjaTables\Framework\Foundation\Application
	 */
	private static $app = null;

	/**
	 * Self instance
	 * 
	 * @var self
	 */
	private static $instance = null;

	/**
	 * The array of async action handlers
	 * 
	 * @var array
	 */
	private static $handlers = [];

	/**
	 * The array of async action handlers in queue
	 * 
	 * @var array
	 */
	private static $queue = [
		'default' => []
	];

	/**
	 * Creates the instance
	 * 
	 * @return self
	 */
	public static function init($app = null)
	{
		$app = $app ?: App::make();

		if (is_null(static::$instance)) {
			static::$app = $app;
			static::$instance = new static;
		}

		$action = static::$instance->makeAsyncHookAction();

		static::$app->addAction(
			"admin_post_{$action}", [static::$instance, 'handle']
		);

		static::$app->addAction(
			"admin_post_nopriv_{$action}", [static::$instance, 'handle']
		);

		return static::$instance;
	}

	/**
	 * Makes the async hook action name
	 * 
	 * @return string
	 */
	public function makeAsyncHookAction()
	{
		$slug = static::$app->config->get('app.slug');

		return  "wpfluent_async_hook_{$slug}";
	}

	/**
	 * Handles the incoming async request
	 * 
	 * @return void
	 */
	public function handle()
	{
		$post = static::$app->request->post();

		$this->verifyRequest(static::$app, $post);
		
		$handlers = Arr::get($post, 'handlers', []);

		foreach ($handlers as $handler) {
			try {
				[$class, $action] = $this->resolveHandler($handler);

				$this->execute(static::$app, $class, $action['params'] ?? []);

			} catch (Exception $e) {
				error_log($e->getMessage());
			}
		}
	}

	/**
	 * Verify the request by checking the nonce.
	 * 
	 * @param  NinjaTables\Framework\Foundation\Application $app
	 * @param  array $data
	 * @return void
	 */
	protected function verifyRequest($app, $data)
	{
		if (!isset($data['wpfluent_async_nonce'])) {
			exit;
		}

		!wp_verify_nonce(
			$data['wpfluent_async_nonce'],
			$app->config->get('app.slug')
		) && exit;
	}

	/**
	 * Resolve the action handler.
	 * 
	 * @param  JSON $handler
	 * @return array
	 */
	protected function resolveHandler($action)
	{
		if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException("Invalid action.");
        }

        $handler = base64_decode($action['handler']);

        [$class, $method] = explode('@', $handler);

        if (!class_exists($class)) {
            throw new InvalidArgumentException(
            	"Handler {$class} does not exist."
            );
        }

        return [$class.'@'.$method, $action];
	}

	/**
	 * Execute the action handler.
	 * 
	 * @param  \NinjaTables\Framework\Foundation\Application $app
	 * @param  string $class
	 * @param  array  $params
	 * @return void
	 */
	protected function execute($app, $class, $params = [])
	{
		set_time_limit(0);
        ignore_user_abort(true);
        [$class, $method] = explode('@', $class);
		$app->make($class)->{$method}($app, $params);
	}

	/**
	 * Add the async handler and register the shutdown handler
	 * All the handlers will be dispatched in a separate request
	 * 
	 * @param  string (Class@handler or with __invoke method) $handler
	 * @return self
	 * @throws \InvalidArgumentException
	 */
	public static function call($handler, array $params = [])
	{
		if (!static::$instance) {
			static::init();
		}

		static::$handlers[] = static::$instance->validate(
			$handler, $params, static::sign(debug_backtrace(false, 1)[0])
		);
		
		return static::$instance->maybeRegisterShutDownHandler();
	}

	/**
	 * Queue the async handler and register the shutdown handler
	 * Queued handlers will be dispatched in a single request
	 * 
	 * @param  string (Class@handler or with __invoke method) $handler
	 * @return self
	 * @throws \InvalidArgumentException
	 */
	public static function queue(
		$handler, $params = [], $name = 'default'
	) {
		if (!static::$instance) {
			static::init();
		}
		
		if (is_string($params)) {
			$name = $params;
			$params = [];
		}

		static::$queue[$name][] = static::$instance->validate(
			$handler, $params, static::sign(debug_backtrace(false, 1)[0])
		);
		
		return static::$instance->maybeRegisterShutDownHandler();
	}

	/**
	 * Sign the handler to mark as dispatched.
	 * 
	 * @param  array $handler
	 * @return string
	 */
	protected static function sign($handler)
	{
		return md5($handler['file'] . $handler['line']);
	}

	/**
	 * Validate the handler and add a sign to mark as dispatched.
	 * 
	 * @param  string (Class@handler or with __invoke method) $handler
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public function validate($handler, $params, $sign)
	{
		$method = '__invoke';

		if (is_array($handler)) {
			if (is_object($handler[0])) {
				$handler[0] = get_class($handler[0]);
			}
			$handler = $handler[0] . '@' . $handler[1];
		}

		if (str_contains($handler, '@')) {
			[$handler, $method] = explode('@', $handler);
		}

		if (!class_exists($handler)) {
			throw new InvalidArgumentException(
				"Class {$handler} not found."
			);
		}

		if (!method_exists($handler, $method)) {
			throw new InvalidArgumentException(
				"Class {$handler} must implement __invoke or specify method."
			);	
		}

		$handler = $handler.'@'.$method;

		return [
			'sign' => $sign,
			'params'  => $params,
			'handler' => base64_encode($handler),
		];
	}

	/**
	 * Register the shutdown handler
	 * 
	 * @return void
	 */
	protected function maybeRegisterShutDownHandler()
	{
		$handler = [static::$instance, 'dispatch'];

		if (!static::$app->hasAction('shutdown', $handler)) {
			static::$app->addAction('shutdown', $handler);
		}

		return static::$instance;
	}

	/**
	 * Dispatches the async request
	 * 
	 * @return void
	 */
	public function dispatch()
	{
		$stacks = array_filter([
			array_filter(static::$queue),
			array_filter(static::$handlers),
		]);

		// At first we need to mark all the handlers from all
		// the stacks as dispatched before sending any request.
		foreach ($stacks as $key => $stack) {
			$stacks[$key] = $this->getDispatchables($stack);
		}

		// Now we can dispatch them all
		foreach ($stacks as $stack) {
			foreach ($stack as $handler) {
				$this->sendAsyncRequest($this->wrap($handler));
			}
		}
	}

	/**
	 * Filter the handlers to be dispatched.
	 * 
	 * @param  array $stack
	 * @return array|null
	 */
	protected function getDispatchables($stack)
	{
		// If the stack is an array of associtive arrays we
		// need to get the first one because queued handlers
		// will containn one associtive array in the stack.
		$stack = !isset($stack[0]) ? reset($stack) : $stack;

		return array_filter($stack, function ($handler) {
			if (isset($handler['sign'])) {
				$isDispatched = in_array(
					$handler['sign'],
					static::$app->request->post('dispatched', [])
				);

				if (!$isDispatched) {
					$this->dispatched[] = $handler['sign'];
					return !$isDispatched;
				}
			}
		});
	}

	/**
	 * Wrap with an array if necessary. Only used for separate
	 * handlers because queued handlers will be an array of
	 * associative arrays and we treat all the stacks same.
	 * 
	 * @param  array $handlers
	 * @return array of array(s)
	 */
	protected function wrap($handlers)
	{
		return isset($handlers[0]) ? $handlers : [$handlers];
	}

	/**
	 * Send the real async request
	 * 
	 * @param  array $handler
	 * @return mixed
	 */
	public function sendAsyncRequest(array $handler)
	{
		Helper::retry(3, function () use ($handler) {
			return $this->sendRequest(
				$this->url(),
				$this->data($handler),
				['cookie' => $this->getCookie()]
			);
		}, 2000, function ($e) {
	        return str_contains($e->getMessage(), 'cURL');
	    });
	}

	/**
	 * Prepare the request body/POST data.
	 * 
	 * @param  string|array $handler
	 * @return array
	 */
	protected function data($handler)
	{
		$post = static::$app->request->post();

		$data = [
	        'handlers' => $handler,
	        'wpfluent_async_nonce' => wp_create_nonce(
	        	static::$app->config->get('app.slug')
	        ),
	        'dispatched' => array_unique(array_merge(
	        	Arr::get($post, 'dispatched', []),
	        	$this->dispatched,
	        )),
	    ];

		return array_merge($data, Arr::except($post, [
		    'handlers', 'wpfluent_async_nonce', 'dispatched'
		]));
	}

	/**
	 * Build the request url.
	 * 
	 * @return string
	 */
	protected function url()
	{
		return admin_url('admin-post.php') . '?' . http_build_query(
			array_merge(
				static::$app->request->query(),
				['action' => $this->makeAsyncHookAction()]
			)
		);
	}

	/**
	 * Send the non-blocking request.
	 * 
	 * @param  string $url
	 * @param  array $body
	 * @param  array  $headers
	 * @return mixed
	 */
	protected function sendRequest($url, $body = [], $headers = [])
	{
		return wp_remote_post($url, [
	        'timeout'   => 0.01,
	        'blocking'  => false,
	        'sslverify' => false,
	        'body'      => $body,
	        'headers'   => $headers,
	    ]);
	}

	/**
	 * Get the cookie to send with the request
	 * @return string Cookie string
	 */
	protected function getCookie()
	{
		$cookies = [];

		foreach ($_COOKIE as $name => $value) {
			$cookies[] = "$name=" . urlencode(
				is_array($value) ? serialize($value) : $value
			);
		}

		return implode('; ', $cookies);
	}
}
