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

class PayplansAppFormatter extends PayplansFormatter
{	
	function getIgnoredata()
	{
		$ignore = array('_trigger', '_component', '_name', '_errors', '_tplVars','_location');
		return $ignore;
	}
	
	/**
	 * override parent applyFormatterRules to handle app_params
	 *  $data is passes through reference
	 */
	public function applyFormatterRules(&$data,$rules)
	{
		$new = array();

		foreach ($data['previous'] as $key => $value)
		{	
			// if there is some rule for that param then apply rule
			if(array_key_exists($key, $rules)){
				$args = array(&$key, &$value ,$data['previous']);
				$this->callFormatRule($rules[$key]['formatter'],$rules[$key]['function'], $args);	
			}
			// handling of app params 
			// display all app params in new line 
			if(in_array($key,array('app_params','core_params')))
			{
				foreach($value as $param=>$v){
					$new['previous'][$param]= $v;
				}
				unset($new['previous'][$key]);
				continue;
			}
			$new['previous'][$key]= $value;
		}	
		
		foreach ($data['current'] as $key => $value)
		{
			// if there is some rule for that param then apply rule
			if(array_key_exists($key, $rules)){
				$args = array(&$key, &$value,$data['current']);
				$this->callFormatRule($rules[$key]['formatter'],$rules[$key]['function'], $args);
			}	
			// handling of app params 
			// display all app params in new line 
			if(in_array($key,array('app_params','core_params')))
			{
				foreach($value as $param=>$v){
					$new['current'][$param]= $v;
				}
				unset($new['current'][$key]);
				continue;
			}
			$new['current'][$key]= $value;
		}	
		
		$data['previous'] = isset($new['previous'])? $new['previous']: '';
		$data['current'] = isset($new['current']) ? $new['current'] : '';
		unset($new);
	}
	
	// get applicable apps on plans
	function getApplicableApps($key,$value,$data)
	{
		// if not array convert to array
		$value = is_array($value) ? $value : array($value);
		$key   = JText::_('COM_PAYPLANS_LOG_KEY_APPLICABLE_APPS');
		$apps = array();
		foreach ($value as $v)
		{   
			if(empty($v)){
				continue;
			}
			$app   = PayplansApp::getInstance($v);
			$apps[]= PayplansHtml::link(XiRoute::_("index.php?option=com_payplans&view=app&task=edit&id=".$app->getId(), false), $app->getId().'('.$app->getTitle().')');
		}
		$value = $apps;
	}
	
	// get app's plans
	function getAppPlans($key,$value,$data)
	{
		// if not array convert to array
		$value = is_array($value) ? $value : array($value);
		$key = JText::_('COM_PAYPLANS_LOG_KEY_PLAN');
		$plans = array();
		foreach ($value as $v){
				if(empty($v)){
					continue;
				}
			$planName = PayplansHelperPlan::getName($v);
			$plans[]  = PayplansHtml::link(XiRoute::_("index.php?option=com_payplans&view=plan&task=edit&id=".$v, false), $v.'('.$planName.')');
		}
		
		$value = $plans;
	}

	// get rules
	public function getVarFormatter()
	{
		$rules = array('_appplans' => array('formatter'=> 'PayplansAppFormatter', 'function' => 'getAppPlans'));
		return $rules;
	}
	
	function getAppName($key,$value,$data)
	{
		$app = PayplansApp::getInstance($value);
		$value = PayplansHtml::link(XiRoute::_("index.php?option=com_payplans&view=app&task=edit&id=".$value, false), $value.'('.$app->getTitle().')');
		
	}
}