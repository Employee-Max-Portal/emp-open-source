<?php
defined('BASEPATH') or exit('No direct script access allowed');

class PPM extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function objectives_kpi()
    {
        if (!get_permission('ppm', 'is_view')) {
            access_denied();
        }

        $this->data['title'] = 'Objectives & KPI';
        $this->data['sub_page'] = 'ppm/objectives_kpi';
        $this->data['main_menu'] = 'ppm';
        $this->load->view('layout/index', $this->data);
    }

    public function components_behaviour()
    {
        if (!get_permission('ppm', 'is_view')) {
            access_denied();
        }

        $this->data['title'] = 'Components & Behaviour';
        $this->data['sub_page'] = 'ppm/components_behaviour';
        $this->data['main_menu'] = 'ppm';
        $this->load->view('layout/index', $this->data);
    }

    public function development_actions()
    {
        if (!get_permission('ppm', 'is_view')) {
            access_denied();
        }

        $this->data['title'] = 'Development Actions';
        $this->data['sub_page'] = 'ppm/development_actions';
        $this->data['main_menu'] = 'ppm';
        $this->load->view('layout/index', $this->data);
    }

    public function summary()
    {
        if (!get_permission('ppm', 'is_view')) {
            access_denied();
        }

        $this->data['title'] = 'Summary';
        $this->data['sub_page'] = 'ppm/summary';
        $this->data['main_menu'] = 'ppm';
        $this->load->view('layout/index', $this->data);
    }

    public function approval_hierarchy()
    {
        if (!get_permission('ppm', 'is_view')) {
            access_denied();
        }

        $this->data['title'] = 'Approval Hierarchy';
        $this->data['sub_page'] = 'ppm/approval_hierarchy';
        $this->data['main_menu'] = 'ppm';
        $this->load->view('layout/index', $this->data);
    }
}
