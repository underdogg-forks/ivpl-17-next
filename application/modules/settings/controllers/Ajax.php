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
 * Class Ajax.
 */
final class Ajax extends Admin_Controller
{
    public $load;

    public $ajax_controller = true;

    public function get_cron_key(): void
    {
        $this->load->helper('string');
        echo random_string('alnum', 16);
    }
}
