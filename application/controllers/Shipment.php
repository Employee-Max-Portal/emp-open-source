<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Shipment extends Admin_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('shipment_model');
        $this->load->helper('file');
    }

	public function index() {
        if (!get_permission('shipment_management', 'is_view')) {
            access_denied();
        }
        $this->data['shipments'] = $this->shipment_model->get_all_shipments_with_milestones();

        // Debug: Check what data we're getting
        if (!empty($this->data['shipments'])) {
            error_log('First shipment data: ' . print_r($this->data['shipments'][0], true));
            error_log('Shipment agent ID: ' . ($this->data['shipments'][0]['shipment_agent_id'] ?? 'NULL'));
            error_log('Agent name: ' . ($this->data['shipments'][0]['agent_name'] ?? 'NULL'));
        }

        $this->data['title'] = translate('shipment_management');
        $this->data['sub_page'] = 'shipment/index';
        $this->data['main_menu'] = 'shipment';
        $this->load->view('layout/index', $this->data);
    }


    public function create() {
        if (!get_permission('shipment_management', 'is_add')) {
            access_denied();
        }

        // Basic data that should exist in current structure
        $data = [
            'tracking_number' => $this->input->post('tracking_number'),
            'shipment_name' => $this->input->post('shipment_name'),
            'origin' => $this->input->post('origin'),
            'destination' => $this->input->post('destination'),
            'status' => 'ordered',
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Add optional fields if they exist in form
        $optional_fields = ['shipping_mark', 'quantity_cartons', 'origin_weight', 'shipping_method', 'description', 'shipment_agent_id'];
        foreach ($optional_fields as $field) {
            $value = $this->input->post($field);
            if ($value !== null && $value !== '') {
                $data[$field] = $value;
            }
        }

        // Handle multiple attachments (based on working RDC pattern)
        $attachments = [];
        if (isset($_FILES['attachments']) && !empty($_FILES['attachments']['name'][0])) {
            if (!is_dir('./uploads/shipments/')) {
                mkdir('./uploads/shipments/', 0777, TRUE);
            }

            $config['upload_path'] = './uploads/shipments/';
            $config['allowed_types'] = 'pdf|doc|docx|jpg|jpeg|png';
            $config['max_size'] = 5120; // 5MB
            $config['encrypt_name'] = true;

            for ($i = 0; $i < count($_FILES['attachments']['name']); $i++) {
                if (!empty($_FILES['attachments']['name'][$i])) {
                    $_FILES['single_file']['name'] = $_FILES['attachments']['name'][$i];
                    $_FILES['single_file']['type'] = $_FILES['attachments']['type'][$i];
                    $_FILES['single_file']['tmp_name'] = $_FILES['attachments']['tmp_name'][$i];
                    $_FILES['single_file']['error'] = $_FILES['attachments']['error'][$i];
                    $_FILES['single_file']['size'] = $_FILES['attachments']['size'][$i];

                    $this->upload->initialize($config);
                    if ($this->upload->do_upload('single_file')) {
                        $attachments[] = $this->upload->data('file_name');
                    }
                }
            }
        }

        $data['attachments'] = !empty($attachments) ? json_encode($attachments) : null;

        // Handle milestone and supplier relationships
        $milestone_ids = $this->input->post('milestone_ids');
        $supplier_ids = $this->input->post('supplier_ids');

        if ($shipment_id = $this->shipment_model->create_shipment($data, $milestone_ids, $supplier_ids)) {
            // Add initial tracking entry
            try {
                $this->shipment_model->add_tracking_history(
                    $data['tracking_number'],
                    'ordered',
                    'Shipment created'
                );
            } catch (Exception $e) {
                error_log('Failed to add tracking history: ' . $e->getMessage());
            }
            set_alert('success', translate('information_has_been_saved_successfully'));
        } else {
            set_alert('error', translate('something_went_wrong'));
        }
        redirect('shipment');
    }

    public function track($tracking_number = null) {
        if (!get_permission('shipment_management', 'is_view')) {
            access_denied();
        }

        // Get tracking number from URL parameter or GET parameter
        if (!$tracking_number) {
            $tracking_number = $this->input->get('tracking');
        }

        if ($tracking_number) {
            $this->data['shipment'] = $this->shipment_model->get_shipment_by_tracking($tracking_number);
            $this->data['tracking_history'] = $this->shipment_model->get_tracking_history($tracking_number);
        }
        $this->data['title'] = translate('track_shipment');
        $this->data['sub_page'] = 'shipment/track';
        $this->data['main_menu'] = 'shipment';
        $this->load->view('layout/index', $this->data);
    }

    public function update_status() {
        if (!get_permission('shipment_management', 'is_edit')) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            return;
        }

        $tracking_number = $this->input->post('tracking_number');
        $status = $this->input->post('status');
        $location = $this->input->post('location');

        if (empty($tracking_number) || empty($status)) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            return;
        }

        try {
            // Prepare delivery details if status is received
            $delivery_details = null;
            if ($status === 'received') {
                $delivery_details = [
                    'delivery_kg' => $this->input->post('delivery_kg'),
                    'per_kg_amount' => $this->input->post('per_kg_amount'),
                    'total_cost' => $this->input->post('total_cost'),
                    'received_by' => $this->input->post('received_by'),
                    'verified_by' => $this->input->post('verified_by'),
                    'storage_location' => $this->input->post('storage_location')
                ];
            }

            $update_result = $this->shipment_model->update_status($tracking_number, $status, $delivery_details);
            if ($update_result) {
                $this->shipment_model->add_tracking_history($tracking_number, $status, $location);
                echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update shipment status']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }

    public function get_shipment_details() {
        if (!get_permission('shipment_management', 'is_edit')) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            return;
        }

        $tracking_number = $this->input->post('tracking_number');
        if (empty($tracking_number)) {
            echo json_encode(['success' => false, 'message' => 'Tracking number required']);
            return;
        }

        try {
            $shipment = $this->shipment_model->get_shipment_by_tracking($tracking_number);
            if (!$shipment) {
                echo json_encode(['success' => false, 'message' => 'Shipment not found']);
                return;
            }

            // Get milestone IDs
            $milestone_ids = [];
            if ($this->db->table_exists('shipment_milestones')) {
                $this->db->select('milestone_id');
                $this->db->where('shipment_id', $shipment['id']);
                $milestones = $this->db->get('shipment_milestones')->result_array();
                $milestone_ids = array_column($milestones, 'milestone_id');
            }

            // Get supplier IDs
            $supplier_ids = [];
            if ($this->db->table_exists('shipment_suppliers')) {
                $this->db->select('supplier_id');
                $this->db->where('shipment_id', $shipment['id']);
                $suppliers = $this->db->get('shipment_suppliers')->result_array();
                $supplier_ids = array_column($suppliers, 'supplier_id');
            }

            echo json_encode([
                'success' => true,
                'shipment' => $shipment,
                'milestone_ids' => $milestone_ids,
                'supplier_ids' => $supplier_ids
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function update() {
        if (!get_permission('shipment_management', 'is_edit')) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            return;
        }

        $old_tracking_number = $this->input->post('old_tracking_number');
        $new_tracking_number = $this->input->post('tracking_number');

        if (empty($old_tracking_number)) {
            echo json_encode(['success' => false, 'message' => 'Original tracking number required']);
            return;
        }

        try {
            // Check if tracking number changed and validate
            if ($old_tracking_number !== $new_tracking_number) {
                $existing = $this->db->get_where('shipments', ['tracking_number' => $new_tracking_number])->row();
                if ($existing) {
                    echo json_encode(['success' => false, 'message' => 'Tracking number already exists']);
                    return;
                }
            }

            // Get shipment ID
            $shipment = $this->db->get_where('shipments', ['tracking_number' => $old_tracking_number])->row_array();
            if (!$shipment) {
                echo json_encode(['success' => false, 'message' => 'Shipment not found']);
                return;
            }

            // Prepare update data
            $data = [
                'tracking_number' => $new_tracking_number,
                'shipment_name' => $this->input->post('shipment_name'),
                'origin' => $this->input->post('origin'),
                'destination' => $this->input->post('destination'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Add optional fields
            $optional_fields = ['shipping_mark', 'quantity_cartons', 'origin_weight', 'shipping_method', 'description', 'shipment_agent_id'];
            foreach ($optional_fields as $field) {
                $value = $this->input->post($field);
                if ($value !== null && $value !== '') {
                    $data[$field] = $value;
                }
            }

            // Filter data to only include existing columns
            $existing_columns = $this->db->list_fields('shipments');
            $filtered_data = [];
            foreach ($data as $key => $value) {
                if (in_array($key, $existing_columns)) {
                    $filtered_data[$key] = $value;
                }
            }

            $milestone_ids = $this->input->post('milestone_ids');
            $supplier_ids = $this->input->post('supplier_ids');

            // Update shipment
            $this->db->where('tracking_number', $old_tracking_number);
            $this->db->update('shipments', $filtered_data);

            // Update tracking history if tracking number changed
            if ($old_tracking_number !== $new_tracking_number) {
                $this->db->where('tracking_number', $old_tracking_number);
                $this->db->update('shipment_tracking', ['tracking_number' => $new_tracking_number]);
            }

            // Update relationships
            if ($this->db->table_exists('shipment_milestones')) {
                $this->db->where('shipment_id', $shipment['id']);
                $this->db->delete('shipment_milestones');
                if (!empty($milestone_ids)) {
                    foreach ($milestone_ids as $milestone_id) {
                        $this->db->insert('shipment_milestones', [
                            'shipment_id' => $shipment['id'],
                            'milestone_id' => $milestone_id
                        ]);
                    }
                }
            }

            if ($this->db->table_exists('shipment_suppliers')) {
                $this->db->where('shipment_id', $shipment['id']);
                $this->db->delete('shipment_suppliers');
                if (!empty($supplier_ids)) {
                    foreach ($supplier_ids as $supplier_id) {
                        $this->db->insert('shipment_suppliers', [
                            'shipment_id' => $shipment['id'],
                            'supplier_id' => $supplier_id
                        ]);
                    }
                }
            }

            echo json_encode(['success' => true, 'message' => 'Shipment updated successfully']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    	public function delete($tracking_number)
    {
        if (get_permission('shipment_management', 'is_delete')) {
            $this->db->where('tracking_number', $tracking_number);
			$this->db->delete('shipment_tracking');
			$this->db->where('tracking_number', $tracking_number);
			$this->db->delete('shipments');
        }
    }
}