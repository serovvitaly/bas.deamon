<?php
/**
 * Controller
 *
 * Basic DEMO outline for standard controllers
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2011 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
namespace Example;

abstract class Controller extends \Controller
{
	// Global view template
	public $template = 'Example/Layout';

	/**
	 * Called after the controller is loaded, before the method
	 *
	 * @param string $method name
	 */
	public function before($method)
	{
		\View::$path = SP . 'View/';

		// Enable global error handling
		set_error_handler(array('\Micro\Error', 'handler'));
		register_shutdown_function(array('\Micro\Error', 'fatal'));

		\Micro\Session::start();
	}


	/**
	 * Load database connection
	 */
	public function load_database($name = 'database')
	{
		// Load database
		$db = new \Micro\Database(config()->$name);

		// Set default ORM database connection
		if(empty(\Micro\ORM::$db))
		{
			\Micro\ORM::$db = $db;
		}

		return $db;
	}


	/**
	 * Show a 404 error page
	 */
	public function show_404()
	{
		headers_sent() OR header('HTTP/1.0 404 Page Not Found');
		print new \View('Example/404');
	}


	/**
	 * Save user session and render the final layout template
	 */
	public function send()
	{
		\Micro\Session::save();

		headers_sent() OR header('Content-Type: text/html; charset=utf-8');

		$layout = new \Micro\View($this->template);
		$layout->set((array) $this);
		print $layout;

		$layout = NULL;

		if(config()->debug_mode)
		{
			print new \Micro\View('System/Debug');
		}
	}

}

// End
