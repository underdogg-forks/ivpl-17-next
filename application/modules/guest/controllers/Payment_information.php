<?php

if ( ! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * InvoicePlane
 *
 * A free and open source web based invoicing system
 *
 * @package		InvoicePlane
 * @author		Kovah (www.kovah.de)
 * @copyright	Copyright (c) 2012 - 2015 InvoicePlane.com
 * @license		https://invoiceplane.com/license.txt
 * @link		https://invoiceplane.com
 *
 */

final class Payment_Information extends Base_Controller
{
    public $load;

    public $mdl_invoices;

    public $session;

    public $config;

    public $mdl_payment_methods;

    public function __construct()
    {
        parent::__construct();

        $this->load->model('invoices/mdl_invoices');
    }

    public function form($invoice_url_key): void
    {
        $this->load->model('payment_methods/mdl_payment_methods');
        $disable_form = false;

        // Check if the invoice exists and is billable
        $invoice = $this->mdl_invoices->where('ip_invoices.invoice_url_key', $invoice_url_key)
            ->get()->row();

        if ( ! $invoice) {
            show_404();
        }

        // Check if the invoice is payable
        if ($invoice->invoice_balance == 0) {
            $this->session->set_userdata('alert_error', lang('invoice_already_paid'));
            $disable_form = true;
        }

        // Get all payment gateways
        $this->load->model('mdl_settings');
        $this->config->load('payment_gateways');
        $gateways = $this->config->item('payment_gateways');

        $available_drivers = [];
        foreach ($gateways as $driver => $fields) {
            $d = mb_strtolower($driver);

            if (get_setting('gateway_' . $d . '_enabled') == 1) {
                $invoice_payment_method = $invoice->payment_method;
                $driver_payment_method = get_setting('gateway_' . $d . '_payment_method');

                if ($invoice_payment_method == 0 || $driver_payment_method == 0 || $driver_payment_method == $invoice_payment_method) {
                    $available_drivers[] = $driver;
                }
            }
        }

        // Get additional invoice information
        $payment_method = $this->mdl_payment_methods->where('payment_method_id', $invoice->payment_method)->get()->row();
        if ($invoice->payment_method == 0) {
            $payment_method = null;
        }

        $is_overdue = ($invoice->invoice_balance > 0 && strtotime($invoice->invoice_date_due) < time());

        // Return the view
        $view_data = ['disable_form' => $disable_form, 'invoice' => $invoice, 'gateways' => $available_drivers, 'payment_method' => $payment_method, 'is_overdue' => $is_overdue];

        //if stripe is active as payment gateway pass also the public api key
        if(in_array('Stripe', $available_drivers)) {
            $view_data['stripe_api_key'] = get_setting('gateway_stripe_apiKeyPublic');
        }

        $this->load->view('guest/payment_information', $view_data);
    }

    //endpoint to load the stripe.js card form
    public function stripe(): void
    {
        $this->load->view('guest/gateway/stripe');
    }
}
