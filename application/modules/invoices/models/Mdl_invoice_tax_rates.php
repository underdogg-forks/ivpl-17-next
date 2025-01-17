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
 * Class Mdl_Invoice_Tax_Rates.
 */
final class Mdl_Invoice_Tax_Rates extends Response_Model
{
    public $db;

    public $input;

    public $mdl_invoice_amounts;

    public $table = 'ip_invoice_tax_rates';

    public $primary_key = 'ip_invoice_tax_rates.invoice_tax_rate_id';

    public function default_select(): void
    {
        $this->db->select('ip_tax_rates.tax_rate_name AS invoice_tax_rate_name');
        $this->db->select('ip_tax_rates.tax_rate_percent AS invoice_tax_rate_percent');
        $this->db->select('ip_invoice_tax_rates.*');
    }

    public function default_join(): void
    {
        $this->db->join('ip_tax_rates', 'ip_tax_rates.tax_rate_id = ip_invoice_tax_rates.tax_rate_id');
    }

    /**
     * @param null $id
     * @param null $db_array
     *
     * @return void
     */
    public function save($id = null, $db_array = null): void
    {
        parent::save($id, $db_array);

        $this->load->model('invoices/mdl_invoice_amounts');

        $invoice_id = $db_array['invoice_id'] ?? $this->input->post('invoice_id');

        if ($invoice_id) {
            $this->mdl_invoice_amounts->calculate_invoice_taxes($invoice_id);
            $this->mdl_invoice_amounts->calculate($invoice_id);
        }
    }

    /**
     * @return array
     */
    public function validation_rules()
    {
        return ['invoice_id' => ['field' => 'invoice_id', 'label' => trans('invoice'), 'rules' => 'required'], 'tax_rate_id' => ['field' => 'tax_rate_id', 'label' => trans('tax_rate'), 'rules' => 'required'], 'include_item_tax' => ['field' => 'include_item_tax', 'label' => trans('tax_rate_placement'), 'rules' => 'required']];
    }
}
