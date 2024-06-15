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
 * Class Form_Validation_Model.
 */
final class Form_Validation_Model extends MY_Model
{
    public $load;

    public $form_validation;

    /**
     * Form_Validation_Model constructor.
     */
    public function __construct()
    {
        $this->load->library('form_validation');
        $this->form_validation->CI = & $this;
    }
}
