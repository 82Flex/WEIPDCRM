<?php
/**
 * DCRM Database Class for Installer
 * Copyright (c) 2016 Hintay <hintay@me.com>
 *
 * This file is part of WEIPDCRM.
 * 
 * WEIPDCRM is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * WEIPDCRM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with WEIPDCRM.  If not, see <http://www.gnu.org/licenses/>.
 */
 
if (!defined('DCRM')) exit();

class DB {
	public $curlink;
	public $last_query;
	public $tryagain_link;
	private $use_mysqli = false;

	public function __construct() {
		if ( function_exists( 'mysqli_connect' ) )
			$this->use_mysqli = true;
	}

	function connect() {
		if($this->_dbconnect()) {
			$this->set_charset( $this->curlink, 'utf8' );
			$this->select_db( DCRM_CON_DATABASE );
		}
	}

	function _dbconnect() {
		$link = null;
		if ( $this->use_mysqli ) {
			$link = mysqli_connect(DCRM_CON_SERVER, DCRM_CON_USERNAME, DCRM_CON_PASSWORD);
		} else {
			$link = mysql_connect(DCRM_CON_SERVER.':'.DCRM_CON_SERVER_PORT, DCRM_CON_USERNAME, DCRM_CON_PASSWORD);
		}
		if (!$link) {
			$this->halt(__('<strong>ERROR</strong>: Can&#8217;t connect database server.'));
			return false;
		} else {
			$this->curlink = $link;
			return true;
		}
	}

	function select_db($dbname) {
		if($this->use_mysqli) {
			$result = mysqli_select_db($this->curlink, $dbname);
		} else {
			$result = mysql_select_db($dbname, $this->curlink);
		}
		return $result;
	}

	function set_charset($dbh, $charset = null, $collate = null) {
		if ( $this->use_mysqli ) {
			if ( function_exists( 'mysqli_set_charset' ) ) {
				mysqli_set_charset( $dbh, $charset );
			} else {
				$query = sprintf('SET NAMES %s', $charset );
				if ( ! empty( $collate ) )
					$query .= sprintf( ' COLLATE %s', $collate );
				mysqli_query( $query, $dbh );
			}
		} else {
			if ( function_exists( 'mysql_set_charset' ) ) {
				mysql_set_charset( $charset, $dbh );
			} else {
				$query = sprintf( 'SET NAMES %s', $charset );
				if ( ! empty( $collate ) )
					$query .= sprintf( ' COLLATE %s', $collate );
				mysql_query( $query, $dbh );
			}
		}
	}

	function query($sql) {
		if (!$this->curlink) $this->connect();
		if($this->use_mysqli)
			$query = mysqli_query($this->curlink, $sql);
		else
			$query = mysql_query($sql, $this->curlink);
		return $this->last_query = $query;
	}

	function _query($sql) {
		$result = $this->query($sql);
		if(!$result) $db->halt();
	}

	function num_rows($query) {
		$func = $this->use_mysqli ? 'mysqli_num_rows' : 'mysql_num_rows';
		$query = $func($query);
		return $query;
	}

	function error() {
		$func = $this->use_mysqli ? 'mysqli_error' : 'mysql_error';
		return (($this->curlink) ? $func($this->curlink) : $func());
	}

	function halt($message = '') {
		$inst_alert = $this->error();
		echo ($message ? ($message . '<br/>') : '' ) . $inst_alert . $this->tryagain_link;
		exit();
	}
}