<?php

if ( ! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Tax_Rates extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('mdl_tax_rates');
    }

    /**
     * @param int $page
     */
    public function index($page = 0): void
    {
        $this->mdl_tax_rates->paginate(site_url('tax_rates/index'), $page);
        $tax_rates = $this->mdl_tax_rates->result();

        $this->layout->set('tax_rates', $tax_rates);
        $this->layout->buffer('content', 'tax_rates/index');
        $this->layout->render();
    }

    /**
     * @param null $id
     */
    public function form($id = null): void
    {
        if ($this->input->post('btn_cancel')) {
            redirect('tax_rates');
        }

        if ($this->mdl_tax_rates->run_validation()) {
            $this->mdl_tax_rates->form_values['tax_rate_percent'] = standardize_amount($this->mdl_tax_rates->form_values['tax_rate_percent']);

            // We need to use the correct decimal point for sql IPT-310
            $db_array = $this->mdl_tax_rates->db_array();
            $db_array['tax_rate_percent'] = standardize_amount($this->input->post('tax_rate_percent'));

            $this->mdl_tax_rates->save($id, $db_array);

            redirect('tax_rates');
        }

        if ($id && ! $this->input->post('btn_submit')) {
            if ( ! $this->mdl_tax_rates->prep_form($id)) {
                show_404();
            }
        }

        $this->layout->buffer('content', 'tax_rates/form');
        $this->layout->render();
    }

    /**
     * @param $id
     */
    public function delete($id): void
    {
        $this->mdl_tax_rates->delete($id);
        redirect('tax_rates');
    }
}
