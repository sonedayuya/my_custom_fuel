<?php

namespace Admin\Controller;

class Base extends \Controller\Base
{
	public function __construct($request)
	{
		parent::__construct($request);
	}

	public function action_404()
	{
	}

	public function set_partial($theme)
	{
		$theme->set_partial('header', 'layout/header');
		$theme->set_partial('footer', 'layout/footer');
	}
}
