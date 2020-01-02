<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Invites table class
 *
 * @since  1.0
 */
class TableInvites extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   object  &$db  Database connector object
	 */
	public function __construct(& $db)
	{
		parent::__construct('#__invitex_imports', 'id', $db);
	}
}

/**
 * Invites Emails table class
 *
 * @since  1.0
 */
class TableInvitesEmails extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   object  &$db  Database connector object
	 */
	public function __construct(& $db)
	{
		parent::__construct('#__invitex_imports_emails', 'id', $db);
	}
}

/**
 * Invites Types table class
 *
 * @since  1.0
 */
class TableInvitesTypes extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   object  &$db  Database connector object
	 */
	public function __construct(& $db)
	{
		parent::__construct('#__invitex_types', 'id', $db);
	}
}
