<?php
/**
 * @version     SVN: <svn_id>
 * @package     Invitex
 * @subpackage  mod_inviters
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */
jimport('joomla.application.module.helper');

// No direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.filesystem.file');

$path = JPATH_SITE . '/components/com_invitex/helper.php';

if (!class_exists('CominvitexHelper'))
{
	JLoader::register('CominvitexHelper', $path);
	JLoader::load('CominvitexHelper');
}

$invitexHelper = new CominvitexHelper;

$invitexHelper->loadInvitexAssetFiles();

$tjStrapperPath = JPATH_SITE . '/media/techjoomla_strapper/tjstrapper.php';

if (JFile::exists($tjStrapperPath))
{
	require_once $tjStrapperPath;
	TjStrapper::loadTjAssets('com_invitex');
}
?>

<div class="inviter-box <?php echo INVITEX_WRAPPER_CLASS . $params->get('moduleclass_sfx'); ?>">
	<div class="row-fluid">
		<div class="span5">
			<strong><?php echo JText::_('MOD_INVITERS_NAME');?></strong>
		</div>
		<div class="span3 center">
			<strong><?php echo JText::_('MOD_INVITERS_TOTAL_COUNT');?></strong>
		</div>
		<div class="span4 center">
			<strong><?php echo JText::_('MOD_INVITERS_TOTAL_ACCPT');?></strong>
		</div>
	</div>
	<?php
	foreach ($inviters as $inviter)
	{
	?>
	<div class="row-fluid">
		<div class="span5">
			<?php echo $inviter->username;?>
		</div>
		<div class="span3 center">
			<?php echo $inviter->total_sent;?>
		</div>
		<div class="span4 center">
			<?php echo $inviter->acc;?>
		</div>
	</div>
	<?php
	}
	?>
</div>
