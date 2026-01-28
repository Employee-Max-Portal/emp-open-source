<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Shipment_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_all_milestones() {
        $this->db->select('id, title as milestone_name, description');
        $this->db->from('tracker_milestones');
        return $this->db->get()->result_array();
    }

    public function get_all_shipments_with_milestones() {
        // Get basic shipments
        $this->db->select('s.*');
        $this->db->from('shipments s');
        $this->db->order_by('s.created_at', 'DESC');
        $shipments = $this->db->get()->result_array();

        // Enhance each shipment with related data
        foreach ($shipments as &$shipment) {
            $shipment_id = $shipment['id'];

            // Get shipping agent if column exists
            if ($this->db->field_exists('shipment_agent_id', 'shipments') && !empty($shipment['shipment_agent_id'])) {
                $agent = $this->db->get_where('staff', ['id' => $shipment['shipment_agent_id']])->row_array();
                $shipment['agent_name'] = $agent ? $agent['name'] : 'N/A';
            } else {
                // Fallback: try to get from existing sender_name or receiver_name fields
                $shipment['agent_name'] = $shipment['receiver_name'] ?? $shipment['sender_name'] ?? 'N/A';
            }

            // Get received_by and verified_by employee names if fields exist
            if ($this->db->field_exists('received_by', 'shipments') && !empty($shipment['received_by'])) {
                $received_by = $this->db->get_where('staff', ['id' => $shipment['received_by']])->row_array();
                $shipment['received_by_name'] = $received_by ? $received_by['name'] : 'N/A';
            }

            if ($this->db->field_exists('verified_by', 'shipments') && !empty($shipment['verified_by'])) {
                $verified_by = $this->db->get_where('staff', ['id' => $shipment['verified_by']])->row_array();
                $shipment['verified_by_name'] = $verified_by ? $verified_by['name'] : 'N/A';
            }

            // Get milestones if junction table exists
            if ($this->db->table_exists('shipment_milestones')) {
                $this->db->select('tm.title');
                $this->db->from('shipment_milestones sm');
                $this->db->join('tracker_milestones tm', 'sm.milestone_id = tm.id');
                $this->db->where('sm.shipment_id', $shipment_id);
                $milestones = $this->db->get()->result_array();
                $milestone_names = array_column($milestones, 'title');
                $shipment['milestone_name'] = !empty($milestone_names) ? implode('|||', $milestone_names) : 'N/A';
            } else {
                $shipment['milestone_name'] = 'N/A';
            }

            // Get suppliers if junction table exists
            if ($this->db->table_exists('shipment_suppliers')) {
                $this->db->select('s.name');
                $this->db->from('shipment_suppliers ss');
                $this->db->join('staff s', 'ss.supplier_id = s.id');
                $this->db->where('ss.shipment_id', $shipment_id);
                $suppliers = $this->db->get()->result_array();
                $supplier_names = array_column($suppliers, 'name');
                $shipment['supplier_names'] = !empty($supplier_names) ? implode(',', $supplier_names) : 'N/A';
            } else {
                $shipment['supplier_names'] = 'N/A';
            }

        }

        return $shipments;
    }

    public function create_shipment($data, $milestone_ids = [], $supplier_ids = []) {
        $this->db->trans_start();

        // Filter data to only include existing columns
        $existing_columns = $this->db->list_fields('shipments');
        $filtered_data = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $existing_columns)) {
                $filtered_data[$key] = $value;
            }
        }

        $this->db->insert('shipments', $filtered_data);
        $shipment_id = $this->db->insert_id();

        // Only insert relationships if new structure exists
        if ($this->db->table_exists('shipment_milestones') && !empty($milestone_ids)) {
            foreach ($milestone_ids as $milestone_id) {
                $this->db->insert('shipment_milestones', [
                    'shipment_id' => $shipment_id,
                    'milestone_id' => $milestone_id
                ]);
            }
        }

        if ($this->db->table_exists('shipment_suppliers') && !empty($supplier_ids)) {
            foreach ($supplier_ids as $supplier_id) {
                $this->db->insert('shipment_suppliers', [
                    'shipment_id' => $shipment_id,
                    'supplier_id' => $supplier_id
                ]);
            }
        }

        $this->db->trans_complete();
        return $this->db->trans_status() ? $shipment_id : false;
    }

    public function get_shipment_by_tracking($tracking_number) {
        // Get basic shipment data
        $this->db->select('s.*');
        $this->db->from('shipments s');
        $this->db->where('s.tracking_number', $tracking_number);
        $shipment = $this->db->get()->row_array();

        if (!$shipment) {
            return null;
        }

        // Get shipping agent if column exists
        if ($this->db->field_exists('shipment_agent_id', 'shipments') && !empty($shipment['shipment_agent_id'])) {
            $agent = $this->db->get_where('staff', ['id' => $shipment['shipment_agent_id']])->row_array();
            $shipment['agent_name'] = $agent ? $agent['name'] : 'N/A';
        } else {
            $shipment['agent_name'] = 'N/A';
        }

        // Get milestones if junction table exists
        if ($this->db->table_exists('shipment_milestones')) {
            $this->db->select('tm.title');
            $this->db->from('shipment_milestones sm');
            $this->db->join('tracker_milestones tm', 'sm.milestone_id = tm.id');
            $this->db->where('sm.shipment_id', $shipment['id']);
            $milestones = $this->db->get()->result_array();
            $milestone_names = array_column($milestones, 'title');
            $shipment['milestone_names'] = !empty($milestone_names) ? implode('|||', $milestone_names) : '';
        } else {
            $shipment['milestone_names'] = '';
        }

        return $shipment;
    }

    public function update_status($tracking_number, $status, $delivery_details = null) {
        $update_data = ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')];

        // Add delivery details if provided and status is received
        if ($status === 'received' && $delivery_details) {
            $existing_columns = $this->db->list_fields('shipments');
            foreach ($delivery_details as $key => $value) {
                if (in_array($key, $existing_columns) && $value !== null && $value !== '') {
                    $update_data[$key] = $value;
                }
            }
        }

        $this->db->where('tracking_number', $tracking_number);
        return $this->db->update('shipments', $update_data);
    }

    public function get_tracking_history($tracking_number) {
        $this->db->where('tracking_number', $tracking_number);
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get('shipment_tracking')->result_array();
    }

    public function add_tracking_history($tracking_number, $status, $location) {
        $data = [
            'tracking_number' => $tracking_number,
            'status' => $status,
            'location' => $location,
            'created_at' => date('Y-m-d H:i:s')
        ];
        return $this->db->insert('shipment_tracking', $data);
    }

    public function update_shipment($tracking_number, $data, $milestone_ids = [], $supplier_ids = []) {
        $this->db->trans_start();

        // Get shipment ID
        $shipment = $this->db->get_where('shipments', ['tracking_number' => $tracking_number])->row_array();
        if (!$shipment) {
            return false;
        }
        $shipment_id = $shipment['id'];

        // Update shipment data
        $this->db->where('tracking_number', $tracking_number);
        $this->db->update('shipments', $data);

        // Update milestone relationships if table exists
        if ($this->db->table_exists('shipment_milestones')) {
            // Delete existing relationships
            $this->db->where('shipment_id', $shipment_id);
            $this->db->delete('shipment_milestones');

            // Insert new relationships
            if (!empty($milestone_ids)) {
                foreach ($milestone_ids as $milestone_id) {
                    $this->db->insert('shipment_milestones', [
                        'shipment_id' => $shipment_id,
                        'milestone_id' => $milestone_id
                    ]);
                }
            }
        }

        // Update supplier relationships if table exists
        if ($this->db->table_exists('shipment_suppliers')) {
            // Delete existing relationships
            $this->db->where('shipment_id', $shipment_id);
            $this->db->delete('shipment_suppliers');

            // Insert new relationships
            if (!empty($supplier_ids)) {
                foreach ($supplier_ids as $supplier_id) {
                    $this->db->insert('shipment_suppliers', [
                        'shipment_id' => $shipment_id,
                        'supplier_id' => $supplier_id
                    ]);
                }
            }
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }
}