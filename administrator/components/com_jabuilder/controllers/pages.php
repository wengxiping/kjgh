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

class JabuilderControllerPages extends JControllerAdmin {

	public function getModel($name = 'Page', $prefix = 'JabuilderModel', $config = array('ignore_request' => true)) {

		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}

	public function delete() {
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');
		foreach ($cid as $id) {
			$this->deleteMenu($id);
		}
		parent::delete();
	}

	public function deleteMenu($id) {
		$q = "DELETE FROM #__menu WHERE link = 'index.php?option=com_jabuilder&view=page&id=$id' AND home != 1";
		$db = JFactory::getDbo();
		$db->setQuery($q);
		$db->execute();
	}

}
