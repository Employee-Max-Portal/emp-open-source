<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/

require_once( BASEPATH .'database/DB.php');
$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$url = rtrim($url, '/');
$domain =  parse_url($url, PHP_URL_HOST);
if (substr($domain, 0, 4) == 'www.') {
	$domain = str_replace('www.', '', $domain);
}
$db =& DB();
$saas_default = false;
if ($db->table_exists("custom_domain")) {
	$getURL = $db->select('count(id) as cid')->get_where('custom_domain', array('status' => 1, 'url' => $domain))->row()->cid;
	if($getURL > 0 ) {
		$route['authentication'] = 'authentication/index/$1';
		$route['forgot'] = 'authentication/forgot/$1';
	} else {
		$saas_default = true;
	}
} else {
	$saas_default = true;
}

$route['(:any)/authentication'] = 'authentication/index/$1';
$route['(:any)/forgot'] = 'authentication/forgot/$1';

$route['dashboard'] = 'dashboard/index';
$route['branch'] = 'branch/index';
$route['attachments'] = 'attachments/index';
$route['event'] = 'event/index';
$route['role'] = 'role/index';
$route['translations'] = 'translations/index';
$route['modules'] = 'modules/index';
$route['backup'] = 'backup/index';
$route['advance_salary'] = 'advance_salary/index';
$route['fund_requisition'] = 'fund_requisition/index';
$route['system_update'] = 'system_update/index';
$route['certificate'] = 'certificate/index';
$route['payroll'] = 'payroll/index';
$route['leave'] = 'leave/index';
$route['award'] = 'award/index';
$route['contact_info'] = 'contact_info/index';
$route['profile'] = 'profile/index';

$route['sop'] = 'sop/index';
$route['rdc'] = 'rdc/index';
$route['notification'] = 'notification/index';
$route['organization_chart'] = 'organization_chart/index';
$route['kpi'] = 'kpi/index';
$route['separation'] = 'separation/index';
$route['probation'] = 'probation/index';
$route['warnings'] = 'warnings/index';
$route['todo'] = 'todo/index';
$route['team_meetings'] = 'team_meetings/index';
$route['promotion'] = 'promotion/apply';
$route['tasks_dashboard'] = 'tasks_dashboard/index';
$route['advisor'] = 'advisor/index';
$route['blacklist'] = 'blacklist/index';
$route['blocked_salary'] = 'blocked_salary/index';

$route['goals'] = 'goals/index';
$route['tracker'] = 'tracker/index';
$route['planner'] = 'planner/index';
$route['team_planner'] = 'team_planner/index';


$route['shipment'] = 'shipment/index';
$route['cashbook'] = 'cashbook/index';
$route['server_request'] = 'server_request/index';
$route['tutorial'] = 'tutorial/index';
$route['training'] = 'training/library';
$route['objectives_kpi'] = 'ppm/objectives_kpi';

$route['email'] = 'email/config';
$route['email/send'] = 'email/send';  // Add this new route
$route['send_emails'] = 'email/send_email';
$route['send_report'] = 'email/send_unit_report';

$route['send_email'] = 'php_mailer/send_email';

$route['authentication'] = 'authentication/index';
$route['install'] = 'install/index';
$route['404_override'] = 'errors';
if ($saas_default) {
	$route['default_controller'] = 'saas_website/index';
}
$route['(:any)'] = 'home/index/$1';
$route['translate_uri_dashes'] = FALSE;


$route['api/add-cashbook-entry'] = 'api/add_cashbook_entry';
$route['api/cashbook_accounts'] = 'api/cashbook_accounts';

if ($saas_default) {
	$route['default_controller'] = 'authentication/index';
}
$route['default_controller'] = 'authentication/index';