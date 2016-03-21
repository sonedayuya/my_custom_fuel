<?php
return array(
	'_root_'  => 'admin/index/index',  // The default route
	'_404_'  => 'admin/base/404',  // The default route

	'admin/hello(/:name)?' => array('admin/index/hello', 'name' => 'hello'),
	'admin/coupon_sites' => array('admin/coupon_sites/index'),
);
