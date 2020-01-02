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

class PPEventConfig extends PayPlans
{
	public static function onPayplansViewBeforeExecute($view, $task)
	{
		if($view instanceof PayplansadminViewConfig){
			
			require_once JPATH_ROOT.'/components/com_payplans/includes/includes.php';
			
			//get all files required for setup
			$allrules = XiHelperSetup::getOrderedRules();
			//for each file check that setup is required or not & get message a/c to this.
			$rules = array();
			foreach($allrules as $setup){
				//get object of class
				$object = XiSetup::getInstance($setup);
				if($object == false || !$object->isApplicable()){
					continue;
				}

				$object->isRequired();
				$rules[]=$object;
			}
			$htmlSetupChecklist = PayplansEventConfig::getSetupChecklist($rules);
			
			//code to get sample data
			//IMP : need to load plugins
			XiHelperPlugin::loadPlugins('payplansmigration');
			$args= array();
			$icons = PayplansHelperEvent::trigger('onPayplansDisplayMigrationAction', $args);
			$htmlImportData = PayplansEventConfig::getMigrationData($icons);

			return array('payplans-admin-config-checklist' => $htmlSetupChecklist,
						 'payplans-admin-config-importdata' => $htmlImportData);
		}
	}

	public static function getSetupChecklist($rules)
	{
		ob_start();
		$counter = 1;
		$required= 0;
		?>
		
		<div class="mod-setup">
		<?php foreach($rules as $rule) :?>
			<?php
				$text = $rule->getMessage();
				$pClass = 'pp-done';
				if($rule->isRequired()){
					$required++;
					$pClass = 'pp-required';
					$text ='<a href="index.php?option=com_payplans&view=support&task=setup&action=doApply&name='.$rule->_name
							. '&from='.$rule->_returl.'">'.$text.'</a>';
				}
			?>
			<div id="setup-<?php echo $counter; ?>" class="setuprule-<?php echo $rule->_name; ?>">
				<div class="pp-rule pp-<?php echo $rule->getType().' '.$pClass;?>" >
					<span class="hasTip" title="<?php echo $rule->getTooltip();?>" payplans-tipsy-gravity="sw" >
						<?php echo $text;?>
					</span>
				 </div>
			</div>
			<?php $counter++;  ?>
		<?php endforeach; ?>
		</div>
		<?php
		$html = ob_get_contents();
		ob_clean();
		return $html;
	}

	public static function getMigrationData($icons)
	{
		ob_start();
		?>
		<div style="height:70px;">
		<div class="pp-module-migration">
			<div class="pp-icons">
				<?php foreach($icons as $key => $value) : ?>
					<?php if($value !== true && $value !== false && is_array($value)) :?>
						<div class="pp-icon pp-cssbutton white" onClick="xi.dashboard.preMigration('<?php echo $value['key']?>'); return false;">
								<img src="<?php echo $value['icon']?>" alt="<?php echo $value['key']?>" />
								<br />			
								<span><?php echo $value['title'];?></span>
						</div>
					<?php endif;?>		 
				<?php endforeach;?>
			</div>
		</div>
		</div>
		<?php 
		$html = ob_get_contents();
		ob_clean();
		return $html;
	}
}
