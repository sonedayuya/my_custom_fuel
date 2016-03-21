<?php

class Request extends \Fuel\Core\Request
{
	public function execute($method_params = null)
	{
		// fire any request started events
		\Event::instance()->has_events('request_started') and \Event::instance()->trigger('request_started', '', 'none');

		if (\Fuel::$profiling)
		{
			\Profiler::mark(__METHOD__.': Start of '.$this->uri->get());
		}

		logger(\Fuel::L_INFO, 'Called', __METHOD__);

		// Make the current request active
		static::$active = $this;

		// First request called is also the main request
		if ( ! static::$main)
		{
			logger(\Fuel::L_INFO, 'Setting main Request', __METHOD__);
			static::$main = $this;
		}

		if ( ! $this->route)
		{
			static::reset_request();
			throw new \HttpNotFoundException();
		}

		// save the current language so we can restore it after the call
		$current_language = \Config::get('language', 'en');

		try
		{
			if ($this->route->callable !== null)
			{
				$response = call_fuel_func_array($this->route->callable, array($this));

				if ( ! $response instanceof Response)
				{
					$response = new \Response($response);
				}
			}
			else
			{
				$method_prefix = $this->method.'_';
				$class = $this->controller;

				// Allow override of method params from execute
				if (is_array($method_params))
				{
					$this->method_params = array_merge($this->method_params, $method_params);
				}

				// If the class doesn't exist then 404
				if ( ! class_exists($class))
				{
					throw new \HttpNotFoundException();
				}

				// Load the controller using reflection
				$class = new \ReflectionClass($class);

				if ($class->isAbstract())
				{
					throw new \HttpNotFoundException();
				}

				// Create a new instance of the controller
				$this->controller_instance = $class->newInstance($this);

				$this->action = $this->action ?: ($class->hasProperty('default_action') ? $class->getProperty('default_action')->getValue($this->controller_instance) : 'index');
				$method = $method_prefix.$this->action;

				// Allow to do in controller routing if method router(action, params) exists
				if ($class->hasMethod('router'))
				{
					$method = 'router';
					$this->method_params = array($this->action, $this->method_params);
				}

				if ( ! $class->hasMethod($method))
				{
					// If they call user, go to $this->post_user();
					$method = strtolower(\Input::method()) . '_' . $this->action;

					// Fall back to action_ if no HTTP request method based method exists
					if ( ! $class->hasMethod($method))
					{
						$method = 'action_'.$this->action;
					}
				}

				if ($class->hasMethod($method))
				{
					$action = $class->getMethod($method);

					if ( ! $action->isPublic())
					{
						throw new \HttpNotFoundException();
					}

					if (count($this->method_params) < $action->getNumberOfRequiredParameters())
					{
						throw new \HttpNotFoundException();
					}

					// fire any controller started events
					\Event::instance()->has_events('controller_started') and \Event::instance()->trigger('controller_started', '', 'none');

					$class->hasMethod('before') and $class->getMethod('before')->invoke($this->controller_instance);

					$action->invokeArgs($this->controller_instance, $this->method_params);

					$response = $this->get_view_response($this->controller_instance);

					$class->hasMethod('set_partial') and $class->getMethod('set_partial')->invoke($this->controller_instance, $response);

					$class->hasMethod('after') and $response = $class->getMethod('after')->invoke($this->controller_instance, $response);

					// fire any controller finished events
					\Event::instance()->has_events('controller_finished') and \Event::instance()->trigger('controller_finished', '', 'none');
				}
				else
				{
					throw new \HttpNotFoundException();
				}
			}

			// restore the language setting
			\Config::set('language', $current_language);
		}
		catch (\Exception $e)
		{
			static::reset_request();

			// restore the language setting
			\Config::set('language', $current_language);

			throw $e;
		}

		// Get the controller's output
		if ($response instanceof Response)
		{
			$this->response = $response;
		}
		else
		{
			throw new \FuelException(get_class($this->controller_instance).'::'.$method.'() or the controller after() method must return a Response object.');
		}

		// fire any request finished events
		\Event::instance()->has_events('request_finished') and \Event::instance()->trigger('request_finished', '', 'none');

		if (\Fuel::$profiling)
		{
			\Profiler::mark(__METHOD__.': End of '.$this->uri->get());
		}

		static::reset_request();

		return $this;
	}

	private function get_view_response($controller_instance)
	{
		$controller = explode('\\', $this->controller);

		$namespace = '';
		if (empty($this->module) === false) {
			$namespace = ucfirst($this->module);
		}
		if (class_exists($namespace . '\\Presenter\\' . ucfirst($controller[2]) . '\\' . ucfirst($this->action))) {
			$view = \Presenter::forge(mb_strtolower($controller[2]) . '/' . $this->action);
		} else {
			$view = \View::forge(mb_strtolower($controller[2]) . '/' . $this->action, $controller_instance->getVar());
		}

		$theme=\Theme::instance();
		$theme->set_template('layout/main');
		$theme->get_template()->set_safe('content', '');

		preg_match_all('/\{% block (.*?) %\}/s', $view, $blocks);

		foreach ($blocks[1] as $block) {
			preg_match('/\{% block ' . $block . ' %\}(.*?)\{% endblock %}/s', $view, $block_content);
			if (isset($block_content[1]) === true) {
				$theme->get_template()->set_safe($block, \Response::forge($block_content[1]));
			}
		}

		return $theme;
	}
}