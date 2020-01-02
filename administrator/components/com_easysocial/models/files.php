<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

jimport('joomla.application.component.model');

ES::import('admin:/includes/model');

class EasySocialModelFiles extends EasySocialModel
{
	private $data = null;
	protected $pagination = null;

	public function __construct()
	{
		parent::__construct('files');
	}

	/**
	 * Retrieves the pagination object based on the current query.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->pagination))
		{
			jimport('joomla.html.pagination');
			$this->pagination = new JPagination($this->total, $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->pagination;
	}

	/**
	 * Retrieves the list of items which stored in Amazon
	 *
	 * @since	1.4.6
	 * @access	public
	 */
	public function getFilesStoredExternally($storageType = 'amazon')
	{
		// Get the number of files to process at a time
		$config = ES::config();
		$limit = $config->get('storage.amazon.limit', 10);

		$db = ES::db();
		$sql = $db->sql();
		$sql->select('#__social_files');
		$sql->where('storage', $storageType);
		$sql->limit($limit);

		$db->setQuery($sql);

		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * Delete files for specific uid and type
	 *
	 * @since	1.4
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function deleteFiles($uid, $type)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_files');
		$sql->where('uid', $uid);
		$sql->where('type', $type);

		$db->setQuery($sql);
		$rows = $db->loadObjectList();

		if (!$rows) {
			return false;
		}

		foreach ($rows as $row) {
			$file = ES::table('File');
			$file->bind($row);

			$file->delete();
		}

		return true;
	}

	/**
	 * Retrieves the total number of files
	 *
	 * @since	2.0
	 * @access	public
	 * @return	int		Total number of files
	 */
	public function getTotalFiles($uid, $type, $options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->column('COUNT(1)', 'total');
		$sql->select('#__social_files', 'a');

		if (ES::config()->get('users.blocking.enabled') && !JFactory::getUser()->guest) {
			$sql->leftjoin('#__social_block_users', 'bus');

			$sql->on('(');
			$sql->on( 'a.user_id' , 'bus.user_id' );
			$sql->on( 'bus.target_id', JFactory::getUser()->id);
			$sql->on(')');

			$sql->on('(', '', '', 'OR');
			$sql->on( 'a.user_id' , 'bus.target_id' );
			$sql->on( 'bus.user_id', JFactory::getUser()->id );
			$sql->on(')');

			$sql->isnull('bus.id');
		}

		$sql->where('a.uid', $uid);
		$sql->where('a.type', $type);

		// Determines if we should filter by specific collection
		$collection = isset($options['collection_id']) ? $options['collection_id'] : '';

		if ($collection) {
			$sql->where('a.collection_id', $collection);
		}

		$db->setQuery($sql);

		$total = $db->loadResult();

		return $total;
	}

	/**
	 * Retrieves a list of files
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getItems($options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_files');

		if (isset($options['storage'])) {
			$sql->where('storage', $options['storage']);
		}

		if (isset($options['limit'])) {
			$sql->limit($options['limit']);
		}

		// Determines if we should order by specific ordering
		$ordering = isset($options['ordering']) ? $options['ordering'] : '';

		if ($ordering) {

			$sorting = isset($options['sort']) ? $options['sort'] : 'DESC';

			if ($ordering == 'random') {
				$sql->order('', '', 'RAND');
			}

			if ($ordering == 'created') {
				$sql->order('created', $sorting);
			}
		}

		// If there's an exclusion list, exclude it
		$exclusion = isset($options['exclusion']) ? $options['exclusion'] :'';

		if (!empty($exclusion)) {

			// Ensure that it's an array
			$exclusion	= ES::makeArray($exclusion);

			foreach ($exclusion as $id) {
				$sql->where('id', $id, '!=', 'AND');
			}

		}

		// echo $sql;exit;
		$db->setQuery($sql);

		$result = $db->loadObjectList();

		if (!$result) {
			return $result;
		}

		$files = array();

		foreach ($result as $row) {
			$file 	= ES::table('File');
			$file->bind($row);

			$files[] = $file;
		}

		return $files;
	}

	/**
	 * Retrieves a list of files for a particular type.
	 *
	 * @since	2.0.20
	 * @access	public
	 */
	public function getFiles($uid, $type, $options = array())
	{
		$config = ES::config();
		$db = ES::db();

		$query = array();
		$query[] = 'SELECT a.* FROM ' . $db->nameQuote('#__social_files') . ' as a';


		if ($config->get('users.blocking.enabled') && !JFactory::getUser()->guest) {
			// user block
			$query[] = ' LEFT JOIN ' . $db->nameQuote('#__social_block_users') . ' as bus';

			$query[] = ' ON (';
			$query[] = ' a.' . $db->nameQuote('user_id') . ' = bus.' . $db->nameQuote('user_id');
			$query[] = ' AND bus.' . $db->nameQuote('target_id') . ' = ' . $db->Quote(JFactory::getUser()->id);
			$query[] = ') OR (';
			$query[] = ' a.' . $db->nameQuote('user_id') . ' = bus.' . $db->nameQuote( 'target_id' ) ;
			$query[] = ' AND bus.' . $db->nameQuote('user_id') . ' = ' . $db->Quote(JFactory::getUser()->id) ;
			$query[] = ')';

		}

		$query[] = 'WHERE a.' . $db->nameQuote('type') . '=' . $db->Quote($type);

		if ($config->get('users.blocking.enabled') && !JFactory::getUser()->guest) {
			// user block continue here
			$query[] = ' AND bus.' . $db->nameQuote('id') . ' IS NULL';
		}

		// Ensure that uid is in an array form.
		$uid = ES::makeArray($uid);

		$query[] = 'AND a.' . $db->nameQuote('uid') . ' IN (';

		foreach ($uid as $id) {
			$query[]	= $db->Quote($id);

			if (next($uid) !== false) {
				$query[]	= ',';
			}
		}

		$query[] = ')';

		if (isset($options['state'])) {
			$publishOption 	= $options['state'] ? '1' : '0';

			$query[] = ' AND a.' . $db->nameQuote('state') . '=' . $db->Quote($publishOption);
		}

		// Test for collection id
		$collectionId = isset($options['collection_id']) ? $options['collection_id'] : false;

		if ($collectionId) {
			$query[] = ' AND a.' . $db->nameQuote('collection_id') . '=' . $db->Quote($collectionId);
		}

		$query[] = ' ORDER BY a.' . $db->nameQuote('created') . ' desc';

		$query = implode(' ', $query);

		$limit = isset($options['limit']) ? $options['limit'] : false;

		if ($limit) {
			$this->setState('limit', $limit);

			// Get the limitstart.
			$limitstart = $this->getUserStateFromRequest('limitstart', 0);
			$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

			$this->setState('limitstart', $limitstart);

			// Run pagination here.
			$this->setTotal($query, true);

			$result = $this->getData($query);
		} else {
			$db->setQuery($query);
			$result = $db->loadObjectList();
		}

		if (!$result) {
			return false;
		}

		$files 	= array();

		foreach ($result as $row) {
			$file 	= ES::table('File');
			$file->bind($row);

			$files[] = $file;
		}

		return $files;
	}

	/**
	 * Retrieves a list of files for a particular stream.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getStreamFiles($streamId, $isCluster = false)
	{
		$db = ES::db();

		$query = "select `params` from `#__social_stream_item` as a";
		$query .= " where a.`uid` = " . $db->Quote($streamId);
		$query .= " and a.`context_type` = " . $db->Quote('files');

		$db->setQuery($query);
		$result = $db->loadObject();

		$files = array();

		$data = ES::json()->decode($result->params);

		$items = array();
		if ($isCluster) {
			$items = $data->file;
		} else {
			$items = $data->files;
		}

		if ($items) {
			$query = "select * from `#__social_files` as a";
			$query .= " where a.`id` IN (" . implode(',', $items). ")";
			$query .= " and a.`state` = " . $db->Quote(SOCIAL_STATE_PUBLISHED);

			$db->setQuery($query);
			$rows = $db->loadObjectList();

			if ($rows) {
				foreach ($rows as $row) {
					$tbl = ES::table('File');
					$tbl->bind($row);

					$files[] = $tbl;
				}
			}

		}

		return $files;
	}

	/**
	 * update [add or remove] files that associated with stream.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function updateStreamFiles($streamId, $files, $isCluster = false)
	{
		$db = ES::db();

		$filesRemove = array();

		if ($files) {

			// get the default values
			$streamItem = ES::table('StreamItem');
			$streamItem->load(array('uid' => $streamId));

			$curFiles = array();

			$data = ES::json()->decode($streamItem->params);
			if ($isCluster) {
				$curFiles = $data->file;
			} else {
				$curFiles = $data->files;
			}

			foreach ($files as $fileId) {

				if (! is_numeric($fileId)) {
					if(is_array($fileId)) {
						$filesRemove[] = $fileId['remove'];

					} else if (is_object()) {
						$filesRemove[] = $fileId->remove;

					}
					continue;
				}

				$curFiles[] = $fileId;
			}

			if ($filesRemove) {

				// update the files list.
				$curFiles = array_diff($curFiles, $filesRemove);

				// it seems like after array_diff, the result is now associative array. lets get the value only.
				$curFiles = array_values($curFiles);

				// Now that we know the saving is successfull, we want to update the state of the photo table.
				foreach ($filesRemove as $fileId) {
					$table = ES::table('File');
					$table->load($fileId);

					// debug
					$state = $table->delete(null, '', false);
				}
			}

			// now we update the files list in the param
			$params = FD::registry();
			if ($isCluster) {
				$params->set('file', $curFiles);
			} else {
				$params->set('files', $curFiles);
			}

			$streamItem->params = $params->toString();
			$streamItem->store();
		}

		return true;
	}

	/**
	 * Retrieves a list of files for a particular type.
	 *
	 * @since	2.0.20
	 * @access	public
	 */
	public function getGdprFiles($userId, $excludeIds = array(), $limit = 20)
	{
		$db = ES::db();

		$query = "select a.* from `#__social_files` as a";
		$query .= " where a.`user_id` = " . $db->Quote($userId);

		if ($excludeIds) {
			$query .= " AND a.`id` NOT IN (" . implode(',', $excludeIds) . ") ";
		}

		$query .= " order by a.`id`";
		$query .= " limit " . $limit;

		$db->setQuery($query);
		$results = $db->loadObjectList();

		$files 	= array();

		if ($results) {
			foreach ($results as $row) {
				$file 	= ES::table('File');
				$file->bind($row);

				$files[] = $file;
			}
		}

		return $files;
	}

}
