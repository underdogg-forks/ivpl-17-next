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
 * Class Mdl_Invoice_Amounts.
 */
final class Mdl_Invoice_Amounts extends CI_Model
{
    public $db;

    public $load;

    public $mdl_invoices;

    public $config;

    public $mdl_invoice_tax_rates;

    /**
     * IP_INVOICE_AMOUNTS
     * invoice_amount_id
     * invoice_id
     * invoice_item_subtotal    SUM(item_subtotal)
     * invoice_item_tax_total   SUM(item_tax_total)
     * invoice_tax_total
     * invoice_total            invoice_item_subtotal + invoice_item_tax_total + invoice_tax_total
     * invoice_paid
     * invoice_balance          invoice_total - invoice_paid.
     *
     * IP_INVOICE_ITEM_AMOUNTS
     * item_amount_id
     * item_id
     * item_tax_rate_id
     * item_subtotal            item_quantity * item_price
     * item_tax_total           item_subtotal * tax_rate_percent
     * item_total               item_subtotal + item_tax_total
     *
     * @param $invoice_id
     */
    public function calculate($invoice_id): void
    {
        // Get the basic totals
        $query = $this->db->query('
        SELECT  SUM(item_subtotal) AS invoice_item_subtotal,
		        SUM(item_tax_total) AS invoice_item_tax_total,
		        SUM(item_subtotal) + SUM(item_tax_total) AS invoice_total,
		        SUM(item_discount) AS invoice_item_discount
		FROM ip_invoice_item_amounts
		WHERE item_id IN (
		    SELECT item_id FROM ip_invoice_items WHERE invoice_id = ' . $this->db->escape($invoice_id) . '
		    )
        ');

        $invoice_amounts = $query->row();

        $invoice_item_subtotal = $invoice_amounts->invoice_item_subtotal - $invoice_amounts->invoice_item_discount;
        $invoice_subtotal = $invoice_item_subtotal + $invoice_amounts->invoice_item_tax_total;
        $invoice_total = $this->calculate_discount($invoice_id, $invoice_subtotal);

        // Get the amount already paid
        $query = $this->db->query(
            '
          SELECT SUM(payment_amount) AS invoice_paid
          FROM ip_payments
          WHERE invoice_id = ' . $this->db->escape($invoice_id)
        );

        $invoice_paid = $query->row()->invoice_paid ? (float) $query->row()->invoice_paid : 0;

        // Create the database array and insert or update
        $db_array = ['invoice_id' => $invoice_id, 'invoice_item_subtotal' => $invoice_item_subtotal, 'invoice_item_tax_total' => $invoice_amounts->invoice_item_tax_total, 'invoice_total' => $invoice_total, 'invoice_paid' => $invoice_paid, 'invoice_balance' => $invoice_total - $invoice_paid];

        $this->db->where('invoice_id', $invoice_id);

        if ($this->db->get('ip_invoice_amounts')->num_rows()) {
            // The record already exists; update it
            $this->db->where('invoice_id', $invoice_id);
            $this->db->update('ip_invoice_amounts', $db_array);
        } else {
            // The record does not yet exist; insert it
            $this->db->insert('ip_invoice_amounts', $db_array);
        }

        // Calculate the invoice taxes
        $this->calculate_invoice_taxes($invoice_id);

        // Get invoice status
        $this->load->model('invoices/mdl_invoices');
        $invoice = $this->mdl_invoices->get_by_id($invoice_id);
        $invoice_is_credit = ($invoice->creditinvoice_parent_id > 0);

        // Set to paid if balance is zero
        // Check if the invoice total is not zero or negative
        if ($invoice->invoice_balance == 0 && ($invoice->invoice_total != 0 || $invoice_is_credit)) {
            $this->db->where('invoice_id', $invoice_id);
            $payment = $this->db->get('ip_payments')->row();
            $payment_method_id = ($payment->payment_method_id ?: 0);
            $this->db->where('invoice_id', $invoice_id);
            $this->db->set('invoice_status_id', 4);
            $this->db->set('payment_method', $payment_method_id);
            $this->db->update('ip_invoices');
            // Set to read-only if applicable
            if (
                $this->config->item('disable_read_only') == false
                && $invoice->invoice_status_id == get_setting('read_only_toggle')
            ) {
                $this->db->where('invoice_id', $invoice_id);
                $this->db->set('is_read_only', 1);
                $this->db->update('ip_invoices');
            }
        }
    }

    /**
     * @param $invoice_id
     * @param $invoice_total
     *
     * @return float
     */
    public function calculate_discount($invoice_id, $invoice_total)
    {
        $this->db->where('invoice_id', $invoice_id);
        $invoice_data = $this->db->get('ip_invoices')->row();

        if ($invoice_data->invoice_discount_amount == null) {
            $invoice_data->invoice_discount_amount = 0.0;
        }

        if ($invoice_data->invoice_discount_percent == null) {
            $invoice_data->invoice_discount_percent = 0.0;
        }

        $total = (float) number_format($invoice_total, 2, '.', '');
        $discount_amount = (float) number_format($invoice_data->invoice_discount_amount, 2, '.', '');
        $discount_percent = (float) number_format($invoice_data->invoice_discount_percent, 2, '.', '');

        $total -= $discount_amount;

        return $total - round(($total / 100 * $discount_percent), 2);
    }

    /**
     * @param $invoice_id
     */
    public function calculate_invoice_taxes($invoice_id): void
    {
        // First check to see if there are any invoice taxes applied
        $this->load->model('invoices/mdl_invoice_tax_rates');
        $invoice_tax_rates = $this->mdl_invoice_tax_rates->where('invoice_id', $invoice_id)->get()->result();

        if ($invoice_tax_rates) {
            // There are invoice taxes applied
            // Get the current invoice amount record
            $invoice_amount = $this->db->where('invoice_id', $invoice_id)->get('ip_invoice_amounts')->row();

            // Loop through the invoice taxes and update the amount for each of the applied invoice taxes
            foreach ($invoice_tax_rates as $invoice_tax_rate) {
                if ($invoice_tax_rate->include_item_tax) {
                    // The invoice tax rate should include the applied item tax
                    $invoice_tax_rate_amount = ($invoice_amount->invoice_item_subtotal + $invoice_amount->invoice_item_tax_total) * ($invoice_tax_rate->invoice_tax_rate_percent / 100);
                } else {
                    // The invoice tax rate should not include the applied item tax
                    $invoice_tax_rate_amount = $invoice_amount->invoice_item_subtotal * ($invoice_tax_rate->invoice_tax_rate_percent / 100);
                }

                // Update the invoice tax rate record
                $db_array = ['invoice_tax_rate_amount' => $invoice_tax_rate_amount];
                $this->db->where('invoice_tax_rate_id', $invoice_tax_rate->invoice_tax_rate_id);
                $this->db->update('ip_invoice_tax_rates', $db_array);
            }

            // Update the invoice amount record with the total invoice tax amount
            $this->db->query('
              UPDATE ip_invoice_amounts
              SET invoice_tax_total = (
                SELECT SUM(invoice_tax_rate_amount)
                FROM ip_invoice_tax_rates
                WHERE invoice_id = ' . $this->db->escape($invoice_id) . ')
              WHERE invoice_id = ' . $this->db->escape($invoice_id));

            // Get the updated invoice amount record
            $invoice_amount = $this->db->where('invoice_id', $invoice_id)->get('ip_invoice_amounts')->row();

            // Recalculate the invoice total and balance
            $invoice_total = $invoice_amount->invoice_item_subtotal + $invoice_amount->invoice_item_tax_total + $invoice_amount->invoice_tax_total;
            $invoice_total = $this->calculate_discount($invoice_id, $invoice_total);
            $invoice_balance = $invoice_total - $invoice_amount->invoice_paid;

            // Update the invoice amount record
            $db_array = ['invoice_total' => $invoice_total, 'invoice_balance' => $invoice_balance];

            $this->db->where('invoice_id', $invoice_id);
            $this->db->update('ip_invoice_amounts', $db_array);
        } else {
            // No invoice taxes applied

            $db_array = ['invoice_tax_total' => '0.00'];

            $this->db->where('invoice_id', $invoice_id);
            $this->db->update('ip_invoice_amounts', $db_array);
        }
    }

    /**
     * @param null $period
     *
     * @return mixed
     */
    public function get_total_invoiced($period = null)
    {
        return match ($period) {
            'month' => $this->db->query('
					SELECT SUM(invoice_total) AS total_invoiced
					FROM ip_invoice_amounts
					WHERE invoice_id IN
					(SELECT invoice_id FROM ip_invoices
					WHERE MONTH(invoice_date_created) = MONTH(NOW())
					AND YEAR(invoice_date_created) = YEAR(NOW()))')->row()->total_invoiced,
            'last_month' => $this->db->query('
					SELECT SUM(invoice_total) AS total_invoiced
					FROM ip_invoice_amounts
					WHERE invoice_id IN
					(SELECT invoice_id FROM ip_invoices
					WHERE MONTH(invoice_date_created) = MONTH(NOW() - INTERVAL 1 MONTH)
					AND YEAR(invoice_date_created) = YEAR(NOW() - INTERVAL 1 MONTH))')->row()->total_invoiced,
            'year' => $this->db->query('
					SELECT SUM(invoice_total) AS total_invoiced
					FROM ip_invoice_amounts
					WHERE invoice_id IN
					(SELECT invoice_id FROM ip_invoices WHERE YEAR(invoice_date_created) = YEAR(NOW()))')->row()->total_invoiced,
            'last_year' => $this->db->query('
					SELECT SUM(invoice_total) AS total_invoiced
					FROM ip_invoice_amounts
					WHERE invoice_id IN
					(SELECT invoice_id FROM ip_invoices WHERE YEAR(invoice_date_created) = YEAR(NOW() - INTERVAL 1 YEAR))')->row()->total_invoiced,
            default => $this->db->query('SELECT SUM(invoice_total) AS total_invoiced FROM ip_invoice_amounts')->row()->total_invoiced,
        };
    }

    /**
     * @param null $period
     *
     * @return mixed
     */
    public function get_total_paid($period = null)
    {
        return match ($period) {
            'month' => $this->db->query('
					SELECT SUM(invoice_paid) AS total_paid
					FROM ip_invoice_amounts
					WHERE invoice_id IN
					(SELECT invoice_id FROM ip_invoices
					WHERE MONTH(invoice_date_created) = MONTH(NOW())
					AND YEAR(invoice_date_created) = YEAR(NOW()))')->row()->total_paid,
            'last_month' => $this->db->query('SELECT SUM(invoice_paid) AS total_paid
					FROM ip_invoice_amounts
					WHERE invoice_id IN
					(SELECT invoice_id FROM ip_invoices
					WHERE MONTH(invoice_date_created) = MONTH(NOW() - INTERVAL 1 MONTH)
					AND YEAR(invoice_date_created) = YEAR(NOW() - INTERVAL 1 MONTH))')->row()->total_paid,
            'year' => $this->db->query('SELECT SUM(invoice_paid) AS total_paid
					FROM ip_invoice_amounts
					WHERE invoice_id IN
					(SELECT invoice_id FROM ip_invoices WHERE YEAR(invoice_date_created) = YEAR(NOW()))')->row()->total_paid,
            'last_year' => $this->db->query('SELECT SUM(invoice_paid) AS total_paid
					FROM ip_invoice_amounts
					WHERE invoice_id IN
					(SELECT invoice_id FROM ip_invoices WHERE YEAR(invoice_date_created) = YEAR(NOW() - INTERVAL 1 YEAR))')->row()->total_paid,
            default => $this->db->query('SELECT SUM(invoice_paid) AS total_paid FROM ip_invoice_amounts')->row()->total_paid,
        };
    }

    /**
     * @param null $period
     *
     * @return mixed
     */
    public function get_total_balance($period = null)
    {
        return match ($period) {
            'month' => $this->db->query('SELECT SUM(invoice_balance) AS total_balance
					FROM ip_invoice_amounts
					WHERE invoice_id IN
					(SELECT invoice_id FROM ip_invoices
					WHERE MONTH(invoice_date_created) = MONTH(NOW())
					AND YEAR(invoice_date_created) = YEAR(NOW()))')->row()->total_balance,
            'last_month' => $this->db->query('SELECT SUM(invoice_balance) AS total_balance
					FROM ip_invoice_amounts
					WHERE invoice_id IN
					(SELECT invoice_id FROM ip_invoices
					WHERE MONTH(invoice_date_created) = MONTH(NOW() - INTERVAL 1 MONTH)
					AND YEAR(invoice_date_created) = YEAR(NOW() - INTERVAL 1 MONTH))')->row()->total_balance,
            'year' => $this->db->query('SELECT SUM(invoice_balance) AS total_balance
					FROM ip_invoice_amounts
					WHERE invoice_id IN
					(SELECT invoice_id FROM ip_invoices WHERE YEAR(invoice_date_created) = YEAR(NOW()))')->row()->total_balance,
            'last_year' => $this->db->query('SELECT SUM(invoice_balance) AS total_balance
					FROM ip_invoice_amounts
					WHERE invoice_id IN
					(SELECT invoice_id FROM ip_invoices WHERE YEAR(invoice_date_created) = (YEAR(NOW() - INTERVAL 1 YEAR)))')->row()->total_balance,
            default => $this->db->query('SELECT SUM(invoice_balance) AS total_balance FROM ip_invoice_amounts')->row()->total_balance,
        };
    }

    /**
     * @param string $period
     *
     * @return array
     */
    public function get_status_totals($period = '')
    {
        switch ($period) {
            default:
            case 'this-month':
                $results = $this->db->query('
					SELECT ip_invoices.invoice_status_id, (CASE ip_invoices.invoice_status_id WHEN 4 THEN SUM(ip_invoice_amounts.invoice_paid) ELSE SUM(ip_invoice_amounts.invoice_balance) END) AS sum_total, COUNT(*) AS num_total
					FROM ip_invoice_amounts
					JOIN ip_invoices ON ip_invoices.invoice_id = ip_invoice_amounts.invoice_id
                        AND MONTH(ip_invoices.invoice_date_created) = MONTH(NOW())
                        AND YEAR(ip_invoices.invoice_date_created) = YEAR(NOW())
					GROUP BY ip_invoices.invoice_status_id')->result_array();
                break;
            case 'last-month':
                $results = $this->db->query('
					SELECT invoice_status_id, (CASE ip_invoices.invoice_status_id WHEN 4 THEN SUM(invoice_paid) ELSE SUM(invoice_balance) END) AS sum_total, COUNT(*) AS num_total
					FROM ip_invoice_amounts
					JOIN ip_invoices ON ip_invoices.invoice_id = ip_invoice_amounts.invoice_id
                        AND MONTH(ip_invoices.invoice_date_created) = MONTH(NOW() - INTERVAL 1 MONTH)
                        AND YEAR(ip_invoices.invoice_date_created) = YEAR(NOW())
					GROUP BY ip_invoices.invoice_status_id')->result_array();
                break;
            case 'this-quarter':
                $results = $this->db->query('
					SELECT invoice_status_id, (CASE ip_invoices.invoice_status_id WHEN 4 THEN SUM(ip_invoice_amounts.invoice_paid) ELSE SUM(ip_invoice_amounts.invoice_balance) END) AS sum_total, COUNT(*) AS num_total
					FROM ip_invoice_amounts
					JOIN ip_invoices ON ip_invoices.invoice_id = ip_invoice_amounts.invoice_id
                        AND QUARTER(ip_invoices.invoice_date_created) = QUARTER(NOW())
                        AND YEAR(ip_invoices.invoice_date_created) = YEAR(NOW())
					GROUP BY ip_invoices.invoice_status_id')->result_array();
                break;
            case 'last-quarter':
                $results = $this->db->query('
					SELECT invoice_status_id, (CASE ip_invoices.invoice_status_id WHEN 4 THEN SUM(invoice_paid) ELSE SUM(invoice_balance) END) AS sum_total, COUNT(*) AS num_total
					FROM ip_invoice_amounts
					JOIN ip_invoices ON ip_invoices.invoice_id = ip_invoice_amounts.invoice_id
                        AND QUARTER(ip_invoices.invoice_date_created) = QUARTER(NOW() - INTERVAL 1 QUARTER)
                        AND YEAR(ip_invoices.invoice_date_created) = YEAR(NOW())
					GROUP BY ip_invoices.invoice_status_id')->result_array();
                break;
            case 'this-year':
                $results = $this->db->query('
					SELECT invoice_status_id, (CASE ip_invoices.invoice_status_id WHEN 4 THEN SUM(ip_invoice_amounts.invoice_paid) ELSE SUM(ip_invoice_amounts.invoice_balance) END) AS sum_total, COUNT(*) AS num_total
					FROM ip_invoice_amounts
					JOIN ip_invoices ON ip_invoices.invoice_id = ip_invoice_amounts.invoice_id
                        AND YEAR(ip_invoices.invoice_date_created) = YEAR(NOW())
					GROUP BY ip_invoices.invoice_status_id')->result_array();
                break;
            case 'last-year':
                $results = $this->db->query('
					SELECT invoice_status_id, (CASE ip_invoices.invoice_status_id WHEN 4 THEN SUM(invoice_paid) ELSE SUM(invoice_balance) END) AS sum_total, COUNT(*) AS num_total
					FROM ip_invoice_amounts
					JOIN ip_invoices ON ip_invoices.invoice_id = ip_invoice_amounts.invoice_id
                        AND YEAR(ip_invoices.invoice_date_created) = YEAR(NOW() - INTERVAL 1 YEAR)
					GROUP BY ip_invoices.invoice_status_id')->result_array();
                break;
        }

        $return = [];

        foreach ($this->mdl_invoices->statuses() as $key => $status) {
            $return[$key] = ['invoice_status_id' => $key, 'class' => $status['class'], 'label' => $status['label'], 'href' => $status['href'], 'sum_total' => 0, 'num_total' => 0];
        }

        foreach ($results as $result) {
            $return[$result['invoice_status_id']] = array_merge($return[$result['invoice_status_id']], $result);
        }

        return $return;
    }
}
