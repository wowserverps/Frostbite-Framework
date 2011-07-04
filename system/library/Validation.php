<?php
/* 
| --------------------------------------------------------------
| 
| Frostbite Framework
|
| --------------------------------------------------------------
|
| Author: 		Steven Wilson
| Copyright:	Copyright (c) 2011, Steven Wilson
| License: 		GNU GPL v3
|
| ---------------------------------------------------------------
| Class: Validation()
| ---------------------------------------------------------------
|
| A class built to validate user input from forms
|
*/
namespace System\Library;

class Validation
{
	// Our fields
    public $fields;
	
	// Our field rules
    public $field_rules;
	
	// A bool of whether we are debugging
    public $debug;
	
	// Our running list of errors
    private $errors;
 
 /*
| ---------------------------------------------------------------
| Constructer
| ---------------------------------------------------------------
|
*/
    function __construct()
    {
		// Init the default values
		$this->fields = $_POST;
		$this->field_rules = array();
		$this->errors = array();
		$this->debug = FALSE;
    }

/*
| ---------------------------------------------------------------
| Function: set_rules()
| ---------------------------------------------------------------
|
| This function is used to simplify the showing of errors
|
| @Param: (Array) $rules_array - An array of each post var =>
|	"rules"... 
|
|	The following rules that can be set are:
|
|	'required' = Field must contain a value (excluding whitespace)
|	'email' = Field must contain a valid email address
|	'number' = Field must contain a valid number
|	'min[#]' = Field Must contain a number at minimun (#)
|	'max[#]' = Field Must contain a number at maximum (#)
|	'pattern[..]' = Field must contain a valid pattern(..)
|
| @Return: (None)
|
*/	
	public function set_rules($rules_array)
	{
		if(!is_array($rules_array))
		{
			show_error('non_array', array('rules_array', 'Validation::set_rules'), E_ERROR);
		}
		
		// Add the current rules
		$this->field_rules = array_merge($this->field_rules, $rules_array);
		
		// Allow chaining here
		return $this;
	}

/*
| ---------------------------------------------------------------
| Function: validate()
| ---------------------------------------------------------------
|
| This function validates all the POST data that has rules set
|
| @Return: (Bool) True if all POST data passed validation, FALSE
|	otherwise
|
*/	
	public function validate()
	{
		// before we begin, make sure we have post data
		if(!empty($this->fields) || !empty($this->field_rules))
		{
			// Validate each of the fields that have rules
			foreach($this->field_rules as $field => $rules)
			{
				$rules = explode('|', $rules);

				// Process each rule for this post var
				foreach($rules as $rule)
				{
					$result = NULL;

					// Make sure that the field we are looking at exists
					if(isset($this->fields[$field]))
					{
						// We will define the param as FALSE, if preg_match
						// finds a second value, then it will overwrite this
						$param = false;

						if (preg_match("/(.*?)\[\~(.*?)\~\]/", $rule, $match))
						{
							$rule = $match[1];
							$param = $match[2];
						}

						// Strip html and php tags, and escape backslashes
						$this->fields[$field] = $this->clean($this->fields[$field]);

						// Call the function that corresponds to the rule
						if (!empty($rule)) $result = $this->$rule($this->fields[$field], $param);

						// Handle errors
						if ($result === false) $this->set_error($field, $rule);
					}
				}
			}
 
			// If we have no errors, then return a TRUE
			return (empty($this->errors));
        }
    }
 
/*
| ---------------------------------------------------------------
| Function: get_errors()
| ---------------------------------------------------------------
|
| This function returns an array of all the errors by field name
|
| @Return: (Array) Returns an array of errors
|
*/
	public function get_errors()
	{
		if(count($this->errors) == 0)
		{
			return array();
		}
		return $this->errors;
	}

/*
| ---------------------------------------------------------------
| Function: set_error()
| ---------------------------------------------------------------
|
| This method sets an error for the $field
|
| @Param: (String) $field - The field name that failed validation
| @Param: (String) $rule - The rule that made the $field fail 
| @Return: (None)
|
*/
	protected function set_error($field, $rule)
	{
		// If debugging, we want an array of all failed validations
		if($this->debug == TRUE)
		{
			if(isset($this->errors[$field]))
			{
				$this->errors[$field] .= "|".$rule;
				return;
			}
			$this->errors[$field] = $rule;
			return;
		}
		$this->errors[$field] = TRUE;
	}

/*
| ---------------------------------------------------------------
| Function: required()
| ---------------------------------------------------------------
|
| This method determines if the string passed has any values
|
| @Param: (String) $string - the string we are checking
| @Return: (Bool) TRUE if the field has value, FALSE otherwise
|
*/
	public function required($string, $value = false)
	{
		if (!is_array($string))
		{
			// Trim white space and see if its still empty
			$string = trim($string);
			return (!empty($string));
		}
		else
		{
			return (count($string) > 0);
		}
	}

/*
| ---------------------------------------------------------------
| Function: email()
| ---------------------------------------------------------------
|
| This method determines if the string is a valid email
|
| @Param: (String) $string - the string we are checking
| @Return: (Bool) TRUE if the field is an email, FALSE otherwise
|
*/
	public function email($string)
	{
		if(filter_var($string, FILTER_VALIDATE_EMAIL))
		{
			return TRUE;
		}
		return FALSE;
	}

/*
| ---------------------------------------------------------------
| Function: number()
| ---------------------------------------------------------------
|
| This method determines if the string passed is numeric
|
| @Param: (String) $string - the string we are checking
| @Return: (Bool) TRUE if the field is numeric, FALSE otherwise
|
*/
	public function number($string)
	{
		return (is_numeric($string));
	}

/*
| ---------------------------------------------------------------
| Function: min()
| ---------------------------------------------------------------
|
| This method determines if the string passed has a minimum value
| of $value
|
| @Param: (String) $string - the string we are checking
| @Param: (Int) $value - The minimum value
| @Return: (Bool) TRUE if the field has value, FALSE otherwise
|
*/
	public function min($string, $value)
	{
		if(!is_numeric($string))
		{
			return (strlen($string) >= $value);
		}
		return ($string >= $value);
	}

/*
| ---------------------------------------------------------------
| Function: max()
| ---------------------------------------------------------------
|
| This method determines if the string passed has a maximum value
| of $value
|
| @Param: (String) $string - the string we are checking
| @Param: (Int) $value - The minimum value
| @Return: (Bool) TRUE if the field has value, FALSE otherwise
|
*/
	public function max($string, $value)
	{
		if(!is_numeric($string))
		{
			return (strlen($string) >= $value);
		}
		return ($string <= $value);
	}

/*
| ---------------------------------------------------------------
| Function: pattern()
| ---------------------------------------------------------------
|
| This method determines if the string passed contains the specified
|	pattern
|
| @Param: (String) $string - the string we are checking
| @Param: (String) $pattern - The pattern we use to validate
| @Return: (Bool) TRUE if the field contains the pattern, else FALSE
|
*/
	public function pattern($string, $pattern)
	{
		return (!preg_match("/".$pattern."/", $string)) ? FALSE : TRUE;
	}

/*
| ---------------------------------------------------------------
| Function: clean()
| ---------------------------------------------------------------
|
| This method cleans the string of any html / php tags
|
| @Param: (String) $string - the string we are checking
| @Return: (String) Returns the cleaned $string
|
*/
    public function clean($string)
    {
        if (is_array($string))
		{
			$temp = array();
			foreach($string as $key)
			{
				$temp[] = $this->clean($key);
			}
			return $temp;
		}

		// Return the strip with all tags stripped
		return strip_tags( trim(htmlentities($string)) );
    }
}
//EOF