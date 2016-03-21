<?php
return array(
	'_root_'  => 'batch/index/index',  // The default route

	'batch/hello(/:name)?' => array('batch/index/hello', 'name' => 'hello'),
);
