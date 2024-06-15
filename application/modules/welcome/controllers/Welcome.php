<?php

if ( ! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * InvoicePlane
 *
 * @author		InvoicePlane Developers & Contributors
 * @copyright	Copyright (c) 2012 - 2018 InvoicePlane.com
 * @license		https://invoiceplane.com/license.txt
 * @link		https://invoiceplane.com
 */

/**
 * Class Welcome.
 */
final class Welcome extends CI_Controller
{
    public $load;

    public function index(): void
    {
        $this->load->helper('url');

        $this->load->view('welcome');
    }
}
