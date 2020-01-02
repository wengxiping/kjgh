<?php
/*------------------------------------------------------------------------
# com_zhbaidumap - Zh BaiduMap
# ------------------------------------------------------------------------
# author:    Dmitry Zhuk
# copyright: Copyright (C) 2011 zhuk.cc. All Rights Reserved.
# license:   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
# website:   http://zhuk.cc
# Technical Support Forum: http://forum.zhuk.cc/
-------------------------------------------------------------------------*/
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla controllerform library
jimport('joomla.application.component.controllerform');

/**
 * ZhBaidu MapMarker Controller
 */
class ZhBaiduMapControllerMapMarker extends JControllerForm
{


	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;
		$user = JFactory::getUser();
		$userId = $user->get('id');

		$canDo = ZhBaiduMapHelper::getMarkerActions($recordId);
		
		$canEdit    = $canDo->get('core.edit');
		$isEnabledEditOwn = $canDo->get('core.edit.own');
		
		
		if ($canEdit || $canEditOwn)
		{
		}
		
		// Check general edit permission first.
		if ($canEdit)
		{
			return true;
		}

		// Fallback on edit.own.
		// First test if the permission is available.
		if ($isEnabledEditOwn)
		{
			// Now test the owner is the user.
			$ownerId = (int) isset($data['createdbyuser']) ? $data['createdbyuser'] : 0;
			if (empty($ownerId) && $recordId)
			{
				// Need to do a lookup from the model.
				$record = $this->getModel()->getItem($recordId);

				if (empty($record))
				{
					return false;
				}

				$ownerId = $record->createdbyuser;
			}

			// If the owner matches 'me' then do the test.
			if ($ownerId == $userId)
			{
				return true;
			}
		}

		// Since there is no asset tracking, revert to the component permissions.
		return parent::allowEdit($data, $key);
	}
	
	
}
