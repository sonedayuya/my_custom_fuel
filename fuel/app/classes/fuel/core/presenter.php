<?php
class Presenter extends \Fuel\Core\Presenter
{
    // 名前空間の接頭辞
    protected static $ns_prefix = 'Presenter\\';

    /**
	 * Factory for fetching the Presenter
	 *
	 * @param   string  $presenter    Presenter classname without View_ prefix or full classname
	 * @param   string  $method       Method to execute
	 * @param   bool    $auto_filter  Auto filter the view data
	 * @param   string  $view         View to associate with this presenter
	 * @return  Presenter
	 */
	public static function forge($presenter, $method = 'view', $auto_filter = null, $view = null)
	{
		// determine the presenter namespace from the current request context
		$namespace = \Request::active() ? ucfirst(\Request::active()->module) : '';

		// create the list of possible class prefixes
		$prefixes = array(static::$ns_prefix, $namespace.'\\');

		/**
		 * Add non prefixed classnames to the list as well, for BC reasons
		 *
		 * @deprecated 1.6
		 */
		if ( ! empty($namespace))
		{
			array_unshift($prefixes, $namespace.'\\'.static::$ns_prefix);
			$prefixes[] = '';
		}

		// loading from a specific namespace?
		if (strpos($presenter, '::') !== false)
		{
			$split = explode('::', $presenter, 2);
			if (isset($split[1]))
			{
				array_unshift($prefixes, ucfirst($split[0]).'\\'.static::$ns_prefix);
				$presenter = $split[1];
			}
		}

		// if no custom view is given, make it equal to the presenter name
		is_null($view) and $view = $presenter;

		// strip any extensions from the view name to determine the presenter to load
		$presenter = \Inflector::words_to_upper(str_replace(
			array('/', DS),
			'\\',
			strpos($presenter, '.') === false ? $presenter : substr($presenter, 0, -strlen(strrchr($presenter, '.')))
		), '\\');

		// create the list of possible presenter classnames, start with the namespaced one
		$classes = array();
		foreach ($prefixes as $prefix)
		{
			$classes[] = $prefix.$presenter;
		}

		// check if we can find one
		foreach ($classes as $class)
		{
			if (class_exists($class))
			{
				return new $class($method, $auto_filter, $view);
			}
		}

		throw new \OutOfBoundsException('Presenter "'.reset($classes).'" could not be found.');
	}
}