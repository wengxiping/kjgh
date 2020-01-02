<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class PPPdf extends PayPlans
{
	protected $adapter = null;

	public static function factory($object)
	{
		return new self($object);
	}

	public function __construct($object)
	{
		$this->adapter = $this->getAdapter($object);
	}

	/**
	 * Retrieves the adapter responsible to handle the project
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getAdapter($object)
	{
		$name = $object->getName();

		require_once(__DIR__ . '/adapters/' . $name . '.php');

		$className = 'PPPdf' . strtoupper($name) . 'Adapter';
		$adapter = new $className($object);

		return $adapter;
	}

	/**
	 * Generate content for pdf 
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function generateContent()
	{
		return $this->adapter->generateContent();
	}

	/**
	 * Generate a physical pdf file
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function generateFile()
	{
		return $this->adapter->generateFile();
	}

	/**
	 * Get path to folder
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPath()
	{
		return $this->adapter->getPath();
	}

	/**
	 * Get path of the file
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getFilePath()
	{
		return $this->adapter->getFilePath();
	}

	/**
	 * Save PDF contents into a file
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function saveToPdf($contents)
	{
		return $this->adapter->saveToPdf($contents);
	}

	/**
	 * Delete PDF file
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function delete()
	{
		return $this->adapter->delete();
	}
}