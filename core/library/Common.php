<?php
/*
| ---------------------------------------------------------------
| Function: show_error()
| ---------------------------------------------------------------
|
| This function is used to simplify the showing of errors
|
| @Param: $lvl - Level of the error
| @Param: $message - Error message
| @Param: $file - The file reporting the error
| @Param: $line - Error line number
| @Param: $errno - Error number
|
*/	
	function show_error($lvl, $message = 'Not Specified', $file = "none", $line = 0, $errno = 0)
	{
		$Core = new Core();
		return $Core->trigger_error($lvl, $message, $file, $line, $errno);
	}
	
/*
| ---------------------------------------------------------------
| Method: __autoload()
| ---------------------------------------------------------------
|
| This function is used to autoload files of delcared classes
| that have not been included yet
|
| @Param: $className - Class name to autoload ofc silly
|
*/

function __autoload($className) 
{	
	// Check the core library folder
	if(file_exists(CORE_PATH . DS .  'library' . DS . $className . '.php')) 
	{
		require_once(CORE_PATH . DS .  'library' . DS . $className . '.php');
	}
	
	// Check the application library folder
	elseif(@file_exists(APP_PATH . DS .  'library' . DS . $className . '.php')) 
	{
		require_once(APP_PATH . DS .  'library' . DS . $className . '.php');
	}
	
	// Check application controllers
	elseif(file_exists(APP_PATH . DS . 'controllers' . DS . strtolower($className) . '.php')) 
	{
		require_once(APP_PATH . DS . 'controllers' . DS . strtolower($className) . '.php');
	}
	
	// Check Module controllers
	elseif(@file_exists(APP_PATH . DS . 'modules' . DS . strtolower($className) . DS . 'controller.php')) 
	{
		require_once(APP_PATH . DS . 'modules' . DS . strtolower($className) . DS . 'controller.php');
	}
	
	// Check application models
	elseif(file_exists(APP_PATH . DS .'models' . DS . strtolower($className) . '.php')) 
	{
		require_once(APP_PATH . DS .'models' . DS . strtolower($className) . '.php');
	}
	
	// We have an error as there is no classname
	else 
	{
		show_error(3, 'Autoload failed to load class: '. $className, __FILE__, __LINE__);
	}
}


/*
| ---------------------------------------------------------------
| Method: config()
| ---------------------------------------------------------------
|
| This function is used to return a config value from a config
| file.
|
| @Param: $item - The config item we are looking for
| @Param: $type - Either App, DB, or Core. Loads the respective
|		config file
|
*/

function config($item, $type = 'App')
{
	global $Config;
	
	switch($type)
	{
		case "App":
			return $Config->get($item, $type);
			break;
		
		case "Core":
			return $Config->get($item, $type);
			break;
			
		case "DB":
			return $Config->getDbInfo($item);
			break;
			
		default:
			show_error(1, "Unknown config type: \"". $type ."\"");
			break;
	}
}

/*
| ---------------------------------------------------------------
| Method: config_set()
| ---------------------------------------------------------------
|
| This function is used to set site config values. This does not
| set core, or database values.
|
| @Param: $item - The config item we are setting a value for
| @Param: $value - the value of $item
|
*/

function config_set($item, $value)
{
	global $Config;
	
	$Config->set($item, $value);
}

/*
| ---------------------------------------------------------------
| Method: config_save()
| ---------------------------------------------------------------
|
| This function is used to save site config values to the condig.php. 
| This does not save core, module, or database values.
|
*/

function config_save()
{
	global $Config;	
	$Config->Save();
}

/*
| ---------------------------------------------------------------
| Method: get_config_vars()
| ---------------------------------------------------------------
|
| This function is used to get all defined variables from a config
| file.
|
| @Param: $file - full path to the config file being loaded
|
*/

function get_config_vars($file)
{	
	$data = array();
	$vars = array();
	
	// Include file
	include( $file );
	$vars = @get_defined_vars();
	if(count($vars) > 1)
	{
		foreach( $vars as $key => $val ) 
		{
			$data[$key] = $val;
		}
	}
	return $data;
}	

/*
| ---------------------------------------------------------------
| Method: load_module_config()
| ---------------------------------------------------------------
|
| This function is used to load a modules config file, and add
| those config values to the site config.
|
| @Param: $module - Name of the module
| @Param: $filename - name of the file if not 'config.php'
|
*/

function load_module_config($module, $filename = 'config.php')
{	
	$file = ''. APP_PATH . DS .'modules' . DS . $module . DS . 'config' . DS . $filename;
	if(file_exists($file))
	{
		$MC = get_config_vars($file);
		if(count($MC) > 1)
		{
			foreach($MC as $key => $value)
			{
				config_set($key, $value);
			}
		}
	}
	return TRUE;
}	

/*
| ---------------------------------------------------------------
| Function: &get_instance()
| ---------------------------------------------------------------
|
| Gateway to adding an outside class or file into the base controller
|
*/	
	function get_instance()
	{
		$class = config('instance', 'Core');
		$class = ucfirst($class);
		return $class::get_instance();
	}

/*
| ---------------------------------------------------------------
| Function: load_class()
| ---------------------------------------------------------------
|
| This function is used to load and store core classes statically 
| that need to be loaded for use, but not reset next time the class
| is called.
|
| @Param: $class - Class needed to be loaded / returned
| @Param: $args - Suppossed to be the args passed to the class method
|	experementatl!
|
*/

function load_class($class, $args = NULL)
{
	// Lets get the prefix
	$prefix = config('subclass_prefix', 'Core');
	
    $Obj = Registry::singleton();
    
	// lowercase classname
    $Class = strtolower($class);
	$className = ucfirst($Class);
	
	// if class already stored, then just return the class
	// We load the custom contollers first 	
    if ($Obj->load($prefix . $Class) !== NULL)
    { 
        return $Obj->load($prefix . $Class);        
    }
    
    // if class already stored, then just return the class  
    elseif ($Obj->load($Class) !== NULL)
    { 
        return $Obj->load($Class);        
    }

	// Check for needed classes from the Application library folder
	if(file_exists(APP_PATH . DS .  'library' . DS . $prefix . $className . '.php')) 
	{
		require_once(APP_PATH . DS .  'library' . DS . $prefix . $className . '.php');
	}
	
	// Check for needed classes from the Core library folder
	elseif(file_exists(CORE_PATH . DS .  'library' . DS . $className . '.php')) 
	{
		require_once(CORE_PATH . DS .  'library' . DS . $className . '.php');
	}
	else
	{
		return FALSE;
	}
    
    // Initiate the new class
	if($args !== NULL) 
	{
		$dispatch = new $className($args);
	}
	else
	{
		$dispatch = new $className();
	}
	
	// Store this new object in the registery
    $Obj->store($Class, $dispatch); 
    
    //return singleton object.
    $Object = $Obj->load($Class);

    if(is_object($Object))
	{
		return $Object;
	}
}

/*
| ---------------------------------------------------------------
| Method: redirect()
| ---------------------------------------------------------------
|
| This function is used to easily redirect and refresh pages
|
| @Param: $url - Where were going
| @Param: $type - 0 - direct header, 1 - meta refresh
| @Param: $wait - Only if $type = 1, then how many sec's we wait
|
*/

function redirect($url, $type = 0, $wait = 0)
{
	// Check for a valid URL. If not then add our current BASE_URL to it.
	if(!preg_match('|^http(s)?://|i', $url) || !preg_match('|^ftp://|i', $url))
	{
		$url = BASE_URL . $url;
	}
	
	// Check for refresh or straight redirect
	if($type == 1)
	{
		header("Refresh:". $wait .";url=". $url);
	}
	else
	{
		header("Location: ".$url);
	}
}
// EOF