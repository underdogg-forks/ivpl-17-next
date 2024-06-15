<?php

if ( ! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Mdl_Payment_Methods extends Response_Model
{
    public $table = 'ip_payment_methods';

    public $primary_key = 'ip_payment_methods.payment_method_id';

    public function default_select(): void
    {
        $this->db->select('SQL_CALC_FOUND_ROWS *', false);
    }

    public function order_by(): void
    {
        $this->db->order_by('ip_payment_methods.payment_method_name');
    }

    /**
     * @return array
     */
    public function validation_rules()
    {
        return ['payment_method_name' => ['field' => 'payment_method_name', 'label' => trans('payment_method'), 'rules' => 'required']];
    }
}
