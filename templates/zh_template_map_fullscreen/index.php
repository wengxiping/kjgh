<?php
/*------------------------------------------------------------------------
# tpl_zh_template_map_fullscreen - Zh Template Map FullScreen
# ------------------------------------------------------------------------
# author    Dmitry Zhuk
# copyright Copyright (C) 2011 zhuk.cc. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
# Websites: http://zhuk.cc
# Technical Support Forum: http://forum.zhuk.cc/
-------------------------------------------------------------------------*/
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.framework', true);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" >
<head>
	<jdoc:include type="head" />
	<link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/system/css/system.css" type="text/css" />
	<link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/system/css/general.css" type="text/css" />
	<link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/template.css" type="text/css" />
	
	<meta name=viewport content="width=device-width, initial-scale=1">
<?php
	$tplAddJS = $this->params->get( 'js2load' );
	$tplListJS = explode(';', str_replace(array("\r", "\r\n", "\n"), ';', $tplAddJS));

	
	for($i = 0; $i < count($tplListJS); $i++) 
		{
			$currJS = trim($tplListJS[$i]);
			if ($currJS != "")
			{
				$this->addScript($currJS);
			}
		}
?>	
<?php
	$tplAddCSS = $this->params->get( 'css2load' );
	$tplListCSS = explode(';', str_replace(array("\r", "\r\n", "\n"), ';', $tplAddCSS));

	
	for($i = 0; $i < count($tplListCSS); $i++) 
		{
			$currCSS = trim($tplListCSS[$i]);
			if ($currCSS != "")
			{
				$this->addStyleSheet($currCSS);
			}
		}
?>	
</head>
<body>
	<jdoc:include type="modules" name="top" /> 
	<jdoc:include type="component" />
	<jdoc:include type="modules" name="bottom" />
</body>
</html>