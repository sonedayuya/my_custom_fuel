<?php

namespace Fuel\Migrations;

class Create_coupon_sites
{
	public function up()
	{
		\DBUtil::create_table('coupon_sites', array(
			'id' => array('constraint' => 11, 'type' => 'int', 'auto_increment' => true, 'unsigned' => true),
			'coupon_site_id' => array('constraint' => 11, 'type' => 'INT'),
			'coupon_site_name_jp' => array('constraint' => 100, 'type' => 'VARCHAR'),
			'coupon_site_name_en' => array('constraint' => 100, 'type' => 'VARCHAR'),
			'created_at' => array('type' => 'timestamp', 'null' => true),
			'updated_at' => array('type' => 'timestamp', 'null' => true),

		), array('id'));
	}

	public function down()
	{
		\DBUtil::drop_table('coupon_sites');
	}
}