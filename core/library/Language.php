<?php

class Language
{
	public $language_vars = array();
	public $loaded_files = array();

/*
| ---------------------------------------------------------------
| Function: load()
| ---------------------------------------------------------------
|
| Loads the lanugage file
|
| @Param: $file - Name of the language file, without the extension
| @Param: $lang - Language we are loading
| @Param: $return - Set to TRUE to return the $lang array, FALSE
|		to just save the variables here.
|
*/
	public function load($file = '', $lang = '', $return = FALSE)
	{
		// Add the extension
		$file = $file . '.php';
		
		// Forgot to include which language we are loading?
		if ($lang == '')
		{
			$lang = ( config('default_language') ? config('default_language') : 'english' );
		}

		// Determine where the language file is and load it
		if(file_exists(APP_PATH . DS .'language' DS . $lang . DS . $file))
		{
			include(APP_PATH . DS .'language' DS . $lang . DS . $file);
		}
		else
		{
			// Check alternate named language folder
			if (file_exists(APP_PATH . DS .'lang' DS . $lang . DS . $file))
			{
				include(APP_PATH . DS .'lang' DS . $lang . DS . $file);
			}
			else
			{
				// Only show an error if we arent able to return FALSE
				if($return == FALSE)
				{
					show_error(3, 'Unable to load the requested language file: '.$lang.'/'.$file);
				}
			}
		}

		// If the array "$lang" is none existant in the language file, we have an error
		if(!isset($lang))
		{
			return FALSE;
		}

		// Do we return the array?
		if($return == TRUE)
		{
			return $lang;
		}

		// Without a return, we need to store what we have here.
		$this->loaded_files[] = $file;
		$this->language_vars = array_merge($this->language_vars, $lang);
		unset($lang);
		return TRUE;
	}

/*
| ---------------------------------------------------------------
| Function: get()
| ---------------------------------------------------------------
|
| Returns the variable from the config array
|
| @Param: $var - the key of the lang array value, needed to be returned
|
*/
	public function get($var)
	{
		if(isset($this->language_vars[$var]))
		{
			return $this->language_vars[$var];
		}
		return FALSE;
	}
}
// EOF