<?php

if ( ! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Class Mdl_Invoice_Custom.
 */
class Mdl_Invoice_Custom extends Validator
{
    public static $positions = ['custom_fields', 'properties'];

    public $table = 'ip_invoice_custom';

    public $primary_key = 'ip_invoice_custom.invoice_custom_id';

    public function default_select(): void
    {
        $this->db->select('SQL_CALC_FOUND_ROWS ip_invoice_custom.*, ip_custom_fields.*', false);
    }

    public function default_join(): void
    {
        $this->db->join('ip_custom_fields', 'ip_invoice_custom.invoice_custom_fieldid = ip_custom_fields.custom_field_id');
    }

    public function default_order_by(): void
    {
        $this->db->order_by('custom_field_table ASC, custom_field_order ASC, custom_field_label ASC');
    }

    public function save_custom($invoice_id, $db_array)
    {
        $result = $this->validate($db_array);
        if ($result === true) {
            $form_data = $this->_formdata ?? null;

            if (null === $form_data) {
                return true;
            }

            $invoice_custom_id = null;

            foreach ($form_data as $key => $value) {
                $db_array = ['invoice_id' => $invoice_id, 'invoice_custom_fieldid' => $key, 'invoice_custom_fieldvalue' => $value];
                $invoice_custom = $this->where('invoice_id', $invoice_id)->where('invoice_custom_fieldid', $key)->get();

                if ($invoice_custom->num_rows()) {
                    $invoice_custom_id = $invoice_custom->row()->invoice_custom_id;
                }

                parent::save($invoice_custom_id, $db_array);
            }

            return true;
        }

        return $result;
    }

    public function by_id($invoice_id)
    {
        $this->db->where('ip_invoice_custom.invoice_id', $invoice_id);

        return $this;
    }
}
