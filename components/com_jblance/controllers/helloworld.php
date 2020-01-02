<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_helloworld
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.controller');

class JblanceControllerMessageHelloWorld extends JControllerLegacy {
    function __construct(){
        parent :: __construct();
    }

   function saveMenuAction(){//保存菜单操作
       JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
       $app  		= JFactory::getApplication();
       $db 		= JFactory::getDbo();
       $now 		= JFactory::getDate();
       $user 		= JFactory::getUser();
       $post 		= $app->input->post->getArray();
       $message 	= JTable::getInstance('content', 'Table');
       $message->save($post);
       $this->setRedirect("return", "这是一段消息");
   }

}


// Get an instance of the controller prefixed by HelloWorld
$controller = JControllerLegacy::getInstance('HelloWorld');

// Perform the Request task
$input = JFactory::getApplication()->input;



$controller->execute($input->getCmd('task'));

// Redirect if set by the controller
$controller->redirect();
