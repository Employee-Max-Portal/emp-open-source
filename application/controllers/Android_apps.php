<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Android_apps extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        redirect(base_url('settings/android_apps'), 'refresh');
    }

    public function download($app_name = null, $version = null)
    {
        $app_name = $app_name ?: $this->uri->segment(3);
        $version = $version ?: $this->uri->segment(4);

        // Validate and sanitize input
        if (empty($app_name) || empty($version)) {
            show_404();
            return;
        }

        $app_name = urldecode($app_name);
        $version = urldecode($version);

        // Sanitize input to prevent path traversal
        $app_name = preg_replace('/[^a-zA-Z0-9\s\-_.]/', '', $app_name);
        $version = preg_replace('/[^a-zA-Z0-9\.]/', '', $version);

        if (empty($app_name) || empty($version)) {
            show_404();
            return;
        }

        $this->db->select('file_name, file_path');
        $this->db->from('android_apps');
        $this->db->where('name', $app_name);
        $this->db->where('version', $version);
        $this->db->where('is_active', 1);
        $app = $this->db->get()->row();

        if (!$app) {
            log_message('error', 'App not found in database: ' . $app_name . ' v' . $version);
            show_404();
            return;
        }

        $file_path = FCPATH . $app->file_path;

        // Validate file path is within allowed directory
        $allowed_path = FCPATH . 'uploads/android_apps/';
        $real_file_path = realpath($file_path);
        $real_allowed_path = realpath($allowed_path);

        if (!$real_file_path || strpos($real_file_path, $real_allowed_path) !== 0) {
            log_message('error', 'Invalid file path attempted: ' . $file_path);
            show_404();
            return;
        }

        if (!file_exists($file_path) || !is_readable($file_path)) {
            log_message('error', 'File not found or not readable: ' . $file_path);
            show_404();
            return;
        }

        // Set proper headers for APK download
        header('Content-Type: application/vnd.android.package-archive');
        header('Content-Disposition: attachment; filename="' . basename($app->file_name) . '"');
        header('Content-Length: ' . filesize($file_path));
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: 0');

        // Clear any output buffers
        if (ob_get_level()) {
            ob_end_clean();
        }

        // Read and output file
        readfile($file_path);
        exit;
    }

    public function get_versions($app_name = null)
    {
        $app_name = $app_name ?: $this->uri->segment(3);

        // Validate input
        if (empty($app_name)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'App name is required']);
            return;
        }

        $app_name = urldecode($app_name);
        $app_name = preg_replace('/[^a-zA-Z0-9\s\-_.]/', '', $app_name);

        if (empty($app_name)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid app name']);
            return;
        }

        $this->db->select('name as app_name, version, release_date, file_size, is_latest, release_notes');
        $this->db->from('android_apps');
        $this->db->where('name', $app_name);
        $this->db->where('is_active', 1);
        $this->db->order_by('release_date', 'DESC');
        $versions = $this->db->get()->result();

        header('Content-Type: application/json');
        echo json_encode(['versions' => $versions]);
    }

    public function get_release_notes($app_name = null, $version = null)
    {
        $app_name = $app_name ?: $this->uri->segment(3);
        $version = $version ?: $this->uri->segment(4);

        // Validate input
        if (empty($app_name) || empty($version)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'App name and version are required']);
            return;
        }

        $app_name = urldecode($app_name);
        $version = urldecode($version);

        // Sanitize input
        $app_name = preg_replace('/[^a-zA-Z0-9\s\-_.]/', '', $app_name);
        $version = preg_replace('/[^a-zA-Z0-9\.]/', '', $version);

        if (empty($app_name) || empty($version)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid app name or version']);
            return;
        }

        $this->db->select('release_notes');
        $this->db->from('android_apps');
        $this->db->where('name', $app_name);
        $this->db->where('version', $version);
        $app = $this->db->get()->row();

        header('Content-Type: application/json');
        echo json_encode(['release_notes' => $app ? $app->release_notes : 'No release notes available']);
    }
}