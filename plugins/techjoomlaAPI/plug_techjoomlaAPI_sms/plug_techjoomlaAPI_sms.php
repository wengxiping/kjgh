<?php
/**
 * @version    SVN: <svn_id>
 * @package    Invitex
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die();
jimport('joomla.plugin.plugin');

/*load language file for plugin frontend*/
$lang = JFactory::getLanguage();
$lang->load('en-GB.plg_techjoomlaAPI_plug_techjoomlaAPI_sms', JPATH_ADMINISTRATOR);

/**
 * PlgSystemInvitex_Sms plugin
 *
 * @package     Com_Invitex
 *
 * @subpackage  site
 *
 * @since       2.2
 */
class PlgTechjoomlaAPIplug_TechjoomlaAPI_Sms extends JPlugin
{
	/**
	 * The function to render plugin html
	 *
	 * @param   ARRAY  $config  plugin configuration
	 *
	 * @return ARRAY plugin
	 */
	public function renderPluginHTML($config)
	{
		$plug = array();
		$plug['name'] = $this->params->get('display_name');

		$sms_options = $this->params->get('sms_options', '');

		if (!empty($sms_options))
		{
			$plug['api_used'] = $this->_name;
			$plug['message_type'] = 'sms';
			$plug['img_file_name'] = "sms.png";

			if (isset($config['client']))
			{
				$client = $config['client'];
			}
			else
			{
				$client = '';
			}

			return $plug;
		}
		else
		{
			$plug['error_message'] = true;

			return $plug;
		}
	}

	/**
	 * Function to send SMS
	 *
	 * @param   MIXED  $mail  mail content
	 * @param   ARRAY  $post  post
	 *
	 * @return ARRAY success 0 or 1
	 */
	public function plug_techjoomlaAPI_smssend_message($mail,$post)
	{
		$post = (object) $post;

		require JPATH_SITE . '/components/com_invitex/helper.php';

		$cominvitexHelper = new cominvitexHelper;
		$invitex_settings = $cominvitexHelper->getconfigData();

		if ($post->invite_type > 0)
		{
			$types_res = $cominvitexHelper->types_data($post->invite_type);
			$template = stripslashes($types_res->template_clickatell);
		}
		else
		{
			$template = stripslashes($invitex_settings->get('sms_message_body'));
		}

		$mail['msg_body'] = $template;
		$message = $cominvitexHelper->tagreplace($mail);
		$dispatcher = JDispatcher::getInstance();
		$plugin_name = $this->params['sms_options'];

		if (!empty($mail['message']))
		{
			JPluginHelper::importPlugin('sms', $plugin_name);
			$smsresult = $dispatcher->trigger('onSmsSendMessage', array($message, $post));
		}

		return $smsresult[0];
	}
}
