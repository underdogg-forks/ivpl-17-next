<?php

if ( ! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Class Ajax.
 */
class Ajax extends Admin_Controller
{
    public $ajax_controller = true;

    public function add(): void
    {
        $this->load->model('payments/mdl_payments');

        if ($this->mdl_payments->run_validation()) {
            $payment_id = $this->mdl_payments->save();

            $response = ['success' => 1, 'payment_id' => $payment_id];
        } else {
            $this->load->helper('json_error');
            $response = ['success' => 0, 'validation_errors' => json_errors()];
        }

        echo json_encode($response, JSON_THROW_ON_ERROR);
    }

    public function modal_add_payment(): void
    {
        $this->load->module('layout');
        $this->load->model('payments/mdl_payments');
        $this->load->model('payment_methods/mdl_payment_methods');
        $this->load->model('custom_fields/mdl_payment_custom');

        $data = ['payment_methods' => $this->mdl_payment_methods->get()->result(), 'invoice_id' => $this->security->xss_clean($this->input->post('invoice_id')), 'invoice_balance' => $this->input->post('invoice_balance'), 'invoice_payment_method' => $this->input->post('invoice_payment_method'), 'payment_cf_exist' => $this->security->xss_clean($this->input->post('payment_cf_exist'))];

        $this->layout->load_view('payments/modal_add_payment', $data);
    }
}
