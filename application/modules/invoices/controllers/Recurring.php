<?php

if ( ! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Recurring extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('mdl_invoices_recurring');
    }

    /**
     * @param int $page
     */
    public function index($page = 0): void
    {
        $this->mdl_invoices_recurring->paginate(site_url('invoices/recurring'), $page);
        $recurring_invoices = $this->mdl_invoices_recurring->result();

        $this->layout->set('recur_frequencies', $this->mdl_invoices_recurring->recur_frequencies);
        $this->layout->set('recurring_invoices', $recurring_invoices);
        $this->layout->buffer('content', 'invoices/index_recurring');
        $this->layout->render();
    }

    /**
     * @param $invoice_recurring_id
     */
    public function stop($invoice_recurring_id): void
    {
        $this->mdl_invoices_recurring->stop($invoice_recurring_id);
        redirect('invoices/recurring/index');
    }

    /**
     * @param $invoice_recurring_id
     */
    public function delete($invoice_recurring_id): void
    {
        $this->mdl_invoices_recurring->delete($invoice_recurring_id);
        redirect('invoices/recurring/index');
    }
}
