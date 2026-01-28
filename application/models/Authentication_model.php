<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Authentication_model extends MY_Model
{

    // checking login credential
    public function login_credential($username, $password)
    {
        $this->db->select('*');
        $this->db->from('login_credential');
        $this->db->where('username', $username);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows() == 1) {
            $verify_password = $this->app_lib->verify_password($password, $query->row()->password);
            if ($verify_password) {
                return $query->row();
            }
        }
        return false;
    }

   // password forgotten

	public function lose_password($username)
	{
		if (!empty($username)) {
			$this->db->select('*');
			$this->db->from('login_credential');
			$this->db->join('staff', 'staff.id = login_credential.user_id');
			$this->db->group_start();
			$this->db->where('login_credential.username', $username);
			$this->db->or_where('staff.email', $username);
			$this->db->group_end();
			$this->db->limit(1);
			$query = $this->db->get();

			if ($query->num_rows() > 0) {
				$login_credential = $query->row();

				$this->db->select('name, email');
				$this->db->from('staff');
				$this->db->where('id', $login_credential->user_id);
				$this->db->limit(1);
				$user_query = $this->db->get();
				$getUser = $user_query->row();

				$key = hash('sha512', $login_credential->role . $login_credential->username . app_generate_hash());

				$query = $this->db->get_where('reset_password', ['login_credential_id' => $login_credential->id]);
				if ($query->num_rows() > 0) {
					$this->db->where('login_credential_id', $login_credential->id);
					$this->db->delete('reset_password');
				}

				$arrayReset = [
					'key' => $key,
					'login_credential_id' => $login_credential->id,
					'username' => $login_credential->username,
				];

				$this->db->insert('reset_password', $arrayReset);

				// Email Content
				$reset_pass_url = base_url('authentication/pwreset?key=' . $arrayReset['key']);
				$mail_subject = 'Reset your user password of EMP';
				$mail_body = "
				<html>
				  <body style='font-family:Arial, sans-serif; background:#f9f9f9; padding:20px;'>
					<table style='max-width:600px; margin:auto; background:#fff; border-radius:8px; box-shadow:0 0 10px rgba(0,0,0,0.05);'>
					  <tr>
						<td align='center'>
						  <table width='600' cellpadding='0' cellspacing='0' style='background:#ffffff; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.1); padding:30px;'>
							<tr>
							  <td align='center' style='padding-bottom:20px;'>
								<h2 style='color:#333;'>Reset Your <span style='color:#0054a6;'>EMP</span> Password &#128274;</h2>
							  </td>
							</tr>
							<tr>
							  <td style='color:#444; font-size:16px; line-height:1.6;'>
								<p>Dear <strong>{$getUser->name}</strong>,</p>
								<p>We received a request to reset your password for your <strong>EMP</strong> account.</p>

								<p style='margin: 15px 0 5px 0;'><strong>&#128100; Username:</strong> {$arrayReset['username']}</p>

								<p>Please click the button below to set your new password securely:</p>

								<p style='text-align:center; margin:20px 0;'>
								  <a href='{$reset_pass_url}' style='background-color:#0054a6; color:#ffffff; padding:12px 24px; border-radius:6px; text-decoration:none; font-size:16px; display:inline-block;'>Reset Password</a>
								</p>

								<p>If the button doesn’t work, you can also copy and paste the following link into your browser:</p>
								<p><a href='{$reset_pass_url}'>{$reset_pass_url}</a></p>

								<p>This link will expire after a certain time for your security.</p>

								<p style='margin-top:30px;'>Thank you,<br><strong>EMP Team</strong></p>

								<p style='text-align:center; font-size:14px; color:#888; margin-top:40px;'>
								  From <strong>EMP</strong> with <span style='color:#e63946;'>&#10084;&#65039;</span>
								</p>
							  </td>
							</tr>
							<tr>
							  <td align='center' style='padding-top:30px; font-size:12px; color:#999; border-top:1px solid #eee;'>
								<p>&copy; " . date('Y') . " EMP. All rights reserved.</p>
							  </td>
							</tr>
						  </table>
						</td>
					  </tr>
					</table>
				  </body>
				</html>
				";

				$this->db->select('*');
				$this->db->from('email_config');
				$this->db->limit(1);
				$email_query = $this->db->get();
				$getEmail = $email_query->row();

				$recipient = $getUser->email;

				$email_data = [
				'smtp_host'     => $getEmail->smtp_host,
				'smtp_auth'     => true,
				'smtp_user'     => $getEmail->smtp_user,
				'smtp_pass'     => $getEmail->smtp_pass,
				'smtp_secure'   => $getEmail->smtp_encryption,
				'smtp_port'     => $getEmail->smtp_port,
				'from_email'    => $getEmail->email,
				'from_name'     => 'EMP Admin',
				'to_email'      => $recipient,
				'to_name'       => $getUser->name,
				'subject'       => $mail_subject,
				'body'          => $mail_body
				];

				// Send Data to External API via cURL
				$ch = curl_init(base_url('assets/email_project/sems.php'));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($email_data));

				$response = curl_exec($ch);
				curl_close($ch);

				return [
					'name' => $getUser->name,
					'email' => $getUser->email,
					'key' => $key,
					'api_response' => $response
				];
			}
		}
		return false;
	}


     public function urlaliasToBranch($url_alias)
    {
       $get = $this->db->select('id')->where('id', $url_alias)->get('branch')->row_array();
        if (empty($url_alias) || empty($get)) {
            return null;
        } else {
            return $get['id'];
        }
    }

    public function getCurrentDomain()
    {
        $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $url = rtrim($url, '/');
        $domain = parse_url($url, PHP_URL_HOST);
        if (substr($domain, 0, 4) == 'www.') {
            $domain = str_replace('www.', '', $domain);
        }
        $getDomain = $this->db->select('school_id')->get_where('custom_domain', array('status' => 1, 'url' => $domain))->row();
        return $getDomain;
    }

    public function getSchoolDeatls($url_alias = '')
    {
        if (!empty($url_alias)) {
            $this->db->select('fs.facebook_url,fs.twitter_url,fs.linkedin_url,fs.youtube_url,branch.address,branch.school_name');
            $this->db->from('front_cms_setting as fs');
            $this->db->join('branch', 'branch.id = fs.branch_id', 'left');
            $this->db->where('fs.url_alias', $url_alias);
            $get = $this->db->get()->row();
            if (empty($get)) {
                return '';
            } else {
                return $get;
            }
        } else {
            return '';
        }
    }

}
