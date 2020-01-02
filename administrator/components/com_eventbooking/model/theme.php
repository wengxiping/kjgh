<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2019 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

class EventbookingModelTheme extends RADModelAdmin
{
	/**
	 * Pre-process data, store plugins param in JSON format
	 *
	 * @param      $row
	 * @param      $input
	 * @param bool $isNew
	 */
	protected function beforeStore($row, $input, $isNew)
	{
		$params = $input->get('params', array(), 'array');

		if (is_array($params))
		{
			$params = json_encode($params);
		}
		else
		{
			$params = null;
		}

		$input->set('params', $params);
	}

	/**
	 * Install a payment plugin from given package
	 *
	 * @param $themePackage
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function install($themePackage)
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.archive');

		$db = $this->getDbo();

		if ($themePackage['error'] || $themePackage['size'] < 1)
		{
			throw new Exception(JText::_('Upload theme package error'));
		}

		$tmpPath = JFactory::getConfig()->get('tmp_path');

		if (!JFolder::exists($tmpPath))
		{
			$tmpPath = JPATH_ROOT . '/tmp';
		}

		$destinationDir = $tmpPath . '/' . $themePackage['name'];

		$uploaded = JFile::upload($themePackage['tmp_name'], $destinationDir, false, true);

		if (!$uploaded)
		{
			throw new Exception(JText::sprintf('Could not upload theme package to %s folder', $destinationDir));
		}

		// Temporary folder to extract the archive into
		$tmpDir     = uniqid('install_');
		$extractDir = JPath::clean(dirname($destinationDir) . '/' . $tmpDir);

		if (version_compare(JVERSION, '4.0.0-dev', 'ge'))
		{
			$archive = new Joomla\Archive\Archive(array('tmp_path' => JFactory::getConfig()->get('tmp_path')));
			$result  = $archive->extract($destinationDir, $extractDir);
		}
		else
		{
			$result = JArchive::extract($destinationDir, $extractDir);
		}

		if (!$result)
		{
			throw new Exception(JText::sprintf('Could not extract plugin package to %s folder', $extractDir));
		}

		$dirList = array_merge(JFolder::files($extractDir, ''), JFolder::folders($extractDir, ''));

		if (count($dirList) == 1)
		{
			if (JFolder::exists($extractDir . '/' . $dirList[0]))
			{
				$extractDir = JPath::clean($extractDir . '/' . $dirList[0]);
			}
		}

		//Now, search for xml file
		$xmlFiles = JFolder::files($extractDir, '.xml$', 1, true);

		if (empty($xmlFiles))
		{
			throw new Exception(JText::_('Could not find xml file in the package'));
		}

		$file = $xmlFiles[0];
		$root = JFactory::getXML($file, true);

		if ($root->getName() != 'install')
		{
			throw new Exception(JText::_('Invalid xml file for theme installation'));
		}

		if ($root->attributes()->type != 'ebtheme')
		{
			throw new Exception(JText::_('Invalid xml file for theme installation'));
		}

		$row          = $this->getTable();
		$name         = (string) $root->name;
		$title        = (string) $root->title;
		$author       = (string) $root->author;
		$creationDate = (string) $root->creationDate;
		$copyright    = (string) $root->copyright;
		$license      = (string) $root->license;
		$authorEmail  = (string) $root->authorEmail;
		$authorUrl    = (string) $root->authorUrl;
		$version      = (string) $root->version;
		$description  = (string) $root->description;

		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__eb_themes')
			->where('name = ' . $db->quote($name));
		$db->setQuery($query);
		$pluginId = (int) $db->loadResult();

		if ($pluginId)
		{
			$row->load($pluginId);
			$row->name          = $name;
			$row->title         = $title;
			$row->author        = $author;
			$row->creation_date = $creationDate;
			$row->copyright     = $copyright;
			$row->license       = $license;
			$row->author_email  = $authorEmail;
			$row->author_url    = $authorUrl;
			$row->version       = $version;
			$row->description   = $description;
		}
		else
		{
			$row->name          = $name;
			$row->title         = $title;
			$row->author        = $author;
			$row->creation_date = $creationDate;
			$row->copyright     = $copyright;
			$row->license       = $license;
			$row->author_email  = $authorEmail;
			$row->author_url    = $authorUrl;
			$row->version       = $version;
			$row->description   = $description;
			$row->published     = 0;
			$row->ordering      = $row->getNextOrder('published=1');
		}

		$row->store();

		$themesDir = JPATH_ROOT . '/components/com_eventbooking/themes/' . $row->name;

		if (!JFolder::exists($themesDir))
		{
			JFolder::create($themesDir);
		}

		JFile::move($file, $themesDir . '/' . basename($file));

		$files = $root->files->children();

		for ($i = 0, $n = count($files); $i < $n; $i++)
		{
			$file = $files[$i];

			if ($file->getName() == 'filename')
			{
				$fileName = $file;
				JFile::copy($extractDir . '/' . $fileName, $themesDir . '/' . $fileName);
			}
			elseif ($file->getName() == 'folder')
			{
				$folderName = $file;

				if (JFolder::exists($extractDir . '/' . $folderName))
				{
					if (JFolder::exists($themesDir . '/' . $folderName))
					{
						JFolder::delete($themesDir . '/' . $folderName);
					}

					JFolder::move($extractDir . '/' . $folderName, $themesDir . '/' . $folderName);
				}
			}
		}


		// CSS, JS files
		if ($root->media && count($root->media->children()))
		{
			$files    = $root->media->children();
			$mediaDir = JPATH_ROOT . '/media/com_eventbooking/assets/themes/' . $row->name;

			if (!JFolder::exists($mediaDir))
			{
				JFolder::create($mediaDir);
			}

			$folder = (string) $root->media->attributes()->folder;

			if ($folder)
			{
				$source = $extractDir . '/' . $folder;
			}
			else
			{
				$source = $extractDir;
			}

			for ($i = 0, $n = count($files); $i < $n; $i++)
			{
				$file = $files[$i];

				if ($file->getName() == 'filename')
				{
					$fileName = $file;
					JFile::copy($source . '/' . $fileName, $mediaDir . '/' . $fileName);
				}
				elseif ($file->getName() == 'folder')
				{
					$folderName = $file;

					if (JFolder::exists($source . '/' . $folderName))
					{
						if (JFolder::exists($mediaDir . '/' . $folderName))
						{
							JFolder::delete($mediaDir . '/' . $folderName);
						}

						JFolder::move($source . '/' . $folderName, $mediaDir . '/' . $folderName);
					}
				}
			}
		}


		JFolder::delete($extractDir);

		return true;
	}

	/**
	 * Uninstall a payment plugin
	 *
	 * @param int $id
	 *
	 * @throws Exception
	 */
	public function uninstall($id)
	{
		$row = $this->getTable();
		$row->load($id);
		$name = $row->name;

		if ($name == 'default')
		{
			throw new Exception('Un-install default theme is not allowed');
		}

		$themeFolder = JPATH_ROOT . '/components/com_eventbooking/themes/' . $name;

		$file = $themeFolder . '/' . $name . '.xml';

		if (!JFile::exists($file))
		{
			$row->delete();

			return;
		}

		$root  = JFactory::getXML($file);
		$files = $root->files->children();

		for ($i = 0, $n = count($files); $i < $n; $i++)
		{
			$file = $files[$i];
			if ($file->getName() == 'filename')
			{
				$fileName = $file;
				if (JFile::exists($themeFolder . '/' . $fileName))
				{
					JFile::delete($themeFolder . '/' . $fileName);
				}
			}
			elseif ($file->getName() == 'folder')
			{
				$folderName = $file;

				if ($folderName)
				{
					if (JFolder::exists($themeFolder . '/' . $folderName))
					{
						JFolder::delete($themeFolder . '/' . $folderName);
					}
				}
			}
		}

		// Remove theme manifest
		JFile::delete($themeFolder . '/' . $name . '.xml');

		// Delete the theme record from database
		$row->delete();
	}

	/**
	 * Set a theme become default theme
	 *
	 * @param int $id
	 */
	public function setDefaultTheme($id)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Make all other themes become none-default
		$query->update('#__eb_themes')
			->set('published = 0')
			->where('id != ' . $id);
		$db->setQuery($query)
			->execute();

		// Make the selected theme become default
		$query->clear()
			->update('#__eb_themes')
			->set('published = 1')
			->where('id = ' . $id);
		$db->setQuery($query)
			->execute();
	}
}
