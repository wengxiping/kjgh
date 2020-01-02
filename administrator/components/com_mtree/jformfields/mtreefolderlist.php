<?php
/**
 * @package	Mosets Tree
 * @subpackage	JFormFields
 * @copyright	Copyright (C) 2016 Mosets Consulting. All rights reserved.
 * @license	GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.folder');
JFormHelper::loadFieldClass('folderlist');

/**
 * Supports an HTML select list of folder

 */
class JFormFieldMtreeFolderList extends JFormFieldFolderList
{
	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects containing folder names.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		$options = array();

		$path = $this->directory;

		if (!is_dir($path))
		{
			$path = JPATH_ROOT . '/' . $path;
		}

		// Prepend some default options based on field attributes.
		if (!$this->hideNone)
		{
			$options[] = JHtml::_('select.option', '-1', JText::alt('JOPTION_DO_NOT_USE', preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)));
		}

		if (!$this->hideDefault)
		{
			$options[] = JHtml::_('select.option', '', JText::alt('JOPTION_USE_DEFAULT', preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)));
		}

		// Get a list of folders in the search path with the given filter.
		$folders = JFolder::folders($path, $this->filter, $this->recursive, false);

		// Build the options list from the list of folders.
		if (is_array($folders))
		{
			foreach ($folders as $folder)
			{
				// Check to see if the file is in the exclude mask.
				if ($this->exclude)
				{
					if (preg_match(chr(1) . $this->exclude . chr(1), $folder))
					{
						continue;
					}
				}

				// Remove the root part and the leading /
				$folder = trim(str_replace($path, '', $folder), '/');

				$options[] = JHtml::_('select.option', $folder, $folder);
			}
		}

		return $options;
	}
}
