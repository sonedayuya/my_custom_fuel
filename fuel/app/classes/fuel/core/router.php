<?php
class Router extends \Fuel\Core\Router
{
	protected static function parse_segments($segments, $namespace = '', $module = false)
	{
		$temp_segments = $segments;
		$prefix = static::get_prefix();

		foreach (array_reverse($segments, true) as $key => $segment)
		{
			// determine which classes to check. First, all underscores, or all namespaced
			$classes = array(
				ucwords($namespace.$prefix.\Inflector::words_to_upper(implode(substr($prefix, -1, 1), $temp_segments), substr($prefix, -1, 1)), '_'),
			);

			// if we're namespacing, check a hybrid version too
			$classes[] = ucwords($namespace.$prefix.\Inflector::words_to_upper(implode('_', $temp_segments)), '_');


			array_pop($temp_segments);

			foreach ($classes as $class)
			{
				if (static::check_class($class))
				{
					return array(
						'controller'       => $class,
						'controller_path'  => implode('/', array_slice($segments, 0, $key + 1)),
						'action'           => isset($segments[$key + 1]) ? $segments[$key + 1] : null,
						'method_params'    => array_slice($segments, $key + 2),
					);
				}
			}
		}

		// Fall back for default module controllers
		if ($module)
		{
			$class = $namespace.$prefix.ucfirst($module);

			if (static::check_class($class))
			{
				return array(
					'controller'       => $class,
					'controller_path'  => isset($key) ? implode('/', array_slice($segments, 0, $key + 1)) : '',
					'action'           => isset($segments[0]) ? $segments[0] : null,
					'method_params'    => array_slice($segments, 1),
				);
			}
		}

		return false;
	}
}