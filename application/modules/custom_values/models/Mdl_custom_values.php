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
 * Class Mdl_Custom_Values.
 */
final class Mdl_Custom_Values extends MY_Model
{
    public $mdl_custom_fields;

    public $table = 'ip_custom_values';

    public $primary_key = 'ip_custom_values.custom_values_id';

    /**
     * @return string[]
     */
    public static function custom_types()
    {
        return array_merge(self::user_input_types(), self::custom_value_fields());
    }

    /**
     * @return string[]
     */
    public static function user_input_types()
    {
        return ['TEXT', 'DATE', 'BOOLEAN'];
    }

    /**
     * @return string[]
     */
    public static function custom_value_fields()
    {
        return ['SINGLE-CHOICE', 'MULTIPLE-CHOICE'];
    }

    /**
     * @param $fid
     */
    public function save_custom($fid): void
    {
        $this->load->module('custom_fields');
        $field_custom = $this->mdl_custom_fields->get_by_id($fid);

        if ( ! $field_custom) {
            return;
        }

        $db_array = $this->db_array();
        $db_array['custom_values_field'] = $fid;

        parent::save(null, $db_array);
    }

    /**
     * @return array
     */
    public function validation_rules()
    {
        return ['custom_values_value' => ['field' => 'custom_values_value', 'label' => 'Value', 'rules' => 'required']];
    }

    /**
     * @return array
     */
    public function custom_tables()
    {
        return ['ip_client_custom' => 'client', 'ip_invoice_custom' => 'invoice', 'ip_payment_custom' => 'payment', 'ip_quote_custom' => 'quote', 'ip_user_custom' => 'user'];
    }

    /**
     * @param $id
     *
     * @return $this
     */
    public function get_by_fid($id)
    {
        $this->where('custom_values_field', $id);

        return $this->get();
    }

    /**
     * @param $id
     *
     * @return $this
     */
    public function get_by_column($id)
    {
        $this->where('custom_field_id', $id);

        return $this->get();
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function get_by_id($id)
    {
        return $this->where('custom_values_id', $id)->get();
    }

    /**
     * @param $ids
     *
     * @return mixed
     */
    public function get_by_ids($ids)
    {
        $ids = is_array($ids) ? $ids : explode(',', $ids);

        return $this->where_in('custom_values_id', $ids)->get();
    }

    /**
     * @param $fid
     * @param $id
     *
     * @return bool
     */
    public function column_has_value($fid, $id)
    {
        $this->where('custom_field_id', $fid);
        $this->where('custom_values_id', $id);
        $this->get();

        return (bool) $this->num_rows();
    }

    /**
     * @return $this
     */
    public function get_grouped()
    {
        $this->db->select('count(custom_field_label) as count');
        $this->db->group_by('ip_custom_fields.custom_field_id');

        return $this->get();
    }

    public function default_select(): void
    {
        $this->db->select('ip_custom_fields.*,ip_custom_values.*', false);
    }

    public function default_join(): void
    {
        $this->db->join('ip_custom_fields', 'ip_custom_values.custom_values_field = ip_custom_fields.custom_field_id', 'inner');
    }

    public function default_order_by(): void
    {
        //$this->db->group_by('ip_custom_fields.custom_field_label');
    }

    public function default_group_by(): void
    {
        //$this->db->group_by('ip_custom_values.custom_values_field');
    }
}
