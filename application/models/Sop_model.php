<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Sop_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }
public function getList()
{
    $this->db->select('
        sop.*,
        staff.name AS created_by_name,
        GROUP_CONCAT(DISTINCT roles.name ORDER BY roles.name SEPARATOR ", ") AS roles_name
    ', FALSE);
    $this->db->from('sop');
    $this->db->join('staff', 'staff.id = sop.created_by', 'left');
    $this->db->join('roles', 'FIND_IN_SET(roles.id, sop.verifier_role)', 'left');
    $this->db->order_by('sop.id', 'DESC');
    $this->db->group_by('sop.id'); // ensure proper aggregation
    $result = $this->db->get()->result_array();
    return $result;
}


   public function getVerifier()
	{
		$this->db->select('name');
		$this->db->from('roles');
		$this->db->where_in('id', [2, 3, 5, 8]);
		$this->db->order_by('name', 'ASC'); // Optional: sort by role name
		$query = $this->db->get();
		return $query->result_array();
	}


 public function save($post)
{
    $data = [
        'title'                => isset($post['title']) ? $post['title'] : '',
        'task_purpose'         => isset($post['task_purpose']) ? $post['task_purpose'] : '',
        'instructions'         => isset($post['instructions']) ? $post['instructions'] : '',
        'proof_required_text'  => isset($post['proof_required_text']) ? 1 : 0,
        'proof_required_image' => isset($post['proof_required_image']) ? 1 : 0,
        'proof_required_file'  => isset($post['proof_required_file']) ? 1 : 0,
        'expected_time'        => isset($post['expected_time']) ? $post['expected_time'] : '',
        'verifier_role'        => isset($post['verifier_role']) && is_array($post['verifier_role']) ? implode(',', $post['verifier_role']) : null,
    ];

	// Debug the incoming POST data
    error_log("POST executor_stage_labels: " . var_export($post['executor_stage_labels'] ?? 'NOT SET', true));
    error_log("POST verifier_stage_labels: " . var_export($post['verifier_stage_labels'] ?? 'NOT SET', true));

    // Handle executor stage block - with better array handling
    $executor_stage = [
        'title'  => isset($post['executor_stage_title']) ? $post['executor_stage_title'] : '',
        'labels' => $this->process_stage_labels($post['executor_stage_labels'] ?? []),
    ];
    $data['executor_stage'] = json_encode($executor_stage);

    // Handle verifier stage block - with better array handling
    $verifier_stage = [
        'title'  => isset($post['verifier_stage_title']) ? $post['verifier_stage_title'] : '',
        'labels' => $this->process_stage_labels($post['verifier_stage_labels'] ?? []),
    ];
    $data['verifier_stage'] = json_encode($verifier_stage);


	$diagrams = $this->generate_two_mermaid_flows($executor_stage, $verifier_stage);

	$data['executor_mermaid'] = $diagrams['executor_mermaid'];
	$data['verifier_mermaid'] = $diagrams['verifier_mermaid'];

/* 	$mermaid = [
        'executor_mermaid'  => isset($diagrams['executor_mermaid']) ? $diagrams['executor_mermaid'] : '',
        'verifier_mermaid'  => isset($diagrams['verifier_mermaid']) ? $diagrams['verifier_mermaid'] : '',
    ];
    $data['mermaid_diagram'] = json_encode($mermaid); */

    // UPDATE LOGIC
    if (!empty($post['id'])) {
        $existing = $this->db->get_where('sop', ['id' => $post['id']])->row_array();
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->where('id', $post['id']);
        $this->db->update('sop', $data);

        // Change tracker
        $diff = [];
        foreach ($data as $key => $newValue) {
            $oldValue = isset($existing[$key]) ? $existing[$key] : null;
            if ($newValue != $oldValue) {
                $diff[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue
                ];
            }
        }

        if (!empty($diff)) {
            $this->log_action($post['id'], 'update', $diff);
        }

    } else {
        // CREATE NEW SOP
        $data['created_by'] = get_loggedin_user_id();
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert('sop', $data);
        $insert_id = $this->db->insert_id();

        $this->log_action($insert_id, 'create', $data);
    }
}

private function process_stage_labels($labels)
{
    if (!is_array($labels)) {
        return [];
    }

    // Handle both indexed and associative arrays
    $processed = [];
    foreach ($labels as $key => $value) {
        $trimmed = trim($value);
        if (!empty($trimmed)) {
            $processed[] = $trimmed;
        }
    }

    error_log("Processed stage labels: " . var_export($processed, true));
    return $processed;
}

public function generate_two_mermaid_flows($executor_stage, $verifier_stage)
{
    return [
        'executor_mermaid' => $this->generate_single_mermaid($executor_stage['title'], $executor_stage['labels']),
        'verifier_mermaid' => $this->generate_single_mermaid($verifier_stage['title'], $verifier_stage['labels']),
    ];
}

private function generate_single_mermaid($title, $labels)
{
    // Debug: Log the input data
    error_log("Mermaid Debug - Title: " . var_export($title, true));
    error_log("Mermaid Debug - Labels: " . var_export($labels, true));

    $title = trim($title);
    $labels = is_array($labels) ? array_filter(array_map('trim', $labels)) : [];

    // Debug: Log after processing
    error_log("Mermaid Debug - Processed Labels: " . var_export($labels, true));

    $diagram = "graph TD\n";

    if (empty($title)) $title = "Untitled Stage";

    // Sanitize title for mermaid
    $sanitized_title = $this->sanitize_mermaid_text($title);
    $diagram .= "A[\"Start: " . $sanitized_title . "\"]\n";

    $prev = "A";
    $node_counter = 1; // Use numbers instead of ASCII

    foreach ($labels as $index => $label) {
        if (empty($label)) continue; // Skip truly empty labels

        $node = "N" . $node_counter; // N1, N2, N3, etc.
        $sanitized_label = $this->sanitize_mermaid_text($label);

        $diagram .= $node . "[\"" . $sanitized_label . "\"]\n";
        $diagram .= $prev . " --> " . $node . "\n";

        $prev = $node;
        $node_counter++;

        // Debug: Log each step
        error_log("Mermaid Debug - Added node: " . $node . " with label: " . $sanitized_label);
    }

    $endNode = "N" . $node_counter;
    $diagram .= $endNode . "[\"End\"]\n";
    $diagram .= $prev . " --> " . $endNode;

    // Debug: Log final diagram
    error_log("Mermaid Debug - Final diagram: " . $diagram);

    return $diagram;
}

private function sanitize_mermaid_text($text)
{
    // Remove or escape characters that might break mermaid syntax
    $text = str_replace(['"', "'", "\n", "\r", "\\"], ['', '', ' ', ' ', ''], $text);
    // Keep only safe characters for mermaid
    $text = preg_replace('/[^a-zA-Z0-9\s\-\.,!?()&;:]/', '', $text);
    return trim($text);
}


public function log_action($sop_id, $action, $data = [])
{
    $log = [
        'sop_id'      => $sop_id,
        'staff_id'    => get_loggedin_user_id(),
        'action'      => $action,
        'data_snapshot' => !empty($data) ? json_encode($data) : null,
        'created_at'  => date('Y-m-d H:i:s'),
    ];
    $this->db->insert('sop_log', $log);
}

	public function getDetailsById($id)
	{
		return $this->db
			->select('sop.*, staff.name as staff_name, staff.staff_id as staffid')
			->from('sop')
			->join('staff', 'staff.id = sop.created_by', 'left')
			->where('sop.id', $id)
			->get()
			->row_array();
	}

    public function getStaff($id)
    {
        $this->db->select('s.*,s.department as deid,s.designation as desid,staff_department.name as department,staff_designation.name as designation,br.name as institute_name,br.email as institute_email,br.address as institute_address,br.mobileno as institute_mobile_no');
        $this->db->from('staff as s');
        $this->db->join('staff_department', 'staff_department.id = s.department', 'left');
        $this->db->join('staff_designation', 'staff_designation.id = s.designation', 'left');
        $this->db->join('branch as br', 'br.id = s.branch_id', 'left');
        $this->db->where('s.id', $id);
        $query = $this->db->get();
        return $query->row_array();
    }
}