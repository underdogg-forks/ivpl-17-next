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
 * Class Mdl_Client_Notes.
 */
final class Mdl_Client_Notes extends Response_Model
{
    public $table = 'ip_client_notes';

    public $primary_key = 'ip_client_notes.client_note_id';

    public function default_order_by(): void
    {
        $this->db->order_by('ip_client_notes.client_note_date DESC');
    }

    public function validation_rules()
    {
        return ['client_id' => ['field' => 'client_id', 'label' => trans('client'), 'rules' => 'required'], 'client_note' => ['field' => 'client_note', 'label' => trans('note'), 'rules' => 'required']];
    }

    public function db_array()
    {
        $db_array = parent::db_array();

        $db_array['client_note_date'] = date('Y-m-d');

        return $db_array;
    }
}
