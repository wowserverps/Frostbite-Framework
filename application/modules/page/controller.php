<?php

class Page extends Controller 
{
	function Page()
	{
		parent::__construct();
	}
	
	function index() 
	{	
		$this->load->model('Page_Model');
		$contents = $this->page_model->get_page_contents();
		$this->output($contents);
	}
}
// EOF