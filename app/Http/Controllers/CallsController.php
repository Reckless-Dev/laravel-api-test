<?php 

namespace App\Http\Controllers\Calls;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_EventDateTime;
use Google_Http_Batch;

# e Auth;

use App\Http\Controllers\Calls\Helper as sys_api;
use App\Http\Controllers\Documents\Helper as sys_document;
// use App\Http\Controllers\Comments\Mhelper as sys_comments;
use App\Http\Controllers\Contacts\Helper as sys_contacts;
use App\Http\Controllers\Org\Helper as sys_org;
use App\Http\Controllers\Deals\Helper as sys_deals;
use App\Http\Controllers\Sys\Helper as sys;
use App\Http\Controllers\Users\Helper as sys_users;
use App\Http\Controllers\Workflow\Helper as sys_workflow;
use App\Http\Controllers\Approval\Helper as sys_approval;
use App\Http\Controllers\PushNotifications\Helper as sys_push;
use App\Http\Controllers\Downloads\Helper as sys_api_downloads;
use Validator;
use DB;

class CallsController extends Controller
{
	var $model_contacts_class 	= 'App\Http\Controllers\Contacts\Models\Contacts';
	var $model_leads_class 			= 'App\Http\Controllers\Leads\Models\Leads';
	var $model_deals_class 			= 'App\Http\Controllers\Deals\Models\Deals';
	var $model_org_class 			= 'App\Http\Controllers\Org\Models\Org';
	var $model_teams_class 			= 'App\Http\Controllers\Users_teams\Models\Users_teams';
	var $model_users_class 			= 'App\Http\Controllers\Users\Models\Users';
	var $module = 'Calls';
	var $table_module = 'calls';
	private $users_id;
	private $company_id;
	private $users_type;
	private $gcredentials = '';

	public function __construct()
	{
		$sys = new sys();
		$data = $sys->get_data_company();

		$this->users_id = isset($data['users_id']) ? $data['users_id'] : 0;
		$this->company_id = isset($data['company_id']) ? $data['company_id'] : 0;
		$this->users_type = isset($data['users_type']) ? $data['users_type'] : '';

		$this->client_id 			= Config('setting.client_id');
		$this->client_secret		= Config('setting.client_secret');

		if (env('APP_ENV') == 'local') {
			/* digunakan ketika local saja */
			$this->gcredentials = rtrim(app()->basePath('storage\app\google-credentials.json'));
		}
	}

	# Created By Fitri Mahardika (03/02/2020)
  # 03/02/2020
  # For Listing Calls
	public function index(Request $request)
	{
		# start check module active
		$sys_module = sys_api('Modules');
		$checkPackage = $sys_module->checkPackageActive($this->module, $this->company_id);
		
		if($checkPackage === false) 
		{
			$status = Config("setting.STATUS_USER_ACTIVATON");
			$response = array(
				'status'  => $status, 
				'message' => Config("setting.msg_package_false"),
			);

			return response()->json($response, $status);
		}
		# end check module active

		# GET ALL REQUEST
		$input = $request->all();

		# Define Helper
		$sys 			= new sys();
		$sys_api 	= new sys_api();
		$sys_approval = new sys_approval();

		# Define Variable
		$status     = Config("setting.STATUS_ERROR"); // $status     = "failed";
		$msg        = "error";
		$data_per_page = isset($input['limit']) ? $input['limit'] : Config('setting.pagesize');
		$page = isset($input['page']) ? (int) $input['page'] : 1;

		$company_id 	= $this->company_id;
		$users_id 		= $this->users_id;
		$users_type 	= $this->users_type;
		$table_module = $this->table_module;
		
		# SORTING : Default by date_created DESC
		$order_by 	= !empty($input['order_by']) ? $input['order_by'] : $this->table_module.'.calls_date_start';
		$type_sort 	= !empty($input['type_sort']) ? $input['type_sort'] : 'DESC';
		# END
		$filterApproval 	= !empty($input['filterApproval']) ? $input['filterApproval'] : '';

		# GET DATA
		$list_fields 	= $this->get_list_fields($company_id, $users_id);

		$list_fields_sort = $sys->get_list_fields_sort($this->table_module, $list_fields);

		$criteria 	= array(
													'company_id' 		=> $company_id, 
													'users_id'			=> $users_id, 
													'order_by'			=> $order_by, 
													'type_sort'			=> $type_sort, 
													'data_per_page'	=> $data_per_page, 
													'keyword' 			=> isset($input['keyword']) ? $input['keyword'] : "",
													'filterApproval'	=> $filterApproval,
											);

		if (!empty($input['subaction']) && $input['subaction'] == "search") 
		{
			if (in_array($company_id, Config('setting.company_testing_elastic'))) 
			{
				$elasticData = $sys_api->elasticListing($criteria, $list_fields, $input);
				$criteria = array_merge($elasticData, $criteria);
				$data 			  = $sys_api->listDataIn($criteria, $list_fields, $input);
			}
			else
			{
				$data 			  = $sys_api->listData($criteria, $list_fields, $input);
			}
		}
		else
		{
			$data 			  = $sys_api->listData($criteria, $list_fields, $input);
		}

		$data       	= $sys_api->getOrgIfparentTypeContacts($data);
		// $data         = $sys->gmt_settings($list_fields_sort, $data, $company_id, $table_module);
		$data 				= $sys_api->getTeamsNameByOwner($data, $company_id);
		$data 				= $sys_api->getTeamsNameByActivity($data, $company_id);
		$data 				= $sys_api->getRoles($data, $company_id, $users_id); // Roles edit
		$activeFilter = $sys_api->viewChecked($company_id, $users_id);
		// APPROVAL INFORMATIONS
		$data = $sys_approval->showApprovalInListing($data, $users_id, $this->table_module, $company_id);
		// END APPROVAL INFORMATIONS

		// get parent UUID
		$data 				= $sys_api->getParentUUID($data, $company_id);

		# FILE AWS IN LISTING
		$attachments_aws = array();
		# Data Tags In Listing
		$data_tags = array();

		//	$teams_roles = sys_api('Users_teams')->checkTeamsLeader($this->sess['users_id'], $this->sess['users_type'], $this->sess['company_id']);

		if ( $users_type == 'admin' || $users_type == 'employee' )
		{
			$attachments_aws 		= $sys_api->getDataDocuments($data, $company_id);
			$data_tags		        = $sys_api->getTagsMapData($data, $this->company_id, $this->table_module);
		}
		# END 

		if (countCustom($data['data']) > 0) 
		{
			foreach ($data['data'] as $key => $value) 
			{
				if ($value['billsec'] != '' && $value['billsec'] != null && $value['billsec'] != 'undefined') 
				{
					$data['data'][$key]['billsec'] = gmdate("H:i:s", $value['billsec']);
				}
				else
				{
					$data['data'][$key]['billsec'] = '-';
				}

				if (countCustom($list_fields['listFieldsCustom'])) 
				{
					foreach ($list_fields['listFieldsCustom'] as $key_lfc => $value_lfc) 
					{
						if ( $value_lfc['html'] == 'file' ) 
						{
							$fields_name = $value_lfc[$this->table_module."_custom_fields_name"];

							if ( !empty($value[$value_lfc[$this->table_module."_custom_fields_name"]])) 
							{
								$url_sm = $sys_api->AWS_GetFileCustomFields_sm($value[$value_lfc[$this->table_module."_custom_fields_name"]], $company_id);
								
								$url = $sys_api->AWS_GetFileCustomFields($value[$value_lfc[$this->table_module."_custom_fields_name"]], $company_id);

								$data['data'][$key] = array_add($data['data'][$key], $fields_name.'_url', $url);
								$data['data'][$key] = array_add($data['data'][$key], $fields_name.'_sm_url', $url_sm);
							 } 
							 else 
							 {
							 	$data['data'][$key] = array_add($data['data'][$key], $fields_name.'_url', '');
							 	$data['data'][$key] = array_add($data['data'][$key], $fields_name.'_sm_url', '');
							 }
						}
					}
				}
			}
		}

		$arr_unset = [
			'company_id', 
			'users_id', 
			$this->table_module.'_view_serial_id',
			$this->table_module.'_view_fields_serial_id',
			$this->table_module.'_fields_serial_id',
			$this->table_module.'_view_fields_type',
			$this->table_module.'_fields_view_sorting',
			$this->table_module.'_fields_sorting',
			$this->table_module.'_fields_status',
			$this->table_module.'_fields_quick',
			$this->table_module.'_custom_fields_serial_id',
			$this->table_module.'_custom_fields_sorting',
			$this->table_module.'_custom_fields_status',
		];

		$list_fields_sort = $sys->cleanerVariable($list_fields_sort, $arr_unset);

		$GetUsersInformation = $sys_api->GetUsersInformation($users_id);
		$pbx_domain = '';
		if (  countCustom($GetUsersInformation) > 0 ) 
		{
			if ( $GetUsersInformation['users_phone_type'] == 'webphone' ) 
			{
				$pbx_domain = $GetUsersInformation['users_domain'];
			}
			elseif ( $GetUsersInformation['users_phone_type'] == 'zoiper' ) 
			{
				$pbx_domain = $GetUsersInformation['url_recording'];
			}
		}

		$status = Config("setting.STATUS_OK");
		$response = array(
			'status'  => $status, 
			'message' => 'Ok', 
			'data'    => array(
												  "fields"						=> $list_fields_sort,
												  $this->table_module	=> $data,
												  "activeFilter"			=> $activeFilter,
												  "file_aws" 					=> $attachments_aws,
												  "data_tags"				=> $data_tags,
												  "pbx_domain" 				=> $pbx_domain
												),
		);

		return response()->json($response, $status);
		// $data 			= $sys_api->getRolesEdit($data, $company_id, $this->sess['users_id']); // Roles edit
	}

	/*
	CREATED BY	:	Fitri Mahardika
	FUNCTION : LOAD ATTACHMENT USING AJAX

	run on :
	Route::get('notes/get_attachment', 'NotesController@get_attachment');
	*/
	// for list Attachment
  public function get_attachment(Request $request)
  {			
  		$module_uuid = $request['uuid'];
  		
			$sys_api_documents = new sys_document;
			$sys 			= new sys();
			$sys_api 	= new sys_api();

			$company_id = $this->company_id;
			$users_id 	= $this->users_id;

			$input = $request->all();
			$data_per_page = isset($input['limit']) ? $input['limit'] : Config('setting.pagesize');
			$page = isset($input['page']) ? (int) $input['page'] : 1;

			$criteria 	= array(
													'page'					=> $page,
													'data_per_page'	=> $data_per_page, 
													//'keyword' 			=> isset($input['keyword']) ? $input['keyword'] : ""
											);

			//	$sys_api_documents->getAttachment($data[$this->table_module.'_custom_fields_options']);
			$attachment     = $sys_api_documents->getAttachments($this->table_module, $criteria, $module_uuid, $users_id, $company_id);

			$list_attachment_aws = array();
			if(countCustom($attachment) > 0)
			{                
					foreach ($attachment['data'] as $key => $value) 
					{
						$file_exists = $sys_api_documents->checkFileAws($value['documents_name'], $company_id);
						if($file_exists === TRUE)
						{
								$documents_name = $value['documents_name'];
								$list_attachment_aws[$key]['documents_name'] = $sys_api_documents->getDocumentsFromAws($documents_name, $company_id);
						}
						else
						{
							$list_attachment_aws[$key]['documents_name'] = '';
						}
					}
			}
			// echo json_encode($attachment);

      //$teams_serial_id  = $request->session()->get('teams_serial_id');
      //$roles_delete_attachments = teams_roles_by_name($teams_serial_id, Config('setting.MODULE_DOCUMENTS'), Config('setting.ACL_DELETE'));

			// if (isset($request['index']))
			//  {

			// 		$index = $request['index'];
			// 		$url = urlencode($list_attachment_aws[$index]['documents_name']);
			// 		return response()->json([
			// 		"attachment" => $attachment,
			// 		'list_attachment_aws'            => $list_attachment_aws,
			// 		'url'            => $url,
   		//        'acl_teams_delete' => $roles_delete_attachments,
			// 		]);

			// }
			// else
			// {
			// 		return response()->json([
			// 		"attachment" => $attachment,
			// 		'list_attachment_aws'            => $list_attachment_aws,
   		//        'acl_teams_delete' => $roles_delete_attachments,
			// ]);
			// }
			
				$status = Config("setting.STATUS_OK");
				$response = array(
				'status'  						=> $status, 
				'data'								=> $attachment,
				'list_attachment_aws'	=> $list_attachment_aws,
			);

			return response()->json($response, $status);
	}

	# Created By Pratama Gilang
  # 28-11-2019
  # Duplicate By Fitri Mahardika (03/02/2020)
  # For Get fields
	public function get_list_fields($company_id=0, $users_id=0)
	{
		$sys = new sys();
		$sys_api = new sys_api();
		$moduleViewFieldsChecked 			= $sys_api->moduleViewFieldsChecked($company_id, $users_id); // check module_view_fields for EMPTY or NOT EMPTY

		$moduleViewFieldsCstmChecked 	= $sys_api->moduleViewFieldsCstmChecked($company_id, $users_id);// END push module_custom_values TO $data 

		if (countCustom($moduleViewFieldsChecked) > 0 ) 
		{
			 	$listFields 					= $sys->sys_api_data_type($moduleViewFieldsChecked, $this->table_module, '_fields_input_type');
			 	$listFields 					= $sys_api->GetCoreFieldsChange($listFields, $company_id); // Change label Core @andrian
			 	$listFieldsCustom 		= $sys->sys_api_data_type($moduleViewFieldsCstmChecked, $this->table_module, '_custom_fields_input_type');
			 	
		}else
		{
				$module_view_default 	= $sys_api->moduleViewDefault();
				$form 								= $sys->sys_api_data_type($module_view_default, $this->table_module, '_fields_input_type');
				$listFields						= $sys_api->GetCoreFieldsChange($form, $company_id); // Change label Core @andrian
				$listFieldsCustom 		= array();
		}

		$result['listFields'] 							= $listFields;
		$result['listFieldsCustom'] 				= $listFieldsCustom;
		$result['listFields_count'] 				= countCustom($listFields);
		$result['listFieldsCustom_count'] 	= countCustom($listFieldsCustom);

		return $result;
	}

	# Created By Pratama Gilang
  # 28-11-2019
  # Duplicate By Fitri Mahardika (03/02/2020)
  # For get Form Create
	public function get_form(Request $request)
	{
		# GET ALL REQUEST
		$input = $request->all();

		# Define Helper
		$sys 			= new sys();
		$sys_api 	= new sys_api();

		# Define Variable
		$status     = Config("setting.STATUS_ERROR"); // $status     = "failed";
		$msg        = "error";

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;

		$form = array();

				// FORM CORE
		$form = $sys_api->GetForm($company_id); // Get core fields and custom fields

		foreach ($form as $key => $value) 
		{
			if (isset($value[$this->table_module.'_fields_name']) AND $value[$this->table_module.'_fields_name'] == $this->table_module.'_owner') 
			{
				$form[$key]['content'] = $users_id;	
			}
			if (isset($value[$this->table_module.'_fields_name']) AND $value[$this->table_module.'_fields_name'] == $this->table_module.'_date_start') 
			{
				$form[$key]['content'] = date("Y-m-d H:i:s");	
			}
			if (isset($value[$this->table_module.'_fields_name']) AND $value[$this->table_module.'_fields_name'] == $this->table_module.'_parent_type') 
			{
				if($value['extra'] != [])
				{
					$form[$key]['content'] = $value['extra'][0]['dropdown_options_value'];	
				}
			}
				
			// for default value
			if( !empty($value[$this->table_module.'_fields_default_value']) ) 
			{
				if(isset($value[$this->table_module.'_fields_input_type']) AND $value[$this->table_module.'_fields_input_type'] == 'date') 
        {
					$form[$key]['content'] = date('Y-m-d', strtotime($value[$this->table_module.'_fields_default_value']));
				}else{
					$form[$key]['content'] = [$value[$this->table_module.'_fields_default_value']];	
				}
			}
			if(!empty($value[$this->table_module.'_custom_fields_default_value']) )
			{
        if(isset($value[$this->table_module.'_custom_fields_input_type']) AND $value[$this->table_module.'_custom_fields_input_type'] == 'date') 
        {
          $form[$key]['content'] = date('d-m-Y', strtotime($value[$this->table_module.'_custom_fields_default_value']));
        }else{          
				  $form[$key]['content'] = [$value[$this->table_module.'_custom_fields_default_value']];
        }
			}
		}

		$status = Config("setting.STATUS_OK");
		$response = array(
			'status'  => $status, 
			'message' => 'Ok', 
			'data'    => array_values($form)
		);

		return response()->json($response, $status);
	}

	# Created By Pratama Gilang
	# 28-11-2019
	# Duplicate By Fitri Mahardika (03/02/2020)
	# For Save Data
	public function saveData(Request $request)
	{
		# GET ALL REQUEST
		$input = $request->all();

		# Define Helper
		$sys 			= new sys();
		$sys_api 	= new sys_api();
		$sys_api_documents = new sys_document;

		# Define Variable
		$status     = Config("setting.STATUS_ERROR"); // $status     = "failed";
		$msg        = "error";

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;
		$process_save = array();

		$ajax_form_agent_console = false;
		// UNSET KEY FROM AGENT CONSOLE // PREVENT ERROR SAVING DATA
		if (isset($input['from_agent_console'])) 
		{
			unset($input['from_agent_console']);
			$ajax_form_agent_console = true;
		}

		// Validasi data input untuk owner id
		if(isset($input[$this->table_module.'_owner']) && $input[$this->table_module.'_owner'] != '')
		{
			$cek = $sys->CekOwnerInCompany($input[$this->table_module.'_owner'],$company_id);

			if(isEmpty($cek))
			{
				$input[$this->table_module.'_owner'] = $users_id;
			}
		}
		else
		{
			$input[$this->table_module.'_owner'] = $users_id;
		}

		$check_approval = sys_api('Workflow')->checkApprovalBeforeCreate($users_id, $company_id, $this->module, $this->table_module, $input);
		if(!isEmpty($check_approval))
		{
			$conditions = json_decode($check_approval['workflow_conditions_fields'], true);
			$actions 	= json_decode($check_approval['workflow_actions_fields'], true);

			// check data apakah ada file untuk disimpan ke document
			if(!empty($input['file'])){
				$save_documents_before_approval = $sys_api_documents->save_documents_before_approval($this->table_module, $input, $company_id, $users_id);
				$input['file'] = array(
					'documents_serial_id' => $save_documents_before_approval['documents_serial_id'],
					'documents_uuid' => $save_documents_before_approval['documents_uuid']
				);
			}

			$sys_approval = new sys_approval();
			$approval_name_function='save_data';
			$assign_approval  = $sys_approval->save($users_id, $company_id, $this->module,$this->table_module,0,$check_approval,$input,$approval_name_function,1);
			
			if(countCustom($assign_approval) > 0 )
			{
				// unset $input['file'] ketika data sudah berhasil disimpan ke table approval
				unset($input['file']);
				
				$approval_info_check 				= json_decode(json_encode($check_approval), True);
				$approval_info_workflow_conditions_details=json_decode($approval_info_check['workflow_conditions_details'] , true);
				$approval_info			= json_decode(json_encode($check_approval), True);
				$approval_info['workflow_conditions_fields'] =$conditions;
				$approval_info['workflow_actions_fields'] =$actions;
				$approved_by =json_decode($approval_info['approved_by'] , true);
				$approvedBy = $sys_approval->approvedBy_data_user($approved_by);// take name of user will be approver based workflow
				$approval_info['approved_by'] = $approvedBy;
				$process_save['approval_info'] = $approval_info;
			}
			else
			{
				$status = Config("setting.STATUS_OK");
				$response = array(
					'status'  => $status, 
					'message' => 'Ok', 
					'data'    => [
							$this->table_module => $process_save,
					]
				);
		
				return response()->json($response, $status);
			}
		}
		else
		{

			$process_save = $sys_api->save_data($input, $company_id, $users_id, $ajax_form_agent_console);

			if (!isEmpty($process_save))
			{
				$serial_id = $process_save[$this->table_module.'_serial_id'];

				/*
				* SIMPAN TAGS
				*/
				$data_tags = isset($input[$this->table_module . '_tags']) ? json_decode($input[$this->table_module . '_tags']) : [];
				if (!isEmpty($data_tags)) {
					$sys_api->simpanTag($data_tags, $serial_id, $company_id, $users_id, $this->table_module);
				}				

				#start workflow for condition Create Data
				#use order for set data: Users ID, Company ID, Table Module, Data ID, Sys API
				$sys_workflow = sys_api('Workflow');
				$worflow_start  = $sys_workflow->workflowStartCreate($users_id, $company_id, $this->module, $this->table_module, $serial_id, $sys_api);

				if(countCustom($worflow_start) > 0)
				{
					$checkAndSendNotif = $sys->sendNotificationFromWorkflow($this->table_module, $users_id, $company_id, $process_save, $worflow_start);
					if($checkAndSendNotif)
					{
					$process_save['notifWorkflow'] = true;
					}
				}

				$sys_push = new sys_push();
				$accessToken = getCurrentUserToken();
				$requestSocket = array(
					"platform" => "web",
					"notification_type" => Config("setting.NOTIFICATION_ASSIGNMENT_TYPE"),
					"payload" => array(
						"action" => "create",
						"serial_id" => $serial_id,
						"table_module" => $this->table_module,
					),
					"access_token" => $accessToken,
				);
				$sys_push->emitPushNotification($requestSocket);
			}
		}

		// START : Quick attachment
		if ( !empty($input['file']) ) {
			$quick_documents = $sys_api_documents->quick_documents_create($this->table_module, $input['file'], $process_save, $company_id, $users_id);
			if ($quick_documents) 
			{
				$syslog_document_create  = $sys->savelogAttachment($quick_documents);
					if ( !isEmpty($syslog_document_create) ) 
					{
						$syslog 				= $sys->sys_api_syslog( $syslog_document_create, 'create', 'documents', $quick_documents['documents_serial_id'], $users_id, $company_id);

						$temp_document = array('module_name' => $this->table_module, 'module_serial_id' => $process_save);
						
				$temp_log = $sys->savelogAttachmentModule($this->table_module, $temp_document, 'create', $company_id);
				$syslog 	= $sys->sys_api_syslog( $temp_log, 'update', 'documents', $quick_documents['documents_serial_id'], $users_id, $company_id);
					}
			}
		}
		// END : Quick attachmentS

		$dataDup = $process_save;
		if (!empty($dataDup['calls_unique_id']))
		{
			$dataDup['calls_unique_id'] = "";
		}

		$status = Config("setting.STATUS_OK");
		$response = array(
			'status'  => $status, 
			'message' => 'Ok', 
			'data'    => [
					$this->table_module => $process_save,
			],
			'dataDup'    => [
				$this->table_module => $dataDup,
			]
		);

		return response()->json($response, $status);
	}

	# Created By Pratama Gilang
  # 28-11-2019
  # Duplicate By Fitri Mahardika (03/02/2020)
  # For Get Data Detail
	public function detail(Request $request, $uuid='')
	{
		# start check module active
		$sys_module = sys_api('Modules');
		$checkPackage = $sys_module->checkPackageActive($this->module, $this->company_id);
		
		if($checkPackage === false) 
		{
			$status = Config("setting.STATUS_USER_ACTIVATON");
			$response = array(
				'status'  => $status, 
				'message' => Config("setting.msg_package_false"),
			);

			return response()->json($response, $status);
		}
		# end check module active

		# GET ALL REQUEST
		$input = $request->all();

		# Define Helper
		$sys 			= new sys();
		$sys_api 	= new sys_api();
		$sys_approval = new sys_approval();

		# Define Variable
		$status     = Config("setting.STATUS_ERROR"); // $status     = "failed";
		$message        = "error";

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;

		$data = array();
		$form_fields = array();
		$form_custom_fields = array();
		$data_join_data_custom = array();
		$owner_teams        = array();
		$detail_comments    = array();
		$approval_checking  = array();

		if (!isEmpty($uuid)) 
		{
			$form_fields = $sys_api->GetCoreFields($company_id); //get core fields
			$form_fields = $sys_api->GetCoreFieldsChange($form_fields, $company_id); //get core fields from tbl module_fields_change
			$form_custom_fields = $sys_api->GetCustomFields($company_id); //get custom fields

			$data = $sys_api->GetDetailData($uuid, $company_id);

			if (!isEmpty($data)) 
			{

				$cek_parent = $sys_api->join_sys_rel_edit($data, $company_id);
				$data_join_data_custom = $sys_api->GetDetailData($uuid, $company_id);

				foreach($form_custom_fields as $key =>$value){
					if($form_custom_fields[$key]['calls_custom_fields_input_type'] == 'person')
					{
						if(isset($data['c_'.$form_custom_fields[$key]['calls_custom_fields_name']]))
						{
							if($cek_parent['calls_parent_type'] == 'contacts' || $cek_parent['calls_parent_type'] == 'org')
							{
								$getcontactname = $this->model_contacts_class::select(DB::raw("CONCAT(COALESCE(contacts_first_name, ''),' ', COALESCE(contacts_last_name, '')) as ContactsName,contacts_uuid"))
																									->where('contacts_serial_id', '=', $data['c_'.$form_custom_fields[$key]['calls_custom_fields_name']])
																									->where( 'company_id', '=', $company_id)
																									->where( 'deleted', '=', Config('setting.NOT_DELETED'))
																									->first();
								if(countCustom($getcontactname) > 0)
								{
									$getcontactname->toArray();
									$data_join_data_custom['c_'.$form_custom_fields[$key]['calls_custom_fields_name']] = array('ContactName'=>$getcontactname['ContactsName'],
																																																								'contact_uuid'=>$getcontactname['contacts_uuid']);
								}
							}
							else
							{
								$getcontactname = $this->model_leads_class::select(DB::raw("CONCAT(COALESCE(leads_first_name, ''),' ', COALESCE(leads_last_name, '')) as ContactsName,leads_uuid"))
																									->where('leads_serial_id', '=', $data['c_'.$form_custom_fields[$key]['calls_custom_fields_name']])
																									->where( 'company_id', '=', $company_id)
																									->where( 'deleted', '=', Config('setting.NOT_DELETED'))
																									->first();
								if(countCustom($getcontactname) > 0)
								{
									$getcontactname->toArray();
									$data_join_data_custom['c_'.$form_custom_fields[$key]['calls_custom_fields_name']] = array('ContactName'=>$getcontactname['ContactsName'],
																																																								'contact_uuid'=>$getcontactname['leads_uuid']);
								}
							}
						}
					}
					elseif($form_custom_fields[$key]['calls_custom_fields_input_type'] == 'leads')
					{
						if(isset($data['c_'.$form_custom_fields[$key]['calls_custom_fields_name']]))
						{
							if($cek_parent['calls_parent_type'] == 'contacts' || $cek_parent['calls_parent_type'] == 'org')
							{
								$getleadname = $this->model_leads_class::select(DB::raw("CONCAT(COALESCE(leads_first_name, ''),' ', COALESCE(leads_last_name, '')) as LeadsName,leads_uuid"))
																									->where('leads_serial_id', '=', $data['c_'.$form_custom_fields[$key]['calls_custom_fields_name']])
																									->where( 'company_id', '=', $company_id)
																									->where( 'deleted', '=', Config('setting.NOT_DELETED'))
																									->first();
								if(countCustom($getleadname) > 0)
								{
									$getleadname->toArray();
									$data_join_data_custom['c_'.$form_custom_fields[$key]['calls_custom_fields_name']] = array('LeadName'=>$getleadname['LeadsName'],
																																																								'lead_uuid'=>$getleadname['leads_uuid']);
								}
							}
						}
					}
					elseif($form_custom_fields[$key]['calls_custom_fields_input_type'] == 'deals')
					{
						if(isset($data['c_'.$form_custom_fields[$key]['calls_custom_fields_name']]))
						{
							if($cek_parent['calls_parent_type'] == 'contacts' || $cek_parent['calls_parent_type'] == 'org')
							{
								$getdealname = $this->model_deals_class::select(DB::raw("CONCAT(COALESCE(deals_name, '')) as DealsName,deals_uuid"))
																									->where('deals_serial_id', '=', $data['c_'.$form_custom_fields[$key]['calls_custom_fields_name']])
																									->where( 'company_id', '=', $company_id)
																									->where( 'deleted', '=', Config('setting.NOT_DELETED'))
																									->first();
								if(countCustom($getdealname) > 0)
								{
									$getdealname->toArray();
									$data_join_data_custom['c_'.$form_custom_fields[$key]['calls_custom_fields_name']] = array('DealName'=>$getdealname['DealsName'],
																																																								'deal_uuid'=>$getdealname['deals_uuid']);
								}
							}
						}
					}
					elseif($form_custom_fields[$key]['calls_custom_fields_input_type'] == 'organization')
					{
						if(isset($data['c_'.$form_custom_fields[$key]['calls_custom_fields_name']]))
						{
							if($cek_parent['calls_parent_type'] == 'contacts' || $cek_parent['calls_parent_type'] == 'org')
							{
								$getorganizationname = $this->model_org_class::select(DB::raw("org_name as OrganizationName,org_uuid"))
																									->where('org_serial_id', '=', $data['c_'.$form_custom_fields[$key]['calls_custom_fields_name']])
																									->where( 'company_id', '=', $company_id)
																									->where( 'deleted', '=', Config('setting.NOT_DELETED'))
																									->first();
								if(countCustom($getorganizationname) > 0)
								{
									$getorganizationname->toArray();
									$data_join_data_custom['c_'.$form_custom_fields[$key]['calls_custom_fields_name']] = array('OrganizationName'=>$getorganizationname['OrganizationName'],
																																																								'org_uuid'=>$getorganizationname['org_uuid']);
								}
							}
						}
					}
					elseif($form_custom_fields[$key]['calls_custom_fields_input_type'] == 'teams')
					{
						if(isset($data['c_'.$form_custom_fields[$key]['calls_custom_fields_name']]))
						{
							if($cek_parent['calls_parent_type'] == 'contacts' || $cek_parent['calls_parent_type'] == 'org')
							{
								$getteamname = $this->model_teams_class::select(DB::raw("teams_name as TeamsName,teams_uuid"))
																									->where('teams_serial_id', '=', $data['c_'.$form_custom_fields[$key]['calls_custom_fields_name']])
																									->where( 'company_id', '=', $company_id)
																									->where( 'deleted', '=', Config('setting.NOT_DELETED'))
																									->first();
								if(countCustom($getteamname) > 0)
								{
									$getteamname->toArray();
									$data_join_data_custom['c_'.$form_custom_fields[$key]['calls_custom_fields_name']] = array('TeamName'=>$getteamname['TeamsName'],
																																																								'team_uuid'=>$getteamname['teams_uuid']);
								}
							}
						}
					}
					elseif($form_custom_fields[$key]['calls_custom_fields_input_type'] == 'users')
					{
						if(isset($data['c_'.$form_custom_fields[$key]['calls_custom_fields_name']]))
						{
							if($cek_parent['calls_parent_type'] == 'contacts' || $cek_parent['calls_parent_type'] == 'org')
							{
								$getusername = $this->model_users_class::select(DB::raw("name as UsersName,users_uuid"))
																									->where('id', '=', $data['c_'.$form_custom_fields[$key]['calls_custom_fields_name']])
																									->first();
								if(countCustom($getusername) > 0)
								{
									$getusername->toArray();
									$data_join_data_custom['c_'.$form_custom_fields[$key]['calls_custom_fields_name']] = array('UserName'=>$getusername['UsersName'],
																																																								'user_uuid'=>$getusername['users_uuid']);
								}
							}
						}
					}
				}

				if ($data['deleted'] == Config('setting.DELETED')) 
				{
					$status = Config("setting.STATUS_FORBIDDEN");
					$message = 'Data Has been deleted';	
				}
				else
				{
					$status = Config("setting.STATUS_OK");
					$message = 'Ok';					
				}

				$data_real = $sys_api->GetDataSysRel($data, $company_id);
				$data = $sys_api->GetNameOwner($data_real);
				$data = $sys_api->GetNameModifiedAndCreated($data);
				$data = $sys_api->ChangeDateFormat($data, $company_id);
				$data = $sys_api->getRolesSingle($data, $company_id, $users_id);

				// get data related
				$data = $sys_api->GetRelatedDealsDetail($data);
				$data = $sys_api->GetRelatedProjectsDetail($data);
				$data = $sys_api->GetRelatedIssueDetail($data);
				$data = $sys_api->GetRelatedTicketsDetail($data);

				// get team owner
				$owner_teams = $sys_api->getTeamsNameByOwnerDetail($data, $company_id); 
				
				// CHECK APPROVAL
        $approval_checking = $sys_approval->showApprovalinDetail($data[$this->table_module.'_serial_id'], $this->users_id, $this->table_module, $company_id); // Get core fields and custom fields
				if (isEmpty($approval_checking)) {
					// check apakah users_id sebagai leader
					$approval_checking = $sys_approval->showApprovalInDetailLeader($data[$this->table_module.'_serial_id'], $this->table_module, $company_id, $users_id);
				}
				// END CHECK APPROVAL

				// get comments
				// $detail_comments = $sys_api->GetDetailComments($data[$this->table_module.'_serial_id'], $this->module, $company_id);
				$data['has_pinned'] = $sys->haspinned($uuid,$this->table_module,$this->company_id);
				$data = $sys_api->last_workflow_detail($data, $company_id);

				$GetUsersInformation = $sys_api->GetUsersInformation($users_id);
				$data['pbx_recording']['pbx_domain'] = '';
				if (  countCustom($GetUsersInformation) > 0 ) 
				{
					if ( $GetUsersInformation['users_phone_type'] == 'webphone' ) 
					{
						$data['pbx_recording']['pbx_domain'] = $GetUsersInformation['users_domain'];
					}
					elseif ( $GetUsersInformation['users_phone_type'] == 'zoiper' ) 
					{
						$data['pbx_recording']['pbx_domain'] = $GetUsersInformation['url_recording'];
					}
				}

				# ADD BY GILANG PRATAMA
				# 10 SEPTEMBER 2019
				# CHECK RECORDING
				$data['pbx_recording']['recordingfile'] = '';
				$data['pbx_recording']['check_pbx_recording'] = $sys_api->check_recording(isset($data[$this->table_module.'_serial_id']) ? $data[$this->table_module.'_serial_id'] : 0, $company_id);
				if (!empty($data['recordingfile'])) 
				{
					$data['pbx_recording']['recordingfile'] = urlencode($data['recordingfile']);
				}

				if ($data['billsec'] != '' && $data['billsec'] != null && $data['billsec'] != 'undefined') 
				{
					$data['billsec'] = gmdate("H:i:s", $data['billsec']);
				}
				else
				{
					$data['billsec'] = '-';
				}

				if($data['viewable'] == 'false')
				{
					$status = Config("setting.STATUS_FORBIDDEN");
					$message = 'You Dont Have Permission';
				}
			}
			else
			{
				$status = Config("setting.STATUS_FORBIDDEN");
				$message = 'Data Not Found';
			}
		}
		else
		{
			$status = Config("setting.STATUS_BAD_REQUEST");
			$message = 'Unknown Parameters';
		}

		$response = array(
			'status'  	  => $status, 
			'message'  	  => $message, 
			'data'    	  => $data,
			'fields' 			=> $form_fields,
			'fieldsCustom'=> $form_custom_fields,
			'dataCustom'   => $data_join_data_custom,
			'owner_teams' => $owner_teams,
			'workflow_approval' => $approval_checking,
		);

		return response()->json($response, $status);
	}

	# Created By Pratama Gilang
  # 28-11-2019
  # Duplicate By Fitri Mahardika (03/02/2020)
  # For Delete Data One
	public function delete_one(Request $request, $uuid='')
	{
		# GET ALL REQUEST
		$input = $request->all();

		# Define Helper
		$sys 			= new sys();
		$sys_api 	= new sys_api();

		# Define Variable
		$status     = Config("setting.STATUS_ERROR"); // $status     = "failed";
		$message = 'Ok';	

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;

		$data = array();

		if (!isEmpty($uuid)) 
		{
			$data = $sys_api->GetDetailData($uuid, $company_id);

			if (countCustom($data) > 0) 
			{
				if ( $data['company_id'] != $company_id ) 
				{
					$response = array(
						'status'  	=> Config("setting.STATUS_FORBIDDEN"), 
						'message'   => 'You Do not have Authority'
					);

					return response()->json($response, $status);
				}
				elseif ($data['deleted'] == Config('setting.DELETED')) 
				{
					$status = Config("setting.STATUS_OK");
					$message = 'Data Has been deleted';	
				}
				else
				{
					$process_delete = $sys_api->DeleteOneData($uuid, $users_id);
					$syslog 				= $sys->sys_api_syslog( $data[$this->table_module.'_serial_id'], 'deleteone', $this->table_module, $data[$this->table_module.'_serial_id'], $users_id, $company_id );
					$deltag 				= $sys->delete_tags_by_data_id($data[$this->table_module.'_serial_id'],$company_id,$users_id,$this->table_module);

					if ( $process_delete )
					{

						elasticDelete($data[$this->table_module.'_serial_id'], $company_id, $this->table_module);
						$status = Config("setting.STATUS_OK");
						$message = 'Delete Success';					
					}
					else
					{
						$status = Config("setting.STATUS_OK");
						$message = 'Delete failed';
					}					
				}
			}
			else
			{
				$status = Config("setting.STATUS_OK");
				$message = 'Data Not Found';
			}
		}
		else
		{
			$status = Config("setting.STATUS_BAD_REQUEST");
			$message = 'Unknown Parameters';
		}

		$response = array(
			'status'  	=> $status, 
			'message'  	=> $message
		);

		return response()->json($response, $status);
	}

	# Created By Pratama Gilang
  # 29-11-2019
  # Duplicate By Fitri Mahardika (03/02/2020)
  # For Update Data
	public function updateData(Request $request, $uuid='')
	{
		# GET ALL REQUEST
		$input = $request->all();

		# Define Helper
		$sys 			= new sys();
		$sys_api 	= new sys_api();

		# Define Variable
		$status     = Config("setting.STATUS_ERROR"); // $status     = "failed";
		$msg        = "error";

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;

		$data = array();
		$data_update = array();

		// Validasi data input untuk owner id
		if(isset($input[$this->table_module.'_owner']) && $input[$this->table_module.'_owner'] != '')
		{
			$cek = $sys->CekOwnerInCompany($input[$this->table_module.'_owner'],$company_id);

			if(isEmpty($cek))
			{
				$input[$this->table_module.'_owner'] = $users_id;
			}
		}
		else
		{
			$input[$this->table_module.'_owner'] = $users_id;
		}
		
		if (!isEmpty($uuid)) 
		{
			$data = $sys_api->GetDetailData($uuid, $company_id);
			if (countCustom($data) > 0) 
			{
				if ( $data['company_id'] != $company_id ) 
				{
					$response = array(
						'status'  	=> Config("setting.STATUS_FORBIDDEN"), 
						'message'   => 'You Do not have Authority'
					);

					return response()->json($response, $status);
				}
				elseif ($data['deleted'] == Config('setting.DELETED')) 
				{
					$status = Config("setting.STATUS_OK");
					$message = 'Data Has been deleted';	
				}
				else
				{
					$input[$this->table_module.'_uuid'] = $uuid;
					$old_data 			= $sys_api->GetDataByUuid($input[$this->table_module.'_uuid']);

					$check_approval = sys_api('Workflow')->checkApprovalBeforeUpdate($users_id, $company_id, $this->module, $this->table_module, $input, $old_data);
					if (!isEmpty($check_approval))
					{
						$sys_approval = new sys_approval();
						$checkExist	=	$sys_approval->checkExistApproval($old_data, $company_id, $this->table_module);

						if ( $checkExist )
						{
							$status = Config("setting.STATUS_ERROR");
							$message = 'This '.$this->table_module.' is already submitted for approval';	
							$response = array(
								'status'  	=> $status, 
								'message'  	=> $message, 
							);
					
							return response()->json($response, $status);
						}

						$sys_workflow = sys_api('Workflow');
						$approval_info_check 				= json_decode(json_encode($check_approval), True);
						$approval_info_workflow_conditions_details=json_decode($approval_info_check['workflow_conditions_details'] , true);
						$conditions = json_decode($check_approval['workflow_conditions_fields'], true);
						$actions 	= json_decode($check_approval['workflow_actions_fields'], true);

						$approval_name_function = 'processUpdateData';
						$assign_approval  = $sys_approval->save($users_id, $company_id, $this->module,$this->table_module, $old_data[$this->table_module.'_serial_id'],$check_approval,$input,$approval_name_function,0);
						
						if(countCustom($assign_approval) > 0 )
						{
							$approval_info			= json_decode(json_encode($check_approval), True);
							$approval_info['workflow_conditions_fields'] =$conditions;
							$approval_info['workflow_actions_fields'] =$actions;
							$approved_by =json_decode($approval_info['approved_by'] , true);
							$approvedBy = $sys_approval->approvedBy_data_user($approved_by);// take name of user will be approver based workflow
							$approval_info['approved_by'] = $approvedBy;
							$data_update['approval_info'] = $approval_info;
						}
						else
						{
							$status = Config("setting.STATUS_OK");
							$message = 'Ok';	

							$response = array(
								'status'  	=> $status, 
								'message'  	=> $message, 
								'data'    	=> $data_update
							);
					
							return response()->json($response, $status);
						}
					}
					else
					{
						$data_tags = isset($input[$this->table_module . '_tags']) ? json_decode($input[$this->table_module . '_tags'], true) : [];
						unset($input[$this->table_module . '_tags']);
						$input[$this->table_module.'_uuid'] = $uuid;						
						$data_update = $sys_api->processUpdateData($input, $company_id, $users_id);

						if (!isEmpty($data_update))
						{
							//start workflow for condition Edit Data
							//use order for set data: Users ID, Company ID, Table Module, Data ID, Sys API
							$data_serial_id = $data[$this->table_module.'_serial_id'];


							/*
							* SIMPAN TAGS
							*/
							if (!isEmpty($data_tags)) {
								$newtags = array_filter($data_tags, function($var) {
										return isset($var['tags_map_uuid']) == false;
									});

								$oldtags = array_filter($data_tags, function($var) {
									return isset($var['tags_map_uuid']) == true;
								});

								$newtags = array_values($newtags);
								$oldtags = array_values($oldtags);

								$oldtagsuuid = array_column($oldtags, 'tags_map_uuid');
								if (countCustom($newtags) > 0) {
									$data_tags = $sys_api->simpanTag($newtags, $data_serial_id, $company_id, $users_id, $this->table_module);
									foreach ($oldtagsuuid as $key => $value) {
										array_push($data_tags['tags_map_uuid'], $value);
									}
								}
							}

							/* REMOVE TAGS */
							$sys_api->removeTag($data_tags, $data_serial_id, $company_id, $users_id, $this->table_module);

							// if error run composer dumpautoload
							$sys_workflow = sys_api('Workflow');
							$worflow_start  = $sys_workflow->WorkflowStartEdit($users_id, $company_id, $this->module, $this->table_module, $data_serial_id, $sys_api,$data);

							if(countCustom($worflow_start) > 0)
							{
								$checkAndSendNotif = $sys->sendNotificationFromWorkflow($this->table_module, $users_id, $company_id, $data_update, $worflow_start);
								if($checkAndSendNotif)
								{
								$data_update['notifWorkflow'] = true;
								}
							}

							$sys_push = new sys_push();
							$accessToken = getCurrentUserToken();
							$requestSocket = array(
								"platform" => "web",
								"notification_type" => Config("setting.NOTIFICATION_ASSIGNMENT_TYPE"),
								"payload" => array(
									"old_owner" => !isEmpty($old_data["{$this->table_module}_owner"]) ? $old_data["{$this->table_module}_owner"] : null,
									"action" => "update",
									"serial_id" => $data_serial_id,
									"table_module" => $this->table_module,
								),
								"access_token" => $accessToken,
							);
							$sys_push->emitPushNotification($requestSocket);
						}
					}

					$status = Config("setting.STATUS_OK");
					$message = 'Ok';					
				}
			}
			else
			{
				$status = Config("setting.STATUS_OK");
				$message = 'Data Not Found';
			}
		}
		else
		{
			$status = Config("setting.STATUS_BAD_REQUEST");
			$message = 'Unknown Parameters';
		}

		$response = array(
			'status'  	=> $status, 
			'message'  	=> $message, 
			'data'    	=> $data_update
		);

		return response()->json($response, $status);
	}

	# Created By Prihan Firmanullah
  # 29-11-2019
  # Duplicate By Fitri Mahardika (03/02/2020)
  # For Get List Filter
	public function listFilter(Request $request)
	{
		# GET ALL REQUEST
		$input = $request->all();

		# Define Helper
		$sys 			= new sys();
		$sys_api 	= new sys_api();

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;
		$table_module = $this->table_module;

		$list_filter  = $sys_api->listFilter($company_id, $users_id);
		$activeFilter = $sys_api->viewChecked($company_id, $users_id); // checked filter view active // get filter name where checked

		# Filter -> get all Tags 
		$list_filter_tags = $sys_api->dataTags($company_id, $table_module);
		$status = Config("setting.STATUS_OK");
		$message = 'Ok';
		$response = array(
			'status'  			=> $status, 
			'message'  			=> $message,
			'data'					=> $list_filter,
			'filter_active' => $activeFilter,
			'all_tags' => $list_filter_tags
		);

		return response()->json($response, $status);
	}

	# Created By Prihan Firmanullah
  # 29-11-2019
  # For Save Filter Save
	public function filterSave(Request $request)
	{
		# GET ALL REQUEST
		$input = $request->all();
		# Define Helper
		$sys 			= new sys();
		$sys_api 	= new sys_api();

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;

		$process_save	= $sys_api->FilterSave($input, $company_id, $users_id);
		$message = 'Ok';
		$status = Config("setting.STATUS_OK");
		$response = array(
			'status'  			=> $status, 
			'message'  			=> $message,
			'data'					=> $process_save,
		);

		return response()->json($response, $status);
	}

	# Created By Prihan Firmanullah
  # 29-11-2019
  # For Edit Filter Save
	public function filterEdit(Request $request)
	{
		# GET ALL REQUEST
		$input = $request->all();

		# Define Helper
		$sys 			= new sys();
		$sys_api 	= new sys_api();

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;

		$table_module = $this->table_module; // For simple define variable table module

		$list_fields 		= $this->get_list_fields($company_id, $users_id);
		$list_fields_sort 	= $sys->get_list_fields_sort($this->table_module, $list_fields);

		$moduleFields 		= $sys_api->moduleFieldsChange($company_id);
		$moduleFields		= $sys_api->GetCoreFieldsChange($moduleFields, $company_id);
		$moduleFieldsCustom	= $sys_api->moduleFieldsCustom($company_id); // load fields custom
		$moduleFieldsSort	= $sys->get_module_fields_sort($table_module, $list_fields_sort, $moduleFields, $moduleFieldsCustom);

		$moduleView 		= $sys_api->moduleView($request['view_uuid'], $company_id);
		$moduleViewCriteria = $sys_api->moduleViewCriteria($moduleView[$this->table_module.'_view_serial_id'], $company_id);
		$data = $sys_api->setContentFilter($moduleFieldsSort, $moduleViewCriteria, $company_id, $users_id);
		// print_r($data);
		// print_r($moduleViewCriteria);
		$data = $sys_api->convertOldFilterToV5($data, $company_id, $users_id);

		$status = Config("setting.STATUS_OK");
		$message = 'Ok';
		$response = array(
			'status'  		=> $status, 
			'message'  		=> $message,
			'filter_active' => $moduleView,
			'filter_data' 	=> $data
		);

		return response()->json($response, $status);
	}

	# Created By Prihan Firmanullah
  # 29-11-2019
  # For Update Filter Save
	public function filterUpdate(Request $request)
	{
		# GET ALL REQUEST
		$input = $request->all();

		# Define Helper
		$sys 		= new sys();
		$sys_api 	= new sys_api();

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;
		$input 		= $sys_api->convertFilterUpdate($input, $company_id, $users_id);
		$process 	= $sys_api->FilterUpdate($input, $company_id, $users_id);

		$status = Config("setting.STATUS_OK");
		$message = 'Ok';
		$response = array(
			'status'  			=> $status, 
			'message'  			=> $message,
			'filter_data' 	=> $process
		);

		return response()->json($response, $status);
	}

	# Created By Prihan Firmanullah
  # 29-11-2019
  # For Delete Filter Save
	public function filterDelete(Request $request)
	{
		# GET ALL REQUEST
		$input = $request->all();

		# Define Helper
		$sys 			= new sys();
		$sys_api 	= new sys_api();

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;

		// get view_uuid filter 'You' 
		$activeFilter = $sys_api->defaultFilter($company_id, $users_id); 

		$view_uuid = $activeFilter[$this->table_module.'_view_uuid'];

		$process_checked = $sys_api->filterChecked($view_uuid, $users_id, $company_id);
		
		$delete 	= $sys_api->filterDelete($input['view_uuid']);

		$status = Config("setting.STATUS_OK");
		$message = 'Ok';
		$response = array(
			'status'  			=> $status, 
			'message'  			=> $message,
			'filter_active' => $process_checked,
		);

		return response()->json($response, $status);
	}

	# Created By Pratama Gilang
  # 29-11-2019
  # Duplicate By Fitri Mahardika (03/02/2020)
  # For Get Core Fields
	public function coreFields(Request $request)
	{
		# GET ALL REQUEST
		$input = $request->all();

		# Define Helper
		$sys 			= new sys();
		$sys_api 	= new sys_api();

		# Define Variable
		$status     = Config("setting.STATUS_ERROR"); // $status     = "failed";
		$msg        = "error";
		$dataCustom = array();

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;

		$dataCustom 	= $sys_api->GetAllCoreFields($company_id); //get list custom

		$status = Config("setting.STATUS_OK");
		$response = array(
			'status'  	=> $status, 
			'data'			=> $dataCustom
		);

		return response()->json($response, $status);
	}

	# Created By Pratama Gilang
  # 29-11-2019
  # Duplicate By Fitri Mahardika (03/02/2020)
  # For Get Custom Fields
	public function customFields(Request $request)
	{
		# GET ALL REQUEST
		$input = $request->all();

		# Define Helper
		$sys 			= new sys();
		$sys_api 	= new sys_api();

		# Define Variable
		$status     = Config("setting.STATUS_ERROR"); // $status     = "failed";
		$msg        = "error";
		$dataCustom = array();

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;

		$dataCustom 	= $sys_api->listDataCustomFields($company_id); //get list custom

		$status = Config("setting.STATUS_OK");
		$response = array(
			'status'  	=> $status, 
			'data'			=> $dataCustom
		);

		return response()->json($response, $status);
	}

	# Created By Prihan Firmanullah
  # 29-11-2019
  # Duplicate By Fitri Mahardika (03/02/2020)
  # For Save Filter Save
	public function coreFieldsDetail(Request $request, $serial_id)
	{
		# GET ALL REQUEST
		$input = $request->all();

		# Define Helper
		$sys 			= new sys();
		$sys_api 	= new sys_api();

		# Define Variable
		$status     = Config("setting.STATUS_ERROR"); // $status     = "failed";
		$msg        = "error";
		$dataCustom = array();

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;

		if ($serial_id == 0) 
		{
			$status = Config("setting.STATUS_FORBIDDEN");
			$response = array(
				'status'  	=> $status, 
				'data'			=> array()
			);

			return response()->json($response, $status);
		}

		$data = $sys_api->GetDataCoreFieldsBy($serial_id, $company_id); //get data by serial_id

		if (countCustom($data) > 0) 
		{
			$status = Config("setting.STATUS_OK");
			$response = array(
				'status'  	=> $status, 
				'data'			=> $data
			);			
		}
		else
		{
			$status = Config("setting.STATUS_OK");
			$response = array(
				'status'  	=> $status, 
				'data'			=> array()
			);			
		}

		return response()->json($response, $status);
	}

	# Created By Prihan Firmanullah
  # 29-11-2019
  # Duplicate By Fitri Mahardika (03/02/2020)
  # For Detail Custom Fields
	public function customFieldsDetail(Request $request, $serial_id=0)
	{
		# GET ALL REQUEST
		$input = $request->all();

		# Define Helper
		$sys 			= new sys();
		$sys_api 	= new sys_api();

		# Define Variable
		$status     = Config("setting.STATUS_ERROR"); // $status     = "failed";
		$message    = "error";
		$dataCustom = array();

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;

		if ($serial_id == 0) 
		{
			$status = Config("setting.STATUS_FORBIDDEN");
			$response = array(
				'status'  	=> $status, 
				'data'			=> array()
			);

			return response()->json($response, $status);
		}

		$data = $sys_api->GetDataCutomFieldsBy($serial_id, $company_id); //get data by serial_id

		if (countCustom($data) > 0) 
		{
			if ( $data['company_id'] != $company_id ) 
			{
				$response = array(
					'status'  	=> Config("setting.STATUS_FORBIDDEN"), 
					'message'    	=> 'You Do not have Authority'
				);
			}
			else
			{
				$status = Config("setting.STATUS_OK");
				$response = array(
					'status'  	=> $status, 
					'message'    	=> 'Ok',
					'data'			=> $data
				);	
			}

		}
		else
		{
			$status = Config("setting.STATUS_OK");
			$response = array(
				'status'  	=> $status, 
				'message'    	=> 'Ok',
				'data'			=> array()
			);			
		}

		return response()->json($response, $status);
	}

	# Created By Prihan Firmanullah
  # 29-11-2019
  # Duplicate By Fitri Mahardika (03/02/2020)
  # For Save Save Custom Fields
	public function saveCustomFields(Request $request)
	{
		# GET ALL REQUEST
		$input = $request->all();

		# Define Helper
		$sys 			= new sys();
		$sys_api 	= new sys_api();

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;

		//post of data modules for save
		if ($request[$this->table_module.'_custom_fields_input_type'] == 'groupoption') 
		{
			$process_save = $sys_api->SaveDataCustomGroup($input, $company_id, $users_id); //save post data
		} 
		else 
		{
			$process_save = $sys_api->SaveDataCustom($input, $company_id, $users_id); //save post data
		}

		$listFields = $sys_api->GetListManageFields($company_id); // Get core fields and custom fields

		if (countCustom($process_save) > 0) 
		{
			$status = Config("setting.STATUS_OK");
			$response = array(
				'status'  		=> Config("setting.STATUS_OK"), 
				'message'    	=> 'Ok',
				'data' 				=> $listFields
			);			
		}
		else
		{
			$status = Config("setting.STATUS_ERROR");
			$response = array(
				'status'  		=> Config("setting.STATUS_ERROR"), 
				'message'    	=> 'Sorry, Failed to Process Your Data, Please Try Again'
			);		
		}

		return response()->json($response, $status);
	}

	# Created By Prihan Firmanullah
  # 12-12-2019
  # Duplicate By Fitri Mahardika (03/02/2020)
  # For Update Core Fields
	public function updateCoreFields(Request $request)
	{
		# GET ALL REQUEST
		$input 		= $request->all();
		$process_update = array();
		# Define Helper
		$sys 			= new sys();
		$sys_api 	= new sys_api();

		# Define Variable
		$status     = Config("setting.STATUS_ERROR"); // $status     = "failed";
		$message    = "error";
		$dataCore = array();

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;


		//get class api access for each module
		$change_serial_id 	= (isset($input[$this->table_module.'_fields_change_serial_id']) && $input[$this->table_module.'_fields_change_serial_id'] !='') ? $input[$this->table_module.'_fields_change_serial_id'] : $input[$this->table_module.'_fields_serial_id'];
		$old_data 					= $sys_api->old_data_log_core($change_serial_id, $company_id);
		
		//post of data modules for update
		$process_update = $sys_api->UpdateCoreFields($input, $company_id, $users_id); //update post data
		
		if (countCustom($process_update)>0) 
		{

			if (!isEmpty($old_data))
			{
				if(isset($request['radio_multiple']) && $request['radio_multiple']== 1){
					//create new option core fields masuk ke log
					$input[$this->table_module.'_fields_change_options'] = $process_update[$this->table_module.'fields_change_options'];
				}
				$syslog_action 			= $sys_api->log_update_custom_core($this->table_module, $old_data, $input, $company_id, 'corefields');
			}
			else
			{
				$syslog_action = null;
			}

			if ( !isEmpty($syslog_action) )
			{
				$syslog 				= $sys_api->sys_api_syslog( $syslog_action, 'updatecore', $this->table_module, $process_update[$this->table_module.'_fields_change_serial_id'], $users_id, $company_id );
			}
			
			$status = Config("setting.STATUS_OK");
			$message = 'Data Has been update';				
		}
		else
		{
			$status = Config("setting.STATUS_ERROR");
			$message = 'Unknown Parameters';
		}

		$response = array(
			'status'  	=> $status, 
			'message'  	=> $message, 
			'data'    	=> $process_update
		);

		return response()->json($response, $status);
	}

	# Created By Prihan Firmanullah
  # 12-06-2019
  # Duplicate By Fitri Mahardika (03/02/2020)
  # For Update Custom Fields
	public function updateCustomFields(Request $request)
	{
		# GET ALL REQUEST
		$input 		= $request->all();
		$process_update = array();

		# Define Helper
		$sys 			= new sys();
		$sys_api 	= new sys_api();

		# Define Variable
		$status     = Config("setting.STATUS_ERROR"); // $status     = "failed";
		$message    = "error";
		$dataCustom = array();

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;


		//get class api access for each module
		$custom_fields_serial_id 	= $input[$this->table_module.'_custom_fields_serial_id'];
		$old_data 								= $sys_api->old_data_log_custom($custom_fields_serial_id, $company_id);
		$syslog_action 						= $sys_api->log_update_custom_core($this->table_module, $old_data, $input, $company_id, 'customfields');

		if ( !isEmpty($syslog_action) )
		{
			$syslog 				= $sys_api->sys_api_syslog($syslog_action, 'updatecustom', $this->table_module, $custom_fields_serial_id, $users_id, $company_id );
	  }
		
		
		//post of data modules for update
		if ( isset($input['dropdown_option_group']) ) 
		{
			$process_update = $sys_api->UpdateCustomFieldsGroupOp($input, $company_id, $users_id); //update post data
		} 
		else 
		{
			$process_update = $sys_api->UpdateCustomFields($input, $company_id, $users_id); //update post data
		}

		if (countCustom($process_update)>0) 
		{
			$status = Config("setting.STATUS_OK");
			$message = 'Data Has been update';				
		}
		else
		{
			$status = Config("setting.STATUS_ERROR");
			$message = 'Unknown Parameters';
		}

		$response = array(
			'status'  	=> $status, 
			'message'  	=> $message, 
			'data'    	=> $process_update
		);

		return response()->json($response, $status);
	}

	# Created By Prihan Firmanullah
  # 12-12-2019
  # Duplicate By Fitri Mahardika (03/02/2020)
  # For Delete Custom Fields
	public function DeleteOneCustom($serial_id)
	{
		# GET ALL REQUEST
		$process_delete = array();

		# Define Helper
		$sys 			= new sys();
		$sys_api 	= new sys_api();

		# Define Variable
		$status     = Config("setting.STATUS_ERROR"); // $status     = "failed";
		$message    = "error";
		$dataCustom = array();

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;
		$users_type = $this->users_type;

		/*USER TYPE IS ADMIN*/
		if ( $users_type == "admin" ) 
		{
			//call function delete based on data uuid
			$process_delete = $sys_api->DeleteOneCustomData($serial_id, $company_id);

			$message = "";
			if ( countCustom($process_delete) > 0 )
			{
				$syslog_action = "Delete ".ucfirst($this->table_module)." Custom Fields ".$process_delete[$this->table_module.'_custom_fields_label'];
				$syslog = $sys_api->sys_api_syslog( $syslog_action, 'deletecustom', $this->table_module, $process_delete[$this->table_module.'_custom_fields_serial_id'], $users_id, $company_id );
				//delete success
				$status = Config("setting.STATUS_OK");
				$message = 'Data Has been delete';				
			}
			else
			{
				//delete fail
				$status = Config("setting.STATUS_ERROR");
				$message = 'Unknown Parameters';
			}
		}

		$response = array(
			'status'  	=> $status, 
			'message'  	=> $message, 
			'data'    	=> $process_delete
		);

		return response()->json($response, $status);
	}

	# Created By Gilang Persada
  # 5 des 2019
  # Duplicate By Fitri Mahardika (03/02/2020)
  # For Delete selected
	public function DeleteSelected(Request $request)
	{
		$input = $request->all();
		
		//get class api access for each module
		$sys 			= new sys();
		$sys_api 	= new sys_api();

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;

		$allSelected = $input['allSelected'];
		if (!$allSelected) {
			$getDeleteRolesEachData = $sys_api->FilteringDeleteRolesLeads($input[$this->table_module.'_uuid'], $company_id, $users_id, $this->table_module);
			$input[$this->table_module.'_uuid'] = $getDeleteRolesEachData;
		}
		

		// START : Espesially for selected : One route but different controller
		// $users_type     	= isset(Auth::user()->users_type) ? Auth::user()->users_type : "";
		// $teams_serial_id  = $request->session()->get('teams_serial_id');
  		//   $roles = teams_roles_by_name($teams_serial_id, Config('setting.MODULE_'.strtoupper($this->module)), Config('setting.ACL_DELETE'));

		// $teams_serial_id  = sys_api('Users')->users_teams($company_id, $users_id); have on
		
		// $roles = null;
		// if (countCustom($teams_serial_id) > 0) 
		// {
		// 	foreach ($teams_serial_id as $key => $value) 
		// 	{
	 	//    	$roles = $sys->teams_roles_by_name($value['teams_serial_id'], Config('setting.MODULE_'.strtoupper($this->module)), Config('setting.ACL_DELETE'));
		// 	}
		// }
		
		// $users_type = $this->sess['users_type'];

		// if ($roles == TRUE AND $users_type == "employee") 
  		//   {
  		//       return redirect('/401');
  		//   }
    	// END : Espesially for selected : One route but different controller

    	// START : General Settings -> access delete
		// $settings_access_delete = $sys->settings_check_exists($company_id, Config('setting.SETTINGS_ACCESS_DELETE'));
 	 	//   if ( countCustom($settings_access_delete) > 0 ) 
  		//   {
  		//   	if ( $settings_access_delete['settings_value'] == Config('setting.SETTINGS_ACCESS_DELETE_2') AND $users_type != 'admin' ) 
		  //   	{
		  //   		return redirect('/401');
		  //   	}
		  //   }
    	// END : General Settings -> access delete

		/*
		- SEND LOG -
		*/
		if ($allSelected) {
			$data_per_page = isset($input['limit']) ? $input['limit'] : Config('setting.pagesize');
			$list_fields 	= $this->get_list_fields($company_id, $users_id);
			$criteria 	= array(
				'company_id' 	=> $company_id, 
				'users_id'		=> $users_id, 
				// 'order_by'			=> $order_by, 
				// 'type_sort'			=> $type_sort, 
				'data_per_page'	=> $data_per_page, 
				'keyword' 		=> isset($input['keyword']) ? $input['keyword'] : ""
			);
	
			$data 			= $sys_api->listDataExport($criteria, $list_fields, $input);
			
			if (countCustom($data) < Config('setting.export_maximum')) 
			{
				$input[$this->table_module.'_uuid'] = array_column($data, $this->table_module.'_uuid');
			}
			else
			{
				echo '<pre>';
				print_r('your data export more than 50000');
				exit();
			}
		}

		if (!empty($input[$this->table_module.'_uuid'])) 
		{
			$serial_data_id = $sys_api->GetSerialIdArray($input[$this->table_module.'_uuid']);
			foreach ($input[$this->table_module.'_uuid'] as $key => $value) 
			{
				$data_id = $sys_api->GetDataByUuid($value);
				
				if (isset($data_id[$this->table_module.'_serial_id'])) 
				{
					$syslog = $sys->sys_api_syslog( $data_id[$this->table_module.'_serial_id'], 'deleteselected', $this->table_module, $data_id[$this->table_module.'_serial_id'], $users_id, $company_id);
				}

				//delete attachments from documents map
				// have on
				// $documents_api  	= sys_api('Documents');
				// $attachments_delete = $documents_api->deleteAttachment($this->table_module, $value);
			}

			//call function delete based on selection id
			$process_delete = $sys_api->DeleteSelectedData($input[$this->table_module.'_uuid'], $users_id);

			$deltag 				= $sys->delete_tags_by_data_id($serial_data_id,$company_id,$users_id,$this->table_module);

			$message 	= "";
			$id 			= "";
			$class 		= "";
			$icon 		= "";

			if ( $process_delete )
			{
				elasticDeleteMass($input[$this->table_module.'_uuid'], $company_id, $this->table_module);
				//delete success
				$status = 200;
				$response = array(
					'status'  			=> $status, 
					'message'  			=> "Success Delete Data",
				);

				return response()->json($response, $status);
			}
			else
			{
				//delete fail
				$status = 200;
				$response = array(
					'status'  			=> $status, 
					'message'  			=> "failed Delete Data"
				);
				
				return response()->json($response, $status);
			}
		}
		else
		{
			$status = 200;
			$response = array(
				'status'  			=> $status, 
				'message'  			=> "failed Delete Data"
			);

			return response()->json($response, $status);
		}
	}

	# Created By Gilang Persada
  # 6 des 2019
  # Duplicate By Fitri Mahardika (03/02/2020)
  # For save comments and comments tagged
	public function save_comments(Request $request)
	{
		# GET ALL REQUEST
		$input = $request->all();

		# Define Helper
		$sys 			= new sys();
		$sys_api 	= new sys_api();

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;

		//get serial id activities
		$getSerialId = $sys_api->GetDataSerialId($request['rel_to_module'], $request['uuid']);

		// ADD BY RENDI 11.03.2019 // Replace comment data for user comments using javascript.
		$request['comments_message'] = str_replace(array('<script type="text/javascript">',
														 '<script>',
														 '</script>'),'***',$request['comments_message']);

		//save data to table comment & comment tagged
		$process_save 	= $sys_api->SaveComments($request->all(), $getSerialId, $company_id, $users_id );

		$get_comment_child = $sys_api->get_child($process_save['rel_to_comments'], $company_id);		

		//get users id
		$users_data = $sys_api->users_data($process_save['users_id'] );
		
		
		$data_comments = [
			'comments_serial_id' => $process_save['comments_serial_id'],
			'comments_message'   => $process_save['comments_message'],
			'comments_status'    => $process_save['comments_status'],
			'date_created'       => $process_save['date_created'],
			'rel_serial_id'      => $process_save['rel_serial_id'],
			'rel_to_module'      => $process_save['rel_to_module'],
			'name'               => $users_data['name'],
			'rel_to_comments'    => $process_save['rel_to_comments'],
			'comments_uuid'			 => $process_save['comments_uuid']
 		];

		if (countCustom($process_save) > 0) 
		{
			$response = array(
				'status'  		=> Config("setting.STATUS_OK"), 
				'message'    	=> 'Ok',
				'data'				=> $data_comments,
				'get_comment_child' => $get_comment_child,
			);			
		}
		else
		{
			$response = array(
				'status'  		=> Config("setting.STATUS_ERROR"), 
				'message'    	=> 'Sorry, Failed to Save Your Comments'
			);		
		}

		return response()->json($response);
	}


	# Created By Gilang Persada
	# Duplicate By Fitri Mahardika (03/02/2020)
  	# 6 des 2019
	/**
	 * [delete_comments description]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function delete_comments(Request $request)
  {
  	# GET ALL REQUEST
		$input = $request->all();

		# Define Helper
		$sys 			= new sys();
		$sys_api 	= new sys_api();

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;
		
		$process_delete = $sys_api->delete_data_comments($input, $company_id, $users_id);

		$response = array(
			'status'  		=> Config("setting.STATUS_OK"), 
			'message'    	=> 'Ok',
		);			
		
		return response()->json($response);
  }

	# Created By Gilang Persada
  	# 5 des 2019
  	# Duplicate By Fitri Mahardika (03/02/2020)
  	# For Mass update data
   /**
   * [mass_update description]
   * @param  Request $request [description]
   * @return [type]           [description]
   */
	public function mass_update(Request $request)
	{
		$input = $request->all();
		
		//get class api access for each module
		$sys 			= new sys();
		$sys_api 	= new sys_api();

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;

		// START : Espesially for selected : One route but different controller
		// $users_type     	= isset(Auth::user()->users_type) ? Auth::user()->users_type : ""; have on
		// $teams_serial_id  = $request->session()->get('teams_serial_id'); have on
  		// $roles = teams_roles_by_name($teams_serial_id, Config('setting.MODULE_'.strtoupper($this->module)), Config('setting.ACL_MASS_UPDATE')); have on

		// have on
		// if ($roles == TRUE AND $users_type == "employee") 
  		// {
  		//   return redirect('/401');
  		// }
    	// END : Espesially for selected : One route but different controller

		//proccess to download / export 
		/* 
		* Ketika select all data get uuid sesuai dengan pencarian
		* lintang
		*/
		// SIMPAN TAGS
		$data_tags=[];
		if (!empty($input[$this->table_module . '_tags'])) {
			$data_tags = $input[$this->table_module . '_tags'];
			$uuid = $input[$this->table_module .'_uuid'];
			foreach ($uuid as $key => $value) {
				$module_serial = $sys_api->GetDataByUuid($value);
				$serial_id = $module_serial[$this->table_module .'_serial_id'];
				$sys_api->simpanTag($data_tags, $serial_id, $company_id, $users_id, $this->table_module);
			}
		}
		// END SIMPAN TAGS
		$allSelected = $input['allSelected'];
		if ($input["type_massupdate"] == "alldata") {
			$data_per_page = isset($input['limit']) ? $input['limit'] : Config('setting.pagesize');
			$list_fields 	= $this->get_list_fields($company_id, $users_id);
			$criteria 	= array(
				'company_id' 	=> $company_id, 
				'users_id'		=> $users_id, 
				// 'order_by'			=> $order_by, 
				// 'type_sort'			=> $type_sort, 
				'data_per_page'	=> $data_per_page, 
				'keyword' 		=> isset($input['keyword']) ? $input['keyword'] : ""
			);
			
						$data 			= $sys_api->listDataExport($criteria, $list_fields, $input['query']);
			$input["type_massupdate"] = "";
			
			if (countCustom($data) < Config('setting.export_maximum') && countCustom($data) > 0)
			{
				$input[$this->table_module.'_uuid'] = array_column($data, $this->table_module.'_uuid');
			}
			else
			{
				echo '<pre>';
				print_r('your data export more than 50000');
				exit();
			}
		}

		if (isset($input[$this->table_module.'_uuid'])) {
			$process_mass 	= $sys_api->SaveAndLogMassUpdate($input, $users_id, $company_id);
			
			$message 	= "";
			$id 			= "";
			$class 		= "";
			$icon 		= "";
			if ( $process_mass )
			{
				elasticMassAddData($input[$this->table_module.'_uuid'], $company_id, $this->table_module);
				//mass update success
				$status = 200;
				$response = array(
					'status'  			=> $status, 
					'message'  			=> "Success Mass Update Data",
				);

				return response()->json($response, $status);
			}
			else
			{
				//mass update fail
				$status = 200;
				$response = array(
					'status'  			=> $status, 
					'message'  			=> "failed Mass Update Data",
				);

				return response()->json($response, $status);
			}
			
		}else{
			$status = 200;
			$response = array(
				'status'  			=> $status, 
				'message'  			=> "failed Mass Update Data",
			);

			return response()->json($response, $status);
		}
	}

	# Created By Gilang Persada
  # 9 des 2019
  # Duplicate By Fitri Mahardika (03/02/2020)
  # update By Gomgom (28 july 2021 for approval)
  # For get fields quick update
	public function field_edit(Request $request)
	{
		$input = $request->all();

		//get class api access for each module
		$sys 			= new sys();
		$sys_api 	= new sys_api();

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;

		$table_module = $this->table_module; // For simple define variable table module
		$condition = array();

		// Get first data fields, by fields_name
		$get_fields = $sys_api->get_fields_by_name($input[$this->table_module.'_fields_name'], $company_id);

		if ( countCustom($get_fields) > 0 ) 
		{
			// Get value content / current value (result is string)

			$content 		= $sys_api->get_content($input[$this->table_module.'_uuid'], $input[$this->table_module.'_fields_name'], $company_id);

			// If Condition with Function
			if (!empty($get_fields[$table_module.'_fields_function']) AND $get_fields[$table_module.'_fields_input_type']=='singleoption' ) 
			{
				$extra     = $sys->{$get_fields[$table_module.'_fields_function']}($company_id); // Get data function to sys helper

			}
			// If Condition with Dropdown
			elseif (!empty($get_fields[$table_module.'_fields_options'])) 
			{
				if ( isset($get_fields[$this->table_module.'_fields_change_options']) AND !isEmpty($get_fields[$this->table_module.'_fields_change_options']) ) 
				{
					$extra     = $sys_api->dropdown($get_fields[$table_module.'_fields_change_options'], $company_id); // Get data to table dropdown_options
				}
				else
				{
					$extra     = $sys_api->dropdown($get_fields[$table_module.'_fields_options'], $company_id); // Get data to table dropdown_options
				}
				
			}
      // If Condition with empty core dropdown but not the change
      elseif ( isset($get_fields[$this->table_module.'_fields_change_options']) AND !isEmpty($get_fields[$this->table_module.'_fields_change_options']) ) 
      {
        $extra     = $sys_api->dropdown($get_fields[$table_module.'_fields_change_options'], $company_id); // Get data to table dropdown_options
      }
			// If Condition not function or dropdown
			else
			{
				$extra 	= "";
			}

			if (!empty( $get_fields['condition'])){
				$condition = $get_fields['condition'];
			}
	    $input_type = "";

			$status = 200;
			$response = array(
				'status'  			=> $status, 
				'message'  			=> "Success Get Edit Fields",
				'data' 					=> [
					'table_module'											=> $this->table_module,
					$this->table_module.'_uuid'					=> $input[$this->table_module.'_uuid'],
					$this->table_module.'_fields_name'	=> $input[$this->table_module.'_fields_name'], 
					'content'														=> $content, 
					'html'															=> $get_fields['html'],
					'extra'															=> $extra,
					'input_type'												=> $input_type,
					'readonly'													=> $get_fields[$this->table_module.'_fields_readonly'],
					'condition'		=> $condition,
					//'subaction'													=> 'quickupdate'
				]
			);

			return response()->json($response, $status);
		}
		else
		{
			// get edit fields failed
			$status = 200;
			$response = array(
				'status'  			=> $status, 
				'message'  			=> "Sorry, Failed Get Edit Fields"
			);

			return response()->json($response, $status);
		}
	}

	# Created By Gilang Persada (9 des 2019)
	# Updated By Prihan Frimanullah (11 des 2019)
	# Duplicate By Fitri Mahardika (03/02/2020)
  # For quick update
	public function fields_update(Request $request)
	{
		$input = $request->all();

		//get class api access for each module
		$sys 			= new sys();
		$sys_api 	= new sys_api();

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;
		$process_update	=	array();

		// For Handle : If the input request of the owner is not the same as the numeric, then it is certain that the data is selected owner who is not active.
		if ( $input['module_fields_name'] == $this->table_module.'_owner' )
		{
			if ( isset($input[$input['module_fields_name']]) && !is_numeric($input[$input['module_fields_name']]) )
			{
				exit;
			}
		}

		/* FOR VALIDATION */
		// Validatation Fields
		$validation_fields  = $sys_api->GetForm($company_id); // Get core fields & custom fields
		
		$rules = $sys_api->ValidateFormSingle($validation_fields, $input);		// Change format form fields to validation laravel

		$attribute = $sys_api->ValidateAttributeForm($validation_fields);		// Change format form fields to Attribute validation laravel

		$message 	= $sys_api->validate_msg($rules, $this->table_module);							// Custom & change error message if validation in laravel

		$module_fields_name = isset($input['module_fields_name']) ? $input['module_fields_name'] : '';

		$check_type_input = $sys_api->get_fields_by_name($input['module_fields_name'], $company_id);
		

		if (countCustom($check_type_input) > 0) 
		{
			// This is validation all fields
			$validasi = Validator::make($input, $rules, $message, $attribute);
			// $validasi = $this->validate($input, $rules, $message, $attribute);// Function default validation laravel
			if ($validasi->fails())
			{	
				$tempmessage = $validasi->getMessageBag()->toArray();
				$message = '';
				foreach ($tempmessage as $key => $value) 
				{
					foreach ($value as $key2 => $value2) {
						$message .= $value2; 
					}
				}
				
				$status = 500;
				$response = array(
					'status'  			=> $status, 
					'message'  			=> $message,
				);

				return response()->json($response, $status);
			}		
			/* END FOR VALIDATION */
		}

		$data_update[$input['module_fields_name']] = isset($input[$input['module_fields_name']]) ? $input[$input['module_fields_name']] : '';
		$old_data 			= $sys_api->GetDataByUuid($input[$this->table_module.'_uuid']);

    $check_approval = sys_api('Workflow')->checkApprovalBeforeUpdate($users_id, $company_id, $this->module, $this->table_module, $input,$old_data);

    if (!isEmpty($check_approval))
    {
			$sys_approval = new sys_approval();
			$checkExist	=	$sys_approval->checkExistApproval($old_data, $company_id, $this->table_module);

			if ( $checkExist )
			{
				$status = Config("setting.STATUS_ERROR");
				$message = 'This '.$this->table_module.' is already submitted for approval';	
				$response = array(
					'status'  	=> $status, 
					'message'  	=> $message, 
				);
		
				return response()->json($response, $status);
			}

      $process_update_key=(array_key_first($input));

      $sys_workflow = sys_api('Workflow');
      $approval_info_check 				= json_decode(json_encode($check_approval), True);
      $approval_info_workflow_conditions_details=json_decode($approval_info_check['workflow_conditions_details'] , true);
      $conditions = json_decode($check_approval['workflow_conditions_fields'], true);
      $actions 	= json_decode($check_approval['workflow_actions_fields'], true);

      //if there is change base approval give modal

       foreach ($approval_info_workflow_conditions_details as $key => $value)
      {
       $workflow_conditions_details_key[]=($value[0]);
        if(is_array($value[2])){
            foreach($value[2] as $key => $value){
                $workflow_conditions_details_value[]=$value;
            }
        }else{
        $workflow_conditions_details_value[]=($value[2]);
        }
      }

      if(in_array($process_update_key,$workflow_conditions_details_key))
      {
       $approval_name_function = 'fieldsUpdate';
       $assign_approval  = $sys_approval->save($users_id, $company_id, $this->module,$this->table_module, $old_data[$this->table_module.'_serial_id'],$check_approval,$input,$approval_name_function,0);
       
				if(countCustom($assign_approval) > 0 )
				{
					$approval_info			= json_decode(json_encode($check_approval), True);
					$approval_info['workflow_conditions_fields'] =$conditions;
					$approval_info['workflow_actions_fields'] =$actions;
					$approved_by =json_decode($approval_info['approved_by'] , true);
					$approvedBy = $sys_approval->approvedBy_data_user($approved_by);// take name of user will be approver based workflow
					$approval_info['approved_by'] = $approvedBy;
					$process_update['approval_info'] = $approval_info;

					// quick update success
					$status = 200;
					$response = array(
						'status'  			=> $status,
						'data'  				=> $process_update,
						'message'  			=> "Success Quick Update Data",
					);

					return response()->json($response, $status);
				}
				else
				{
					// quick update success
					$status = 200;
					$response = array(
						'status'  			=> $status,
						'data'  				=> $process_update,
						'message'  			=> "Success Quick Update Data",
					);

					return response()->json($response, $status);
				}
      }
    }
    else
    {
      // FOR LOG
      $data_update[$input['module_fields_name']] = isset($input[$input['module_fields_name']]) ? $input[$input['module_fields_name']] : '';

			if($input['module_fields_name'] == $this->table_module."_date_start")
			{
				$data_update[$this->table_module."_date_start"] = date('Y-m-d H:i:s', strtotime($input[$this->table_module.'_date_start']. "+ 7 hours"));
			}
			else if($input['module_fields_name'] == $this->table_module."_date_end")
			{
				$data_update[$this->table_module."_date_end"] = date('Y-m-d H:i:s', strtotime($input[$this->table_module.'_date_end']. "+ 7 hours"));
			}
			
      $old_data 			= $sys_api->GetDataByUuid($input[$this->table_module.'_uuid']);
      $syslog_action 	= $sys->log_update($old_data, $data_update, $this->table_module, $company_id);

      if ( !isEmpty($syslog_action) )
      {
        $syslog 				= $sys->sys_api_syslog( $syslog_action, 'updateajax', $this->table_module, $old_data[$this->table_module.'_serial_id'], $users_id, $company_id );
      }
      // END FOR LOG

      $process_update 	= $sys_api->fieldsUpdate($input, $company_id, $users_id);

      // workflow harus aktif
      //start workflow for condition Edit Data
      //use order for set data: Users ID, Company ID, Table Module, Data ID, Sys API
      // $worflow_start  = sys_api('Workflow')->WorkflowStartEdit($this->sess['users_id'], $company_id, $this->module, $this->table_module, $data[$this->table_module.'_serial_id'], sys_api($this->module));

      if(countCustom($process_update) > 0 )
      {
        $sys_workflow = sys_api('Workflow');
        $new_data 			= $sys_api->GetDataByUuid($input[$this->table_module.'_uuid']);

        $worflow_start  = $sys_workflow->WorkflowStartEdit($users_id, $company_id, $this->module, $this->table_module, $old_data[$this->table_module.'_serial_id'], $sys_api, $old_data);

        if(countCustom($worflow_start) > 0)
        {
          $checkAndSendNotif = $sys->sendNotificationFromWorkflow($this->table_module, $users_id, $company_id, $new_data, $worflow_start);
          if($checkAndSendNotif)
          {
            $process_update['notifWorkflow'] = true;
          }
        }

				if ($input['module_fields_name'] == $this->table_module.'_owner')
				{
					if ($old_data[$this->table_module.'_owner'] != $data_update[$this->table_module.'_owner'])
					{
						$sys_push = new sys_push();
						$accessToken = getCurrentUserToken();
						$requestSocket = array(
							"platform" => "web",
							"notification_type" => Config("setting.NOTIFICATION_ASSIGNMENT_TYPE"),
							"payload" => array(
								"old_owner" => !isEmpty($old_data["{$this->table_module}_owner"]) ? $old_data["{$this->table_module}_owner"] : null,
								"action" => "update",
								"serial_id" => $old_data[$this->table_module.'_serial_id'],
								"table_module" => $this->table_module,
							),
							"access_token" => $accessToken,
						);
						$sys_push->emitPushNotification($requestSocket);
					}
				}

        // quick update success
        $status = 200;
        $response = array(
          'status'  			=> $status,
          'data'  				=> $process_update,
          'message'  			=> "Success Quick Update Data",
        );

        return response()->json($response, $status);
      }

      else
      {
        // quick update failed
        $status = 500;
        $response = array(
          'status'  			=> $status,
          'message'  			=> "Sorry, Quick Update Failed",
        );

        return response()->json($response, $status);
      }
    }
	}

	# Created By Gilang Persada
  # 9 des 2019
  # Update By Fitri Mahar Dika 17 DES 2019
  # Duplicate By Fitri Mahardika (03/02/2020)
  # For get fields quick update
	public function custom_field_edit(Request $request)
	{
		$input = $request->all();

		//get class api access for each module
		$sys 			= new sys();
		$sys_api 	= new sys_api();

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;

		$get_fields 	= $sys_api->get_custom_fields_by_id($input[$this->table_module.'_custom_fields_name'], $company_id); // Get first data fields, by fields_name

		$getSerialId = $sys_api->GetDataSerialId($this->table_module, $request[$this->table_module.'_uuid']);

		$condition=array();
		// Get serial id by uuid custom fields
		
		if ( countCustom($get_fields) > 0 ) 
		{
				// Get custom values data
				$GetCustomValues = $sys_api->GetCustomValuesEditable($getSerialId, $input[$this->table_module.'_custom_fields_name'], $company_id);

				// Check - if new fields $GetCustomValues == empty, so set $content
				$content 	= '';
				$valuesid = '';
				if ( countCustom($GetCustomValues) > 0 ) 
				{
					// Default content
					if($get_fields['calls_custom_fields_input_type'] == "person")
					{						
							$getcontactname = $this->model_contacts_class::select(DB::raw("CONCAT(COALESCE(contacts_first_name, ''),' ', COALESCE(contacts_last_name, '')) as ContactsName,contacts_serial_id as id"))
																							->where('contacts_serial_id', '=', $GetCustomValues['text'])
																							->where( 'company_id', '=', $company_id)
																							->where( 'deleted', '=', Config('setting.NOT_DELETED'))
																							->first();
							if(countCustom($getcontactname) > 0){
								$getcontactname->toArray();
								$content 		= array();
								$content['name'] 		= $getcontactname['ContactsName'];
								$content['value'] 	= $getcontactname['id'];
							
							}
					}
					elseif($get_fields['calls_custom_fields_input_type'] == "leads")
					{
						$getleadname = $this->model_leads_class::select(DB::raw("CONCAT(COALESCE(leads_first_name, ''),' ', COALESCE(leads_last_name, '')) as LeadsName,leads_serial_id as id"))
																							->where('leads_serial_id', '=', $GetCustomValues['text'])
																							->where( 'company_id', '=', $company_id)
																							->where( 'deleted', '=', Config('setting.NOT_DELETED'))
																							->first();
							if(countCustom($getleadname) > 0){
								$getleadname->toArray();
								$content 		= array();
								$content['name'] 		= $getleadname['LeadsName'];
								$content['value'] 	= $getleadname['id'];
							}
					}
					elseif($get_fields['calls_custom_fields_input_type'] == "deals")
					{
						$getdealname = $this->model_deals_class::select(DB::raw("deals_name as DealsName,deals_serial_id as id"))
																							->where('deals_serial_id', '=', $GetCustomValues['text'])
																							->where( 'company_id', '=', $company_id)
																							->where( 'deleted', '=', Config('setting.NOT_DELETED'))
																							->first();
							if(countCustom($getdealname) > 0){
								$getdealname->toArray();
								$content 		= array();
								$content['name'] 		= $getdealname['DealsName'];
								$content['value'] 	= $getdealname['id'];
							}
					}
					elseif($get_fields['calls_custom_fields_input_type'] == "organization")
					{
						$getorganizationname = $this->model_org_class::select(DB::raw("org_name as OrganizationName,org_serial_id as id"))
																							->where('org_serial_id', '=', $GetCustomValues['text'])
																							->where( 'company_id', '=', $company_id)
																							->where( 'deleted', '=', Config('setting.NOT_DELETED'))
																							->first();
																							
							if(countCustom($getorganizationname) > 0){
								$getorganizationname->toArray();
								$content 		= array();
								$content['name'] 		= $getorganizationname['OrganizationName'];
								$content['value'] 	= $getorganizationname['id'];
							}
					}
					elseif($get_fields['calls_custom_fields_input_type'] == "teams")
					{
						$getteamname = $this->model_teams_class::select(DB::raw("teams_name as TeamsName,teams_serial_id as id"))
																							->where('teams_serial_id', '=', $GetCustomValues['text'])
																							->where( 'company_id', '=', $company_id)
																							->where( 'deleted', '=', Config('setting.NOT_DELETED'))
																							->first();
							if(countCustom($getteamname) > 0){
								$getteamname->toArray();
								$content 		= array();
								$content['name'] 		= $getteamname['TeamsName'];
								$content['value'] 	= $getteamname['id'];
							}
					}
					elseif($get_fields['calls_custom_fields_input_type'] == "users")
					{
						$getusername = $this->model_users_class::select(DB::raw("name as UsersName,id"))
																							->where('id', '=', $GetCustomValues['text'])
																							->first();
							if(countCustom($getusername) > 0){
								$getusername->toArray();
								$content 		= array();
								$content['name'] 		= $getusername['UsersName'];
								$content['value'] 	= $getusername['id'];
							}
							$get_fields['html'] = $get_fields['calls_custom_fields_input_type'];
					}
					else{
						$content 		= $GetCustomValues['text'];
					}
					// Condition content if multiple option AND not empty
					if ( $sys->isJSON($GetCustomValues['text']) ) 
					{
						$content = json_decode($GetCustomValues['text'], TRUE);
					}
				}

				// Set extra
				// if ( $get_fields['html'] == 'person' ) 
				// {
				// 	$extra 	= $sys->InputTypePerson($company_id);
				// }
				// elseif ( $get_fields['html'] == 'organization' ) 
				// {
				// 	$extra 	= $sys->InputTypeOrganization($company_id);
				// }
				// elseif ( $get_fields['html'] == 'user' ) 
				// {
				// 	$extra 	= $sys->InputTypeUser($company_id);
				// }
				if (!isEmpty($get_fields[$this->table_module.'_custom_fields_function']) AND $get_fields[$this->table_module.'_custom_fields_input_type']=='singleoption' ) 
				{
		      $extra     = $get_fields[$this->table_module.'_custom_fields_function']($company_id);
		    }
		  	elseif (!isEmpty($get_fields[$this->table_module.'_custom_fields_options'])) 
		  	{
		      $extra   = $sys_api->dropdown($get_fields[$this->table_module.'_custom_fields_options'], $company_id);
		    }
		  	else
		  	{
		      $extra  = "";
		    }

				if (!empty( $get_fields['condition'])){
					$condition = $get_fields['condition'];
				}

				$input_type = "";

				$status = 200;
			
				$response = array(
				'status'  			=> $status, 
				'message'  			=> "Success Get Edit Fields",
				'data' 					=> ['table_module'																=> $this->table_module,
														$this->table_module.'_uuid'										=> $input[$this->table_module.'_uuid'],
														$this->table_module.'_custom_fields_name'			=> $input[$this->table_module.'_custom_fields_name'],
														// $this->table_module.'_custom_field_serial_id' => isset($input[$this->table_module.'_custom_field_serial_id'])?,
														'content'																			=> $content, 
														'html'																				=> $get_fields['html'],
														'extra'																				=> $extra,
														'input_type'																	=> $input_type,
														'valuesid'																		=> $valuesid,
														'readonly'																		=> $get_fields[$this->table_module.'_custom_fields_readonly'],
														'condition'																		=> $condition,
													]
			);

					return response()->json($response, $status);

		}
		else
		{
			$status = 200;
			$response = array(
				'status'  			=> $status, 
				'message'  			=> "Sorry, Failed Get Edit Fields"
			);

			return response()->json($response, $status);
		}
	}

	# Created By Fitri Mahar Dika 17 DES 2019
	# Duplicate By Fitri Mahardika (03/02/2020)
  # For get fields quick update CUSTOM
	public function fields_update_custom(Request $request)
	{
		
		$sys_api 	= new sys_api();
		$sys 			= new sys();
		$input = $request->all();

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;
		$process_update	=	array();

		$getSerialId = $sys_api->GetDataSerialId($this->table_module, $request[$this->table_module.'_uuid']);
		// Get serial id by uuid custom fields

			//validation
		/* FOR VALIDATION */
		// Validatation Fields
		$validation_fields  = $sys_api->GetForm($company_id); // Get core fields & custom fields

		$rules = $sys_api->ValidateFormSingle($validation_fields, $input);		// Change format form fields to validation laravel

		$attribute = $sys_api->ValidateAttributeForm($validation_fields);		// Change format form fields to Attribute validation laravel
		$message 	= $sys_api->validate_msg($rules, $this->table_module);							// Custom & change error message if validation in laravel

		// This is validation all fields
		$custom = array();
		foreach($input as $key => $value){
			if($key == $this->table_module."_custom_fields_name")
			{
				if(isset($input[$value]))
				{
					$custom['custom_'.$value] = $input[$value];
				}
			}else{
				$custom[$key] = $value;
			}
		}

		// This is validation all fields
		$validasi = Validator::make($custom, $rules, $message, $attribute);
		// $validasi = $this->validate($input, $rules, $message, $attribute);// Function default validation laravel

		if ($validasi->fails())
		{	
			$tempmessage = $validasi->getMessageBag()->toArray();
			$message = '';
			foreach ($tempmessage as $key => $value) 
			{
				foreach ($value as $key2 => $value2) {
					$message .= $value2; 
				}
			}
			
			$status = 500;
			$response = array(
				'status'  			=> $status, 
				'message'  			=> $message,
			);

			return response()->json($response, $status);
		}		
		/* END FOR VALIDATION */

		// serialid 				= id tasks_serial_id
		// fieldid 					= id tasks_custom_field_serial_id
		// valuesid 				= id tasks_custom_values_serial_id

		$data 			= $sys_api->custom_values_origin($getSerialId, $input[$this->table_module.'_custom_fields_name'], $company_id);
		$uuid 			= $sys_api->get_uuid_by_serial_id($getSerialId, $company_id);
		$dataBefore	= $sys_api->GetDetailData($uuid, $company_id);

    $check_approval = sys_api('Workflow')->checkApprovalBeforeUpdateCustom($users_id, $company_id, $this->module, $this->table_module, $input, $dataBefore);
    if (!isEmpty($check_approval))
    {
			$sys_approval = new sys_approval();
			$checkExist	=	$sys_approval->checkExistApproval($dataBefore, $company_id, $this->table_module);

			if ( $checkExist )
			{
				$status = Config("setting.STATUS_ERROR");
				$message = 'This '.$this->table_module.' is already submitted for approval';	
				$response = array(
					'status'  	=> $status, 
					'message'  	=> $message, 
				);
		
				return response()->json($response, $status);
			}

        $process_update_value=$input[(array_key_first($input))];
        $sys_approval = new sys_approval();
        $sys_workflow = sys_api('Workflow');
        $approval_info_check 				= json_decode(json_encode($check_approval), True);
        $approval_info_workflow_conditions_details=json_decode($approval_info_check['workflow_conditions_details'] , true);
        $conditions = json_decode($check_approval['workflow_conditions_fields'], true);
        $actions 	= json_decode($check_approval['workflow_actions_fields'], true);

        //if there is change base approval give modal
				foreach ($approval_info_workflow_conditions_details as $key => $value)
        {
         $workflow_conditions_details_key[]=($value[0]);
          if(is_array($value[2])){
              foreach($value[2] as $key => $value){
                  $workflow_conditions_details_value[]=$value;
              }
          }else{
          $workflow_conditions_details_value[]=($value[2]);
          }
        }

        if($process_update_value == $workflow_conditions_details_value || in_array($process_update_value, $workflow_conditions_details_value)){
					$approval_name_function = 'fieldsUpdateCustom';
					$assign_approval  = $sys_approval->save($users_id, $company_id, $this->module,$this->table_module, $dataBefore[$this->table_module.'_serial_id'],$check_approval,$input,$approval_name_function,0);
           
					if(countCustom($assign_approval) > 0 )
					{
						$approval_info			= json_decode(json_encode($check_approval), True);
						$approval_info['workflow_conditions_fields'] =$conditions;
						$approval_info['workflow_actions_fields'] =$actions;
						$approved_by =json_decode($approval_info['approved_by'] , true);
						$approvedBy = $sys_approval->approvedBy_data_user($approved_by);// take name of user will be approver based workflow
						$approval_info['approved_by'] = $approvedBy;
						$process_update['approval_info'] = $approval_info;
					}
					else
					{
						$status = Config("setting.STATUS_OK");
						$message = 'Success Quick Update Custom Data';
				
						$response = array(
							'status'  	=> $status,
							'data'    	=> $process_update,
							'message'  	=> $message
							);
			
						return response()->json($response, $status);
					}
				}

        if (countCustom($process_update)>0)
        {
          $status = Config("setting.STATUS_OK");
          $message = 'Success Quick Update Custom Data';
        }
        else
        {
          $status = Config("setting.STATUS_ERROR");
          $message = 'Sorry, Quick Update Custom Failed';
        }
        $response = array(
        'status'  	=> $status,
        'data'    	=> $process_update,
        'message'  	=> $message
        );

        return response()->json($response, $status);
     }
    else
    {
      // FOR LOG
      // $data 					= $sys_api->GetCustomValuesById($input['valuesid']);
      $data 			= $sys_api->custom_values_origin($getSerialId, $input[$this->table_module.'_custom_fields_name'], $company_id);

      $uuid 			= $sys_api->get_uuid_by_serial_id($getSerialId, $company_id);

      $old_data 	= $sys_api->old_data_log($uuid, $company_id);

      $input_syslog_action = [];
      $custom_field_name = $input[$this->table_module.'_custom_fields_name'];
      $id_custom = $sys_api->checkCustomFieldsIdByName($custom_field_name,$company_id);
      $input_syslog_action[$this->table_module.'_custom_values_text'][$id_custom] =  isset($input[$input[$this->table_module.'_custom_fields_name']]) ? $input[$input[$this->table_module.'_custom_fields_name']] : "";
      $syslog_action 	= $sys->log_update($old_data, $input_syslog_action, $this->table_module, $company_id);

      // If empty syslog action, dont create or save in table syslog
      if ( !isEmpty($syslog_action) )
      {
        $syslog 				= $sys->sys_api_syslog( $syslog_action, 'updateajax', $this->table_module, $getSerialId, $users_id, $company_id );
      }
      // END FOR LOG

      $process_update 	= $sys_api->fieldsUpdateCustom($input, $company_id, $users_id, $getSerialId);

      //post of data modules for update
      if (countCustom($process_update)>0)
      {
        $new_data 	= $sys_api->old_data_log($uuid, $company_id);

        $sys_workflow = sys_api('Workflow');
        $worflow_start  = $sys_workflow->WorkflowStartEdit($users_id, $company_id, $this->module, $this->table_module, $getSerialId, $sys_api,$dataBefore);

        if(countCustom($worflow_start) > 0)
        {
          $checkAndSendNotif = $sys->sendNotificationFromWorkflow($this->table_module, $users_id, $company_id, $new_data, $worflow_start);
        }

        $status = Config("setting.STATUS_OK");
        $message = 'Success Quick Update Custom Data';
      }
      else
      {
        $status = Config("setting.STATUS_ERROR");
        $message = 'Sorry, Quick Update Custom Failed';
      }

      $response = array(
        'status'  	=> $status,
        'data'    	=> $process_update,
        'message'  	=> $message
      );

      return response()->json($response, $status);
    }
	}

	# Created By Pratama Gilang
  # 9 des 2019
  # Duplicate By Fitri Mahardika (03/02/2020)
  # For edit data and get value content
  # Output like getForm with content
	public function editData(Request $request, $uuid='')
	{
		# GET ALL REQUEST
		$input = $request->all();

		# Define Helper
		$sys 			= new sys();
		$sys_api 	= new sys_api();

		# Define Variable
		$status     = Config("setting.STATUS_ERROR"); // $status     = "failed";
		$msg        = "error";

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;

		$result = array();
	
		if (!isEmpty($uuid)) 
		{
			$data = $sys_api->GetDetailData($uuid, $company_id);
			
			if (countCustom($data) > 0) 
			{
				if ($data['deleted'] == Config('setting.DELETED')) 
				{
					$status = Config("setting.STATUS_USER_DEL");
					$message = 'Data Has been deleted';	
				}
				else
				{
					$status = Config("setting.STATUS_OK");
					$message = 'Ok';		

					// get related module
					$data = $sys_api->join_sys_rel_edit($data, $company_id);				
					$result = $sys_api->getDataEdit($data, $company_id);

					// Option Deals
					$option_related_deals		 = $sys->sys_api_SelectRelatedDeals($data[$this->table_module.'_parent_type'], $data[$this->table_module.'_parent_id_id'], $users_id, $company_id, $data['deals_serial_id']);
					
					// Option Projects
					$option_related_projects = $sys->sys_api_SelectRelatedProjects($data[$this->table_module.'_parent_type'], $data[$this->table_module.'_parent_id_id'], $users_id, $company_id, $data['deals_serial_id']);

					// Option Issue
					$option_related_issue		 = $sys->sys_api_SelectRelatedIssue($data[$this->table_module.'_parent_type'], $data[$this->table_module.'_parent_id_id'], $users_id, $company_id, $data['deals_serial_id']);

					// Option Tickets
					$option_related_tickets	 = $sys->sys_api_SelectRelatedTickets($data[$this->table_module.'_parent_type'], $data[$this->table_module.'_parent_id_id'], $users_id, $company_id, $data['deals_serial_id']);

					foreach ($result as $key => $value) 
					{
						if ( isset($value[$this->table_module.'_fields_name']) AND $value[$this->table_module.'_fields_name'] == 'deals_serial_id' ) 
						{
							$result[$key]['extra']	= $option_related_deals;
						}
						elseif ( isset($value[$this->table_module.'_fields_name']) AND $value[$this->table_module.'_fields_name'] == 'projects_serial_id' ) 
						{
							$result[$key]['extra']	= $option_related_projects;
						}
						elseif ( isset($value[$this->table_module.'_fields_name']) AND $value[$this->table_module.'_fields_name'] == 'issue_serial_id' ) 
						{
							$result[$key]['extra']	= $option_related_issue;
						}
						elseif ( isset($value[$this->table_module.'_fields_name']) AND $value[$this->table_module.'_fields_name'] == 'tickets_serial_id' ) 
						{
							$result[$key]['extra']	= $option_related_tickets;
						}
					}
				}
			}
			else
			{
				$status = Config("setting.STATUS_BAD_REQUEST");
				$message = 'Data Not Found';
			}
		}
		else
		{
			$status = Config("setting.STATUS_BAD_REQUEST");
			$message = 'Unknown Parameters';
		}

		$response = array(
			'status'  	=> $status, 
			'message'  	=> $message,
			'data'  		=> array_values($result),
		);

		return response()->json($response, $status);
	}

	# Created By Gilang Persada
  # 20 des 2019
  # Duplicate By Fitri Mahardika (03/02/2020)
	public function duplicateData(Request $request, $uuid='')
	{
		# GET ALL REQUEST
		$input = $request->all();

		# Define Helper
		$sys 			= new sys();
		$sys_api 	= new sys_api();

		# Define Variable
		$status     = Config("setting.STATUS_ERROR"); // $status     = "failed";
		$msg        = "error";

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;

		$result = array();
	
		if (!isEmpty($uuid)) 
		{
			$data = $sys_api->GetDetailData($uuid, $company_id);
			
			if (countCustom($data) > 0) 
			{
				$data['calls_unique_id'] = "";
				if ($data['deleted'] == Config('setting.DELETED')) 
				{
					$status = Config("setting.STATUS_USER_DEL");
					$message = 'Data Has been deleted';	
				}
				else
				{
					$status = Config("setting.STATUS_OK");
					$message = 'Ok';					
					
					// get related module
					$data = $sys_api->join_sys_rel_edit($data, $company_id);					
					$result = $sys_api->getDataEdit($data, $company_id);

					// Option Deals
					$option_related_deals		 = $sys->sys_api_SelectRelatedDeals($data[$this->table_module.'_parent_type'], $data[$this->table_module.'_parent_id_id'], $users_id, $company_id, $data['deals_serial_id']);
					
					// Option Projects
					$option_related_projects = $sys->sys_api_SelectRelatedProjects($data[$this->table_module.'_parent_type'], $data[$this->table_module.'_parent_id_id'], $users_id, $company_id, $data['deals_serial_id']);

					// Option Issue
					$option_related_issue		 = $sys->sys_api_SelectRelatedIssue($data[$this->table_module.'_parent_type'], $data[$this->table_module.'_parent_id_id'], $users_id, $company_id, $data['deals_serial_id']);

					// Option Tickets
					$option_related_tickets	= $sys->sys_api_SelectRelatedTickets($data[$this->table_module.'_parent_type'], $data[$this->table_module.'_parent_id_id'], $users_id, $company_id, $data['deals_serial_id']);

					foreach ($result as $key => $value) 
					{
						if ( isset($value[$this->table_module.'_fields_name']) AND $value[$this->table_module.'_fields_name'] == 'deals_serial_id' ) 
						{
							$result[$key]['extra']	= $option_related_deals;
						}
						elseif ( isset($value[$this->table_module.'_fields_name']) AND $value[$this->table_module.'_fields_name'] == 'projects_serial_id' ) 
						{
							$result[$key]['extra']	= $option_related_projects;
						}
						elseif ( isset($value[$this->table_module.'_fields_name']) AND $value[$this->table_module.'_fields_name'] == 'issue_serial_id' ) 
						{
							$result[$key]['extra']	= $option_related_issue;
						}
						elseif ( isset($value[$this->table_module.'_fields_name']) AND $value[$this->table_module.'_fields_name'] == 'tickets_serial_id' ) 
						{
							$result[$key]['extra'] = $option_related_tickets;
						}
					}
				}
			}
			else
			{
				$status = Config("setting.STATUS_BAD_REQUEST");
				$message = 'Data Not Found';
			}
		}
		else
		{
			$status = Config("setting.STATUS_BAD_REQUEST");
			$message = 'Unknown Parameters';
		}

		$response = array(
			'status'  	=> $status, 
			'message'  	=> $message,
			'data'  		=> array_values($result),
		);

		return response()->json($response, $status);
	}

	# Created By Gilang Persada
  # 17 des 2019
  # Duplicate By Fitri Mahardika (03/02/2020)
	public function columns_save(Request $request)
	{
		$input = $request->all();

		//get class api access for each module
		$sys 			= new sys();
		$sys_api 	= new sys_api();

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;

		$table_module = $this->table_module; // For simple define variable table module

		$process_save = $sys_api->ColumnsSave($input, $company_id, $users_id);

		$status = 200;
		$response = array(
			'status'  			=> $status, 
			'message'  			=> "Success update columns",
		);

		return response()->json($response, $status);
	}

	# Created By Pratama Gilang
  # 28-11-2019
  # Duplicate By Fitri Mahardika (03/02/2020)
  # For get List Core And Custom
	public function get_list_manage_fields(Request $request)
	{
		# GET ALL REQUEST
		$input = $request->all();

		# Define Helper
		$sys 			= new sys();
		$sys_api 	= new sys_api();

		# Define Variable
		$status     = Config("setting.STATUS_ERROR"); // $status     = "failed";
		$msg        = "error";

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;
		$listFields = array();

				// FORM CORE
		$listFields = $sys_api->GetListManageFields($company_id); // Get core fields and custom fields

		$status 	= Config("setting.STATUS_OK");
		$response = array(
			'status'  => $status, 
			'message' => 'Ok', 
			'data'    => $listFields
		);

		return response()->json($response, $status);
	}

	# Duplicate By Fitri Mahardika (03/02/2020)
	public function get_list_module_fields(Request $request)
	{
		$input = $request->all();

		//get class api access for each module
		$sys 			= new sys();
		$sys_api 	= new sys_api();

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;

		$table_module = $this->table_module; // For simple define variable table module

		$list_fields 	= $this->get_list_fields($company_id, $users_id);
		$list_fields_sort = $sys->get_list_fields_sort($this->table_module, $list_fields);

		$moduleFields 			= $sys_api->moduleFieldsChange($company_id);
		$moduleFields				= $sys_api->GetCoreFieldsChange($moduleFields, $company_id);
		$moduleFieldsCustom	= $sys_api->moduleFieldsCustom($company_id); // load fields custom
		$moduleFieldsSort		= $sys->get_module_fields_sort($table_module, $list_fields_sort, $moduleFields, $moduleFieldsCustom);

		$arr_unset = [
			'html',
			$this->table_module.'_fields_change_status',
			$this->table_module.'_fields_quick',
			$this->table_module.'_fields_validation',
			$this->table_module.'_fields_status',
			$this->table_module.'_custom_fields_validation',
			$this->table_module.'_custom_fields_status',
		];

		$moduleFieldsSort = $sys->cleanerVariable($moduleFieldsSort, $arr_unset);

		$status 	= Config("setting.STATUS_OK");
		
		$response = array(
			'status'  => $status, 
			'message' => 'Ok', 
			'data'    => $moduleFieldsSort
		);

		return response()->json($response, $status);
	}

	# Created By Gilang persada
  # 19 desc 2019
  # Duplicate By Fitri Mahardika (03/02/2020)
  # For filter view
	public function filter_checked(Request $request)
	{
		# GET ALL REQUEST
		$input = $request->all();

		# Define Helper
		$sys 			= new sys();
		$sys_api 	= new sys_api();

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;

		if ( isset($input['_token']) )
		{
			$view_uuid  = $input['_token'];

			$process_checked = $sys_api->filterChecked($view_uuid, $users_id, $company_id);
		}

		$status 	= Config("setting.STATUS_OK");
		$response = array(
			'status'  => $status, 
			'message' => 'Ok',
			'data' 		=> $process_checked
		);

		return response()->json($response, $status);
	}

	# Created By Prihan Firmanullah
  # 2019-12-20
  # Duplicate By Fitri Mahardika (03/02/2020)
  # For Save Customize Form
	public function customize_save(Request $request)
	{
		# GET ALL REQUEST
		$input = $request->all();
		
		# Define Helper
		$sys 			= new sys();
		$sys_api 	= new sys_api();

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;

		$SaveCustomize = $sys_api->SaveCustomize($input, $company_id);

		$status 	= Config("setting.STATUS_OK");
		$response = array(
			'status'  => $status, 
			'message' => 'Ok'
		);

		return response()->json($response, $status);
	}

	# Created By Prihan Firmanullah
  # 2019-12-20
  # Duplicate By Fitri Mahardika (03/02/2020)
  # For Save Customize Form
	public function changelog(Request $request, $uuid='')
	{
		# GET ALL REQUEST
		$input = $request->all();
		
		# Define Helper
		$sys 			= new sys();
		$sys_api 	= new sys_api();

		$status     = Config("setting.STATUS_ERROR"); // $status     = "failed";
		$msg        = "error";

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;

		$data = array();
		$data_audit = array();

		if (!isEmpty($uuid)) 
		{
			$data = $sys_api->GetDetailData($uuid, $company_id);

			if (countCustom($data) > 0) 
			{
				if ( $data['company_id'] != $company_id ) 
				{
					$response = array(
						'status'  	=> Config("setting.STATUS_FORBIDDEN"), 
						'message'   => 'You Do not have Authority'
					);

					return response()->json($response, $status);
				}
				elseif ($data['deleted'] == Config('setting.DELETED')) 
				{
					$status = Config("setting.STATUS_USER_DEL");
					$message = 'Data Has been deleted';	
				}
				else
				{
					$data_audit = $sys_api->GetDetailAudit($data[$this->table_module.'_serial_id'], $this->table_module, $company_id);
					$status = Config("setting.STATUS_OK");
					$message = 'Ok';	
				}
			}
			else
			{
				$status = Config("setting.STATUS_USER_DEL");
				$message = 'Data Not Found';
			}
		}
		else
		{
			$status = Config("setting.STATUS_BAD_REQUEST");
			$message = 'Unknown Parameters';
		}

		$response = array(
			'status'  	=> $status, 
			'message'  	=> $message,
			'data'			=> $data_audit
		);

		return response()->json($response, $status);
	}

	# Created By Prihan Firmanullah
  # 2019-12-20
  # Duplicate By Fitri Mahardika (03/02/2020)
  # For Save Customize Form
  public function list_summary_reports(Request $request)
 	{
  	# GET ALL REQUEST
		$input = $request->all();
		
		# Define Helper
		$sys 			= new sys();
		$sys_api 	= new sys_api();

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;

		$getListReport = $sys_api->getListReport($company_id);

		if(countCustom($getListReport) > 0)
		{
			$status 	= Config("setting.STATUS_OK");
			$message 	= 'Ok';					
		}
		else
		{
			$status 	= Config("setting.STATUS_ERROR");
			$message 	= "error";
		}

		$response = array(
			'status'  	=> $status, 
			'message'  	=> $message,
			'data'			=> $getListReport
		);

		return response()->json($response, $status);
  	}

	# Created By Pratama Gilang
  # 28-11-2019
  # Duplicate By Fitri Mahardika (03/02/2020)
  # For Import
	public function import(Request $request)
	{
		# GET ALL REQUEST
		$input = $request->all();

		# Define Helper
		$sys 			= new sys();
		$sys_api 	= new sys_api();

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;

		$process_import 	= $sys_api->importSave($input, $company_id, $users_id);

		$status 	= Config("setting.STATUS_OK");
		$response = array(
			'status'  => $status, 
			'message' => 'Ok'
		);
		
		return response()->json($response, $status);
	}

	# Duplicate By Fitri Mahardika (03/02/2020)
	public function getDropdownManageFields(Request $request)
	{
		# Define Helper
		$sys 			= new sys();
		$sys_api 	= new sys_api();

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;
		
		//post of data modules for save
		$result = $sys->sys_api_view_ajax($request->all(), $company_id, $this->table_module);

		$GetDropdown = $sys_api->DropdownAll($company_id);
		
		$data = [
			'input_type' => $result,
			'GetDropdown' => $GetDropdown
		];

		return $data;
	}

	# Created By Pratama Gilang
	# 14-01-2019
	# Duplicate By Fitri Mahardika (03/02/2020)
	# For Export All data
	public function exportAll(Request $request)
	{
		# GET ALL REQUEST
		$input = $request->all();
		# Define Helper
		$sys 			= new sys();
		$sys_api 	= new sys_api();
		$sys_user = new sys_users();
		$process_export = array();

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;
		$module_name = $this->module;

		// check status export data
		if (!empty($input)) {
			$checkDataExport = array();
			$checkDataExport['module'] = $module_name;
			$checkDataExport['company_id'] = $company_id;
			$checkDataExport['users_id'] = $users_id;
			$sys_api_downloads = new sys_api_downloads();
			$resultCheckData = $sys_api_downloads->checkStatusExport($checkDataExport);
			if (!isEmpty($resultCheckData)) {
				return array();
			}
		}
		// end check status export data

		$data_per_page = isset($input['limit']) ? $input['limit'] : 50000;
		$page = isset($input['page']) ? (int) $input['page'] : 1;
		# SORTING : Default by date_created DESC
		$order_by 	= !empty($input['order_by']) ? $input['order_by'] : $this->table_module.'.date_created';
		$type_sort 	= !empty($input['type_sort']) ? $input['type_sort'] : 'DESC';
		$check_users         = $sys_user->get_users_by_id($users_id);
		$users_type = isset($check_users['users_type']) ? $check_users['users_type'] : "";
		# END

		# START : Espesially for selected : One route but different controller
		$teams_serial_id  = $sys_user->users_teams($company_id, $users_id);

		$roles = null;
		if (countCustom($teams_serial_id) > 0) 
		{
			foreach ($teams_serial_id as $key => $value) 
			{
				$roles = $sys->teams_roles_by_name($value['teams_serial_id'], Config('setting.MODULE_'.strtoupper($this->module)), Config('setting.ACL_DOWNLOAD'));
			}
		}

		if ($roles == TRUE AND $users_type == "employee") 
		{
			$status     = Config("setting.STATUS_FORBIDDEN");
			$response = array(
			'status'  => $status,
			'data'        => '', 
			'message' => 'Ok'
			);
					return response()->json($response, $status);
		}
		# END : Espesially for selected : One route but different controller

		# GET DATA
		$list_fields 	= $this->get_list_fields($company_id, $users_id);
		$criteria 		= [
							'company_id' 	=> $company_id, 
							'users_id'		=> $users_id,
							'data_per_page'	=> $data_per_page, 
							'keyword' 		=> isset($input['keyword']) ? $input['keyword'] : ""
						];
		
		$data = $sys_api->listDataExportAll($criteria, $list_fields, $input);
		$status 	= Config("setting.STATUS_OK");
		if (in_array($this->company_id, Config("setting.company_testing"))) 
		{
			$response = array(
				'status'  => $status,
				'data'		=> [
					"url" => ''
				],
				'exportype' => 'queue',
				'message' => 'Ok'
			);
		}
		elseif (in_array('*', Config("setting.company_testing"))) 
	  {
	  	$response = array(
				'status'  => $status,
				'data'		=> [
					"url" => ''
				],
				'exportype' => 'queue',
				'message' => 'Ok'
			);	
	  }
		else
		{
			$process_export = $sys_api->DownloadExportAll($data, $list_fields, $company_id);
			$response = array(
				'status'  => $status,
				'data'		=> [
					"url" => $process_export
				],
				'exportype' => '',
				'message' => 'Ok'
			);
		}
		
		return response()->json($response, $status);
	}

	# Created By Gilang Persada
	# 16 januari 2020
	# Duplicate By Fitri Mahardika (03/02/2020)
	# For edit custom manage fields
	public function editCustomFields($serial_id)
	{
		# Define Helper
		$sys 			= new sys();
		$sys_api 	= new sys_api();

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;
		
		$form = array();

		// $serial_id = base64_decode($serial_id);
		//get list of data modules with criteria
		$data = $sys_api->GetDataCutomFieldsBy($serial_id, $company_id); //get data by serial_id
		if ( countCustom($data) > 0 )
		{
			$data = $sys_api->ExplodeValidationEdit($data, '_custom_fields_validation'); // explode validation

			// data list options by dropdown_name
			if ($data[$this->table_module.'_custom_fields_input_type'] == 'groupoption') 
			{
				$list_options = $sys_api->listOptionsGroup($data[$this->table_module.'_custom_fields_options']);
			} 
			else 
			{
				$list_options = $sys_api->list_options($data[$this->table_module.'_custom_fields_options']);
			}

			$GetDropdown = $sys_api->DropdownAll($company_id);

      $getModules = $sys->getModulesChanges($company_id, 7);
			
			$customFieldsOption = $sys_api->getFieldsCondition($serial_id, $company_id , 1);

			$form = $sys_api->GetForm($company_id);

      if (countCustom($getModules) > 0) 
      {
          $getModules = $getModules['modules_change_label'];
      }
      else
      {
          $getModules = $this->module;
      }

			$status = Config("setting.STATUS_OK");
			$response = array(
				'status'  => $status, 
				'message' => 'Ok', 
				'data' 						=> $data,
				'GetDropdown'			=> $GetDropdown,
				'list_options'		=> $list_options,
				'fields_condition'	=> $customFieldsOption,
				'getform'					=> $form,
			);
			return response()->json($response, $status);
		}
	}

	# Created By Gilang Persada
	# 16 januari 2020
	# Duplicate By Fitri Mahardika (03/02/2020)
	# For edit core manage fields
	public function editCoreFields($serial_id)
	{
		# Define Helper
		$sys 			= new sys();
		$sys_api 	= new sys_api();

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;
		
		$form = array();

		//get list of data modules with criteria
		$data = $sys_api->GetDataCoreFieldsBy($serial_id, $company_id); //get data by serial_id
		$data = $sys_api->GetDataCoreFieldsChangeBy($data, $company_id); //get data by serial_id
		$data = $sys_api->ExplodeValidationEdit($data); // explode validation
		
		$GetDropdown = $sys_api->DropdownAll($company_id);

	    $getModules = $sys->getModulesChanges($company_id, 7);
	    if ( !isEmpty($data))
        {
            // data list options by dropdown_name
            if ($data[$this->table_module.'_fields_input_type'] == 'groupoption') 
            {
                $list_options = $sys_api->listOptionsGroup($data[$this->table_module.'_fields_options']);
            } 
            else 
            {
                $list_options = $sys_api->list_options($data[$this->table_module.'_fields_options']);
            }
            
            $GetDropdown = $sys_api->DropdownAll($company_id);

            $getModules = $sys->getModulesChanges($company_id, 7);
						
						$coreFieldsOption = $sys_api->getFieldsCondition($serial_id, $company_id , 0);
						
						$form = $sys_api->GetForm($company_id);

            if (!isEmpty($getModules)) 
            {
                $getModules = $getModules['modules_change_label'];
            }
            else
            {
                $getModules = $this->module;
            }

            $status = Config("setting.STATUS_OK");
            $response = array(
                'status'  => $status, 
                'message' => 'Ok', 
                'data'  => $data,
                'GetDropdown' => $GetDropdown,
                'modules_name'=> $getModules,
                'list_options' => $list_options,
								'fields_condition' =>$coreFieldsOption,
								'getform'					=> $form,
			);
			return response()->json($response, $status);
		}
	}
	

	# Created By Pratama Gilang
  # 14-01-2019
  # Duplicate By Fitri Mahardika (03/02/2020)
  # For Export Selected Data
  public function exportSelected(Request $request)
  {
    # GET ALL REQUEST
    $input = $request->all();
    # Define Helper
    $sys         	= new sys();
    $sys_api     	= new sys_api();
    $sys_users 		= new sys_users();
    $process_export = array();
    $list_fields 	= array();
    $list_fields_custom = array();
    $fields 		= array();

    $company_id = $this->company_id;
		$users_id 	= $this->users_id;
		$users_type = $this->users_type;

    // START : Espesially for selected : One route but different controller
    $check_users 		= $sys_users->get_users_by_id($users_id);
    $users_type         = isset($check_users['users_type']) ? $check_users['users_type'] : "";
    $teams_serial_id  	= $sys_users->users_teams($company_id, $users_id);

    $roles = null;
    if (countCustom($teams_serial_id) > 0) 
    {
      foreach ($teams_serial_id as $key => $value) 
      {
      	$roles = $sys->teams_roles_by_name($value['teams_serial_id'], Config('setting.MODULE_'.strtoupper($this->module)), Config('setting.ACL_DOWNLOAD'));
      }
    }
    
    if ($roles == TRUE AND $users_type == "employee") 
		{
			$status     = Config("setting.STATUS_FORBIDDEN");
	    $response = array(
	        'status'  => $status,
	        'data'        => '', 
	        'message' => 'Ok'
	    );
	      return response()->json($response, $status);
		}

    $data = $sys_api->DownloadExport($input[$this->table_module.'_uuid'], $company_id);

    $temp_sys_log_action     = $sys->log_download_by_uuid($input[$this->table_module.'_uuid'], $this->table_module, $company_id);
        
    if (!isEmpty($temp_sys_log_action)) 
    {
        $syslog = $sys->sys_api_syslog( $temp_sys_log_action, 'download', $this->table_module, '', $users_id, $company_id );
    }

    $status     = Config("setting.STATUS_OK");
    $response = array(
        'status'  => $status,
        'data'        => $data, 
        'message' => 'Ok'
    );
    
    return response()->json($response, $status);
  }

  	# Duplicate By Fitri Mahardika (03/02/2020)
	public function customize_export(Request $request)
	{
		# GET ALL REQUEST
		$input = $request->all();

		# Define Helper
		$sys             = new sys();
		$sys_api     = new sys_api();
		$form = array();
		$check_export = array();

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;

		// FORM CORE
		$form                             = $sys_api->GetFormCustomizeExport($company_id); // Get core fields & custom fields

		$check_export_form    = $sys_api->GetFormExport($company_id); // Get core fields & custom fields

		if ( countCustom($check_export_form) > 0 ) 
		{
		   $form = $check_export_form;
		}

		$check_export    = $sys_api->check_export($company_id);

		$checked = Config('setting.CUSTOMIZE_EXPORT_STANDARD');
		if ( countCustom($check_export) > 0 ) 
		{
		   $checked = Config('setting.CUSTOMIZE_EXPORT_CUSTOM');            
		}

		$arr_unset = [
			$this->table_module.'_fields_data_type',
			$this->table_module.'_fields_function',
			$this->table_module.'_fields_input_type',
			$this->table_module.'_fields_options',
			$this->table_module.'_fields_quick',
			$this->table_module.'_fields_validation',
			$this->table_module.'_fields_status',
			$this->table_module.'_fields_sorting',
		];

		$form = $sys->cleanerVariable($form, $arr_unset);

		$status     = Config("setting.STATUS_OK");
		$response = array(
		    'status'  => $status,
		    'data'        => [
		                      'form'    => array_values($form),
		                      'selected'    => $checked,
		                      ], 
		    'message' => 'Ok'
		);

		return response()->json($response, $status);
	}

 	# Duplicate By Fitri Mahardika (03/02/2020)
	public function save_export(Request $request)
	{
	  	# GET ALL REQUEST
	    $input = $request->all();

	    # Define Helper
	    $sys             = new sys();
	    $sys_api     = new sys_api();
	    $save_export = array();

	    $company_id = $this->company_id;
			$users_id 	= $this->users_id;

	    $save_export = $sys_api->save_customize_export($input, $company_id);

	    $status     = Config("setting.STATUS_OK");
	    $response = array(
	        'status'  => $status,
	        'data'        => [], 
	        'message' => 'Ok'
	    );
	    
	    return response()->json($response, $status);
	}

	#Create By Fitrimahardika(12/02/2020)
	public function ajax_autocomplete(Request $request)
	{
		$sys = new sys;
		$company_id = $this->company_id;
		$users_id 	= $this->users_id;
	 
		if($request['parent_type'] == "deals")
		{
			if(!empty($request['contact_id']) AND !empty($request['org_id']))
			{
					$result =  $sys->sys_api_Select_deals($request['parent_type'], $company_id, $request['keywords'], $request['contact_id'], $request['org_id']);
			}
			elseif(empty($request['contact_id']) AND !empty($request['org_id']))
			{
					$result =  $sys->sys_api_Select_deals($request['parent_type'], $company_id, $request['keywords'], '', $request['org_id']);
			}
			else
			{
				$result = $sys->sys_api_SelectParentIdByNew($request['parent_type'], $company_id, $users_id, $request['keywords']);
			
				foreach ($result as $key => $value) 
				{
					$result[$key]['collect'] = explode('~', $value['label']);
					$result[$key]['name'] =  (!empty($result[$key]['collect'][0])) ? $result[$key]['collect'][0]: 'none';
					$result[$key]['email'] =  (!empty($result[$key]['collect'][1])) ? $result[$key]['collect'][1]: 'none';
					$result[$key]['org'] =  (!empty($result[$key]['collect'][2])) ? $result[$key]['collect'][2] : 'none';
					$result[$key]['parent_type'] = $request['parent_type'];
				}
			}
		}
		elseif(isset($request['contact_id']))
		{
			$result = $sys->sys_api_SelectParentIdByNew($request['parent_type'], $company_id, $users_id, $request['keywords'], $request['contact_id']);
		}
		else
		{
			$result = $sys->sys_api_SelectParentIdByNew($request['parent_type'], $company_id, $users_id, $request['keywords']);
			
			foreach ($result as $key => $value) 
			{
				$result[$key]['collect'] = explode('~', $value['label']);
				$result[$key]['name'] =  (!empty($result[$key]['collect'][0])) ? $result[$key]['collect'][0]: 'none';
				$result[$key]['email'] =  (!empty($result[$key]['collect'][1])) ? $result[$key]['collect'][1]: 'none';
				$result[$key]['org'] =  (!empty($result[$key]['collect'][2])) ? $result[$key]['collect'][2] : 'none';
			  $result[$key]['parent_type'] = $request['parent_type'];
      }
		}

		return $result;
	}

	#Create By Fitrimahardika(13/02/2020)
	//Show calendar of calls
	public function calendar(Request $request, $criteria=array(), $function = '' )
	{
		$sys_users  = new sys_users();
		$sys 			= new sys();
		$sys_api 	= new sys_api();

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;

		$users_oauth2   = $sys_users->usersTokensData($users_id, $company_id);

		if(countCustom($users_oauth2) > 0)
		{
			$access_token = 1;
		}
		else
		{
			$access_token = 0;
		}

		    //For loging out google account
		    if (isset($_GET['logout'])) 
		    {
				$request->session()->forget('google_token');
				$remove_session  = $sys_users->usersTokensRemove($users_id, $company_id);

				return redirect($this->table_module.'/calendar');
		    }

		//get class api access for each module

		//list all data
		$data 		 = $sys_api->dataCalendar($users_id, $company_id);
		$teams 		 = $sys_users->usersTeamList($company_id);
		$users       = $sys_users->usersList($company_id);
		$data_count  = countCustom($data);
		$teams_count = countCustom($teams);
		$users_count = countCustom($users);
		// END : Check settings : themes template

			$data_calendar 	= array(
			'list_data'			=> $data, //Get data in table module ex. calls
			'data_count'		=> $data_count, // count data in table module ex. count(calls)
			'users_name'		=> $users_id,
			'company_name'	=> $company_id,
			'access_token'	=> $access_token, // check availbility of google oauth2
			// filter calendar
			'teams'					=> $teams,
			'teams_count'		=> $teams_count,
			'users'					=> $users,
			'users_count'		=> $users_count,
			'filter_by'			=> $request['filter_by'],
			'current_filter_uuid' => $request['uuid'],
		);

		 $status     = Config("setting.STATUS_OK");
	    $response 	= array(
	        'status'  => $status,
	        'data'    => $data_calendar, 
	        'message' => 'Ok'
	    );
	    
	    return response()->json($response, $status);
		//set view variables	
	}

	public function google_session()
	{
		if($_SERVER['SERVER_PORT']  == 443)
		{
			$protocol = 'https://';
		}
		else
		{
			$protocol = 'http://localhost/backend-services/v1/calls/calendar/google';
		}

		$sys_api 		= new sys_api;
		$sys_users	= new sys_users;

		$calendar_item  = $sys_api->googleCalendar($users_id);
		$users_oauth2   = $sys_users->usersTokensData($users_id, $company_id);
		//start google oauth2 and calendar
		// Get these values from https://console.developers.google.com
	    $client_id = $this->client_id;
	    $client_secret = $this->client_secret;
	    $redirect_uri = $protocol . $_SERVER['HTTP_HOST'] . '/backend-services/v1/'.$this->table_module.'/calendar/google';
	    
		//Start google API
	    $client = new Google_Client();
	    $client->setApplicationName("Barantum CRM Calendar");
	    $client->setAccessType('offline');   // Gets us our refreshtoken
	    $client->setApprovalPrompt("force");
	    $client->setClientId($client_id);
	    $client->setClientSecret($client_secret);
	    $client->setRedirectUri($redirect_uri);

	    $client->setScopes(array(
	    	'https://www.googleapis.com/auth/calendar', 
	    	'https://www.googleapis.com/auth/calendar.readonly', 
	    	'https://www.googleapis.com/auth/plus.me',
	    	'https://www.googleapis.com/auth/userinfo.email',
	    	'https://www.googleapis.com/auth/userinfo.profile',
	    	));

	    $authUrl = $client->createAuthUrl();

	    //1. Exchange user code with the server API
	    if (isset($_GET['code'])) {
		
		$client->authenticate($_GET['code']);  
		//$token = Session::put('google_token', $client->getAccessToken());
		$users_create_session = $sys_users->usersAccessUpdate($users_id, $company_id, $client->getAccessToken());
         $redirect_uri = $protocol . $_SERVER['HTTP_HOST'] . '/backend-services/v1/'.$this->table_module.'/calendar/google';

		return redirect($redirect);
		
	    }

	    //2. Authenticate user and send them back to calendar CRM   
	    if (countCustom($users_oauth2) == 0)  {

		$authUrl = $client->createAuthUrl();
		return redirect($authUrl);
	    }

	    //check data first, session on oauth2 must exist!
	    if (countCustom($users_oauth2) > 0) {
	    	//2.5. Get refresh token?
			if($client->isAccessTokenExpired())
			{  // if token expired
			    // refresh the token
			    $client->refreshToken($users_oauth2['users_tokens_google_refresh']);

			  // $token 	= Session::put('google_token', $client->getAccessToken());
			    $token_ = $sys_users->usersAccessUpdate($users_id, $company_id, $client->getAccessToken());
			}
	    }
        
	}

	public function google_calendar(Request $request)
	{
		if($_SERVER['SERVER_PORT']  == 443)
		{
			$protocol = 'https://';
		}
		else
		{
			$protocol = 'http://';
		}

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;

		$sys_api 		= new sys_api;
		$sys_users	= new sys_users;
		$calendar_item 	= $sys_api->googleCalendar($users_id);
		$users_oauth2   = $sys_users->usersTokensData($users_id, $company_id);

		//start google oauth2 and calendar
		// Get these values from https://console.developers.google.com
	    $client_id = $this->client_id;
	    $client_secret = $this->client_secret;

		//Start google API
	    $client = new Google_Client();
	    $client->setApplicationName("Barantum CRM Calendar");
	    $client->setAccessType('offline');   // Gets us our refreshtoken
	    $client->setApprovalPrompt("force");

	    // $client->setRedirectUri($redirect_uri);
		
	    $client->setScopes(array(
	    	'https://www.googleapis.com/auth/calendar', 
	    	'https://www.googleapis.com/auth/calendar.readonly', 
	    	'https://www.googleapis.com/auth/plus.me',
	    	'https://www.googleapis.com/auth/userinfo.email',
	    	'https://www.googleapis.com/auth/userinfo.profile',
	    ));

		$redirectURL = $protocol . $_SERVER['HTTP_HOST'] . '/backend-services/v1/'.$this->table_module.'/calendar/google';
		if (env('APP_ENV') == 'local') {
			$client->setAuthConfig($this->gcredentials);
		} else {
			$client->setClientId($client_id);
			$client->setClientSecret($client_secret);
			$redirectURL = $protocol . 'api.barantum.com/v1/'.$this->table_module.'/calendar/google';
		}
		
        $client->setPrompt('select_account consent');
        $client->setRedirectUri($redirectURL);

		if (isset($_GET['code'])) {
			//1. Exchange user code with the server API
			$client->authenticate($_GET['code']); 
			
			$token 			   = $client->getAccessToken();

			if (env('APP_ENV') == 'local') {
				$redirect_uri = $protocol . $_SERVER['HTTP_HOST'] . '/frontend/'.$this->table_module . '?oauth=yes&access_token=' . $token['access_token'] . '&expires_in=' . $token["expires_in"] . '&refresh_token=' . $token["refresh_token"] . '&scope=' . $token["scope"] . '&token_type=' . $token["token_type"] . '&id_token=' . $token['id_token'] . '&created=' . $token["created"];
			} else {
				$redirect_uri = $protocol . 'crmv5.barantum.com/'.$this->table_module . '?oauth=yes&access_token=' . $token['access_token'] . '&expires_in=' . $token["expires_in"] . '&refresh_token=' . $token["refresh_token"] . '&scope=' . $token["scope"] . '&token_type=' . $token["token_type"] . '&id_token=' . $token['id_token'] . '&created=' . $token["created"];
			}

			return redirect($redirect_uri);
		}
		//2. Authenticate user and send them back to calendar CRM   
		if (countCustom($users_oauth2) == 0) 
		{
			$authUrl = $client->createAuthUrl();
			
			$status = Config("setting.STATUS_OK");
			$result = [
				"status"  	=> $status,
				"data"		=> [
					"action"	=> "redirect",
					"authUrl" 	=> $authUrl
				]
			];

			return response()->json($result, $status);
		}
	}
	//END calendar of calls

	public function google_setoauth(Request $request)
	{
		$input = $request->all();

		if($_SERVER['SERVER_PORT']  == 443)
		{
			$protocol = 'https://';
		}
		else
		{
			$protocol = 'http://';
		}

		$sys_users	= new sys_users;

		$access_token 	= $input["access_token"];
		$expires_in 	= $input["expires_in"];
		$refresh_token 	= $input["refresh_token"];
		$scope 			= $input["scope"];
		$token_type 	= $input["token_type"];
		$id_token 		= $input["id_token"];
		$created 		= $input["created"];

		$token = [
			"access_token" 	=> $access_token,
			"expires_in" 	=> $expires_in,
			"refresh_token" => $refresh_token,
			"scope" 		=> $scope,
			"token_type" 	=> $token_type,
			"id_token" 		=> $id_token,
			"created" 		=> $created
		];

		$token = json_encode($token);

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;
		$status = Config("setting.STATUS_OK");
		try {
			$sys_users->usersTokensSave($users_id, $company_id, $token, $refresh_token);
		} catch (\Exception $e) {
			$status = Config("setting.STATUS_ERROR");
		}
		
		$result = [
			"status"  	=> $status
		];

		return response()->json($result, $status);
	}

	public function google_checkoauth(Request $request)
	{
		$users_oauth2   = sys_api('Users')->usersTokensData($this->users_id, $this->company_id);

		$data = [
			"status" => Config("setting.STATUS_BAD_REQUEST"),
			"message" => "Data tidak ditemukan"
		];
		
		if ($users_oauth2) {
			$data = [
				"status" => Config("setting.STATUS_OK"),
				"data" => [
					"exist" => true
				]
			];			
		}

		return response()->json($data);
	}

	public function google_sync(Request $request)
	{
		if($_SERVER['SERVER_PORT']  == 443)
		{
			$protocol = 'https://';
		}
		else
		{
			$protocol = 'http://';
		}

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;

		$sys_api 		= new sys_api;
		$sys_users		= new sys_users;
		$calendar_item 	= $sys_api->googleCalendar($users_id);
		$users_oauth2   = $sys_users->usersTokensData($users_id, $company_id);
		$users_oauth2   = $users_oauth2 ? $users_oauth2->toArray() : [];

		//start google oauth2 and calendar
		// Get these values from https://console.developers.google.com
	    $client_id = $this->client_id;
	    $client_secret = $this->client_secret;

		//Start google API
	    $client = new Google_Client();
	    $client->setApplicationName("Barantum CRM Calendar");
	    $client->setAccessType('offline');   // Gets us our refreshtoken
	    $client->setApprovalPrompt("force");

	    // $client->setRedirectUri($redirect_uri);
		
	    $client->setScopes(array(
	    	'https://www.googleapis.com/auth/calendar', 
	    	'https://www.googleapis.com/auth/calendar.readonly', 
	    	'https://www.googleapis.com/auth/plus.me',
	    	'https://www.googleapis.com/auth/userinfo.email',
	    	'https://www.googleapis.com/auth/userinfo.profile',
	    ));

		if (env('APP_ENV') == 'local') {
			$client->setAuthConfig($this->gcredentials);
		} else {
			$client->setClientId($client_id);
			$client->setClientSecret($client_secret);
		}
		
        $client->setPrompt('select_account consent');
		
		if($client->isAccessTokenExpired())
		{
			// if token expired
		    $users_oauth2        = sys_api('Users')->usersTokensData($users_id, $company_id);
			$users_oauth2   = $users_oauth2 ? $users_oauth2->toArray() : [];
		    // refresh the token
		    $client->refreshToken($users_oauth2['users_tokens_google_refresh']);

			$token = $client->getAccessToken();

			sys_api('Users')->usersAccessUpdate($users_id, $company_id, $token);
		}

		$status   = Config("setting.STATUS_OK");
		try {
			//3. Get token access for adding and updating their calendar
			if (countCustom($users_oauth2) > 0)
			{
				$token = $users_oauth2['users_tokens_google_access'];
				$client->setAccessToken($token);
				$client_token = json_decode($token, TRUE);
				
				// Get the API client and construct the service object
				$service = new Google_Service_Calendar($client);
				
				foreach ($calendar_item as $data) 
				{
					//Start the calendar proccess
					$date = date("Y-m-d", strtotime('+7 hours', strtotime($data[$this->table_module.'_date_start'])));
					$start_time = date("H:i:s", strtotime('+7 hours', strtotime($data[$this->table_module.'_date_start'])));

					$date2 = date("Y-m-d", strtotime('+7 hours', strtotime($data[$this->table_module.'_date_end'])));
					$end_time = date("H:i:s", strtotime('+7 hours', strtotime($data[$this->table_module.'_date_end'])));

					$start = array(
						"dateTime" => $date . "T" . $start_time . "+07:00",
					);

					$end = array(
						"dateTime" => $date2 . "T" . $end_time . "+07:00",
					);

						$description = $data['calls_description'];
						$summary     = '[Call] '.$data['calls_name'].' ['.$data['calls_status'].'] ['.$data['calls_parent_name'].']';
						$id 		 = str_replace('-', '', $data['calls_uuid']);
						// print_r($id); exit();

					//Event data, put all data that need to be imported to google calendar
					$event = array(
						'id' => $id,
						'summary' => $summary,
						'description' => $description,
						'start' => $start,
						'end' => $end,
						'colorId' => 5
					);
					
					// print "<pre>"; print_r($event); exit();
				
					$json_encode_data = json_encode($event, TRUE);

					// $json_encode_data = substr($json_encode_data, 1);

					// $json_encode_data = substr($json_encode_data, 0, -1);

					//print_r($json_encode_data); exit();

					//Add new event if it doesn't exist
					$curl = curl_init();
					curl_setopt_array($curl, array(
					CURLOPT_URL => 'https://www.googleapis.com/calendar/v3/calendars/primary/events',
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_SSL_VERIFYPEER => false,
					CURLOPT_ENCODING => "",
					CURLOPT_MAXREDIRS => 10,
					CURLOPT_TIMEOUT => 600,
					CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST => "POST",
					CURLOPT_POSTFIELDS => $json_encode_data,
					CURLOPT_HTTPHEADER => array(
							//"apikey: $this->api_key",
							'Content-type: application/json',
							'Authorization: Bearer ' . $client_token['access_token'],
						),
					));

					$response = curl_exec($curl);
					$error    = curl_error($curl);
					curl_close($curl);

					//If event exist, use update instead
					$curl = curl_init();
					curl_setopt_array($curl, array(
					CURLOPT_URL => 'https://www.googleapis.com/calendar/v3/calendars/primary/events/'.$id,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_SSL_VERIFYPEER => false,
					CURLOPT_ENCODING => "",
					CURLOPT_MAXREDIRS => 10,
					CURLOPT_TIMEOUT => 600,
					CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST => "PUT",
					CURLOPT_POSTFIELDS => $json_encode_data,
					CURLOPT_HTTPHEADER => array(
							//"apikey: $this->api_key",
							'Content-type: application/json',
							'Authorization: Bearer ' . $client_token['access_token'],
						),
					));

					$response = curl_exec($curl);
					$error    = curl_error($curl);
					curl_close($curl);
				
				}

				//Set success message
				$message = trans('lang.LBL_ALERT_CALENDAR_UPDATE');
			}
		} catch (\Exception $e) {
			$message = $e->getMessage();
			$status   = Config("setting.STATUS_ERROR");
		}

		return response()->json([
			"message" => $message,
			"status" => $status
		], $status);
	}

	public function google_out() {
		$company_id = $this->company_id;
		$users_id 	= $this->users_id;	

		$sys_api 		= new sys_api;
		$sys_users		= new sys_users;
		$status   = Config("setting.STATUS_OK");

		$message = "";
		try {
			sys_api('Users')->usersTokensRemove($users_id, $company_id);
		} catch (\Exception $e) {
			$message = $e->getMessage();
			$status   = Config("setting.STATUS_ERROR");
		}

		return response()->json([
			"message" => $message,
			"status" => $status
		], $status);
	}
	
	// Ajax From Parent Id To Related Deal
	public function ajax_parent_id(Request $request)
	{
		//get class api access for each module
		$sys 			  = new sys();
		$sys_api 	  = new sys_api();
		$setData    = array();

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;
		$input = $request->all();

		$parent_type = $request[$this->table_module.'_parent_type'];
		$parent_id = $request[$this->table_module.'_parent_id'];

		//get function for option parent id by parent id
		$DropdownParentDeals = $sys->sys_api_SelectRelatedDeals($parent_type, $parent_id, $users_id, $company_id);

		if ( countCustom($DropdownParentDeals) > 0 ) 
		{
			foreach ($DropdownParentDeals as $key => $value) 
			{
				$setData[$key] = [
					'dropdown_options_label' => $value['dropdown_options_label'],
					'dropdown_options_value' => $value['dropdown_options_value'],
					'dropdown_name'					 => 'deals_serial_id'
				]; 
			}
		}

		$status   = Config("setting.STATUS_OK");
		$response = array(
			'status'  => $status, 
			'message' => 'Ok',
			'data'    => $setData
		);

		return response()->json($response, $status);
	}
	// End Ajax From Parent Id To Related Deal

	// Ajax From Parent Id To Related Projects
	public function ajax_parent_projects(Request $request)
	{
		//get class api access for each module
		$sys 			  = new sys();
		$sys_api 	  = new sys_api();
		$setData    = array();

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;
		$input = $request->all();

		$parent_type = $request[$this->table_module.'_parent_type'];
		$parent_id = $request[$this->table_module.'_parent_id'];

		//get function for option parent id by parent id
		$DropdownParentProjects = $sys->sys_api_SelectRelatedProjects($parent_type, $parent_id, $users_id, $company_id);

		if ( countCustom($DropdownParentProjects) > 0 ) 
		{
			foreach ($DropdownParentProjects as $key => $value) 
			{
				if (isset($value)) {
					$setData[$key] = [
						'dropdown_options_label' => $value['dropdown_options_label'],
						'dropdown_options_value' => $value['dropdown_options_value'],
						'dropdown_name'					 => 'projects_serial_id'
					];
				}
			}
		}

		$status   = Config("setting.STATUS_OK");
		$response = array(
			'status'  => $status, 
			'message' => 'Ok',
			'data'    => $setData
		);

		return response()->json($response, $status);
	}
	// End Ajax From Parent Id To Related Projects

	// Ajax From Parent Id To Related Issue
	public function ajax_parent_issue(Request $request)
	{
		//get class api access for each module
		$sys 			  = new sys();
		$sys_api 	  = new sys_api();
		$setData    = array();

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;
		$input = $request->all();

		$parent_type = $request[$this->table_module.'_parent_type'];
		$parent_id = $request[$this->table_module.'_parent_id'];

		//get function for option parent id by parent id
		$DropdownParentIssue = $sys->sys_api_SelectRelatedIssue($parent_type, $parent_id, $users_id, $company_id);

		$default = array();

		if ( countCustom($DropdownParentIssue) > 0 ) 
		{
			foreach ($DropdownParentIssue as $key => $value) 
			{
				$setData[$key] = [
					'dropdown_options_label' => $value['dropdown_options_label'],
					'dropdown_options_value' => $value['dropdown_options_value'],
					'dropdown_name'					 => 'issue_serial_id'
				]; 
			}
		}

		$status   = Config("setting.STATUS_OK");
		$response = array(
			'status'  => $status, 
			'message' => 'Ok',
			'data'    => $setData
		);

		return response()->json($response, $status);
	}
	// End Ajax From Parent Id To Related Issue
	
	public function ajax_parent_tickets(Request $request)
	{
		//get class api access for each module
		$sys 			  = new sys();
		$sys_api 	  = new sys_api();
		$setData    = array();

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;
		
		$parent_type = $request[$this->table_module.'_parent_type'];
		$parent_id = $request[$this->table_module.'_parent_id'];

		//get function for option parent id by parent id
		$DropdownParentTickets = $sys->sys_api_SelectRelatedTickets($parent_type, $parent_id, $users_id, $company_id);

		$default = array();

		if ( countCustom($DropdownParentTickets) > 0 ) 
		{
			foreach ($DropdownParentTickets as $key => $value) 
			{
				$setData[$key] = [
					'dropdown_options_label' => $value['dropdown_options_label'],
					'dropdown_options_value' => $value['dropdown_options_value'],
					'dropdown_name'					 => 'tickets_serial_id'
				]; 
			}
		}

		$status   = Config("setting.STATUS_OK");
		$response = array(
			'status'  => $status, 
			'message' => 'Ok',
			'data'    => $setData
		);

		return response()->json($response, $status);
	}
	
	public function parent_deals_serial_id(Request $request)
	{
		//get class api access for each module
		$sys 			  = new sys();
		$sys_api 	  = new sys_api();
		$setData    = array();

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;
		
		//get function for option parent id by parent id
		$related_projects = $sys_api->related_projects($request->all(), $company_id);

		if ( countCustom($related_projects) > 0 ) 
		{
			foreach ($related_projects as $key => $value) 
			{
				$setData[$key] = [
					'dropdown_options_label' => $value['dropdown_options_label'],
					'dropdown_options_value' => $value['dropdown_options_value'],
					'dropdown_name'					 => 'projects_serial_id'
				]; 
			}
		}

		$status   = Config("setting.STATUS_OK");
		$response = array(
			'status'  => $status, 
			'message' => 'Ok',
			'data'    => $setData
		);

		return response()->json($response, $status);
	}

	public function get_history_pbx(Request $request)
	{
		//get class api access for each module
		$sys 			  = new sys();
		$sys_api 	  = new sys_api();
		$data    = array();
		$input = $request->all();

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;

	  $data = $sys_api->get_history_pbx($input, $company_id, $users_id);

		$status   = Config("setting.STATUS_OK");
		$response = array(
			'status'  => $status, 
			'message' => 'Ok',
			'data'    => $data
		);

		return response()->json($response, $status);
	}

	public function pbx_save_zoiper(Request $request)
	{
		$sys 			  = new sys();
		$sys_api 	  = new sys_api();
		$sys_deals 	  = new sys_deals();
		$input = $request->all();

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;
		
		$calls_serial_id = $input['calls_serial_id'];

		if(isset($input['deals_uuid']) && $input['deals_uuid'] != '')
		{
			$data_deals = $sys_deals->GetDetailData($input['deals_uuid'], $company_id, $users_id);
			if(!isEmpty($data_deals))
			{
				$request['deals_serial_id'] = $data_deals['deals_serial_id'];
			}

			unset($request['deals_uuid']);
		}

		$GetUsersInformation = $sys_api->GetUsersInformation($users_id);

		// Encode URL Destination Phone Number
		$phone_dst = urlencode($request['dst']);

		// Src
		if (countCustom($GetUsersInformation) > 0) 
		{
			$request['src'] = isset($GetUsersInformation['users_zoiper_extention']) ? $GetUsersInformation['users_zoiper_extention'] : '';
		}

		if ( isset($input['uniqueid']) && $request->input('uniqueid') != '' ) 
		{
			$uniqueid = urlencode($request->input('uniqueid'));
			$link_url = "https://".$GetUsersInformation['url_recording']."/tool/get_call.php?uniqueid=".$uniqueid;
		}
		else
		{
			$link_url = "https://".$GetUsersInformation['url_recording']."/tool/get_call.php?src=".$request['src']."&dst=".$phone_dst."&calldate=".date('Y-m-d');
		}

		$curl = curl_init();
	    curl_setopt_array($curl, array(
	    CURLOPT_URL => $link_url,
	    CURLOPT_RETURNTRANSFER => true,
	    CURLOPT_SSL_VERIFYPEER => false,
	    CURLOPT_SSL_VERIFYHOST => false,
	    CURLOPT_ENCODING => "",
	    CURLOPT_MAXREDIRS => 10,
	    CURLOPT_TIMEOUT => 600,
	    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	    CURLOPT_CUSTOMREQUEST => "GET",
	    ));

	    $response = curl_exec($curl);
	    $error    = curl_error($curl);
	    curl_close($curl);

	    if ( !isEmpty($response) ) 
	    {
			  $json_decode = json_decode($response, TRUE);

			  if ( isset($json_decode['disposition']) ) 
			  {
			  	if ( $json_decode['disposition'] == 'ANSWERED' ) 
			  	{
					  $explode 	= explode('/', $json_decode["recordingfile"]);

					  $count 		= countCustom($explode) - 1;

					  $request['recordingfile'] 	= $explode[$count];
					  $request['uniqueid'] 				= $json_decode["uniqueid"];
					  $request['billsec'] 				= $json_decode["billsec"];
					  $request['calldate'] 				= $json_decode["calldate"];
					}
			  }
	    }

		if ( !empty($request['module']) AND !empty($request['serial_id']) ) 
		{
			if ($calls_serial_id == '') {	
				$process_save = $sys_api->PBXSave($request->all(), $company_id, $users_id);
				
				// add last activity on click to call
				$request['calls_parent_type'] = $request->module;
				$request['calls_parent_id'] = $request->serial_id;
				$sys_api->save_last_activities($request->all(), $process_save, $company_id);

				//SAVE FIRST ACTIVITY
				$sys_api->save_first_activities($request->all(), $process_save, $company_id);

				// save last completed activity
				$sys_api->save_last_completed_activities($request->all(), $process_save, $company_id);

				$syslog_action  = $sys->log_save($request->all(), $this->table_module, $company_id);
				if ( $syslog_action != '' )
				{
					$syslog 			= $sys->sys_api_syslog( $syslog_action, 'create', $this->table_module, $process_save, $users_id, $company_id );
				}
				// end add last activity

				// SAVE PBX CALLS RECORDING
				$SaveCallsPbxRecording = $sys_api->SaveCallsPbxRecording($request->all(), $process_save, $company_id);

				elasticAddData($process_save, $company_id, $this->table_module);

				$status = Config("setting.STATUS_OK");
				$message = 'Save  data call PBX success';
			}else {

				$input = $request->all();
				$input['date_modified']								= date('Y-m-d H:i:s');
				$input[$this->table_module.'_status'] = 'Held';
				$input['modified_by']									= $users_id;

				//Save Log
				$data_serial_id 		= $sys_api->GetSerialIdByUuid($request[$this->table_module.'_uuid'], $company_id);
				$old_data 					= $sys_api->old_data_log($input[$this->table_module.'_uuid'], $company_id);
				$process_update 		= $sys_api->PBXUpdate($request->all(), $company_id, $users_id);

				// this is for strtotime input type = datetime
				if ( isset($input[$this->table_module.'_date_start']) && $input[$this->table_module.'_date_start'] != "" )
				{
					$input[$this->table_module.'_date_start'] = date('Y-m-d H:i:s', strtotime($input[$this->table_module.'_date_start']. "-7 hours"));
				}
				if ( isset($input[$this->table_module.'_date_end']) && $input[$this->table_module.'_date_end'] != "" )
				{
					$input[$this->table_module.'_date_end'] = date('Y-m-d H:i:s', strtotime($input[$this->table_module.'_date_end']. "-7 hours"));
				}
				
				$syslog_action 			= $sys->log_update($old_data, $process_update, $this->table_module, $company_id);

				if ( !isEmpty($syslog_action) ) {
					$syslog 					= $sys->sys_api_syslog( $syslog_action, 'update', $this->table_module, $data_serial_id, $users_id, $company_id );
				}
				//end Save log
				// add last activity on click to call
				$request['calls_parent_type'] = $request->module;
				$request['calls_parent_id'] = $request->serial_id;
				$sys_api->save_last_activities($request->all(), $input['calls_serial_id'], $company_id);
				// end add last activity
				
				// save last completed activity
				$sys_api->save_last_completed_activities($request->all(), $input['calls_serial_id'], $company_id);

				$SaveCallsPbxRecording = $sys_api->SaveCallsPbxRecording($request->all(), $input['calls_serial_id'], $company_id);
				
				elasticAddData($input['calls_serial_id'], $company_id, $this->table_module);

				$status = Config("setting.STATUS_OK");
				$message = 'Update data call PBX success';
			}

			$data_request['src'] = $request['src'];
			$data_request['dst'] = $request['dst'];

			$UpdatePbxIncoming = $sys_api->UpdatePbxIncoming($users_id, $company_id, $data_request);
		}
		else
		{
			$status = Config("setting.STATUS_BAD_REQUEST");
			$message = 'Sorry failed to save, please select the destination of your phone';
		}

		$response = array(
			'status'  => $status, 
			'message' => $message,
		);

		return response()->json($response, $status);
	
	}

	public function getIncomingCalls(Request $request)
  {
  	$input = $request->all();
    $sys        = new sys();
    $sys_api    = new sys_api();
		
    # Defined Variable
    $status       = Config("settings.STATUS_ERROR");
    $msg          = "error";
    $data         = array();
		
		$company_id = $this->company_id;
		$users_id   = isset($input['users_id']) ? $input['users_id'] : 0;
		
		
    $data 	    = $sys_api->getIncomingCalls($company_id, $users_id);

    if($data)
    {
			$status     = Config("setting.STATUS_OK");
      $msg        = "ok";
    }
  	else
  	{
  		$status   = Config("setting.STATUS_ERROR");
  	}
		
    $response = array(
      'status'  => $status,
      'data'    => $data,
    );

    return response()->json($response);
	}

	public function pin_timeline(Request $request,string $data_uuid)
	{
		$input = $request->all();
	    $sys        = new sys();
	    $sys_api    = new sys_api();
			
	    # Defined Variable
	    $status       = Config("settings.STATUS_ERROR");
	    $msg          = "error";
	    $data         = array();
		
		$company_id = $this->company_id;
		$users_id   = $this->users_id;

		if($company_id < 1 || $users_id < 1){
			$response = array(
		      'status'  => $status,
		      'data'    => [],
		    );
		    return response()->json($response);		
		}

		$pin_detail = $sys_api->GetDataByUuid($data_uuid);
		
		if($pin_detail === null){
			$response = array(
		      'status'  => $status,
		      'data'    => [],
		    );
		    return response()->json($response);		
		}

		$pin_data = $sys_api->pinAddData($data_uuid, $pin_detail[$this->table_module.'_name'], $this->table_module, $users_id, $company_id);

		$response = array('status' => Config("setting.STATUS_OK"),'data'=> []);
		return response()->json($response);
	}

	public function unpin_timeline(Request $request,string $data_uuid)
	{
		$input = $request->all();
	    $sys        = new sys();
	    $sys_api    = new sys_api();
			
	    # Defined Variable
	    $status       = Config("settings.STATUS_ERROR");
	    $msg          = "error";
	    $data         = array();
		
		$company_id = $this->company_id;
		$users_id   = $this->users_id;

		if($company_id < 1 || $users_id < 1){
			$response = array(
		      'status'  => $status,
		      'data'    => [],
		    );
		    return response()->json($response);		
		}
		
		$pin_detail = $sys_api->GetDataByUuid($data_uuid);
		
		if($pin_detail === null){
			$response = array(
		      'status'  => $status,
		      'data'    => [],
		    );
		    return response()->json($response);		
		}

		// $syslog = sys_api_syslog('Unpinned', 'unpin', $this->table_module, $pin_detail[$this->table_module.'_serial_id'], $this->sess['users_id'], $this->sess['company_id'] );

		$unpin_data = $sys_api->pinDelete($data_uuid);

		$response = array('status' => Config("setting.STATUS_OK"),'data'=> []);
		return response()->json($response);
	}

	public function open_notify_campaign(Request $request)
	{
		//get class api access for each module
		$sys 			  = new sys();
		$sys_api 	  = new sys_api();
		$data    = array();
		$input = $request->all();

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;

	  $data = $sys_api->updateStatusCampaighRead($input, $company_id);

		$status   = Config("setting.STATUS_OK");
		$response = array(
			'status'  => $status, 
			'message' => 'Ok',
			'data'    => $data
		);

		return response()->json($response, $status);
	}

	public function get_recording_new(Request $request)
	{
		$sys_api = sys_api($this->module);
		$input = $request->all();
		$result = array();

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;

		$getRecord = $sys_api->getRecord($input, $users_id);

		if (countCustom($getRecord) > 0) 
		{
			$result = [
				'status'  => 200,
				'content' => $getRecord,
				'message' => 'get recording file successfully',
			];
		}
		else
		{
			$result = [
				'status'  => 404,
				'content' => '',
				'message' => 'get recording file not successfully',
			];
		}

		return response()->json($result, $result['status']);
	}

	# Created By Pratama Gilang
  # 9 des 2019
  # Duplicate By Fitri Mahardika (03/02/2020)
  # For edit data and get value content
  # Output like getForm with content
	public function editDataPbx(Request $request, $uuid='')
	{
		# GET ALL REQUEST
		$input = $request->all();

		# Define Helper
		$sys 			= new sys();
		$sys_api 	= new sys_api();

		# Define Variable
		$status     = Config("setting.STATUS_ERROR"); // $status     = "failed";
		$msg        = "error";

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;

		$result = array();
	
		if (!isEmpty($uuid)) 
		{
			$data = $sys_api->GetDetailData($uuid, $company_id);
			
			if (countCustom($data) > 0) 
			{
				if ($data['deleted'] == Config('setting.DELETED')) 
				{
					$status = Config("setting.STATUS_USER_DEL");
					$message = 'Data Has been deleted';	
				}
				else
				{
					$status = Config("setting.STATUS_OK");
					$message = 'Ok';		

					// get related module
					$data = $sys_api->join_sys_rel_edit($data, $company_id);
					//$data['calls_date_start'] = '';
					$data['calls_direction'] = 'Outbound';
					$result = $sys_api->getDataEdit($data, $company_id);

					// Option Deals
					$option_related_deals		 = $sys->sys_api_SelectRelatedDeals($data[$this->table_module.'_parent_type'], $data[$this->table_module.'_parent_id_id'], $users_id, $company_id, $data['deals_serial_id']);
					
					// Option Projects
					$option_related_projects = $sys->sys_api_SelectRelatedProjects($data[$this->table_module.'_parent_type'], $data[$this->table_module.'_parent_id_id'], $data['deals_serial_id'], $company_id);

					// Option Issue
					$option_related_issue		 = $sys->sys_api_SelectRelatedIssue($data[$this->table_module.'_parent_type'], $data[$this->table_module.'_parent_id_id'], $data['deals_serial_id'], $company_id);

					foreach ($result as $key => $value) 
					{
						if ( isset($value[$this->table_module.'_fields_name']) AND $value[$this->table_module.'_fields_name'] == 'deals_serial_id' ) 
						{
							$result[$key]['extra']	= $option_related_deals;
						}
						elseif ( isset($value[$this->table_module.'_fields_name']) AND $value[$this->table_module.'_fields_name'] == 'projects_serial_id' ) 
						{
							$result[$key]['extra']	= $option_related_projects;
						}
						elseif ( isset($value[$this->table_module.'_fields_name']) AND $value[$this->table_module.'_fields_name'] == 'issue_serial_id' ) 
						{
							$result[$key]['extra']	= $option_related_issue;
						}
					}
				}
			}
			else
			{
				$status = Config("setting.STATUS_BAD_REQUEST");
				$message = 'Data Not Found';
			}
		}
		else
		{
			$status = Config("setting.STATUS_BAD_REQUEST");
			$message = 'Unknown Parameters';
		}

		$response = array(
			'status'  	=> $status, 
			'message'  	=> $message,
			'data'  		=> array_values($result),
		);

		return response()->json($response, $status);
	}

	public function getId(Request $request)
	{
		$input = $request->all();

		# Define Helper
		$sys 		= new sys();
		$sys_api 	= new sys_api();

		$status = Config("setting.STATUS_OK");
		$message = 'success';
		$data_per_page = isset($input['limit']) ? $input['limit'] : Config('setting.pagesize');

		$company_id = $this->company_id;
		$users_id 	= $this->users_id;
		$users_type = $this->users_type;
		$table_module = $this->table_module;
		
		# SORTING : Default by date_created DESC
		$order_by 	= !empty($input['order_by']) ? $input['order_by'] : $this->table_module.'.calls_date_start';
		$type_sort 	= !empty($input['type_sort']) ? $input['type_sort'] : 'DESC';
		# END

		$criteria 	= array(
			'company_id' 	=> $company_id, 
			'users_id'		=> $users_id, 
			'order_by'		=> $order_by, 
			'type_sort'		=> $type_sort, 
			'data_per_page'	=> $data_per_page, 
			'keyword' 		=> isset($input['keyword']) ? $input['keyword'] : ""
		);

		$list_fields 	= $this->get_list_fields($company_id, $users_id);

		# GET DATA
		$data = [];
		try {
			$data['data'] = $sys_api->getAllFilteredId($input, $list_fields, $criteria);
			$data = $sys_api->getRoles($data, $company_id, $users_id); // Roles edit
		} catch (\Exception $e) {
			$status  = Config("setting.STATUS_ERROR");
			$message = $e->getMessage();
		}

		$response = array(
			'status'  	=> $status, 
			'message'  	=> $message,
			'data'  	=> array_values($data),
		);

		return response()->json($response, $status);
	}

	public function hardCodeTemplateCSV($input_type, $email)
	{
		$str = '';
		if($input_type=='multiplecheckbox' || $input_type=='multipleoption')
		{
			$str = "[\"Sample\",\"Data\"]";
		}
		elseif($input_type=='datetime'||$input_type=='date')
		{
			$str = '2021-08-18';
		}
		elseif($input_type=='numeric' || $input_type=='monetary')
		{
			$str = '123456';
		}
		elseif($input_type=='phone')
		{
			$str = '082108210821';
		}
		elseif($email)
		{
			$str = 'sample@data.com';
		}
		else
		{
			$str = "Sample Data";
		}
		return $str;
	}

	public function ExcludeSomeFieldCoreTemplateCSV($fn)
	{
		$result = false;
		if(str_contains($fn, 'first_id') === false &&
				str_contains($fn, 'first_module') === false &&
				str_contains($fn, 'first_assign') === false &&
				str_contains($fn, 'last_id') === false &&
				str_contains($fn, 'last_module') === false &&
				str_contains($fn, 'last_date') === false &&
				str_contains($fn, 'projects_serial_id') === false &&
				str_contains($fn, 'tickets_serial_id') === false &&
				str_contains($fn, 'issue_serial_id') === false &&
				str_contains($fn, 'deals_serial_id') === false &&
				str_contains($fn, 'last_workflow') === false &&
				str_contains($fn, 'last_workflow_date') === false &&
				str_contains($fn, 'last_workflow_description') === false &&
				str_contains($fn, 'first_respon') === false &&
				str_contains($fn, 'tickets_bulk_serial_id') === false
		)
		{
			$result = true;
		}
		return $result;
	}

	public function templateCSV(Request $request)
  {
    $sys_api     = new sys_api();
    $company_id = $this->company_id;

		$form = $sys_api->GetForm($company_id); 
		$result = array();
		$i =0 ;
		$email = false;
		ksort($form);
		foreach($form as $key => $value)
		{
			$fn = isset($value[$this->table_module.'_fields_name']) ? $value[$this->table_module.'_fields_name']: null;
			if(isset($fn) && $this->ExcludeSomeFieldCoreTemplateCSV($fn))
			{
				if(isset($value[$this->table_module.'_fields_label']))
				{
					$result[0][$i] = $value[$this->table_module.'_fields_label'];
					if(isset($value[$this->table_module.'_fields_validation_multi']))
					{
						foreach($value[$this->table_module.'_fields_validation_multi'] as $key1 => $value1)
						{
							if($value1=='email')
							{
								$email = true;
							}
						}
					}
					$result[1][$i] = $this->hardCodeTemplateCSV($value[$this->table_module.'_fields_input_type'],$email);
				}
			}

			if(isset($value[$this->table_module.'_custom_fields_label']))
			{
				$result[0][$i] = '- '.$value[$this->table_module.'_custom_fields_label'];
				if(isset($value[$this->table_module.'_custom_fields_validation_multi']))
				{
					foreach($value[$this->table_module.'_custom_fields_validation_multi'] as $key1 => $value1)
					{
						if($value1=='email')
						{
							$email = true;
						}
					}
				}
				$result[1][$i] = $this->hardCodeTemplateCSV($value[$this->table_module.'_custom_fields_input_type'],$email);
			}

			$email = false;
			$i++;
		}
		// dd($form,$result);
    $status     = Config("setting.STATUS_OK");
    $response = array(
        'status'  => $status,
        'data'        => $result, 
        'message' => 'Ok'
    );
    
    return response()->json($response, $status);
  }

  public function searchRelatedModule(Request $request)
  {
		$input = $request->only('autocomplete', 'parent_type', 'keyword');
		$parent_type = $input['parent_type'];
		$sys_api 	= new sys_api();
		
		$company_id = $this->company_id;
		$users_id 	= $this->users_id;

		$keyword = $input['keyword'];
		try {
			$datarelated = [];
			foreach ($parent_type as $key => $value) {
				$related = $sys_api->{'ajax_parent'}($value,$company_id, $users_id, $keyword);
				foreach ($related['data'] as $relkey => $relvalue) {
					$datarelated[] = [
						'dropdown_options_reltype' => $related['reltype'],
						'dropdown_options_value' => $relvalue->dropdown_options_value,
						'dropdown_options_label' => $relvalue->dropdown_options_label
					];
				}
			}
		} catch (\Exception $e) {
			$status = Config("setting.STATUS_ERROR");
			$response = array(
				'status'  => $status, 
				'message' => $e->getMessage(),
			);

			return response()->json($response, $status);
		}

		$status = Config("setting.STATUS_OK");
		$response = array(
			'status'  => $status, 
			'message' => 'Ok', 
			'data'    => $datarelated
		);

		return response()->json($response, $status);
  }
  public function uploadImport(Request $request)
  {
  	# GET ALL REQUEST
		$input = $request->all();

		# Define Helper
		$sys 			= new sys();
		$sys_api 	= new sys_api();

		# Define Variable
		$status 			= Config("setting.STATUS_OK");
		$message      = "ok";	# GET ALL REQUEST
		$upload = $request->file('file');
		$input['module'] = $this->module;
		unset($input['file']);

		// $check_total_data = checkDataImport($upload);

		if (in_array($this->company_id, Config("setting.company_testing"))) # condition import queue if data beter than 500
		{
			unset($input['collection']);
			$process = sys_api('Documents')->processUploadsExcel($input, $upload, $this->users_id, $this->company_id);
		}
		elseif (in_array('*', Config("setting.company_testing"))) 
	  {
			unset($input['collection']);
			$process = sys_api('Documents')->processUploadsExcel($input, $upload, $this->users_id, $this->company_id);
	  }
		else
		{
			$criteria['data'] = json_decode($input['data'], true);
			$criteria[$this->table_module.'_owner'] = json_decode($input[$this->table_module.'_owner'], true);
			$criteria['next_condition'] = json_decode($input['next_condition'], true);
			$criteria['collection'] = json_decode($input['collection'], true);

			$process 	= $sys_api->importSave($criteria, $this->company_id, $this->users_id);
		}

		if(isset($input['save_mapping_name'])){

			unset($input['collection']);
			$criteria['data'] = $input['data'];
			$criteria['mapping_name'] = $input['save_mapping_name'];
			$sys_api->saveMapping($criteria , $this->users_id, $this->company_id);
		}

		# Define Variable
		$status 			= Config("setting.STATUS_OK");
		$message      = "ok";

		$response = array(
			'status'  	   => $status, 
			'message'   	 => $message, 
		);

		return response()->json($response, $status);
  }

  public function getTagsMap($uuid)
  {
	  $sys_api 	= new sys_api();
	  $data = [];
	  $status = Config("setting.STATUS_OK");
	  try {
		  $data		= $sys_api->getTagsMap($uuid, $this->company_id, $this->table_module);
	  } catch (\Exception $e) {
		  $status = Config("setting.STATUS_ERROR");
		  $response = array(
			  'status'  	=> $status,
			  'message'  	=> $e->getMessage()
		  );
  
		  return response()->json($response, $status);
	  }

	  $response = array(
		  'status'  	=> $status, 
		  'data'		=> $data
	  );

	  return response()->json($response, $status);
  }

	public function updateTagsMap(Request $request)
	{
		$sys_api 	= new sys_api();
		$data = [];
		$input = $request->all();
		$status = Config("setting.STATUS_OK");
		try {
			$calls = $sys_api->GetDataByUuid($input['uuid']);
			$serial_id = $calls['calls_serial_id'];
			$calls_tags = new \stdClass();
			$calls_tags->type	= $input['type'];
			$calls_tags->label	= $input['label'];
			$calls_tags->color	= $input['color'];
			$calls_tags->value	= $input['value'];

			$sys_api->simpanTag([$calls_tags], $serial_id, $this->company_id, $this->users_id, $this->table_module);

		} catch (\Exception $e) {
			$status = Config("setting.STATUS_ERROR");
			$response = array(
				'status'  	=> $status,
				'message'  	=> $e->getMessage()
			);
	
			return response()->json($response, $status);
		}

		$response = array(
			'status'  	=> $status, 
			'data'		=> $data
		);

		return response()->json($response, $status);
	}

	public function removeTagsMap($uuid)
	{
		$sys_api = new sys_api();
		$status = Config("setting.STATUS_OK");
		try {
			$sys_api->removeTagByuuid($uuid, $this->company_id, $this->users_id, $this->table_module);
		} catch (\Exception $e) {
			$status = Config("setting.STATUS_ERROR");
			$response = array(
				'status'  	=> $status,
				'message'  	=> $e->getMessage() . ' Line : ' . $e->getLine()
			);
	
			return response()->json($response, $status);
		}

		$response = array(
			'status'  	=> $status
		);

		return response()->json($response, $status);
	}  

	public function getcountdata(Request $request)
  {
		# GET ALL REQUEST
		$input = $request->all();

		# Define Helper
		$sys             = new sys();
		$sys_api     = new sys_api();

		# Define Variable
		$status     = Config("setting.STATUS_ERROR"); // $status     = "failed";
		$msg        = "error";
		$data_per_page = isset($input['limit']) ? $input['limit'] : Config('setting.pagesize');
		$page = isset($input['page']) ? (int) $input['page'] : 1;

		$company_id   = $this->company_id;
		$users_id       = $this->users_id;
		$users_type   = $this->users_type;
		$table_module = $this->table_module;

		# SORTING : Default by date_created DESC
		$order_by     = !empty($input['order_by']) ? $input['order_by'] : 'date_created';
		$type_sort     = !empty($input['type_sort']) ? $input['type_sort'] : 'DESC';
		# END

		# GET DATA
		$list_fields     = $this->get_list_fields($company_id, $users_id);

		$list_fields_sort = $sys->get_list_fields_sort($this->table_module, $list_fields);

		$criteria     = array(
												'company_id'         => $company_id, 
												'users_id'            => $users_id, 
												'order_by'            => $order_by, 
												'type_sort'            => $type_sort, 
												'data_per_page'    => $data_per_page, 
													'keyword'             => isset($input['keyword']) ? $input['keyword'] : ""
										);

		$filterView = array();
		if (in_array($company_id, Config('setting.company_testing_elastic'))) 
		{
			$filterView         = $sys_api->viewChecked($company_id, $users_id); // checked filter view active
			$filterViewName = '';
			if (!empty($filterView)) 
			{
				$filterViewName = $filterView[$this->table_module.'_view_name'];
			}
		}

		if ( (!empty($input['subaction']) && $input['subaction'] == "search") || (!empty($input['subaction']) && $input['subaction'] == "search_global") || (!empty($filterView) > 0 && $filterViewName != 'Everyone' && $filterViewName != 'You') ) 
		{
				if (in_array($company_id, Config('setting.company_testing_elastic'))) 
				{
					$elasticData = $sys_api->elasticListing($criteria, $list_fields, $input);
					$criteria = array_merge($elasticData, $criteria);
					$data      = [
											"data" => $criteria['total']
											];    
				}
				else
				{
						$data  = $sys_api->listData($criteria, $list_fields, $input, $data_roles=TRUE, $data_filter=TRUE,'countdata');
				}
		}
		else
		{
			$data  = $sys_api->listData($criteria, $list_fields, $input, $data_roles=TRUE, $data_filter=TRUE,'countdata');
		}

		if (countCustom($data) > 0) {
							$status = Config("setting.STATUS_OK");
							$message = 'Ok';    
		} else {
							$status = Config("setting.STATUS_OK");
							$message = 'Data Not Found';
		}

		$response = array(
						'status'      => $status, 
						'message'      => $message, 
						'data'        => $data
		);

		return response()->json($response, $status);
  }

	public function attachment_validate(Request $request)
	{
		$sys = new sys();
		
		# Define Variable
		$status 			= Config("setting.STATUS_OK");
		$message      = "ok";

		# GET DATA
		$data = $sys->getAttachSettings($this->company_id, $this->table_module);

		$response = array(
			'status'  	   => $status, 
			'message'   	 => $message, 
			'data'        => $data,
		);
		return response()->json($response, $status);

		
	}

	public function deleteFieldsCondition($uuid=""){
		
		$sys_api 	= new sys_api();
		$company_id = $this->company_id;
		$proses = $sys_api->deleteFieldsCondition($uuid, $company_id);
		
		if ( countCustom($proses) > 0 ){
			//delete success
			$status = Config("setting.STATUS_OK");
			$message = 'Data Has been delete';				
		}
		else
		{
			//delete fail
			$status = Config("setting.STATUS_ERROR");
			$message = 'Unknown Parameters';
		}
	

		$response = array(
			'status'  	=> $status, 
			'message'  	=> $message, 
			'data'    	=> $proses,
		);

		return response()->json($response, $status);

	}
	public function getListMapping(){

		$sys_api 	= new sys_api();
		$company_id = $this->company_id;

		$proses = $sys_api->getListMapping($company_id);

		$status = Config("setting.STATUS_OK");
		$message	= 'Ok';


		$response = array(
			'status'  	=> $status, 
			'message'  	=> $message, 
			'data'    	=> $proses,
		);

		return response()->json($response, $status);

	}
	public function getFieldsMapping($uuid)
	{
		$sys_api 	= new sys_api();
		$company_id = $this->company_id;

		$proses = $sys_api->getFieldsMapping($uuid, $company_id);

		$result = array();

		if ( countCustom($proses) > 0 ){
			$status = Config("setting.STATUS_OK");
			$message	= 'Ok';
		}
		else
		{
			$status = Config("setting.STATUS_ERROR");
			$message = 'Unknown Parameters';
		}


		$response = array(
			'status'  	=> $status, 
			'message'  	=> $message, 
			'data'    	=> $proses,
		);

		return response()->json($response, $status);

	}

	public function deleteMapping(Request $request){
		$sys_api 	= new sys_api();
		$company_id = $this->company_id;

		$uuid = $request['mapping_uuid'];
		$proses = $sys_api->deleteMapping($uuid, $company_id);

		$status = Config("setting.STATUS_OK");
		$message = 'Ok';
		$response = array(
			'status'  			=> $status, 
			'message'  			=> $message,
		);

		return response()->json($response, $status);
	}

}