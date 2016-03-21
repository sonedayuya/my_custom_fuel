<?php

namespace Batch\Controller;

class Index extends Base
{
	public function __construct($request)
	{
		parent::__construct($request);
	}

	public function action_index()
	{
	}

	public function action_hello()
	{
		// how to use model
		// $coupon_site =\Model\Coupon_Site::find('all');

		// if you want to set variable in your view , write like this;
		$this->setVar('name', $this->request->param('name', 'default'));
	}
}
