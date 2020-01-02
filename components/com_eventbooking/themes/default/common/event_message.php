<?php
/**
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2019 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */

defined( '_JEXEC' ) or die;

if (!$event->is_multiple_date
	&& !$event->can_register
	&& $event->registration_type != 3
	&& $config->display_message_for_full_event
	&& !$event->waiting_list && $event->registration_start_minutes >= 0)
{
	$bootstrapHelper    = EventbookingHelperBootstrap::getInstance();
	$viewLevels         = JFactory::getUser()->getAuthorisedViewLevels();
	$loginLink          = JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode(JUri::getInstance()->toString()), false);
	$loginToRegisterMsg = str_replace('[LOGIN_LINK]', $loginLink, JText::_('EB_LOGIN_TO_REGISTER'));

	if (@$event->user_registered)
	{
		$msg = JText::_('EB_YOU_REGISTERED_ALREADY');
	}
	elseif (!in_array($event->registration_access, $viewLevels))
	{
		if (JFactory::getUser()->id)
		{
			$msg = JText::_('EB_REGISTRATION_NOT_AVAILABLE_FOR_ACCOUNT');
		}
		else
		{
			$msg = $loginToRegisterMsg;
		}
	}
	else
	{
		$msg = JText::_('EB_NO_LONGER_ACCEPT_REGISTRATION');
	}
	?>
		<div class="<?php echo $bootstrapHelper->getClassMapping('clearfix'); ?>">
			<p class="text-info eb-notice-message"><?php echo $msg; ?></p>
		</div>
	<?php
}