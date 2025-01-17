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

    public $mdl_invoices;

    public $layout;

    public $mdl_quotes;

    public $mdl_clients;

    public $mdl_payments;

    public $ajax_controller = true;

    public function filter_invoices(): void
    {
        $this->load->model('invoices/mdl_invoices');

        $query = $this->input->post('filter_query');
        $keywords = explode(' ', $query);

        foreach ($keywords as $keyword) {
            if ($keyword !== '' && $keyword !== '0') {
                $keyword = mb_strtolower($keyword);
                $this->mdl_invoices->like("CONCAT_WS('^',LOWER(invoice_number),invoice_date_created,invoice_date_due,LOWER(client_name),invoice_total,invoice_balance)", $keyword);
            }
        }

        $data = ['invoices' => $this->mdl_invoices->get()->result(), 'invoice_statuses' => $this->mdl_invoices->statuses()];

        $this->layout->load_view('invoices/partial_invoice_table', $data);
    }

    public function filter_quotes(): void
    {
        $this->load->model('quotes/mdl_quotes');

        $query = $this->input->post('filter_query');
        $keywords = explode(' ', $query);

        foreach ($keywords as $keyword) {
            if ($keyword !== '' && $keyword !== '0') {
                $keyword = mb_strtolower($keyword);
                $this->mdl_quotes->like("CONCAT_WS('^',LOWER(quote_number),quote_date_created,quote_date_expires,LOWER(client_name),quote_total)", $keyword);
            }
        }

        $data = ['quotes' => $this->mdl_quotes->get()->result(), 'quote_statuses' => $this->mdl_quotes->statuses()];

        $this->layout->load_view('quotes/partial_quote_table', $data);
    }

    public function filter_clients(): void
    {
        $this->load->model('clients/mdl_clients');

        $query = $this->input->post('filter_query');
        $keywords = explode(' ', $query);

        foreach ($keywords as $keyword) {
            if ($keyword !== '' && $keyword !== '0') {
                $keyword = trim(mb_strtolower($keyword));
                $this->mdl_clients->like("CONCAT_WS('^',LOWER(client_name),LOWER(client_surname),LOWER(client_email),client_phone,client_active)", $keyword);
            }
        }

        $data = ['records' => $this->mdl_clients->with_total_balance()->get()->result()];

        $this->layout->load_view('clients/partial_client_table', $data);
    }

    public function filter_payments(): void
    {
        $this->load->model('payments/mdl_payments');

        $query = $this->input->post('filter_query');
        $keywords = explode(' ', $query);

        foreach ($keywords as $keyword) {
            if ($keyword !== '' && $keyword !== '0') {
                $keyword = mb_strtolower($keyword);
                $this->mdl_payments->like("CONCAT_WS('^',payment_date,LOWER(invoice_number),LOWER(client_name),payment_amount,LOWER(payment_method_name),LOWER(payment_note))", $keyword);
            }
        }

        $data = ['payments' => $this->mdl_payments->get()->result()];

        $this->layout->load_view('payments/partial_payment_table', $data);
    }
}
