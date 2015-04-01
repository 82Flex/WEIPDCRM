<?php
if (!defined('IN_DCRM')) exit();
class db_mysql {
	var $curlink;
	var $last_query;
	function connect() {
		$this->curlink = $this->_dbconnect(DCRM_CON_SERVER.':' . (defined("DCRM_CON_SERVER_PORT") ? DCRM_CON_SERVER_PORT : '3306'), DCRM_CON_USERNAME, DCRM_CON_PASSWORD, 'utf8', DCRM_CON_DATABASE, (defined("DCRM_CON_PCONNECT") ? DCRM_CON_PCONNECT : false));
	}
	function _dbconnect($dbhost, $dbuser, $dbpw, $dbcharset, $dbname, $pconnect) {
		$link = null;
		$func = empty($pconnect) ? 'mysql_connect' : 'mysql_pconnect';
		if (!$link = @$func($dbhost, $dbuser, $dbpw, 1)) {
			$this->halt('Couldn\'t connect to MySQL Server');
		} else {
			$this->curlink = $link;
			if ($this->version() > '4.1') {
				$serverset = $dbcharset ? 'character_set_connection='.$dbcharset.', character_set_results='.$dbcharset.', character_set_client=binary' : '';
				$serverset .= $this->version() > '5.0.1' ? ((empty($serverset) ? '' : ',').'sql_mode=\'\'') : '';
				$serverset && mysql_query("SET $serverset", $link);
			}
			$dbname && @mysql_select_db($dbname, $link);
		}
		return $link;
	}
	function select_db($dbname) {
		return mysql_select_db($dbname, $this->curlink);
	}
	function fetch_array($query, $result_type = MYSQL_ASSOC) {
		return mysql_fetch_array($query, $result_type);
	}
	function fetch_first($sql) {
		return $this->fetch_array($this->query($sql));
	}
	function result_first($sql) {
		return $this->result($this->query($sql), 0);
	}
	function query($sql, $type = '') {
		$func = $type == 'UNBUFFERED' && @function_exists('mysql_unbuffered_query') ? 'mysql_unbuffered_query' : 'mysql_query';
		if (!$this->curlink) $this->connect();
		if (!($query = $func($sql, $this->curlink))) {
			if ($type != 'SILENT') {
				$this->halt('MySQL Query ERROR', $sql);
			}
		}
		return $this->last_query = $query;
	}
	function affected_rows() {
		return mysql_affected_rows($this->curlink);
	}
	function error() {
		return (($this->curlink) ? mysql_error($this->curlink) : mysql_error());
	}
	function errno() {
		return intval(($this->curlink) ? mysql_errno($this->curlink) : mysql_errno());
	}
	function result($query, $row = 0) {
		$query = @mysql_result($query, $row);
		return $query;
	}
	function num_rows($query) {
		$query = mysql_num_rows($query);
		return $query;
	}
	function num_fields($query) {
		return mysql_num_fields($query);
	}
	function free_result($query) {
		return mysql_free_result($query);
	}
	function insert_id() {
		return ($id = mysql_insert_id($this->curlink)) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
	}
	function fetch_row($query) {
		$query = mysql_fetch_row($query);
		return $query;
	}
	function fetch_fields($query) {
		return mysql_fetch_field($query);
	}
	function real_escape_string($query) {
		if (!$this->curlink) $this->connect();
		return mysql_real_escape_string($query);
	}
	function version() {
		if (empty($this->version)) {
			$this->version = mysql_get_server_info($this->curlink);
		}
		return $this->version;
	}
	function close() {
		return mysql_close($this->curlink);
	}
	function stat() {
		if (!$this->curlink) $this->connect();
		return mysql_stat($this->curlink);
	}
	function halt($message = '', $sql = '') {
		error::db_error($message, $sql);
	}
	function __destruct() {
		$this->close();
	}
}
class DB {
	function delete($table, $condition, $limit = 0, $unbuffered = true) {
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
	function insert($table, $data, $return_insert_id = true, $replace = false, $silent = false) {
		$sql = DB::implode_field_value($data);
		$cmd = $replace ? 'REPLACE INTO' : 'INSERT INTO';
		$silent = $silent ? 'SILENT' : '';
		$return = DB::query("$cmd $table SET $sql", $silent);
		return $return_insert_id ? DB::insert_id() : $return;
	}
	function update($table, $data, $condition, $unbuffered = false, $low_priority = false) {
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
	function implode_field_value($array, $glue = ',') {
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
	function insert_id() {
		return DB::_execute('insert_id');
	}
	function fetch($resourceid, $type = MYSQL_ASSOC) {
		return DB::_execute('fetch_array', $resourceid, $type);
	}
	function fetch_first($sql) {
		return DB::_execute('fetch_first', $sql);
	}
	function fetch_all($sql) {
		$query = DB::_execute('query', $sql);
		$return = array();
		while ($result = DB::fetch($query)) {
			$return[] = $result;
		}
		return $return;
	}
	function result($resourceid, $row = 0) {
		return DB::_execute('result', $resourceid, $row);
	}
	function result_first($sql) {
		return DB::_execute('result_first', $sql);
	}
	function query($sql, $type = '') {
		return DB::_execute('query', $sql, $type);
	}
	function num_rows($resourceid) {
		return DB::_execute('num_rows', $resourceid);
	}
	function affected_rows() {
		return DB::_execute('affected_rows');
	}
	function free_result($query) {
		return DB::_execute('free_result', $query);
	}
	function real_escape_string($query) {
		return DB::_execute('real_escape_string', $query);
	}
	function fetch_row($query) {
		return DB::_execute('fetch_row', $query);
	}
	function error() {
		return DB::_execute('error');
	}
	function errno() {
		return DB::_execute('errno');
	}
	function version() {
		return DB::_execute('version');
	}
	function stat() {
		return DB::_execute('stat');
	}
	function _execute($cmd , $arg1 = '', $arg2 = '') {
		static $db;
		if (empty($db)) $db = &DB::object();
		$res = $db->$cmd($arg1, $arg2);
		return $res;
	}
	function &object() {
		static $db;
		if (empty($db)) $db = new db_mysql();
		return $db;
	}
	public function escape_by_ref( &$string ) {
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
	public function prepare( $query, $args ) {
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