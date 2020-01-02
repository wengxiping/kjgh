<?php
defined('_JEXEC') or die('Restricted access'); // no direct access

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

$target="";
 $rel="";
  $class="";
$invite_url=urlencode(base64_encode($invite_url));

$link="index.php?option=com_invitex&view=invites&Itemid=".$itemid."&invite_type=".$invite_type."&invite_url=".$invite_url."&catch_act=".$catch_act."&invite_anywhere=1";
if($open_module_in=='1')
  $target="_self";
else
	$target="_blank";


$input_value=$button_text;
?>
<div class="<?php echo INVITEX_WRAPPER_CLASS;?>">
	<div class="invite_anywhere">
		<a href="<?php echo $link;?>" target="<?php echo $target?>" rel="<?php echo $rel?>" class="<?php echo $class?>"><input type="button" class='btn btn-primary' name="invite_anywhere" value="<?php echo $input_value ?>" /></a>
	</div>
</div>
