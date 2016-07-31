<?php
if (!defined('IN_DCRM')) exit();
class db_mysql {
	var $curlink;
	var $last_query;
	private $use_mysqli = false;
	public function __construct() {
		if ( function_exists( 'mysqli_connect' ) ) {
			//if ( version_compare( phpversion(), '5.5', '>=' ) || ! function_exists( 'mysql_connect' ) )
			$this->use_mysqli = true;
		}
	}
	function connect() {
		$this->curlink = $this->_dbconnect(DCRM_CON_SERVER, DCRM_CON_USERNAME, DCRM_CON_PASSWORD, 'utf8', DCRM_CON_DATABASE, (defined("DCRM_CON_SERVER_PORT") ? DCRM_CON_SERVER_PORT : '3306'), (defined("DCRM_CON_PCONNECT") ? DCRM_CON_PCONNECT : false));
	}
	function _dbconnect($dbhost, $dbuser, $dbpw, $dbcharset, $dbname, $dbport = '3306', $pconnect = false) {
		$link = null;
		if ( $this->use_mysqli ) {
			$link = mysqli_connect($dbhost, $dbuser, $dbpw, $dbname, $dbport);
		} else {
			$func = $pconnect ? 'mysql_connect' : 'mysql_pconnect';
			$link = @$func($dbhost.':'.$dbport, $dbuser, $dbpw, 1);
		}

		if (!$link) {
			$this->halt('Couldn\'t connect to MySQL Server');
		} else {
			$this->curlink = $link;
			$this->set_charset( $link, $dbcharset );
			$this->select_db( $dbname );
		}
		return $link;
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
	function select_db($dbname) {
		if($this->use_mysqli) {
			$result = mysqli_select_db($this->curlink, $dbname);
		} else {
			$result = mysql_select_db($dbname, $this->curlink);
		}
		return $result;
	}
	function fetch_array($query, $result_type = null) {
		if($this->use_mysqli) {
			if($result_type === null) $result_type = MYSQLI_ASSOC;
			$result = mysqli_fetch_array($query, $result_type);
		} else {
			if($result_type === null) $result_type = MYSQL_ASSOC;
			$result = mysql_select_db($dbname, $this->curlink);
		}
		return $result;
	}
	function fetch_first($sql) {
		return $this->fetch_array($this->query($sql));
	}
	function result_first($sql) {
		return $this->result($this->query($sql), 0);
	}
	function query($sql, $type = '') {
		if (!$this->curlink) $this->connect();
		if($this->use_mysqli) {
			$query = mysqli_query($this->curlink, $sql);
		} else {
			$func = $type == 'UNBUFFERED' && @function_exists('mysql_unbuffered_query') ? 'mysql_unbuffered_query' : 'mysql_query';
			$query = $func($sql, $this->curlink);
		}
		if (!$query) {
			if ($type != 'SILENT') {
				$this->halt('MySQL Query ERROR', $sql);
			}
		}
		return $this->last_query = $query;
	}
	function affected_rows() {
		$func = $this->use_mysqli ? 'mysqli_affected_rows' : 'mysql_affected_rows';
		return $func($this->curlink);
	}
	function error() {
		$func = $this->use_mysqli ? 'mysqli_error' : 'mysql_error';
		return (($this->curlink) ? $func($this->curlink) : $func());
	}
	function errno() {
		$func = $this->use_mysqli ? 'mysqli_errno' : 'mysql_errno';
		return intval(($this->curlink) ? $func($this->curlink) : $func());
	}
	function result($query, $row = 0) {
		if($this->use_mysqli){
			$result = false;
			$numrows = mysqli_num_rows($query); 
			if ($numrows && $row <= ($numrows - 1) && $row >=0){
				mysqli_data_seek($query, $row);
				$resrow = mysqli_fetch_row($query);
				if (isset($resrow[0])){
					$result = $resrow[0];
				}
			}
		} else {
			$result = mysql_result($query, $row);
		}
		return $result;
	}
	function num_rows($query) {
		$func = $this->use_mysqli ? 'mysqli_num_rows' : 'mysql_num_rows';
		$query = $func($query);
		return $query;
	}
	function num_fields($query) {
		$func = $this->use_mysqli ? 'mysqli_num_fields' : 'mysql_num_fields';
		return $func($query);
	}
	function free_result($query) {
		$func = $this->use_mysqli ? 'mysqli_free_result' : 'mysql_free_result';
		return $func($query);
	}
	function insert_id() {
		$func = $this->use_mysqli ? 'mysqli_insert_id' : 'mysql_insert_id';
		return ($id = $func($this->curlink)) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
	}
	function fetch_row($query) {
		$func = $this->use_mysqli ? 'mysqli_fetch_row' : 'mysql_fetch_row';
		$query = $func($query);
		return $query;
	}
	function fetch_fields($query) {
		$func = $this->use_mysqli ? 'mysqli_fetch_field' : 'mysql_fetch_field';
		return $func($query);
	}
	function real_escape_string($query) {
		if (!$this->curlink) $this->connect();
		if($this->use_mysqli) {
			$result = mysqli_real_escape_string($this->curlink, $query);
		} else {
			$result = mysql_real_escape_string($query);
		}
		return $result;
	}
	function version() {
		if (empty($this->version)) {
			$func = $this->use_mysqli ? 'mysqli_get_server_info' : 'mysql_get_server_info';
			$this->version = $func($this->curlink);
		}
		return $this->version;
	}
	function close() {
		$func = $this->use_mysqli ? 'mysqli_close' : 'mysql_close';
		return $func($this->curlink);
	}
	function stat() {
		if (!$this->curlink) $this->connect();
		$func = $this->use_mysqli ? 'mysqli_stat' : 'mysql_stat';
		return $func($this->curlink);
	}
	function halt($message = '', $sql = '') {
		Crash::db_error($message, $sql);
	}
	function __destruct() {
		$this->close();
	}
}
class DB {
	static function delete($table, $condition, $limit = 0, $unbuffered = true) {
		if (empty($condition)) {
			$where = '1';
		} elseif (is_array($condition)) {
			$where = DB::implode_field_value($condition, ' AND ');
		} else {
			$where = $condition;
		}
		$sql = "DELETE FROM {$table} WHERE $where ".($limit ? "LIMIT $limit" : '');
		return DB::query($sql, ($unbuffered ? 'UNBUFFERED' : ''));
	}
	static function insert($table, $data, $return_insert_id = true, $replace = false, $silent = false) {
		$sql = DB::implode_field_value($data);
		$cmd = $replace ? 'REPLACE INTO' : 'INSERT INTO';
		$silent = $silent ? 'SILENT' : '';
		$return = DB::query("$cmd $table SET $sql", $silent);
		return $return_insert_id ? DB::insert_id() : $return;
	}
	static function update($table, $data, $condition, $unbuffered = false, $low_priority = false) {
		$sql = DB::implode_field_value($data);
		$cmd = "UPDATE ".($low_priority ? 'LOW_PRIORITY' : '');
		$where = '';
		if (empty($condition)) {
			$where = '1';
		} elseif (is_array($condition)) {
			$where = DB::implode_field_value($condition, ' AND ');
		} else {
			$where = $condition;
		}
		$res = DB::query("$cmd $table SET $sql WHERE $where", $unbuffered ? 'UNBUFFERED' : '');
		return $res;
	}
	static function implode_field_value($array, $glue = ',') {
		$sql = $comma = '';
		foreach ($array as $k => $v) {
			$k = DB::real_escape_string($k);
			$v = DB::real_escape_string($v);
			if ('NULL' == $v)
				$v = '';
			$sql .= $comma."`$k`='$v'";
			$comma = $glue;
		}
		return $sql;
	}
	static function insert_id() {
		return DB::_execute('insert_id');
	}
	static function fetch($resourceid, $type = null) {
		return DB::_execute('fetch_array', $resourceid, $type);
	}
	static function fetch_first($sql) {
		return DB::_execute('fetch_first', $sql);
	}
	static function fetch_all($sql) {
		$query = DB::_execute('query', $sql);
		$return = array();
		while ($result = DB::fetch($query)) {
			$return[] = $result;
		}
		return $return;
	}
	static function result($resourceid, $row = 0) {
		return DB::_execute('result', $resourceid, $row);
	}
	static function result_first($sql) {
		return DB::_execute('result_first', $sql);
	}
	static function query($sql, $type = '') {
		return DB::_execute('query', $sql, $type);
	}
	static function num_rows($resourceid) {
		return DB::_execute('num_rows', $resourceid);
	}
	static function affected_rows() {
		return DB::_execute('affected_rows');
	}
	static function free_result($query) {
		return DB::_execute('free_result', $query);
	}
	static function real_escape_string($query) {
		return DB::_execute('real_escape_string', $query);
	}
	static function fetch_row($query) {
		return DB::_execute('fetch_row', $query);
	}
	static function error() {
		return DB::_execute('error');
	}
	static function errno() {
		return DB::_execute('errno');
	}
	static function version() {
		return DB::_execute('version');
	}
	static function stat() {
		return DB::_execute('stat');
	}
	static function _execute($cmd , $arg1 = '', $arg2 = '') {
		static $db;
		if (empty($db)) $db = &DB::object();
		$res = $db->$cmd($arg1, $arg2);
		return $res;
	}
	static function &object() {
		static $db;
		if (empty($db)) $db = new db_mysql();
		return $db;
	}
	static public function escape_by_ref( &$string ) {
		if ( ! is_float( $string ) )
			$string = DB::real_escape_string( $string );
	}
	/**
	 * Prepares a SQL query for safe execution. Uses sprintf()-like syntax.
	 *
	 * The following directives can be used in the query format string:
	 *   %d (integer)
	 *   %f (float)
	 *   %s (string)
	 *   %% (literal percentage sign - no argument needed)
	 *
	 * All of %d, %f, and %s are to be left unquoted in the query string and they need an argument passed for them.
	 * Literals (%) as parts of the query must be properly written as %%.
	 *
	 * This function only supports a small subset of the sprintf syntax; it only supports %d (integer), %f (float), and %s (string).
	 * Does not support sign, padding, alignment, width or precision specifiers.
	 * Does not support argument numbering/swapping.
	 *
	 * May be called like {@link http://php.net/sprintf sprintf()} or like {@link http://php.net/vsprintf vsprintf()}.
	 *
	 * Both %d and %s should be left unquoted in the query string.
	 *
	 *     DB::prepare( "SELECT * FROM `table` WHERE `column` = %s AND `field` = %d", 'foo', 1337 )
	 *     DB::prepare( "SELECT DATE_FORMAT(`field`, '%%c') FROM `table` WHERE `column` = %s", 'foo' );
	 *
	 * @link http://php.net/sprintf Description of syntax.
	 *
	 * @param string $query Query statement with sprintf()-like placeholders
	 * @param array|mixed $args The array of variables to substitute into the query's placeholders if being called like
	 * 	{@link http://php.net/vsprintf vsprintf()}, or the first variable to substitute into the query's placeholders if
	 * 	being called like {@link http://php.net/sprintf sprintf()}.
	 * @param mixed $args,... further variables to substitute into the query's placeholders if being called like
	 * 	{@link http://php.net/sprintf sprintf()}.
	 * @return null|false|string Sanitized query string, null if there is no query, false if there is an error and string
	 * 	if there was something to prepare
	 */
	static public function prepare( $query, $args ) {
		if ( is_null( $query ) )
			return;

		// This is not meant to be foolproof -- but it will catch obviously incorrect usage.
		if ( strpos( $query, '%' ) === false ) {
			throw new Exception(__( 'The query argument of DB::prepare() must have a placeholder.' ));
		}

		$args = func_get_args();
		array_shift( $args );
		// If args were passed as an array (as in vsprintf), move them up
		if ( isset( $args[0] ) && is_array($args[0]) )
			$args = $args[0];
		$query = str_replace( "'%s'", '%s', $query ); // in case someone mistakenly already singlequoted it
		$query = str_replace( '"%s"', '%s', $query ); // doublequote unquoting
		$query = preg_replace( '|(?<!%)%f|' , '%F', $query ); // Force floats to be locale unaware
		$query = preg_replace( '|(?<!%)%s|', "'%s'", $query ); // quote the strings, avoiding escaped strings like %%s
		array_walk( $args, "DB::escape_by_ref" );
		return @vsprintf( $query, $args );
	}
}
?>