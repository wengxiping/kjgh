<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

jimport('joomla.filesystem.file');

class PPLogFormatter
{	
	// Set default template for logs
	public $template = 'view';
	
	public static function writer($previous, $current)
	{	
		$content['previous'] = $previous ? (method_exists($previous, 'toArray') ? $previous->toArray() : (array)$previous ) : array();
		$content['current'] = method_exists($current, 'toArray') ? $current->toArray() : (array) $current;
		
		return $content;
	}
	
	/**
	 * Retrieve the adapter for the log formatter
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getAdapter($type)
	{
		$file = __DIR__ . '/adapters/' . $type . '.php';

		if (!JFile::exists($file)) {
			return false;
		}

		include_once($file);

		$className = 'Payplans' . ucwords($type) . 'Formatter';
		$adapter = new $className();
		
		return $adapter;
	}

	/**
	 * Retrieves an objects formatter
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getFormatter($type)
	{
		// type consists of PayplansSubscription, PayplansOrder etc. We need to strip it out
		$type = str_ireplace('Payplans', '', $type);
		$type = strtolower($type);

		$adapter = $this->getAdapter($type);

		return $adapter;
	}


	public function formatter($content,$type=null)
	{    
		// if content is not array convert it in array
		if(!is_array($content))
		{
			$content = array($content);
		}
		// if content has previous or current set
		if(array_key_exists('previous', $content) || array_key_exists('current', $content)){
			$data = $this->_formatter($content);
		}
		// if content doesn't have previous and current set
		// for email logs and error logs and cron logs
		else {			
			$data['previous']  	= array();
			$data['current'] 	= $content;
		}
	   return $data; 		
	}
	
	public function callFormatRule($formatter,$functionName,$args)
	{
		//XiToDo:: call function on instance or use call_user_func_array
		if($formatter){
			$call = array($formatter, $functionName);
		}else{
			$call = $functionName;
		}
		return call_user_func_array($call,$args);
	}
	
	/**
	 * 
	 * apply rules on data 
	 *  $data is passes through reference
	 */
	public function applyFormatterRules(&$data,$rules)
	{
		$new = array();

		foreach ($data['previous'] as $key => $value){
			if(array_key_exists($key, $rules)){
				$args = array(&$key, &$value ,$data['previous']);
				$this->callFormatRule($rules[$key]['formatter'],$rules[$key]['function'], $args);	
			}
			$new['previous'][$key]= $value;
		}	
		
		foreach ($data['current'] as $key => $value){
			if(array_key_exists($key, $rules)){
				$args = array(&$key, &$value,$data['current']);
				$this->callFormatRule($rules[$key]['formatter'],$rules[$key]['function'], $args);	
			}
			$new['current'][$key]= $value;
		}	
		
		$data['previous'] = isset($new['previous'])? $new['previous']: '';
		$data['current']  = isset($new['current']) ? $new['current'] : '';
		unset($new);
	}
	
	public function _formatter($content)
	{
		$data['previous'] 	= array_key_exists('previous', $content)  	?  $content['previous']  	: array();
		$data['current'] 	= array_key_exists('current', $content)   	?  $content['current']   	: array();

		$data['previous'] = array_key_exists('previous', $data['previous'])? array_pop($data['previous']): $data['previous'];
		if(method_exists($this, 'getIgnoredata')){
			$ignore = $this->getIgnoredata();
		
			foreach($ignore as $key)
			{
				 unset($data['previous'][$key]);
				 unset($data['current'][$key]);
			}
		}
		
		if(method_exists($this, 'getVarFormatter')){
			$rules = $this->getVarFormatter();
			$this->applyFormatterRules($data,$rules);
		}
		return $data;
	}
	
	// format params in all logs
	public function getFormattedParams($key,$value,$data)
	{
		$key= JText::_('COM_PAYPLANS_LOG_KEY_PARAMS');
		
		$params = "";
		foreach($value as $index => $val)
		{
			$params .= $index.' = '.$val.',';
		}
		
		$params = explode(",", $params);
		$value  = implode("<br/>", $params);
		
	}
}