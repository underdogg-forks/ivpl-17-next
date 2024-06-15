<?php

if ( ! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Ajax extends Admin_Controller
{
    /**
     * @param null|int $invoice_id
     */
    public function modal_task_lookups($invoice_id = null): void
    {
        $data = [];
        $data['tasks'] = [];
        $this->load->model('mdl_tasks');

        if ( ! empty($invoice_id)) {
            $data['tasks'] = $this->mdl_tasks->get_tasks_to_invoice($invoice_id);
        }

        $this->layout->load_view('tasks/modal_task_lookups', $data);
    }

    public function process_task_selections(): void
    {
        $this->load->model('mdl_tasks');

        $tasks = $this->mdl_tasks->where_in('task_id', $this->input->post('task_ids'))->get()->result();
        foreach ($tasks as $task) {
            $task->task_price = format_amount($task->task_price);
        }

        echo json_encode($tasks, JSON_THROW_ON_ERROR);
    }
}
