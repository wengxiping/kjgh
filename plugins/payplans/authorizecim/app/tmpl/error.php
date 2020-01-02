<?php
/**
* @package      PayPlans
* @copyright    Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
if(defined('_JEXEC')===false) die();?>
<?php XiHtml::stylesheet('style.css',PayplansHelperUtils::pathFS2URL(dirname(__FILE__).DS.'_css'.DS)); ?>
<?php if(!empty($errors)):?>
	<div class="authorize-response-error">
		<p class="authorize-response-reason-code">      
				<?php echo JText::_('COM_PAYPLANS_APP_AUTHORIZE_RESPONSE_REASON_CODE').": ";?>
				<?php echo empty($errors['response_reason_code'])? '':JString::ucfirst($errors['response_reason_code']); ?>
		</p>
		<p class="authorize-response-code">       
				<?php echo JText::_('COM_PAYPLANS_APP_AUTHORIZE_RESPONSE_CODE'). ": ";?>
				<?php echo empty($errors['response_code'])? '':JString::ucfirst($errors['response_code']); ?>
		</p>
		<p class="authorize-response-reason">           
				<?php echo JText::_('COM_PAYPLANS_APP_AUTHORIZE_RESPONSE_REASON_TEXT').": ";?>
				<?php echo empty($errors['response_reason_text'])? '':JString::ucfirst($errors['response_reason_text']); ?>
		</p>
    </div>
<?php endif;?>