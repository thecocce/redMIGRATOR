<?php
/**
 * jUpgrade
 *
 * @version		$Id$
 * @package		MatWare
 * @subpackage	com_jupgrade
 * @copyright	Copyright 2006 - 2011 Matias Aguire. All rights reserved.
 * @license		GNU General Public License version 2 or later.
 * @author		Matias Aguirre <maguirre@matware.com.ar>
 * @link		http://www.matware.com.ar
 */

/**
 * Upgrade class for Newsfeeds
 *
 * This class takes the newsfeeds from the existing site and inserts them into the new site.
 *
 * @since	0.4.5
 */
class jUpgradeNewsfeeds extends jUpgrade
{
	/**
	 * @var		string	The name of the source database table.
	 * @since	0.4.5
	 */
	protected $source = '#__newsfeeds';

	/**
	 * @var		string	The key of the table
	 * @since	3.0.0
	 */
	protected $_tbl_key = 'id';

	/**
	 * Get the raw data for this part of the upgrade.
	 *
	 * @return	array	Returns a reference to the source data array.
	 * @since	0.4.5
	 * @throws	Exception
	 */
	public function &getSourceDatabase()
	{
/*
		$rows = parent::getSourceData(
			'`catid`,`id`,`name`,`alias`,`link`,`filename`,`published`,`numarticles`,`cache_time`, '
     .'`checked_out`,`checked_out_time`,`ordering`,`rtl`',
			null,
			'id'
		);
*/
		$rows = parent::getSourceDatabase();

		return $rows;
	}
	
	/**
	 * Sets the data in the destination database.
	 *
	 * @return	void
	 * @since	3.0.
	 * @throws	Exception
	 */
	protected function setDestinationData()
	{
		// Getting the component parameter with global settings
		$params = $this->getParams();	
	
		// Get the source data.
		$rows = $this->loadData('newsfeeds');

		// Getting the categories id's
		$categories = $this->getMapList('categories', 'com_newsfeeds');

		// Do some custom post processing on the list.
		foreach ($rows as &$row)
		{
			$row['language'] = '*';
		
			$cid = $row['catid'];
			$row['catid'] = &$categories[$cid]->new;
		}
	}
}
