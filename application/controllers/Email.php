<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

defined('BASEPATH') or exit('No direct script access allowed');

class Email extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('email_model');
    }

    public function index()
    {

        $this->data['title'] = translate('Email');
        $this->data['sub_page'] = 'email/index';
        $this->data['main_menu'] = 'email';
        $this->load->view('layout/index', $this->data);

    }

	public function config() {
    if ($_POST) {
        $this->form_validation->set_rules('email', 'Email', 'required');
        $this->form_validation->set_rules('protocol', 'Protocol', 'required');
        $this->form_validation->set_rules('smtp_host', 'SMTP Host', 'required');
        $this->form_validation->set_rules('smtp_user', 'SMTP User', 'required');
        $this->form_validation->set_rules('smtp_pass', 'SMTP Password', 'required');
        $this->form_validation->set_rules('smtp_port', 'SMTP Port', 'required');

        if ($this->form_validation->run() !== false) {
            $data = array(
                'email' => $this->input->post('email'),
                'protocol' => $this->input->post('protocol'),
                'smtp_host' => $this->input->post('smtp_host'),
                'smtp_user' => $this->input->post('smtp_user'),
                'smtp_pass' => $this->input->post('smtp_pass'),
                'smtp_port' => $this->input->post('smtp_port'),
            );

            $this->email_model->save_email_config($data);
            set_alert('success', translate('information_has_been_saved_successfully'));
            redirect(base_url('email/config'));
        }
    }

    $this->data['config'] = $this->email_model->get_email_config();
    $this->data['staff_emails'] = $this->email_model->get_all_user_emails();
    $this->data['title'] = translate('Email Configuration');
    $this->data['sub_page'] = 'email/config';
    $this->data['main_menu'] = 'email';
    $this->load->view('layout/index', $this->data);
}

public function send()
{
    if ($this->input->post('send_email')) {
        $this->form_validation->set_rules('from_email', 'From Email', 'required|valid_email');
        // Adjust validation to accept array of emails
        $this->form_validation->set_rules('to_email[]', 'To Email', 'required');
        $this->form_validation->set_rules('subject', 'Subject', 'required');
        $this->form_validation->set_rules('message', 'Message', 'required');

        if ($this->form_validation->run() !== false) {
            $mail = new PHPMailer(true);

            try {
                // Get email configuration
                $config = $this->email_model->get_email_config();

                // Server settings
                $mail->isSMTP();
                $mail->Host       = $config['smtp_host'];
                $mail->SMTPAuth   = true;
                $mail->Username   = $config['smtp_user'];
                $mail->Password   = $config['smtp_pass'];
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = $config['smtp_port'];

                // Recipients
                $mail->setFrom($this->input->post('from_email'));

                // Add multiple To addresses
                $to_emails = $this->input->post('to_email'); // this is an array
                if (is_array($to_emails)) {
                    foreach ($to_emails as $to_email) {
                        if (filter_var($to_email, FILTER_VALIDATE_EMAIL)) {
                            $mail->addAddress($to_email);
                        }
                    }
                } else {
                    // fallback if single email sent as string
                    if (filter_var($to_emails, FILTER_VALIDATE_EMAIL)) {
                        $mail->addAddress($to_emails);
                    }
                }

                // Optional: add CC addresses (if you want)
                // $cc_emails = $this->input->post('cc_email'); // make sure cc_email[] in form
                // if (is_array($cc_emails)) {
                //     foreach ($cc_emails as $cc_email) {
                //         if (filter_var($cc_email, FILTER_VALIDATE_EMAIL)) {
                //             $mail->addCC($cc_email);
                //         }
                //     }
                // }

                // Content
                $mail->isHTML(true);
                $mail->Subject = $this->input->post('subject');
                $mail->Body    = $this->input->post('message');

                // Attachment (if any)
                if (!empty($_FILES['attachment']['name'])) {
                    $mail->addAttachment($_FILES['attachment']['tmp_name'], $_FILES['attachment']['name']);
                }

                $mail->send();

                $response = array(
                    'status' => 'success',
                    'message' => translate('email_sent_successfully')
                );
            } catch (Exception $e) {
                $response = array(
                    'status' => 'error',
                    'message' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"
                );
            }

            if ($this->input->is_ajax_request()) {
                echo json_encode($response);
                exit;
            } else {
                if ($response['status'] == 'success') {
                    set_alert('success', $response['message']);
                } else {
                    set_alert('error', $response['message']);
                }
                redirect(base_url('email'));
            }
        }
    }

    // If form validation fails or no post data
    $this->data['validation_error'] = true;
    $this->config();  // Load the config page with the form
}

}
