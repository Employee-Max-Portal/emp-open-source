<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ajax extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_csrf_token()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'name' => $this->security->get_csrf_token_name(),
                'hash' => $this->security->get_csrf_hash()
            ]));
    }
}