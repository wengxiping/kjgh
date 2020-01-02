<?php

/**
 * ------------------------------------------------------------------------
 * JA Builder Package
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2018 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
 */

defined('_JEXEC') or die;

$input = JFactory::getApplication()->input;

$id = $input->getCmd('id');

$user = JFactory::getUser();

$session = JFactory::getSession();

$session_id = $session->getId();

$name = $user->username;

$url = JURI::root().'index.php?option=com_jabuilder&task=login.autologin&name='.$name.'&session_id='.$session_id.'&id='.$id;

$title = 'Live Edit'; 
?>
<a href="<?php echo $url ?>" target="_blank" class="btn btn-small btn-warning">
	<span class="icon-share" title="<?php echo $title; ?>"></span> <?php echo $title; ?>
</a>