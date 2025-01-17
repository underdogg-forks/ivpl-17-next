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

    public $input;

    public $mdl_email_templates;

    public $ajax_controller = true;

    public function get_content(): void
    {
        $this->load->model('email_templates/mdl_email_templates');

        $id = $this->input->post('email_template_id');

        echo json_encode($this->mdl_email_templates->get_by_id($id), JSON_THROW_ON_ERROR);
    }
}
