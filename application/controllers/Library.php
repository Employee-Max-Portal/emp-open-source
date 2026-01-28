<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Library extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('library_model');
    }

    public function index()
    {
        if (is_loggedin()) {
            redirect(base_url('dashboard'));
        } else {
            redirect(base_url(), 'refresh');
        }
    }

	/* book form validation rules */
	protected function book_validation()
	{
		if (is_superadmin_loggedin()) {
			$this->form_validation->set_rules('branch_id', translate('branch'), 'required');
		}
		$this->form_validation->set_rules('book_title', translate('book_title'), 'trim|required');
		$this->form_validation->set_rules('category_id', translate('book_category'), 'trim|required');
	}


    /* category form validation rules */
    protected function category_validation()
    {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
    }

	public function categorized_view()
	{
		if (!get_permission('policy', 'is_view')) {
			access_denied();
		}

		$branch_id = $this->application_model->get_branch_id();

		if (!empty($branch_id)) {
			$this->db->where('branch_id', $branch_id);
		}
		$categories = $this->db->get('policy_category')->result_array();

		$categorized_books = [];

		foreach ($categories as $cat) {
			$this->db->where('category_id', $cat['id']);
			if (!empty($branch_id)) {
				$this->db->where('branch_id', $branch_id);
			}
			$books = $this->db->get('policy')->result_array();

			$categorized_books[] = [
				'category_name' => $cat['name'],
				'books' => $books,
			];
		}

		$this->data['categorized_books'] = $categorized_books;
		$this->data['title'] = translate('company rules & policy');
		$this->data['sub_page'] = 'library/by_category';
		$this->data['main_menu'] = 'library';
		$this->load->view('layout/index', $this->data);
	}

	public function get_view_description()
{
    $policy_id = $this->input->post('id');

    // Fetch policy details with branch and category
    $this->db->select('p.title, p.description, b.name AS branch_name, pc.name AS category_name');
    $this->db->from('policy AS p');
    $this->db->join('branch AS b', 'b.id = p.branch_id', 'left');
    $this->db->join('policy_category AS pc', 'pc.id = p.category_id', 'left');
    $this->db->where('p.id', $policy_id);
    $query = $this->db->get();

    if ($query->num_rows() > 0) {
        $row = $query->row();

        // Build formatted reference string
        $date = date('Ymd'); // or use date('d-m-Y') depending on your need
        $reference = $row->branch_name . '/HR & ADMIN/' . $row->category_name . '/TO-DO/' . $date . '-' . $row->title;

        $this->data['description'] = $row->description;
        $this->data['title'] = $row->title;
        $this->data['reference'] = $reference;
    } else {
        $this->data['description'] = 'No description found.';
        $this->data['title'] = 'No title found.';
        $this->data['reference'] = '';
    }

    // Load the view with fetched details
    $this->load->view('library/get_view_description', $this->data);
}

    // book page
    public function book()
    {
        if (!get_permission('policy', 'is_add')) {
            access_denied();
        }

        if ($_POST) {
            if (!get_permission('policy', 'is_add')) {
                ajax_access_denied();
            }
            $this->book_validation();
            if ($this->form_validation->run() !== false) {
                $post = $this->input->post();
                $this->library_model->book_save($post);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $url = base_url('library/categorized_view');
                $array = array('status' => 'success', 'url' => $url);
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
            exit();
        }
        $branchID = $this->application_model->get_branch_id();

        $this->data['branch_id'] = $branchID;
        $this->data['booklist'] = $this->app_lib->getTable('policy');
        $this->data['title'] = translate('policy');
        $this->data['sub_page'] = 'library/book';
        $this->data['main_menu'] = 'library';
		   $this->data['headerelements'] = array(
			'css' => array(
				'vendor/dropify/css/dropify.min.css',
				'vendor/summernote/summernote.css',
			),
			'js' => array(
				'vendor/dropify/js/dropify.min.js',
				'vendor/summernote/summernote.js',
			),
		);

        $this->load->view('layout/index', $this->data);
    }

    /* file downloader */
    public function documents_download()
    {
        $encrypt_name = urldecode($this->input->get('file'));

        if(preg_match('/^[^.][-a-z0-9_.]+[a-z]$/i', $encrypt_name)) {
            $file_name = $this->db->select('document_file_name')->where('document_enc_name', $encrypt_name)->get('policy')->row()->document_file_name;

            if (!empty($file_name)) {
                $this->load->helper('download');
                force_download($file_name, file_get_contents('uploads/attachments/documents/' . $encrypt_name));
            }
        }
    }

    /* the book information is updated here */
    public function book_edit($id = '')
    {
        if (!get_permission('policy', 'is_edit')) {
            access_denied();
        }

        if ($_POST) {
            $this->book_validation();
            if ($this->form_validation->run() !== false) {
                $post = $this->input->post();
                //save all route information in the database file
                $this->library_model->book_save($post);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $url = base_url('library/categorized_view');
                $array = array('status' => 'success', 'url' => $url, 'error' => '');
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'url' => '', 'error' => $error);
            }
            echo json_encode($array);
            exit();
        }
        $this->data['book'] = $this->app_lib->getTable('policy', array('t.id' => $id), true);
        $this->data['branch_id'] = $this->application_model->get_branch_id();
        $this->data['booklist'] = $this->app_lib->getTable('policy');
        $this->data['title'] = translate('edit_policy');
        $this->data['sub_page'] = 'library/book_edit';
        $this->data['main_menu'] = 'library';
       $this->data['headerelements'] = array(
			'css' => array(
				'vendor/dropify/css/dropify.min.css',
				'vendor/summernote/summernote.css',
			),
			'js' => array(
				'vendor/dropify/js/dropify.min.js',
				'vendor/summernote/summernote.js',
			),
		);

        $this->load->view('layout/index', $this->data);
    }

    public function book_delete($id = '')
    {
        if (get_permission('policy', 'is_delete')) {
            $file = 'uploads/book_cover/' . get_type_name_by_id('book', $id, 'cover');
            if (file_exists($file)) {
                @unlink($file);
            }
            if (!is_superadmin_loggedin()) {
                $this->db->where('branch_id', get_loggedin_branch_id());
            }
            $this->db->where('id', $id);
            $this->db->delete('policy');
        }
    }

    // category information are prepared and stored in the database here
    public function category()
    {
        if (isset($_POST['save'])) {
            if (!get_permission('policy_category', 'is_add')) {
                access_denied();
            }
            $this->category_validation();
            if ($this->form_validation->run() !== false) {
                //save hostel type information in the database file
                $this->library_model->category_save($this->input->post());
                set_alert('success', translate('information_has_been_saved_successfully'));
                redirect(base_url('library/category'));
            }
        }
        $this->data['categorylist'] = $this->app_lib->getTable('policy_category');
        $this->data['title'] = translate('category');
        $this->data['sub_page'] = 'library/category';
        $this->data['main_menu'] = 'library';
        $this->load->view('layout/index', $this->data);
    }

    public function category_edit()
    {
        if ($_POST) {
            if (!get_permission('policy_category', 'is_edit')) {
                ajax_access_denied();
            }
            $this->category_validation();
            if ($this->form_validation->run() !== false) {
                //update book category information in the database file
                $this->library_model->category_save($this->input->post());
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('library/category');
                $array = array('status' => 'success', 'url' => $url, 'error' => '');
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'url' => '', 'error' => $error);
            }
            echo json_encode($array);
        }
    }

    public function category_delete($id)
    {
        if (get_permission('policy_category', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->where('branch_id', get_loggedin_branch_id());
            }
            $this->db->where('id', $id);
            $this->db->delete('policy_category');
        }
    }

}
