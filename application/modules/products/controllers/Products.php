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
 * Class Products.
 */
final class Products extends Admin_Controller
{
    public $load;

    public $mdl_products;

    public $layout;

    public $input;

    public $mdl_families;

    public $mdl_units;

    public $mdl_tax_rates;

    /**
     * Products constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->load->model('mdl_products');
    }

    /**
     * @param int $page
     */
    public function index($page = 0): void
    {
        $this->mdl_products->paginate(site_url('products/index'), $page);
        $products = $this->mdl_products->result();

        $this->layout->set('products', $products);
        $this->layout->buffer('content', 'products/index');
        $this->layout->render();
    }

    /**
     * @param null $id
     */
    public function form($id = null): void
    {
        if ($this->input->post('btn_cancel')) {
            redirect('products');
        }

        if ($this->mdl_products->run_validation()) {
            // Get the db array
            $db_array = $this->mdl_products->db_array();
            $this->mdl_products->save($id, $db_array);
            redirect('products');
        }

        if ($id && ! $this->input->post('btn_submit') && ! $this->mdl_products->prep_form($id)) {
            show_404();
        }

        $this->load->model('families/mdl_families');
        $this->load->model('units/mdl_units');
        $this->load->model('tax_rates/mdl_tax_rates');

        $this->layout->set(
            ['families' => $this->mdl_families->get()->result(), 'units' => $this->mdl_units->get()->result(), 'tax_rates' => $this->mdl_tax_rates->get()->result()]
        );

        $this->layout->buffer('content', 'products/form');
        $this->layout->render();
    }

    /**
     * @param $id
     */
    public function delete($id): void
    {
        $this->mdl_products->delete($id);
        redirect('products');
    }
}
