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

if (!JFactory::getUser()->authorise('core.manage', 'com_jabuilder'))
{
	throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
}

JLoader::register('JabuilderHeper', __DIR__ . '/helpers/jabuilder.php');

$controller = JControllerLegacy::getInstance('jabuilder');

$input = JFactory::getApplication()->input;

$controller->execute($input->getCmd('task'));

$controller->redirect();