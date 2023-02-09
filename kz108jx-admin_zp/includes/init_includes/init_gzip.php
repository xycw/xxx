<?php
/**
 * init_includes init_gzip.php
 */
if (extension_loaded('zlib')) {
	if ((int)ini_get('zlib.output_compression') < 1) {
		ob_start('ob_gzhandler');
	} else {
		@ini_set('zlib.output_compression_level', 1);
	}
}
