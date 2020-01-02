<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

jimport('joomla.filesystem.file');

class SocialString
{
	private $adapter = null;

	public function __construct()
	{
		$this->config = ES::config();
	}

	public static function factory()
	{
		return new self();
	}

	public function __call($method , $arguments)
	{
		if (method_exists($this->adapter , $method)) {
			return call_user_func_array(array($this->adapter , $method) , $arguments);
		}

		return false;
	}

	/**
	 * Our own implementation of only allowing safe html tags
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function filterHtml($str)
	{
		// We can't use JComponentHelper::filterText because by default registered users aren't allowed html codes
		$filter = JFilterInput::getInstance(array(), array(), 1, 1);
		$str = $filter->clean($str, 'html');

		return $str;
	}

	/**
	 * Converts color code into RGB values
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public static function hexToRGB($hex)
	{
		$hex = str_ireplace('#', '', $hex);
		$rgb = array();
		$rgb['r'] = hexdec(substr($hex, 0, 2));
		$rgb['g'] = hexdec(substr($hex, 2, 2));
		$rgb['b'] = hexdec(substr($hex, 4, 2));

		$str = $rgb['r'] . ',' . $rgb['g'] . ',' . $rgb['b'];
		return $str;
	}

	/**
	 * Determines if a given string is in ascii format
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function isAscii($text)
	{
		return (preg_match('/(?:[^\x00-\x7F])/', $text) !== 1);
	}

	/**
	 * Computes a noun given the string and count
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function computeNoun($string , $count)
	{
		$zeroAsPlural = $this->config->get('zeroasplural.enabled' , true);

		// Always use plural
		$text = $string . '_PLURAL';

		if ($count == 1 || $count == -1 || ($count == 0 && !$zeroAsPlural)) {
			$text 	= $string 	. '_SINGULAR';
		}

		return $text;
	}

	/**
	 * Convert a list of names into a string valid for notifications
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function namesToNotifications($userIds, $page = false)
	{
		// Ensure that users is an array
		$userIds 	= ES::makeArray($userIds);

		// Ensure that they are all SocialUser objects
		$tmpUsers = ES::user($userIds);
		$users = array();

		// Due to cache mechanism in ES::user(), there are instances where the ordering will be messed up.
		// To fix this, Re-organized the users to always follow the correct ordering. #3438
		// or directly fix in ES::user() @loadUsers
		foreach ($userIds as $userId) {
			foreach ($tmpUsers as $user) {
				if ($user->id != $userId) {
					continue;
				}

				$users[] = $user;
			}
		}

		// If the page exists, we need to include it.
		if ($page) {
			$users[] = $page;
		}

		// Get the total number of users
		$total = count($users);

		// Init the name variable
		$name = '';

		// If there's only 1 user, we don't need to do anything.
		if ($total == 1) {
			$name 	= '{b}' . $users[0]->getName() . '{/b}';

			return $name;
		}

		// user1 and user2
		if ($total == 2) {
			$name 	= JText::sprintf('COM_EASYSOCIAL_STRING_NOTIFICATIONS_NAMES_AND' , $users[0]->getName() , $users[ 1 ]->getName());
		}

		// user1, user2 and user3
		if ($total == 3) {
			$name 	= JText::sprintf('COM_EASYSOCIAL_STRING_NOTIFICATIONS_NAMES_AND_USER' , $users[0]->getName() , $users[ 1 ]->getName(), $users[2]->getName());
		}

		// user1, user2, user3 and user4
		if ($total == 4) {
			$name 	= JText::sprintf('COM_EASYSOCIAL_STRING_NOTIFICATIONS_NAMES_AND_USERS' , $users[0]->getName() , $users[ 1 ]->getName() , $users[ 2 ]->getName() , $users[ 3 ]->getName());
		}

		// user1, user2, user3 and 2 others
		if ($total >= 5) {
			$name 	= JText::sprintf('COM_EASYSOCIAL_STRING_NOTIFICATIONS_NAMES_USER_AND_OTHERS' , $users[0]->getName() , $users[ 1 ]->getName() , $users[ 2 ]->getName(), $total - 3);
		}

		return $name;
	}

	/**
	 * Determines the type of parameters parsed to this method and automatically returns a stream-ish like content.
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function namesToStream($users , $linkUsers = true , $limit = 3 , $uppercase = true , $boldNames = false, $showPopbox = false)
	{
		// Ensure that users is an array
		$users = ES::makeArray($users);

		// Ensure that they are all SocialUser objects
		$users 	= ES::user($users);

		$theme 	= ES::themes();

		$theme->set('users', $users);
		$theme->set('boldNames', $boldNames);
		$theme->set('linkUsers', $linkUsers);
		$theme->set('total', count($users));
		$theme->set('limit', $limit);
		$theme->set('uppercase', $uppercase);
		$theme->set('showPopbox', $showPopbox);

		$message = $theme->output('site/utilities/users');
		$message = JString::trim($message);

		return $message;
	}

	/**
	 * Replaces email text with html codes
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function replaceEmails($text)
	{
		if (strpos($text, 'data:image') !== false) {
			return $text;
		}

		// $pattern = '/(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))/is';
  //   	$replace = '<a href="mailto:$0">$0</a>';

		// lets first replace the base64 image string.
		$pattern = '/([\w\.]+@([a-zA-Z0-9]+?\.)+[a-zA-Z0-9]{2,6})/';
		$replace = '<a href="mailto:$1">$1</a>';

		return preg_replace($pattern , $replace, $text);
	}

	/**
	 * Replaces hyperlink text with html anchors
	 *
	 * @since	1.0
	 * @access	public
	 */
	public static function replaceHyperlinks($text, $options=array('target'=>'_blank'), $tag = 'anchor')
	{
		$attributes = '';

		foreach ($options as $key => $val) {
			$attributes .= " $key=\"$val\"";
		}

		$pattern = '@(?i)\b((?:https?://|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))@';

		preg_match_all($pattern, $text, $matches);

		// Do not proceed if there are no links to process
		if (!isset($matches[0]) || !is_array($matches[0]) || empty($matches[0])) {

			return $text;
		}

		$tmplinks = $matches[0];

		$links = array();
		$linksWithProtocols = array();
		$linksWithoutProtocols = array();

		// We need to separate the link with and without protocols to avoid conflict when there are similar url present in the content.
		if ($tmplinks) {
			foreach($tmplinks as $link) {

				if (stristr($link , 'http://') === false && stristr($link , 'https://') === false && stristr($link , 'ftp://') === false) {
					$linksWithoutProtocols[] = $link;
				} else if (stristr($link , 'http://') !== false || stristr($link , 'https://') !== false || stristr($link , 'ftp://') === false) {
					$linksWithProtocols[] = $link;
				}
			}
		}

		// the idea is the first convert the url to [ESWURLx] and [ESWOURLx] where x is the index. This is to prevent same url get overwritten with wrong value.
		$linkArrays = array();

		// global indexing.
		$idx = 1;

		// lets process the one with protocol
		if ($linksWithProtocols) {
			$linksWithProtocols = array_unique($linksWithProtocols);

			foreach($linksWithProtocols as $link) {

				$mypattern = '[ESWURL' . $idx . ']';

				$text = str_ireplace($link, $mypattern, $text);

				$obj = new stdClass();
				$obj->index = $idx;
				$obj->link = $link;
				$obj->newlink = $link;
				$obj->customcode = $mypattern;

				$linkArrays[] = $obj;

				$idx++;
			}
		}

		// Now we process the one without protocol
		if ($linksWithoutProtocols) {
			$linksWithoutProtocols = array_unique($linksWithoutProtocols);

			foreach($linksWithoutProtocols as $link) {
				$mypattern = '[ESWOURL' . $idx . ']';
				$text = str_ireplace($link, $mypattern, $text);

				$obj = new stdClass();
				$obj->index = $idx;
				$obj->link = $link;
				$obj->newlink = $link;
				$obj->customcode = $mypattern;

				$linkArrays[] = $obj;

				$idx++;
			}
		}

		// Let's replace back the link now with the proper format based on the index given.
		if ($linkArrays) {
			foreach ($linkArrays as $link) {

				$text = str_ireplace($link->customcode, $link->newlink, $text);


			}

			$patternReplace = '@(?<![.*">])\b(?:(?:https?|ftp|file)://|[a-z]\.)[-A-Z0-9()+&#/%=~_|$?!;:,.]*[A-Z0-9()+&#/%=~_|$]@i';

			// Use preg_replace to only replace if the URL doesn't has <a> tag
			$text = preg_replace($patternReplace, '<a href="\0" ' . $attributes . '>\0</a>', $text);
		}

		return $text;
	}

	/**
	 * Determines the type of parameters parsed to this method and automatically
	 * returns a stream-ish like string.
	 *
	 * E.g: name1 , name2 and name3
	 *
	 * @param	Array of object containing name and link property
	 * @return 	string
	 */
	public function beautifyNamestoStream($data)
	{
		$datatring = '';
		$j = 0;
		$cntData = count($data);
		foreach ($data as $item) {

			if (empty($datatring)) {
				$text = '<a href="' . $item->link . '">' . $item->name . '</a>';
				$datatring	= $text;
			} else {
				if (($j + 1) == $cntData) {
					$text = '<a href="' . $item->link . '">' . $item->name . '</a>';
					$datatring = $datatring . ' and ' . $text;
				} else {
					$datatring = $datatring . ', ' . $text;
				}
			}

			$j++;
		}

		return $datatring;
	}

	/**
	 * Convert special characters to HTML entities
	 *
	 * @param	string
	 * @return  string
	 */
	public function escape($var)
	{
		return htmlspecialchars($var, ENT_COMPAT, 'UTF-8');
	}

	/**
	 * This is useful if the inital tag processing is using the simple mode so that we can revert back to the original tags
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function processSimpleTags($message)
	{
		$pattern 	= '/\[tag\](.*)\[\/tag\]';

		preg_match_all($pattern . '/uiU', $message , $matches , PREG_SET_ORDER);

		if ($matches) {
			foreach ($matches as $match) {
				$jsonString = html_entity_decode($match[ 1 ]);
				$obj = ES::json()->decode($jsonString);

				if (!isset($obj->type)) {
					continue;
				}

				if ($obj->type == 'entity') {
					$replace = '<a href="' . $obj->link . '" data-popbox="module://easysocial/profile/popbox" data-popbox-position="top-left" data-user-id="' . $obj->id . '" class="mentions-user">' . $obj->title . '</a>';
				}

				if ($obj->type == 'hashtag') {
					$replace = '<a href="' . $obj->link . '" class="mentions-hashtag">#' . $obj->title . '</a>';
				}

				$message = str_ireplace($match[ 0 ] , $replace , $message);
			}
		}

		return $message;
	}

	/**
	 * Parse emoticons from content
	 *
	 * @since   3.0
	 * @access  public
	 */
	public function parseEmoticons($content, $replaceWithCharacter = '')
	{
		$model = ES::model('Emoticons');
		$emoticons = $model->getItems();

		foreach ($emoticons as $smile) {
			$table = ES::table('Emoticon');
			$table->bind($smile);

			$search = ':(' . $table->title . ')';
			$replace = ($replaceWithCharacter) ? $replaceWithCharacter : $table->getIcon();

			$pattern = '/(?:' . preg_quote($search, '/') . ')/is';
			$content = preg_replace($pattern, ' ' . $replace . ' ', $content);
		}

		return $content;
	}

	/**
	 * Parse emoticons from content
	 *
	 * @since   3.0
	 * @access  public
	 */
	public function matchEmoticons($content, $replaceWithCharacter = '*')
	{
		$model = ES::model('Emoticons');
		$emoticons = $model->getItems();

		$segments = array();

		$i = 0;
		foreach ($emoticons as $smile) {

			$search = ':(' . $smile->title . ')';
			$patterns = '/(?:' . preg_quote($search, '/') . ')/is';

			preg_match_all($patterns, $content, $matches);

			if ($matches && isset($matches[0]) && count($matches[0]) > 0) {

				$m = $matches[0][0];

				$replacement = $replaceWithCharacter . $i++;

				$obj = new stdClass();
				$obj->smiley = $m;
				$obj->segment = $replacement;

				$segments[] = $obj;

				$content = JString::str_ireplace($m, $replacement, $content);
			}
		}

		if ($segments) {
			$data = new stdClass();

			$data->newtext = $content;
			$data->matches = $segments;

			return $data;
		}

		return false;
	}

	/**
	 * process notification content to show with emiticons and truncation.
	 *
	 * @since   3.0
	 * @access  public
	 */
	public function processEmoWithTruncate($text, $len = 40)
	{
		$tmpContent = $text;

		// check if we need to replace emoticons or not.
		$data = ES::string()->matchEmoticons($text);

		// we need to replace emoticons with a two characters before we
		// can test the content len. #3005

		if ($data !== false) {
			$tmpContent = $data->newtext;
		}

		// flag to determine if elipse need to be appended into the content.
		$addElipse = false;

		if (JString::strlen($tmpContent) > $len) {
			$tmpContent = JString::substr(strip_tags($tmpContent), 0, $len);

			// need to do a rtrim incase the last character is * which processed from matchEmoticons
			$tmpContent = JString::rtrim($tmpContent, '*');
			$addElipse = true;
		}

		if ($data !== false) {
			// there is emoticons. lets process it properly.
			$matches = $data->matches;

			foreach ($matches as $item) {
				$replacement = $item->smiley;
				$m = $item->segment;

				$tmpContent = JString::str_ireplace($m, $replacement, $tmpContent);
			}

			// finally we convert emoticons into images
			$tmpContent = ES::string()->parseEmoticons($tmpContent);
		}

		$tmpContent .= ($addElipse) ? JText::_('COM_EASYSOCIAL_ELLIPSES') : '';

		return $tmpContent;
	}

	/**
	 * Method to process the tags after the pre-processing has finished
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function afterProcessTags($tags, $message)
	{
		foreach ($tags as $tag) {
			if (isset($tag->replace) && $tag->replace) {
				$message = str_ireplace($tag->tmpReplace, $tag->replace, $message);
			}
		}

		return $message;
	}

	/**
	 * Processes a text and replace the mentions / hashtags hyperlinks.
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function processTags(&$tags , $message , $simpleTags = false, $tmpReplace = false)
	{
		$i = 1;

		// We need to merge the mentions and hashtags since we are based on the offset.
		foreach ($tags as $tag) {

			$replace = '';

			if ($tag->type == 'entity' || $tag->type == 'user') {

				if (isset($tag->user) && $tag->user instanceof SocialUser) {
					$user = $tag->user;
				} else {
					$user = ES::user($tag->item_id);
				}

				if ($simpleTags) {
					$data = new stdClass();
					$data->type = $tag->type;
					$data->link = $user->getPermalink();
					$data->title = $user->getName();
					$data->id = $user->id;

					$replace = '[tag]' . ES::json()->encode($data) . '[/tag]';
				} else {
					$replace 	= '<a href="' . $user->getPermalink() . '" data-popbox="module://easysocial/profile/popbox" data-popbox-position="top-left" data-user-id="' . $user->id . '" class="mentions-user">' . $user->getName() . '</a>';
				}
			}

			if ($tag->type == 'hashtag') {
				$alias = JFilterOutput::stringURLSafe($tag->title);

				$url = FRoute::dashboard(array('layout' => 'hashtag' , 'tag' => $alias));

				if ($simpleTags) {
					$data = new stdClass();
					$data->type = $tag->type;
					$data->link = $url;
					$data->title = $tag->title;
					$data->id = $tag->id;

					$replace = '[tag]' . ES::json()->encode($data) . '[/tag]';
				} else {
					$replace = '<a href="' . $url . '" class="mentions-hashtag">#' . $tag->title . '</a>';
				}
			}

			if ($tag->type == 'emoticon') {
				$title = str_replace(array('(', ')'), '', $tag->title);

				// Load the emoticon using title
				$table = ES::table('emoticon');
				$table->load(array('title' => $title));

				if ($table->id) {
					$replace = $table->getIcon();
				}
			}

			if ($replace) {

				// Add support for temporary replacement. #3122
				if ($tmpReplace) {
					$tag->tmpReplace = '[[ESTAGREPLACE_' . $i . ']]';
					$tag->replace = $replace;

					$replace = $tag->tmpReplace;
				}

				$message = JString::substr_replace($message, $replace, $tag->offset, $tag->length);
			}

			$i++;
		}

		return $message;
	}

	/**
	 * Replaces gist links into valid gist objects
	 *
	 * @since	1.2
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function replaceGist($content)
	{
		$pattern = '/https:\/\/gist\.github\.com\/(.*)(?=)/is';
		//
		$content = preg_replace($pattern, '<script src="$0.js"></script>', $content);

		return $content;
	}

	/**
	 * Convert blocks data into valid html codes
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function parseBBCode($string, $options = array())
	{
		// Configurable option to determine if the bbcode should perform the following
		$options = array_merge(array('censor' => false, 'emoticons' => true), $options);

		$bbcode = ES::bbcode();

		$string = $bbcode->parse($string, $options);

		return $string;
	}

	/**
	 * An alternative to encodeURIComponent equivalent on javascript.
	 * Useful when we need to use decodeURIComponent on the client end.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function encodeURIComponent($contents)
	{
		$revert = array('%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')');

		return strtr(rawurlencode($contents), $revert);
	}

	/**
	 * Method to remove unwanted spacing between each words
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function trimSpacing($str)
	{
		// First we remove the spacing at each end of the word
		$str = JString::trim($str);

		// Next remove unwanted space in between each words
		$str = preg_replace('/\s\s+/', ' ', JString::str_ireplace("\n", " ", $str));

		return $str;
	}

	/**
	 * Method to truncate the string while maintaining the HTML integrity of the string
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function truncateWithHtml($text, $max = 250, $ending = '', $exact = false)
	{
		// splits all html-tags to scanable lines
		preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);

		$total_length = JString::strlen($ending);
		$open_tags = array();
		$truncate = '';

		foreach ($lines as $line_matchings) {

			// if there is any html-tag in this line, handle it and add it (uncounted) to the output
			if (!empty($line_matchings[1])) {

				// if it's an "empty element" with or without xhtml-conform closing slash
				if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
					// do nothing
				// if tag is a closing tag
				} else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {

					// delete tag from $open_tags list
					$pos = array_search($tag_matchings[1], $open_tags);

					if ($pos !== false) {
						unset($open_tags[$pos]);
					}

				// if tag is an opening tag
				} else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {

					// add tag to the beginning of $open_tags list
					array_unshift($open_tags, JString::strtolower($tag_matchings[1]));
				}

				// add html-tag to $truncate'd text
				$truncate .= $line_matchings[1];
			}

			// calculate the length of the plain text part of the line; handle entities as one character
			$content_length = JString::strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));

			if ($total_length + $content_length > $max) {

				// the number of characters which are left
				$left = $max - $total_length;
				$entities_length = 0;

				// search for html entities
				if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {

					// calculate the real length of all entities in the legal range
					foreach ($entities[0] as $entity) {
						if ($entity[1] + 1 - $entities_length <= $left) {
							$left--;
							$entities_length += JString::strlen($entity[0]);
						} else {
							// no more characters left
							break;
						}
					}
				}
				$truncate .= JString::substr($line_matchings[2], 0, $left + $entities_length);
				// maximum lenght is reached, so get off the loop
				break;
			} else {
				$truncate .= $line_matchings[2];
				$total_length += $content_length;
			}

			// if the maximum length is reached, get off the loop
			if ($total_length >= $max) {
				break;
			}
		}

		// If the words shouldn't be cut in the middle...
		if (!$exact) {

			// ...search the last occurance of a space...
			$spacepos = JString::strrpos($truncate, ' ');

			// ...and cut the text in this position
			if (isset($spacepos)) {

				// lets further test if the about truncate string has a html tag or not.
				$remainingString = JString::substr($truncate, $spacepos + 1);
				$remainingString = trim($remainingString);

				// check if string contain any html closing/opening tag before we proceed. #463
				$closingTagV1 = JString::strpos($remainingString, '>');
				$closingTagV2 = JString::strpos($remainingString, '/>');

				// Everything is safe. Let's truncate it.
				if ((!$closingTagV1 && !$closingTagV2) || ($closingTagV1 === 0 && $closingTagV2 === 0)) {
					$truncate = JString::substr($truncate, 0, $spacepos);
				}
			}
		}

		// add the defined ending to the text
		$truncate .= $ending;

		// close all unclosed html-tags
		foreach ($open_tags as $tag) {
			$truncate .= '</' . $tag . '>';
		}

		return $truncate;
	}
}
