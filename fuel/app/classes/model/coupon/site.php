<?php
namespace Model;

class Coupon_Site extends \Orm\Model
{
	protected static $_properties = array(
		'id',
		'coupon_site_id',
		'coupon_site_name_jp',
		'coupon_site_name_en',
		'created_at',
		'updated_at',
	);

	protected static $_observers = array(
		'\Orm\Observer_CreatedAt' => array(
			'events' => array('before_insert'),
			'mysql_timestamp' => true,
		),
		'\Orm\Observer_UpdatedAt' => array(
			'events' => array('before_update'),
			'mysql_timestamp' => true,
		),
	);

	protected static $_table_name = 'coupon_sites';

}
