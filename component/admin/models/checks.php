<?php
/**
 * @package     RedMIGRATOR.Backend
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * 
 *  redMIGRATOR is based on JUpgradePRO made by Matias Aguirre
 */

// No direct access.
defined('_JEXEC') or die;

JLoader::register('RedMigrator', JPATH_COMPONENT_ADMINISTRATOR . '/includes/redmigrator.class.php');
JLoader::register('RedMigratorDriver', JPATH_COMPONENT_ADMINISTRATOR . '/includes/redmigrator.driver.class.php');
JLoader::register('RedMigratorStep', JPATH_COMPONENT_ADMINISTRATOR . '/includes/redmigrator.step.class.php');

/**
 * RedMigrator Model
 *
 */
class RedMigratorModelChecks extends RModelAdmin
{
	/**
	 * Initial checks in RedMigrator
	 *
	 * @return	none
	 *
	 * @since	1.2.0
	 */
	function checks()
	{
		// Getting the component parameter with global settings
		$params = RedMigratorHelper::getParams();

		// Checking tables
		$tables = $this->_db->getTableList();

		// Check if the tables exists if not populate install.sql
		$tablesComp = array();
		$tablesComp[] = 'categories';
		$tablesComp[] = 'default_categories';
		$tablesComp[] = 'default_menus';
		$tablesComp[] = 'errors';
		$tablesComp[] = 'extensions';
		$tablesComp[] = 'extensions_tables';
		$tablesComp[] = 'files_images';
		$tablesComp[] = 'files_media';
		$tablesComp[] = 'files_templates';
		$tablesComp[] = 'menus';
		$tablesComp[] = 'modules';
		$tablesComp[] = 'steps';

		foreach ($tablesComp as $table)
		{
			if (!in_array($this->_db->getPrefix() . 'redmigrator_' . $table, $tables))
			{
				if (RedMigratorHelper::isCli())
				{
					print("\n\033[1;37m-------------------------------------------------------------------------------------------------\n");
					print("\033[1;37m|  \033[0;34m	Installing RedMigrator tables\n");
				}

				RedMigratorHelper::populateDatabase($this->_db, JPATH_COMPONENT_ADMINISTRATOR . '/sql/install.sql');
				break;
			}
		}

		// Check safe_mode_gid
		if (@ini_get('safe_mode_gid') && @ini_get('safe_mode'))
		{
			throw new Exception('COM_REDMIGRATOR_ERROR_DISABLE_SAFE_GID');
		}

		// Check for bad configurations
		if ($params->method == "rest")
		{
			if (!isset($params->rest_hostname) || !isset($params->rest_username)
					|| !isset($params->rest_password) || !isset($params->rest_key) )
			{
				throw new Exception('COM_REDMIGRATOR_ERROR_REST_CONFIG');
			}

			if ($params->rest_hostname == 'http://www.example.org/' || $params->rest_hostname == ''
					|| $params->rest_username == '' || $params->rest_password == '' || $params->rest_key == '')
			{
				throw new Exception('COM_REDMIGRATOR_ERROR_REST_CONFIG');
			}

			// Checking the RESTful connection
			$driver = RedMigratorDriver::getInstance();
			$code = $driver->requestRest('check');

			switch ($code)
			{
				case 401:
					throw new Exception('COM_REDMIGRATOR_ERROR_REST_501');
				case 402:
					throw new Exception('COM_REDMIGRATOR_ERROR_REST_502');
				case 403:
					throw new Exception('COM_REDMIGRATOR_ERROR_REST_503');
				case 405:
					throw new Exception('COM_REDMIGRATOR_ERROR_REST_505');
				case 406:
					throw new Exception('COM_REDMIGRATOR_ERROR_REST_506');
			}
		}

		// Check for bad configurations
		if ($params->method == "database")
		{
			if ($params->old_hostname == '' || $params->old_username == ''
				|| $params->old_db == '' || $params->old_dbprefix == '')
			{
				throw new Exception('COM_REDMIGRATOR_ERROR_DATABASE_CONFIG');
			}
		}

		// Done checks
		if (!RedMigratorHelper::isCli())
		{
			RedMigratorHelper::returnError(100, 'DONE');
		}
	}
} // End class
