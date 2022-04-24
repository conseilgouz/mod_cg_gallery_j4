<?php
/**
* CG Gallery - Joomla Module 
* Version			: 2.1.0
* Package			: Joomla 4.0.x
* copyright 		: Copyright (C) 2021 ConseilGouz. All rights reserved.
* license    		: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
namespace ConseilGouz\Module\CGGallery\Field;

defined('JPATH_PLATFORM') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\SqlField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class SqlNoErrField extends SQLField
{
	public $type = 'SQLnoerr';
	protected $keyField;
	protected $valueField;
	protected $translate = false;
	protected $query;

	/**
	 * Method to check if SQL query contains errors
	 * @return  array  The field option objects or empty (if error in query)
	 */
	protected function getOptions()
	{
		$options = array();

		// Initialize some field attributes.
		$key   = $this->keyField;
		$value = $this->valueField;
		$header = $this->header;

		if ($this->query)
		{
			// Get the database object.
			$db = Factory::getDbo();

			// Set the query and get the result list.
			$db->setQuery($this->query);

			try
			{
				$items = $db->loadObjectlist();
			}
			catch (JDatabaseExceptionExecuting $e)
			{
				 return $options; // SQL Error : return empty
			}
		}
		// No error : execute SQL
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
