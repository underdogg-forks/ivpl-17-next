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
 * Class Mdl_Templates.
 */
final class Mdl_Templates extends CI_Model
{
    public $load;

    /**
     * @param string $type
     *
     * @return array
     */
    public function get_invoice_templates($type = 'pdf')
    {
        $templates = null;
        $this->load->helper('directory');

        if ($type == 'pdf') {
            $templates = directory_map(APPPATH . '/views/invoice_templates/pdf', true);
        } elseif ($type == 'public') {
            $templates = directory_map(APPPATH . '/views/invoice_templates/public', true);
        }

        return $this->remove_extension($templates);
    }

    /**
     * @param string $type
     *
     * @return array|mixed
     */
    public function get_quote_templates($type = 'pdf')
    {
        $templates = null;
        $this->load->helper('directory');

        if ($type == 'pdf') {
            $templates = directory_map(APPPATH . '/views/quote_templates/pdf', true);
        } elseif ($type == 'public') {
            $templates = directory_map(APPPATH . '/views/quote_templates/public', true);
        }

        return $this->remove_extension($templates);
    }

    /**
     * @param $files
     *
     * @return mixed
     */
    private function remove_extension($files)
    {
        foreach ($files as $key => $file) {
            $files[$key] = str_replace('.php', '', $file);
        }

        return $files;
    }
}
