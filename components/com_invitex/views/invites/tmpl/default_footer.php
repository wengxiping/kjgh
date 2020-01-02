<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die( 'Restricted access' );

$itemid = $this->invhelperObj->getitemid('index.php?option=com_invitex&view=invites');
$itemid_resend	= $this->invhelperObj->getitemid('index.php?option=com_invitex&view=invites');

// Revoke consent
$user = JFactory::getUser();

$invitationTermsAndConditions = $this->invitex_params->get('invitationTermsAndConditions', '0');
$tNcArticleId = $this->invitex_params->get('tNcArticleId', '0');
$session = JFactory::getSession();
$tncAccepted = $session->get('tj_send_invitations_consent');

if (!empty($invitationTermsAndConditions) && !empty($tNcArticleId) && !empty($tncAccepted))
{
	?>
	<div class="alert alert-warning">
		<?php echo JText::sprintf('COM_INVITEX_DECLINE_PRIVACY_CONSENT_MSG', $this->privacyPolicyLink)?>
	</div>
	<?php
}

if(!$itemid_resend)
{
	$itemid_resend	= $itemid;
}

$resend_link	=	JRoute::_("index.php?option=com_invitex&view=resend&Itemid=$itemid_resend",false);

$itemid_stats	= $this->invhelperObj->getitemid('index.php?option=com_invitex&view=stats');

if(!$itemid_stats)
{
	$itemid_stats	= $itemid;
}

$stat_link	=	JRoute::_("index.php?option=com_invitex&view=stats&Itemid=$itemid_stats",false);

$itemid_urlstats	= $this->invhelperObj->getitemid('index.php?option=com_invitex&view=stats');

if(!$itemid_urlstats)
{
	$itemid_urlstats	= $itemid;
}

$URLstat_link	=	JRoute::_("index.php?option=com_invitex&view=urlstats&Itemid=$itemid_urlstats",false);

?>
<div id="consentToken">
	<?php echo JHtml::_('form.token'); ?>
</div>
<div class="<?php echo INVITEX_WRAPPER_CLASS;?>">
	<div class="invitex_footer">
		<div class="row">
			<div class="col-md-4 col-sm-12"><a href="<?php echo $resend_link;?>"><?php echo JText::_('RE_SEND');?></a></div>
			<div class="col-md-4 col-sm-12"><a href="<?php echo $stat_link;?>"><?php echo JText::_('IX_STATS');?></a></div>
			<div class="col-md-4 col-sm-12"><a href="<?php echo $URLstat_link;?>"><?php echo JText::_('URL_STATS');?></a></div>
		</div>
	</div>
</div>
