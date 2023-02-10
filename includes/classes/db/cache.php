<?php
class cache {

	function sql_cache_exists($ef_query)
	{
		global $db;
		$ep_cache_name = $this->cache_generate_cache_name($ef_query);
		switch (DB_CACHE_METHOD) {
			case 'file':
				if (file_exists(DIR_FS_CATALOG_CACHE . $ep_cache_name . '.sql')) {
					return true;
				} else {
					return false;
				}
			break;
			
			case 'database':
				return false;
			break;
			
			case 'memory':
				return false;
			break;
			
			case 'none':
			default:
				return false;
			break;
		}
	}

	function sql_cache_is_expired($ef_query, $ef_cachetime)
	{
		global $db;
		$ep_cache_name = $this->cache_generate_cache_name($ef_query);
		switch (DB_CACHE_METHOD) {
			case 'file':
				if (filemtime(DIR_FS_CATALOG_CACHE . $ep_cache_name . '.sql') > (time() - $ef_cachetime)) {
					return false;
				} else {
					return true;
				}
			break;
			
			case 'database':
				return true;
			break;
			
			case 'memory':
				return true;
			break;
			
			case 'none':
			default:
				return true;
			break;
		}
	}

	function sql_cache_expire_now($ef_query)
	{
		global $db;
		$ep_cache_name = $this->cache_generate_cache_name($ef_query);
		switch (DB_CACHE_METHOD) {
			case 'file':
				@unlink(DIR_FS_CATALOG_CACHE . $ep_cache_name . '.sql');
				return true;
			break;
			
			case 'database':
				return true;
			break;
			
			case 'memory':
				unset($this->cache_array[$ep_cache_name]);
				return true;
			break;
			
			case 'none':
			default:
				return true;
			break;
		}
	}

	function sql_cache_store($ef_query, $ef_result_array)
	{
		global $db;
		$ep_cache_name = $this->cache_generate_cache_name($ef_query);
		switch (DB_CACHE_METHOD) {
			case 'file':
				$OUTPUT = serialize($ef_result_array);
				$fp = fopen(DIR_FS_CATALOG_CACHE . $ep_cache_name . '.sql',"w");
				fputs($fp, $OUTPUT);
				fclose($fp);
				return true;
			break;
			
			case 'database':
				return true;
			break;
			
			case 'memory':
				return true;
			break;
			
			case 'none':
			default:
				return true;
			break;
		}
	}

	function sql_cache_read($ef_query)
	{
		global $db;
		$ep_cache_name = $this->cache_generate_cache_name($ef_query);
		switch (DB_CACHE_METHOD) {
			case 'file':
				$ep_fa = file(DIR_FS_CATALOG_CACHE . $ep_cache_name . '.sql');
				$ep_result_array = unserialize(implode('', $ep_fa));
				return $ep_result_array;
			break;
			
			case 'database':
				return true;
			break;
			
			case 'memory':
				return true;
			break;
			
			case 'none':
			default:
				return true;
			break;
		}
	}

	function sql_cache_flush_cache()
	{
		global $db;
		switch (DB_CACHE_METHOD) {
			case 'file':
				if ($dir = @dir(DIR_FS_CATALOG_CACHE)) {
					while ($file = $dir->read()) {
						if (strstr($file, '.sql') && strstr($file, 'es_')) {
							@unlink(DIR_FS_CATALOG_CACHE . $file);
						}
					}
					$dir->close();
				}
				return true;
			break;
			
			case 'database':
				return true;
			break;
			
			case 'memory':
				return true;
			break;
			
			case 'none':
			default:
				return true;
			break;
		}
	}

	function cache_generate_cache_name($ef_query)
	{
		switch (DB_CACHE_METHOD) {
			case 'file':
				return 'es_' . DB_DATABASE . md5($ef_query);
				break;

			case 'database':
				return 'es_' . DB_DATABASE . md5($ef_query);
				break;

			case 'memory':
				return 'es_' . DB_DATABASE . md5($ef_query);
				break;

			case 'none':
			default:
				return true;
				break;
		}
	}
}
