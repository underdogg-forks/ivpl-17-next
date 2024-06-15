<?php

if ( ! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Mdl_Projects extends Response_Model
{
    public $table = 'ip_projects';

    public $primary_key = 'ip_projects.project_id';

    public function default_select(): void
    {
        $this->db->select('SQL_CALC_FOUND_ROWS *', false);
    }

    public function default_order_by(): void
    {
        $this->db->order_by('ip_projects.project_id');
    }

    public function default_join(): void
    {
        $this->db->join('ip_clients', 'ip_clients.client_id = ip_projects.client_id', 'left');
    }

    public function get_latest()
    {
        $this->db->order_by('ip_projects.project_id', 'DESC');

        return $this;
    }

    /**
     * @return array
     */
    public function validation_rules()
    {
        return ['project_name' => ['field' => 'project_name', 'label' => trans('project_name'), 'rules' => 'required'], 'client_id' => ['field' => 'client_id', 'label' => trans('client')]];
    }

    public function get_tasks($project_id)
    {
        $result = [];

        if ( ! $project_id) {
            return $result;
        }

        $this->load->model('tasks/mdl_tasks');
        $query = $this->mdl_tasks
            ->where('ip_tasks.project_id', $project_id)
            ->get();

        foreach ($query->result() as $row) {
            $result[] = $row;
        }

        return $result;
    }
}
