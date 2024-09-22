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
 * Class Mdl_User_Clients.
 */
class Mdl_User_Clients extends MY_Model
{
    public $db;

    public $load;

    public $mdl_clients;

    public $mdl_users;

    public $table = 'ip_user_clients';

    public $primary_key = 'ip_user_clients.user_client_id';

    public function default_select(): void
    {
        $this->db->select('ip_user_clients.*, ip_users.user_name, ip_clients.client_name, ip_clients.client_surname');
    }

    public function default_join(): void
    {
        $this->db->join('ip_users', 'ip_users.user_id = ip_user_clients.user_id');
        $this->db->join('ip_clients', 'ip_clients.client_id = ip_user_clients.client_id');
    }

    public function default_order_by(): void
    {
        $this->db->order_by('ip_clients.client_name', 'ACS');
    }

    /**
     * @return array
     */
    public function validation_rules()
    {
        return ['user_id' => ['field' => 'user_id', 'label' => trans('user'), 'rules' => 'required'], 'client_id' => ['field' => 'client_id', 'label' => trans('client'), 'rules' => 'required']];
    }

    /**
     * @param $user_id
     *
     * @return $this
     */
    public function assigned_to($user_id)
    {
        $this->filter_where('ip_user_clients.user_id', $user_id);

        return $this;
    }

    /**
     * @param array $users_id
     */
    public function set_all_clients_user($users_id): void
    {
        $this->load->model('clients/mdl_clients');

        foreach ($users_id as $x => $singleUsers_id) {
            $clients = $this->mdl_clients->get_not_assigned_to_user($singleUsers_id);
            $clientsCount = count($clients);
            for ($i = 0; $i < (is_array($clients) || $clients instanceof \Countable ? $clientsCount : 0); $i++) {
                $user_client = ['user_id' => $singleUsers_id, 'client_id' => $clients[$i]->client_id];

                $this->db->insert('ip_user_clients', $user_client);
            }
        }
    }

    public function get_users_all_clients(): void
    {
        $this->load->model('users/mdl_users');
        $users = $this->mdl_users->where('user_all_clients', 1)->get()->result();

        $new_users = [];
        $usersCount = count($users);

        for ($i = 0; $i < (is_array($users) || $users instanceof \Countable ? $usersCount : 0); $i++) {
            $new_users[] = $users[$i]->user_id;
        }

        $this->set_all_clients_user($new_users);
    }
}
