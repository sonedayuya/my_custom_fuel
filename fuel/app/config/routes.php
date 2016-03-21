<?php
return array(
	'_root_'  => 'user/index/index',  // The default route
	'_404_'   => 'user/base/404',    // The main 404 route

	'hello(/:name)?' => array('user/index/hello', 'name' => 'hello'),
);
