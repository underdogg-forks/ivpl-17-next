<?php

if ( ! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Class Welcome.
 */
class Welcome extends CI_Controller
{
    public function index(): void
    {
        $this->load->helper('url');

        $this->load->view('welcome');
    }
}
