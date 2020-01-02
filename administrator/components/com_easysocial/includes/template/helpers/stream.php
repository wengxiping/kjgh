<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class ThemesHelperStream extends ThemesHelperAbstract
{
	/**
	 * Generates the cluster stream object
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function cluster(SocialCluster $cluster)
	{
		$type = $cluster->getType();

		$theme = ES::themes();
		$theme->set('cluster', $cluster);
		$theme->set('type', $type);
		
		$output = $theme->output('site/helpers/stream/cluster');
		
		return $output;
	}

	/**
	 * Renders the file stream object
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function files($files)
	{
		$theme = ES::themes();
		$theme->set('files', $files);

		$output = $theme->output('site/helpers/stream/files');

		return $output;
	}

	/**
	 * Generates the user stream object
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function user(SocialUser $user)
	{
	}

	/**
	 * Generates the achievements stream object
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function achievement()
	{
	}

	/**
	 * Generates the article object for stream
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function article()
	{
	}

	/**
	 * Generates the video object for stream
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function video()
	{
	}

	/**
	 * Generates the broadcast object for stream
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function broadcasts($broadcast)
	{
		$theme = ES::themes();
		$theme->set('broadcast', $broadcast);
		$output = $theme->output('site/helpers/stream/broadcasts');

		return $output;
	}
}
