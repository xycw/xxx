<?php
class queryFactory {
var $link, $count_queries, $total_query_time;

	function __construct()
	{
	$this->count_queries = 0;
	$this->total_query_time = 0;
	}

	function connect($ef_host, $ef_user, $ef_password, $ef_database, $ef_pconnect = 'false', $ep_real = false) {
		$this->database = $ef_database;
		$this->user = $ef_user;
		$this->host = $ef_host;
		$this->password = $ef_password;
		$this->pConnect = $ef_pconnect;
		$this->real = $ep_real;
		if (!function_exists('mysql_connect')) die ('Call to undefined function: mysql_connect().  Please install the MySQL Connector for PHP');
		$connectionRetry = 10;
		while (!isset($this->link) || ($this->link == FALSE && $connectionRetry !=0) ) {
			$this->link = @mysql_connect($ef_host, $ef_user, $ef_password, true);
			$connectionRetry--;
		}
		if ($this->link) {
			if (@mysql_select_db($ef_database, $this->link)) {
				if (defined('DB_CHARSET') && version_compare(@mysql_get_server_info(), '4.1.0', '>=')) {
					@mysql_query("SET NAMES '" . DB_CHARSET . "'", $this->link);
					if (function_exists('mysql_set_charset')) {
						@mysql_set_charset(DB_CHARSET, $this->link);
					} else {
						@mysql_query("SET CHARACTER SET '" . DB_CHARSET . "'", $this->link);
					}
				}
				$this->db_connected = true;
				return true;
			} else {
				$this->set_error(mysql_errno(),mysql_error(), $ep_real);
				return false;
			}
		} else {
			$this->set_error(mysql_errno(),mysql_error(), $ep_real);
			return false;
		}
	}

	function selectdb($ef_database)
	{
		@mysql_select_db($ef_database, $this->link);
	}

	function prepare_input($ep_string)
	{
		if (function_exists('mysql_real_escape_string')) {
			return mysql_real_escape_string($ep_string, $this->link);
		} elseif (function_exists('mysql_escape_string')) {
			return mysql_escape_string($ep_string, $this->link);
		} else {
			return addslashes($ep_string);
		}
	}

	function close()
	{
		@mysql_close($this->link);
	}

	function set_error($ep_err_num, $ep_err_text, $ep_fatal = true)
	{
		$this->error_number = $ep_err_num;
		$this->error_text = $ep_err_text;
		if ($ep_fatal && $ep_err_num != 1141) { // error 1141 is okay ... should not die on 1141, but just continue on instead
			$this->show_error();
			die();
		}
	}

	function show_error()
	{
		if ($this->error_number == 0 && $this->error_text == 'Error - Could not connect to Database' && !headers_sent() && file_exists('nddbc.html') ) include('nddbc.html');
		echo '<div class="systemError">';
		echo $this->error_number . ' ' . $this->error_text;
		echo '<br />in:<br />[' . (strstr($this->ef_sql, 'db_cache') ? 'db_cache table' : $this->ef_sql) . ']<br />';
		echo '</div>';
	}

	function Execute($ef_sql, $ef_limit = false, $ef_cache = false, $ef_cachetime=0)
	{
		global $cache;
		if ($ef_limit) {
			$ef_sql = $ef_sql . ' LIMIT ' . $ef_limit;
		}
		$this->ef_sql = $ef_sql;
		if ($ef_cache && $cache->sql_cache_exists($ef_sql) && !$cache->sql_cache_is_expired($ef_sql, $ef_cachetime)) {
			$obj = new queryFactoryResult;
			$obj->cursor = 0;
			$obj->is_cached = true;
			$obj->sql_query = $ef_sql;
			$ep_result_array = $cache->sql_cache_read($ef_sql);
			$obj->result = $ep_result_array;
			if (sizeof($ep_result_array) > 0 ) {
				$obj->EOF = false;
				foreach ($ep_result_array[0] as $key => $value) {
					$obj->fields[$key] = $value;
				}
			} else {
				$obj->EOF = true;
			}
			return($obj);
		} elseif ($ef_cache) {
			$cache->sql_cache_expire_now($ef_sql);
			$time_start = explode(' ', microtime());
			$obj = new queryFactoryResult;
			$obj->sql_query = $ef_sql;
			if (!$this->db_connected) {
				if (!$this->connect($this->host, $this->user, $this->password, $this->database, $this->pConnect, $this->real)) {
					$this->set_error('0', 'Error - Could not connect to Database');
				}
			}
			$ep_db_resource = @mysql_query($ef_sql, $this->link);
			if (!$ep_db_resource) $this->set_error(@mysql_errno(),@mysql_error());
			if (!is_resource($ep_db_resource)) {
				$obj = null;
				return true;
			}
			$obj->resource = $ep_db_resource;
			$obj->cursor = 0;
			$obj->is_cached = true;
			$obj->result = array();
			if ($obj->RecordCount() > 0) {
				$obj->EOF = false;
				$ep_ii = 0;
				while (!$obj->EOF) {
					$ep_result_array = @mysql_fetch_array($ep_db_resource);
					if ($ep_result_array) {
						foreach ($ep_result_array as $key => $value) {
							if (!preg_match('/^[0-9]/', $key)) {
								$obj->result[$ep_ii][$key] = $value;
							}
						}
					} else {
						$obj->Limit = $ep_ii;
						$obj->EOF = true;
					}
					$ep_ii++;
				}
				foreach ($obj->result[$obj->cursor] as $key => $value) {
					if (!preg_match('/^[0-9]/', $key)) {
						$obj->fields[$key] = $value;
					}
				}
				$obj->EOF = false;
			} else {
				$obj->EOF = true;
			}
			$cache->sql_cache_store($ef_sql, $obj->result);
			$time_end = explode (' ', microtime());
			$query_time = $time_end[1]+$time_end[0]-$time_start[1]-$time_start[0];
			$this->total_query_time += $query_time;
			$this->count_queries++;
			return($obj);
		} else {
			$time_start = explode(' ', microtime());
			$obj = new queryFactoryResult;
			if (!$this->db_connected) {
				if (!$this->connect($this->host, $this->user, $this->password, $this->database, $this->pConnect, $this->real)) {
					$this->set_error('0', 'Error - Could not connect to Database');
				}
			}
			$ep_db_resource = @mysql_query($ef_sql, $this->link);
			if (!$ep_db_resource) {
				if (@mysql_errno($this->link) == 2006) {
					$this->link = FALSE;
					$this->connect($this->host, $this->user, $this->password, $this->database, $this->pConnect, $this->real);
					$ep_db_resource = @mysql_query($ef_sql, $this->link);
				}
				if (!$ep_db_resource) {
					$this->set_error(@mysql_errno($this->link),@mysql_error($this->link));
				}
			}
			if (!is_resource($ep_db_resource)){
				$obj = null;
				return true;
			}
			$obj->resource = $ep_db_resource;
			$obj->cursor = 0;
			if ($obj->RecordCount() > 0) {
				$obj->EOF = false;
				$ep_result_array = @mysql_fetch_array($ep_db_resource);
				if ($ep_result_array) {
					foreach ($ep_result_array as $key => $value) {
						if (!preg_match('/^[0-9]/', $key)) {
							$obj->fields[$key] = $value;
						}
					}
					$obj->EOF = false;
				} else {
					$obj->EOF = true;
				}
			} else {
				$obj->EOF = true;
			}
			
			$time_end = explode (' ', microtime());
			$query_time = $time_end[1]+$time_end[0]-$time_start[1]-$time_start[0];
			$this->total_query_time += $query_time;
			$this->count_queries++;
			return($obj);
		}
	}

	function ExecuteRandomMulti($ef_sql, $ef_limit = 0, $ef_cache = false, $ef_cachetime=0)
	{
		$this->ef_sql = $ef_sql;
		$time_start = explode(' ', microtime());
		$obj = new queryFactoryResult;
		$obj->result = array();
		if (!$this->db_connected) {
			if (!$this->connect($this->host, $this->user, $this->password, $this->database, $this->pConnect, $this->real)) {
				$this->set_error('0', 'Error - Could not connect to Database');
			}
		}
		$ep_db_resource = @mysql_query($ef_sql, $this->link);
		if (!$ep_db_resource) $this->set_error(mysql_errno(),mysql_error());
		if (!is_resource($ep_db_resource)) {
			$obj = null;
			return true;
		}
		$obj->resource = $ep_db_resource;
		$obj->cursor = 0;
		$obj->Limit = $ef_limit;
		if ($obj->RecordCount() > 0 && $ef_limit > 0) {
			$obj->EOF = false;
			$ep_start_row = 0;
			if ($ef_limit) {
				$ep_start_row = es_rand(0, $obj->RecordCount() - $ef_limit);
			}
			$obj->Move($ep_start_row);
			$ep_ii = 0;
			while (!$obj->EOF) {
				$ep_result_array = @mysql_fetch_array($ep_db_resource);
				if ($ep_ii == $ef_limit) $obj->EOF = true;
				if ($ep_result_array) {
					foreach ($ep_result_array as $key => $value) {
						$obj->result[$ep_ii][$key] = $value;
					}
				} else {
					$obj->Limit = $ep_ii;
					$obj->EOF = true;
				}
				$ep_ii++;
			}
			if (!empty($obj->result)) {
				$obj->result_random = array_rand($obj->result, sizeof($obj->result));
				if (is_array($obj->result_random)) {
					$ep_ptr = $obj->result_random[$obj->cursor];
				} else {
					$ep_ptr = $obj->result_random;
				}
				foreach ($obj->result[$ep_ptr] as $key => $value) {
					if (!preg_match('/^[0-9]/', $key)) {
						$obj->fields[$key] = $value;
					}
				}
			}
			$obj->EOF = false;
		} else {
			$obj->EOF = true;
		}
	
		$time_end = explode (' ', microtime());
		$query_time = $time_end[1]+$time_end[0]-$time_start[1]-$time_start[0];
		$this->total_query_time += $query_time;
		$this->count_queries++;
		return($obj);
	}

	function insert_ID()
	{
		return @mysql_insert_id($this->link);
	}

	function metaColumns($ep_table)
	{
		$result = @mysql_query("select * from " . $ep_table . " limit 1", $this->link);
		$num_fields = @mysql_num_fields($result);
		for ($i = 0; $i < $num_fields; $i++) {
			$obj[@mysql_field_name($result, $i)] = new queryFactoryMeta($i, $result);
		}
		return $obj;
	}

	function get_server_info()
	{
		if ($this->link) {
			return mysql_get_server_info($this->link);
		} else {
			return UNKNOWN;
		}
	}

	function queryCount()
	{
		return $this->count_queries;
	}

	function queryTime()
	{
		return $this->total_query_time;
	}

	function perform($tableName, $tableData, $performType='INSERT', $performFilter='')
	{
		switch (strtoupper($performType)) {
			case 'INSERT':
				$insertString = "INSERT INTO " . $tableName . " (";
				foreach ($tableData as $key => $value) {
					$insertString .= $value['fieldName'] . ", ";
				}
				$insertString = substr($insertString, 0, strlen($insertString)-2) . ') VALUES (';
				reset($tableData);
				foreach ($tableData as $key => $value) {
					$bindVarValue = $this->getBindVarValue($value['value'], $value['type']);
					$insertString .= $bindVarValue . ", ";
				}
				$insertString = substr($insertString, 0, strlen($insertString)-2) . ')';
				if (!$this->db_connected) {
					if (!$this->connect($this->host, $this->user, $this->password, $this->database, $this->pConnect, $this->real)) {
						$this->set_error('0', 'Error - Could not connect to Database');
					}
				}
				return @mysql_query($insertString, $this->link);
			break;
			
			case 'UPDATE':
				$updateString = 'UPDATE ' . $tableName . ' SET ';
				foreach ($tableData as $key => $value) {
					$bindVarValue = $this->getBindVarValue($value['value'], $value['type']);
					$updateString .= $value['fieldName'] . '=' . $bindVarValue . ', ';
				}
				$updateString = substr($updateString, 0, strlen($updateString)-2);
				if ($performFilter != '') {
					$updateString .= ' WHERE ' . $performFilter;
				}
				if (!$this->db_connected) {
					if (!$this->connect($this->host, $this->user, $this->password, $this->database, $this->pConnect, $this->real)) {
						$this->set_error('0', 'Error - Could not connect to Database');
					}
				}
				return @mysql_query($updateString, $this->link);
			break;
		}
	}
	
	function getBindVarValue($value, $type)
	{
		$typeArray = explode(':',$type);
		$type = $typeArray[0];
		switch ($type) {
		case 'csv':
			return $value;
		break;
		
		case 'passthru':
			return $value;
		break;
		
		case 'float':
			return (!not_null($value) || $value=='' || $value == 0) ? 0 : $value;
		break;
		
		case 'integer':
			return (int) $value;
		break;
		
		case 'string':
			if (isset($typeArray[1])) {
				$regexp = $typeArray[1];
			}
			return '\'' . $this->prepare_input($value) . '\'';
		break;
		
		case 'noquotestring':
			return $this->prepare_input($value);
		break;
		
		case 'decimal':
			return '\'' . $this->prepare_input($value) . '\'';
		break;
		
		case 'date':
			return '\'' . $this->prepare_input($value) . '\'';
		break;
		
		case 'enum':
			if (isset($typeArray[1])) {
				$enumArray = explode('|', $typeArray[1]);
			}
			return '\'' . $this->prepare_input($value) . '\'';
		break;
		case 'regexp':
			$searchArray = array('[', ']', '(', ')', '{', '}', '|', '*', '?', '.', '$', '^');
			foreach ($searchArray as $searchTerm) {
				$value = str_replace($searchTerm, '\\' . $searchTerm, $value);
			}
			return $this->prepare_input($value);
		break;
		default:
			die('var-type undefined: ' . $type . '('.$value.')');
		}
	}

	function bindVars($sql, $bindVarString, $bindVarValue, $bindVarType, $debug = false)
	{
		$bindVarTypeArray = explode(':', $bindVarType);
		$sqlNew = $this->getBindVarValue($bindVarValue, $bindVarType);
		$sqlNew = str_replace($bindVarString, $sqlNew, $sql);
		return $sqlNew;
	}

	function prepareInput($string)
	{
		return $this->prepare_input($string);
	}
}

class queryFactoryResult {

	function queryFactoryResult()
	{
		$this->is_cached = false;
	}

	function MoveNext()
	{
		$this->cursor++;
		if ($this->is_cached) {
			if ($this->cursor >= sizeof($this->result)) {
				$this->EOF = true;
			} else {
				foreach ($this->result[$this->cursor] as $key => $value) {
					$this->fields[$key] = $value;
				}
			}
		} else {
			$ep_result_array = @mysql_fetch_array($this->resource);
			if (!$ep_result_array) {
				$this->EOF = true;
			} else {
				foreach ($ep_result_array as $key => $value) {
					if (!preg_match('/^[0-9]/', $key)) {
						$this->fields[$key] = $value;
					}
				}
			}
		}
	}

	function MoveNextRandom()
	{
		$this->cursor++;
		if ($this->cursor < $this->Limit) {
			$ep_result_array = $this->result[$this->result_random[$this->cursor]];
			foreach ($ep_result_array as $key => $value) {
				if (!preg_match('/^[0-9]/', $key)) {
					$this->fields[$key] = $value;
				}
			}
		} else {
			$this->EOF = true;
		}
	}

	function RecordCount()
	{
		if (isset($this->resource)) return @mysql_num_rows($this->resource);
		return sizeof($this->result);
	}

	function Move($ep_row)
	{
		if ($this->is_cached) {
			$this->cursor = $ep_row;
			if (isset($this->result[$this->cursor])) {
				foreach ($this->result[$this->cursor] as $key => $value) {
					$this->fields[$key] = $value;
				}
				$this->EOF = false;
			} else {
				$this->EOF = true;
			}
		} else {
			if (@mysql_data_seek($this->resource, $ep_row)) {
				$ep_result_array = @mysql_fetch_array($this->resource);
				foreach ($ep_result_array as $key => $value) {
					$this->fields[$key] = $value;
				}
				$this->EOF = false;
			} else {
				$this->EOF = true;
			}
		}
	}
}

class queryFactoryMeta {

	function queryFactoryMeta($ep_field, $ep_res)
	{
		$this->type = @mysql_field_type($ep_res, $ep_field);
		$this->max_length = @mysql_field_len($ep_res, $ep_field);
	}
}
