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
 * Class Mdl_Invoice_Groups.
 */
final class Mdl_Invoice_Groups extends Response_Model
{
    public $db;

    public $table = 'ip_invoice_groups';

    public $primary_key = 'ip_invoice_groups.invoice_group_id';

    public function default_select(): void
    {
        $this->db->select('SQL_CALC_FOUND_ROWS *', false);
    }

    public function default_order_by(): void
    {
        $this->db->order_by('ip_invoice_groups.invoice_group_name');
    }

    /**
     * @return array
     */
    public function validation_rules()
    {
        return ['invoice_group_name' => ['field' => 'invoice_group_name', 'label' => trans('name'), 'rules' => 'required'], 'invoice_group_identifier_format' => ['field' => 'invoice_group_identifier_format', 'label' => trans('identifier_format'), 'rules' => 'required'], 'invoice_group_next_id' => ['field' => 'invoice_group_next_id', 'label' => trans('next_id'), 'rules' => 'required'], 'invoice_group_left_pad' => ['field' => 'invoice_group_left_pad', 'label' => trans('left_pad'), 'rules' => 'required']];
    }

    /**
     * @param      $invoice_group_id
     * @param bool $set_next
     *
     * @return mixed
     */
    public function generate_invoice_number($invoice_group_id, $set_next = true)
    {
        $invoice_group = $this->get_by_id($invoice_group_id);

        $invoice_identifier = $this->parse_identifier_format(
            $invoice_group->invoice_group_identifier_format,
            $invoice_group->invoice_group_next_id,
            $invoice_group->invoice_group_left_pad
        );

        if ($set_next) {
            $this->set_next_invoice_number($invoice_group_id);
        }

        return $invoice_identifier;
    }

    /**
     * @param $invoice_group_id
     */
    public function set_next_invoice_number($invoice_group_id): void
    {
        $this->db->where($this->primary_key, $invoice_group_id);
        $this->db->set('invoice_group_next_id', 'invoice_group_next_id+1', false);
        $this->db->update($this->table);
    }

    /**
     * @param $identifier_format
     * @param $next_id
     * @param $left_pad
     *
     * @return mixed
     */
    private function parse_identifier_format($identifier_format, $next_id, $left_pad)
    {
        if (preg_match_all('/{{{([^{|}]*)}}}/', $identifier_format, $template_vars)) {
            foreach ($template_vars[1] as $var) {
                $replace = match ($var) {
                    'year'  => date('Y'),
                    'yy'    => date('y'),
                    'month' => date('m'),
                    'day'   => date('d'),
                    'id'    => str_pad($next_id, $left_pad, '0', STR_PAD_LEFT),
                    default => '',
                };

                $identifier_format = str_replace('{{{' . $var . '}}}', $replace, $identifier_format);
            }
        }

        return $identifier_format;
    }
}
