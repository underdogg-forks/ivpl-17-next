<?php

if ( ! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Invoices extends Guest_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('invoices/mdl_invoices');
    }

    public function index(): void
    {
        // Display open invoices by default
        redirect('guest/invoices/status/open');
    }

    /**
     * @param string $status
     * @param int    $page
     */
    public function status($status = 'open', $page = 0): void
    {
        match ($status) {
            'paid'  => $this->mdl_invoices->is_paid()->where_in('ip_invoices.client_id', $this->user_clients),
            default => $this->mdl_invoices->is_open()->where_in('ip_invoices.client_id', $this->user_clients),
        };

        $this->mdl_invoices->paginate(site_url('guest/invoices/status/' . $status), $page);
        $invoices = $this->mdl_invoices->result();

        $this->layout->set(
            ['invoices' => $invoices, 'status' => $status]
        );

        $this->layout->buffer('content', 'guest/invoices_index');
        $this->layout->render('layout_guest');
    }

    /**
     * @param $invoice_id
     */
    public function view($invoice_id): void
    {
        $this->load->model('invoices/mdl_items');
        $this->load->model('invoices/mdl_invoice_tax_rates');

        $invoice = $this->mdl_invoices->where('ip_invoices.invoice_id', $invoice_id)->where_in('ip_invoices.client_id', $this->user_clients)->get()->row();

        if ( ! $invoice) {
            show_404();
        }

        $this->mdl_invoices->mark_viewed($invoice->invoice_id);

        $this->layout->set(
            ['invoice' => $invoice, 'items' => $this->mdl_items->where('invoice_id', $invoice_id)->get()->result(), 'invoice_tax_rates' => $this->mdl_invoice_tax_rates->where('invoice_id', $invoice_id)->get()->result(), 'invoice_id' => $invoice_id]
        );

        $this->layout->buffer(
            [['content', 'guest/invoices_view']]
        );

        $this->layout->render('layout_guest');
    }

    /**
     * @param      $invoice_id
     * @param bool $stream
     * @param null $invoice_template
     */
    public function generate_pdf($invoice_id, $stream = true, $invoice_template = null): void
    {
        $this->load->helper('pdf');

        $invoice = $this->mdl_invoices->guest_visible()->where('ip_invoices.invoice_id', $invoice_id)
            ->where_in('ip_invoices.client_id', $this->user_clients)
            ->get()->row();

        if ( ! $invoice) {
            show_404();
        }

        $this->mdl_invoices->mark_viewed($invoice_id);

        generate_invoice_pdf($invoice_id, $stream, $invoice_template, true);
    }

    /**
     * @param      $invoice_id
     * @param bool $stream
     * @param null $invoice_template
     */
    public function generate_sumex_pdf($invoice_id, $stream = true, $invoice_template = null): void
    {
        $this->load->helper('pdf');

        $invoice = $this->mdl_invoices->guest_visible()->where('ip_invoices.invoice_id', $invoice_id)
            ->where_in('ip_invoices.client_id', $this->user_clients)
            ->get()->row();

        if ( ! $invoice) {
            show_404();
        }

        $this->mdl_invoices->mark_viewed($invoice_id);

        generate_invoice_sumex($invoice_id);
    }
}
