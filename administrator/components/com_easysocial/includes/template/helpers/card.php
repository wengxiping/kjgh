<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class ThemesHelperCard extends ThemesHelperAbstract
{
	/**
	 * Generates the DOM structure for the card avatar
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function avatar($cluster)
	{
		$title = '';

		if ($cluster instanceof SocialUser) {
			$title = $cluster->getName();
		}

		if (method_exists($cluster, 'getTitle')) {
			$title = $cluster->getTitle();
		}

		$avatarUrl = $cluster->getAvatar();
		$permalink = $cluster->getPermalink();

		$theme = ES::themes();
		$theme->set('permalink', $permalink);
		$theme->set('avatarUrl', $avatarUrl);
		$theme->set('title', $title);

		$output = $theme->output('site/helpers/card/avatar');

		return $output;
	}

	/**
	 * Generates the DOM structure for the card cover
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function cover($cluster)
	{
		$coverUrl = $cluster->getCover();
		$backgroundPosition = $cluster->getCoverPosition();
		$permalink = $cluster->getPermalink();

		$theme = ES::themes();
		$theme->set('permalink', $permalink);
		$theme->set('coverUrl', $coverUrl);
		$theme->set('backgroundPosition', $backgroundPosition);

		$output = $theme->output('site/helpers/card/cover');

		return $output;
	}

	/**
	 * Generates the DOM structure for the card calendar
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function calendar($day, $month)
	{
		$theme = ES::themes();

		$theme->set('day', $day);
		$theme->set('month', $month);

		$output = $theme->output('site/helpers/card/calendar');

		return $output;
	}

	/**
	 * Generates the DOM structure for the card avatar
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function icon($type, $title)
	{
		$theme = ES::themes();

		$theme->set('title', $title);
		$theme->set('type', $type);

		$output = $theme->output('site/helpers/card/icon');

		return $output;
	}

	/**
	 * Generates the DOM structure for the card avatar
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function title($title, $permalink)
	{
		$theme = ES::themes();

		$theme->set('title', $title);
		$theme->set('permalink', $permalink);

		$output = $theme->output('site/helpers/card/title');

		return $output;
	}
}
