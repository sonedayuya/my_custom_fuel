<?php
class Autoloader extends \Fuel\Core\Autoloader
{
	/**
	 * Loads a class.
	 *
	 * @param   string  $class  Class to load
	 * @return  bool    If it loaded the class
	 */
	public static function load($class)
	{
		// deal with funny is_callable('static::classname') side-effect
		if (strpos($class, 'static::') === 0)
		{
			// is called from within the class, so it's already loaded
			return true;
		}

		$loaded = false;
		$class = ltrim($class, '\\');
		$pos = strripos($class, '\\');

		if (empty(static::$auto_initialize))
		{
			static::$auto_initialize = $class;
		}

		if (isset(static::$classes[strtolower($class)]))
		{
			static::init_class($class, str_replace('/', DS, static::$classes[strtolower($class)]));
			$loaded = true;
		}
		elseif ($full_class = static::find_core_class($class))
		{
			if ( ! class_exists($full_class, false) and ! interface_exists($full_class, false))
			{
				include static::prep_path(static::$classes[strtolower($full_class)]);
			}
			if ( ! class_exists($class, false))
			{
				class_alias($full_class, $class);
			}
			static::init_class($class);
			$loaded = true;
		}
		else
		{
			$full_ns = substr($class, 0, $pos);

			if ($full_ns)
			{
				foreach (static::$namespaces as $ns => $path)
				{
					$temp_path = $path;
					$ns = ltrim($ns, '\\');
					if (stripos($full_ns, $ns) === 0)
					{
						$temp_path .= static::class_to_path(
							substr($class, strlen($ns) + 1),
							array_key_exists($ns, static::$psr_namespaces)
						);

						if (is_file($temp_path))
						{
							static::init_class($class, $temp_path);
							$loaded = true;
							break;
						}
						else
						{
							$temp_path = $path;
							$temp_path .= static::class_to_path(
								substr($class, strlen($ns) + 1),
								false,
								''
							);

							if (is_file($temp_path))
							{
								static::init_class($class, $temp_path);
								$loaded = true;
								break;
							}
						}
					}
				}
			}

			if ( ! $loaded)
			{
				$path = APPPATH.'classes'.DS.static::class_to_path($class);

				if (is_file($path))
				{
					static::init_class($class, $path);
					$loaded = true;
				}
			}

		}

		// Prevent failed load from keeping other classes from initializing
		if (static::$auto_initialize == $class)
		{
			static::$auto_initialize = null;
		}

		return $loaded;
	}

	/**
	 * Takes a class name and turns it into a path.  It follows the PSR-0
	 * standard, except for makes the entire path lower case, unless you
	 * tell it otherwise.
	 *
	 * Note: This does not check if the file exists...just gets the path
	 *
	 * @param   string  $class  Class name
	 * @param   bool    $psr    Whether this is a PSR-0 compliant class
	 * @return  string  Path for the class
	 */
	protected static function class_to_path($class, $psr = false, $class_seperater = '_')
	{
		$file  = '';
		if ($last_ns_pos = strripos($class, '\\'))
		{
			$namespace = substr($class, 0, $last_ns_pos);
			$class = substr($class, $last_ns_pos + 1);
			$file = str_replace('\\', DS, $namespace).DS;
		}

		$file .= str_replace($class_seperater, DS, $class).'.php';

		if ( ! $psr)
		{
			$file = strtolower($file);
		}

		return $file;
	}
}