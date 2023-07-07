<?php
/*********
 * 
 * BARANTUM.COM © CONFIDENTIAL
 * __
 * 
 *  PT KOSADA GROUP INDONESIA © All Rights Reserved.
 * 
 * NOTICE:  All information contained herein is, and remains
 * the property of PT KOSADA GROUP INDONESIA and its suppliers,
 * if any.  The intellectual and technical concepts contained
 * herein are proprietary to PT KOSADA GROUP INDONESIA
 * and its suppliers and may be covered by Indonesia and Foreign Patents,
 * patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from PT KOSADA GROUP INDONESIA.
 * 
 * 
 * Hak Cipta dipegang sepenuhnya oleh PT Kosada Group Indonesia (Barantum.com) dan dilindungi sesuai Pasal-pasal dalam Undang-Undang Nomor 28 Tahun 2014 tentang Hak Cipta (“UU Hak Cipta”) .
 */

namespace App\Http\Controllers\Calls;

use App\Http\Controllers\Sys\Helper as sys;
use App\Http\Controllers\Contacts\Helper as sys_contacts;
use App\Http\Controllers\Org\Helper as sys_org;
use App\Http\Controllers\Deals\Helper as sys_deals;
use App\Http\Controllers\Users\Helper as sys_users;
use App\Http\Controllers\Dashboards\Helper as sys_dashboards;
use App\Http\Controllers\Documents\Helper as sys_documents;

use Illuminate\Support\Facades\Storage;
use \Aws\S3\S3Client as s3;
use Intervention\Image\Facades\Image;

use DB;
use DateTime;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

use Illuminate\Support\Facades\Log;
use App\Http\Controllers\PushNotifications\Helper as sys_push;

class Helper 
{
	var $uuid = 'Ramsey\Uuid\Uuid';
	var $uuid_parent = 'Ramsey\Uuid\Exception\UnsatisfiedDependencyException';

	var $module = "Calls";
	var $excel = 'Maatwebsite\Excel\Facades\Excel';
	var $file = 'Illuminate\Support\Facades\File';
	var $input = 'Illuminate\Support\Facades\Input';
	var $paginate = 'Illuminate\Pagination\Paginator';
	var $paginate_manually = 'Illuminate\Pagination\LengthAwarePaginator';

	var $model_class = 'App\Http\Controllers\Calls\Models\Calls';
	var $model_fields_class = 'App\Http\Controllers\Calls\Models\Calls_fields';
	var $model_custom_fields_class = 'App\Http\Controllers\Calls\Models\Calls_custom_fields';
	var $model_custom_values_class = 'App\Http\Controllers\Calls\Models\Calls_custom_values';
	var $model_custom_values__class = 'App\Http\Controllers\Calls\Models\Calls_custom_values_';
	var $model_fields_change_class = 'App\Http\Controllers\Calls\Models\Calls_fields_change';
	var $model_fields_sorting_class = 'App\Http\Controllers\Calls\Models\Calls_fields_sorting';
	var $model_export_class = 'App\Http\Controllers\Calls\Models\Calls_export';
	var $model_export_batch_class = 'App\Http\Controllers\Calls\Models\Calls_export_batch';

	var $model_comments_class = 'App\Http\Controllers\Comments\Models\Comments';
	var $model_comments_tagged_class = 'App\Http\Controllers\Comments\Models\Comments_tagged';
	var $model_notifications_class = 'App\Http\Controllers\Comments\Models\Notifications';
	
	var $model_users_class = 'App\Http\Controllers\Users\Models\Users';
	var $model_teams_class = 'App\Http\Controllers\Users_teams\Models\Users_teams';
	var $model_teams_map_class = 'App\Http\Controllers\Users_teams\Models\Users_teams_map';
	var $model_data_roles_class = 'App\Http\Controllers\Users_teams\Models\Data_roles';
	var $model_users_information_class = 'App\Http\Controllers\Users\Models\UsersInformation';
	var $model_users_company_class = 'App\Modules\UsersCompany\Models\UsersCompany';
	var $model_users_pinned = 'App\Http\Controllers\Users\Models\UsersPinned';

	var $model_view_class 					= 'App\Http\Controllers\Calls\Models\Calls_view';
	var $model_view_checked_class 	= 'App\Http\Controllers\Calls\Models\Calls_view_checked';
  	var $model_view_criteria_class 	= 'App\Http\Controllers\Calls\Models\Calls_view_criteria';
  	var $model_view_fields_class 		= 'App\Http\Controllers\Calls\Models\Calls_view_fields';

	var $model_dropdown_class = 'App\Http\Controllers\Calls\Models\Dropdown';
	var $model_dropdown_options_class = 'App\Http\Controllers\Calls\Models\Dropdown_options';
	var $model_sys_rel_class = 'App\Http\Controllers\Calls\Models\Sys_rel';
	var $model_syslog_class = 'App\Http\Controllers\Sys\Models\Syslog';
	var $model_generate_custom_unique_id = 'App\Http\Controllers\Setting\Models\generate_custom_unique_id';
	var $model_modules_customize_id = 'App\Http\Controllers\Setting\Models\modules_customize_id';
	var $model_fields_option_class = 'App\Http\Controllers\Calls\Models\Calls_fields_condition';
	var $model_import_class 					= 'App\Http\Controllers\Calls\Models\Calls_import';

	#Contacts
	var $model_contacts_class 	= 'App\Http\Controllers\Contacts\Models\Contacts';
	var $model_contacts_custom_fields_class = 'App\Http\Controllers\Contacts\Models\Contacts_custom_fields';
	var $model_contacts_custom_values__class = 'App\Http\Controllers\Contacts\Models\Contacts_custom_values_';

	#org
	var $model_org_class 				= 'App\Http\Controllers\Org\Models\Org';
	var $model_org_custom_fields_class = 'App\Http\Controllers\Org\Models\Org_custom_fields';
	var $model_org_custom_values__class = 'App\Http\Controllers\Org\Models\Org_custom_values_';

	#Deals
	var $model_deals_class = 'App\Http\Controllers\Deals\Models\Deals';
	var $model_deals_custom_fields_class  = 'App\Http\Controllers\Deals\Models\Deals_custom_fields';
	var $model_deals_custom_values__class = 'App\Http\Controllers\Deals\Models\Deals_custom_values_';

	#leads 
	var $model_leads_class = 'App\Http\Controllers\Leads\Models\Leads';

	#Projects
	var $model_projects_class = 'App\Http\Controllers\Projects\Models\Projects';

	#Issue
	var $model_issue_class = 'App\Http\Controllers\Issue\Models\Issue';

	#Calls 
	var $model_calls_class = 'App\Http\Controllers\Calls\Models\Calls';

	#Tickets 
	var $model_tickets_class = 'App\Http\Controllers\Tickets\Models\Tickets';

	var $model_pbx_recording_class 	= 'App\Http\Controllers\Calls\Models\Calls_pbx_recording';
	var $model_pbx_incoming_class = 'App\Http\Controllers\Calls\Models\Pbx_incoming';

	# ADD BY GILANG PRATAMA
	# FOR ADD MODEL CUSTOM FIELDS COUNTRY, PROVINCES, REGENCIES, DISTRICTS
	var $model_provinces_class = 'App\Http\Controllers\Calls\Models\Provinces';
	var $model_country_class = 'App\Http\Controllers\Calls\Models\Country';
	var $model_regencies_class = 'App\Http\Controllers\Calls\Models\Regencies';
	var $model_districts_class = 'App\Http\Controllers\Calls\Models\Districts';
	# END

	var $model_tag_class = 'App\Http\Controllers\Tags\Models\Tags';
	var $model_tags_map_class = 'App\Http\Controllers\Tags\Models\TagsMap';	
	
	var $model_documents_class 		= 'App\Http\Controllers\Documents\Models\Documents';

	#Module Change
	var $model_module_change_class = 'App\Http\Controllers\Modules\Models\Modules_change';

	#Workflow
	var $model_workflow_class = 'App\Http\Controllers\Workflow\Models\Workflow';
	
	# Report
	var $model_widget_class 			= 'App\Http\Controllers\Reports\Models\Widget';
	var $model_widget_map_class 	= 'App\Http\Controllers\Reports\Models\WidgetMap';
	# End Report
	var $table_module = 'calls';
	var $pagesize;
	var $json_mode;

	public function __construct()
	{
		$this->pagesize = Config('setting.pagesize'); //Get default pagination in /apps/config/setting.php
		$this->json_mode = Config('setting.json_mode');
		// date_default_timezone_set("Asia/Jakarta");
	}

	public function example_uuid()
	{
		$uuid4 	= $this->uuid::uuid4()->toString();

		return $uuid4;
	}

	# Created By Prihan Firmanullah
  # 2019-12-20
  # For Save Customize Form
	public function moduleViewFieldsChecked($company_id=0, $users_id=0)
	{
		$result = array();
		$fields_name 		= array(
											$this->table_module."_view_fields.*",
											'b.*',
											);
		$data 	= $this->model_view_fields_class::select($fields_name)
										->leftjoin($this->table_module."_fields as b", $this->table_module."_view_fields.".$this->table_module."_fields_serial_id", "=", "b.".$this->table_module."_fields_serial_id")
										->where('users_id', '=', $users_id)
										->where('company_id', '=', $company_id)
										->where($this->table_module."_view_fields_type", "=", Config('setting.view_fields_type_core')) // Core Fields
										->groupBy($this->table_module."_view_fields.".$this->table_module."_fields_serial_id")
										->orderby($this->table_module.'_fields_sorting', 'ASC')
										->get();
		if (countCustom($data) > 0) {
			$result 	= $data->toArray();
		}

		return $result;
	}

	# Created By Prihan Firmanullah
  # 2019-12-20
  # For Save Customize Form
	public function moduleViewFieldsCstmChecked($company_id=0, $users_id=0)
	{
		$result 			= array();
		$fields_name 	= array(
									$this->table_module.'_view_fields.*', 
									'b.'.$this->table_module.'_custom_fields_serial_id', 
									'b.'.$this->table_module.'_custom_values_maps',
									'b.'.$this->table_module.'_custom_fields_name', 
									'b.'.$this->table_module.'_custom_fields_label', 
									'b.'.$this->table_module.'_custom_fields_data_type', 
									'b.'.$this->table_module.'_custom_fields_input_type', 
									'b.'.$this->table_module.'_custom_fields_function', 
									'b.'.$this->table_module.'_custom_fields_options', 
									'b.'.$this->table_module.'_custom_fields_validation', 
									'b.'.$this->table_module.'_custom_fields_sorting', 
									'b.'.$this->table_module.'_custom_fields_status', 
									'b.'.$this->table_module.'_custom_fields_readonly',
									'b.'.$this->table_module.'_custom_fields_default_value',
									);

		$data 	= $this->model_view_fields_class::select($fields_name)
										->leftjoin($this->table_module.'_custom_fields as b', $this->table_module.'_fields_serial_id', '=', 'b.'.$this->table_module.'_custom_fields_serial_id')
										->where($this->table_module.'_view_fields_type', '=', Config('setting.view_fields_type_custom')) // Fields custom
										->where($this->table_module.'_view_fields.company_id', '=', $company_id)
										->where('users_id', '=', $users_id)
										->where('b.company_id', '=', $company_id)
										->orderby($this->table_module.'_custom_fields_serial_id', 'ASC')
										->get();
		
		if (countCustom($data) > 0) 
		{
			$result 	= $data->toArray();
		}
		
		return $result;
	}

	public function GetCoreFieldsChange($dataCore=array(), $company_id=0)
	{
		foreach ($dataCore as $key => $value) 
		{
			$serial_id = $value[$this->table_module.'_fields_serial_id'];
			$get = $this->model_fields_change_class::where($this->table_module.'_fields_serial_id', '=', $serial_id)
																			->where('company_id', '=', $company_id)
																			->get();

			if( countCustom($get) > 0 )
			{
				$get 	= $get->toArray();

				$dataCore[$key][$this->table_module.'_fields_label'] 				= $get[0][$this->table_module.'_fields_change_label'];
				$dataCore[$key][$this->table_module.'_fields_validation'] 	= $get[0][$this->table_module.'_fields_change_validation'];
				$dataCore[$key][$this->table_module.'_fields_status'] 			= $get[0][$this->table_module.'_fields_change_status'];
				$dataCore[$key][$this->table_module.'_fields_options'] 			= $get[0][$this->table_module.'_fields_change_options'];
				$dataCore[$key][$this->table_module.'_fields_quick'] 				= $get[0][$this->table_module.'_fields_change_quick'];
				$dataCore[$key][$this->table_module.'_fields_status'] 				= $get[0][$this->table_module.'_fields_change_status'];
				$dataCore[$key][$this->table_module.'_fields_readonly'] 		= $get[0][$this->table_module.'_fields_change_readonly'];
				$dataCore[$key][$this->table_module.'_fields_default_value'] 	= $get[0][$this->table_module.'_fields_change_default_value'];
				if ( !isEmpty($get[0][$this->table_module.'_fields_change_input_type']) ) 
				{
					$dataCore[$key][$this->table_module.'_fields_input_type']	= $get[0][$this->table_module.'_fields_change_input_type'];
          			$dataCore[$key]['html'] = $get[0][$this->table_module.'_fields_change_input_type'];
				}
			}
		}

		$data = array_filter($dataCore, function($var) {
			return $var[$this->table_module.'_fields_status'] != 0;
		});

		$data = array_values($data);		

		return $data;
	}

	# Created By Prihan Firmanullah
  # 2019-12-20
  # For Save Customize Form
	public function moduleViewDefault()
	{
		$data 	= $this->model_fields_class::where($this->table_module.'_fields_status', '=', Config('setting.fields_status_active'))
																				->get()->toArray();
		$default 	= array(
									$this->table_module.'_name',
									$this->table_module.'_direction',
									$this->table_module.'_parent_type',
									$this->table_module.'_parent_id',
									$this->table_module.'_status',
									$this->table_module.'_date_start',
									$this->table_module.'_owner',
									'date_created',
									'date_modified'
									);
									
		$contents = [];
		$fieldColumnName = array_column($data, $this->table_module.'_fields_name');
		$getKeys = [];
		foreach ($default as $key => $fields_name)
		{
			$getKeys 	= array_search($fields_name, $fieldColumnName);
			if ($getKeys > -1) {
				$data[$getKeys][$this->table_module.'_view_fields_type'] = Config('setting.view_fields_type_core');
				$contents[] 	= $data[$getKeys];
			}
		}

		return $contents;
	}

	# Created By Fitri Mahardika
  	# 03-02-2020
  	# For List Data
	public function listData($criteria=array(), $fields=array(), $input=array(), $data_roles=TRUE, $data_filter=TRUE, $countdata='')
	{
		$sys = new sys();

		# DEFINED VARIABLE
		$listFieldsCustom 		= $fields['listFieldsCustom']; // Get Fields Custom
		$query_count 					= 0; // count query : default
		$fieldsTeamsOwners 		="";
		$tags_id ="";
		$cek_filter_is = FALSE;
		# END

		# LIST CORE FIELDS AND CUSTOM FIELDS (MERGE)
		$fieldsName 			= $this->select_fieldsName($fields); // list fields in core field and custom fields. 
		# END
		# CHANGE OWNER_ID TO OWNER_NAME
		$checkOwner 	= in_array($this->table_module.'.'.$this->table_module.'_owner', $fieldsName); // check if owner available in $fieldsName
		if($checkOwner === TRUE)
		{
			$fieldsName 	= array_merge($fieldsName, array('users.name as '.$this->table_module.'_owner')); 
			$fieldsTeamsOwners = ", ( SELECT Group_concat(users_teams.teams_name separator '|') from `users_teams` left join `users_teams_map` as `utm` on `utm`.`teams_serial_id` = `users_teams`.`teams_serial_id` where `users_teams`.`company_id` = ".$criteria['company_id']." and `users_teams`.`deleted` = 0 and `utm`.`users_id` = ".$this->table_module."_owner ) as owner_teams_name";
		}
		// $criteria['order_by'] = ($criteria['order_by'] == $this->table_module."_owner") ? "users.name" : $criteria['order_by']; // if order owner, then order by users.name ASC/DESC
		# END

		# CHANGE CREATED_ID TO CREATED_NAME
		$checkCreated 	= in_array($this->table_module.'.created_by', $fieldsName); // check if created_by available in $fieldsName
		if($checkCreated === TRUE)  
		{
			$fieldsName 	= array_merge($fieldsName, array('users_created.name as created_by')); 
		}
		// $criteria['order_by'] = ($criteria['order_by'] == "created_by") ? "users_created.name" : $criteria['order_by']; // if order created_by, then order by users_created.name ASC/DESC
		# END

		# CHANGE MODIFIED_ID TO MODIFIED_NAME
		$checkModified 	= in_array($this->table_module.'.modified_by', $fieldsName); // check if modified_by available in $fieldsName
		if($checkModified === TRUE)  
		{
			$fieldsName 	= array_merge($fieldsName, array('users_modified.name as modified_by')); 
		}
		// $criteria['order_by'] = ($criteria['order_by'] == "modified_by") ? "users_modified.name" : $criteria['order_by']; // if order modified_by, then order by users_modified.name ASC/DESC
		# END

		# CHANGE DEALS_SERIAL_ID TO DEALS_UUID and DEALS_NAME  
		$checkDealSerialId 	= in_array($this->table_module.'.deals_serial_id', $fieldsName); // check if owner available in $fieldsName
		if($checkDealSerialId === TRUE)  
		{
			$fieldsName 	= array_merge($fieldsName, array('deals.deals_uuid', 'deals.deals_name')); 
		} 
		# END 

		# CHANGE PROJECTS_SERIAL_ID TO PROJECTS_UUID and PROJECTS_NAME  
		$checkProjectsSerialId 	= in_array($this->table_module.'.projects_serial_id', $fieldsName); // check if projects available in $fieldsName
		if($checkProjectsSerialId === TRUE)  
		{
			$fieldsName 	= array_merge($fieldsName, array('projects.projects_uuid', 'projects.projects_name')); 
		} 
		# END 

		# CHANGE ISSUE_SERIAL_ID TO ISSUE_UUID and ISSUE_NAME  
		$checkIssueSerialId 	= in_array($this->table_module.'.issue_serial_id', $fieldsName); // check if issue available in $fieldsName
		if($checkIssueSerialId === TRUE)  
		{
			$fieldsName 	= array_merge($fieldsName, array('issue.issue_uuid', 'issue.issue_name')); 
		} 
		# END
		
		# CHANGE TICKETS_SERIAL_ID TO TICKETS_UUID and TICKETS_NAME  
		$checkTicketsSerialId 	= in_array($this->table_module.'.tickets_serial_id', $fieldsName); // check if tickets available in $fieldsName
		if($checkTicketsSerialId === TRUE)  
		{
			$fieldsName 	= array_merge($fieldsName, array('tickets.tickets_uuid', 'tickets.tickets_name')); 
		} 
		# END
		# 

		# CONVERT TO ROW QUERY FORMAT
		$fieldsNameConvert	= $this->convertFieldsName($fieldsName);
		# END 
		foreach($listFieldsCustom as $key =>$value){
			$fldsCstmMap = '';
			$fldsCstmName= '';
			if($listFieldsCustom[$key][$this->table_module."_custom_fields_input_type"] == "person")
			{
				$fldsCstmMap = $listFieldsCustom[$key][$this->table_module."_custom_values_maps"];	
				$fldsCstmName = $listFieldsCustom[$key][$this->table_module."_custom_fields_name"];
				$find = "mcv." . $fldsCstmMap . " as " . $fldsCstmName;
				$replace = "(
											CASE 
												WHEN sys_rel.rel_to_module = 'contacts' or sys_rel.rel_to_module = 'org' THEN 
												(SELECT CONCAT(COALESCE(contacts_first_name, ''),' ', COALESCE(contacts_last_name, '')) FROM contacts WHERE contacts_serial_id = mcv.".$fldsCstmMap." limit 1)
											END
										) as " . $fldsCstmName;
				$contacts_uuid = "( 
														CASE
															WHEN sys_rel.rel_to_module = 'contacts'  or sys_rel.rel_to_module = 'org' THEN 
															(SELECT contacts_uuid FROM contacts WHERE contacts_serial_id = mcv.".$fldsCstmMap." limit 1)
														END
														) as contacts_uuid_".$fldsCstmMap;
				$merge = $replace.','.$contacts_uuid;
				$fieldsNameConvert = str_replace($find,$merge,$fieldsNameConvert);
			}
			elseif($listFieldsCustom[$key][$this->table_module."_custom_fields_input_type"] == "leads")
			{
				$fldsCstmMap = $listFieldsCustom[$key][$this->table_module."_custom_values_maps"];	
				$fldsCstmName = $listFieldsCustom[$key][$this->table_module."_custom_fields_name"];
				$find = "mcv." . $fldsCstmMap . " as " . $fldsCstmName;
				$replace = "(SELECT CONCAT(COALESCE(leads.leads_first_name, ''),' ', COALESCE(leads.leads_last_name, '')) FROM leads WHERE leads.leads_serial_id = mcv.".$fldsCstmMap." LIMIT 1) as " . $fldsCstmName;
				$leads_uuid = "( SELECT leads_uuid FROM leads WHERE leads.leads_serial_id = mcv.".$fldsCstmMap." LIMIT 1
														) as leads_uuid_".$fldsCstmMap;
				$merge = $replace.','.$leads_uuid;
				$fieldsNameConvert = str_replace($find,$merge,$fieldsNameConvert);
			}
			elseif($listFieldsCustom[$key][$this->table_module."_custom_fields_input_type"] == "deals")
			{
				$fldsCstmMap = $listFieldsCustom[$key][$this->table_module."_custom_values_maps"];	
				$fldsCstmName = $listFieldsCustom[$key][$this->table_module."_custom_fields_name"];
				$find = "mcv." . $fldsCstmMap . " as " . $fldsCstmName;
				$replace = "(SELECT deals.deals_name FROM deals WHERE deals.deals_serial_id = mcv.".$fldsCstmMap." LIMIT 1) as " . $fldsCstmName;
				$deals_uuid = "( SELECT deals_uuid FROM deals WHERE deals.deals_serial_id = mcv.".$fldsCstmMap." LIMIT 1
														) as deals_uuid_".$fldsCstmMap;
				$merge = $replace.','.$deals_uuid;
				$fieldsNameConvert = str_replace($find,$merge,$fieldsNameConvert);
			}
			elseif($listFieldsCustom[$key][$this->table_module."_custom_fields_input_type"] == "organization")
			{
				$fldsCstmMap = $listFieldsCustom[$key][$this->table_module."_custom_values_maps"];	
				$fldsCstmName = $listFieldsCustom[$key][$this->table_module."_custom_fields_name"];
				$find = "mcv." . $fldsCstmMap . " as " . $fldsCstmName;
				$replace = "(SELECT org.org_name FROM org WHERE org.org_serial_id = mcv.".$fldsCstmMap." LIMIT 1) as " . $fldsCstmName;
				$organization_uuid = "( SELECT org_uuid FROM org WHERE org.org_serial_id = mcv.".$fldsCstmMap." LIMIT 1
														) as organization_uuid_".$fldsCstmMap;
				$merge = $replace.','.$organization_uuid;
				$fieldsNameConvert = str_replace($find,$merge,$fieldsNameConvert);
			}
			elseif($listFieldsCustom[$key][$this->table_module."_custom_fields_input_type"] == "teams")
			{
				$fldsCstmMap = $listFieldsCustom[$key][$this->table_module."_custom_values_maps"];	
				$fldsCstmName = $listFieldsCustom[$key][$this->table_module."_custom_fields_name"];
				$find = "mcv." . $fldsCstmMap . " as " . $fldsCstmName;
				$replace = "(SELECT users_teams.teams_name FROM users_teams WHERE users_teams.teams_serial_id = mcv.".$fldsCstmMap." LIMIT 1) as " . $fldsCstmName;
				$teams_uuid = "( SELECT teams_uuid FROM users_teams WHERE users_teams.teams_serial_id = mcv.".$fldsCstmMap." LIMIT 1
														) as teams_uuid_".$fldsCstmMap;
				$merge = $replace.','.$teams_uuid;
				$fieldsNameConvert = str_replace($find,$merge,$fieldsNameConvert);
			}
			elseif($listFieldsCustom[$key][$this->table_module."_custom_fields_input_type"] == "users")
			{
				$fldsCstmMap = $listFieldsCustom[$key][$this->table_module."_custom_values_maps"];	
				$fldsCstmName = $listFieldsCustom[$key][$this->table_module."_custom_fields_name"];
				$find = "mcv." . $fldsCstmMap . " as " . $fldsCstmName;
				$replace = "(SELECT users.name FROM users WHERE users.id = mcv.".$fldsCstmMap." LIMIT 1) as " . $fldsCstmName;
				$users_uuid = "( SELECT users_uuid FROM users WHERE users.id = mcv.".$fldsCstmMap." LIMIT 1
														) as users_uuid_".$fldsCstmMap;
				$merge = $replace.','.$users_uuid;
				$fieldsNameConvert = str_replace($find,$merge,$fieldsNameConvert);
			}
		}
		# SELECT QUERY DYNAMIC BY $fieldsName
		$b = 'b';
		$leadsConvert = strtotime("now");
		$fieldsNameSysRel = "sys_rel.rel_to_module as ".$this->table_module."_parent_type, sys_rel.rel_to_id as ".$this->table_module."_parent_id,
											(
												CASE   
												  WHEN sys_rel.rel_to_module = 'leads' THEN ( SELECT CONCAT(COALESCE(leads.leads_first_name, ''),' ', COALESCE(leads.leads_last_name, '')) FROM leads WHERE leads.leads_serial_id = sys_rel.rel_to_id AND company_id =".$criteria['company_id']." AND deleted = ".Config('setting.NOT_DELETED')." LIMIT 1)  
													WHEN sys_rel.rel_to_module = 'contacts' THEN ( SELECT CONCAT(COALESCE(contacts.contacts_first_name, ''),' ', COALESCE(contacts.contacts_last_name, '')) FROM contacts WHERE contacts.contacts_serial_id = sys_rel.rel_to_id AND company_id =".$criteria['company_id']." AND deleted = ".Config('setting.NOT_DELETED')." LIMIT 1) 
													WHEN sys_rel.rel_to_module = 'org'  THEN ( SELECT org_name FROM org WHERE org.org_serial_id = sys_rel.rel_to_id AND company_id =".$criteria['company_id']." AND deleted = ".Config('setting.NOT_DELETED')." LIMIT 1) 
													WHEN sys_rel.rel_to_module = 'projects' THEN ( SELECT projects_name FROM projects WHERE projects.projects_serial_id = sys_rel.rel_to_id AND company_id =".$criteria['company_id']." AND deleted = ".Config('setting.NOT_DELETED')." LIMIT 1 )
													WHEN sys_rel.rel_to_module is NULL THEN ''
												END
												) as ".$this->table_module."_parent_name,
												(
													CASE
														WHEN sys_rel.rel_to_module = 'leads' THEN ( SELECT leads_unique_id FROM leads WHERE leads.leads_serial_id = sys_rel.rel_to_id AND company_id =".$criteria['company_id']." AND deleted = ".Config('setting.NOT_DELETED')." LIMIT 1)
														WHEN sys_rel.rel_to_module = 'contacts' THEN ( SELECT contacts_unique_id FROM contacts WHERE contacts.contacts_serial_id = sys_rel.rel_to_id AND company_id =".$criteria['company_id']." AND deleted = ".Config('setting.NOT_DELETED')." LIMIT 1)
														WHEN sys_rel.rel_to_module = 'org' THEN ( SELECT org_unique_id FROM org WHERE org.org_serial_id = sys_rel.rel_to_id AND company_id =".$criteria['company_id']." AND deleted = ".Config('setting.NOT_DELETED')." LIMIT 1)
														WHEN sys_rel.rel_to_module is NULL THEN ''
													END
													) as ".$this->table_module."_parent_unique";

		$query = $this->model_class::select(DB::raw($fieldsNameConvert.",".$fieldsNameSysRel.$fieldsTeamsOwners));
		# END 

		// $query->having($this->table_module."_parent_name",'!=',$leadsConvert);
		# LEFT JOIN WITH SYS REL
		$query->leftjoin('sys_rel', function($join) use ($criteria)
		        { 
		            $join->on('sys_rel.rel_from_id', '=', $this->table_module.'.'.$this->table_module.'_serial_id')
		            ->where('sys_rel.rel_from_module', '=', $this->table_module);
		        });
		if ($criteria['order_by'] == $this->table_module."_parent_id" || $criteria['order_by'] == $this->table_module . "." . $this->table_module."_parent_id")
		{
			$criteria['order_by'] 	= $this->table_module."_parent_id";
		}
		# END 

		# LEFT JOIN WITH CUSTOM FIELDS
		$temp_alias = array();
		if (countCustom($listFieldsCustom) > 0) // 	if fields custom available
		{

			$query->leftjoin($this->table_module.'_custom_values_ as mcv', function($join) use ($criteria)
							{
								$join->on('mcv.'.$this->table_module.'_serial_id', '=', $this->table_module.'.'.$this->table_module.'_serial_id')
								->where('mcv.company_id', '=', $criteria['company_id']);
							});

		}
			
		# IF OWNER AVAILABLE IN $fieldsName
		if ($checkOwner === TRUE || $data_filter == TRUE || (isset($_GET['subaction']) && $_GET['subaction'] == "search"))
		{
			//run if owner show in listing OR 
			//run if data_filter TRUE OR
			//run if search feature true
			$query->leftjoin('users', 'users.id', '=', $this->table_module.'.'.$this->table_module.'_owner');
		}

		# IF CREATED AVAILABLE IN $fieldsName
		if ($checkCreated === TRUE || (isset($_GET['subaction']) && $_GET['subaction'] == "search"))
		{
			//run if created show in listing OR 
			//run if search feature true
			$query->leftjoin('users as users_created', 'users_created.id', '=', $this->table_module.'.created_by');
		}

		# IF MODIFIED AVAILABLE IN $fieldsName
		if ($checkModified === TRUE || (isset($_GET['subaction']) && $_GET['subaction'] == "search"))
		{
			//run if modified show in listing OR 
			//run if search feature true
			$query->leftjoin('users as users_modified', 'users_modified.id', '=', $this->table_module.'.modified_by');
		}

		# IF DEALS_SERIAL_ID AVAILABLE IN $fieldsName
		$checkDealSerialId 	= in_array($this->table_module.'.deals_serial_id', $fieldsName); // check if owner available in $fieldsName
		if($checkDealSerialId === TRUE)  
		{
			$query->leftjoin('deals', 'deals.deals_serial_id', '=', $this->table_module.'.deals_serial_id');
		}

		# IF PROJECTS_SERIAL_ID AVAILABLE IN $fieldsName
		$checkProjectsSerialId 	= in_array($this->table_module.'.projects_serial_id', $fieldsName); // check if projects available in $fieldsName
		if($checkProjectsSerialId === TRUE)  
		{
			$query->leftjoin('projects', 'projects.projects_serial_id', '=', $this->table_module.'.projects_serial_id');
		}

		# IF ISSUE_SERIAL_ID AVAILABLE IN $fieldsName
		$checkIssueSerialId 	= in_array($this->table_module.'.issue_serial_id', $fieldsName); // check if issue available in $fieldsName
		if($checkIssueSerialId === TRUE)  
		{
			$query->leftjoin('issue', 'issue.issue_serial_id', '=', $this->table_module.'.issue_serial_id');
		}

		# IF TICKETS_SERIAL_ID AVAILABLE IN $fieldsName
		$checkTicketsSerialId 	= in_array($this->table_module.'.tickets_serial_id', $fieldsName); // check if issue available in $fieldsName
		if($checkTicketsSerialId === TRUE)  
		{
			$query->leftjoin('tickets', 'tickets.tickets_serial_id', '=', $this->table_module.'.tickets_serial_id');
		}

		# DATA ROLES
		$roles 				= $this->get_roles($this->table_module, $criteria['company_id'], $criteria['users_id']);
		$myRecordOnlyCheck 	= $this->myRecordOnlyCheck($criteria['company_id'], $criteria['users_id']); //if filter view " (You) " Checked
		if ($data_roles == TRUE && countCustom($roles['contents']) > 0 && $myRecordOnlyCheck === FALSE) 
		{
			//$roles['contents'] is container member inside roles by users_id, example : team view then $roles['contents'] = array('11', '12', '13') 
			//if member != empty , then running this block
			//more detail about $roles, please check get_roles()
			$query->whereIn($this->table_module.'.'.$this->table_module.'_owner', $roles['contents']);
		}

			

		# FILTER FEATURE
		if ($data_filter == TRUE)
		{
			//Running filter when $data_filter TRUE
			$filterView 	= $this->viewChecked($criteria['company_id'], $criteria['users_id']); // checked filter view active
			$filterViewName = $filterView[$this->table_module.'_view_name'];
			
			$filterCriteria = $this->generate_view($filterView, $criteria['company_id'], $criteria['users_id']); // get the selected filter 	

			$filterCriteria = $this->data_search($filterCriteria, $criteria['company_id'], $temp_alias, $listFieldsCustom, $b); // generate format for use filter feature
			
			if(isset($filterCriteria['temp']))
			{
				if (countCustom($listFieldsCustom) == 0) // if fields custom not available
				{
					$query->leftjoin($this->table_module.'_custom_values_ as mcv', function($join) use ($criteria)
								{
									$join->on('mcv.'.$this->table_module.'_serial_id', '=', $this->table_module.'.'.$this->table_module.'_serial_id')
									->where('mcv.company_id', '=', $criteria['company_id']);
								});
				}
			}
			

			$filterCriteriaCore 	= $filterCriteria['result']['core']; // filter by core field type
			$filterCriteriaDate 	= $filterCriteria['result']['date']; // filter by date type
			$filterCriteriaCustom = $filterCriteria['result']['custom']; // filter by custom field type
			$filterCriteriaDateCustom 	= $filterCriteria['result']['date_custom'];

			if ($myRecordOnlyCheck === TRUE)
		  	{
		  	// if user choosen filter "You", then owner by sesson users_id login
				$query->where($this->table_module.'_owner', '=', $criteria['users_id']);
			}
			elseif (countCustom($filterView) > 0 && $filterViewName != 'Everyone' && $filterViewName != 'You') 
			{
				// get users statuc active or deactive
				$get_users = $this->model_users_class::select('users_status')
									->leftjoin('users_company as comp','comp.users_id','=','users.id')
									->where('id','=',$filterView['users_id'])
									->where('company_id','=',$filterView['company_id'])
									->first();

				// for check if users deactive
				if($get_users['users_status'] == 'deactive')
				{
					// get current users active
					$get_active = $this->model_view_class::select($this->table_module.'_view_serial_id', $this->table_module.'_view_name')
										->where($this->table_module.'_view_name','=','Everyone')
										->where('users_id','=', $criteria['users_id'])
										->where('company_id','=', $criteria['company_id'])
										->first();

					// update contact_view_serial_id into default
					$update = $this->model_view_checked_class::where('users_id','=',$criteria['users_id'])
									->where('company_id','=',$criteria['company_id'])
									->update([$this->table_module.'_view_serial_id' => $get_active[$this->table_module.'_view_serial_id']]);

					// change filter into default/Everyone when load data
					$filterView[$this->table_module.'_view_serial_id'] = $get_active[$this->table_module.'_view_serial_id'];
					$filterView[$this->table_module.'_view_name'] = 'Everyone';

					// change criteria into empty
					$filterCriteriaCore = array();
					$filterCriteriaDate = array();
					$filterCriteriaCustom = array();
					$filterCriteriaDateCustom = array();
				}
				
				$query_count = 1; // count query with left join
				//if user choosen filter, except filter 'Everyone' and 'You' 
				$checkFilterByOwner = in_array($this->table_module.'.'.$this->table_module.'_owner', array_column($filterCriteriaCore, 0)); 

				if ($checkFilterByOwner == TRUE) // if filter data by owner
				{
					$key_filter_owner = array_search($this->table_module.'.'.$this->table_module.'_owner', array_column($filterCriteriaCore, 0)); // get key, position owner in array		      
					if (is_array($filterCriteriaCore[$key_filter_owner][2]) && countCustom($filterCriteriaCore[$key_filter_owner][2]) > 0)
					{
						// if filter data by multi owner
						if ($filterCriteriaCore[$key_filter_owner][1] == "=") // when owner by IS 
						{
							$query->whereIn($this->table_module.'.'.$this->table_module.'_owner', $filterCriteriaCore[$key_filter_owner][2]);

						}elseif ($filterCriteriaCore[$key_filter_owner][1] == "!=") // when owner by isnt
						{
							$query->whereNotIn($this->table_module.'.'.$this->table_module.'_owner', $filterCriteriaCore[$key_filter_owner][2]);
						}
					}else
					{
						// if filter data by single owner
						$query->where('users.name', $filterCriteriaCore[$key_filter_owner][1], $filterCriteriaCore[$key_filter_owner][2]);
					}			
					unset($filterCriteriaCore[$key_filter_owner]); // remove owner in array, by position key
				}
				if (countCustom($filterCriteriaDate) > 0) // if filter data by date
				{
					$date_between 	= $this->date_between($filterCriteriaDate);
					$query->whereRaw($date_between);
				}
				if (countCustom($filterCriteriaDateCustom) > 0) // if filter data by date
				{
					$date_between 	= $this->date_between_custom($filterCriteriaDateCustom);
					$query->whereRaw($date_between);
				}
				if (countCustom($filterCriteriaCustom) > 0 ) // if filter data by custom fields
				{
					foreach ($filterCriteriaCustom as $value) 
					{
						if ($value[1] == "=" && is_array($value[2])) // operator is 
						{
							$query->whereIn($value[0], $value[2]);
						}
						elseif ($value[1] == "!=" && is_array($value[2])) // operator isn't
						{
							$query->whereNotIn($value[0], $value[2]);
						}
						elseif ( $value[1] === '==' ) // operator is_empty
						{
							$query->where(function ($query) use ($value) 
							{
								$query->where($value[0], '=', $value[2]);
								$query->orWhereNull($value[0]);
							});
						}
						elseif ( $value[1] === '!==' ) // operator is_not_empty
						{
							$query->where(function ($query) use ($value) 
							{
								$query->where($value[0], '!=', $value[2]);
								$query->whereNotNull($value[0]);
							});
						}
						elseif($value[3] === "person")
						{
							$a = $value[2];
							$b = $value[1];
							if($value[4] == "starts_with"){
								$query->leftjoin('contacts','contacts.contacts_serial_id','=',$value[0])
											->where(function ($key) use ($a, $b) 
											{
												$key->where('contacts.contacts_first_name', $b, $a);
											});
							}elseif($value[4] == "ends_with")
							{
								$query->leftjoin('contacts','contacts.contacts_serial_id','=',$value[0])
											->where(function ($key) use ($a, $b) 
											{
												$key->where('contacts.contacts_last_name',$b, $a);
											});
							}else{
								$query->where($value[0], $value[1], $value[2]);
							}												 			
						}
						elseif($value[3] === "leads")
						{
							$a = $value[2];
							$b = $value[1];
							if($value[4] == "starts_with"){
								$query->leftjoin('leads','leads.leads_serial_id','=',$value[0])
											->where(function ($key) use ($a, $b) 
											{
												$key->where('leads.leads_first_name', $b, $a);
											});
							}elseif($value[4] == "ends_with")
							{
								$query->leftjoin('leads','leads.leads_serial_id','=',$value[0])
											->where(function ($key) use ($a, $b) 
											{
												$key->where('leads.leads_last_name',$b, $a);
											});
							}else{
								$query->where($value[0], $value[1], $value[2]);
							}												 			
						}
						elseif($value[3] === "deals")
						{
							$a = $value[2];
							$b = $value[1];
							if($value[4] == "starts_with"){
								$query->leftjoin('deals','deals.deals_serial_id','=',$value[0])
											->where(function ($key) use ($a, $b) 
											{
												$key->where('deals.deals_name', $b, $a);
											});
							}else{
								$query->where($value[0], $value[1], $value[2]);
							}												 			
						}
						elseif($value[3] === "organization")
						{
							$a = $value[2];
							$b = $value[1];
							if($value[4] == "starts_with"){
								$query->leftjoin('org','org.org_serial_id','=',$value[0])
											->where(function ($key) use ($a, $b) 
											{
												$key->where('org.org_name', $b, $a);
											});
							}else{
								$query->where($value[0], $value[1], $value[2]);
							}												 			
						}
						elseif($value[3] === "teams")
						{
							$a = $value[2];
							$b = $value[1];
							if($value[4] == "starts_with"){
								$query->leftjoin('users_teams','users_teams.teams_serial_id','=',$value[0])
											->where(function ($key) use ($a, $b) 
											{
												$key->where('users_teams.teams_name', $b, $a);
											});
							}else{
								$query->where($value[0], $value[1], $value[2]);
							}												 			
						}
						elseif($value[3] === "users")
						{
							$a = $value[2];
							$b = $value[1];
							if($value[4] == "starts_with"){
								$query->leftjoin('users','users.id','=',$value[0])
											->where(function ($key) use ($a, $b) 
											{
												$key->where('users.name', $b, $a);
											});
							}else{
								$query->where($value[0], $value[1], $value[2]);
							}												 			
						}
						else
						{
							$query->where($value[0], $value[1], $value[2]); // operator contains, start_with and end_with
						}
					}
				}
				$checkParentType 	= in_array($this->table_module.'.'.$this->table_module.'_parent_type', array_column($filterCriteriaCore, 0));
				if ($checkParentType == TRUE) // search data by calls_parent_type
				{
					$keyParentType 	= array_search($this->table_module.'.'.$this->table_module.'_parent_type', array_column($filterCriteriaCore, 0));
					$parentTypeOpp 			= $filterCriteriaCore[$keyParentType][1]; // get operator
					$parentTypeKeyword 	= $filterCriteriaCore[$keyParentType][2]; // get keyword
					if (is_array($parentTypeKeyword)) {
	
						$checkParentId 	= in_array($this->table_module.'.'.$this->table_module.'_parent_id', array_column($filterCriteriaCore, 0));
						if ($checkParentId) {
							$filterCriteriaCore = array_values($filterCriteriaCore); // reset key array, to 0 
							$keyParentId 		= array_search($this->table_module.'.'.$this->table_module.'_parent_id', array_column($filterCriteriaCore, 0));
							$parentIdOpp 		= $filterCriteriaCore[$keyParentId][1]; // get operator
							$parentIdKeyword 	= $filterCriteriaCore[$keyParentId][2]; // get keyword
							
							$whererelated = false;
							$cek_filter_is = TRUE;
							$query->where(function($whereparent) use ($parentTypeKeyword, $parentTypeOpp, $parentIdOpp, $parentIdKeyword) {
								foreach ($parentTypeKeyword as $key => $value) {
									$whereparent->{$key==0 ? 'where' : 'orWhere'}(function($whereparentid) use ($key, $parentTypeOpp, $value, $parentIdOpp, $parentIdKeyword){
										$whereparentid->where('sys_rel.rel_to_module', $parentTypeOpp, $value);
										if ($parentIdOpp <> "LIKE" AND $parentIdOpp <> "NOT LIKE") {
											$whererelated = true;
											$whereparentid->whereIn('sys_rel.rel_to_id', $parentIdKeyword);
										}
									});
								}		
							});

							if ($whererelated) {
								/* relatedt to / parent id berisikan id / menggunakan operator is */
								unset($filterCriteriaCore[$keyParentId]);
							}
						} else {
							$query->where(function($whereparent) use ($parentTypeKeyword, $parentTypeOpp) {
								foreach ($parentTypeKeyword as $key => $value) {
									$whereparent->{$key==0 ? 'where' : 'orWhere'}('sys_rel.rel_to_module', '=', $value);
								}	
							});
						}
					} else {
						$query->where('sys_rel.rel_to_module', $parentTypeOpp, $parentTypeKeyword);
					}
					unset($filterCriteriaCore[$keyParentType]);
				}
	
				$checkParentId 	= in_array($this->table_module.'.'.$this->table_module.'_parent_id', array_column($filterCriteriaCore, 0));
				if ($checkParentId == TRUE)  // search data by calls_parent_id
				{
					$filterCriteriaCore = array_values($filterCriteriaCore); // reset key array, to 0 
					$keyParentId 		= array_search($this->table_module.'.'.$this->table_module.'_parent_id', array_column($filterCriteriaCore, 0));
					$parentIdOpp 		= $filterCriteriaCore[$keyParentId][1]; // get operator
					$parentIdKeyword 	= $filterCriteriaCore[$keyParentId][2]; // get keyword
					
					if ($parentIdOpp == "LIKE" OR $parentIdOpp == "NOT LIKE") {
						$query->having($this->table_module.'_parent_name', $parentIdOpp, $parentIdKeyword);
					}elseif($parentIdOpp == "=" AND $cek_filter_is == FALSE){
						if(!is_array($parentIdKeyword))
						{
							$parentIdKeyword = explode(" ",$parentIdKeyword);
						}
						$query->wherein('sys_rel.rel_to_id', $parentIdKeyword);
					}elseif($parentIdOpp == "!="){
						if(is_numeric($parentIdKeyword))
						{
							$query->whereNotin('sys_rel.rel_to_id', $parentIdKeyword);
						}else{
							$query->whereNotNull('sys_rel.rel_to_id');
						}
					}elseif($parentIdOpp == "=="){
						$query->whereNull('sys_rel.rel_to_id');
					}
					
					unset($filterCriteriaCore[$keyParentId]);
				}

				if ( countCustom($filterCriteriaCore) > 0 ) 
				{
					foreach ($filterCriteriaCore as $key => $value) 
					{
						if ($value[1] == "=" && is_array($value[2])) // operator is 
						{
							$query->whereIn($value[0], $value[2]);
						}
						elseif ($value[1] == "!=" && is_array($value[2])) // operator isn't
						{
							$query->whereNotIn($value[0], $value[2]);
						}
						elseif ( $value[1] === '==' ) 
						{
							$query->where(function ($query) use ($value) 
							{
								$query->where($value[0], '=', $value[2]);
								$query->orWhereNull($value[0]);
							});
						}
						elseif ( $value[1] === '!==' ) 
						{
							$query->where(function ($query) use ($value) 
							{
								$query->where($value[0], '!=', $value[2]);
								$query->whereNotNull($value[0]);
							});
						}
						elseif ($value[1] == "=" && ($value[0] == $this->table_module.".date_created" || $value[0] == $this->table_module.".date_modified" || $value[0] == $this->table_module.".".$this->table_module."_date_start" || $value[0] == $this->table_module.".".$this->table_module."_date_end"))
						{
							$date_range = explode(" - ", $value[2]);
							if (countCustom($date_range) > 1)
							{
								$date_start = date('Y-m-d', strtotime($date_range[0])).' 00:00:00'; 
								$date_end 	= date('Y-m-d', strtotime($date_range[1])).' 23:59:59';

								$query->whereBetween($value[0], [$date_start, $date_end]);
							}
						}
						elseif ($value[1] == "!=" && ($value[0] == $this->table_module.".date_created" || $value[0] == $this->table_module.".date_modified" || $value[0] == $this->table_module.".".$this->table_module."_date_start" || $value[0] == $this->table_module.".".$this->table_module."_date_end"))
						{
							$date_range = explode(" - ", $value[2]);
							if (countCustom($date_range) > 1)
							{
								$date_start = date('Y-m-d', strtotime($date_range[0])).' 00:00:00'; 
								$date_end 	= date('Y-m-d', strtotime($date_range[1])).' 23:59:59';

								$query->whereNotBetween($value[0], [$date_start, $date_end]);
							}
						}
						else
						{
							$query->where($value[0], $value[1], $value[2]); // search data in core fields
						}
					}
				}
				// end
			}
		}
		 # Tags Fiter
		 if (isset($input['tags_id']) && $input['tags_id'] != null)
		 {
				$query->leftjoin('tags_map', 'tags_map.tags_map_data_id', '=', $this->table_module.'.'.$this->table_module.'_serial_id')
											->where('tags_map.tags_map_module_name',$this->table_module)
											->where('tags_map.tags_serial_id',$input['tags_id']);

				if (isset($input['date_start']) && !empty($input['date_start']) && isset($input['date_end']) && !empty($input['date_end'])) {
					$date_start = $input['date_start']." 00:00:00";
					$date_end = $input['date_end']." 23:59:59";
					$query->where('tags_map.date_created', '>=', $date_start)
								->where('tags_map.date_created', '<=', $date_end);
				}
				$query->get();
		 }

		# SEARCH FEATURE
		if (isset($_GET['subaction']) && $_GET['subaction'] == "search")
		{
			$query_count = 1; // count query with left join
			
			//ADD BY AGUNG -> 15/03/2019 -> SEARCHING OWNER BY TEAMS
			// HANDLE SEACH DEALS OWNER BY TEAM
			if(isset($input[$this->table_module.'_owner_opp']) AND isset($input[$this->table_module.'_owner']))
			{
				if($input[$this->table_module.'_owner_opp'] == "is" OR $input[$this->table_module.'_owner_opp'] == "isnt")
				{
					if(is_array($input[$this->table_module.'_owner']))
					{
						if(countCustom($input[$this->table_module.'_owner']) > 0 OR !empty($input[$this->table_module.'_owner']))
						{
							$search_owner = array();
							foreach($input[$this->table_module.'_owner'] as $key_owner => $val_owner)
							{
								$owner_id = $val_owner;
								if (is_array($val_owner)) 
								{
									$owner_id = $val_owner[0];
								}
								$search_owner[$key_owner] = explode("!@#$%^&*()", $owner_id);
								if(isset($search_owner[$key_owner][1]))
								{
									$input[$this->table_module.'_owner'][$key_owner] = $search_owner[$key_owner][1];
								}
							}
	
						}
					}
					else
					{
						$search_owner = explode("!@#$%^&*()", $input[$this->table_module.'_owner']);
						if(isset($search_owner[1]))
						{
							$input[$this->table_module.'_owner'] = array($search_owner[1]);
						}
					}
				}

			}
			// END HANDLE SEACH DEALS OWNER BY TEAM
			//when use search feature, running this block code
			$searchCriteria 		= $this->data_search($input, $criteria['company_id']);
			$searchCriteriaCore 	= $searchCriteria['result']['core']; // filter by core field type
			$searchCriteriaDate 	= $searchCriteria['result']['date']; // filter by type date
			$searchCriteriaCustom 	= $searchCriteria['result']['custom']; // filter by custom field type
			$searchCriteriaDateCustom = $searchCriteria['result']['date_custom']; // filter by custom field type

     	 	$checkSearchByOwner = in_array($this->table_module.'.'.$this->table_module.'_owner', array_column($searchCriteriaCore, 0)); 
			if ($checkSearchByOwner == TRUE) // if search data by owner
			{
				$key_search_owner = array_search($this->table_module.'.'.$this->table_module.'_owner', array_column($searchCriteriaCore, 0)); // get key, position owner in array	      
				if (is_array($searchCriteriaCore[$key_search_owner][2]) && countCustom($searchCriteriaCore[$key_search_owner][2]) > 0)
				{
					// if search data by multi owner
					if ($searchCriteriaCore[$key_search_owner][1] == "=") // when owner by IS 
					{
						$query->whereIn($this->table_module.'.'.$this->table_module.'_owner', $searchCriteriaCore[$key_search_owner][2]);

					}elseif ($searchCriteriaCore[$key_search_owner][1] == "!=") // when owner by isnt
					{
						$query->whereNotIn($this->table_module.'.'.$this->table_module.'_owner', $searchCriteriaCore[$key_search_owner][2]);
					}
				}else
				{
					// if search data by single owner
					$query->where('users.name', $searchCriteriaCore[$key_search_owner][1], $searchCriteriaCore[$key_search_owner][2]);
				}			
				// unset($searchCriteriaCore[$key_search_owner]); // remove owner in array, by position key
				array_splice($searchCriteriaCore, $key_search_owner, 1);
			}

			$checkSearchByCreated = in_array($this->table_module.'.created_by', array_column($searchCriteriaCore, 0)); 
			if ($checkSearchByCreated == TRUE) // if search data by created
			{
				$key_search_created = array_search($this->table_module.'.created_by', array_column($searchCriteriaCore, 0)); // get key, position created in array
				if (is_array($searchCriteriaCore[$key_search_created][2]) && countCustom($searchCriteriaCore[$key_search_created][2]) > 0)
				{
					// if search data by multi created
					if ($searchCriteriaCore[$key_search_created][1] == "=") // when owner by IS 
					{
						$query->whereIn($this->table_module.'.created_by', $searchCriteriaCore[$key_search_created][2]);

					}elseif ($searchCriteriaCore[$key_search_created][1] == "!=") // when owner by isnt
					{
						$query->whereNotIn($this->table_module.'.created_by', $searchCriteriaCore[$key_search_created][2]);
					}
				}else
				{
					// if search data by single created
					$query->where('users_created.name', $searchCriteriaCore[$key_search_created][1], $searchCriteriaCore[$key_search_created][2]);
				}			
				// unset($searchCriteriaCore[$key_search_created]); // remove created in array, by position key
				array_splice($searchCriteriaCore, $key_search_created, 1);

			}

			$checkSearchByModified = in_array($this->table_module.'.modified_by', array_column($searchCriteriaCore, 0)); 
			if ($checkSearchByModified == TRUE) // if search data by modified
			{
				$key_search_modified = array_search($this->table_module.'.modified_by', array_column($searchCriteriaCore, 0)); // get key, position modified in array
				if (is_array($searchCriteriaCore[$key_search_modified][2]) && countCustom($searchCriteriaCore[$key_search_modified][2]) > 0)
				{
					// if search data by multi modified
					if ($searchCriteriaCore[$key_search_modified][1] == "=") // when owner by IS 
					{
						$query->whereIn($this->table_module.'.modified_by', $searchCriteriaCore[$key_search_modified][2]);

					}elseif ($searchCriteriaCore[$key_search_modified][1] == "!=") // when owner by isnt
					{
						$query->whereNotIn($this->table_module.'.modified_by', $searchCriteriaCore[$key_search_modified][2]);
					}
				}else
				{
					// if search data by single modified
					$query->where('users_modified.name', $searchCriteriaCore[$key_search_modified][1], $searchCriteriaCore[$key_search_modified][2]);
				}			
				// unset($searchCriteriaCore[$key_search_modified]); // remove modified in array, by position key
				array_splice($searchCriteriaCore, $key_search_modified, 1);
			}

			if (countCustom($searchCriteriaDate) > 0) // if search data by date
			{
				$date_between 	= $this->date_between($searchCriteriaDate);
				$query->whereRaw($date_between);
			}

			if (countCustom($searchCriteriaDateCustom) > 0) // if search data by date
			{
				$date_between_custom 	= $this->date_between_custom($searchCriteriaDateCustom);

				$query->whereRaw($date_between_custom);
			}

			// Update By Rendi 11.03.2019
			if (countCustom($searchCriteriaCustom) > 0 ) // if search data by custom fields
			{
				foreach ($searchCriteriaCustom as $value) 
				{
					if ($value[1] == "=" && is_array($value[2])) // operator is 
					{
						// only for custom multipleoption
						$fields = explode('.', $value[0]);
						$fields_type = $this->model_custom_fields_class::select($this->table_module.'_custom_fields_input_type')
																		->where($this->table_module.'_custom_values_maps','=',$fields[1])
																		->where('company_id','=',$criteria['company_id'])
																		->first();
						if ($fields_type[$this->table_module.'_custom_fields_input_type'] === 'multipleoption') 
						{
							$query->where(function ($query) use ($value) 
							{
								foreach ($value[2] as $value2) 
								{
									$query->orwhere($value[0], 'LIKE', '%'.$value2.'%');
								}
							});
						}// end
						else
						{
							$query->whereIn($value[0], $value[2]);
						}
					}
					elseif ($value[1] == "IN") // operator isn't
					{
						$query->whereIn($value[0],$value[2]);
					}
					elseif ($value[1] == "!=" && is_array($value[2])) // operator isn't
					{
						$query->whereNotIn($value[0], $value[2]);
					}elseif ($value[2] === "%%" && $value[3] !== 'multipleoption') {

					}
					elseif($value[3] === "person")
					{
						$a = $value[2];
						$b = $value[1];
						if($value[4] == "starts_with"){
							$query->leftjoin('contacts','contacts.contacts_serial_id','=',$value[0])
										->where(function ($key) use ($a, $b) 
										{
											$key->where('contacts.contacts_first_name', $b, $a);
										});
						}elseif($value[4] == "ends_with")
						{
							$query->leftjoin('contacts','contacts.contacts_serial_id','=',$value[0])
										->where(function ($key) use ($a, $b) 
										{
											$key->where('contacts.contacts_last_name',$b, $a);
										});
						}else{
							$query->where($value[0], $value[1], $value[2]);
						}												 			
					}
					elseif($value[3] === "leads")
					{
						$a = $value[2];
						$b = $value[1];
						if($value[4] == "starts_with"){
							$query->leftjoin('leads','leads.leads_serial_id','=',$value[0])
										->where(function ($key) use ($a, $b) 
										{
											$key->where('leads.leads_first_name', $b, $a);
										});
						}elseif($value[4] == "ends_with")
						{
							$query->leftjoin('leads','leads.leads_serial_id','=',$value[0])
										->where(function ($key) use ($a, $b) 
										{
											$key->where('leads.leads_last_name',$b, $a);
										});
						}else{
							$query->where($value[0], $value[1], $value[2]);
						}												 			
					}
					elseif($value[3] === "deals")
					{
						$a = $value[2];
						$b = $value[1];
						if($value[4] == "starts_with"){
							$query->leftjoin('deals','deals.deals_serial_id','=',$value[0])
										->where(function ($key) use ($a, $b) 
										{
											$key->where('deals.deals_name', $b, $a);
										});
						}else{
							$query->where($value[0], $value[1], $value[2]);
						}												 			
					}
					elseif($value[3] === "organization")
					{
						$a = $value[2];
						$b = $value[1];
						if($value[4] == "starts_with"){
							$query->leftjoin('org','org.org_serial_id','=',$value[0])
										->where(function ($key) use ($a, $b) 
										{
											$key->where('org.org_name', $b, $a);
										});
						}else{
							$query->where($value[0], $value[1], $value[2]);
						}												 			
					}
					elseif($value[3] === "teams")
					{
						$a = $value[2];
						$b = $value[1];
						if($value[4] == "starts_with"){
							$query->leftjoin('users_teams','users_teams.teams_serial_id','=',$value[0])
										->where(function ($key) use ($a, $b) 
										{
											$key->where('users_teams.teams_name', $b, $a);
										});
						}else{
							$query->where($value[0], $value[1], $value[2]);
						}												 			
					}
					elseif($value[3] === "users")
					{
						$a = $value[2];
						$b = $value[1];
						if($value[4] == "starts_with"){
							$query->leftjoin('users','users.id','=',$value[0])
										->where(function ($key) use ($a, $b) 
										{
											$key->where('users.name', $b, $a);
										});
						}else{
							$query->where($value[0], $value[1], $value[2]);
						}												 			
					}
					else
					{
						if ($value[3] == 'multipleoption') {
							if ($value[4] == 'is_empty') {
								$query->where(function ($query) use ($value) {
									$query->whereNull($value[0])->orWhere($value[0], "=", "");
								});
							} else if ($value[4] == 'is_not_empty') {
								$query->whereNotNull($value[0])->where($value[0], "!=", "");
							} else {
								/* 
								* Untuk mendapatkan jml index
								* yang dibutuhkan untuk search
								*/
								$fieldmaps = explode('.', $value[0]);
								$countValueCustom = $this->model_custom_fields_class::select('dropdown.dropdown_name')
														->join('dropdown', 'dropdown.dropdown_name', $this->table_module.'_custom_fields.' . $this->table_module . '_custom_fields_options')
														->join('dropdown_options', 'dropdown_options.dropdown_serial_id', 'dropdown.dropdown_serial_id')
														->where($this->table_module . '_custom_values_maps','=',$fieldmaps[1])
														->where($this->table_module . '_custom_fields.company_id', '=', $criteria['company_id'])
														->count();
								$query->where(function($querywhere) use ($countValueCustom, $value) {
									for ($i = 1; $i > ($countValueCustom * (-1)); $i--) {
										$querywhere->{ $i == 1 ? 'where' : 'orWhere' }(DB::raw("substring_index(REGEXP_REPLACE(" . $value[0] . ",'[]/[/\"]',''), ',', $i)"), $value[1], $value[2]);
									}
								});
							}
						} else {
							$query->where($value[0], $value[1], $value[2]); // Like
						}
					}
				}
			}
			$checkParentType 	= in_array($this->table_module.'.'.$this->table_module.'_parent_type', array_column($searchCriteriaCore, 0));
            if ($checkParentType == TRUE) // search data by calls_parent_type
			{
				$keyParentType 	= array_search($this->table_module.'.'.$this->table_module.'_parent_type', array_column($searchCriteriaCore, 0));
				$parentTypeOpp 			= $searchCriteriaCore[$keyParentType][1]; // get operator
				$parentTypeKeyword 	= $searchCriteriaCore[$keyParentType][2]; // get keyword
				if (is_array($parentTypeKeyword)) {

					$checkParentId 	= in_array($this->table_module.'.'.$this->table_module.'_parent_id', array_column($searchCriteriaCore, 0));
					if ($checkParentId) {
						$searchCriteriaCore = array_values($searchCriteriaCore); // reset key array, to 0 
						$keyParentId 		= array_search($this->table_module.'.'.$this->table_module.'_parent_id', array_column($searchCriteriaCore, 0));
						$parentIdOpp 		= $searchCriteriaCore[$keyParentId][1]; // get operator
						$parentIdKeyword 	= $searchCriteriaCore[$keyParentId][2]; // get keyword

						$whererelated = false;
						$cek_filter_is = TRUE;
						$query->where(function($whereparent) use ($parentTypeKeyword, $parentTypeOpp, $parentIdOpp, $parentIdKeyword) {
							foreach ($parentTypeKeyword as $key => $value) {
								$whereparent->{$key==0 ? 'where' : 'orWhere'}(function($whereparentid) use ($key, $parentTypeOpp, $value, $parentIdOpp, $parentIdKeyword){
									$whereparentid->where('sys_rel.rel_to_module', $parentTypeOpp, $value);
									if ($parentIdOpp <> "LIKE" AND $parentIdOpp <> "NOT LIKE") {
										$whererelated = true;
										$whereparentid->whereIn('sys_rel.rel_to_id', $parentIdKeyword);
									}
								});
							}	
						});

						if ($whererelated) {
							/* relatedt to / parent id berisikan id / menggunakan operator is */
							unset($searchCriteriaCore[$keyParentId]);
						}
					} else {
						$query->where(function($whereparent) use ($parentTypeKeyword, $parentTypeOpp) {
							foreach ($parentTypeKeyword as $key => $value) {
								$whereparent->{$key==0 ? 'where' : 'orWhere'}('sys_rel.rel_to_module', '=', $value);
							}	
						});
					}
				} else {
						if($parentTypeOpp == 'LIKE'){
							$countValue = $this->model_fields_class::select('dropdown_options.dropdown_options_value')
															->join('dropdown', 'dropdown.dropdown_name', $this->table_module.'_fields.' . $this->table_module . '_fields_options')
															->join('dropdown_options', 'dropdown_options.dropdown_serial_id', 'dropdown.dropdown_serial_id')
															->where($this->table_module . '_fields_name','=',$this->table_module.'_parent_type')
															->where('dropdown_options.dropdown_options_label', $parentTypeOpp, $parentTypeKeyword)
															->first();

							if(isset($countValue['dropdown_options_value']) && $countValue['dropdown_options_value'] == "org")
							{
								$query->where('sys_rel.rel_to_module', $parentTypeOpp, '%'.$countValue['dropdown_options_value'].'%');
							}
							else
							{
								$query->where('sys_rel.rel_to_module', $parentTypeOpp, $parentTypeKeyword);
							}
						}else
						{
							$query->where('sys_rel.rel_to_module', $parentTypeOpp, $parentTypeKeyword);
						}
				}
				unset($searchCriteriaCore[$keyParentType]);
			}

			$checkParentId 	= in_array($this->table_module.'.'.$this->table_module.'_parent_id', array_column($searchCriteriaCore, 0));
			if ($checkParentId == TRUE)  // search data by calls_parent_id
			{
				$searchCriteriaCore = array_values($searchCriteriaCore); // reset key array, to 0 
				$keyParentId 		= array_search($this->table_module.'.'.$this->table_module.'_parent_id', array_column($searchCriteriaCore, 0));
				$parentIdOpp 		= $searchCriteriaCore[$keyParentId][1]; // get operator
				$parentIdKeyword 	= $searchCriteriaCore[$keyParentId][2]; // get keyword
				
				if ($parentIdOpp == "LIKE" OR $parentIdOpp == "NOT LIKE") {
					$query->having($this->table_module.'_parent_name', $parentIdOpp, $parentIdKeyword);
				}elseif($parentIdOpp == "=" AND $cek_filter_is == FALSE){
					if(!is_array($parentIdKeyword))
					{
						$parentIdKeyword = explode(" ",$parentIdKeyword);
					}
					$query->wherein('sys_rel.rel_to_id', $parentIdKeyword);
				}elseif($parentIdOpp == "!="){
						if(is_numeric($parentIdKeyword))
						{
							$query->whereNotin('sys_rel.rel_to_id', $parentIdKeyword);
						}else{
							$query->whereNotNull('sys_rel.rel_to_id');
						}
				}elseif($parentIdOpp == "=="){
					$query->whereNull('sys_rel.rel_to_id');
				}
				
				unset($searchCriteriaCore[$keyParentId]);
			}

			// ADD BY ANDRIAN
			// FOR SOLVED SEARCH 'is_empty' and 'is_not_empty'
			if ( countCustom($searchCriteriaCore) ) 
			{
				foreach ($searchCriteriaCore as $key => $value) 
				{
					if ($value[1] == "=" && is_array($value[2])) // operator is 
					{
						$query->whereIn($value[0], $value[2]);
					}
					elseif ($value[1] == "!=" && is_array($value[2])) // operator isn't
					{
						$query->whereNotIn($value[0], $value[2]);
					}
					elseif ( $value[1] === '==' ) 
					{
						$query->where(function ($query) use ($value) 
						{
							$query->where($value[0], '=', $value[2]);
							$query->orWhereNull($value[0]);
						});
					}
					elseif ( $value[1] === '!==' ) 
					{
						$query->where(function ($query) use ($value) 
						{
							$query->where($value[0], '!=', $value[2]);
							$query->whereNotNull($value[0]);
						});
					}
					else
					{
						$query->where($value[0], $value[1], $value[2]); // search data in core fields
					}
				}
			}
			// END ADD BY ANDRIAN
			// END FOR SOLVED SEARCH 'is_empty' and 'is_not_empty'
		} 

		# SEARCH GLOBAL FEATURE
		if (isset($_GET['subaction']))
		{
			if ($_GET['subaction'] == "search_global") 
			{
				$query_count = 1; // count query with left join

				$keyword 	= $criteria['keyword'];
				$fields 	= array();

				foreach ($fieldsName as $value) 
				{
					$name 			= $value;
					$exp_value 	= explode(" ", $value);
					if (is_array($exp_value) && isset($exp_value[2])) 
					{
						$fields[]		= $exp_value[0];
					}else{
						$fields[] 	= $name;
					}
				}

				$query->where(function ($query) use ($fields, $keyword) 
				{
					for ($i=0; $i < countCustom($fields); $i++) 
					{ 
						$query->orWhere($fields[$i], 'LIKE', $keyword.'%');
					}
				});
			}
		}

		if ( isset($criteria['filterApproval']) && $criteria['filterApproval'] !== '' )
		{
			$query	=	$query->join('approval','approval.relate_to_serial_id','=',$this->table_module.'.'.$this->table_module.'_serial_id')
											->where('approval.company_id','=',$criteria['company_id'])
											->where('approval.status_approval','=',Config('setting.approval_submitted'))
											->where('approval.rel_to_module','=',$this->table_module)
											->where(function ($query) use ($criteria) {
												$query->where('approval.approved_by','=',$criteria['users_id'])
															->orWhere(function ($query) use ($criteria){
																$query->where('approval.user_approver_serial_id','=',$criteria['users_id'])
																			->where('approval.sequence','=',1);
															});
											});
		}

		$query->where($this->table_module.'.company_id', '=', $criteria['company_id']);
		$query->where($this->table_module.'.deleted', '=', Config('setting.NOT_DELETED'));
		# END

		if($countdata != '' AND $countdata == 'countdata')
		{
			if ($query_count == 0) 
			{
					$q_count     = $this->model_class::where($this->table_module.'.company_id', '=', $criteria['company_id'])
															->where($this->table_module.'.deleted', '=', Config('setting.NOT_DELETED'));
					if ($myRecordOnlyCheck === TRUE) 
					{
							// count data when filter "You" choosen
							$q_count->where($this->table_module.'_owner', '=', $criteria['users_id']);
					}
					elseif ($data_roles == TRUE && countCustom($roles['contents']) > 0 && $myRecordOnlyCheck === FALSE) 
					{ 
							// count data when data roles running
							$q_count->whereIn($this->table_module.'.'.$this->table_module.'_owner', $roles['contents']);
					}
					$items_count     = $q_count->count();
			}
			else
			{
					$items_count     = $query->get()->count();
			}

			$result = [
					'data'             => $items_count,
			];

			return $result;
		}
		# PAGINATION MANUALLY
		$page = 1;
		if (isset($input['page'])) 
		{
			$page 			= (!empty($input['page'])) ? $input['page'] : 1;
		}
		$perPage 			= $criteria['data_per_page'];
		$skip 				= ($page - 1) * $perPage;

		// if ($query_count == 0) 
		// {
		// 	$q_count 	= $this->model_class::where($this->table_module.'.company_id', '=', $criteria['company_id'])
		// 						->where($this->table_module.'.deleted', '=', Config('setting.NOT_DELETED'));
		// 	if ($myRecordOnlyCheck === TRUE) 
		// 	{
		// 		// count data when filter "You" choosen
		// 		$q_count->where($this->table_module.'_owner', '=', $criteria['users_id']);
		// 	}
		// 	elseif ($data_roles == TRUE && countCustom($roles['contents']) > 0 && $myRecordOnlyCheck === FALSE) 
		// 	{ 
		// 		// count data when data roles running
		// 		$q_count->whereIn($this->table_module.'.'.$this->table_module.'_owner', $roles['contents']);
		// 	}
		// 	$items_count 	= $q_count->count();
		// }
		// else
		// {
		// 	$items_count 	= $query->get()->count();
		// }

		if(preg_match('/desc/i',$criteria['type_sort']))
		{
			$criteria['type_sort'] = 'DESC';
		}
		else {
			$criteria['type_sort'] = 'ASC';
		}
		
		$query = $query->orderBy($criteria['order_by'], $criteria['type_sort'])
									->simplepaginate($perPage,['*'],'page',$page);
		//$items 				= $query->take($perPage)->skip($skip)->get();
		$items 				= json_decode(json_encode($query), True);

		# ADD BY GILANG PRATAMA
		# SATURDAY, 09 SEPTEMBER 2019
		# TO GET RECORDING CLICK TO CALL
		
		foreach($listFieldsCustom as $key =>$value)
		{
			$fldsCstmMap = '';
			$fldsCstmName= '';
			if($listFieldsCustom[$key][$this->table_module."_custom_fields_input_type"] == "person")
			{
				$fldsCstmName = $listFieldsCustom[$key][$this->table_module."_custom_fields_name"];
				$fldsCstmMap  = $listFieldsCustom[$key][$this->table_module."_custom_values_maps"];	
				foreach($items['data'] as $key2 => $valu2)
				{
					if($items['data'][$key2][$fldsCstmName] == null && $items['data'][$key2][$fldsCstmName] == '')
					{
						$data = $this->model_custom_values__class::select($fldsCstmMap)
																								->where('calls_serial_id','=',$items['data'][$key2]['calls_serial_id'])
																								->where('company_id', '=', $criteria['company_id'])
																								->first();
						if(countCustom($data) > 0)
						{
							$data = $data->toArray();
							$items['data'][$key2][$fldsCstmName] = $data[$fldsCstmMap];
						}
					}
				}
			}
			if($listFieldsCustom[$key][$this->table_module."_custom_fields_input_type"] == "leads")
			{
				$fldsCstmName = $listFieldsCustom[$key][$this->table_module."_custom_fields_name"];
				$fldsCstmMap  = $listFieldsCustom[$key][$this->table_module."_custom_values_maps"];	
				foreach($items['data'] as $key2 => $valu2)
				{
					if($items['data'][$key2][$fldsCstmName] == null && $items['data'][$key2][$fldsCstmName] == '')
					{
						$data = $this->model_custom_values__class::select($fldsCstmMap)
																								->where('calls_serial_id','=',$items['data'][$key2]['calls_serial_id'])
																								->where('company_id', '=', $criteria['company_id'])
																								->first();
						if(countCustom($data) > 0)
						{
							$data = $data->toArray();
							$items['data'][$key2][$fldsCstmName] = $data[$fldsCstmMap];
						}
					}
				}
			}
			if($listFieldsCustom[$key][$this->table_module."_custom_fields_input_type"] == "deals")
			{
				$fldsCstmName = $listFieldsCustom[$key][$this->table_module."_custom_fields_name"];
				$fldsCstmMap  = $listFieldsCustom[$key][$this->table_module."_custom_values_maps"];	
				foreach($items['data'] as $key2 => $valu2)
				{
					if($items['data'][$key2][$fldsCstmName] == null && $items['data'][$key2][$fldsCstmName] == '')
					{
						$data = $this->model_custom_values__class::select($fldsCstmMap)
																								->where('calls_serial_id','=',$items['data'][$key2]['calls_serial_id'])
																								->where('company_id', '=', $criteria['company_id'])
																								->first();
						if(countCustom($data) > 0)
						{
							$data = $data->toArray();
							$items['data'][$key2][$fldsCstmName] = $data[$fldsCstmMap];
						}
					}
				}
			}
			if($listFieldsCustom[$key][$this->table_module."_custom_fields_input_type"] == "organization")
			{
				$fldsCstmName = $listFieldsCustom[$key][$this->table_module."_custom_fields_name"];
				$fldsCstmMap  = $listFieldsCustom[$key][$this->table_module."_custom_values_maps"];	
				foreach($items['data'] as $key2 => $valu2)
				{
					if($items['data'][$key2][$fldsCstmName] == null && $items['data'][$key2][$fldsCstmName] == '')
					{
						$data = $this->model_custom_values__class::select($fldsCstmMap)
																								->where('calls_serial_id','=',$items['data'][$key2]['calls_serial_id'])
																								->where('company_id', '=', $criteria['company_id'])
																								->first();
						if(countCustom($data) > 0)
						{
							$data = $data->toArray();
							$items['data'][$key2][$fldsCstmName] = $data[$fldsCstmMap];
						}
					}
				}
			}
			if($listFieldsCustom[$key][$this->table_module."_custom_fields_input_type"] == "teams")
			{
				$fldsCstmName = $listFieldsCustom[$key][$this->table_module."_custom_fields_name"];
				$fldsCstmMap  = $listFieldsCustom[$key][$this->table_module."_custom_values_maps"];	
				foreach($items['data'] as $key2 => $valu2)
				{
					if($items['data'][$key2][$fldsCstmName] == null && $items['data'][$key2][$fldsCstmName] == '')
					{
						$data = $this->model_custom_values__class::select($fldsCstmMap)
																								->where('calls_serial_id','=',$items['data'][$key2]['calls_serial_id'])
																								->where('company_id', '=', $criteria['company_id'])
																								->first();
						if(countCustom($data) > 0)
						{
							$data = $data->toArray();
							$items['data'][$key2][$fldsCstmName] = $data[$fldsCstmMap];
						}
					}
				}
			}
			if($listFieldsCustom[$key][$this->table_module."_custom_fields_input_type"] == "users")
			{
				$fldsCstmName = $listFieldsCustom[$key][$this->table_module."_custom_fields_name"];
				$fldsCstmMap  = $listFieldsCustom[$key][$this->table_module."_custom_values_maps"];	
				foreach($items['data'] as $key2 => $valu2)
				{
					if($items['data'][$key2][$fldsCstmName] == null && $items['data'][$key2][$fldsCstmName] == '')
					{
						$data = $this->model_custom_values__class::select($fldsCstmMap)
																								->where('calls_serial_id','=',$items['data'][$key2]['calls_serial_id'])
																								->where('company_id', '=', $criteria['company_id'])
																								->first();
						if(countCustom($data) > 0)
						{
							$data = $data->toArray();
							$items['data'][$key2][$fldsCstmName] = $data[$fldsCstmMap];
						}
					}
				}
			}
		}

		if (!isEmpty($items)) 
		{
			$items = $this->getListRecord($items['data'], $criteria['company_id']);
		}

		// $result 	= new $this->paginate_manually($items, $items_count, $perPage, $page);
		// $result->setPath($this->table_module);
		// $result->appends($input);

		//$last_page = ceil($items_count / $perPage);

		$result = [
		//	'total_data' 						=> $items_count,
			"limit"							=> (int) $perPage,
			"prev_page"					=> ($page == 1) ? 1 : $page - 1, # next page
			"current_page"			=> (int) $page, # next page
		//	"next_page"					=> ($last_page <= $page) ? '' : $page + 1, # next page
		//	"last_page"					=> $last_page, # next page
		//	"total_page"				=> $last_page, # next page
			'data' 							=> $items,

		];

		return $result;
	}

	# Created By Prihan Firmanullah
  	# 2019-12-20
  	# For Save Customize Form
	public function generate_view($view=array(), $company_id=0, $users_id=0)
	{
		$sys = new sys();

		$result['_token'] 	= md5(rand(10, 1000));

		if (countCustom($view) > 0) 
		{
			# define variable
			$view_serial_id 	= $view[$this->table_module.'_view_serial_id'];
			$view_name 				= $view[$this->table_module.'_view_name'];
			# end

			$query 	= $this->model_view_criteria_class::select($this->table_module.'_view_criteria.*')
											->where($this->table_module.'_view_serial_id', '=', $view_serial_id)
											->get();
			if (countCustom($query) > 0) 
			{
				foreach ($query as $key => $row)  // Looping table view_criteria
				{
					# define variable
					$field_serial_id 					= $row[$this->table_module.'_fields_serial_id'];
					$view_criteria_operator 	= $row[$this->table_module.'_view_criteria_operator'];
					$view_criteria_value 			= $row[$this->table_module.'_view_criteria_value'];
					$view_criteria_type 			= $row[$this->table_module.'_view_criteria_type'];
					# end 

					if ($view_criteria_type == Config('setting.view_criteria_type_core')) // if core field
					{
						$qry_field = $this->model_fields_class::select($this->table_module.'_fields_name', $this->table_module.'_fields_input_type')
																->where($this->table_module.'_fields_serial_id', '=', $field_serial_id)
																->first();
						$field_name = "no_field_name";

						if ($qry_field[$this->table_module.'_fields_input_type'] == "date" || $qry_field[$this->table_module.'_fields_input_type'] == "datetime") 
						{
								$field_name = "date_".$qry_field[$this->table_module.'_fields_name'];

						}
						elseif (countCustom($qry_field) > 0) 
						{
								$field_name = $qry_field[$this->table_module.'_fields_name'];
						}

					
						if ($field_name == $this->table_module.'_owner' AND ($view_criteria_operator == 'is' OR $view_criteria_operator =='isnt')) 
						{
							$temp_keyword = ($sys->isJSON($view_criteria_value)) ? json_decode($view_criteria_value) : str_replace('"', "", $view_criteria_value);

							if (is_array($temp_keyword)) 
							{
								foreach ($temp_keyword as $key => $value) 
								{
									$pos = strpos($value, '!@#$%^&*()');
									if ($pos !== false) 
									{
										$explode_owner = explode("!@#$%^&*()", $value);
										$temp_keyword[$key] = $explode_owner[1];
									}
								}

								$keyword = $temp_keyword;
							}
						}
						else if($field_name == $this->table_module.'_parent_id' AND ($view_criteria_operator == 'is' OR $view_criteria_operator =='isnt'))
						{
							$temp_keyword = ($sys->isJSON($view_criteria_value)) ? json_decode($view_criteria_value) : str_replace('"', "", $view_criteria_value);
							if (is_array($temp_keyword)) 
							{
								foreach ($temp_keyword as $key => $value) 
								{
									if(isset($value->id))
									{
										$temp_keyword[$key] = $value->id;
									}
								}

								$keyword = $temp_keyword;
							}
						}
						else
						{
							$keyword = ($sys->isJSON($view_criteria_value)) ? json_decode($view_criteria_value) : str_replace('"', "", $view_criteria_value);
						}

						$result[$field_name.'_opp'] = $view_criteria_operator;
						$result[$field_name] 				= $keyword;

					} // end core field
					elseif ($view_criteria_type == Config('setting.view_criteria_type_custom')) // if custom field
					{
						$qry_custom_field 	= DB::table($this->table_module.'_custom_fields as a')
																			->select('a.'.$this->table_module.'_custom_fields_serial_id', 'a.'.$this->table_module.'_custom_fields_name',
																			'a.'.$this->table_module.'_custom_fields_input_type')
																			
																			->where('a.company_id', '=', $company_id)
																			->where('a.'.$this->table_module.'_custom_fields_serial_id', '=', $field_serial_id)
																			->orderBy('a.'.$this->table_module.'_custom_fields_serial_id', 'ASC')
																			->get();

																			
						if (countCustom($qry_custom_field) > 0) 
						{
							$qry_custom_field 	= json_decode(json_encode($qry_custom_field), TRUE);
							
							foreach ($qry_custom_field as $key => $row) 
							{
								$custom_fields_serial_id 	= $row[$this->table_module.'_custom_fields_serial_id'];
								$custom_fields_name 			= 'custom_'.$row[$this->table_module.'_custom_fields_name'];
								$criteria_operator 				= "contains";
								$criteria_value 					= "";
								
								if ($row[$this->table_module.'_custom_fields_input_type'] == "date" || $row[$this->table_module.'_custom_fields_input_type'] == "datetime")
								{
										$custom_fields_name = "date_custom_".$row[$this->table_module.'_custom_fields_name'];
								}

								if ($custom_fields_serial_id == $field_serial_id) 
								{
									$criteria_operator 	= $view_criteria_operator;
									$criteria_value 		= $view_criteria_value;
								}

								$keyword = ($sys->isJSON($view_criteria_value)) ? json_decode($view_criteria_value) : str_replace('"', "", $view_criteria_value);

								$result[$custom_fields_name.'_opp'] = $criteria_operator;
								$result[$custom_fields_name] 				= 	$keyword;

							}
						}
					} // end custom field
				}
			}
		}

		return $result;
	}

	# Created By Prihan Firmanullah
  	# 03-02-2020
  	# For Save Customize Form
	public function save_data($input=array(), $company_id=0, $users_id=0, $ajax_form_agent_console="")
	{
		$sys = new sys();
		# Define Variable
		$save_core = array();
		$input_core = array();
		$data_core = array();

		$save_custom = array();
		$data_custom = array();

		$valueDocuments = array();
		$file = isset($input['file']);
		if($file){
			$valueDocuments = $input['file'];
		}
		unset($input['file']);

		$calls_notifications = isset($input['calls_notifications']);
		unset($input['calls_notifications']);

		$approval_data	=	isset($input['approval_data']) ? $input['approval_data'] : array();
		if ( $approval_data )
		{
			unset($input['approval_data']);
		}

		$userApprover	=	isset($input['userApprover']) ? $input['userApprover'] : 0;
		if ( $userApprover > 0 )
		{
			unset($input['userApprover']);
		}

		foreach ($input as $key => $value) 
		{
			$prefix_custom  = substr($key, 0, 7);
			if ($prefix_custom == "custom_") 
			{
				$fields_name = substr_replace($key, '', 0, 7);
				$input_custom[$fields_name] = $input[$key];
				unset($input[$key]);
			}
			else
			{
				$input_core[$key] = $input[$key];
			}
		}

		$save_core = $this->save_data_core($input_core, $company_id, $users_id, $ajax_form_agent_console);

		if (countCustom($save_core) > 0) 
		{
			$data_core = $save_core;
			$input_custom[$this->table_module.'_serial_id'] = $save_core[$this->table_module.'_serial_id'];
			$save_custom = $this->save_data_custom($input_custom, $company_id, $users_id);
			if (countCustom($save_custom) > 0) 
			{
				$data_custom = $save_custom;
			}

			unset($input_custom[$this->table_module.'_serial_id']);
			$serial_id = $save_core[$this->table_module.'_serial_id'];
			
			$custom_id = array();
			foreach ($input_custom as $key => $value) 
			{
				$custom_id = $this->get_custom_fields_by_id($key, $company_id);
				if (!isEmpty($custom_id)) 
				{
				$input[$this->table_module.'_custom_values_text'][$custom_id[$this->table_module.'_custom_fields_serial_id']] = $value;
				}
			}

			// # SAVE SYS REL : PARENTY_TYPE AND RELATED TO
		 //  if ( !empty($input[$this->table_module.'_parent_type']) AND !empty($input[$this->table_module.'_parent_id']) )
		 //  {
		  	
			// }
			
			// Query First & Last Activities 
			if (isset($input[$this->table_module.'_parent_type']) && !empty($input[$this->table_module.'_parent_type'])
				  && isset($input[$this->table_module.'_parent_id']) && !empty($input[$this->table_module.'_parent_id'])) 
			{
				if ($input[$this->table_module.'_parent_id'] == 'none') 
				{
						unset($input[$this->table_module.'_parent_type']);
						unset($input[$this->table_module.'_parent_id']);
						$input[$this->table_module.'_parent_type'] = 'org';
						$input[$this->table_module.'_parent_id'] = $input['org_serial_id'];
				}
						
				$check_sysrel = DB::table($input[$this->table_module.'_parent_type'])
		  										->where($input[$this->table_module.'_parent_type'].'_serial_id', '=', $input[$this->table_module.'_parent_id'])
		  										->where('company_id', '=', $company_id)
		  										->first();

		  	if (!isEmpty($check_sysrel)) 
		  	{
			  	// Insert Relation rel_sys
				  $rel['rel_from_module'] = $this->table_module;
				  $rel['rel_from_id'] 		= $serial_id;
				  $rel['rel_to_module']   = $input[$this->table_module.'_parent_type'];
			    $rel['rel_to_id'] 			= $input[$this->table_module.'_parent_id'];
			    $rel['company_id'] 			= $company_id;
				  $save_rel_sys 	= $this->model_sys_rel_class::create($rel);

					$this->save_upcoming_activities($input_core, $save_core[$this->table_module.'_serial_id'], $company_id);
		      $this->save_last_activities($input_core, $save_core[$this->table_module.'_serial_id'], $company_id);
		      $this->save_first_activities($input_core, $save_core[$this->table_module.'_serial_id'], $company_id);
					if(isset($input_core['calls_status']) && $input_core['calls_status'] == 'Held')
					{						
						$this->save_last_completed_activities($input_core, $save_core[$this->table_module.'_serial_id'], $company_id);
					}
		  	}
		  	else
		  	{
		  		unset($input[$this->table_module.'_parent_type']);
		  		unset($input[$this->table_module.'_parent_id']);
		  	}

				// First & Last Activities Org ,if prent type contacts
				// if (isset($input['org_parent_type']) && !empty($input['org_parent_type'])
				// 		&& isset($input['org_parent_id']) && !empty($input['org_parent_id'])) 
				// {
							
				// 	$check_sysrel_org = DB::table($input['org_parent_type'])
				// 										->where($input['org_parent_type'].'_serial_id', '=', $input['org_parent_id'])
				// 										->where('company_id', '=', $company_id)
				// 										->first();
	
				// 	if (!isEmpty($check_sysrel_org)) 
				// 	{
				// 		// Insert Relation rel_sys
				// 		$rel['rel_from_module'] = $this->table_module;
				// 		$rel['rel_from_id'] 		= $serial_id;
				// 		$rel['rel_to_module']   = $input['org_parent_type'];
				// 		$rel['rel_to_id'] 			= $input['org_parent_id'];
				// 		$rel['company_id'] 			= $company_id;
				// 		$save_rel_sys 	= $this->model_sys_rel_class::create($rel);
	
				// 		$this->save_upcoming_activities($input_core, $input['org_parent_id'], $company_id);
				// 		$this->save_last_activities($input_core, $input['org_parent_id'], $company_id);
				// 		$this->save_first_activities($input_core, $input['org_parent_id'], $company_id);
				// if(isset($input_core['calls_status']) && $input_core['calls_status'] == 'Held')
				// 	{						
				// 		$this->save_last_completed_activities($input_core, $input['org_parent_id'], $company_id);
				// 	}
				// 	}
				// 	else
				// 	{
				// 		unset($input['org_parent_type']);
				// 		unset($input['org_parent_id']);
				// 	}
				// }
      }

			// $syslog_action 	= $sys_api->LogSave($input);
			if ( empty($input[$this->table_module.'_parent_type']) OR empty($input[$this->table_module.'_parent_id']) ) 
			{
				unset($input[$this->table_module.'_parent_type']);
				unset($input[$this->table_module.'_parent_id']);
			}

			// re-format to syslog
			if(isset($input[$this->table_module."_date_start"]))
			{
				$input[$this->table_module."_date_start"] = date('Y-m-d H:i:s', strtotime($input[$this->table_module.'_date_start']. "+ 7 hours"));
			}
			if(isset($input[$this->table_module."_date_end"]))
			{
				$input[$this->table_module."_date_end"] = date('Y-m-d H:i:s', strtotime($input[$this->table_module.'_date_end']. "+ 7 hours"));
			}
			// end re-format to syslog

			$syslog_action 	= $sys->log_save($input, $this->table_module, $company_id);
			if (!isEmpty($syslog_action))	
			{
				if (!isEmpty($approval_data)) {
					$usersName	=	"";
					$dateApprovalCreated	=	",";

					$dateNow	= date('d F Y H:i');
					$dateNow	=	date('d F Y H:i', strtotime($dateNow. "+7 hours"));

					if ( isset($approval_data['date_created']) )
					{
						if ( !empty($approval_data['date_created']) )
						{
							$dateApproval	=	date('d F Y H:i', strtotime($approval_data['date_created']. "+7 hours"));
					
							$dateApprovalCreated	=	$dateApprovalCreated."Date Approval &#8594 ".$dateApproval.",";
						}
					}

					if ( $userApprover > 0 )
					{
						$usersName	=	$this->users_data($userApprover);
						if ( !isEmpty($usersName) )
						{
							$usersName	=	$usersName['name'];
						}
					}
		
					$addLogApproval	=	"From &#8594"." Approval".$dateApprovalCreated."Date Approved &#8594 ".$dateNow.",Approved By &#8594 ".$usersName.",";
					$syslog_action  = $addLogApproval.$syslog_action;
				}
				
			  if( isset($input['isDuplicate']) && $input['isDuplicate'] == 'true')
        {
          $syslog_action_type = 'duplicate';
        }
        else
        {
          $syslog_action_type = 'create';
        }

				$syslog 				= $sys->sys_api_syslog( $syslog_action, $syslog_action_type, $this->table_module, $serial_id, $users_id, $company_id );
			}			

			elasticAddData($serial_id, $company_id, $this->table_module);			
		}
		
		$result = array_merge($data_core, $data_custom);

		if(!isEmpty($result) && isset($input[$this->table_module.'_parent_type']) && !empty($input[$this->table_module.'_parent_type']) && isset($input[$this->table_module.'_parent_id']) && !empty($input[$this->table_module.'_parent_id'])){
			if(gettype($check_sysrel) == 'object'){
				$check_sysrel = json_decode(json_encode($check_sysrel),true);
			}

			$sys_push 		= new sys_push();
			$leaderTeam 	= array();
			$dataNotif 		= $check_sysrel;
			$moduleParent	= $input[$this->table_module.'_parent_type'];

			$leaderTeam = $this->getLeaderTeam($dataNotif[$moduleParent.'_owner'], $company_id);
			$dataNotif['table_module'] = $moduleParent;
			$dataNotif['notif_status'] = true;

			$sys_push->sendNotificationsForUsers($dataNotif);

			if(!isEmpty($leaderTeam)){
				foreach($leaderTeam as $keyLead => $valueLead){
					$dataNotif[$moduleParent.'_owner'] = $valueLead;

					$sys_push->sendNotificationsForUsers($dataNotif);
				}
			}
		}

		if($calls_notifications && $result[$this->table_module.'_status'] == "Planned"){
			$sys_push = new sys_push();
			$sys_push->sendToNotificationCalls($result);
		}
		
		$result = $this->GetNameOwnerNew($result);
		
		// data document ketika data di approve
		if(is_array($valueDocuments)){
			$sys_documents = new sys_documents();
			$sys_documents->saveDocumentApproval($this->table_module, $company_id, $result, $valueDocuments);
		}

		return $result;
	}

	# Created By Prihan Firmanullah
  	# 2019-12-20
  	# For Save Customize Form
	public function select_fieldsName($fields=array())
	{
		# defined variable
		$fieldsName = array();
		$fieldsNameCore 	= array();
		$fieldsNameCustom = array();
		# end


		# Process merge list fields core and list field custom

		if (countCustom($fields) > 0) 
		{
			if ($fields['listFields_count'] > 0) 
			{
				
				$fieldsNameCore[0] 	= $this->table_module.'.'.$this->table_module.'_serial_id';
				$fieldsNameCore[1] 	= $this->table_module.'.'.$this->table_module.'_uuid';
				$fieldsNameCore[2] 	= $this->table_module.'.'.$this->table_module.'_owner as owner_id';
				$fieldsNameCore[3] 	= $this->table_module.'.billsec';
				
				foreach ($fields['listFields'] as $key => $value) 
				{
					$field_name 	= $value[$this->table_module.'_fields_name'];
					if ($field_name != $this->table_module.'_parent_type' && $field_name != $this->table_module.'_parent_id') 
					{
						$fieldsNameCore[] = $this->table_module.'.'.$value[$this->table_module.'_fields_name'];
					}
				}
			}
			
			if ($fields['listFieldsCustom_count'] > 0) 
			{
				foreach ($fields['listFieldsCustom'] as $key => $value) 
				{
					$fieldsNameCustom[] 	= 'mcv.'.$value[$this->table_module.'_custom_values_maps'].' as '.$value[$this->table_module.'_custom_fields_name'];
				}
			}
			$fieldsName 	= array_merge($fieldsNameCore, $fieldsNameCustom); // merge core field and custom field for selected query
		}

	
		/* PREVIEW RESULT
			Array
			(
			    [0] => leads.leads_serial_id
			    [1] => leads.leads_uuid
			    [2] => leads.leads_salutation
			    [3] => leads.leads_first_name
			    [4] => leads.leads_last_name
			    [5] => leads.leads_status
			    [6] => leads.leads_owner
			    [8] => leads.date_created
			    [9] => b.leads_custom_values_text as leads_religion
			    [10] => c.leads_custom_values_text as leads_skype
			    [11] => d.leads_custom_values_text as leads_facebook
			)
		END */
		return $fieldsName;
	}

	# Created By Prihan Firmanullah
  	# 2019-12-20
  	# For Save Customize Form
	public function convertFieldsName($fieldsName=array())
	{
		$result 	= "";
		if (countCustom($fieldsName) > 0) 
		{
			foreach ($fieldsName as $key => $value) 
			{
				$result .= $value;
				if ($key < countCustom($fieldsName) - 1) {
					$result .= ", ";
				}
			}
		}
		
		return $result;
	}

	# Created By Prihan Firmanullah
  	# 2019-12-20
  	# For Save Customize Form
	public function myRecordOnlyCheck($company_id=0, $users_id=0)
	{
		$result = FALSE;
		$check 	= $this->model_view_class::where($this->table_module.'_view_name', '=', 'You')
													->where($this->table_module.'_view_checked', '=', Config('setting.view_checked'))
													->where('company_id', '=', $company_id)
													->where('users_id', '=', $users_id)
													->where('deleted', '=', Config('setting.NOT_DELETED'))
													->get();
		$check 	= $this->model_view_checked_class::where('b.'.$this->table_module.'_view_name', '=', 'You')
										->where($this->table_module.'_view_checked.users_id', '=', $users_id)
										->where($this->table_module.'_view_checked.company_id', '=', $company_id)
										->where('b.deleted', '=', Config('setting.NOT_DELETED'))
										->leftjoin($this->table_module.'_view as b', $this->table_module.'_view_checked.'.$this->table_module.'_view_serial_id', '=', 'b.'.$this->table_module.'_view_serial_id')
										->get();
		if (countCustom($check) > 0) 
		{
			$result 	= TRUE;
		}

		return $result;
	}

	# Created By Prihan Firmanullah
  	# 2019-12-20
  	# Get Form
	public function GetForm($company_id=0 ,$agents_new='')
	{
		$sys = new sys();

		$core_fields = $this->model_fields_class::select('*')->get()->toArray();

		$core_change = $this->model_fields_class::select('b.*')
                           	->leftjoin($this->table_module.'_fields_change as b', 'b.'.$this->table_module.'_fields_serial_id', '=', $this->table_module.'_fields.'.$this->table_module.'_fields_serial_id')
                           	->where('b.company_id', '=', $company_id)
		                        ->get();

		$core_sorting = $this->model_fields_class::select('b.*')
                           	->leftjoin($this->table_module.'_fields_sorting as b', 'b.'.$this->table_module.'_fields_serial_id', '=', $this->table_module.'_fields.'.$this->table_module.'_fields_serial_id')
                           	->where('b.company_id', '=', $company_id)
		                        ->get();

		// For handle duplicate data in fields change & fields sorting
		$temp_core_fields = $core_fields;

		if ( countCustom($core_change) > 0 )
		{
			foreach ($core_change as $key => $value) 
			{
				$key_change = array_search($value[$this->table_module.'_fields_serial_id'], array_column($temp_core_fields, $this->table_module.'_fields_serial_id'));
				if ( $key_change !== false )
				{
						$core_fields[$key_change][$this->table_module.'_fields_label'] 				= $value[$this->table_module.'_fields_change_label'];
						$core_fields[$key_change][$this->table_module.'_fields_validation'] 	= $value[$this->table_module.'_fields_change_validation'];
						$core_fields[$key_change][$this->table_module.'_fields_status'] 			= $value[$this->table_module.'_fields_change_status'];
						$core_fields[$key_change][$this->table_module.'_fields_options'] 			= $value[$this->table_module.'_fields_change_options'];
						$core_fields[$key_change][$this->table_module.'_fields_quick'] 				= $value[$this->table_module.'_fields_change_quick'];
						$core_fields[$key_change][$this->table_module.'_fields_readonly'] 		= $value[$this->table_module.'_fields_change_readonly'];
						$core_fields[$key_change][$this->table_module.'_fields_default_value'] 	= $value[$this->table_module.'_fields_change_default_value'];
						if ( !empty($value[$this->table_module.'_fields_change_input_type']) ) 
						{
							$core_fields[$key_change][$this->table_module.'_fields_input_type']	= $value[$this->table_module.'_fields_change_input_type'];
						}
					// }
				}
			}
		}

		// For handle not increment key array, handle problem in array search & array column
		$core_fields = array_values($core_fields);

		if ( countCustom($core_sorting) > 0 )
		{
			foreach ($core_sorting as $key => $value) 
			{
				$key_sorting = array_search($value[$this->table_module.'_fields_serial_id'], array_column($core_fields, $this->table_module.'_fields_serial_id'));
				if ( $key_sorting !== false )
				{
					$core_fields[$key_sorting][$this->table_module.'_fields_sorting'] 						= $value[$this->table_module.'_fields_sorting'];
					$core_fields[$key_sorting][$this->table_module.'_fields_sorting_put_header'] 	= $value[$this->table_module.'_fields_sorting_put_header'];
				}
			}
		}
		else
		{
			foreach ($core_fields as $key => $value) 
			{
					$core_fields[$key][$this->table_module.'_fields_sorting_put_header'] 	= Config('setting.fields_sorting_not_put_header');
			}
		}


		$select = $this->table_module.'_custom_fields_serial_id,'.
				$this->table_module.'_custom_fields_name,'.
				$this->table_module.'_custom_fields_label,'.
				$this->table_module.'_custom_fields_data_type,'.
				$this->table_module.'_custom_fields_input_type,'.
				$this->table_module.'_custom_fields_function,'.
				$this->table_module.'_custom_fields_options,'.
				$this->table_module.'_custom_fields_validation,'.
				$this->table_module.'_custom_fields_sorting as '.$this->table_module.'_fields_sorting,'.
				$this->table_module.'_custom_fields_status,'.
				$this->table_module.'_custom_fields_quick,'.
				$this->table_module.'_custom_fields_readonly,'.
				$this->table_module.'_custom_fields_default_value,'.
				$this->table_module.'_custom_fields_put_header';

		$custom_fields = $this->model_custom_fields_class::select(DB::raw($select))
																											->where( $this->table_module.'_custom_fields_status', '=', Config('setting.custom_fields_status_active'))
																											->where( 'company_id', '=', $company_id)
																											->get()->toArray();

		foreach ($custom_fields as $key => $value) 
		{
			$custom_fields[$key][$this->table_module.'_custom_fields_name'] = 'custom_'.$custom_fields[$key][$this->table_module.'_custom_fields_name'];
		}

		$result = array_merge($core_fields, $custom_fields);


		// This is for selection form only status active
		$result = $this->GetFormActive($result);
		// This is for explode and split validation - required
		$result = $this->GetFormValidation($result);

		$result = $this->GetFormExtra($result, $company_id);

		$result = $this->getAllFieldsCondition($result, $company_id);

		$result = $sys->array_sort_custom( $result, $this->table_module.'_fields_sorting', SORT_ASC);

		return $result;
	}

	# Created By Prihan Firmanullah
  	# 2019-12-20
  	# For Save Customize Form
	public function GetFormActive($result=array())
	{
		foreach ($result as $key => $value) 
		{
			if ( isset($value[$this->table_module.'_custom_fields_status']) AND $value[$this->table_module.'_custom_fields_status']==Config('setting.custom_fields_status_inactive') )
			{
				unset($result[$key]);
			}
			elseif ( isset($value[$this->table_module.'_fields_status']) AND $value[$this->table_module.'_fields_status']== Config('setting.fields_status_inactive') )
			{
				unset($result[$key]);
			}
		}

		return $result;
	}


	# Created By Prihan Firmanullah
  	# 2019-12-20
  	# For Save Customize Form
	public function GetQuickFormActive($result=array())
	{
		$temp = $result;

		foreach ($result as $key => $value) 
		{
			if (isset($value[$this->table_module.'_custom_fields_quick'])) 
			{
				if ( isEmpty($value[$this->table_module.'_custom_fields_quick']) AND $value[$this->table_module.'_custom_fields_quick'] == Config('setting.custom_fields_quick_inactive') )
				{
					unset($temp[$key]);
				}
			}
			elseif (isset($value[$this->table_module.'_fields_quick']) OR is_null($value[$this->table_module.'_fields_quick'])) 
			{
				if ( isEmpty($value[$this->table_module.'_fields_quick']) AND $value[$this->table_module.'_fields_quick'] != Config('setting.fields_quick_active') )
				{
					unset($temp[$key]);
				}
			}
		}

		return $temp;
	}

	# Created By Prihan Firmanullah
  	# 2019-12-20
  	# For Save Customize Form
	public function GetFormValidation($result=array())
	{
		foreach ($result as $key => $value) 
		{
			if ( isset($value[$this->table_module.'_fields_validation']) ) 
			{
				$explode = explode('|', $value[$this->table_module.'_fields_validation']);

				$result[$key][$this->table_module.'_fields_validation_multi'] = $explode;

				foreach ($explode as $key2 => $value2) 
				{
					if ( $value2 == 'required' ) 
					{
						$result[$key][$this->table_module.'_fields_validation'] = 'required';
					}
				}
			}
			elseif ( isset($value[$this->table_module.'_custom_fields_validation']) ) 
			{
				$explode = explode('|', $value[$this->table_module.'_custom_fields_validation']);

				$result[$key][$this->table_module.'_custom_fields_validation_multi'] = $explode;

				foreach ($explode as $key2 => $value2) 
				{
					if ( $value2 == 'required' ) 
					{
						$result[$key][$this->table_module.'_custom_fields_validation'] = 'required';
					}
				}
			}
		}

		return $result;
	}

	# Created By Prihan Firmanullah
  	# 2019-12-20
  	# For Save Customize Form
	public function GetFormExtra($result=array(), $company_id=0)
	{
		$sys = new sys();

		foreach ($result as $key => $value) 
		{
			if ( isset($value[$this->table_module.'_custom_fields_serial_id']) )
			{
				$option = $value[$this->table_module.'_custom_fields_options'];

				if (!isEmpty($option))
				{
					if ( $value[$this->table_module.'_custom_fields_input_type'] == 'radio' 
								OR $value[$this->table_module.'_custom_fields_input_type'] == 'multiplecheckbox')
					{
						$result[$key]['extra'] = $this->model_dropdown_class::select(DB::raw('dropdown_options_value, dropdown_options_label, dropdown_options_group'))
																																				->leftjoin( 'dropdown_options', 'dropdown_options.dropdown_serial_id', '=', 'dropdown.dropdown_serial_id')
																																				->where( 'dropdown_name', '=', $option)
																																				->where(function($query) use ($company_id)
																	                                      {
																			                                    $query->where('dropdown.company_id', '=', $company_id)
																			                                    ->orWhere('dropdown.company_id', '=', Config('setting.company_default'));
																			                                  })
																																				->get()
																																				->toArray();
					}

				}				
			}
			else
			{
				$option = $value[$this->table_module.'_fields_options'];

				if ($value[$this->table_module.'_fields_function'] != '') 
				{
					if ( $value[$this->table_module.'_fields_name'] == $this->table_module.'_owner' )
					{
						$result[$key]['extra'] 	= $sys->{$value[$this->table_module.'_fields_function']}($company_id);
					}
				}
				elseif (!isEmpty($option) && $value[$this->table_module.'_fields_name'] != $this->table_module.'_parent_type')
				{
					$data_option = $this->model_dropdown_class::select(DB::raw('dropdown_options_value, dropdown_options_label, dropdown_options_group'))
																				->leftjoin( 'dropdown_options', 'dropdown_options.dropdown_serial_id', '=', 'dropdown.dropdown_serial_id')
																				->where( 'dropdown_name', '=', $option)
																				->where(function($query) use ($company_id)
	                                      {
			                                    $query->where('dropdown.company_id', '=', $company_id)
			                                    ->orWhere('dropdown.company_id', '=', Config('setting.company_default'));
			                                  });
					if ($value[$this->table_module.'_fields_name'] == $this->table_module.'_status'){

						$data_option = $data_option->orderBy('dropdown_options.dropdown_options_label', 'ASC');
					}

					$data_option = $data_option->get()->toArray();


					$result[$key]['extra'] = $data_option;
					if ( $value[$this->table_module.'_fields_name'] == $this->table_module.'_direction') 
					{
						$search_content = array_search('Outbound', array_column($data_option, 'dropdown_options_value'));
						if ( $search_content !== false )
						{
							$result[$key]['content'] = 'Outbound';
						}
					}
					elseif ( $value[$this->table_module.'_fields_name'] == $this->table_module.'_status') 
					{
						$search_content = array_search('Planned', array_column($data_option, 'dropdown_options_value'));
						if ( $search_content !== false )
						{
							$result[$key]['content'] = 'Planned';
						}
					}
				}
				elseif ($value[$this->table_module.'_fields_name'] == $this->table_module.'_parent_type')
				{
					$result[$key]['extra'] = $this->model_module_change_class::select(DB::raw("modules_change.modules_change_name as dropdown_options_value,modules_change.modules_change_label as dropdown_options_label" )) 
																	->leftjoin("modules_group", "modules_group.modules_group_id", "=", "modules_change.modules_group_id") 
																	->where("modules_change.company_id", "=", $company_id) 
																	->whereIn("modules_change.modules_id", [Config("setting.MODULE_LEADS"), Config("setting.MODULE_CONTACTS"), Config("setting.MODULE_ORG")]) 
																	->where("modules_change.modules_change_status", "=", "1") 
																	->where("modules_group.modules_group_status", "=", "1") 
																	->groupBy("modules_change.modules_id")
																	->get()
																	->toArray();
				}

				if ( $value[$this->table_module.'_fields_name'] == $this->table_module.'_date_end') 
				{
					unset($result[$key]);
				}
				elseif ( $value[$this->table_module.'_fields_name'] == 'modified_by') 
				{
					unset($result[$key]);
				}
				elseif ( $value[$this->table_module.'_fields_name'] == 'created_by') 
				{
					unset($result[$key]);
				}
				elseif ( $value[$this->table_module.'_fields_name'] == 'date_created') 
				{
					unset($result[$key]);
				}
				elseif ( $value[$this->table_module.'_fields_name'] == 'date_modified') 
				{
					unset($result[$key]);
				}
			}
		}

		return $result;
	}

	# Created By Prihan Firmanullah
  	# 02-03-2020
  	# For Save Customize Form
	public function save_data_core($request=array(), $company_id=0, $users_id=0, $ajax_form_agent_console=false)
	{
		// save notes parent org, when parent contacts value none
		if ( isset($request['org_serial_id'])) {
			if ($request[$this->table_module.'_parent_id'] == 'none') {
					$request[$this->table_module.'_parent_type'] = 'org';
					$request[$this->table_module.'_parent_id'] = $request['org_serial_id'];
			}
    }

    $input = $request;
		$dataTickets	=	array();

		// Unset data custom field (JSON)
		unset($request["{$this->table_module}_custom_values_text"]);
		unset($request["{$this->table_module}_parent_type"]);
		unset($request["{$this->table_module}_parent_id"]);
		unset($request['_token']);
		unset($request["{$this->table_module}_serial_id"]);
		unset($request["last_view"]);
		unset($request["create_another"]);

		$request[$this->table_module.'_uuid'] = $this->uuid::uuid4()->toString();
		$request['date_created']							= date('Y-m-d H:i:s');
		$request['created_by']								= $users_id;
		$request['company_id']								= $company_id;
		$request[$this->table_module.'_owner']								= isset($request[$this->table_module.'_owner']) ? $request[$this->table_module.'_owner'] : $users_id;
		//$request[$this->table_module.'_first_assign']	= date('Y-m-d H:i:s');

		foreach ($request as $key => $value) {
            
            if (is_array($value)){
                $value_json = json_encode($value, TRUE);                
                $request[$key] = $value_json;
            }
        }

		// this is for strtotime input type = datetime
			if (isset($request[$this->table_module.'_date_start']))
			{
				$date_start = $request[$this->table_module.'_date_start'];
				$request[$this->table_module.'_date_start'] = date('Y-m-d H:i:s', strtotime($request[$this->table_module.'_date_start']));
				$request[$this->table_module.'_date_end'] = date('Y-m-d H:i:s', strtotime($date_start));
			}
	
		// if ( !empty($request[$this->table_module.'_date_end']) )
		// {
		// 	if (isset($request[$this->table_module.'_date_end']))
		// 	{
		// 		$request[$this->table_module.'_date_end'] = date('Y-m-d H:i:s', strtotime($request[$this->table_module.'_date_end']. "-7 hours"));
		// 	}
		// }
		// else
		// {
		// 	if (isset($request[$this->table_module.'_date_end']))
		// 	{
		// 		$request[$this->table_module.'_date_end'] = date('Y-m-d H:i:s', strtotime($date_start. "-7 hours"));
		// 	}	
		// }

		if ( isset($request['tickets_serial_id']) )
		{
			if ( !empty($request['tickets_serial_id']) )
			{
				$relSerialId	=	(int) $request['tickets_serial_id'];

				$dataTickets = $this->model_tickets_class::select('b.tickets_status_name')
																									->leftjoin("tickets_status as b", "tickets.tickets_status", "=", "b.tickets_status_serial_id")
																									->where('tickets.tickets_serial_id', '=', $relSerialId)
																									->where('tickets.company_id', '=', $company_id)
																									->where('b.company_id', '=', $company_id)
																									->where('b.tickets_status', '=', config('setting.FIELDS_ACTIVE'))
																									->where('tickets.deleted', '=', config('setting.NOT_DELETED'))
																									->first();

				if ( !isEmpty($dataTickets) )
				{
					$statusName	=	json_decode(json_encode($dataTickets), true);

					$request['tickets_status']	=	$statusName['tickets_status_name'];
				}
			}
		}


		if ( isset($request[$this->table_module.'_date_start']) AND isset($request[$this->table_module.'_date_end']) ) 
		{
			if ( !empty($request[$this->table_module.'_date_start']) AND !empty($request[$this->table_module.'_date_end']) ) 
			{
				if ($ajax_form_agent_console == true) 
				{
					$request[$this->table_module.'_date_end'] = date('Y-m-d H:i:s');
				}

				$d1=new DateTime($request[$this->table_module.'_date_start']); 
				$d2=new DateTime($request[$this->table_module.'_date_end']); 
				$diff=$d2->diff($d1); // function interval diff php
				$hour 		= $diff->h; // function interval diff php


				if ( $diff->d > 0 ) 
				{
					$sum_day 	= $diff->d * 24;
					$hour 		= $diff->h + $sum_day;
				}

				// Set calls_duration_hours
				if ( $hour == 0 OR $hour == 1 OR $hour == 2 OR $hour == 3 OR $hour == 4 OR $hour == 5 OR $hour == 6 OR $hour == 7 OR $hour == 8 OR $hour == 9 ) 
				{
					$request['calls_duration_hours'] 		= '0'.$hour;
				}
				elseif ( $hour > 24 )
				{
					$request['calls_duration_hours'] 		= '24';
				}
				else
				{
					$request['calls_duration_hours'] 		= $hour;
				}

				// Set calls_duration_minutes
				if ( $diff->i == 0 ) 
				{
					$request['calls_duration_minutes']	= '00';
				}
				elseif ( $diff->i == 1 ) 
				{
					$request['calls_duration_minutes']	= '01';
				}
				elseif ( $diff->i == 2 ) 
				{
					$request['calls_duration_minutes']	= '02';
				}
				elseif ( $diff->i == 3 ) 
				{
					$request['calls_duration_minutes']	= '03';
				}
				elseif ( $diff->i == 4 ) 
				{
					$request['calls_duration_minutes']	= '04';
				}
				elseif ( $diff->i == 5 ) 
				{
					$request['calls_duration_minutes']	= '05';
				}
				elseif ( $diff->i == 6 ) 
				{
					$request['calls_duration_minutes']	= '06';
				}
				elseif ( $diff->i == 7 ) 
				{
					$request['calls_duration_minutes']	= '07';
				}
				elseif ( $diff->i == 8 ) 
				{
					$request['calls_duration_minutes']	= '08';
				}
				elseif ( $diff->i == 9 ) 
				{
					$request['calls_duration_minutes']	= '09';
				}
				elseif ( $diff->i == 10 ) 
				{
					$request['calls_duration_minutes']	= '10';
				}
				elseif ( $diff->i == 11 ) 
				{
					$request['calls_duration_minutes']	= '11';
				}
				elseif ( $diff->i == 12 ) 
				{
					$request['calls_duration_minutes']	= '12';
				}
				elseif ( $diff->i == 13 ) 
				{
					$request['calls_duration_minutes']	= '13';
				}
				elseif ( $diff->i == 14 ) 
				{
					$request['calls_duration_minutes']	= '14';
				}
				elseif ( $diff->i == 15 ) 
				{
					$request['calls_duration_minutes']	= '15';
				}
				elseif ( $diff->i == 16 ) 
				{
					$request['calls_duration_minutes']	= '16';
				}
				elseif ( $diff->i == 17 ) 
				{
					$request['calls_duration_minutes']	= '17';
				}
				elseif ( $diff->i == 18 ) 
				{
					$request['calls_duration_minutes']	= '18';
				}
				elseif ( $diff->i == 19 ) 
				{
					$request['calls_duration_minutes']	= '19';
				}
				elseif ( $diff->i == 20 ) 
				{
					$request['calls_duration_minutes']	= '20';
				}
				elseif ( $diff->i == 21 ) 
				{
					$request['calls_duration_minutes']	= '21';
				}
				elseif ( $diff->i == 22 ) 
				{
					$request['calls_duration_minutes']	= '22';
				}
				elseif ( $diff->i == 23 ) 
				{
					$request['calls_duration_minutes']	= '23';
				}
				elseif ( $diff->i == 24 ) 
				{
					$request['calls_duration_minutes']	= '24';
				}
				elseif ( $diff->i == 25 ) 
				{
					$request['calls_duration_minutes']	= '25';
				}
				elseif ( $diff->i == 26 ) 
				{
					$request['calls_duration_minutes']	= '26';
				}
				elseif ( $diff->i == 27 ) 
				{
					$request['calls_duration_minutes']	= '27';
				}
				elseif ( $diff->i == 28 ) 
				{
					$request['calls_duration_minutes']	= '28';
				}
				elseif ( $diff->i == 29 ) 
				{
					$request['calls_duration_minutes']	= '29';
				}
				elseif ( $diff->i == 30 ) 
				{
					$request['calls_duration_minutes']	= '30';
				}
				elseif ( $diff->i == 31 ) 
				{
					$request['calls_duration_minutes']	= '31';
				}
				elseif ( $diff->i == 32 ) 
				{
					$request['calls_duration_minutes']	= '32';
				}
				elseif ( $diff->i == 33 ) 
				{
					$request['calls_duration_minutes']	= '33';
				}
				elseif ( $diff->i == 34 ) 
				{
					$request['calls_duration_minutes']	= '34';
				}
				elseif ( $diff->i == 35 ) 
				{
					$request['calls_duration_minutes']	= '35';
				}
				elseif ( $diff->i == 36 ) 
				{
					$request['calls_duration_minutes']	= '36';
				}
				elseif ( $diff->i == 37 ) 
				{
					$request['calls_duration_minutes']	= '37';
				}
				elseif ( $diff->i == 38 ) 
				{
					$request['calls_duration_minutes']	= '38';
				}
				elseif ( $diff->i == 39 ) 
				{
					$request['calls_duration_minutes']	= '39';
				}
				elseif ( $diff->i == 40 ) 
				{
					$request['calls_duration_minutes']	= '40';
				}
				elseif ( $diff->i == 41 ) 
				{
					$request['calls_duration_minutes']	= '41';
				}
				elseif ( $diff->i == 42 ) 
				{
					$request['calls_duration_minutes']	= '42';
				}
				elseif ( $diff->i == 43 ) 
				{
					$request['calls_duration_minutes']	= '43';
				}
				elseif ( $diff->i == 44 ) 
				{
					$request['calls_duration_minutes']	= '44';
				}
				elseif ( $diff->i == 45 ) 
				{
					$request['calls_duration_minutes']	= '45';
				}
				elseif ( $diff->i == 46 ) 
				{
					$request['calls_duration_minutes']	= '46';
				}
				elseif ( $diff->i == 47 ) 
				{
					$request['calls_duration_minutes']	= '47';
				}
				elseif ( $diff->i == 48 ) 
				{
					$request['calls_duration_minutes']	= '48';
				}
				elseif ( $diff->i == 49 ) 
				{
					$request['calls_duration_minutes']	= '49';
				}
				elseif ( $diff->i == 50 ) 
				{
					$request['calls_duration_minutes']	= '50';
				}
				elseif ( $diff->i == 51 ) 
				{
					$request['calls_duration_minutes']	= '51';
				}
				elseif ( $diff->i == 52 ) 
				{
					$request['calls_duration_minutes']	= '52';
				}
				elseif ( $diff->i == 53 ) 
				{
					$request['calls_duration_minutes']	= '53';
				}
				elseif ( $diff->i == 54 ) 
				{
					$request['calls_duration_minutes']	= '54';
				}
				elseif ( $diff->i == 55 ) 
				{
					$request['calls_duration_minutes']	= '55';
				}
				elseif ( $diff->i == 56 ) 
				{
					$request['calls_duration_minutes']	= '56';
				}
				elseif ( $diff->i == 57 ) 
				{
					$request['calls_duration_minutes']	= '57';
				}
				elseif ( $diff->i == 58 ) 
				{
					$request['calls_duration_minutes']	= '58';
				}
				elseif ( $diff->i == 59 ) 
				{
					$request['calls_duration_minutes']	= '59';
				}
				else
				{
					$request['calls_duration_minutes']	= '00';
				}
			}
		}

		// Insert into database core data
		$save_fields = $this->model_class::create($request);
		$last_id 		= $save_fields[$this->table_module.'_serial_id'];

    //for check data from customize by user
		$format_generate_unique_id = $this->model_generate_custom_unique_id::select('format_custom_unique_id')
	  																			->where('modules', '=', $this->table_module)
	  																			->where('company_id', '=', $company_id)
	  																			->orderBy('id', 'DESC')
	  																			->first();

    //for generate unique id based customize user
	  if(!isEmpty($format_generate_unique_id) AND empty($request[$this->table_module.'_unique_id']))
    {
      $newformat = json_decode($format_generate_unique_id['format_custom_unique_id'], true);
      $newformat = array_values($newformat)[0];

      $previous_customize_id = $this->model_modules_customize_id::select('last_customize_id')
                                      ->where('company_id', '=', $company_id)
                                      ->where('module','=', $this->table_module)
                                      ->first();
      if(isEmpty($previous_customize_id) AND $previous_customize_id == null )
      {
        $data_cutomize_id['company_id']						= $company_id;
        $data_cutomize_id['module']								= $this->table_module;
        $data_cutomize_id['last_customize_id']    = 0;
        $save_data_customide_id = $this->model_modules_customize_id::create($data_cutomize_id);

        $previous_customize_id = $this->model_modules_customize_id::select('last_customize_id')
                                      ->where('company_id', '=', $company_id)
                                      ->where('module','=', $this->table_module)
                                      ->first();
      }

      $last_customize_id = json_decode(json_encode($previous_customize_id), true);
      $last_customize_id = $last_customize_id['last_customize_id']+1;

      $update_request[$this->table_module.'_unique_id']	    = $newformat['generate_custom_char'].'-'.str_pad($last_customize_id, $newformat['generate_custom_total'], '0', STR_PAD_LEFT);
			$save_fields[$this->table_module.'_unique_id']        = $update_request[$this->table_module.'_unique_id'];

			$this->model_class::where($this->table_module.'_serial_id', '=', $last_id)
												->where('company_id', '=', $company_id)
												->update($update_request);

			$update_modules_customize_id['last_customize_id']	= $last_customize_id;

			$this->model_modules_customize_id::where('module', '=', $this->table_module)
												->where('company_id', '=', $company_id)
												->update($update_modules_customize_id);
    }
		// Auto Generate Unique ID
		elseif ( empty($request[$this->table_module.'_unique_id']) )
		{
			$update_request[$this->table_module.'_unique_id']	= 'Calls-'.sprintf("%'.010d", $company_id.$last_id);
			$save_fields[$this->table_module.'_unique_id'] = $update_request[$this->table_module.'_unique_id'];

			$this->model_class::where($this->table_module.'_serial_id', '=', $last_id)
												->where('company_id', '=', $company_id)
												->update($update_request);
		}

		return json_decode(json_encode($save_fields), true);
	}

	# Created By Prihan Firmanullah
  	# 2019-12-20
  	# For Save Customize Form
	public function save_data_custom($request=array(), $company_id=0)
	{
		$sys = new sys();
		$input = $request;

		unset($input[$this->table_module.'_serial_id']);

		foreach ($input as $key => $value) 
		{
			// // Query for get values_maps by custom_fields_serial_id
			$key = $sys->library_values_maps_by_name($key, $this->table_module, $company_id);

			if ( is_array($value) )
			{
				$value_json 	= json_encode($value, TRUE);
				$data[$key] 	= $value_json;
			}	
			elseif ( is_object($value) ) 
			{
				# if input type custom is file
				$file = $this->AWS_SaveDataFile($value, $company_id);
				$data[$key] = $file;
			}			
			else
			{
				$data[$key] = $value;
			}
		}

		$data[$this->table_module.'_serial_id'] = $request[$this->table_module.'_serial_id'];
		$data['company_id']											= $company_id;

		$save = $this->model_custom_values__class::create($data);

		return json_decode(json_encode($save),true);
	}

	# Created By Fitri Mahardika
  	# 04/02/2020
  	# For Save Customize Form
	public function GetDetailData($data_uuid='', $company_id=0)
	{
		$sys = new sys();

		// Define variable
		$result 			= array();
		$alias 				= 'a'; // This is for query alias
		$temp_select 	= '';  // This is for list temporary query select
		
		if ( $data_uuid == '' OR $company_id == 0 OR $company_id == '' )
		{
			return $result;
		}

		// Get data find by data_uuid
		$notes_fields = $this->table_module.".*";
		$query = $this->model_class::where($this->table_module.'.company_id', '=', $company_id);

		// Check custom fields exists ?
		$temp_select = $sys->library_select_custom_fields_as_c_name($this->table_module, $company_id);
		if ( countCustom($temp_select) > 0 )
		{
			$select = implode(', ', $temp_select);
			$select = ",".$select;
		}
		else
		{
			$select = '';
		}

		$query->leftjoin($this->table_module.'_custom_values_ as mcv', function($join) use ($company_id)
						{
							$join->on('mcv.'.$this->table_module.'_serial_id', '=', $this->table_module.'.'.$this->table_module.'_serial_id')
							->where('mcv.company_id', '=', $company_id);
						});

		$query->leftjoin('users', 'users.id', '=', $this->table_module.'.'.$this->table_module.'_owner'); // This id for get name owner
		$query->leftjoin('users as a2', $this->table_module.'.created_by', '=', 'a2.id');
		$query->leftjoin('users as a3', $this->table_module.'.modified_by', '=', 'a3.id');
		$query->select(DB::raw($notes_fields.$select.', users.name as '.$this->table_module.'_owner, users.id as '.$this->table_module.'_owner_id, a2.users_uuid as created_by_name_uuid, a3.users_uuid as modified_by_name_uuid')); // For select fields name || module_owner_id (for merge data)
		$query->where($this->table_module.'_uuid', '=', $data_uuid); // For condition where uuid
		$query->where( $this->table_module.'.company_id', '=', $company_id);
		$query->where($this->table_module.'.deleted', '=', Config('setting.NOT_DELETED'));
		$query = $query->first(); // Get data first

		if (countCustom($query) > 0 ) 
		{
			$result = $query->toArray();
		}

		return $result;
	}

	public function getDataDocuments($data=array(), $company_id=0)
	{

		$sys_api_documents 	= new sys_documents();
		$attachments_aws = array();

		if (countCustom($data) > 0 )
		{
			foreach ($data['data'] as $key => $value)
			{

				$query = $this->model_sys_rel_class::where('rel_from_module', '=', 'documents')
																					 ->where('rel_to_module', '=', $this->table_module)
																					 ->where('rel_to_id', '=', $value[$this->table_module.'_serial_id'])
																					 ->get();

				if (countCustom($query) > 0 )
				{
					foreach ($query as $key2 => $value2)
					{
						$query2 = $this->model_documents_class::where('documents_serial_id', '=', $value2['rel_from_id'])->first();

						if (countCustom($query2) > 0 )
						{
							// Format extension : jpg or png or pdf or mp4 or zip or etc
							$format_ext = substr($query2['documents_type'], strpos($query2['documents_type'],'/')+1);

							$attachments_aws[$key][$key2]['doc_uuid'] 	= $query2['documents_uuid'];
							$attachments_aws[$key][$key2]['doc_url'] 		= $sys_api_documents->getDocumentsFromAwsExport($query2['documents_name'], $company_id);
							$attachments_aws[$key][$key2]['doc_type'] 	= $query2['documents_type'];
							if ( $format_ext == 'png' OR $format_ext == 'jpg' OR $format_ext == 'jpeg' OR $format_ext == 'bmp' )
							{
								$attachments_aws[$key][$key2]['doc_image'] 	= $sys_api_documents->getThumbnailsFromAws($query2['documents_name'], $company_id, 'thumbs_sm');
							}
							$attachments_aws[$key][$key2]['doc_check_extension'] 	= $format_ext;
						}
					}
				}
			}
		}

		return $attachments_aws;
	}

	# Created By Fitri Mahardika
  	# 05/02/2020
  	# For sys rel leads, contacts and org
	public function GetDataSysRel($data=array(), $company_id=0)
  	{
  		$data_serial_id = $data[$this->table_module.'_serial_id'];
  		$query = $this->model_sys_rel_class::where('rel_from_module', '=', $this->table_module)
  																		 ->where('rel_from_id', '=', $data_serial_id)
  																		 ->first();

	  	$data[$this->table_module.'_parent_type'] = '';
	  	$data[$this->table_module.'_parent_id'] 	= '';
			$data[$this->table_module.'_parent_unique'] 	= '';
	  	$data['drildown_uuid'] 										= '';
	  	if (countCustom($query) > 0 )
	  	{
	  		if ( $query['rel_to_module'] == 'org' )
	  		{
	  			$query2 = $this->model_org_class::select(DB::raw('org_name as name, org_uuid as uuid, org_unique_id as unique_id, convert_id as convert_id'))
	  																			->where('org_serial_id', '=', $query['rel_to_id'])
	  																			->where('company_id', '=', $company_id)
	  																			->first();
	  		}
	  		elseif ( $query['rel_to_module'] == 'leads' )
	  		{
	  			$query2 = $this->model_leads_class::select(DB::raw("CONCAT(COALESCE(leads_first_name, ''),' ', COALESCE(leads_last_name, '')) as name, leads_uuid as uuid, leads_unique_id as unique_id, leads_convert_id as convert_id"))
	  																			->where('leads_serial_id', '=', $query['rel_to_id'])
	  																			->where('company_id', '=', $company_id)
	  																			->first();
	  		}
	  		elseif ( $query['rel_to_module'] == 'contacts' )
	  		{
	  			$query2 = $this->model_contacts_class::select(DB::raw("CONCAT(COALESCE(contacts_first_name, ''),' ', COALESCE(contacts_last_name, '')) as name, contacts_uuid as uuid, contacts_unique_id as unique_id, convert_id as convert_id"))
	  																			->where('contacts_serial_id', '=', $query['rel_to_id'])
	  																			->where('company_id', '=', $company_id)
	  																			->first();
	  		}

	  		if ( isset($query2) AND countCustom($query2) > 0 )
	  		{
		  		$data[$this->table_module.'_parent_id'] 	= $query2['name']; // SET PARENT ID
		  		$data['drildown_uuid'] 										= $query2['uuid']; // SET UUID DRILDOWN
					$data[$this->table_module.'_parent_unique'] = $query2['unique_id']; //SET UNIQUE ID
					if ( $query['rel_to_module'] == 'leads' && $query2['convert_id'] > 0 )
					{
						$data[$this->table_module.'_parent_convert'] = "Converted"; //SET UNIQUE ID
					}
	  		}
		  	$data[$this->table_module.'_parent_type'] 	= $query['rel_to_module']; // SET PARENT TYPE
	  	}

	  	// $result 	= $data->toArray();

	  	return $data;
  	}

  	# Created By Fitri Mahardika
  	# 05/02/2020
  	# For sys rela leads, contacts and org
  	public function GetNameOwner($data)
		{
			$module_owner = $data[$this->table_module.'_owner'];

			if ($module_owner != 0 OR $module_owner != null) {
				$name = $this->model_users_class::where('id', '=', $module_owner)->get()->toArray();

				if ( countCustom($name) > 0 ) 
				{
					$data[$this->table_module.'_owner'] = $name[0]['name'];
					$data['owner_id'] = $module_owner;
				}
			}

			return $data;
		}

		public function GetNameOwnerNew($data)
		{
			$module_owner = $data[$this->table_module.'_owner'];
			$module_created_by = $data['created_by'];

			if ($module_owner != 0 OR $module_owner != null) {
				$name = $this->model_users_class::where('id', '=', $module_owner)->get()->toArray();

				if ( countCustom($name) > 0 ) 
				{
					$data[$this->table_module.'_owner_name'] =  $name[0]['name'];
				}
			}

			if ($module_created_by != 0 OR $module_created_by != null) {
				$name = $this->model_users_class::where('id', '=', $module_created_by)->get()->toArray();

				if ( countCustom($name) > 0 ) 
				{
					$data['created_by_name'] =  $name[0]['name'];
				}
			}

			return $data;
		}

	# Created By Fitri Mahardika
  	# 05/02/2020
  	# For Detail Deals
	public function GetRelatedDealsDetail($data)
	{
		if ( $data['deals_serial_id'] != 0 )
		{
				$get = $this->model_deals_class::where('deals_serial_id', '=', $data['deals_serial_id'])->first();
				if ( countCustom($get) > 0 )
				{
					 	$data['deals_serial_id'] 			= $get['deals_name'];
					 	$data['deals_uuid'] 			= $get['deals_uuid'];
				}

		}else{
			$data['deals_serial_id'] = '';
			$data['deals_uuid'] 		 = '';
		}

		return $data;
	}

	# Created By Fitri Mahardika
  	# 05/02/2020
  	# For Detail Projects
	public function GetRelatedProjectsDetail($data)
	{
		if ( $data['projects_serial_id'] != 0 )
		{
				$get = $this->model_projects_class::where('projects_serial_id', '=', $data['projects_serial_id'])->first();

				if ( countCustom($get) > 0 )
				{
					 	// add by rendi 27.02.2019 -> get uuid for drilldown
					 	$data['projects_uuid'] 					= $get['projects_uuid'];
					 	// end
					 	$data['projects_serial_id'] 			= $get['projects_name'];
				}

		}else{
			$data['projects_serial_id'] = '';
		}

		return $data;
	}

	# Created By Fitri Mahardika
  	# 05/02/2020
  	# For Detail Issue
		public function GetRelatedIssueDetail($data)
	{
		if ( $data['issue_serial_id'] != 0 )
		{
				$get = $this->model_issue_class::where('issue_serial_id', '=', $data['issue_serial_id'])->first();

				if ( countCustom($get) > 0 )
				{
					 	// add by rendi 27.02.2019 -> get uuid for drilldown
					 	$data['issue_uuid'] 				= $get['issue_uuid'];
					 	// end
					 	$data['issue_serial_id'] 			= $get['issue_name'];
				}

		}else{
			$data['issue_serial_id'] = '';
		}

		return $data;
	}

	public function GetRelatedTicketsDetail($data)
	{
		if ( $data['tickets_serial_id'] != 0 )
		{
			$get = $this->model_tickets_class::where('tickets_serial_id', '=', $data['tickets_serial_id'])->first();

			if ( countCustom($get) > 0 )
			{
			 	$data['tickets_uuid'] 		 = $get['tickets_uuid'];
			 	$data['tickets_serial_id'] = $get['tickets_name'];
			}
		}
		else
		{
			$data['tickets_serial_id'] = '';
		}

		return $data;
	}

	# Created By Prihan Firmanullah
  	# 2019-12-20
  	# For Save Customize Form
	public function DeleteOneData($data_uuid='', $users_id='')
	{
		$data_update = array(
			'deleted' => Config('setting.DELETED'),
			'date_modified' => date('Y-m-d H:i:s'),
			'modified_by' => $users_id,
		);

		$this->model_class::where( $this->table_module.'_uuid', '=', $data_uuid)
												->update($data_update);

		return true;
	}

	# Created By Fitri Mahardika
  	# 04/02/2020
  	# For Save Process Update Data Calls
	public function processUpdateData($request=array(), $company_id=0, $users_id=0)
	{
		# Define Sys
		$sys = new sys();

		$uuid4 = $this->uuid::uuid4();
		$data_serial_id = $this->GetSerialIdByUuid($request[$this->table_module.'_uuid'], $company_id); // Get data_serial_id. result => 1/2/3/4/5,etc

		//for check input from approval or not
		$approval_data	=	isset($request['approval_data']) ? $request['approval_data'] : array();
		if ( $approval_data )
		{
			unset($request['approval_data']);
		}

		$userApprover	=	isset($request['userApprover']) ? $request['userApprover'] : 0;
		if ( $userApprover > 0 )
		{
			unset($request['userApprover']);
		}

		// Container for money fields info
		$custom_money_fields_info = array();
		// Get core & custom fields

		# Created By Pratama Gilang
		# 29-11-2019
		# For Slice Custom fields And Core Fields
		$data_core = array();
		$data_custom = array();
		foreach ($request as $key => $value) 
		{
			$prefix_custom  = substr($key, 0, 7);
			if ($prefix_custom == "custom_") 
			{
				$fields_name = substr_replace($key, '', 0, 7); ;
				$data_custom[$fields_name] = $request[$key];
				unset($request[$key]);
			}
			else
			{
				$data_core[$key] = $request[$key];
			}
		}


    // Checking for value array, convert value to json
		foreach ($data_custom as $key => $value) 
		{
			$custom_id = $this->get_custom_fields_by_id($key, $company_id);

			if (!isEmpty($custom_id)) 
			{
				$request[$this->table_module.'_custom_values_text'][$custom_id[$this->table_module.'_custom_fields_serial_id']] = $value;
			}

			// Query for get values_maps by custom_fields_serial_id
			$key = $sys->library_values_maps_by_name($key, $this->table_module, $company_id);

			if ( is_array($value) )
			{
				$value_json 	= json_encode($value, TRUE);
				$update[$key] 	= $value_json;
			}
			elseif ( is_object($value) ) 
			{
				# if input type custom is file
				$file = $this->AWS_SaveDataFile($value, $company_id);
				$update[$key] = $file;
			}	
			else
			{
				if(strpos($value,'|') ==  TRUE)
				{
					#For split teks Before result exp. 93234-23453245-5fdsg|Barantum
					$value_text 		= substr($value, strpos($value,'|')+1); //get value teks exp. org : Barantum
					$value_related_uuid = substr($value, 0, strpos($value,'|')); // get value uuid. org: 324-235df-kffgfd
					#END
					$update[$key] 		= $value_text;
				}
				else
				{
					$update[$key] 			= $value;
				}
			}
		}

		if (isset($update)) 
		{
			// Update custom values
			$query = $this->model_custom_values__class::where('company_id', '=', $company_id)
							->where($this->table_module.'_serial_id', '=', $data_serial_id)
							->first();

			if (!empty($query)) 
			{
				$this->model_custom_values__class::where('company_id', '=', $company_id)
							->where($this->table_module.'_serial_id', '=', $data_serial_id)
							->update($update);
			} else {
				$update[$this->table_module.'_serial_id'] = $data_serial_id;
				$update['company_id'] = $company_id;
				$this->model_custom_values__class::create($update);
			}
		}
		// Update custom values


  	$old_data = $this->old_data_log($request[$this->table_module.'_uuid'], $company_id);
		// re-format for syslog
		if(isset($old_data[$this->table_module."_date_start"]))
		{
			// $old_data[$this->table_module."_date_start"] = date('Y-m-d H:i:s', strtotime($old_data[$this->table_module.'_date_start']. "- 7 hours"));
			$old_data[$this->table_module."_date_start"] = date('Y-m-d H:i:s', strtotime($old_data[$this->table_module.'_date_start']));
		}
		if(isset($old_data[$this->table_module."_date_end"]))
		{
			// $old_data[$this->table_module."_date_end"] = date('Y-m-d H:i:s', strtotime($old_data[$this->table_module.'_date_end']. "- 7 hours"));
			$old_data[$this->table_module."_date_end"] = date('Y-m-d H:i:s', strtotime($old_data[$this->table_module.'_date_end']));
		}
		// end re-format for syslog
		
		// Get all data input Post
		$input = $data_core;

		// Unset data custom field
		unset($input["_token"]);
		unset($input['last_view']);
		unset($input[$this->table_module."_custom_values_text"]);
		unset($input[$this->table_module."_parent_type"]);
		unset($input[$this->table_module."_parent_id"]);
		unset($input["create_another"]);

		$calls_notifications = isset($input['calls_notifications']);
		unset($input['calls_notifications']);
		
		// For Handle : If the input request of the owner is not the same as the numeric, then it is certain that the data is selected owner who is not active.
		if ( isset($input[$this->table_module.'_owner']) )
		{
			if ( !is_numeric($input[$this->table_module.'_owner']) )
			{
				unset($input[$this->table_module.'_owner']);
			}
		}

		$input['date_modified']								= date('Y-m-d H:i:s');
		$input['modified_by']									= $users_id;

		// this is for strtotime input type = datetime
		if ( isset($request[$this->table_module.'_date_start']) && $request[$this->table_module.'_date_start'] != "" )
		{
			$input[$this->table_module.'_date_start'] = date('Y-m-d H:i:s', strtotime($request[$this->table_module.'_date_start']));
			$input[$this->table_module.'_date_end'] = date('Y-m-d H:i:s', strtotime($request[$this->table_module.'_date_start']));
		}
		// Insert into database core data
  		$this->model_class::where($this->table_module.'_serial_id', '=', $data_serial_id)
  									->update($input);

  		# UPDATE SYS REL : PARENT TYPE AND RELATED TO
  	$query = $this->model_sys_rel_class::where('rel_from_module', '=', $this->table_module)
																		 ->where('rel_from_id', '=', $data_serial_id)
																		 ->first();

  	if (countCustom($query) > 0 )
  	{
  		if(!empty($request[$this->table_module.'_parent_type']) AND !empty($request[$this->table_module.'_parent_id']))
  		{
				$check_sysrel = DB::table($request[$this->table_module.'_parent_type'])
				                          ->where($request[$this->table_module.'_parent_type'].'_serial_id', '=', $request[$this->table_module.'_parent_id'])
				                          ->where('company_id', '=', $company_id)
				                          ->first();

				if (!isEmpty($check_sysrel)) 
				{
	  			$rel['rel_to_module']     = $request[$this->table_module.'_parent_type'];
			    $rel['rel_to_id'] 				= $request[$this->table_module.'_parent_id'];

					$this->save_upcoming_activities($data_core, $this->table_module.'_serial_id', $company_id);
					$this->save_last_activities($data_core, $this->table_module.'_serial_id', $company_id);
			    if(isset($data_core['calls_status']) && $data_core['calls_status'] == 'Held')
					{
						$this->save_last_completed_activities($data_core, $data_serial_id, $company_id);
					}
					$this->model_sys_rel_class::where('rel_serial_id', '=', $query['rel_serial_id'])
				  													->update($rel);
				}
				else
				{
					unset($request[$this->table_module.'_parent_type']);
					unset($request[$this->table_module.'_parent_id']);
				}
  		}
  	}
  	else
  	{
  		if ( !empty($request[$this->table_module.'_parent_type']) AND !empty($request[$this->table_module.'_parent_id']) )
  		{
  			$check_sysrel = DB::table($request[$this->table_module.'_parent_type'])
				                          ->where($request[$this->table_module.'_parent_type'].'_serial_id', '=', $request[$this->table_module.'_parent_id'])
				                          ->where('company_id', '=', $company_id)
				                          ->first();

				if (!isEmpty($check_sysrel)) 
				{
		  		$rel['rel_from_module'] = $this->table_module;
				  $rel['rel_from_id'] 		= $data_serial_id;
				  $rel['rel_to_module']   = $request[$this->table_module.'_parent_type'];
			    $rel['rel_to_id'] 			= $request[$this->table_module.'_parent_id'];
			    $rel['company_id'] 			= $company_id;
				  $save_rel_sys 	= $this->model_sys_rel_class::create($rel);
				}
				else
				{
					unset($request[$this->table_module.'_parent_type']);
					unset($request[$this->table_module.'_parent_id']);
				}
			}
  	}
	  # END

		if(isset($request[$this->table_module."_date_start"]))
		{
			$request[$this->table_module."_date_start"] = date('Y-m-d H:i:s', strtotime($request[$this->table_module.'_date_start']. "+ 7 hours"));
		}
		if(isset($request[$this->table_module."_date_end"]))
		{
			$request[$this->table_module."_date_end"] = date('Y-m-d H:i:s', strtotime($request[$this->table_module.'_date_end']. "+ 7 hours"));
		}

	  $syslog_action = $sys->log_update($old_data, $request, $this->table_module, $company_id);

		if ( !isEmpty($syslog_action) ) 
		{
			if (!isEmpty($approval_data)) {
				$usersName	=	"";
				$dateApprovalCreated	=	",";

				$dateNow	= date('d F Y H:i');
				$dateNow	=	date('d F Y H:i', strtotime($dateNow. "+7 hours"));

				if ( isset($approval_data['date_created']) )
				{
					if ( !empty($approval_data['date_created']) )
					{
						$dateApproval	=	date('d F Y H:i', strtotime($approval_data['date_created']. "+7 hours"));
				
						$dateApprovalCreated	=	$dateApprovalCreated."Date Approval &#8594 ".$dateApproval.",";
					}
				}

				if ( $userApprover > 0 )
				{
					$usersName	=	$this->users_data($userApprover);
					if ( !isEmpty($usersName) )
					{
						$usersName	=	$usersName['name'];
					}
				}
	
				$addLogApproval	=	"From &#8594"." Approval".$dateApprovalCreated."Date Approved &#8594 ".$dateNow.",Approved By &#8594 ".$usersName.",";
				$syslog_action  = $addLogApproval.$syslog_action;
			}

			$syslog = $sys->sys_api_syslog( $syslog_action, 'update', $this->table_module, $data_serial_id, $users_id, $company_id );
		}
		

  	$data_update = $this->model_class::where($this->table_module.'_serial_id', '=', $data_serial_id)
	  									->first();

		if($calls_notifications){
			$result = json_decode($data_update,true);
			if($result[$this->table_module.'_status'] == "Planned"){
				$sys_push = new sys_push();
				$sys_push->sendToNotificationCalls($result);
			}
		}

		elasticAddData($data_serial_id, $company_id, $this->table_module);		  									
		return $data_update;
	}

	# Created By Prihan Firmanullah
  	# 2019-12-20
  	# For Save Customize Form
	public function GetSerialIdByUuid($data_uuid='', $company_id)
	{
		// Define variable
		$result = 0;

		$query 	= $this->model_class::select($this->table_module.'_serial_id')
																->where($this->table_module.'_uuid', '=', $data_uuid)
																->where('company_id', '=', $company_id)
																->first();

		if ( countCustom($query) > 0 ) 
		{
			$result = $query[$this->table_module.'_serial_id'];
		}

		return $result;
	}

	# Created By Prihan Firmanullah
  	# 2019-12-20
  	# For Save Customize Form
	public function data_search($input=array(), $company_id=0, $temp_alias=array(), $fields_custom = array(), $b = 'b')
	{
		$result 	= array();
		$temp 		= array();

		$i = 0;
		if (countCustom($input) > 0) 
		{
			# defined variable
			$contents 	= $input;

			# Remove fields is empty			 
			unset($contents['_token']);
			unset($contents['subaction']);
			if ( isset($contents['filterApproval']) )
			{
				unset($contents['filterApproval']);
			}
			
			foreach ($contents as $key => $value) 
			{
				$check_date = substr($key, 0, 5); // get field type date
				$check_custom = substr($key, 0, 7); // get field type custom
				$key_val = substr($key, 0, (strlen($key) - 4)); // get field type date

				if (($check_date == "date_" || strpos($key, 'date')) && ($value == "null" || $value == "")) 
				{
					unset($contents[$key]); // remove date_created_opp == null
					unset($contents[str_replace('_opp', '', $key)]); // remove date_created == ''
				} 
				elseif($value == 'contains' && empty($contents[$key_val]))
				{
					unset($contents[$key]);
				}
				elseif (($value == "null" || $value == "") && $check_date != "date_" && $check_custom != "custom_" && (isset($contents[$key.'_opp']) && $contents[$key.'_opp'] != 'is_empty' && $contents[$key.'_opp'] != 'is_not_empty') ) 
				{
					//unset($contents[$key.'_opp']);
					unset($contents[$key]);
				}
			}

			# Change format operator, ex : contains => LIKE '%keywords%'
			$result['core']				= array();
			$result['date']				= array();
			$result['custom'] 		= array();
			$result['date_custom'] = array();

			foreach ($contents as $key => $value) 
			{
				$container 		= array();
				$prefix_opp 	= substr($key, -4); // get prefix :  _opp ex. leads_first_name_opp
				$prefix_date 	= substr($key, 0, 5); // get prefix : date_ ex. date_date_created_opp
				$prefix_custom	= substr($key, 0, 7); // get prefix : custom_ ex. custom_leads_religion_opp

				$check_value 	= $this->checkDateType($value);
				if ($prefix_opp == "_opp" && $prefix_date == "date_" && $check_value == TRUE) 
				{ 
					//Process generate format FIELD TYPE DATE, ex.: date_date_created
					$field_name_opp 	= $key;
					$field_name 			= str_replace('_opp', '', $field_name_opp);

					if ($field_name != 'date_created' && $field_name != 'date_modified') 
					{
						$field_name 			= substr($field_name, 5);
						$field_name = preg_replace("/^custom_/", "", $field_name);
					}

					$custom_fields_date = $this->model_custom_fields_class::select($this->table_module.'_custom_values_maps', $this->table_module.'_custom_fields_name')	
												->where('company_id','=', $company_id)
												->where($this->table_module.'_custom_fields_name', '=', $field_name)
												->first();

					if (countCustom($custom_fields_date)> 0) 
					{
						$field_name = $custom_fields_date[$this->table_module.'_custom_values_maps'];
						if ($value == "select_date") 
						{
							$container[0] = 'mcv.'.$field_name;
							$container[1] = $value; // select_date
							if(!empty($contents['date_custom_'.$custom_fields_date[$this->table_module.'_custom_fields_name'].'_s'])){
								$select_date_start	= $contents['date_custom_'.$custom_fields_date[$this->table_module.'_custom_fields_name'].'_s'];
							}else if(!empty($contents['date_custom_'.$custom_fields_date[$this->table_module.'_custom_fields_name']][0])){
								$select_date_start	= $contents['date_custom_'.$custom_fields_date[$this->table_module.'_custom_fields_name']][0];
							}else{
								$select_date_start	= date('d-m-Y');
							}

							if(!empty($contents['date_custom_'.$custom_fields_date[$this->table_module.'_custom_fields_name'].'_e'])){
								$select_date_end	= $contents['date_custom_'.$custom_fields_date[$this->table_module.'_custom_fields_name'].'_e'];
							}else if(!empty($contents['date_custom_'.$custom_fields_date[$this->table_module.'_custom_fields_name']][1])){
								$select_date_end	= $contents['date_custom_'.$custom_fields_date[$this->table_module.'_custom_fields_name']][1];
							}else{
								$select_date_end	= date('d-m-Y');
							}
							$container[2] = $select_date_start.'and'.$select_date_end;
							$result['date_custom'][] 	= $container;
						} 
						elseif ($value == "before") 
						{
							$container[0] = 'mcv.'.$field_name;
							$container[1] = $value; // before
							$container[2] = !empty($contents['date_custom_'.$custom_fields_date[$this->table_module.'_custom_fields_name'].'_ba']) ? $contents['date_custom_'.$custom_fields_date[$this->table_module.'_custom_fields_name'].'_ba'] : date('d-m-Y');
							$result['date_custom'][] 	= $container;
						}
						elseif ($value == "after") 
						{
							$container[0] = 'mcv.'.$field_name;
							$container[1] = $value; //after
							$container[2] = !empty($contents['date_custom_'.$custom_fields_date[$this->table_module.'_custom_fields_name'].'_ba']) ? $contents['date_custom_'.$custom_fields_date[$this->table_module.'_custom_fields_name'].'_ba'] : date('d-m-Y');
							$result['date_custom'][] 	= $container;
						}
						elseif ($value != '' && !isset($contents[$key.'_opp'])) 
						{
							$container[0] 	= 'mcv.'.$field_name; // field_name
							$container[1] 	= $value; // operator
							$container[2] 	= "date"; // type 
							$result['date_custom'][] 	= $container;
						}
					}
					else
					{
						if ($value == "select_date") 
						{
							$container[0] = $this->table_module.'.'.$field_name;
							$container[1] = $value; // select_date
							$select_date_start	= !empty($contents['date_'.$field_name.'_s']) ? $contents['date_'.$field_name.'_s'] : ( isset($contents['date_'.$field_name][0]) ? $contents['date_'.$field_name][0] : date('d-m-Y') );
							$select_date_end		= !empty($contents['date_'.$field_name.'_e']) ? $contents['date_'.$field_name.'_e'] : ( isset($contents['date_'.$field_name][1]) ? $contents['date_'.$field_name][1] : date('d-m-Y') );
							$container[2] = $select_date_start.'and'.$select_date_end;
							$result['date'][] 	= $container;
						} 
						elseif ($value == "before") 
						{
							$container[0] = $this->table_module.'.'.$field_name;
							$container[1] = $value; // before
							$container[2] = !empty($contents['date_'.$field_name.'_ba']) ? $contents['date_'.$field_name.'_ba'] : date('d-m-Y');
							$result['date'][] 	= $container;
						}
						elseif ($value == "after") 
						{
							$container[0] = $this->table_module.'.'.$field_name;
							$container[1] = $value; //after
							$container[2] = !empty($contents['date_'.$field_name.'_ba']) ? $contents['date_'.$field_name.'_ba'] : date('d-m-Y');
							$result['date'][] 	= $container;
						}
						elseif ($value != '' && !isset($contents[$key.'_opp'])) 
						{
							$container[0] 	= $this->table_module.'.'.$field_name; // field_name
							$container[1] 	= $value; // operator
							$container[2] 	= "date"; // type 
							$result['date'][] 	= $container;
						}
					}
				}
				elseif ($prefix_opp === "_opp" && $prefix_custom == "custom_")
				{
					//Process generate format FIELD TYPE CUSTOM, ex.: custom_leads_religion
					$field_name_opp 	= $key;
					$field_name 			= str_replace('_opp', '', $field_name_opp);

					$c_operator 	= $value;
					$c_keyword 		= isset($contents[$field_name]) ? $contents[$field_name] : '';
					
					$operator 		= "contains";
					$keyword 			= "";

					if (($c_operator == "contains" || $c_operator == "containts") && $c_keyword !== null && $c_keyword !== "")
					{
						$operator 	= 'LIKE';
						$keyword 		= '%'.$c_keyword.'%';
					}
					elseif (($c_operator == "doesnt_contains" || $c_operator == "doesnt_containts") && $c_keyword !== null && $c_keyword !== "")
					{
						$operator 	= 'NOT LIKE';
						$keyword 		= '%'.$c_keyword.'%';
					}
					elseif ($c_operator == "is") {
						$operator 	= '=';
						$keyword 		= $c_keyword;
					}
					elseif ($c_operator == "isnt") {
						$operator 	= '!=';
						$keyword 		= $c_keyword;
					}
					elseif ($c_operator == "starts_with" && $c_keyword !== null && $c_keyword !== "") {
						$operator 	= 'LIKE';
						$keyword 		= $c_keyword.'%';
					}
					elseif ($c_operator == "ends_with" && $c_keyword !== null && $c_keyword !== "") {
						$operator 	= 'LIKE';
						$keyword 	 	= '%'.$c_keyword;
					}
					elseif ($c_operator == "is_empty") {
						$operator 	= '=';
						$keyword 	 	= '';
					}
					elseif ($c_operator == "is_not_empty") {
						$operator 	= '!=';
						$keyword 	 	= '';
					}

					#For change : exp. custom_leads_fields_name_opp to result leads_fields_name
					$cut_string_opp 			= substr($key, -4); 
					$cut_string_custom 		 	= substr($key, 7); 
					$field_custom_name	 		= str_replace($cut_string_opp, "", $cut_string_custom);
					
					#END
					#By Nasa
					$fieldtype = '';
					if($prefix_custom == 'date_cu'){
						$field_custom_name = preg_replace('/^date_custom_/', '',$key);
						$field_custom_name = preg_replace('/_opp$/', '',$field_custom_name);
					}
					$qry_get_custom_id = $this->model_custom_fields_class::select($this->table_module . '_custom_fields_input_type', $this->table_module . '_custom_fields_serial_id', $this->table_module . '_custom_values_maps')
												->where($this->table_module.'_custom_fields_name', '=', $field_custom_name)
												->where('company_id', '=', $company_id)
												->first();

					if(!isEmpty($qry_get_custom_id))
					{
						$qry_get_custom_id = $qry_get_custom_id->toArray();

						$fieldtype = $qry_get_custom_id[$this->table_module.'_custom_fields_input_type'];
						$exists = array_search($qry_get_custom_id[$this->table_module.'_custom_fields_serial_id'], array_column($fields_custom, $this->table_module.'_fields_serial_id'));
						if ( $exists === FALSE )
						{
							$temp[$i]['alias'] = $b;
							$temp[$i]['id'] = $qry_get_custom_id[$this->table_module.'_custom_fields_serial_id'];
							$i++;
						}
					}


					if(countCustom($qry_get_custom_id) > 0)
					{
						$id = $qry_get_custom_id[$this->table_module.'_custom_fields_serial_id'];
						if ( isset($temp_alias[$id]) )
						{
							$a = $temp_alias[$id];
						}
						else
						{
							$a = $b;
						}
						$container[0] 	= 'mcv.'.$qry_get_custom_id[$this->table_module.'_custom_values_maps']; // field_name
					}

					$container[1] 	= $operator; // operator
					$container[2] 	= $keyword; // keyword
					$container[3] 	= $fieldtype;
					$container[4] 	= $c_operator; /* untuk mengetahui param operator pencarian */
					
					if ($c_operator == "is_not_empty" || $c_operator == "is_empty" || ($c_operator != "is_not_empty" && $c_operator != "is_empty" && $c_keyword !== null && $c_keyword !== "")) {
						$result['custom'][] = $container;
					}
					
					$b++;
				}
				elseif($prefix_opp === "_opp")
				{
					// Process generate format FIELD TYPE CORE, ex.: leads_first_name
					$field_name_opp 	= $key;
					$field_name 		= str_replace('_opp', '', $field_name_opp);

					$c_operator 	= $value;
					$c_keyword 		= isset($contents[$field_name]) ? $contents[$field_name] : '';
					$operator 		= "contains";
					$keyword 		= "";
						if (($c_operator == "contains" || $c_operator == "containts") && $c_keyword !== null && $c_keyword !== "")
						{
							$operator 	= 'LIKE';
							$keyword 		= '%'.$c_keyword.'%';
						}
						elseif (($c_operator == "doesnt_contains" || $c_operator == "doesnt_containts") && $c_keyword !== null && $c_keyword !== "")
						{
							$operator 	= 'NOT LIKE';
							$keyword 		= '%'.$c_keyword.'%';
						}
						elseif ($c_operator == "is") {
							$operator 	= '=';
							$keyword 		= $c_keyword;
						}
						elseif ($c_operator == "isnt") {
							$operator 	= '!=';
							$keyword 		= $c_keyword;
						}
						elseif ($c_operator == "starts_with" && $c_keyword !== null && $c_keyword !== "") {
							if (!is_array($c_keyword)) {
								$operator 	= 'LIKE';
								$keyword 		= $c_keyword.'%';
							}
						}
						elseif ($c_operator == "ends_with" && $c_keyword !== null && $c_keyword !== "") {
							$operator 	= 'LIKE';
							$keyword 	 	= '%'.$c_keyword;
						}
						elseif ($c_operator == "is_empty") {
							$operator 	= '==';
							$keyword 	 	= '';
						}
						elseif ($c_operator == "is_not_empty") {
							$operator 	= '!=';
							$keyword 	 	= '';
						}

						$field_name 	= str_replace("_date_", "_", $field_name); // remove prefix date_ for generate data_search
						$container[0] 	= $this->table_module.'.'.$field_name; // field_name
						$container[1] 	= $operator; // operator
						$container[2] 	= $keyword; // keyword
						// $result['core'][] 	= $container;
						if ($c_operator == "isnt" || $c_operator == "is" || $c_operator == "is_not_empty" || $c_operator == "is_empty" || ($c_operator != "is_not_empty" && $c_operator != "is_empty" && $c_keyword !== null && $c_keyword !== "")) {
							$result['core'][] = $container;
						}
				}// end if
			}
			# end looping

		}
		/* PREVIEW RESULT 
			Array
			(
		    [core] => Array
		        (
		        )

		    [date] => Array
		        (
		        )

		    [custom] => Array
		        (
		        )
			)
		END */
		$result_data = array(
			'result' => $result,
			'temp' => $temp,
		);

		return $result_data;
	}

	# Created By Prihan Firmanullah
  	# 2019-12-20
  	# For Save Customize Form
	public function checkDateType($value='')
	{
		$dateList 	= array(
								'today', 'last_5_days', 'last_7_days', 'next_7_days', 'last_30_days', 'next_30_days', 
								'this_month', 'last_month', 'next_month',
								'this_year', 'last_year', 'next_year', 
								'select_date', 'before', 'after', 
								'yesterday');

		$check = in_array($value, $dateList);

		return $check;
	}

	# Created By Prihan Firmanullah
  	# 2019-12-20
  	# For Save Customize Form
	public function listFilter($company_id, $users_id)
	{
		$result['users'] 			= array();
		$result['my_filter'] 	= array();
		$fields_name 		= array(
										$this->table_module."_view_serial_id", 
										$this->table_module."_view_uuid", 
										$this->table_module."_view_name", 
										$this->table_module."_view_visibility",
										'users_id', 
										'company_id',
										);

		$data = $this->model_view_class::select($fields_name)
									->where('company_id', '=', $company_id)
									->leftjoin('users','users.id','=',$this->table_module.'_view.users_id')
		 							->where('users.users_status','=',Config('setting.ACTIVE'))
		 							->where('users.users_deleted','=',Config('setting.NOT_DELETED'))
									// ->where('users_id', '=', $users_id)
									->where('deleted', '=', Config('setting.NOT_DELETED'))
									->whereIn($this->table_module.'_view_visibility', [Config('setting.view_visibility_private'), Config('setting.view_visibility_shared')] )
									->where(function($q) use ($company_id, $users_id) {
						          $q->where('company_id', '=', $company_id)
												->where('users_id', '=', $users_id)
												->orWhere($this->table_module.'_view_visibility', '=', Config('setting.view_visibility_shared'));
						      })
									->orderby($this->table_module.'_view_serial_id', 'ASC')
									->get();
																		
		if (countCustom($data) > 0 ) 
		{
			$data 	= $data->toArray();	
			foreach ($data as $key => $value) 
			{
				$module_view_serial_id 	= $value[$this->table_module.'_view_serial_id'];

				$checked 	= "0";
				$view_checked 	= $this->model_view_checked_class::where($this->table_module.'_view_serial_id', '=', $module_view_serial_id)
																->where('company_id', '=', $company_id)
																->where('users_id', '=', $users_id)
																->get();
				if (countCustom($view_checked) > 0) {
					$checked  = "1";
				}

				if ($value[$this->table_module.'_view_name'] == 'Everyone' || $value[$this->table_module.'_view_name'] == 'You') {
					$result['users'][] 	= array(
															$this->table_module.'_view_serial_id' 	=> $value[$this->table_module.'_view_serial_id'], 
															$this->table_module.'_view_uuid' 				=> $value[$this->table_module.'_view_uuid'], 
															$this->table_module.'_view_name' 				=> $value[$this->table_module.'_view_name'], 
															$this->table_module.'_view_checked' 		=> $checked,
															$this->table_module.'_view_visibility' 	=> $value[$this->table_module.'_view_visibility'],
															);
				}else
				{
					$visibility_button	 	= "false";
					if ($value['users_id'] == $users_id && $value['company_id'] == $company_id) 
					{
						$visibility_button 	= "true";
					}
					$result['my_filter'][] 	= array(
															$this->table_module.'_view_serial_id' 	=> $value[$this->table_module.'_view_serial_id'], 
															$this->table_module.'_view_uuid' 				=> $value[$this->table_module.'_view_uuid'], 
															$this->table_module.'_view_name' 				=> $value[$this->table_module.'_view_name'], 
															$this->table_module.'_view_checked' 		=> $checked,
															$this->table_module.'_view_visibility' 	=> $value[$this->table_module.'_view_visibility'],
															'users_id' 															=> $value['users_id'],
															'visibility_button'											=> $visibility_button,
															);
				}
			}
		}

		return $result;
	}

	# Created By Prihan Firmanullah
  # 2019-12-20
  # For Save Customize Form
	public function FilterSave($request, $company_id, $users_id)
	{
		$uuid4 = $this->uuid::uuid4();


		// 1. save module_view
		$view[$this->table_module.'_view_uuid']	= $uuid4->toString();
		$view[$this->table_module.'_view_name']	= isset($request[$this->table_module.'_view_name']) ? $request[$this->table_module.'_view_name'] : '';
		$view[$this->table_module.'_view_visibility'] = isset($request[$this->table_module.'_view_visibility'])?$request[$this->table_module.'_view_visibility']:0;
		$view['users_id']			= $users_id;
		$view['company_id']			= $company_id;
		$view['date_created']		= date('Y-m-d H:i:s');
		$view['created_by']			= $users_id;
		$view['date_modified']	= date('Y-m-d H:i:s');
		$parent_type = '';

		$save_filter 	= $this->model_view_class::create($view);
		$last_id		= $save_filter[$this->table_module.'_view_serial_id'];
		
		// 2. save module_view_criteria
		$filter_criteria = json_decode(json_encode($request['filters_fields']),true);
		foreach ($request['filters_fields'] as $key => $value) {
			if($value['fields_name'] == $this->table_module . '_parent_type')
			{
				if(is_array($value['fields_value']))
				{
					foreach ($value['fields_value'] as $key2 => $value2) {
						$parent_type = $value2;
					}
				}else{
					$parent_type = $value['fields_value'];
				}
			}
		}

		foreach ($request['filters_fields'] as $key => $value) 
		{
			if( $value['operator'] != 'null')
			{
				$view_criteria[$this->table_module.'_view_serial_id']			= $last_id;
				$view_criteria[$this->table_module.'_fields_serial_id']			= $value['fields_serial_id'];
				$view_criteria[$this->table_module.'_view_criteria_operator'] 	= $value['operator'];
				if(isset($value['fields_name']) && ($value['operator'] != "is_empty" && $value['operator'] != "is_not_empty"))
				{	
					if ($value['fields_name'] == $this->table_module . '_parent_id' && ($value['operator'] == 'is' || $value['operator'] == 'isnt')) {
						$newcontent = [];
						if (isset($value['content']) OR isset($value['fields_value'])) {
							if(isset($value['content']))
							{
								$content = json_decode($value['content']);
								$value['fields_value'] = [];
							}
							else
							{
								$content = $value['fields_value'];
							}
			
							foreach($content as $keycontent => $valuecontent){
								$id_parent = '';

								if(isset($valuecontent->dropdown_options_value))
								{
									$id_parent = $valuecontent->dropdown_options_value;
								}
								else
								{
									$id_parent = $valuecontent;
								}
								
								$newcontent[] = [
									'type' => $parent_type,
									'id' => $id_parent
								];
							}
							$value['fields_value'] = $newcontent;
						}
					}
					$view_criteria[$this->table_module.'_view_criteria_value']	= isset($value['fields_value'])?json_encode($value['fields_value']):'';
				}
				else // is_empty and is_not_empty
				{
					$view_criteria[$this->table_module.'_view_criteria_value']	= "";
				}

				$view_criteria[$this->table_module.'_view_criteria_type']		= $value['fields_type'];
				$save_filter_criteria 	= $this->model_view_criteria_class::create($view_criteria);
			}
		}

		$result = array('filter' => $save_filter);

		return json_decode(json_encode($result), true);
	}

	public function FilterUpdate($request, $company_id, $users_id)
	{
		// 1. update module_view
		$view[$this->table_module.'_view_name'] 	  = isset($request[$this->table_module.'_view_name']) ? $request[$this->table_module.'_view_name'] : 'View Name';
		$view[$this->table_module.'_view_visibility'] = $request[$this->table_module.'_view_visibility'];
		$update_view 	= $this->model_view_class::where($this->table_module.'_view_serial_id', '=', $request[$this->table_module.'_view_serial_id'])->update($view);

		// 2. Delete module_view_criteria where view_serial_id
		$data 	= $this->model_view_criteria_class::where($this->table_module.'_view_serial_id', '=', $request[$this->table_module.'_view_serial_id'])->delete();

		// 3. save module_view_criteria
		$filter_criteria = json_decode(json_encode($request['filters_fields']),true);
		$last_id = $request[$this->table_module.'_view_serial_id'];

		$parent_type = '';
		foreach ($request['filters_fields'] as $key => $value) {
			if($value['fields_name'] == $this->table_module . '_parent_type')
			{
				if(is_array($value['fields_value']))
				{
					foreach ($value['fields_value'] as $key2 => $value2) {
						$parent_type = $value2;
					}
				}else{
					$parent_type = $value['fields_value'];
				}
			}
		}

		foreach ($request['filters_fields'] as $key => $value) 
		{
			if( $value['operator'] != 'null')
			{
				$view_criteria[$this->table_module.'_view_serial_id']			= $last_id;
				$view_criteria[$this->table_module.'_fields_serial_id']			= $value['fields_serial_id'];
				$view_criteria[$this->table_module.'_view_criteria_operator'] 	= $value['operator'];
				if(isset($value['fields_name']) && ($value['operator'] != "is_empty" && $value['operator'] != "is_not_empty"))
				{	
					if ($value['fields_name'] == $this->table_module . '_parent_id' && ($value['operator'] == 'is' || $value['operator'] == 'isnt')) {
						$newcontent = [];
						if (isset($value['content']) OR isset($value['fields_value'])) {
							if(isset($value['content']))
							{
								$content = json_decode($value['content']);
								$value['fields_value'] = [];
							}
							else
							{
								$content = $value['fields_value'];
							}
			
	
							foreach($content as $keycontent => $valuecontent){
								$id_parent = '';

								if(isset($valuecontent->dropdown_options_value))
								{
									$id_parent = $valuecontent->dropdown_options_value;
								}
								else
								{
									$id_parent = $valuecontent;
								}
								
								$newcontent[] = [
									'type' => $parent_type,
									'id' => $id_parent
								];
							}
							$value['fields_value'] = $newcontent;
						}
					}
					$view_criteria[$this->table_module.'_view_criteria_value']	= isset($value['fields_value'])?json_encode($value['fields_value']):'';
				}
				else // is_empty and is_not_empty
				{
					$view_criteria[$this->table_module.'_view_criteria_value']	= "";
				}

				$view_criteria[$this->table_module.'_view_criteria_type']		= $value['fields_type'];
				$save_filter_criteria 	= $this->model_view_criteria_class::create($view_criteria);
			}
		}

		$fields_name 		= array(
										$this->table_module."_view_serial_id", 
										$this->table_module."_view_uuid", 
										$this->table_module."_view_name", 
										$this->table_module."_view_visibility",
										'users_id', 
										'company_id',
										);

		$data = $this->model_view_class::select($fields_name)
									->where('company_id', '=', $company_id)
									->leftjoin('users','users.id','=',$this->table_module.'_view.users_id')
		 							->where('users.users_status','=',Config('setting.ACTIVE'))
		 							->where('users.users_deleted','=',Config('setting.NOT_DELETED'))
									->where('deleted', '=', Config('setting.NOT_DELETED'))
									->whereIn($this->table_module.'_view_visibility', [Config('setting.view_visibility_private'), Config('setting.view_visibility_shared')] )
									->where(function($q) use ($company_id, $users_id) {
						          $q->where('company_id', '=', $company_id)
												->where('users_id', '=', $users_id)
												->orWhere($this->table_module.'_view_visibility', '=', Config('setting.view_visibility_shared'));
						      })
						      ->where($this->table_module.'_view_serial_id','=',$request[$this->table_module.'_view_serial_id'])
									->first();

		return json_decode(json_encode($data), true);
	}

	public function filterDelete($uuid)
	{
		$delete['deleted'] 	= Config('setting.DELETED'); // deleted row module_view
		$data 	= $this->model_view_class::where($this->table_module.'_view_uuid', '=', $uuid)->update($delete);

		return TRUE;
	}

	# Created By Prihan Firmanullah
  	# 2019-12-20
  	# For Save Customize Form
	public function viewChecked($company_id=0, $users_id=0)
	{
		$result 		= array();
		$qry_view 	= $this->model_view_class::where('users_id', '=', $users_id)
												->where('company_id', '=', $company_id)
												->count();
		if ($qry_view == 0) // if No available filter 
		{
			// create filter 'Everyone' and 'You' if no detect/available filter
			$uuid4_default1  		= $this->uuid::uuid4();
			$uuid4_default2 		= $this->uuid::uuid4();

			// 1. Everyone
			$input[$this->table_module.'_view_uuid']		= $uuid4_default1->toString();
			$input[$this->table_module.'_view_name']		= 'Everyone';
			$input['users_id']				= $users_id;
			$input['company_id'] 			= $company_id;
			$input['date_created']		= date('Y-m-d H:i:s');
			$input['created_by']			= $users_id;
			$input['date_modified']		= date('Y-m-d H:i:s');
			$save 								= $this->model_view_class::create($input);
			$serialIdByEveryone 	= $save->{$this->table_module.'_view_serial_id'};

			// 2. You
			$input_2[$this->table_module.'_view_uuid']		= $uuid4_default2->toString();
			$input_2[$this->table_module.'_view_name']		= 'You';
			$input_2['users_id']				= $users_id;
			$input_2['company_id'] 			= $company_id;
			$input_2['date_created']		= date('Y-m-d H:i:s');
			$input_2['created_by']			= $users_id;
			$input_2['date_modified']		= date('Y-m-d H:i:s');
			$save_2 				= $this->model_view_class::create($input_2); // Save Filter Name 'You'

			// 3. Automatic Checked, by insert data in table view_checked
			$input_3['users_id'] 				= $users_id;
			$input_3['company_id']			= $company_id;
			$input_3[$this->table_module.'_view_serial_id'] 	= $serialIdByEveryone;
			$save_3 				= $this->model_view_checked_class::create($input_3);

			$query 	= $this->model_view_class::where($this->table_module.'_view_serial_id', '=', $serialIdByEveryone)
										->first();
			if (countCustom($query) > 0) {
				$result 	= $query->toArray();
			}
		}
		else // if available filter
		{
			$query 	= $this->model_view_class::select($this->table_module.'_view.*')
												->leftjoin($this->table_module.'_view_checked as b', 'b.'.$this->table_module.'_view_serial_id', '=', $this->table_module.'_view.'.$this->table_module.'_view_serial_id')
												->where('b.users_id', '=', $users_id)
												->where('b.company_id', '=', $company_id)
												->first();
			if (countCustom($query) > 0) { //  if checked is available
				$result 	= $query->toArray();
				
				#if view_name is NULL, set view_name
				if($result[$this->table_module.'_view_name'] == NULL)
				{
					$update[$this->table_module.'_view_name'] = 'Calls Filter';
					$this->model_view_class::where($this->table_module.'_view_serial_id', '=', $result[$this->table_module.'_view_serial_id'])->update($update);
				}
			}else // if no checked, 
			{
				$result = $this->model_view_class::where('users_id', '=', $users_id)
									->where('company_id', '=', $company_id)
									->where($this->table_module.'_view_name', '=', 'Everyone')
									->first();
			}
		}

		return $result;
	}

	# Created By Prihan Firmanullah
  	# 2019-12-20
  	# For Save Customize Form
	public function GetAllCoreFields($company_id=0)
	{
		# Define Variable 
		$data = array();

		$data = $this->model_fields_class::orderBy($this->table_module.'_fields_sorting', 'ASC')->get();
			
		if(countCustom($data) > 0)
		{
			if(!is_array($data))
			{
				$data = $data->toArray();				
				$data = $this->GetCoreFieldsChange($data, $company_id);
			}
		}

		return $data;
	}

	# Created By Prihan Firmanullah
 	# 2019-12-20
  	# For Save Customize Form
	public function coreFieldsValidation($data_core=array())
	{
		$result = array();

		foreach ($data_core as $key => $value) 
		{
			if ($value[$this->table_module.'_fields_name'] != $this->table_module.'_last_date'
					AND $value[$this->table_module.'_fields_name'] != $this->table_module.'_last_id' 
					AND $value[$this->table_module.'_fields_name'] != $this->table_module.'_last_module'
					AND $value[$this->table_module.'_fields_name'] != 'modified_by'
					AND $value[$this->table_module.'_fields_name'] != 'created_by'
					AND $value[$this->table_module.'_fields_name'] != 'date_modified'
					AND $value[$this->table_module.'_fields_name'] != 'date_created'
				) 
			{
				$result[] = $data_core[$key];
			}
		}

		return $result;
	}

	# Created By Prihan Firmanullah
  	# 2019-12-20
  	# For Save Customize Form
	public function listDataCoreFields($company_id=0)
	{
		//load data with pagination and criteria
		$data = $this->model_custom_fields_class::where('company_id', '=', $company_id)->get(); //get listing data

		if (countCustom($data) > 1) 
		{
			$data = $data->toArray(); // put listing data in array

			foreach ($data as $key => $value)
			{
				unset($value['company_id']);
				$data[$key] = $value;
			}
		}

		return $data;
	}

	# Created By Prihan Firmanullah
  	# 2019-12-20
  	# For Save Customize Form
	public function listDataCustomFields($company_id=0)
	{
		//load data with pagination and criteria
		$data = $this->model_custom_fields_class::where('company_id', '=', $company_id)->get(); //get listing data

		if (countCustom($data) > 1) 
		{
			$data = $data->toArray(); // put listing data in array

			foreach ($data as $key => $value)
			{
				unset($value['company_id']);
				$data[$key] = $value;
			}
		}

		return $data;
	}

	# Created By Prihan Firmanullah
  	# 2019-12-20
  	# For Save Customize Form
	public function GetDataCoreFieldsBy($serial_id=0, $company_id)
	{
		// Get data find by data_uuid
		$data = $this->model_fields_class::where($this->table_module.'_fields_serial_id', '=', $serial_id)->first();

		if (countCustom($data) > 0) 
		{
			$data = $data->toArray(); // put listing data in array
			//for check core field type can edit
			$data[$this->table_module.'_fields_input_type_before'] = $data[$this->table_module.'_fields_input_type'];
			$data = $this->GetDataCoreFieldsChangeBy($data, $company_id);
		}		

		return $data;
	}

	# Created By Prihan Firmanullah
  	# 2019-12-20
  	# For Save Customize Form
	public function GetDataCoreFieldsChangeBy($data=array(), $company_id=0)
	{
		$dataChange = $this->model_fields_change_class::where($this->table_module.'_fields_serial_id', '=', $data[$this->table_module.'_fields_serial_id'])
																									->where('company_id', '=', $company_id)
																									->first();

		if( countCustom($dataChange) > 0 )
		{
			$data[$this->table_module.'_fields_change_serial_id'] = $dataChange[$this->table_module.'_fields_change_serial_id'];
			$data[$this->table_module.'_fields_label'] 						= $dataChange[$this->table_module.'_fields_change_label'];
			$data[$this->table_module.'_fields_validation'] 			= $dataChange[$this->table_module.'_fields_change_validation'];
			$data[$this->table_module.'_fields_status'] 					= $dataChange[$this->table_module.'_fields_change_status'];
			$data[$this->table_module.'_fields_options'] 					= $dataChange[$this->table_module.'_fields_change_options'] != '' && $dataChange[$this->table_module.'_fields_change_options'] != null 
																															? $dataChange[$this->table_module.'_fields_change_options'] : $data[$this->table_module.'_fields_options'];
			$data[$this->table_module.'_fields_quick'] 						= $dataChange[$this->table_module.'_fields_change_quick'];
			$data[$this->table_module.'_fields_input_type'] 			= $dataChange[$this->table_module.'_fields_change_input_type'] != '' && $dataChange[$this->table_module.'_fields_change_input_type'] != null 
																															? $dataChange[$this->table_module.'_fields_change_input_type'] : $data[$this->table_module.'_fields_input_type'];
			$data[$this->table_module.'_fields_readonly'] 				= $dataChange[$this->table_module.'_fields_change_readonly'];
			$data[$this->table_module.'_fields_default_value'] 		= $dataChange[$this->table_module.'_fields_change_default_value'];

		}else{
			$data[$this->table_module.'_fields_change_serial_id'] = '';

		}

		return $data;
	}

	# Created By Prihan Firmanullah
  	# 2019-12-20
  	# For Save Customize Form
	public function GetDataCutomFieldsBy($serial_id=0, $company_id=0)
	{
		// Define variable
		$result = array();

		$query = $this->model_custom_fields_class::where( $this->table_module.'_custom_fields_serial_id', '=', $serial_id)
																						->where('company_id', '=', $company_id)
																						->first();
		if ( countCustom($query) > 0 )
		{
			$result = $query->toArray();
		}

		return $result;
	}

	# Created By Prihan Firmanullah
  	# 12-05-2019
  	# For Save Save Custom Fields
	public function SaveDataCustom($input=array(), $company_id=0, $users_id=0)
	{
		$result = '';
		if ( isset($input[$this->table_module.'_custom_fields_validation']) AND countCustom($input[$this->table_module.'_custom_fields_validation']) > 0 )
		{
			foreach ($input[$this->table_module.'_custom_fields_validation'] as $key => $value) 
			{
				if ( $key == 0 )
				{
					$result = $value;
				}
				else
				{
					$result = $result.'|'.$value;
				}
			}

			$input[$this->table_module.'_custom_fields_validation'] 		= $result;
		}

		$standard_name1 = preg_replace('/[^\p{L}\p{N}\s]/u', '', $input[$this->table_module.'_custom_fields_label']);
		$standard_name1 = str_replace(' ', '_', $standard_name1);
		$standard_name1 = $this->table_module."_".strtolower($standard_name1);
		// check if same name ($standard_name)
		$standard_name = $this->CheckName($standard_name1, $company_id);


		$input[$this->table_module.'_custom_fields_name'] 					= $standard_name;
		$input['company_id']																				= $company_id;
		
		// For single option
		if ( isset($input['options']) ) 
		{
			$input[$this->table_module.'_custom_fields_options'] = $input["options"];
		}

		// for multiple option
		if ( isset($input['radio_multiple']) ) 
		{
			if ( $input['radio_multiple'] == '1' ) 
			{
				$input[$this->table_module.'_custom_fields_options'] = $input['options_one'];
			}
			elseif ( $input['radio_multiple'] == '0' ) 
			{
				$data_dropdown['dropdown_name'] = date('YmdHis_').strtolower($standard_name1);
				$data_dropdown['company_id'] 		= $company_id;
				$data_dropdown['deleted'] 			= Config('setting.NOT_DELETED');

				$save_dropdown 	= $this->model_dropdown_class::create($data_dropdown);
				$last_id 				= $save_dropdown->dropdown_serial_id;

				foreach ($input['options_zero'] as $key => $value) 
				{
					if ( !isEmpty($value) ) 
					{
						$dropdown_options['dropdown_serial_id'] 		= $last_id;
						$dropdown_options['dropdown_options_value'] = $value;
						$dropdown_options['dropdown_options_label'] = $value;

						$save_dropdown_options = $this->model_dropdown_options_class::create($dropdown_options);
					}
				}

				$input[$this->table_module.'_custom_fields_options'] = $data_dropdown['dropdown_name'];
			}
		}

		$string_maps = $this->library_module_values_maps_free_use($company_id);

		if ( $string_maps == '' )
		{
			return false;
		}
		$input[$this->table_module.'_custom_values_maps']	= $string_maps;
		
		unset($input["options"]); // unset from single option
		unset($input["radio_multiple"]); // unset from multiple option
		unset($input["options_one"]); // unset from choose option
		unset($input["options_zero"]); // unset from create new option

		// Insert into database custom fileds data
    	$save_custom_fields = $this->model_custom_fields_class::create($input);

		if(!isEmpty($save_custom_fields))
		{
			$save_custom_fields = $save_custom_fields->toArray();
			$savelog = $this->saveLogCreateCustomfields($save_custom_fields,$company_id,$users_id);
		}

		if (isset($input['fields_options'])){
			$serial_id = $save_custom_fields[$this->table_module."_custom_fields_serial_id"];
			$type_fields = $input['fields_type'];
			$this->saveFieldsCondition($input,$serial_id,$type_fields,$company_id,$users_id);
	}
		
		return $save_custom_fields;
	}

	# Created By Prihan Firmanullah
  	# 12-05-2019
  	# For Save Data Custom Group
	public function SaveDataCustomGroup($request, $company_id, $users_id)
	{
		$result = '';
		if ( !empty($request[$this->table_module."_custom_fields_validation"]) )
		{
			foreach ($request[$this->table_module."_custom_fields_validation"] as $key => $value) 
			{
				if ( countCustom($request[$this->table_module."_custom_fields_validation"]) > 1 )
				{
					$validation = "";
					$validation .= $value;

					$result = $result."$validation|";
				}
				else
				{
					$result = $value;
				}
			}

			// PROCCESS DATA (REQUEST) FOR INSERT INTO TABLE_MODULE CUSTOM FIELDS
			$request[$this->table_module."_custom_fields_validation"] 		= $result;
		}

		$standard_name1 = preg_replace('/[^\p{L}\p{N}\s]/u', '', $request[$this->table_module."_custom_fields_label"]);
		$standard_name1 = str_replace(' ', '_', $standard_name1);
		$standard_name1 = $this->table_module."_".strtolower($standard_name1);
		$standard_name = $this->CheckName($standard_name1, $company_id);

		# 1. insert into tbl dropdown
		$request[$this->table_module."_custom_fields_name"] 					= $standard_name;
		$request['company_id']																				= $company_id;

		$data_dropdown['dropdown_name'] 		= date('YmdHis_').strtolower($standard_name1);
		$data_dropdown['company_id'] 				= $company_id;
		$data_dropdown['dropdown_group'] 		= 1;
		$data_dropdown['deleted'] 					= Config('setting.NOT_DELETED');

		$save_dropdown 	= $this->model_dropdown_class::create($data_dropdown);
		$last_id 				= $save_dropdown->dropdown_serial_id;

		# 2. insert into tbl dropdown_options
		foreach ($request['dropdown_option_group'] as $key => $value) 
		{

			if ($request['radio'][$key] == 'new') 
			{
				// New dropdown
				foreach ($request['options_new'][$key] as $key2 => $value2) 
				{
					if ( !isEmpty($value2) ) 
					{
						$dropdown_options_['dropdown_serial_id'] 		 = $last_id;
						$dropdown_options_['dropdown_options_value'] = $value2;
						$dropdown_options_['dropdown_options_group'] = $request['dropdown_option_group'][$key];
						$dropdown_options_['dropdown_options_label'] = $value2;
						$save_dropdown_options = $this->model_dropdown_options_class::insert($dropdown_options_);
					}
				}
			} else {
				// Choose Dropdown 
				$dropdown_serial_id = $this->model_dropdown_class::select('dropdown_serial_id')
															->where('dropdown_name', '=', $request['options_choose'][$key])
															->where('company_id', '=', $company_id)
															->first();
				if (countCustom($dropdown_serial_id) > 0) 
				{
					$dropdown_serial_id = $dropdown_serial_id['dropdown_serial_id'];
					$dropdown_options = $this->model_dropdown_options_class::where('dropdown_serial_id', '=', $dropdown_serial_id)
					->get()->toArray();

					foreach ($dropdown_options as $key_in => $value_in) 
					{
						$dropdown_options[$key_in]['dropdown_options_group'] = $request['dropdown_option_group'][$key];
						$dropdown_options[$key_in]['dropdown_serial_id'] = $last_id;
						unset($dropdown_options[$key_in]['dropdown_options_serial_id']);
					}
					
					$save_dropdown_options = $this->model_dropdown_options_class::insert($dropdown_options);
				} 
			}
		}

		$request[$this->table_module.'_custom_fields_options'] = $data_dropdown['dropdown_name'];

		# 3. insert into tbl %-tbl-%_custom_fields
		$string_maps = $this->library_module_values_maps_free_use($company_id);
		if ( $string_maps == '' )
		{
			return false;
		}
		$request[$this->table_module.'_custom_values_maps']	= $string_maps;
		
		unset($request["dropdown_option_group"]); // unset from single option
		unset($request["radio"]); // unset from multiple option
		unset($request["options_choose"]); // unset from choose option
		unset($request["options_new"]); // unset from create new option

		// Insert into database custom fileds data
   	 $save_custom_fields = $this->model_custom_fields_class::create($request);

		if(!isEmpty($save_custom_fields))
		{
			$save_custom_fields = $save_custom_fields->toArray();
			$savelog = $this->saveLogCreateCustomfields($save_custom_fields,$company_id,$users_id);
		}

		// fields option
		if (isset($request['fields_options'])){
			$serial_id = $request[$this->table_module.'_custom_fields_serial_id'];
			$type_fields = $request['fields_type'];
			$this->saveFieldsCondition($request,$serial_id,$type_fields,$company_id,$users_id);
		}

		return $save_custom_fields;
	}

	public function saveLogCreateCustomfields($save_custom_fields = array(),$company_id = 0, $users_id = 0)
	{
		if(isset($save_custom_fields[$this->table_module.'_custom_fields_status']) && $save_custom_fields[$this->table_module.'_custom_fields_status'] == Config('setting.FIELDS_ACTIVE'))
		{
			$fields_status = 'Active';
		}
		else 
		{
			$fields_status = 'Not Active';
		}

		if(isset($save_custom_fields[$this->table_module.'_custom_fields_quick']) && $save_custom_fields[$this->table_module.'_custom_fields_quick'] == Config('setting.custom_fields_quick_active'))
		{
			$quick_create = 'Yes';
		}
		else 
		{
			$quick_create = 'No';
		}

		if(isset($save_custom_fields[$this->table_module.'_custom_fields_readonly']) && $save_custom_fields[$this->table_module.'_custom_fields_readonly'] == Config('setting.custom_field_readonly_active'))
		{
			$readonly = 'Yes';
		}
		else 
		{
			$readonly = 'No';
		}

		$syslog_action = "Create ".ucfirst($this->table_module)." Custom Fields: Label &#8594;'".$save_custom_fields[$this->table_module.'_custom_fields_label']."', Input Type &#8594;'".$save_custom_fields[$this->table_module.'_custom_fields_input_type'].
		"', Validation &#8594;'".(isset($save_custom_fields[$this->table_module.'_custom_fields_validation']) ? $save_custom_fields[$this->table_module.'_custom_fields_validation'] : 'Not Required')."', Status &#8594;'".$fields_status."', Quick Create &#8594;'".$quick_create."'"
		."', Readonly Create &#8594;'".$readonly."'"."', Default Value &#8594;'".(isset($save_custom_fields[$this->table_module.'_custom_fields_default_value']) ? $save_custom_fields[$this->table_module.'_custom_fields_default_value'] : 'Not Set')."'";
		$syslog = $this->sys_api_syslog( $syslog_action, 'createcustom', $this->table_module, $save_custom_fields[$this->table_module.'_custom_fields_serial_id'], $users_id, $company_id );

		return true;
	}

	# Created By Prihan Firmanullah
  	# 29-11-2019
  	# For Check Name Custom Fields
	public function CheckName($name_custom_fields, $company_id)
	{
		$result = $name_custom_fields;

		$check = $this->model_custom_fields_class::where($this->table_module.'_custom_fields_name', '=', $name_custom_fields)
																						->where('company_id', '=', $company_id)
																						->get();

		if ( countCustom($check) > 0 ) 
		{
			$result = $name_custom_fields.'_'.date('YmdHis');

		} else {

			$check = $this->model_fields_class::where($this->table_module.'_fields_name', '=', $name_custom_fields)
																						->get();

			if (countCustom($check) > 0) 
			{
				$result = $name_custom_fields.'_'.date('YmdHis');
			}
		}

		return $result;
	}

	# Created By Prihan Firmanullah
  	# 12-05-2019
  	# For Maps Name Used
	public function library_module_values_maps_free_use($company_id=0)
	{
		// START : For check custom values maps
		// First, check table custom_fields. Check custom values maps free to use
		$query_custom_fields = $this->model_custom_fields_class::select($this->table_module.'_custom_values_maps')
																										 ->where('company_id', '=', $company_id)
																										 ->orderBy($this->table_module.'_custom_fields_serial_id', 'ASC')
																										 ->get();
		$string_maps = '';
		// Second, process to use custom values maps
		if ( countCustom($query_custom_fields) > 0 ) // If any data custom fields, check back which maps are available
		{
			$query_custom_fields = $query_custom_fields->toArray(); // Convert to Array
			// Check maps available from bottom to top (1 to 100)
			for ($i=1; $i <= 100; $i++)
			{
				$check_exists_maps = array_search($this->table_module.'_custom_values_'.$i, array_column($query_custom_fields, $this->table_module.'_custom_values_maps'));
				if ( $check_exists_maps === FALSE )
				{
					$string_maps = $this->table_module.'_custom_values_'.$i;
					break;
				}
			}
		}
		else // If the first data custom values maps = 1
		{
			$string_maps	= $this->table_module.'_custom_values_1';
		}
		// END : For check custom values maps

		return $string_maps;
	}

	# Created By Gilang Persada
  	# 5 des 2019
  	# For Filtering Delete Roles Leads
	public function FilteringDeleteRolesLeads($data_uuid=array(),$company_id=0,$users_id=0,$module)
	{
		$db = 'Illuminate\Support\Facades\DB';
		$result = array();
		// $aa = array();

		$query = $db::table($module)->select($module.'_owner',$module.'_uuid')
																->whereIn($module.'_uuid',$data_uuid)
																->where('company_id','=',$company_id)
																->get();

		if (countCustom($query) > 0) 
		{
			$query = json_decode(json_encode($query),true);

			$roles = $this->get_roles($module, $company_id, $users_id);

			foreach ($query as $key => $value) 
			{
				if (countCustom($roles['contents_delete']) > 0) 
				{
					$check = in_array($value[$module.'_owner'], $roles['contents_delete']);

					if ( $check === true OR isEmpty($roles['contents_delete']) )
					{
						$result[] = $value[$module.'_uuid'];
					}
					else
					{
						unset($query[$key]);
					}
				}
				else
				{
					$result[] = $value[$module.'_uuid'];
				}
			}
		}

		return $result;
	}

	# Created By Gilang Persada
  	# 5 des 2019
  	# For get roles
	public function get_roles($module='', $company_id=0, $users_id=0)
	{
		$sys = new sys();

		// DEFINED FOR NO TEAM
		$contents_dummy 				= array();
		$contents_edit_dummy 		= array();
		$contents_delete_dummy 	= array();

		$modules_serial_id 			= Config('setting.MODULE_CALLS'); // modules_serial_id in table modules				
		$table_module						= 'leads';
		$model_class            = 'App\Http\Controllers\Calls\Models\Calls';

		$result['roles_type'][] = Config('setting.ROLES_NO_SETTING'); // default, if users don't have a teams
		$result['contents'][] 	= array();
		// Roles edit
		$result['roles_edit'][] 		= Config('setting.ROLES_EDIT_ALL'); // default, if users don't have a teams
		$result['contents_edit'][] 	= array();
		$arr 	= array();

		// Roles delete
		$result['roles_delete'][] 		= Config('setting.ROLES_DELETE_ALL'); // default, if users don't have a teams
		$result['contents_delete'][] 	= array();

		#1. Check teams.
		$fields_name 	= array('users_teams.teams_serial_id', 
													'users_teams.teams_name');
		$teams 	= $this->model_teams_class::select($fields_name)
																				->leftjoin('users_teams_map as b', 'users_teams.teams_serial_id', '=', 'b.teams_serial_id')
																				->where('users_teams.teams_status', '=', Config('setting.ACTIVE'))
																				->where('users_teams.company_id', '=', $company_id)
																				->where('users_teams.deleted', '=', Config('setting.NOT_DELETED'))
																				->where('b.users_id', '=', $users_id)
																				->get();

		if (countCustom($teams) > 0) // if users_id have a teams
		{
			$teams = $teams->toArray();

			foreach ($teams as $value)
			{
				$teams_serial_id[] 	= $value['teams_serial_id'];
			}

			$data_roles 	= $this->model_data_roles_class::select('data_roles_serial_id', 'data_roles_type', 'data_roles_edit','data_roles_delete')
																										->whereIn('teams_serial_id', $teams_serial_id)
																										->where('modules_serial_id', '=', $modules_serial_id)
																										->where('company_id', '=', $company_id)
																										->get();

			if (countCustom($teams_serial_id) == countCustom($data_roles)) 
			{
				unset($result['roles_type']); // unset default, because have teams
				unset($result['contents']); // unset contents, because have teams
				$data_roles 	= $data_roles->toArray();
				$roles_type 	= "";
				$contents 		= array();

				foreach ($data_roles as $key => $value) 
				{
					$roles_edit   = $sys->get_roles_edit($value['data_roles_edit'], $company_id, $users_id);
					$roles_delete = $sys->get_roles_delete($value['data_roles_delete'], $company_id, $users_id);

					$roles_type = Config('setting.ROLES_NO_SETTING'); // This is for default not set ROLES Setting (ROLES_NO_SETTING)
					$contents = array();
					
					$data_roles_serial_id = $value['data_roles_serial_id'];
					$data_roles_type 			= $value['data_roles_type'];

					if ($data_roles_type == Config('setting.ROLES_TEAM_VIEW')) // 2. TEAM VIEW
					{
						$sys_api_dashboards = new sys_dashboards;
						$criteria = array('company_id' => $company_id, 'users_id' => $users_id);
						$teams 		= $sys_api_dashboards->teams($criteria);

						foreach ($teams as $key => $value) 
						{
							$teams_serial_id[] 	= $value['teams_serial_id'];
						}
						$teams_map = $this->model_teams_map_class::select('users_teams_map.users_id')
																											->leftjoin('users_teams','users_teams_map.teams_serial_id','=','users_teams.teams_serial_id')
													                            ->where('users_teams.deleted','=',Config('setting.NOT_DELETED'))
																											->whereIn('users_teams_map.teams_serial_id', $teams_serial_id)
																											->where('users_teams_map.company_id', '=', $company_id)
																											->groupBy('users_teams_map.users_id')
																											->get();
						if (countCustom($teams_map) > 0) 
						{
							$teams_map 	= $teams_map->toArray();
							foreach ($teams_map as $key => $value) 
							{
								$content_id[] 	= $value['users_id'];
							}
							// #summary
							$roles_type 		= Config('setting.ROLES_TEAM_VIEW');
							$contents				= $content_id;
							#end
						}
					}elseif ($data_roles_type == Config('setting.ROLES_LEADER_VIEW')) //3. LEADER VIEW
					{
						$teamsList 	= $this->model_teams_map_class::select('users_teams_map.teams_serial_id')
																												->leftjoin('users_teams','users_teams_map.teams_serial_id','=','users_teams.teams_serial_id')
														                            ->where('users_teams.deleted','=',Config('setting.NOT_DELETED'))
																												->where('users_teams_map.users_id', '=', $users_id)
																												->where('users_teams_map.company_id', '=', $company_id)
																												// ->where('teams_map_status', '=', Config('setting.ACTIVE'))
																												->where('users_teams_map.teams_map_leader', '=', Config('setting.teams_map_leader')) // as leader
																												->get();

						if (countCustom($teamsList) > 0) // if users_login as leader 
						{
							foreach ($teamsList as $key_2 => $value_2) 
							{
								$teams_id_by_leader[] 	= $value_2['teams_serial_id'];
							}
							$teams_map 	= $this->model_teams_map_class::select('users_teams_map.users_id')
																													->leftjoin('users_teams','users_teams_map.teams_serial_id','=','users_teams.teams_serial_id')
																                          ->where('users_teams.deleted','=',Config('setting.NOT_DELETED'))
																													->where('users_teams_map.company_id', '=', $company_id)
																													// ->where('teams_map_status', '=', Config('setting.ACTIVE'))
																													->whereIn('users_teams_map.teams_serial_id', $teams_id_by_leader)
																													->groupBy('users_teams_map.users_id')
																													->get();
							if (countCustom($teams_map) > 0) 
							{
								foreach ($teams_map as $key_2 => $value_2) 
								{
									$contents_id[] 	= $value_2['users_id'];
								}
								#summary
								$roles_type 		= Config('setting.ROLES_LEADER_VIEW');
								$contents			= $contents_id;
								#end
							}
						
						}else{ // if user_id not a leader
								$roles_type 		= Config('setting.ROLES_LEADER_VIEW');
								$contents				= array($users_id);
						}
					
					}elseif ($data_roles_type == Config('setting.ROLES_OWNER_VIEW')) //4. OWNER VIEW
					{
						#summary
						$roles_type 		= Config('setting.ROLES_OWNER_VIEW');
						$contents				= array($users_id);
						#end
					}elseif ($data_roles_type == Config('setting.ROLES_LEADER_GROUP_VIEW')) // 5. LEADER GROUP
					{
						$teamsList 	= $this->model_teams_map_class::select('users_teams_map.teams_serial_id')
																												->leftjoin('users_teams','users_teams_map.teams_serial_id','=','users_teams.teams_serial_id')
															                          ->where('users_teams.deleted','=',Config('setting.NOT_DELETED'))
																												->where('users_teams_map.users_id', '=', $users_id)
																												->where('users_teams_map.company_id', '=', $company_id)
																												// ->where('teams_map_status', '=', Config('setting.ACTIVE'))
																												->where('users_teams_map.teams_map_leader', '=', Config('setting.teams_map_leader')) // as leader
																												->get();
						if (countCustom($teamsList) > 0) // if users_login as leader 
						{

							foreach ($teamsList as $key_2 => $value_2) 
							{
								$teams_id_by_leader[] 	= $value_2['teams_serial_id'];
							}

							$teams_map 	= $this->model_teams_map_class::select('users_teams_map.users_id')
																													->leftjoin('users_teams','users_teams_map.teams_serial_id','=','users_teams.teams_serial_id')
																	                        ->where('users_teams.deleted','=',Config('setting.NOT_DELETED'))
																													->where('users_teams_map.company_id', '=', $company_id)
																													// ->where('teams_map_status', '=', Config('setting.ACTIVE'))
																													->whereIn('users_teams_map.teams_serial_id', $teams_id_by_leader)
																													->groupBy('users_teams_map.users_id')
																													->get();

							if (countCustom($teams_map) > 0) 
							{
								foreach ($teams_map as $key_2 => $value_2) 
								{
									$contents_id[] 	= $value_2['users_id'];
								}
								#summary
								$roles_type 		= Config('setting.ROLES_LEADER_GROUP_VIEW');
								$contents			= $contents_id;
								#end
							}
						
						}else{ // if user_id not a leader

							$sys_api_dashboards = new sys_dashboards();
							$criteria 	= array('company_id' => $company_id, 'users_id' => $users_id);
							$teams 			= $sys_api_dashboards->teams($criteria);

							foreach ($teams as $key => $value) 
							{
								$teams_serial_id[] 	= $value['teams_serial_id'];
							}
							$teams_map = $this->model_teams_map_class::select('users_teams_map.users_id', 'users_teams_map.teams_map_leader')
																													->leftjoin('users_teams','users_teams_map.teams_serial_id','=','users_teams.teams_serial_id')
																                          ->where('users_teams.deleted','=',Config('setting.NOT_DELETED'))
																													->whereIn('users_teams_map.teams_serial_id', $teams_serial_id)
																													->where('users_teams_map.company_id', '=', $company_id)
																													->groupBy('users_teams_map.users_id')
																													->get();
							if (countCustom($teams_map) > 0) 
							{
								$teams_map 	= $teams_map->toArray();

								foreach ($teams_map as $key => $value) 
								{
									if($value['teams_map_leader'] != Config('setting.teams_map_leader'))
									{
										$contents_id[] 	= $value['users_id'];
									}
								}
								#summary
								$roles_type 		= Config('setting.ROLES_LEADER_GROUP_VIEW');
								$contents			= $contents_id;
								#end
							}

						}
						
					}
					else // 1. NO SETTING / DEFAULT
					{
						$roles_type = Config('setting.ROLES_NO_SETTING');

						$query = $this->model_users_class::select("users.id as users_id", "users.name as users_name")
									                                ->leftjoin("users_company as b", "b.users_id", "=", "users.id")
									                                ->where("b.company_id", "=", $company_id)
									                                // ->where("users.users_deleted", "=", Config("setting.NOT_DELETED"))
									                                ->get();
            if (countCustom($query) > 0)  
            {
					    $query = json_decode(json_encode($query), TRUE);
					    foreach ($query as $key => $row)
					    {
					        $contents[] = $row['users_id'];
					    }
					  }
						// $contents				= $contents;
					}

					# Result temporary for process combine multi content
					$result_tmp['roles_type'][] 	= $roles_type;
					$result_tmp['contents'][]		= $contents;
					$result_tmp['roles_edit'][]	= $roles_edit['roles_edit'];
					$result_tmp['contents_edit'][]	= $roles_edit['contents_edit'];
					$result_tmp['roles_delete'][]	= $roles_delete['roles_delete'];
					$result_tmp['contents_delete'][]	= $roles_delete['contents_delete'];
					unset($roles_type);
					unset($contents);
					# end
				} //end foreach $data_roles
			
				#combine users_id
				$contents_tmp 	= array();
				foreach ($result_tmp['contents'] as $key => $value) 
				{	
					foreach ($value as $val) {
						$contents_tmp[] 	= $val;
					}
				}

				// Roles edit
				$contents_tmp_edit 	= array();
				foreach ($result_tmp['contents_edit'] as $key => $value) 
				{	
					foreach ($value as $val) {
						$contents_tmp_edit[] 	= $val;
					}
				}

				// Roles delete
				$contents_tmp_delete 	= array();
				foreach ($result_tmp['contents_delete'] as $key => $value) 
				{	
					foreach ($value as $val) {
						$contents_tmp_delete[] 	= $val;
					}
				}
				#end
				$result['roles_type'] 	= $result_tmp['roles_type'];
				$result['contents'] 		= $contents_tmp;
				// Roles edit
				$result['roles_edit'] 	= $result_tmp['roles_edit'];
				$result['contents_edit']= $contents_tmp_edit;
				// Roles delete
				$result['roles_delete'] 	= $result_tmp['roles_delete'];
				$result['contents_delete']= $contents_tmp_delete;
			}
			else
			{
				$result['contents'] 			= array();
				$result['contents_edit'] 	= array();
				$result['contents_delete']= array();
			}
		}
		else // No Teams
		{

			$query = $this->model_users_class::select("users.id as users_id", "users.name as users_name")
						                                ->leftjoin("users_company as b", "b.users_id", "=", "users.id")
						                                ->where("b.company_id", "=", $company_id)
						                                // ->where("users.users_deleted", "=", Config("setting.NOT_DELETED"))
						                                ->get();

      if (countCustom($query) > 0)  
      {
		    $query     = json_decode(json_encode($query), TRUE);

		    foreach ($query as $key => $row)
		    {
		        $contents_dummy[]     = $row['users_id'];
		        $contents_edit_dummy[]     = $row['users_id'];
		        $contents_delete_dummy[]     = $row['users_id'];
		    }
		  }

			$result['contents'] 			= $contents_dummy;
			$result['contents_edit'] 	= $contents_edit_dummy;
			$result['contents_delete'] 	= $contents_delete_dummy;
		}

		return $result;
	}

	# Created By Gilang Persada
  	# 5 des 2019
	public function DeleteSelectedData($data_uuid, $users_id=0)
	{
		foreach ( $data_uuid as $val )
		{
				$data_update = array(
					'deleted' => Config('setting.DELETED'),
					'date_modified' => date('Y-m-d H:i:s'),
					'modified_by'		=> $users_id,
				);

				$this->model_class::where( $this->table_module.'_uuid', '=', $val)
													->update($data_update);
		}
		return true;
	}

	# Created By Prihan Firmanullah
  	# 2019-12-20
  	# For Save Customize Form
	public function SaveComments($request, $serial_id='', $company_id='', $users_id='')
	{
		//convert data mention to array. example: [{"id":"9","type":"contact","value":"Kenneth Hulthin"}]
		$data_mention = isset($request['data_mention']) ? $request['data_mention'] : array();

		unset($request['_token']);
		unset($request['uuid']);
		unset($request['data_mention']);

		$comments_message = isset ($request['comments_message']) ? $request['comments_message'] : '';

		$data_id = array();
		$user_id = array();
		$uuid4 = $this->uuid::uuid4();
		// get module owner
		$module 			= $request['rel_to_module'];
		$module_owner = $this->getOwnerModule($module, $serial_id);

		// set key and value to database input
		$request['comments_uuid'] 	= $uuid4->toString();
		$request['comments_message'] 	= $comments_message;
		$request['users_id'] 				= $users_id;
		$request['comments_status'] = Config('setting.comments_status_unread');
		$request['rel_serial_id'] 	= $serial_id;
		$request['module_owner'] 		= $module_owner;
		$request['company_id'] 			= $company_id;
		$request['date_created'] 		= date('Y-m-d H:i:s') ;
		$request['date_modified'] 	= date('Y-m-d H:i:s');
		$request['created_by'] 			= $users_id;
		$request['modified_by'] 		= $users_id;

		// process save to db
		$save_fields = $this->model_comments_class::create($request);
		$save_fields = $save_fields->toArray();

		// get comment serial id
		$last_id = $save_fields['comments_serial_id'];

		// save data_mention to table "comments_tagged"
		if(countCustom($data_mention)>0)
		{
			$this->save_comments_tagged($data_mention,$last_id,$company_id);
		}

		$data_users_id = $this->model_comments_class::select('users_id')
														->where('rel_serial_id', '=', $serial_id)
														->where('company_id','=',$company_id)
														->get()
														->toArray();

		foreach($data_users_id as $key)
		{	
			if( $key['users_id'] != 	$module_owner AND $key['users_id'] != $users_id)
			{
			$user_id[] = $key['users_id'];
			}

		}
		$user_id = array_unique($user_id);

		foreach($user_id as $key)
		{
			 $data_id[] = array('id'        => $key,
	                        'group'     => 'User'
	                        );
		}

		if(countCustom($data_id)>0)
		{
			$this->save_comments_tagged($data_id, $last_id, $company_id);
		}
		 // END TAGGED COMMENT

		$results = $save_fields;
		return $results;
	}

	# Created By Prihan Firmanullah
  	# 2019-12-20
  	# For Save Customize Form
	public function delete_data_comments($request=array(), $company_id=0, $users_id=0)
  	{	
  		$data_update['deleted'] = Config('setting.DELETED');
  		$data_update['modified_by'] = $users_id;    

    	$query = $this->model_comments_class::where('company_id', '=', $company_id)
						                               ->where(function($where) use ($request){
						                                 $where->where('comments_serial_id', '=', $request['id']);
						                               })
						                               ->update($data_update);

    	$get_child = $this->model_comments_class::where('company_id', '=', $company_id)
														    							->where('rel_to_comments', '=', $request['id'])
														    							->get();
    	if (countCustom($get_child) > 0) {
    	foreach ($get_child as $key => $value) {
    		$query = $this->model_comments_class::where('company_id', '=', $company_id)
													    								->where('comments_serial_id', '=', $value[$this->table_module.'_serial_id'])
													    								->where('deleted', '=', Config('setting.NOT_DELETED'))
													    								->update($data_update);
    		}
    	}

    	return $query;
  	}

  	# Created By Prihan Firmanullah
  	# 2019-12-20
  	# For Save Customize Form
	public function save_comments_tagged($data_mention,$comment_id, $company_id)
	{
		// insert data mention to database comments_tagged
		foreach ($data_mention as $key => $value) 
		{
			if ($value['group'] == 'User') 
			{
				$create_data = [
					'comments_serial_id' => $comment_id,
					'rel_to_users'			=> $value['id'],
					'date_created'		  => date('Y-m-d H:i:s'),
				];

				$save_tagged = $this->model_comments_tagged_class::create($create_data);
			}
			else if($value['group'] == "Team")
			{
				// for store in aws
				$teamMember  = $this->teamsMember($value['id'], $company_id);

				if (countCustom($teamMember)>0) 
				{
					foreach ($teamMember as $key => $teams) 
					{
						$create_data = [
							'comments_serial_id' => $comment_id,
							'rel_to_users'			=> $teams['users_id'],
							'date_created'		  => date('Y-m-d H:i:s'),
						];

						$save_tagged = $this->model_comments_tagged_class::create($create_data);
					}
				}
			}
		}
		return true;
	}

	# Created By Prihan Firmanullah
  	# 2019-12-20
  	# For Save Customize Form
	public function get_child($rel_to_comments, $company_id)
	{
		$get_child = $this->model_comments_class::select('rel_to_comments')
																							->where('company_id','=',$company_id)
																							->where('rel_to_comments','=',$rel_to_comments)
																							->where('deleted', '=', Config('setting.NOT_DELETED'))
																							->first();
		return $get_child;
	}

	# Created By Prihan Firmanullah
  	# 2019-12-20
  	# For Save Customize Form
	public function users_data($id)
	{ 
		// user name by user id
		$users_data = $this->model_users_class::select('id', 'name')
									->where('id','=',$id)
									->first();

		if($users_data)
		{
			$results = $users_data->toArray();
		}
		return $results;
	}

	# Created By Prihan Firmanullah
  	# 2019-12-20
  	# For Save Customize Form
	public function teamsMember($teams_serial_id = '', $company_id=0)
	{
		$fields_name 	= array(
									'users_teams_map.teams_serial_id', 
									'users_teams_map.users_id', 
									'users_teams_map.teams_map_status',
									'users_teams_map.teams_map_leader',
									'users_teams_map.date_created', 
									'a4.company_id',
									'a2.users_uuid as users_uuid', 
									'a2.name as users_name', 
									'a2.email as users_email',
									'a3.users_picture as users_picture',
									'a2.users_status'
								);

		$data 	= $this->model_teams_map_class::select($fields_name)
																						->leftjoin('users as a2', 'users_teams_map.users_id', '=', 'a2.id')
																						->leftjoin('users_information as a3', 'users_teams_map.users_id', '=', 'a3.users_id')
																						->leftjoin('users_company as a4', 'users_teams_map.users_id', '=', 'a4.users_id')
																						->where('users_teams_map.teams_serial_id', '=', $teams_serial_id)
																						->where('a4.company_id', '=', $company_id)
																						->get();
		
		if ( $this->json_mode === TRUE) 
		{
			$data = $data->toJson(); //put listing data in json
		}
		else {
			$data = $data->toArray(); // put listing data in array
		}

		return $data;				
	}

	# Created By Prihan Firmanullah
  	# 2019-12-20
  	# For Save Customize Form
	public function GetDataSerialId($rel_to_module="", $data_uuid="")
	{
		// get serial id, by module name and uuid
		$results = "";
		$query   = array();

		if ($rel_to_module === 'calls')
		{
			$query = $this->model_calls_class::select($rel_to_module.'_serial_id')->where( $rel_to_module.'_uuid', '=', $data_uuid)->first();

		}else{

			$query = $this->model_class::select($rel_to_module.'_serial_id')->where( $rel_to_module.'_uuid', '=', $data_uuid)->first();
		}
	
		
		if( countCustom($query) > 0)
		{
			$query = $query->toArray();
			$results = $query[$rel_to_module.'_serial_id'];			
		}
		
		return $results;
	}

	public function GetSerialIdArray($uuid="")
	{
		$results = "";
		$serial_id = array();
		
		foreach($uuid as $key =>$val)
		{
			if($val != '')
			{
				$query = $this->model_calls_class::select($this->table_module.'_serial_id')
																					->where($this->table_module.'_uuid', '=', $val)->first();
			}
			$serial_id[$key] = $query[$this->table_module.'_serial_id'];
		}
		
		return $serial_id;
	}

	# Created By Prihan Firmanullah
  	# 2019-12-20
  	# For Save Customize Form
	public function getOwnerModule($module = "",$serial_id="")
	{
		$select = $module.'_owner';

		$query 	  = DB::table($module)->select($select)->where($module.'_serial_id','=',$serial_id)->first();
		$query  	= json_decode(json_encode($query),true);
		$results  = $query[$module.'_owner'];

		return $results; 
	}

	# Created By Gilang Persada
  # 5 des 2019
  # For save and log mass update
	public function SaveAndLogMassUpdate($data, $users_id, $company_id)
	{
		$data_origin = $data;
		$core 	= array();
		$custom = array();
		$update = array();
		$result = array();
		$updateCustom = array();
		$update_log = array();
		$sys = new sys();
		$listReceiver = array();

		unset($data_origin['_token']);
		unset($data_origin['subaction']);
		unset($data_origin['leads_uuid']);

		if ($data['type_massupdate'] == 'alldata')
		{
				// Define variable
				$update = array();

				$DataLog = $this->model_class::where('company_id', '=', $company_id)->first();

				if ( countCustom($DataLog) > 0 )
				{
					if ( $this->json_mode === TRUE) {
						$DataLog = $DataLog->toJson(); //put listing data in json
					}
					else {
						$DataLog = $DataLog->toArray(); // put listing data in array
					}
				}

				foreach ($data_origin as $key2 => $value2) 
				{
					$check_checkbox = substr($key2, 0, 9);

					if ( $check_checkbox == 'checkbox_' ) 
					{
						$field_serial_id = $this->GetFieldSerialId($data_origin[$key2]);

						if ( !isEmpty($field_serial_id) ) 
						{
							$core[$field_serial_id]['from'] 	= $DataLog[$data_origin[$key2]];
							$core[$field_serial_id]['to'] 		= $data_origin[$value2];

							// This is for Save Data Mass Update
							$update[$data_origin[$key2]] = $data_origin[$value2];
							$update_log[$data_origin[$key2]] = $data_origin[$value2];
						}

						$check_custom_field = substr($key2, 9, 13);

						if(str_contains($key2, 'custom_'.$this->table_module.'_'))
						{
							
							$name_custom = substr($key2, 16);

							$search_custom_value = $this->model_custom_fields_class::select($this->table_module.'_custom_values_maps' , $this->table_module.'_custom_fields_serial_id')
																																			->where('company_id', '=', $company_id)
																																			->where($this->table_module.'_custom_fields_name', '=', $name_custom)
																																			->get()
																																			->toArray();
							
							foreach ($search_custom_value as $key3 => $valu3) {
								# code...
								$custom_fields_serial_id = $valu3[$this->table_module.'_custom_fields_serial_id'];
								$custom_key = $valu3[$this->table_module.'_custom_values_maps'];
							// $custom[$data_origin[$key2]]['from']     = $old_log[$key][$data_origin[$key2]];
							// $custom[$data_origin[$key2]]['to']         = $data_origin[$custom_key];
							
								if(isset($custom_key))
								{
									$updateCustom[$custom_key] = $data_origin['custom_'.$name_custom];
									$update_log[$this->table_module.'_custom_values_text'][$custom_fields_serial_id] = $data_origin['custom_'.$name_custom];
								}
							}
						}
					}
				}

				// Increment receiver notification by owner (excluding the same submitter)
				if (!empty($update) AND !empty($update["{$this->table_module}_owner"]) AND $update["{$this->table_module}_owner"] != $users_id)
				{
					if (!isset($listReceiver[$update["{$this->table_module}_owner"]]))
					{
						$listReceiver[$update["{$this->table_module}_owner"]] = 0;
					}
					$listReceiver[$update["{$this->table_module}_owner"]]++;
				}

				$get_serial_id = $this->model_class::where('company_id', '=', $company_id)->where('deleted', config('setting.NOT_DELETED'))->get()->toArray();

				$old_log = $get_serial_id;

				foreach ($get_serial_id as $key_serial => $value_serial) 
				{
					$syslog_action  = $sys->log_update($old_log[$key_serial], $update_log, $this->table_module, $company_id);
					if ( !isEmpty($syslog_action) )
					{
						$syslog 			= $sys->sys_api_syslog( $syslog_action, 'massupdate', $this->table_module, $value_serial[$this->table_module.'_serial_id'], $users_id, $company_id );
					}
				}

				// This is for Save Data Mass Update
				$update['date_modified'] 		= date('Y-m-d H:i:s');
				$update['modified_by'] 			= $users_id;

				if(countCustom($update) > 0)
				{
					$this->model_class::where('company_id', '=', $company_id)
															->where('deleted', '=', config('setting.NOT_DELETED'))
															->update($update);
					// End This is for Save Data Mass Update
				}
				
				if(countCustom($updateCustom) > 0)
				{
					// This is for Save Data Mass Update Custom Fields
					$this->model_custom_values__class::where('company_id', '=', $company_id)
																					->update($updateCustom);
				}
		}
		else
		{
			$DataLog = $this->DataMassUpdate($data);

			$old_log = $DataLog;
			if(countCustom($DataLog) > 0)
			{
				foreach($DataLog as $key_log => $val)
				{
					$tmp[$key_log] = array();
					
					if(isset($val[$this->table_module.'_uuid']) AND isset($val['company_id']) )
					{
						$tmp[$key_log] =    $this->old_data_log($val[$this->table_module.'_uuid'], $val['company_id']);
					}

					if(!is_array($tmp[$key_log]))
					{
						$old_log[$key_log] = $tmp[$key_log]->toArray();

					}
				}
			}

			$custom_field = "custom_field_";
			foreach ($data[$this->table_module.'_uuid'] as $key => $value) 
			{
				// Define variable
				$update = array();

				foreach ($data_origin as $key2 => $value2) 
				{
					$check_checkbox = substr($key2, 0, 9);

					if ( $check_checkbox == 'checkbox_' ) 
					{
						$field_serial_id = $this->GetFieldSerialId($data_origin[$key2]);
						if ( !isEmpty($field_serial_id) ) 
						{
							$core[$field_serial_id]['from'] 	= $DataLog[$key][$data_origin[$key2]];
							$core[$field_serial_id]['to'] 		= $data_origin[$value2];

							// This is for Save Data Mass Update
							$update[$data_origin[$key2]] = $data_origin[$value2];
							$update_log[$data_origin[$key2]] = $data_origin[$value2];
						}

						$check_custom_field = substr($key2, 9, 13);

						if(str_contains($key2, 'custom_'.$this->table_module.'_'))
						{
							
							$name_custom = substr($key2, 16);

							$search_custom_value = $this->model_custom_fields_class::select($this->table_module.'_custom_values_maps' , $this->table_module.'_custom_fields_serial_id')
																																			->where('company_id', '=', $company_id)
																																			->where($this->table_module.'_custom_fields_name', '=', $name_custom)
																																			->get()
																																			->toArray();
							
							foreach ($search_custom_value as $key3 => $valu3) {
								# code...
								$custom_fields_serial_id = $valu3[$this->table_module.'_custom_fields_serial_id'];
								$custom_key = $valu3[$this->table_module.'_custom_values_maps'];
							// $custom[$data_origin[$key2]]['from']     = $old_log[$key][$data_origin[$key2]];
							// $custom[$data_origin[$key2]]['to']         = $data_origin[$custom_key];
							
								if(isset($custom_key))
								{
									$updateCustom[$custom_key] = $data_origin['custom_'.$name_custom];
									$update_log[$this->table_module.'_custom_values_text'][$custom_fields_serial_id] = $data_origin['custom_'.$name_custom];
								}
							}
						}

					}
				}

				// Increment receiver notification by owner (excluding the same submitter)
				if (!empty($update) AND !empty($update["{$this->table_module}_owner"]) AND $update["{$this->table_module}_owner"] != $users_id)
				{
					if (!isset($listReceiver[$update["{$this->table_module}_owner"]]))
					{
						$listReceiver[$update["{$this->table_module}_owner"]] = 0;
					}
					$listReceiver[$update["{$this->table_module}_owner"]]++;
				}
				
				// if ( countCustom($core) > 0 ) 
				// {
				// 	$result = array(
				// 			'CORE' 		=> $core,
				// 			'CUSTOM' 	=> $custom,
				// 	);

				// 	$result = json_encode($result);

				// 	$syslog 				= sys_api_syslog( $result, 'massupdate', $this->table_module, $value, $users_id, $company_id );
				// }

				// $syslog_action  = log_update($DataLog[$key], $update, $this->table_module, $company_id);
				$get_serial_id = $this->GetSerialIdByUuid($value, $company_id);

				$syslog_action  = $sys->log_update($old_log[$key], $update_log, $this->table_module, $company_id);
				if ( !isEmpty($syslog_action) )
				{
					$syslog 				= $sys->sys_api_syslog( $syslog_action, 'massupdate', $this->table_module, $get_serial_id, $users_id, $company_id );
				}

				// This is for Save Data Mass Update
				$update['date_modified'] 		= date('Y-m-d H:i:s');
				$update['modified_by'] 			= $users_id;

				if(countCustom($update) > 0)
				{
		
					$this->model_class::where($this->table_module.'_uuid', '=', $value)
															->where('company_id', '=', $company_id)
															->update($update);
					// End This is for Save Data Mass Update
				}

				$get_serial_id = $this->GetDataSerialId($this->table_module, $value);

				if(countCustom($updateCustom) > 0)
				{
					// This is for Save Data Mass Update Custom Fields
					$this->model_custom_values__class::where('company_id', '=', $company_id)
																					->where($this->table_module.'_serial_id', '=',  $get_serial_id)
																					->update($updateCustom);
				}
			}
		}

		// Initiate push notification helper function
		$sys_push = new sys_push();
		// Get access token
		$accessToken = getCurrentUserToken();
		// Generate socket request
		$requestSocket = array(
			"platform" => "web",
			"notification_type" => Config("setting.NOTIFICATION_ASSIGNMENT_BULK_TYPE"),
			"payload" => array(
				"action" => "update",
				"table_module" => $this->table_module,
				"listReceiver" => $listReceiver,
			),
			"access_token" => $accessToken,
		);
		$sys_push->emitPushNotification($requestSocket);

		return true;
	}

	# Created By Gilang Persada
  	# 5 des 2019
  	# Data for mass update
	public function DataMassUpdate($data)
	{
		$result = array();

		foreach ($data[$this->table_module.'_uuid'] as $key => $value) 
		{
			$Get1 = $this->GetDataByUuid($value);

			$result[$key]	= $Get1;
		}

		return $result;
	}

	# Created By Gilang Persada
  # 5 des 2019
  # For get data listing by uuid
	public function GetDataByUuid($leads_uuid)
	{
		// define variable
		$result = array();

		// Get data find by data_uuid
		$result = $this->model_class::where( $this->table_module.'_uuid', '=', $leads_uuid)->first();

		if ( countCustom($result) > 0 )
		{
			if ( $this->json_mode === TRUE) {
				$result = $result->toJson(); //put listing data in json
			}
			else {
				$result = $result->toArray(); // put listing data in array
			}
		}

		return $result;
	}

	# Created By Gilang Persada
  # 5 des 2019
	public function old_data_log($data_uuid=0, $company_id=0)
	{
		$sys = new sys();
		// Define variable
		$result 			= array();

		if ( $data_uuid == '' OR $company_id == 0 OR $company_id == '' )
		{
			return $result;
		}

		// Get data find by data_uuid
		$fields = array(
								$this->table_module.'.*',
								"sys_rel.rel_to_module as ".$this->table_module."_parent_type, sys_rel.rel_to_id as ".$this->table_module."_parent_id",
							);
		$fields = implode(', ', $fields);
		// $fields = $this->table_module.'.*';

		$query = $this->model_class::where($this->table_module.'.company_id', '=', $company_id);

		// Check custom fields exists ?
		$temp_select = $sys->library_select_custom_fields_as_id($this->table_module, $company_id);
		
		if ( countCustom($temp_select) > 0 )
		{
			$select = implode(', ', $temp_select);
			$select = ",".$select;
		}
		else
		{
			$select = '';
		}

		$query->select(DB::raw($fields.$select)); // For select fields name || module_owner_id (for merge data)
		$query->leftjoin($this->table_module.'_custom_values_ as mcv', function($join) use ($company_id)
						{
							$join->on('mcv.'.$this->table_module.'_serial_id', '=', $this->table_module.'.'.$this->table_module.'_serial_id')
							->where('mcv.company_id', '=', $company_id);
						});
		$query->leftjoin('sys_rel', function($join)
		        { 
		            $join->on('sys_rel.rel_from_id', '=', $this->table_module.'.'.$this->table_module.'_serial_id')
		            ->where('sys_rel.rel_from_module', '=', $this->table_module);
		        });
		$query->where($this->table_module.'_uuid', '=', $data_uuid); // For condition where uuid
		$query = $query->first(); // Get data first

		if ( countCustom($query) > 0 ) 
		{
			$result = $query;
		}

		return $result;
	}

	# Created By Gilang Persada
  # 5 des 2019
	public function GetFieldSerialId($field_name)
	{
		$result = '';
		
		$get_field = $this->model_fields_class::where($this->table_module.'_fields_name', '=', $field_name)->first();
		if ( countCustom($get_field) > 0 ) 
		{
			$result = $get_field[$this->table_module.'_fields_serial_id'];
		}

		return $result;
	}

	# Created By Prihan Firmanullah
  # 2019-12-20
  # For Save Customize Form
	public function get_fields_by_name($fields_name='', $company_id=0)
	{
		$sys = new sys();

		// Define variable
		$result = array();

		$table_module = $this->table_module;

		// Query first data fields 
		$query = $this->model_fields_class::where($this->table_module.'_fields_name', '=', $fields_name)
																			->leftJoin($this->table_module.'_fields_change as b', function($join) use ($company_id, $table_module)
															        {
															            $join->on('b.'.$table_module.'_fields_serial_id', '=', $table_module.'_fields.'.$table_module.'_fields_serial_id')
															            ->where('b.company_id', '=', $company_id);
															        })
																			->first();


		if ( countCustom($query) > 0  ) 
		{
			$result = $query;
      if (!empty($result[$this->table_module.'_fields_change_input_type'])) 
      {
        $result[$this->table_module.'_fields_input_type'] = $result[$this->table_module.'_fields_change_input_type'];
        $result[$this->table_module.'_fields_options'] = $result[$this->table_module.'_fields_change_options'];
        $result[$this->table_module.'_fields_readonly'] = $result[$this->table_module.'_fields_change_readonly'];
        $result[$this->table_module.'_fields_default_value'] = $result[$this->table_module.'_fields_change_default_value'];
				$result["condition"] = $this->getFieldsCondition($result[$this->table_module.'_fields_serial_id'] , $company_id , 0);
      }
			// This is for get input type, add key 'html'
			$result	= $sys->sys_api_input_type_first($result, $this->table_module);
		}

		return $result;
	}

	# Created By Prihan Firmanullah
  # 2019-12-20
  # For Save Customize Form
	public function get_content($uuid=0, $fields_name='', $company_id=0)
	{
		// Define variable
		$result = '';

		// Query for get first data fields value
		$query = $this->model_class::select(DB::raw($fields_name))
															->where('company_id', '=', $company_id)
															->where($this->table_module.'_uuid', '=', $uuid)
															->first();

		// Check condition if empty
		if ( countCustom($query) > 0 ) 
		{
			// Default content
			$content = $query[$fields_name]; 

			// If criteria owner, change id owner to name owner
			if ( $fields_name == $this->table_module.'_owner' ) 
			{
				$check_name = $this->model_users_class::select(DB::raw('name'))
																					->where('id', '=', $content)
																					->first();

				if ( countCustom($check_name) > 0 ) 
				{
					// Content if owner (name owner)
					$content = $check_name['name'];
				}
			}

			// Result 
			// Ahmad Yatallatof or tes or etc. (string)
			$result = $content;
		}

		return $result;
	}

	# Created By Prihan Firmanullah
  # 2019-12-20
  # For Save Customize Form
	public function dropdown($value, $company_id)
	{
		$data = $this->model_dropdown_class::leftjoin('dropdown_options', 'dropdown.dropdown_serial_id', '=', 'dropdown_options.dropdown_serial_id')
                                      ->where('dropdown.dropdown_name', '=', $value)
                                      ->where(function($query) use ($company_id)
                                      {
		                                    $query->where('dropdown.company_id', '=', $company_id)
		                                    ->orWhere('dropdown.company_id', '=', Config('setting.company_default'));
		                                  })
																			->get();

		if ( $this->json_mode === TRUE) {
			$data = $data->toJson(); //put listing data in json
		}
		else {
			$data = $data->toArray(); // put listing data in array
		}

		return $data;
	}

	# Created By Prihan Firmanullah
  # 2019-12-20
  # For Save Customize Form
	//this function used by set attribute in valiasi message with label not key field
	public function ValidateFormSingle($validation_fields, $field=array())
	{
		$rules = array();

		foreach ($validation_fields as $key => $value) 
		{
			if ( isset($value[$this->table_module.'_custom_fields_serial_id']) ) // This is for custom fields
			{
				if ( $value[$this->table_module.'_custom_fields_status'] == Config('setting.custom_fields_status_inactive') ) 
				{
					continue;
				}

				if (isset($value[$this->table_module.'_custom_fields_validation_multi']))
				{
					$name_fields[$key] = $value[$this->table_module.'_custom_fields_name'];

          // Input type phone fields always use phone validation
          if(isset($value[$this->table_module.'_custom_fields_input_type']) AND $value[$this->table_module.'_custom_fields_input_type'] == "phone")
          {
            $validate_fields[$key][] = "regex:/^([0-9\s\-\+\(\)]*)$/";
          }

					foreach ($value[$this->table_module.'_custom_fields_validation_multi'] as $key2 => $multi_validation) 
					{
						$validate_fields[$key][] = $multi_validation;
					}
				}
				else
				{
					$name_fields[$key] = $value[$this->table_module.'_custom_fields_name'];

          // Input type phone fields always use phone validation
          if(isset($value[$this->table_module.'_custom_fields_input_type']) AND $value[$this->table_module.'_custom_fields_input_type'] == "phone")
          {
            $validate_fields[$key][] = "regex:/^([0-9\s\-\+\(\)]*)$/";
          }
          else
          {
					  $validate_fields[$key][] = "";
          }
				}
			}
			else // This is for core fields
			{
				if ( $value[$this->table_module.'_fields_status'] == Config('setting.fields_status_inactive') ) 
				{
					continue;
				}
		
				if (isset($value[$this->table_module.'_fields_validation_multi']))
				{
					$name_fields[$key] = $value[$this->table_module.'_fields_name'];

          // Input type phone fields always use phone validation
          if(isset($value[$this->table_module.'_fields_input_type']) AND $value[$this->table_module.'_fields_input_type'] == "phone")
          {
            $validate_fields[$key][] = "regex:/^([0-9\s\-\+\(\)]*)$/";
          }

					foreach ($value[$this->table_module.'_fields_validation_multi'] as $key2 => $multi_validation) 
					{	
						$validate_fields[$key][] = $multi_validation;
					}
				}else{
					$name_fields[$key] = $value[$this->table_module.'_fields_name'];

          // Input type phone fields always use phone validation
          if(isset($value[$this->table_module.'_fields_input_type']) AND $value[$this->table_module.'_fields_input_type'] == "phone")
          {
            $validate_fields[$key][] = "regex:/^([0-9\s\-\+\(\)]*)$/";
          }
          else
          {
					  $validate_fields[$key][] = "";
          }
				}
			}
		}

		foreach ($validation_fields as $key => $value) 
		{
			if ( (isset($value[$this->table_module.'_custom_fields_status']) AND $value[$this->table_module.'_custom_fields_status'] == Config('setting.custom_fields_status_inactive')) 
					OR 
					(isset($value[$this->table_module.'_fields_status']) AND $value[$this->table_module.'_fields_status'] == Config('setting.fields_status_inactive')) ) 
			{
				continue;
			}

			if (isset($field['module_fields_name'])) 
			{
				$moduleFieldsName = $field['module_fields_name'];
				if ($moduleFieldsName == $name_fields[$key]) 
				{
					$rules[$name_fields[$key]] = $validate_fields[$key];
				}
			}

			if(isset($field[$this->table_module.'_custom_fields_name']))
			{
				$custom = substr($name_fields[$key],7);
				$moduleFieldsName = $field[$this->table_module.'_custom_fields_name'];
				
				if ($moduleFieldsName == $custom) 
				{
					$rules[$name_fields[$key]] = $validate_fields[$key];
				}
			}
		}
		
		// Result
		// Array
		// (
		//     [leads_salutation] => required
		//     [leads_first_name] => 
		//     [leads_custom_values_text.67] => 
		//     [leads_custom_values_text.66] => email
		// )

		return $rules;
	}

	# Created By Prihan Firmanullah
  # 2019-12-20
  # For Save Customize Form
	//this function used by set attribute in valiasi message with label not key field
	public function ValidateAttributeForm($validation_fields)
	{
		foreach ($validation_fields as $key => $value) 
		{
			if ( isset($value[$this->table_module.'_custom_fields_serial_id']) ) // This is for custom fields
			{
				if ( $value[$this->table_module.'_custom_fields_status'] == Config('setting.custom_fields_status_inactive') ) 
				{
					continue;
				}
				
				if (isset($value[$this->table_module.'_custom_fields_validation']))
				{
					$name_fields[$key] = $this->table_module."_custom_values_text.".$value[$this->table_module.'_custom_fields_serial_id'];
					$validate_fields[$key] = $value[$this->table_module.'_custom_fields_label'];
				}
				else
				{
					$name_fields[$key] = $this->table_module."_custom_values_text.".$value[$this->table_module.'_custom_fields_serial_id'];
					$validate_fields[$key] = "";
				}
			}
			else // This is for core fields
			{
				if ( $value[$this->table_module.'_fields_status'] == Config('setting.fields_status_inactive') ) 
				{
					continue;
				}
		
				if (isset($value[$this->table_module.'_fields_validation']))
				{
					$name_fields[$key] = $value[$this->table_module.'_fields_name'];
					$validate_fields[$key] = $value[$this->table_module.'_fields_label'];
				}
				else
				{
					$name_fields[$key] = $value[$this->table_module.'_fields_name'];
					$validate_fields[$key] = "";
				}
			}
		}

		foreach ($validation_fields as $key => $value) 
		{
			if ( (isset($value[$this->table_module.'_custom_fields_status']) AND $value[$this->table_module.'_custom_fields_status'] == Config('setting.custom_fields_status_inactive')) 
						OR 
						(isset($value[$this->table_module.'_fields_status']) AND $value[$this->table_module.'_fields_status'] == Config('setting.fields_status_inactive')) ) 
			{
				continue;
			}
			
			$rules[$name_fields[$key]] = $validate_fields[$key];
		}
			
		// Result
		// Array
		// (
		//     [leads_salutation] => Title
		//     [leads_first_name] => Nama Awal
		//     [leads_custom_values_text.67] = Job title
		//     [leads_custom_values_text.66] => email
		// )
		return $rules;
	}

	# Created By Prihan Firmanullah
  # 2019-12-20
  # For Save Customize Form
	public function validate_msg($rule=array(), $table_module='')
	{
		$db 			= 'Illuminate\Support\Facades\DB';
		$result 	= array();

		if (countCustom($rule) > 0) 
		{
			foreach ($rule as $key => $value) // ex. $key=calls_name | $value=required
			{
				// Check Core || Custom
				$field_type 	= explode("_", $key); // calls_custom_value_text.1
				
				if ($field_type[0] == "mod" AND $field_type[1] == "values") // Modules dynamic Custom
				{
					$module_serial_id 	= explode(".", $field_type[2]);
					$moduleField 	= $db::table("mod_fields")
														->select("mod_fields_label as field_label")
														->where("mod_fields_serial_id", '=', $module_serial_id[1])
														->first();
				}
				elseif ($field_type[0] == "mod" AND $field_type[1] == "data") // Modules dynamic Core
				{
					$moduleField 	= $db::table("mod_core")
														->select("mod_core_label as field_label")
														->where("mod_core_name", '=', $key)
														->first();
				}
				else{ // core
					$moduleField 	= $db::table($table_module.'_fields')
														->select($table_module.'_fields_label as field_label')
														->where($table_module.'_fields_name', '=', $key)
														->first();
				}
				// END Check Core or Custom
				
				if (countCustom($moduleField) > 0) 
				{
					$moduleField 	= (array) $moduleField; // convert object format to array format
					$fieldLabel = $moduleField['field_label'];

					foreach ($value as $key2 => $value2) 
					{
						if ($value2 == "required") // validation : required
						{
							$result[$key.'.'.'required'] 	= trans('validation.'.$value2);
						}
						else
						{
							$result[$key.'.'.$value2] 	= trans('validation.'.$value2);
						}
					}

				}
			}
		}

		return $result;
	}

	# Created By Prihan Firmanullah
  # 12-09-2019
  # For Maps Name Used
	public function fieldsUpdate($request=array(), $company_id=0, $users_id=0)
	{
		$data_update = array();
		$module_uuid 	= $request[$this->table_module.'_uuid'];
		$module_fields_name	= $request['module_fields_name'];

		$check_input_type 	= $this->model_fields_class::where($this->table_module.'_fields_name', '=', $module_fields_name)
																									->first();

		$content = isset($request[$module_fields_name]) ? $request[$module_fields_name] : '';
		if ( countCustom($check_input_type) > 0 ) 
		{
			if ( $check_input_type[$this->table_module.'_fields_input_type'] == 'datetime' ) 
			{
				$content = date('Y-m-d H:i:s', strtotime($request[$module_fields_name]));
			}
			elseif ( $check_input_type[$this->table_module.'_fields_input_type'] == 'date' ) 
			{
				$content = date('Y-m-d', strtotime($request[$module_fields_name]));
			
			}
		}

		if ($module_fields_name == $this->table_module.'_date_start')
		{
			$field[$module_fields_name]	= $content;
			$field[$this->table_module.'_date_end'] = $content;
		}
		else
		{
			$field[$module_fields_name]	= $content;
		}
		
		$field['date_modified'] 		= date('Y-m-d H:i:s');
		$field['modified_by']				= $users_id;

		$data 	= $this->model_class::where($this->table_module.'_uuid', '=', $module_uuid)
																->where('company_id', '=', $company_id)
																->update($field);
		if ($data) 
		{
			if($module_fields_name == $this->table_module.'_owner')
			{
				$content = $this->model_users_class::select('name')
																					 ->where('id', '=', $content)
							                             ->where('users_last_company', '=', $company_id)
							                             ->first();
				if(!isEmpty($content))
				{
					$data_update = array($module_fields_name => $content->name);
				}
			}
			else
			{
				$data_update = array($module_fields_name => $content);
			}
		}

		$GetDataByUuid = $this->GetDataByUuid($module_uuid);
		if (!isEmpty($GetDataByUuid)) 
		{
			elasticAddData($GetDataByUuid[$this->table_module.'_serial_id'], $company_id, $this->table_module);
		}

		return $data_update;
	}

	# Created By Prihan Firmanullah
  # 12-09-2019
  # For Maps Name Used
	public function old_data_log_custom($serial_id='', $company_id='')
	{
			$data_query = $this->model_custom_fields_class::where($this->table_module.'_custom_fields_serial_id', $serial_id)
																								->where('company_id', '=', $company_id)
																								->first();
				if ( $this->json_mode === TRUE) {
				$data = $data_query->toJson(); //put listing data in json
				}
				else {
				$data = $data_query; // put listing data in array
				}

				return $data;
	}

	# Created By Prihan Firmanullah
  # 12-09-2019
  # For Maps Name Used
	public function old_data_log_core($serial_id='', $company_id='')
	{
			$data = array();

			$data = $this->model_fields_change_class::where($this->table_module.'_fields_change_serial_id', $serial_id)
																							->where('company_id', '=', $company_id)
																							->first();

				if (!isEmpty($data)) 
				{
					$data = json_decode(json_encode($data),true); //put listing data in json
				}
				else 
				{
					$data_core = $this->model_fields_class::where($this->table_module.'_fields_serial_id', $serial_id)
																							->first();
					if(!isEmpty($data_core))
					{
						$data_core = $data_core->toArray();

						$data[$this->table_module.'_fields_change_label'] = $data_core[$this->table_module.'_fields_label'];
						$data[$this->table_module.'_fields_serial_id'] = $data_core[$this->table_module.'_fields_serial_id'];
						$data[$this->table_module.'_fields_change_input_type'] = $data_core[$this->table_module.'_fields_input_type'];
						$data[$this->table_module.'_fields_change_validation'] = $data_core[$this->table_module.'_fields_validation'];
						$data[$this->table_module.'_fields_change_status'] = $data_core[$this->table_module.'_fields_status'];
						$data[$this->table_module.'_fields_change_options'] = $data_core[$this->table_module.'_fields_options'];
						$data[$this->table_module.'_fields_change_quick'] = $data_core[$this->table_module.'_fields_quick'];
						$data[$this->table_module.'_fields_change_readonly'] = $data_core[$this->table_module.'_fields_readonly'];
						$data[$this->table_module.'_fields_change_default_value'] = $data_core[$this->table_module.'_fields_default_value'];
						$data['company_id'] = $company_id;
					}
				}

				return $data;
	}

	# Created By Prihan Firmanullah
  # 12-09-2019
  # For Maps Name Used
	public function log_update_custom_core($tabledata="", $old_data=array(), $request=array(), $company_id=0, $typefields = '')
	{
		$result 				= '';
		$log_re 		= array();

		unset($request['_token']);
		unset($old_data[$this->table_module.'_fields_change_serial_id']);
		unset($request[$this->table_module.'_fields_change_serial_id']);
		
		if($typefields == 'customfields')
		{
			if (!empty($request[$this->table_module.'_custom_fields_validation']))
			{
				foreach ($request[$this->table_module.'_custom_fields_validation'] as $key => $value)
				{
					if(countCustom($request[$this->table_module.'_custom_fields_validation']) > 1)
					{
						$validation = "";
						$validation .= $value;
	
						$result = $result."$validation|";
					}else{
						$result = $value;
					}
				}
			}
			$request[$this->table_module.'_custom_fields_validation'] 		= $result;
		}
		else 
		{
			if (!empty($request[$this->table_module.'_fields_change_validation']))
			{
				foreach ($request[$this->table_module.'_fields_change_validation'] as $key => $value)
				{
					if(countCustom($request[$this->table_module.'_fields_change_validation']) > 1)
					{
						$validation = "";
						$validation .= $value;
	
						$result = $result."$validation|";
					}else{
						$result = $value;
					}
				}
			}
			$request[$this->table_module.'_fields_change_validation'] 		= $result;
		}
		unset($request['radio_multiple']);
        unset($request['options_zero']);
        unset($request['options_one']);
        unset($request['dropdown_serial_id']);
        unset($request["dropdown_option_group"]); // unset from single option
        unset($request["radio"]); // unset from multiple option
        unset($request["options_choose"]); // unset from choose option
        unset($request["options_new"]); // unset from create new option
        unset($request["create_options"]); // unset from create new option

		// for fields option
		unset($request['fields_type']);
		unset($request['fields_type_action']);
		unset($request['fields_value']);
		unset($request['fields_options']);

		foreach ($request as $key => $value) 
		{
			if ( $old_data[$key] == $value )
			{
				continue; 
			} 
			else 
			{ 
				if ($key == $tabledata.'_custom_fields_status' || $key == $tabledata.'_fields_change_status') 
				{
					if($old_data[$key] == 1)
					{
						$old_data[$key] = 'Active';
					}
					else 
					{
						$old_data[$key] = 'Not Active';
					}

					if ($value == 1) 
					{
						$value = 'Active';

					}
					else
					{
						$value = 'Not active';
					}
					$value  = implode(', ', (array)$value);
					$value  = "'".$value."'";
				}
				elseif ($key == $tabledata.'_custom_fields_quick' || $key == $tabledata.'_fields_change_quick' || $key == $tabledata.'_fields_change_readonly' || $key == $tabledata.'_custom_fields_readonly') 
				{
					if($old_data[$key] == 1)
					{
						$old_data[$key] = 'yes';
					}
					else 
					{
						$old_data[$key] = 'no';
					}

					if ($value == 1) 
					{
						$value = 'yes';

					}
					else
					{
						$value = 'no';
					}
					$value  = implode(', ', (array)$value);
					$value  = "'".$value."'";
				}
				elseif ($key == $tabledata.'_custom_fields_validation' || $key == $tabledata.'_fields_change_validation') 
				{
					if($old_data[$key] == 'required')
					{
						$old_data[$key] = 'Required';
					}
					else if($old_data[$key] == 'email')
					{
						$old_data[$key] = 'Email';
					}
					else 
					{
						$old_data[$key] = '-';
					}

					if ($value == 'required') 
					{
						$value = 'Required';
					}
					else if($value == 'email')
					{
						$value = 'Email';
					}
					else 
					{
						$value = '-';	
					}

					$value  = implode(', ', (array)$value);
					$value  = "'".$value."'";
				}
				else
				{
					$value  = json_encode($value);
					$value  = "'".$value."'";
				}

				$log_re[] = $key." : "."'".$old_data[$key]."'"." &#8594; ".$value;
			}
		}

		if (!isEmpty($log_re) AND countCustom($log_re) > 0) 
		{
			$result = implode(', ', $log_re);
			$result = "Fields Label ".(isset($old_data[$this->table_module.'_custom_fields_label']) ? $old_data[$this->table_module.'_custom_fields_label'] : $old_data[$this->table_module.'_fields_change_label'])." ".$result;
		}

		return $result;
	}

	# Created By Prihan Firmanullah
  # 12-09-2019
  # For Maps Name Used
	public function sys_api_syslog( $syslog_action='', $syslog_action_type='', $syslog_module_name='', $syslog_data_id=0, $users_id=0, $company_id=0 )
	{
		$get_ip = $_SERVER['REMOTE_ADDR'];
		$db = 'Illuminate\Support\Facades\DB';
		
		$db::table('syslog')->insert(
				[ 
					'syslog_action' => $syslog_action, 
					'syslog_action_type' => $syslog_action_type, 
					'syslog_module_name' => $syslog_module_name, 
					'syslog_data_id' => $syslog_data_id, 
					'syslog_date_created' => date('Y-m-d H:i:s'), 
					'syslog_created_by' => $users_id, 
					'company_id' => $company_id,
					'syslog_ip' => $get_ip,
				]
		);
		
		return true;
	}

	# Created By Prihan Firmanullah
  # 12-09-2019
  # For Update Custom Fields Groups Op
	public function UpdateCustomFieldsGroupOp($request, $company_id, $users_id)
	{
		$original_data = $request; // Set original data from request controllers

		// PROCCESS DATA (REQUEST) FOR INSERT INTO TABLE_MODULE CUSTOM FIELDS
		$hasil = '';
		if (!empty($request["{$this->table_module}_custom_fields_validation"]))
		{
			foreach ($request["{$this->table_module}_custom_fields_validation"] as $key => $value) {
				if(countCustom($request["{$this->table_module}_custom_fields_validation"]) > 1)
				{
					$validation = "";
					$validation .= $value;

					$hasil = $hasil."$validation|";
				}else{
					$hasil = $value;
				}
			}

			// PROCCESS DATA (REQUEST) FOR INSERT INTO TABLE_MODULE CUSTOM FIELDS
		}
		$request["{$this->table_module}_custom_fields_validation"] 		= $hasil;

		$dropdown_serial_id = isset($request['dropdown_serial_id']) ? $request['dropdown_serial_id'] : '';
		unset($request["_token"]);
		if(isEmpty($dropdown_serial_id))
		{
			$standard_name1 = preg_replace('/[^\p{L}\p{N}\s]/u', '', $request[$this->table_module."_custom_fields_label"]);
			$standard_name1 = str_replace(' ', '_', $standard_name1);
			$standard_name1 = $this->table_module."_".strtolower($standard_name1);
			$standard_name = $this->CheckName($standard_name1, $company_id);

			$data_dropdown['dropdown_name'] 		= date('YmdHis_').strtolower($standard_name1);
			$data_dropdown['company_id'] 				= $company_id;
			$data_dropdown['dropdown_group'] 		= 1;
			$data_dropdown['deleted'] 					= Config('setting.NOT_DELETED');

			$save_dropdown 	= $this->model_dropdown_class::create($data_dropdown);
			$last_id 				= $save_dropdown->dropdown_serial_id;

			# 2. insert into tbl dropdown_options
			foreach ($request['dropdown_option_group'] as $key => $value) 
			{

				if ($request['radio'][$key] == 'new') 
				{
					// New dropdown
					foreach ($request['options_new'][$key] as $key2 => $value2) 
					{
						if ( !isEmpty($value2) ) 
						{
							$dropdown_options_['dropdown_serial_id'] 		 = $last_id;
							$dropdown_options_['dropdown_options_value'] = $value2;
							$dropdown_options_['dropdown_options_group'] = $request['dropdown_option_group'][$key];
							$dropdown_options_['dropdown_options_label'] = $value2;
							// print_r($dropdown_options_);
							$save_dropdown_options = $this->model_dropdown_options_class::insert($dropdown_options_);
						}
					}
				} else {
					// Choose Dropdown 
					$dropdown_serial_id = $this->model_dropdown_class::select('dropdown_serial_id')
																->where('dropdown_name', '=', $request['options_choose'][$key])
																->where('company_id', '=', $company_id)
																->first();
					if (countCustom($dropdown_serial_id) > 0) 
					{
						$dropdown_serial_id = $dropdown_serial_id['dropdown_serial_id'];
						$dropdown_options = $this->model_dropdown_options_class::where('dropdown_serial_id', '=', $dropdown_serial_id)
						->get()->toArray();

						foreach ($dropdown_options as $key_in => $value_in) 
						{
							$dropdown_options[$key_in]['dropdown_options_group'] = $request['dropdown_option_group'][$key];
							$dropdown_options[$key_in]['dropdown_serial_id'] = $last_id;
							unset($dropdown_options[$key_in]['dropdown_options_serial_id']);
						}
						// echo '<pre>'; 
						// print_r($dropdown_options);
						$save_dropdown_options = $this->model_dropdown_options_class::insert($dropdown_options);
					} 
				}
			}

			$request[$this->table_module.'_custom_fields_options'] = $data_dropdown['dropdown_name'];

			# 3. insert into tbl %-tbl-%_custom_fields
			$string_maps = $this->library_module_values_maps_free_use($company_id);
			if ( $string_maps == '' )
			{
				return false;
			}
			$request[$this->table_module.'_custom_values_maps']	= $string_maps;
		}
		else{
			// for multiple option
			# 1. delete dropdown_option 
			$delete_list_options = $this->model_dropdown_options_class::where('dropdown_serial_id', '=', $dropdown_serial_id)->delete();

			# 2. insert new dropdown_option
			foreach ($request['dropdown_option_group'] as $key => $value) 
			{

				if ($request['radio'][$key] == 'new') 
				{
					// New dropdown
					foreach ($request['options_new'][$key] as $key2 => $value2) 
					{
						if ( !isEmpty($value2) ) 
						{
							$dropdown_options_['dropdown_serial_id'] 		 = $dropdown_serial_id;
							$dropdown_options_['dropdown_options_value'] = $value2;
							$dropdown_options_['dropdown_options_group'] = $request['dropdown_option_group'][$key];
							$dropdown_options_['dropdown_options_label'] = $value2;
							
							$save_dropdown_options = $this->model_dropdown_options_class::insert($dropdown_options_);
						}
					}
				} else {
					// Choose Dropdown 
					$get_dropdown = $this->model_dropdown_class::select('dropdown_serial_id')
																->where('dropdown_name', '=', $request['options_choose'][$key])
																// ->where('company_id', '=', $company_id)
																->first();

					if (countCustom($get_dropdown) > 0) 
					{
						$dropdown_options = $this->model_dropdown_options_class::where('dropdown_serial_id', '=', $get_dropdown['dropdown_serial_id'])
						->get()->toArray();

						foreach ($dropdown_options as $key_in => $value_in) 
						{
							$dropdown_options[$key_in]['dropdown_options_group'] = $request['dropdown_option_group'][$key];
							$dropdown_options[$key_in]['dropdown_serial_id'] = $dropdown_serial_id;
							unset($dropdown_options[$key_in]['dropdown_options_serial_id']);
						}
						
						$save_dropdown_options = $this->model_dropdown_options_class::insert($dropdown_options);
					} 
				}
			}
		}

		// fields option
		if (isset($request['fields_options'])){
			$serial_id = $request[$this->table_module.'_custom_fields_serial_id'];
			$type_fields = $request['fields_type'];
			$this->saveFieldsCondition($request,$serial_id,$type_fields,$company_id,$users_id);
		}
		unset($request['radio_multiple']);
		unset($request['options_one']);
		unset($request['options_zero']);
		unset($request['dropdown_serial_id']);
		unset($request["dropdown_option_group"]); // unset from single option
		unset($request["radio"]); // unset from multiple option
		unset($request["options_choose"]); // unset from choose option
		unset($request["options_new"]); // unset from create new option

		
		// for fields option
		unset($request['fields_type']);
		unset($request['fields_type_action']);
		unset($request['fields_value']);
		unset($request['fields_options']);
		
		# 3. update custom_fields
	  $this->model_custom_fields_class::where($this->table_module.'_custom_fields_serial_id', '=', $request["{$this->table_module}_custom_fields_serial_id"])->update($request);

	  $data_update = 	$this->model_custom_fields_class::where($this->table_module.'_custom_fields_serial_id', '=', $request["{$this->table_module}_custom_fields_serial_id"])->first();

    return $data_update;
	}

	# Created By Prihan Firmanullah
  # 12-11-2019
  # For Update Custom Fields Leads
	public function UpdateCustomFields($request, $company_id=0, $users_id=0)
	{
		$original_data = $request; // Set original data from request controllers
		// PROCCESS DATA (REQUEST) FOR INSERT INTO TABLE_MODULE CUSTOM FIELDS
		$result = '';
		$standard_name1 = preg_replace('/[^\p{L}\p{N}\s]/u', '', $original_data[$this->table_module.'_custom_fields_label']);
		$standard_name1 = str_replace(' ', '_', $standard_name1);
		$standard_name1 = $this->table_module."_".strtolower($standard_name1);
		// check if same name ($standard_name)
		$standard_name = $this->CheckName($standard_name1, $company_id);

		if (!empty($request[$this->table_module.'_custom_fields_validation']))
		{
			foreach ($request[$this->table_module.'_custom_fields_validation'] as $key => $value)
			{
				if(countCustom($request[$this->table_module.'_custom_fields_validation']) > 1)
				{
					$validation = "";
					$validation .= $value;

					$result = $result."$validation|";
				}else{
					$result = $value;
				}
			}
		}
		$request[$this->table_module.'_custom_fields_validation'] 		= $result;
		unset($request["_token"]);

		// for multiple option
		if ( isset($request['radio_multiple']) ) 
		{
			if ( $request['radio_multiple'] == '1' ) 
			{
				$request[$this->table_module.'_custom_fields_options'] = $request['options_one'];
			}
			elseif ( $request['radio_multiple'] == '0' ) 
			{
				$dropdown_serial_id 				= isset($request['dropdown_serial_id']) ? $request['dropdown_serial_id'] :'';
				if (isEmpty($dropdown_serial_id))
				{


					$data_dropdown['dropdown_name'] = date('YmdHis_').strtolower($standard_name1);
					$data_dropdown['company_id'] 		= $company_id;
					$data_dropdown['deleted'] 			= Config('setting.NOT_DELETED');

					$save_dropdown 	= $this->model_dropdown_class::create($data_dropdown);
					$last_id 				= $save_dropdown->dropdown_serial_id;

					foreach ($request['options_zero'] as $key => $value) 
					{
						if ( !isEmpty($value) ) 
						{
							$dropdown_options['dropdown_serial_id'] 		= $last_id;
							$dropdown_options['dropdown_options_value'] = $value;
							$dropdown_options['dropdown_options_label'] = $value;

							$save_dropdown_options = $this->model_dropdown_options_class::create($dropdown_options);
						}
					}

					$request[$this->table_module.'_custom_fields_options'] = $data_dropdown['dropdown_name'];
				}else
				{
					unset($request['options_zero'][0]);

					$delete_list_options = $this->model_dropdown_options_class::where('dropdown_serial_id', '=', $dropdown_serial_id)->delete();

					if(isset($request['options_zero']))
					{
						foreach ($request['options_zero'] as $key => $value) 
						{
							if ( !isEmpty($value) ) 
							{
								$dropdown_options['dropdown_serial_id'] 		= $dropdown_serial_id;
								$dropdown_options['dropdown_options_value'] = $value;
								$dropdown_options['dropdown_options_label'] = $value;

								$save_dropdown_options = $this->model_dropdown_options_class::create($dropdown_options);
							}
						}
					}
				}
			}
		}

		// fields option
		if (isset($request['fields_options'])){
			$serial_id = $request[$this->table_module.'_custom_fields_serial_id'];
			$type_fields = $request['fields_type'];
			$this->saveFieldsCondition($request,$serial_id,$type_fields,$company_id,$users_id);
		}
			unset($request['radio_multiple']);
			unset($request['options_one']);
			unset($request['options_zero']);
			unset($request['dropdown_serial_id']);
		
			unset($request['fields_type']);
			unset($request['fields_type_action']);
			unset($request['fields_value']);
			unset($request['fields_options']);

		// Insert into database core data
	  $this->model_custom_fields_class::where($this->table_module.'_custom_fields_serial_id', '=', $request[$this->table_module.'_custom_fields_serial_id'])
	  																->update($request);

	  $data_update = $this->model_custom_fields_class::where($this->table_module.'_custom_fields_serial_id', '=', $request[$this->table_module.'_custom_fields_serial_id'])->first();

    return $data_update;
	}

	public function checkCoreDropdown($request, $company_id){
        //for create new single option
        $data='';
        if ( isset($request['radio_multiple']) ) 
        {
            
            $standard_name1 = preg_replace('/[^\p{L}\p{N}\s]/u', '', $request[$this->table_module.'_fields_change_label']);
            $standard_name1 = str_replace(' ', '_', $standard_name1);
            $standard_name1 = $this->table_module."_".strtolower($standard_name1);

            if ( $request['radio_multiple'] == '1' ) {
                
                //create new single option
                if(isset($request['create_options'])){
                    $data_dropdown['dropdown_name'] = date('YmdHis_').strtolower($standard_name1);
                    $data_dropdown['company_id']        = $company_id;
                    $data_dropdown['deleted']           = Config('setting.NOT_DELETED');

                    $save_dropdown  = $this->model_dropdown_class::create($data_dropdown);
                    $last_id                = $save_dropdown->dropdown_serial_id;
                    
                    foreach ($request['options_zero'] as $key => $value) 
                    {
                        if ( !isEmpty($value) ) 
                        {
                            $dropdown_options['dropdown_serial_id']     = $last_id;
                            $dropdown_options['dropdown_options_value'] = $value;
                            $dropdown_options['dropdown_options_label'] = $value;

                            $save_dropdown_options = $this->model_dropdown_options_class::create($dropdown_options);
                        }
                        
                    }
                    
                    $data=$data_dropdown['dropdown_name'];
                }else{
					
					$data = $request['options_one'];
				}
                
            }elseif ( $request['radio_multiple'] == '0' ) 
            {
                $dropdown_serial_id                 = isset($request['dropdown_serial_id']) ? $request['dropdown_serial_id'] :'';
                if (isEmpty($dropdown_serial_id))
                {
                    $data_dropdown['dropdown_name'] = date('YmdHis_').strtolower($standard_name1);
                    $data_dropdown['company_id']        = $company_id;
                    $data_dropdown['deleted']           = Config('setting.NOT_DELETED');

                    $save_dropdown  = $this->model_dropdown_class::create($data_dropdown);
                    $last_id                = $save_dropdown->dropdown_serial_id;

                    foreach ($request['options_zero'] as $key => $value) 
                    {
                        if ( !isEmpty($value) ) 
                        {
                            $dropdown_options['dropdown_serial_id']         = $last_id;
                            $dropdown_options['dropdown_options_value'] = $value;
                            $dropdown_options['dropdown_options_label'] = $value;

                            $save_dropdown_options = $this->model_dropdown_options_class::create($dropdown_options);
                        }
                    }
                    $data = $data_dropdown['dropdown_name'];
                }else
                {
                    unset($request['options_zero'][0]);

                    $delete_list_options = $this->model_dropdown_options_class::where('dropdown_serial_id', '=', $dropdown_serial_id)->delete();

                    if(isset($request['options_zero']))
                    {
                        foreach ($request['options_zero'] as $key => $value) 
                        {
                            if ( !isEmpty($value) ) 
                            {
                                $dropdown_options['dropdown_serial_id']         = $dropdown_serial_id;
                                $dropdown_options['dropdown_options_value'] = $value;
                                $dropdown_options['dropdown_options_label'] = $value;
                                
                                $save_dropdown_options = $this->model_dropdown_options_class::create($dropdown_options);
                            }
                        }
                    }
                }
            }   
        }   
            unset($request['radio_multiple']);
            unset($request['options_one']);
            unset($request['options_zero']);
            unset($request['dropdown_serial_id']);      
        
        return $data;
    }


	public function UpdateCoreFieldsGroupOp($request, $company_id)
    {
        $hasil = '';
        
        $dropdown_serial_id = isset($request['dropdown_serial_id']) ? $request['dropdown_serial_id'] : '';
        unset($request["_token"]);
        
        if(isEmpty($dropdown_serial_id))
        {
            $standard_name1 = preg_replace('/[^\p{L}\p{N}\s]/u', '', $request[$this->table_module."_fields_change_label"]);
            $standard_name1 = str_replace(' ', '_', $standard_name1);
            $standard_name1 = $this->table_module."_".strtolower($standard_name1);
            $standard_name = $this->CheckName($standard_name1, $company_id);

            $data_dropdown['dropdown_name']         = date('YmdHis_').strtolower($standard_name1);
            $data_dropdown['company_id']                = $company_id;
            $data_dropdown['dropdown_group']        = 1;
            $data_dropdown['deleted']                   = Config('setting.NOT_DELETED');

            $save_dropdown  = $this->model_dropdown_class::create($data_dropdown);
            $last_id                = $save_dropdown->dropdown_serial_id;

            # 2. insert into tbl dropdown_options
            foreach ($request['dropdown_option_group'] as $key => $value) 
            {

                if ($request['radio'][$key] == 'new') 
                {
                    // New dropdown
                    foreach ($request['options_new'][$key] as $key2 => $value2) 
                    {
                        if ( !isEmpty($value2) ) 
                        {
                            $dropdown_options_['dropdown_serial_id']         = $last_id;
                            $dropdown_options_['dropdown_options_value'] = $value2;
                            $dropdown_options_['dropdown_options_group'] = $request['dropdown_option_group'][$key];
                            $dropdown_options_['dropdown_options_label'] = $value2;
                            // print_r($dropdown_options_);
                            $save_dropdown_options = $this->model_dropdown_options_class::insert($dropdown_options_);
                        }
                    }
                } else {
                    // Choose Dropdown 
                    $dropdown_serial_id = $this->model_dropdown_class::select('dropdown_serial_id')
                                                                ->where('dropdown_name', '=', $request['options_choose'][$key])
                                                                ->where('company_id', '=', $company_id)
                                                                ->first();
                    if (!isEmpty($dropdown_serial_id)) 
                    {
                        $dropdown_serial_id = $dropdown_serial_id['dropdown_serial_id'];
                        $dropdown_options = $this->model_dropdown_options_class::where('dropdown_serial_id', '=', $dropdown_serial_id)
                        ->get()->toArray();

                        foreach ($dropdown_options as $key_in => $value_in) 
                        {
                            $dropdown_options[$key_in]['dropdown_options_group'] = $request['dropdown_option_group'][$key];
                            $dropdown_options[$key_in]['dropdown_serial_id'] = $last_id;
                            unset($dropdown_options[$key_in]['dropdown_options_serial_id']);
                        }
                        // echo '<pre>'; 
                        // print_r($dropdown_options);
                        $save_dropdown_options = $this->model_dropdown_options_class::insert($dropdown_options);
                    } 
                }
            }

            $hasil = $data_dropdown['dropdown_name'];

        }
        else{
            // for multiple option
            # 1. delete dropdown_option 
            $delete_list_options = $this->model_dropdown_options_class::where('dropdown_serial_id', '=', $dropdown_serial_id)->delete();

            # 2. insert new dropdown_option
            foreach ($request['dropdown_option_group'] as $key => $value) 
            {

                if ($request['radio'][$key] == 'new') 
                {
                    // New dropdown
                    foreach ($request['options_new'][$key] as $key2 => $value2) 
                    {
                        if ( !isEmpty($value2) ) 
                        {
                            $dropdown_options_['dropdown_serial_id']         = $dropdown_serial_id;
                            $dropdown_options_['dropdown_options_value'] = $value2;
                            $dropdown_options_['dropdown_options_group'] = $request['dropdown_option_group'][$key];
                            $dropdown_options_['dropdown_options_label'] = $value2;
                            
                            $save_dropdown_options = $this->model_dropdown_options_class::insert($dropdown_options_);
                        }
                    }
                } else {
                    // Choose Dropdown 
                    $get_dropdown = $this->model_dropdown_class::select('dropdown_serial_id')
                                                                ->where('dropdown_name', '=', $request['options_choose'][$key])
                                                                // ->where('company_id', '=', $company_id)
                                                                ->first();

                    if (!isEmpty($get_dropdown)) 
                    {
                        $dropdown_options = $this->model_dropdown_options_class::where('dropdown_serial_id', '=', $get_dropdown['dropdown_serial_id'])
                        ->get()->toArray();

                        foreach ($dropdown_options as $key_in => $value_in) 
                        {
                            $dropdown_options[$key_in]['dropdown_options_group'] = $request['dropdown_option_group'][$key];
                            $dropdown_options[$key_in]['dropdown_serial_id'] = $dropdown_serial_id;
                            unset($dropdown_options[$key_in]['dropdown_options_serial_id']);
                        }
                        
                        $save_dropdown_options = $this->model_dropdown_options_class::insert($dropdown_options);
                    } 
                }
            }
        }   

        return $hasil;
    }


	# Created By Prihan Firmanullah
  # 12-11-2019
  # For Update Core Fields
	public function UpdateCoreFields($request, $company_id , $users_id)
	{
		$original_data = $request; // Set original data from request controllers

		// PROCCESS DATA (REQUEST) FOR INSERT INTO TABLE_MODULE CUSTOM FIELDS
		$result = '';
		if (!empty($request["{$this->table_module}_fields_change_validation"]))
		{
			if (is_array($request["{$this->table_module}_fields_change_validation"])) 
			{
				foreach ($request["{$this->table_module}_fields_change_validation"] as $key => $value) 
				{
					if(countCustom($request["{$this->table_module}_fields_change_validation"]) > 1)
					{
						$validation = "";
						$validation .= $value;

						$result = $result."$validation|";
					}
          else
          {
						$result = $value;
					}
				}
			}
			else
			{
				$result = $request["{$this->table_module}_fields_change_validation"];
			}

			// PROCCESS DATA (REQUEST) FOR INSERT INTO TABLE_MODULE CUSTOM FIELDS
		}
		$request["{$this->table_module}_fields_change_validation"] 		= $result;
		$request["company_id"] 		= $company_id;
		unset($request["_token"]);

		if ( empty($request[$this->table_module.'_fields_change_serial_id']) )
		{
			if ( isset($request['radio_multiple']) )
            {
                $request[$this->table_module.'_fields_change_options'] = $this->checkCoreDropdown($request, $company_id);
            }
            if ( isset($request['dropdown_option_group']) ) 
            {
                $request[$this->table_module.'_fields_change_options'] = $this->UpdateCoreFieldsGroupOp($request, $company_id); //update post data
                
            }

      // Case change field status (Core)      
      if (isset($request[$this->table_module.'_fields_change_input_type'])
           && $request[$this->table_module.'_fields_change_input_type'] == 'singleoption'
             && !isset($request[$this->table_module.'_fields_change_options'])) {
          
          // get field change options from core fields
          $get_options = $this->model_fields_class::select($this->table_module."_fields_options")
                                                                                      ->where($this->table_module."_fields_serial_id", $request[$this->table_module.'_fields_serial_id'])
                                                                                      ->first();

          if (countCustom($get_options) > 0) {
              $request[$this->table_module.'_fields_change_options'] = $get_options[$this->table_module.'_fields_options'];
          }
      }

			// If empty - Insert
			$data_update = $this->model_fields_change_class::create($request);
		}
    else
    {
			// If not empty - Update
			$update[$this->table_module.'_fields_change_label'] 			= $request[$this->table_module.'_fields_change_label'];
			$update[$this->table_module.'_fields_change_input_type'] 	= isset($request[$this->table_module.'_fields_change_input_type']) ? $request[$this->table_module.'_fields_change_input_type'] : "";
			$update[$this->table_module.'_fields_change_validation'] 	= $request[$this->table_module.'_fields_change_validation'];
			$update[$this->table_module.'_fields_change_status'] 			= isset($request[$this->table_module.'_fields_change_status'])? $request[$this->table_module.'_fields_change_status']: Config('setting.fields_status_change_active') ;
			if ( isset($request[$this->table_module.'_fields_change_options']) AND !empty($request[$this->table_module.'_fields_change_options']) ) 
			{
				$update[$this->table_module.'_fields_change_options'] 	= isset($request[$this->table_module.'_fields_change_options']) ? $request[$this->table_module.'_fields_change_options'] : '' ;
			}

			// Case change field status (Core)      
      if (isset($request[$this->table_module.'_fields_change_input_type'])
           && $request[$this->table_module.'_fields_change_input_type'] == 'singleoption'
             && !isset($request[$this->table_module.'_fields_change_options'])) {
          
          // get field change options from core fields
          $get_options = $this->model_fields_class::select($this->table_module."_fields_options")
                                                                                      ->where($this->table_module."_fields_serial_id", $request[$this->table_module.'_fields_serial_id'])
                                                                                      ->first();

          if (countCustom($get_options) > 0) {
              $update[$this->table_module.'_fields_change_options'] = $get_options[$this->table_module.'_fields_options'];
          }
      }
      
			if ( isset($request['radio_multiple']) )
            {
                $update[$this->table_module.'_fields_change_options'] = $this->checkCoreDropdown($request, $company_id);        
            }
            if ( isset($request['dropdown_option_group']) ) 
            {
                $update[$this->table_module.'_fields_change_options'] = $this->UpdateCoreFieldsGroupOp($request, $company_id); //update post data
                
            }
			$update[$this->table_module.'_fields_change_quick'] 			= isset($request[$this->table_module.'_fields_change_quick']) ? $request[$this->table_module.'_fields_change_quick'] : '' ;
			
			$update[$this->table_module.'_fields_change_readonly'] 			= isset($request[$this->table_module.'_fields_change_readonly']) ? $request[$this->table_module.'_fields_change_readonly'] : '' ;
			
			$update[$this->table_module.'_fields_change_default_value'] 			= isset($request[$this->table_module.'_fields_change_default_value']) ? $request[$this->table_module.'_fields_change_default_value'] : '' ;

			// fields option
			if (isset($request['fields_options'])){
					$serial_id = $request[$this->table_module.'_fields_serial_id'];
					$type_fields = $request['fields_type'];
					$this->saveFieldsCondition($request,$serial_id,$type_fields,$company_id,$users_id);
			}

			$this->model_fields_change_class::where($this->table_module.'_fields_change_serial_id', '=', $request[$this->table_module.'_fields_change_serial_id'])
																			->update($update);

			$data_update = $this->model_fields_change_class::where($this->table_module.'_fields_change_serial_id', '=', $request[$this->table_module.'_fields_change_serial_id'])
																			->first();

		}

    return $data_update;
	}

	# Created By Prihan Firmanullah
  # 12-11-2019
  # For Delete Custom Fields
	public function DeleteOneCustomData($serial_id='', $company_id=0)
	{
		$update_data 	= array();
		$query 				= false;
		// Query for get module custom values maps
		$query_fields = $this->model_custom_fields_class::where($this->table_module.'_custom_fields_serial_id', '=', $serial_id)
																		->where('company_id', '=', $company_id)
																		->first();

		// delete fields option condition
		$this->model_fields_option_class::where($this->table_module.'_fields_serial_id' , '=' , $serial_id)
							->where($this->table_module.'_fields_type' , '=' , 1)
							->where('company_id' , '=' , $company_id)
							->delete();

		if ( countCustom($query_fields) > 0 )
		{
			$update_data = $query_fields;
			$update_blank[$query_fields[$this->table_module.'_custom_values_maps']] = '';
			// Blank value module values maps column all
			$this->model_custom_values__class::where('company_id', '=', $company_id)->update($update_blank); 

			// Query for delete custom fields
			$query = $this->model_custom_fields_class::where($this->table_module.'_custom_fields_serial_id', '=', $serial_id)
																			->where('company_id', '=', $company_id)
																			->delete();
		}

		return $update_data;
	}

	//GET TEAMS NAME BASED ON OWNER
	# Created By Prihan Firmanullah
  # 12-11-2019
  # For Teams Owner On List data
	public function getTeamsNameByOwner($data=array(), $company_id=0)
	{
		$teams_name = array();
		$result = array();
		
		if(countCustom($data) > 0)
		{
			$i = 0;
			if (countCustom($data['data'])) {
				foreach($data['data'] as $key => $val)
				{
					$cek_teams = array();

					if(isset($val['owner_teams_name']))
					{
						if($val['owner_teams_name'] !== '')
						{
							$data['data'][$key]['owner_teams_name'] = explode('|', $val['owner_teams_name']);
						}
					}
					// if(isset($val['owner_id']))
					// {
					// 	$cek_teams = $this->model_teams_class::select('users_teams.teams_name')
					// 																		->leftJoin('users_teams_map as utm', 'utm.teams_serial_id', '=', 'users_teams.teams_serial_id')
					// 																		->where('users_teams.company_id' , '=', $company_id)
					// 																		->where('users_teams.deleted', '=', config('setting.NOT_DELETED'))
					// 																		->where('utm.users_id', '=', $val['owner_id'])
					// 																		->get();

																		
					// 	if(countCustom($cek_teams) > 0)
					// 	{
					// 		$cek_teams = $cek_teams->toArray();
					// 		$total_teams = countCustom($cek_teams);
					// 		$teams_name = array();
					// 		foreach($cek_teams as $key_teams => $val_teams)
					// 		{
					// 			$data['data'][$key]['owner_teams_name'][] = $val_teams['teams_name'];
					// 		}
					// 	}
					// 	else
					// 	{
					// 		$data['data'][$key]['owner_teams_name'] = array();
					// 	}
					// }

					// $i++;
				}
			}

		}

		return json_decode(json_encode($data), True);;
	}
	// END GET TEAMS NAME BASED ON OWNER

	# Created By Prihan Firmanullah
  # 12-11-2019
  # For Teams Name By Activity
	public function getTeamsNameByActivity($data=array(), $company_id=0)
	{	
		$teams_name = array();
		$result = array();
		$last_id = '';
		$sys = new sys();

		if(countCustom($data) > 0)
		{
			$i = 0;
			if (countCustom($data['data'])) 
			{
				foreach($data['data'] as $key => $val)
				{
					if(isset($val[$this->table_module.'_last_id']))
					{
						$activity_subject 	= '';
						$activity_last_name = '';
						$activity_last_id 	= '';
						$activity_module 	= '';
						$activity_date_created = '';

						$explode  = explode('!/^!^/!', $val[$this->table_module.'_last_id']);
						if (countCustom($explode) > 0) 
						{
							$activity_subject 	= $explode[0];
							$activity_last_name = $explode[1];
							$activity_last_id 	= $explode[2];
							$activity_date_created = $sys->set_datetime($explode[3]);
							$activity_module 		= $explode[4];
						}

						$activity_owner_teams = $this->model_teams_class::select('users_teams.teams_name')
																							->leftJoin('users_teams_map as utm', 'utm.teams_serial_id', '=', 'users_teams.teams_serial_id')
																							->where('users_teams.company_id' , '=', $company_id)
																							->where('users_teams.deleted', '=', config('setting.NOT_DELETED'))
																							->where('utm.users_id', '=', $activity_last_id)
																							->get();
	
						$teams_name = array();
						if(countCustom($activity_owner_teams) > 0)
						{
							$activity_owner_teams = $activity_owner_teams->toArray();
							$total_teams = countCustom($activity_owner_teams);
							
							foreach($activity_owner_teams as $key_teams => $val_teams)
							{
								$teams_name[] = $val_teams['teams_name'];
							}
						}

						$data['data'][$key][$this->table_module.'_last_id'] = array('activity_subject' 	=> $activity_subject, 
																				'activity_last_name' => $activity_last_name,
																				'acticity_last_id'  	=> $activity_last_id,
																				'activity_module' 		=> $activity_module,
																				'activity_teams' 		=> $teams_name,
																				'activity_date_created' => $activity_date_created
																			);
					}

					$i++;
				}
			}

		}

		return $data;
	}

	# Created By Prihan Firmanullah
  # 12-11-2019
  # For Log Update From Ajax
	public function LogUpdateAjax($data, $data_update)
	{
			// define variable
			$core 	= array();
			$custom = array();
			$result = '';

			foreach ($data as $key => $value) {
				foreach ($data_update as $key2 => $value2) {
					if ( $key == $key2 ) 
					{
						if($value != $data_update[$key] )
						{
							$form = $this->GetCoreFields();

							foreach ($form as $key_form => $value_form) 
							{
								if ($value_form[$this->table_module.'_fields_name'] == $key2)
								{
									if ($value_form[$this->table_module.'_fields_input_type'] == 'datetime') 
									{
										$value 	= date('d-m-Y H:i', strtotime($value));
										$value2 = date('d-m-Y H:i', strtotime($value2));
										if ( $value != $value2 ) 
										{
											$core[$value_form[$this->table_module.'_fields_serial_id']]['from'] = $value;
											$core[$value_form[$this->table_module.'_fields_serial_id']]['to'] 	= $value2;
										}

									}
									elseif ($value_form[$this->table_module.'_fields_input_type'] == 'date') 
									{
										$value 	= date('d-m-Y', strtotime($value));
										$value2 = date('d-m-Y', strtotime($value2));
										if ( $value != $value2 ) 
										{
											$core[$value_form[$this->table_module.'_fields_serial_id']]['from'] = $value;
											$core[$value_form[$this->table_module.'_fields_serial_id']]['to'] 	= $value2;
										}

									}
									else
									{
										$core[$value_form[$this->table_module.'_fields_serial_id']]['from'] = $value;
										$core[$value_form[$this->table_module.'_fields_serial_id']]['to'] 	= $value2;
									}
									
								}
							}

						}
					}
				}
			}

			if ( !isEmpty($core) ) 
			{
				$result = array(
							'CORE' 		=> $core,
							'CUSTOM' 	=> $custom,
					);

				$result = json_encode($result);
			}

			return $result;
	}

	# Created By Prihan Firmanullah
  # 12-11-2019
  # For List Core Fields
	public function GetCoreFields($company_id=0)
	{
		$data = $this->model_fields_class::all();

		if ( $this->json_mode === TRUE) {
			$data = $data->toJson(); //put listing data in json
		}
		else {
			$data = $data->toArray(); // put listing data in array
		}

		return $data;
	}

	# Created By Prihan Firmanullah
  # 12-11-2019
  # For List Custom Fields
	public function GetCustomFields($company_id)
	{
		// Set custom fields
		$data = $this->model_custom_fields_class::where( $this->table_module.'_custom_fields_status', '=', Config('setting.custom_fields_status_active'))->where( 'company_id', '=', $company_id)->get();

		if ( $this->json_mode === TRUE) {
			$data = $data->toJson(); //put listing data in json
		}
		else {
			$data = $data->toArray(); // put listing data in array
		}

		return $data;
	}

	# Created By Prihan Firmanullah
  # 12-12-2019
  # For Get List Custom Fields By Id
	public function get_custom_fields_by_id($fields_name='', $company_id=0)
	{
		// Define variable
		$result = array();
		$sys 			= new sys();

		// Query first data fields 
		$query = $this->model_custom_fields_class::where($this->table_module.'_custom_fields_name', '=', $fields_name)
																						->where('company_id', '=', $company_id)
																						->first();

		if ( countCustom($query) > 0  ) 
		{
			$result = $query->toArray();
			// This is for get input type, add key 'html'
			$result	= $sys->sys_api_input_type_first($result, $this->table_module);
			
			$result["condition"] = $this->getFieldsCondition($result[$this->table_module.'_custom_fields_serial_id'] , $company_id , 1);
		}

		return $result;
	}


	# Created By Pratama Gilang
  # 9 des 2019
  # For edit data and get value content
  # Output like getForm with content
  public function getDataEdit($data=array(), $company_id=0)
  {
  	$get_form = array();

  	$get_form = $this->GetForm($company_id);

  	foreach ($get_form as $key => $value) 
  	{
  		if (isset($value[$this->table_module.'_custom_fields_name'])) 
  		{
  			$fields_name = substr_replace($value[$this->table_module.'_custom_fields_name'], '', 0, 7);
					if($value[$this->table_module.'_custom_fields_input_type'] == 'regencies')
					{
							$regencies = DB::table('regencies')->where('name', $data['c_'.$fields_name])->first();
							if(countCustom($regencies)>0){
								$get_form[$key]['content'] = [
									"regencies_serial_id" => $regencies->regencies_serial_id,
									"dropdown_options_value" => $regencies->name,
									"dropdown_options_label" => $regencies->name
							];
							}else{
								$get_form[$key]['content'] =[
									"regencies_serial_id" =>0,
									"dropdown_options_value" =>'',
									"dropdown_options_label" =>''
							];;
							}
					}else	if($value[$this->table_module.'_custom_fields_input_type'] == 'districts')
					{
							$districts = DB::table('districts')->where('name', $data['c_'.$fields_name])->first();
							if(countCustom($districts)>0){
							$get_form[$key]['content'] = [
									"districts_serial_id" => $districts->districts_serial_id,
									"dropdown_options_value" => $data['c_'.$fields_name],
									"dropdown_options_label" => $data['c_'.$fields_name]
							];
							}else{
								$get_form[$key]['content'] =[
									"regencies_serial_id" =>0,
									"dropdown_options_value" =>'',
									"dropdown_options_label" =>''
							];;
							}
					}else	if($value[$this->table_module.'_custom_fields_input_type'] == 'provinces')
					{
							$provinces = DB::table('provinces')->where('name', $data['c_'.$fields_name])->first();
							if(countCustom($provinces)>0){
							$get_form[$key]['content'] = [
									"provinces_serial_id" => $provinces->provinces_serial_id,
									"dropdown_options_value" => $data['c_'.$fields_name],
									"dropdown_options_label" => $data['c_'.$fields_name]
							];
							}else{
								$get_form[$key]['content'] =[
									"provinces_serial_id" =>0,
									"dropdown_options_value" =>'',
									"dropdown_options_label" =>''
							];;
							}
					}else	if($value[$this->table_module.'_custom_fields_input_type'] == 'country')
					{
							$country = DB::table('country')->where('name', $data['c_'.$fields_name])->first();
							if(countCustom($country)>0){
							$get_form[$key]['content'] = [
									"country_serial_id" => $country->country_serial_id,
									"dropdown_options_value" => $data['c_'.$fields_name],
									"dropdown_options_label" => $data['c_'.$fields_name]
							];
							}else{
								$get_form[$key]['content'] =[
									"country_serial_id" =>0,
									"dropdown_options_value" =>'',
									"dropdown_options_label" =>''
							];;
							}
					}
					elseif($value[$this->table_module.'_custom_fields_input_type'] == "person")
					{
						if($data[$this->table_module.'_parent_type'] == 'contacts' || $data[$this->table_module.'_parent_type'] == 'org')
						{
							$getcontactname = $this->model_contacts_class::select(DB::raw("CONCAT(COALESCE(contacts_first_name, ''),' ', COALESCE(contacts_last_name, '')) as ContactsName"))
																								->where('contacts_serial_id', '=', $data['c_'.$fields_name])
																								->where( 'company_id', '=', $company_id)
																								->where( 'deleted', '=', Config('setting.NOT_DELETED'))
																								->first();
							if(countCustom($getcontactname) > 0){
								$getcontactname->toArray();
								$get_form[$key]['content']['name'] = $getcontactname['ContactsName'];
								$get_form[$key]['content']['label'] = $getcontactname['ContactsName'];					
								$get_form[$key]['content']['value'] = $data['c_'.$fields_name];
							}
						}
						else
						{
							$getcontactname = $this->model_leads_class::select(DB::raw("CONCAT(COALESCE(leads_first_name, ''),' ', COALESCE(leads_last_name, '')) as ContactsName"))
																								->where('leads_serial_id', '=', $data['c_'.$fields_name])
																								->where( 'company_id', '=', $company_id)
																								->where( 'deleted', '=', Config('setting.NOT_DELETED'))
																								->first();									
							if(countCustom($getcontactname) > 0){
								$getcontactname->toArray();
								$get_form[$key]['content']['name'] = $getcontactname['ContactsName'];
								$get_form[$key]['content']['label'] = $getcontactname['ContactsName'];					
								$get_form[$key]['content']['value'] = $data['c_'.$fields_name];
							}
						}
					}
					elseif($value[$this->table_module.'_custom_fields_input_type'] == "leads")
					{
							$getleadname = $this->model_leads_class::select(DB::raw("CONCAT(COALESCE(leads_first_name, ''),' ', COALESCE(leads_last_name, '')) as LeadsName"))
																								->where('leads_serial_id', '=', $data['c_'.$fields_name])
																								->where( 'company_id', '=', $company_id)
																								->where( 'deleted', '=', Config('setting.NOT_DELETED'))
																								->first();
							if(countCustom($getleadname) > 0){
								$getleadname->toArray();
								$get_form[$key]['content']['name'] = $getleadname['LeadsName'];
								$get_form[$key]['content']['label'] = $getleadname['LeadsName'];					
								$get_form[$key]['content']['value'] = $data['c_'.$fields_name];
							}
					}
					elseif($value[$this->table_module.'_custom_fields_input_type'] == "deals")
					{
							$getdealname = $this->model_deals_class::select(DB::raw("CONCAT(COALESCE(deals_name, '')) as DealsName"))
																								->where('deals_serial_id', '=', $data['c_'.$fields_name])
																								->where( 'company_id', '=', $company_id)
																								->where( 'deleted', '=', Config('setting.NOT_DELETED'))
																								->first();
							if(countCustom($getdealname) > 0){
								$getdealname->toArray();
								$get_form[$key]['content']['name'] = $getdealname['DealsName'];
								$get_form[$key]['content']['label'] = $getdealname['DealsName'];					
								$get_form[$key]['content']['value'] = $data['c_'.$fields_name];
							}
					}
					elseif($value[$this->table_module.'_custom_fields_input_type'] == "organization")
					{
							$getorganizationname = $this->model_org_class::select(DB::raw("org_name as OrganizationName"))
																								->where('org_serial_id', '=', $data['c_'.$fields_name])
																								->where( 'company_id', '=', $company_id)
																								->where( 'deleted', '=', Config('setting.NOT_DELETED'))
																								->first();
							if(countCustom($getorganizationname) > 0){
								$getorganizationname->toArray();
								$get_form[$key]['content']['name'] = $getorganizationname['OrganizationName'];
								$get_form[$key]['content']['label'] = $getorganizationname['OrganizationName'];					
								$get_form[$key]['content']['value'] = $data['c_'.$fields_name];
							}
					}
					elseif($value[$this->table_module.'_custom_fields_input_type'] == "teams")
					{
							$getteamname = $this->model_teams_class::select(DB::raw("teams_name as TeamsName"))
																								->where('teams_serial_id', '=', $data['c_'.$fields_name])
																								->where( 'company_id', '=', $company_id)
																								->where( 'deleted', '=', Config('setting.NOT_DELETED'))
																								->first();
							if(countCustom($getteamname) > 0){
								$getteamname->toArray();
								$get_form[$key]['content']['name'] = $getteamname['TeamsName'];
								$get_form[$key]['content']['label'] = $getteamname['TeamsName'];					
								$get_form[$key]['content']['value'] = $data['c_'.$fields_name];
							}
					}
					elseif($value[$this->table_module.'_custom_fields_input_type'] == "users")
					{
							$getusername = $this->model_users_class::select(DB::raw("name as UsersName"))
																								->where('id', '=', $data['c_'.$fields_name])
																								->first();
							if(countCustom($getusername) > 0){
								$getusername->toArray();
								$get_form[$key]['content']['name'] = $getusername['UsersName'];
								$get_form[$key]['content']['label'] = $getusername['UsersName'];					
								$get_form[$key]['content']['value'] = $data['c_'.$fields_name];
							}
					}
					else
					{
							$get_form[$key]['content'] = !empty($data['c_'.$fields_name]) ? $data['c_'.$fields_name] : '';  
					}
  		}
  		else
  		{
  			if ($value[$this->table_module.'_fields_name'] == $this->table_module.'_owner') 
  			{
  				$get_form[$key]['content'] = $data[$this->table_module.'_owner_id'];
  			}
  			elseif($value[$this->table_module.'_fields_name'] == $this->table_module.'_parent_id')
  			{
  				$get_form[$key]['content']['collect'] = explode('~', $data[$this->table_module.'_parent_id']);
  				$get_form[$key]['content']['name'] = $data[$this->table_module.'_parent_id'];
  				$get_form[$key]['content']['label'] = $data[$this->table_module.'_parent_id'];
  				$get_form[$key]['content']['value'] = $data[$this->table_module.'_parent_id_id'];
  				$get_form[$key]['content']['parent_type'] = $data[$this->table_module.'_parent_type'];
  			}
  			else
  			{
  				$get_form[$key]['content'] = !empty($data[$value[$this->table_module.'_fields_name']]) ? $data[$value[$this->table_module.'_fields_name']] : '';

  			}
  		}
  	}

  	return $get_form;
  }

  # Created By Prihan Firmanullah
  # 2019-12-20
  # For Save Customize Form
	public function GetCustomValuesEditable($serial_id=0, $fields_id=0, $company_id=0)
	{
		// Define variable
		$result = array();
		$sys 			= new sys();

		$select = $sys->library_values_maps_by_name($fields_id, $this->table_module, $company_id);

		$query = $this->model_custom_values__class::select(DB::raw($select.' as text'))
																						->where($this->table_module.'_serial_id', '=', $serial_id)
																						->where('company_id', '=', $company_id)
																						->first();

		if ( countCustom($query) > 0 ) 
		{
			$result = $query->toArray();
		}

		// Result : 1 or 2 or 3 or etc.
		return $result;
	}

	# Created By Prihan Firmanullah
  # 2019-12-20
  # For Save Customize Form
	public function custom_values_origin($serial_id=0, $fields_id=0, $company_id=0)
	{
		// Define variable
		$result = array();
		$sys 			= new sys();


		$select = $sys->library_values_maps_by_name($fields_id, $this->table_module, $company_id);

		$query = $this->model_custom_values__class::select(DB::raw($select.' as c_'.$fields_id))
																							->where($this->table_module.'_serial_id', '=', $serial_id)
																							->where('company_id', '=', $company_id)
																							->first();

		if ( countCustom($query) > 0 )
		{
			$result = $query->toArray();
		}

		return $result;
	}

	# Created By Prihan Firmanullah
  # 2019-12-20
  # For Save Customize Form
	public function get_uuid_by_serial_id($serial_id=0, $company_id=0)
	{
		// Define variable
		$result = '';

		$query = $this->model_class::select(DB::raw($this->table_module.'_uuid as uuid'))
															 ->where($this->table_module.'_serial_id', '=', $serial_id)
															 ->where('company_id', '=', $company_id)
															 ->first();
		if ( countCustom($query) > 0 )
		{
			$result = $query['uuid'];
		}

		return $result;
	}	

	# Created By Prihan Firmanullah
  # 2019-12-20
  # For Save quick update custome
	public function fieldsUpdateCustom($request=array(), $company_id=0, $users_id = '', $serial_id=0)
	{	
		$sys = new sys();

		$get_fields 	= $this->get_custom_fields_by_id($request[$this->table_module.'_custom_fields_name'], $company_id); 
		// Get first data fields, by fields_name

		$field_id 		= isset($get_fields[$this->table_module.'_custom_fields_serial_id']) ? $get_fields[$this->table_module.'_custom_fields_serial_id'] : '' ;
		
		//define for update data
		$module_fields_name									= isset($request[$this->table_module.'_custom_fields_name']) ? $request[$this->table_module.'_custom_fields_name'] : '' ;

    $custom_money_fields_info           = array(); // Container for custom money fields info

    $validation_fields                  = $this->GetForm($company_id); // Get core fields & custom fields

		$values_maps = $sys->library_values_maps_by_name($module_fields_name, $this->table_module, $company_id);

		if ( isset($module_fields_name) && isset($request[$module_fields_name]) ) // Check multipleoption isset? 
		{
			if ( is_array($request[$module_fields_name]) ) // This is multipleoption condition
			{
				$updateField[$values_maps]		= json_encode($request[$module_fields_name]);
			}
			else // This is != multipleoption
			{
				$value = $request[$module_fields_name];

				if(strpos($value,'|') ==  TRUE)
				{
					#For split teks Before result exp. 93234-23453245-5fdsg|Barantum
					$value_text 		= substr($value, strpos($value,'|')+1); //get value teks exp. org : Barantum
					$value_related_uuid = substr($value, 0, strpos($value,'|')); // get value uuid. org: 324-235df-kffgfd
					#END

					$updateField[$values_maps] 			= $value_text;
					// $updateField[$this->table_module."_custom_values_related_uuid"] 	= $value_related_uuid;
					
				}
				else
				{
					$updateField[$values_maps] 			= $value;
					// $updateField[$this->table_module."_custom_values_related_uuid"] 	= '0';
					
				}
			}
		}
		else // This is multipleoption, condition multipleoption empty
		{
				$updateField[$values_maps]		= '';
		}

		// UPDATE
		// process update calls_custom_values
		// Check whether you have booked custom values ​​or not
		$check_booked = $this->model_custom_values__class::where($this->table_module.'_serial_id', '=', $serial_id)
																								->where('company_id', '=', $company_id)
																								->first();
		if ( countCustom($check_booked) == 0 )
		{
			$updateField[$this->table_module.'_serial_id']	= $serial_id;

			$updateField['company_id']	= $company_id;
			$this->model_custom_values__class::create($updateField); // Booked custom values
		}
		else
		{
			$update 	= $this->model_custom_values__class::where($this->table_module.'_serial_id', '=', $serial_id)
																									->where('company_id', '=', $company_id)
																									->update($updateField);
		}

		// Update modified date & by
		$update_modified['date_modified'] = date('Y-m-d H:i:s');
		$update_modified['modified_by'] 	= $users_id;
		$this->model_class::where($this->table_module.'_serial_id', '=', $serial_id)->update($update_modified);
	
		//get data value maps for custom
		$value_maps = 	$this->model_custom_fields_class::select($this->table_module.'_custom_values_maps')->where($this->table_module.'_custom_fields_name', '=', $get_fields[$this->table_module.'_custom_fields_name'])->where('company_id', '=', $company_id)->first();

		$array_values_maps = $value_maps->toArray();

		foreach ($array_values_maps as $value) {
			//get data quick update custom
			$data_update = 	$this->model_custom_values__class::select($value)->where($this->table_module.'_serial_id', '=', $serial_id)->where('company_id', '=', $company_id)->first();
		}

		elasticAddData($serial_id, $company_id, $this->table_module);
		return $data_update;
	}

	# Created By Prihan Firmanullah
  # 2019-12-20
  # For Save Customize Form
	public function ColumnsSave($request, $company_id, $users_id)
	{
		unset($request['_token']);
		// 1. delete to module_view_fields where module_view_serial_id
		$data 	= $this->model_view_fields_class::where('company_id', '=', $company_id)->where('users_id', '=', $users_id)->first();

		if (!empty($data)) 
		{
			$this->model_view_fields_class::where('company_id', '=', $company_id)->where('users_id', '=', $users_id)->delete();
		}

		// 2. insert to module_view_fields 
		$sort = 1;
		if (isset($request['fields_serial_id']))
		{
			foreach ($request['fields_serial_id'] as $key => $value)
			{
				$fields_serial_id 		= $value;
				$view_fields_type 		= $request['fields_type'][$key];
				$fields_view_sorting 	= $sort;

				// $input[$this->table_module.'_view_serial_id']		= $module_view_serial_id;
				$input[$this->table_module.'_fields_serial_id']			= $fields_serial_id;
				$input[$this->table_module.'_view_fields_type']			= $view_fields_type;
				$input[$this->table_module.'_fields_view_sorting']	= $sort;
				$input['users_id']		= $users_id;
				$input['company_id']	= $company_id;
				$process_save 	= $this->model_view_fields_class::create($input);
				$sort ++;
			}
		}

		return TRUE;
	}
	
	# Created By Prihan Firmanullah
  # 12-17-2019
  # For Get List Core And Custom
  public function GetListManageFields($company_id=0 ,$agents_new_leads='')
	{
		$sys = new sys();

		$core_fields = $this->model_fields_class::where($this->table_module.'_fields_name', '!=', 'date_modified')
																						->where($this->table_module.'_fields_name', '!=', 'created_by')
																						->where($this->table_module.'_fields_name', '!=', 'modified_by')
																						->get()->toArray();

		$core_change = $this->model_fields_class::select('b.*')
                           	->leftjoin($this->table_module.'_fields_change as b', 'b.'.$this->table_module.'_fields_serial_id', '=', $this->table_module.'_fields.'.$this->table_module.'_fields_serial_id')
                           	->where('b.company_id', '=', $company_id)
		                        ->get();

		$core_sorting = $this->model_fields_class::select('b.*')
                           	->leftjoin($this->table_module.'_fields_sorting as b', 'b.'.$this->table_module.'_fields_serial_id', '=', $this->table_module.'_fields.'.$this->table_module.'_fields_serial_id')
                           	->where('b.company_id', '=', $company_id)
		                        ->get();

		// For handle duplicate data in fields change & fields sorting
		$temp_core_fields = $core_fields;
		if ( countCustom($core_change) > 0 )
		{
			foreach ($core_change as $key => $value) 
			{
				$key_change = array_search($value[$this->table_module.'_fields_serial_id'], array_column($temp_core_fields, $this->table_module.'_fields_serial_id'));
				if ( $key_change !== false )
				{
					$core_fields[$key_change][$this->table_module.'_fields_change_serial_id'] 		= $value[$this->table_module.'_fields_change_serial_id'];
					$core_fields[$key_change][$this->table_module.'_fields_label'] 				= $value[$this->table_module.'_fields_change_label'];
					$core_fields[$key_change][$this->table_module.'_fields_validation'] 	= $value[$this->table_module.'_fields_change_validation'];
					$core_fields[$key_change][$this->table_module.'_fields_status'] 			= $value[$this->table_module.'_fields_change_status'];
					$core_fields[$key_change][$this->table_module.'_fields_options'] 			= $value[$this->table_module.'_fields_change_options'];
					$core_fields[$key_change][$this->table_module.'_fields_readonly'] 		= $value[$this->table_module.'_fields_change_readonly'];
					$core_fields[$key_change][$this->table_module.'_fields_quick'] 				= $value[$this->table_module.'_fields_change_quick'];
					$core_fields[$key_change][$this->table_module.'_fields_default_value'] = $value[$this->table_module.'_fields_change_default_value'];
					if ( !empty($value[$this->table_module.'_fields_change_input_type']) ) 
					{
						$core_fields[$key_change][$this->table_module.'_fields_input_type']	= $value[$this->table_module.'_fields_change_input_type'];
					}
				}
			}
		}

		// For handle not increment key array, handle problem in array search & array column
		$core_fields = array_values($core_fields);

		if ( countCustom($core_sorting) > 0 )
		{
			foreach ($core_sorting as $key => $value) 
			{
				$key_sorting = array_search($value[$this->table_module.'_fields_serial_id'], array_column($core_fields, $this->table_module.'_fields_serial_id'));
				if ( $key_sorting !== false )
				{
					$core_fields[$key_sorting][$this->table_module.'_fields_sorting'] 						= $value[$this->table_module.'_fields_sorting'];
					$core_fields[$key_sorting][$this->table_module.'_fields_sorting_put_header'] 	= $value[$this->table_module.'_fields_sorting_put_header'];
					$core_fields[$key_sorting][$this->table_module.'_fields_type'] 				= 0;
				}
			}
		}
		else
		{
			foreach ($core_fields as $key => $value) 
			{
					$core_fields[$key][$this->table_module.'_fields_sorting_put_header'] 	= Config('setting.fields_sorting_not_put_header');
					$core_fields[$key][$this->table_module.'_fields_type'] 				= 0;
			}
		}

		foreach ($core_fields as $key => $value) 
		{
			$core_fields[$key][$this->table_module.'_fields_type'] 								= 0;
		}

		$select = $this->table_module.'_custom_fields_serial_id,'.
				$this->table_module.'_custom_fields_name,'.
				$this->table_module.'_custom_fields_label,'.
				$this->table_module.'_custom_fields_data_type,'.
				$this->table_module.'_custom_fields_input_type,'.
				$this->table_module.'_custom_fields_function,'.
				$this->table_module.'_custom_fields_options,'.
				$this->table_module.'_custom_fields_validation,'.
				$this->table_module.'_custom_fields_sorting as '.$this->table_module.'_fields_sorting,'.
				$this->table_module.'_custom_fields_status,'.
				$this->table_module.'_custom_fields_quick,'.
				$this->table_module.'_custom_fields_readonly,'.
				$this->table_module.'_custom_fields_default_value,'.
				$this->table_module.'_custom_fields_put_header';

		$custom_fields = $this->model_custom_fields_class::select(DB::raw($select))
																											->where( 'company_id', '=', $company_id)
																											->get()->toArray();

		foreach ($custom_fields as $key => $value) 
		{
			$custom_fields[$key][$this->table_module.'_custom_fields_name'] = 'custom_'.$custom_fields[$key][$this->table_module.'_custom_fields_name'];
			$custom_fields[$key][$this->table_module.'_fields_type'] = 1;
		}

		$result = array_merge($core_fields, $custom_fields);

		// This is for explode and split validation - required
		$result = $this->GetFormValidation($result);

		$result = $sys->array_sort_custom( $result, $this->table_module.'_fields_sorting', SORT_ASC);
		$result = array_values($result);

		return $result;
	}

	# Created By Prihan Firmanullah
  # 12-17-2019
  # For Get Module Fields Change
	public function moduleFieldsChange($company_id=0)
	{
		$result 			= array();
		$fieldsName 	= array(
										$this->table_module.'_fields.'.$this->table_module."_fields_serial_id", 
										$this->table_module.'_fields.'.$this->table_module."_fields_name", 
										$this->table_module.'_fields.'.$this->table_module."_fields_label", 
										$this->table_module.'_fields.'.$this->table_module."_fields_input_type", 
										$this->table_module.'_fields.'.$this->table_module."_fields_status", 
										$this->table_module.'_fields.'.$this->table_module."_fields_options", 
										$this->table_module.'_fields.'.$this->table_module."_fields_function", 
										'b.'.$this->table_module.'_fields_change_status',
										'b.'.$this->table_module.'_fields_change_options');
		$b = 'b';
		$query 	= $this->model_fields_class::select($fieldsName);
							$query->leftjoin($this->table_module.'_fields_change as '.$b, function($join) use ($b, $company_id)
						    { 
						        $join->on($this->table_module.'_fields.'.$this->table_module.'_fields_serial_id', '=', $b.'.'.$this->table_module.'_fields_serial_id')
						        	->where($b.'.company_id', '=', $company_id);
						    });
					//->where($this->table_module.'_fields.'.$this->table_module.'_fields_status', '=', Config('setting.fields_status_active')); // leads_fields_status ACTIVE
		$contents 	 = $query->get();

		if (countCustom($contents) > 0) 
		{
			$result = $contents->toArray();
			foreach ($result as $key => $value) 
			{
				if ($value[$this->table_module.'_fields_change_options'] != '' ) 
				{
					if ($value[$this->table_module.'_fields_input_type'] == 'singleoption' OR $value[$this->table_module.'_fields_input_type'] == 'multipleoption' || $value[$this->table_module.'_fields_input_type'] == 'groupoption') {
						
						$result[$key][$this->table_module.'_fields_options'] = $this->table_module.'_fields_change_options';
					}
				}
				if ($value[$this->table_module.'_fields_change_status'] === Config('setting.fields_status_change_inactive')  ) 
				{
					unset($result[$key]);
				}

				unset($result[$key][$this->table_module.'_fields_change_options']);
			}
		}
		
		return $result;
	}

	# Created By Prihan Firmanullah
  # 12-17-2019
  # For Get Core Fields Change
	public function moduleFieldsCustom($company_id)
	{
		$result = array();
		$fields = array(
							$this->table_module."_custom_fields_serial_id", 
							$this->table_module."_custom_fields_name", 
							$this->table_module."_custom_fields_label",
							$this->table_module."_custom_fields_input_type",
							$this->table_module."_custom_fields_status",
							$this->table_module."_custom_fields_options",
							$this->table_module."_custom_fields_function",
							);
		$data 	= $this->model_custom_fields_class::select($fields)
																		->where($this->table_module . '_custom_fields_status', '=', Config('setting.custom_fields_status_active'))
																		->where('company_id', '=', $company_id)
																		->get(); //get listing data
		if (countCustom($data) > 0) 
		{
			$result 	= $data->toArray();
		}

		return $result;
	}

	public function filterChecked($view_uuid='', $users_id='', $company_id='', $users_name='')
	{
		$table_module 	= $this->table_module;

		#Proces check and uncheck filter
		// 1.  get meetings_view_serial_id
		$moduleView 	= $this->model_view_class::select($this->table_module.'_view_name', $this->table_module.'_view_serial_id')
																->where($this->table_module.'_view_uuid', '=', $view_uuid)
																->first();
		$module_view_serial_id 	= (countCustom($moduleView) > 0) ? $moduleView[$this->table_module.'_view_serial_id'] : "0";

		// 2. update data
		$data['users_id'] 		= $users_id;
		$data['company_id']		= $company_id;
		$data[$this->table_module.'_view_serial_id']		= $module_view_serial_id;

		$check 	= $this->model_view_checked_class::where('users_id', '=', $users_id)->where('company_id', '=', $company_id)->count();
		if ($check > 0) {
			$moduleViewChecked 	= $this->model_view_checked_class::where('users_id', '=', $users_id)
																	->where('company_id', '=', $company_id)
																	->update($data);
		}else
		{
			$moduleViewChecked = $this->model_view_checked_class::create($data);
		}
		#End process check and uncheck filter
		
		# Process get content in table meetings_view-criteria | Format data for send to function search.
		$content 	= array();
		if ($moduleView[$this->table_module.'_view_name'] == 'You') // if selected filter ex. admin(You)
		{
			$content[$this->table_module.'_owner_opp'] 		= 'contains';
		}
		else
		{
			$checkCriteria 	= $this->model_view_criteria_class::select($this->table_module.'_view_criteria_type')
															->where($table_module.'_view_serial_id', '=', $module_view_serial_id)
															->get();
			if (countCustom($checkCriteria) > 0) 
			{
				$checkCriteria 	= $checkCriteria->toArray();
				foreach ($checkCriteria as $key => $value) 
				{
					$viewCriteriaType 	= $value[$this->table_module.'_view_criteria_type'];
					if ($viewCriteriaType == 1 ) // if fields costum, join to table module_custom_fields
					{
						$qry_custom_field 	= DB::table($table_module.'_custom_fields as a')
																			->select('a.'.$table_module.'_custom_fields_serial_id', 'a.'.$table_module.'_custom_fields_name')
																			->leftjoin($table_module.'_view_fields as b', 'b.'.$table_module.'_fields_serial_id', '=', 'a.'.$table_module.'_custom_fields_serial_id')
																			->where('a.company_id', '=', $company_id)
																			->where('b.users_id', '=', $users_id)
																			->where('b.company_id', '=', $company_id)
																			->orderBy('a.'.$table_module.'_custom_fields_serial_id', 'ASC')
																			->get();
						if (countCustom($qry_custom_field) > 0) 
						{
							$qry_custom_field 	= json_decode(json_encode($qry_custom_field), TRUE);
							foreach ($qry_custom_field as $key => $row) 
							{
								$custom_fields_serial_id 	= $row[$table_module.'_custom_fields_serial_id'];
								$custom_fields_name 			= $row[$table_module.'_custom_fields_name'];
								$criteria_operator 				= "contains";
								$criteria_value 					= "";

								$viewCriteria 	= $this->model_view_criteria_class::select($table_module.'_view_criteria_operator', $table_module.'_view_criteria_value')
																			->where($table_module.'_view_serial_id', '=', $module_view_serial_id)
																			->where($table_module.'_fields_serial_id', '=', $custom_fields_serial_id)
																			->where($this->table_module.'_view_criteria_type', '=', Config('setting.view_criteria_type_custom')) // fields custom
																			->first();
								if (countCustom($viewCriteria) > 0) 
								{
									$criteria_operator 	= $viewCriteria[$table_module.'_view_criteria_operator'];
									$criteria_value 		= $viewCriteria[$table_module.'_view_criteria_value'];
								}

								$content['custom_'.$custom_fields_name.'_opp'] 		= $criteria_operator;
								$content['custom_'.$custom_fields_name] 					= $criteria_value;
							}
						}

					}else // if fields core, join to table module_fields
					{
						$viewCriteria 	= $this->model_view_criteria_class::where($table_module.'_view_serial_id', '=', $module_view_serial_id)
																		->leftjoin($table_module.'_fields AS a2', $table_module.'_view_criteria.'.$table_module.'_fields_serial_id', '=', 'a2.'.$table_module.'_fields_serial_id')
																		->where($this->table_module.'_view_criteria_type', '=', Config('setting.view_criteria_type_core')) // fields core
																		->get();
						if (countCustom($viewCriteria) > 0) 
						{
							$viewCriteria 	= $viewCriteria->toArray();
							foreach ($viewCriteria as $val)
							{			
								// check json or not
								// filter_old : save data with string
								// filter _new : save data with json format
								$checkJson 				= json_decode($val[$table_module.'_view_criteria_value']);
								$criteria_value  	= $val[$table_module.'_view_criteria_value'];
								if ($checkJson == TRUE ) {
									$criteria_value = json_decode($val[$table_module.'_view_criteria_value']);
								}

								$content[$val[$table_module.'_fields_name'].'_opp'] 	= $val[$table_module.'_view_criteria_operator'];
								$content[$val[$table_module.'_fields_name']]					= $criteria_value;
							}
						}
					}
				} // end foreach $checkCriteria
			}
		}

		$content['filter_name'] = $moduleView[$this->table_module.'_view_name'];
		$container 			= $content;
		#End Process get content in table meetings_view-criteria
		
		return $container;
	}

	# Created By Prihan Firmanullah
  # 12-20-2019
  # For Get Core Fields Change
	public function SaveCustomize($request, $company_id)
	{
		foreach ($request['layouts'] as $key => $value) 
		{
			$trim_custom = substr($value, -1, 1); // output c, if c (custom fields)
			
			if ( $trim_custom != 'c' )
			{
				$GetSorting = $this->model_fields_sorting_class::where('company_id', '=', $company_id)
																											->where($this->table_module.'_fields_serial_id', '=', $value)
																											->get();

				if ( countCustom($GetSorting) > 0 ) 
				{
					// For Update
					$sorting[$this->table_module.'_fields_sorting'] 		= $key;

					$UpdateSorting = $this->model_fields_sorting_class::where('company_id', '=', $company_id)
																														->where($this->table_module.'_fields_serial_id', '=', $value)
																														->update($sorting);

				}
				else
				{
					// For Insert
					$sorting['company_id'] 															= $company_id;
					$sorting[$this->table_module.'_fields_serial_id'] 	= $value;
					$sorting[$this->table_module.'_fields_sorting'] 		= $key;

					$SaveSorting = $this->model_fields_sorting_class::create($sorting);
				}
			}
			else
			{
				// For Update Custom
				$value = substr($value, 0, -1);
				
				$sorting_custom[$this->table_module.'_custom_fields_sorting'] = $key;

				$SaveSorting = $this->model_custom_fields_class::where('company_id', '=', $company_id)
																											->where($this->table_module.'_custom_fields_serial_id', '=', $value)
																											->update($sorting_custom);
			}
		}
		
		return true;
	}

	# Created By Prihan Firmanullah
  # 2019-12-20
  # For Save Customize Form
	public function GetDetailAudit($serial_id='', $module='', $company_id='')
	{
		// Define variable
		$a = $b = 0;
		$result = array();

		$log_type = array('create','duplicate','import','update','updateajax','massupdate', 'removetag', 'create_api', 'update_api');

		$data = $this->model_syslog_class::select(DB::raw('syslog.*, users.name as syslog_created_by'))
																		->leftjoin('users', 'users.id', '=', 'syslog.syslog_created_by')
																		->where('syslog_module_name', '=', $module)
																		->where('syslog_data_id', '=', $serial_id)
																		->where('company_id', '=', $company_id)
																		->whereIn('syslog_action_type', $log_type)
																		->orderBy('syslog_serial_id', 'DESC')
																		->get();

		// FORM CORE
		$form_fields = $this->GetCoreFields($company_id); //get core fields from tbl module_fields
		
		if ( countCustom($data) > 0 )
		{
			foreach ($data as $key => $value)
			{
				unset($core);
				unset($custom);
				unset($combine);
				unset($content_all);
				$core 				= array();
				$custom 			= array();
				$combine 			= array();
				$content_all 	= '';

				$decode = json_decode($value['syslog_action'], TRUE);
				if ( $value['syslog_action_type'] == 'create' || $value['syslog_action_type'] == 'duplicate' || $value['syslog_action_type'] == 'import' || $value['syslog_action_type'] == 'create_api')
				{
					$result[$key]['description'] 						= $value['syslog_action'];
					$result[$key]['name'] 									= $value['syslog_created_by'];
					$result[$key]['syslog_date_created'] 		= date('Y-m-d H:i:s', strtotime($value['syslog_date_created']));
					if($value['syslog_action_type'] == 'duplicate')
          {
            $result[$key]['syslog_action_type'] 		= "duplicate";
          }
          else
          {
            $result[$key]['syslog_action_type'] 		= $value['syslog_action_type'] == "create_api" ? "Create API" :"create";
          }
							// Core
							if ( !empty($decode['CORE']) ) 
							{
								foreach ($decode['CORE'] as $key2 => $value2) 
								{
									foreach ($form_fields as $key3 => $value3) 
									{
										if ( $value3[$this->table_module.'_fields_serial_id'] == $key2 ) 
										{
											if ( $value3[$this->table_module.'_fields_name'] == $this->table_module.'_owner' ) 
											{
												$name_owner = $this->GetNameOwnerById($value2);
												$content 		= " &#8594; ".$name_owner."'";
											}else{
												$content 		= " &#8594; '".$value2."'";
											}

											$label 												= $value3[$this->table_module.'_fields_label'];
											$core[$a]['description'] 			= $label.$content;
											$a++;
										}
									}
								}
							}
							// Custom
							if ( !empty($decode['CUSTOM'])) 
							{
								foreach ($decode['CUSTOM'] as $key2 => $value2) 
								{
									// FORM CUSTOM
									$form_custom_fields = $this->model_custom_fields_class::select(DB::raw($this->table_module.'_custom_fields_serial_id, '.$this->table_module.'_custom_fields_label'))
																																				->where($this->table_module.'_custom_fields_serial_id', '=', $key2)
																																				->first();
									
									if ( $form_custom_fields[$this->table_module.'_custom_fields_serial_id'] == $key2 ) 
									{
											$label 																= $form_custom_fields[$this->table_module.'_custom_fields_label'];
											$content 															= " &#8594; '".$value2."'";
											$custom[$b]['description'] 						= $label.$content;
											$b++;
									}
								}
							}
							// Combine core & custom
							$combine = array_merge($core, $custom);
							if ( countCustom($combine) > 0 ) 
							{
								foreach ($combine as $key2 => $value2) 
								{
										$content_all = $content_all." ".$value2['description'].", ";
								}
								$result[$key]['description'] 						= $content_all;
								$result[$key]['name'] 									= $value['syslog_created_by'];
								$result[$key]['syslog_date_created'] 		= date('Y-m-d H:i:s', strtotime($value['syslog_date_created']));
								$result[$key]['syslog_action_type'] 		= "create";
							}
				}



				elseif ( $value['syslog_action_type'] == 'update' || $value['syslog_action_type'] == 'updateajax' || $value['syslog_action_type'] == 'massupdate' || $value['syslog_action_type'] == 'update_api')
				{
					$result[$key]['description'] 						= $value['syslog_action'];
					$result[$key]['name'] 									= $value['syslog_created_by'];
					$result[$key]['syslog_date_created'] 		= date('Y-m-d H:i:s', strtotime($value['syslog_date_created']));
					$result[$key]['syslog_action_type'] 		= $value['syslog_action_type'] == "update_api" ? "Update API" : "update";
							// Core
							if ( !empty($decode['CORE'])) 
							{
								foreach ($decode['CORE'] as $key2 => $value2) 
								{
									foreach ($form_fields as $key3 => $value3) 
									{
										if ( $value3[$this->table_module.'_fields_serial_id'] == $key2 ) 
										{
											if ( $value3[$this->table_module.'_fields_name'] == $this->table_module.'_owner' ) 
											{
												$name_owner_from 	= '-';
												$name_owner_to 		= '-';
												if ( $value2['from'] != 0 AND $value2['from'] != NULL AND $value2['from'] != '' ) 
												{
													$name_owner_from 	= $this->GetNameOwnerById($value2['from']);
												}
												if ( $value2['to'] != 0 AND $value2['to'] != NULL AND $value2['to'] != '' ) 
												{
													$name_owner_to 		= $this->GetNameOwnerById($value2['to']);
												}
												$content 	= " &#8594; '".$name_owner_from."' &#8646; '".$name_owner_to."'";
											}
											else
											{
												$from 		= (isEmpty($value2['from'])) ? '-' : $value2['from'];
												$content 	= " &#8594; '".$from."' &#8646; '".$value2['to']."'";
											}
											$label 		= $value3[$this->table_module.'_fields_label'];
											$core[$a]['description'] 	= $label.$content;
											$a++;
										}
									}
								}
							}
							// Custom
							if ( !empty($decode['CUSTOM'])) 
							{
								foreach ($decode['CUSTOM'] as $key2 => $value2) 
								{
									// FORM CUSTOM
									$form_custom_fields = $this->model_custom_fields_class::select(DB::raw($this->table_module.'_custom_fields_label'))
																																			->where($this->table_module.'_custom_fields_serial_id', '=', $key2)
																																			->first();
									if ( countCustom($form_custom_fields) > 0 ) 
									{
											$label 		= $form_custom_fields[$this->table_module.'_custom_fields_label'];
											$from 		= (isEmpty($value2['from'])) ? '-' : $value2['from'];
											$content 	= " &#8594; '".$from."' &#8646; '".$value2['to']."'";
											$custom[$b]['description'] 						= $label.$content;
											$b++;
									}
								}
							}
							// Combine core & custom
							$combine = array_merge($core, $custom);
							if ( countCustom($combine) > 0 ) 
							{
								foreach ($combine as $key2 => $value2) 
								{
										$content_all = $content_all." ".$value2['description'].", ";
								}
								$result[$key]['description'] 						= $content_all;
								$result[$key]['name'] 									= $value['syslog_created_by'];
								$result[$key]['syslog_date_created'] 		= date('Y-m-d H:i:s', strtotime($value['syslog_date_created']));
								$result[$key]['syslog_action_type'] 		= "update";
							}
				}
				elseif ( $value['syslog_action_type'] == 'removetag')
				{
					$result[$key]['description'] 				= $value['syslog_action'];
					$result[$key]['name'] 						= $value['syslog_created_by'];
					$result[$key]['syslog_date_created'] 		= date('Y-m-d H:i:s', strtotime($value['syslog_date_created']));
					$result[$key]['syslog_action_type'] 		= "remove";
				}

			}// End foreach
		}// End if not null

		$result = array_values($result);
		return $result;
	}

	public function getListReport($company_id)
	{
		$data_widget = $this->model_widget_class::where('widget_module','=', $this->table_module)
																						->where('deleted','=',Config('setting.NOT_DELETED'))
																						->get();
		return $data_widget->all();
	}

	public function GetDataBy($data_uuid)
	{
		// define variable
		$result = array();

		// Get data find by data_uuid
		$leads_fields = "leads.*, DATE_FORMAT(leads.leads_birthdate,'%d-%m-%Y') as leads_birthdate";
		$result = $this->model_class::select(DB::raw($leads_fields))
														->where( $this->table_module.'_uuid', '=', $data_uuid)
														->get()
														->first();

		if (countCustom($result) > 0 ) {
			if ( $this->json_mode === TRUE) {
				$result = $result->toJson(); //put listing result in json
			}
			else {
				$result = $result->toArray(); // put listing result in array
			}
		}

		return $result;
	}

	public function GetCustomValues($data_uuid=0, $company_id=0)
	{
		// define variable
		$sys = new sys();
		$result = array();
		$CustomValuesBy = array();

		// Get data : custom_values find by data_uuid
		$GetSerialId = $this->model_class::where($this->table_module.'_uuid', '=', $data_uuid)->first();
		// Query for select custom fields
		$select_custom_values = $sys->library_select_custom_fields_as_id($this->table_module, $company_id);
		$select = implode(', ', $select_custom_values);

		// Get data : custom_values find by data_uuid
		if (!isEmpty($select_custom_values)) 
		{
			$CustomValuesBy = $this->model_custom_values__class::select(DB::raw($select))->where( $this->table_module.'_serial_id', '=', $GetSerialId["{$this->table_module}_serial_id"])->first();
		}

		// convert $CustomValuesBy to array by data_uuid
		if (countCustom($CustomValuesBy) > 0)
		{
			$result = $CustomValuesBy->toArray();
		}

		return $result;
	}

	public function CheckDuplicate($request, $company_id)
	{
		$check = array(); 
		$type_check = '';
		$filtered = array_filter($request, function ($key) { return strpos($key, 'contacts_') === 0; }, ARRAY_FILTER_USE_KEY);
		$request_contacts = ['contacts' => $filtered];
		$filtered = array_filter($request, function ($key) { return strpos($key, 'org_') === 0; }, ARRAY_FILTER_USE_KEY);
		$request_org = ['org' => $filtered];

		$first_name = isset($request_contacts['contacts']['contacts_first_name']) ? $request_contacts['contacts']['contacts_first_name'] : '';
		$last_name 	= isset($request_contacts['contacts']['contacts_last_name']) ? $request_contacts['contacts']['contacts_last_name'] : '';
		$email 			= isset($request_contacts['contacts']['contacts_email']) ? $request_contacts['contacts']['contacts_email'] : '';
		$org_name 	= isset($request_org['org']['org_name']) ? $request_org['org']['org_name'] : '';

		if ( isset($request['radio1']) AND $request['radio1'] == 'create_contacts' ) 
		{
				// 1. Check duplicate all
				$check_contacts = $this->model_contacts_class::where('contacts_first_name', '=', $first_name)
																->where('contacts_last_name', '=', $last_name)
																->where('contacts_email', '=', $email)
																->where('company_id', '=', $company_id)
																->where('deleted', '=', Config('setting.NOT_DELETED'))
																->first();

				if (countCustom($check_contacts) > 0 )
				{
					$type_check = 'contacts';
					$check = $check_contacts;

					return array(
										'name' 				=> $first_name.' '.$last_name,
										'check' 			=> $check,
										'type_check' 	=> $type_check,
									);
				}
		}

		if ( isset($request['radio2']) AND $request['radio2'] == 'create_org' AND !isEmpty($org_name) ) 
		{
				// 2. Check duplicate all
				$check_org = $this->model_org_class::where('org_name', '=', $org_name)
																					->where('company_id', '=', $company_id)
																					->where('deleted', '=', Config('setting.NOT_DELETED'))
																					->first();

				if (countCustom($check_org) > 0 )
				{
					$type_check = 'org';
					$check = $check_org;

					return array(
										'name' 				=>$org_name,
										'check' 			=> $check,
										'type_check' 	=> $type_check,
									);
				}
		}

		return array(
							'name' 				=> '',
							'check' 			=> $check,
							'type_check' 	=> $type_check,
						);
	}

	# Created By Fitri Mahardika
 	# 04/02/20
  	# For save Import
		public function importSave($request=array(), $company_id=0, $users_id=0)
	{
		$sys = new sys();

		// define variables for looping the needs of dynamic data
		$key_import 		= array(); // data array (key)
		$name_import 		= array(); // data array (value)
		$data 			 		= array(); // data array (value)
		$out 			 			= array(); // data array (value)
		$owner_id 			= array(); // data array (value)
		$next_condition = array(); // data array (value)
		
		if (isset($request['data'])) 
		{
			if (isset($request['data']['key'])) 
			{
				$key_import = $request['data']['key'];
			}
			
			if (isset($request['data']['value']))
			{
				$name_import = $request['data']['value'];
			}
		}

		if (isset($request[$this->table_module.'_owner']))
		{
			if (isset($request[$this->table_module.'_owner']['owner_id']))
			{
				$owner_id = $request[$this->table_module.'_owner']['owner_id'];
			}
		}

		if (isset($request['next_condition']))
		{
			if (isset($request['next_condition']['value']))
			{
				$next_condition = $request['next_condition']['value'];
			}
		}

		if (isset($request['collection'])) 
		{
			$data = $request['collection'];
		}

    $GetInputTypeCustomField    = $this->GetForm($company_id); // get core and custom fields
    $form_custom                = array(); // will contain index of custom fields serial id and input type
    foreach($GetInputTypeCustomField as $key => $value)
    {
      if( isset($value[$this->table_module.'_fields_serial_id']) )
      {
        unset($GetInputTypeCustomField[$key]);
      }
      else
      {
        $form_custom[$value[$this->table_module.'_custom_fields_serial_id']] = $value[$this->table_module.'_custom_fields_input_type'];
      }
    }

    foreach ($data as $key => $value) 
		{
			// Reset variable
			unset($InAll);
			unset($inAllCustom);

			foreach ($key_import as $key1 => $value1) 
			{
				if ( $name_import[$key1] != 'none' ) 
				{
					$trim_custom = substr($name_import[$key1], 0, 9);

					if ($trim_custom == 'custommm_') 
					{
						$custom = substr($name_import[$key1], 9);
						$inAllCustom[$custom] = $value[$key_import[$key1]];
						continue;
					}

					if ( $name_import[$key1] == $this->table_module.'_owner' OR $name_import[$key1] == 'created_by' OR $name_import[$key1] == 'modified_by' ) 
					{
						if (isset($owner_id))
						{
							foreach ($owner_id as $key2 => $value2) {
								$InAll[$name_import[$key1]] = $value2;
							} 
						}
						else
						{
							$name_owner = $value[$key_import[$key1]];

							$GetOwnerId = $this->GetOwnerToId($name_owner, $company_id, $users_id);

							$InAll[$name_import[$key1]] = $GetOwnerId;
						}	
					}
					elseif ( $name_import[$key1] == $this->table_module.'_direction' )
					{
						if ( $value[$key_import[$key1]] == 'Inbound' OR $value[$key_import[$key1]] == 'Outbound' )
						{
							$InAll[$name_import[$key1]] = $value[$key_import[$key1]];
						}
						else
						{
							$InAll[$name_import[$key1]] = 'Outbound'; // Default
						}
					}
					elseif ( $name_import[$key1] == $this->table_module.'_duration_hours' OR $name_import[$key1] == $this->table_module.'_duration_minutes' )
					{
						if ( is_numeric($value[$key_import[$key1]]) )
						{
							$InAll[$name_import[$key1]] = $value[$key_import[$key1]];
						}
						else
						{
							$InAll[$name_import[$key1]] = '00'; // Default
						}
					}
					elseif ( $name_import[$key1] == $this->table_module.'_status' )
					{
							$InAll[$name_import[$key1]] = $value[$key_import[$key1]]; // Default						
					}
					elseif ( $name_import[$key1] == $this->table_module.'_parent_type' )
					{
						if ( strtolower($value[$key_import[$key1]]) == 'lead' OR strtolower($value[$key_import[$key1]]) == 'leads' )
						{
							$InAll[$name_import[$key1]] = "leads";
						}
						elseif ( strtolower($value[$key_import[$key1]]) == 'contact' OR strtolower($value[$key_import[$key1]]) == 'contacts' )
						{
							$InAll[$name_import[$key1]] = "contacts";
						}
						elseif ( strtolower($value[$key_import[$key1]]) == 'organization' OR strtolower($value[$key_import[$key1]]) == 'organizations' )
						{
							$InAll[$name_import[$key1]] = "org";
						}
						else
						{
							// Remove parent type & parent id need for clean log
							unset($InAll[$this->table_module.'_parent_type']);
							unset($InAll[$this->table_module.'_parent_id']);
						}
					}
					elseif ( $name_import[$key1] == 'date_created' OR $name_import[$key1] == 'date_modified' OR $name_import[$key1] == $this->table_module.'_date_start' OR $name_import[$key1] == $this->table_module.'_date_end' )
					{
						if (is_int($value[$key_import[$key1]])) {
							$valueDate = $value[$key_import[$key1]];
							$convert = $this->convertDate($valueDate);
							$InAll[$name_import[$key1]] = date("Y-m-d H:i:s", strtotime($convert."-7 hours"));
						} else {
							$InAll[$name_import[$key1]] = date("Y-m-d H:i:s", strtotime($value[$key_import[$key1]]."-7 hours"));
						}
					}
					else
					{
						if (isset($value[$key_import[$key1]])) 
						{
							$InAll[$name_import[$key1]] = $value[$key_import[$key1]];
						}
					}
				}
			}

			$uuid4 = $this->uuid::uuid4();
			$InAll["{$this->table_module}_uuid"] 		= $uuid4->toString();
			$InAll['company_id'] 										= $company_id;

			// owner
			if ( !isset($InAll[$this->table_module.'_owner']))
			{
				if (isset($owner_id))
				{
					foreach ($owner_id as $key => $value) {
						$InAll[$this->table_module.'_owner'] = $value;
					} 
				}
				else
				{
					$InAll[$this->table_module.'_owner'] = $users_id;
				}
			}

			// Default date_created
			if ( !isset($InAll['date_created']) ) 
			{
				$InAll['date_created'] = date('Y-m-d H:i:s');
			}

			// Default created_by
			if ( !isset($InAll['created_by']) ) 
			{
				$InAll['created_by'] = $users_id;
			}

			//Default Related Projects
			if (isset($InAll['projects_serial_id']))
			{
				$tempinall = $this->model_projects_class::where('company_id', '=', $company_id)
																								->where('projects_unique_id', '=', $InAll['projects_serial_id'])
																								->first();

																								if (count((array)$tempinall) > 0 )
																								{
																									$tempinall = json_decode(json_encode($tempinall), true);
																									$InAll['projects_serial_id'] = $tempinall['projects_serial_id'];
																								}
			}

			//Default Related Deals
			if (isset($InAll['deals_serial_id']))
			{
				$tempinall = $this->model_deals_class::where('company_id', '=', $company_id)
																								->where('deals_unique_id', '=', $InAll['deals_serial_id'])
																								->first();

																								if (count((array)$tempinall) > 0 )
																								{
																									$tempinall = json_decode(json_encode($tempinall), true);
																									$InAll['deals_serial_id'] = $tempinall['deals_serial_id'];
																								}
			}

			//Default Related Issue
			if (isset($InAll['issue_serial_id']))
			{
				$tempinall = $this->model_issue_class::where('company_id', '=', $company_id)
																								->where('issue_unique_id', '=', $InAll['issue_serial_id'])
																								->first();

																								if (count((array)$tempinall) > 0 )
																								{
																									$tempinall = json_decode(json_encode($tempinall), true);
																									$InAll['issue_serial_id'] = $tempinall['issue_serial_id'];
																								}
			}

			//Default Related Tickets
			if (isset($InAll['tickets_serial_id']))
			{
				$tempinall = $this->model_tickets_class::where('company_id', '=', $company_id)
																								->where('tickets_unique_id', '=', $InAll['tickets_serial_id'])
																								->first();

																								if (count((array)$tempinall) > 0 )
																								{
																									$tempinall = json_decode(json_encode($tempinall), true);
																									$InAll['tickets_serial_id'] = $tempinall['tickets_serial_id'];
																								}
			}

			// Check Related to Exists
			if ( isset($InAll[$this->table_module.'_parent_type']) AND isset($InAll[$this->table_module.'_parent_id']) )
			{
				// Query check exists
				if ( $InAll[$this->table_module.'_parent_type'] == 'leads' )
				{
					$query_check = $this->model_leads_class::select(DB::raw("leads_serial_id as id, CONCAT(COALESCE(leads_first_name, ''),' ', COALESCE(leads_last_name, '')) as name"));
				}
				elseif ( $InAll[$this->table_module.'_parent_type'] == 'contacts' )
				{
					$query_check = $this->model_contacts_class::select(DB::raw("contacts_serial_id as id, CONCAT(COALESCE(contacts_first_name, ''),' ', COALESCE(contacts_last_name, '')) as name"));
				}
				elseif ( $InAll[$this->table_module.'_parent_type'] == 'org' )
				{
					$query_check = $this->model_org_class::select(DB::raw("org_serial_id as id, org_name as name"));
				}

				$query_check = $query_check->having('name', '=', $InAll[$this->table_module.'_parent_id'])
																	 ->where('company_id', '=', $company_id)
																	 ->where('deleted', '=', Config('setting.NOT_DELETED'))
																	 ->first();
				if (countCustom($query_check) > 0 )
				{
					$InAll[$this->table_module.'_parent_id'] = $query_check['id'];
				}
				else
				{
					unset($InAll[$this->table_module.'_parent_type']);
					unset($InAll[$this->table_module.'_parent_id']);
				}
			}
			else
			{
				unset($InAll[$this->table_module.'_parent_type']);
				unset($InAll[$this->table_module.'_parent_id']);
			}

			$out[] = $InAll;
			if ( isset($inAllCustom) ) 
			{
				$out_custom[] = $inAllCustom;
			}
		}

		// result data is important $out & $out_custom 

		if (countCustom($out) > 0 ) 
		{
			// Initiate user that will be receive data
			$listUserIdReceived = array();
			foreach ($out as $key => $value) 
			{
				// define variable
				$result 				= array();
				$log_result 		= array();

				// save core
				$insert = $out[$key];
				unset($insert[$this->table_module.'_parent_type']);
				unset($insert[$this->table_module.'_parent_id']);

				$ownerData = !empty($insert[$this->table_module.'_owner']) ? $insert[$this->table_module.'_owner'] : null;
				if(empty($insert[$this->table_module.'_unique_id']) || $insert[$this->table_module.'_unique_id'] == '-')
        {
          $save_core  = $this->model_class::create($insert);
					if (!isEmpty($ownerData) AND $ownerData != $users_id)
					{
						if (isEmpty($listUserIdReceived[$ownerData])) $listUserIdReceived[$ownerData] = 0;
						$listUserIdReceived[$ownerData]++;
					}
          $last_id    = $save_core[$this->table_module.'_serial_id'];
					$format_generate_unique_id = $this->model_generate_custom_unique_id::select('format_custom_unique_id')
					->where('modules', '=', $this->table_module)
					->where('company_id', '=', $company_id)
					->orderBy('id', 'DESC')
					->first();

					if(!isEmpty($format_generate_unique_id))
						{
							$newformat = json_decode($format_generate_unique_id['format_custom_unique_id'], true);
							$newformat = array_values($newformat)[0];

							$previous_customize_id = $this->model_modules_customize_id::select('last_customize_id')
										->where('company_id', '=', $company_id)
										->where('module','=', $this->table_module)
										->first();

							if(isEmpty($previous_customize_id) AND $previous_customize_id == null )
							{
								$data_cutomize_id['company_id']						= $company_id;
								$data_cutomize_id['module']								= $this->table_module;
								$data_cutomize_id['last_customize_id']    = 0;
								$save_data_customide_id = $this->model_modules_customize_id::create($data_cutomize_id);

								$previous_customize_id = $this->model_modules_customize_id::select('last_customize_id')
																							->where('company_id', '=', $company_id)
																							->where('module','=', $this->table_module)
																							->first();
							}


										$last_customize_id = json_decode(json_encode($previous_customize_id), true);
										$last_customize_id = $last_customize_id['last_customize_id']+1; 

										$update_request[$this->table_module.'_unique_id']	= $newformat['generate_custom_char'].'-'.str_pad($last_customize_id, $newformat['generate_custom_total'], '0', STR_PAD_LEFT);

											$this->model_class::where($this->table_module.'_serial_id', '=', $last_id)
																				->where('company_id', '=', $company_id)
																				->update($update_request);

										$update_modules_customize_id['last_customize_id']	= $last_customize_id;

										$this->model_modules_customize_id::where('module', '=', $this->table_module)
																			->where('company_id', '=', $company_id)
																			->update($update_modules_customize_id);
					 	}
							 else
					 	{
							$insert[$this->table_module.'_unique_id']	= strtoupper($this->table_module).'-'.sprintf("%'.010d", $company_id.$last_id);

							$this->model_class::where($this->table_module.'_serial_id', '=', $last_id)
												->where('company_id', '=', $company_id)
												->update($insert);
						}
        }
				elseif (isset($insert[$this->table_module.'_unique_id']))
				{
					$new_data = array();
					foreach ($out as $key2 => $value2) 
					{
						$new_data[$this->table_module.'_unique_id'][$key2] = $out[$key2][$this->table_module.'_unique_id'];
					}

					$query = $this->model_class::where('company_id', '=', $company_id)
																		 ->where('deleted', '=', Config('setting.NOT_DELETED'))
																		 ->whereIn($this->table_module.'_unique_id', $new_data[$this->table_module.'_unique_id'])
																		 ->count();

					if ($query > 0)
					{
						if (isset($next_condition))
						{
							foreach ($next_condition as $key2 => $value2) 
							{
								if ($value2 == 1)
								{
									$get_serial_id = $this->model_class::where($this->table_module.'_unique_id', '=', $insert[$this->table_module.'_unique_id'])
																										 ->where('deleted', '=', Config('setting.NOT_DELETED'))
																										 ->where('company_id', '=', $company_id)
																										 ->first();

									if (isset($insert['date_created']))
									{
										unset($insert['date_created']);
									}
									if (isset($insert['created_by']))
									{
										unset($insert['created_by']);
									}
									if (!isset($insert['date_modified']))
									{
										$insert['date_modified'] = date('Y-m-d H:i:s');
									}
									if (!isset($insert['modified_by']))
									{
										$insert['modified_by'] = $users_id;
									}
									
									if($get_serial_id instanceof $this->model_class)
									{
										$get_serial_id = $get_serial_id->toArray();

										$this->model_class::where($this->table_module.'_serial_id', '=', $get_serial_id[$this->table_module.'_serial_id'])
																			->where($this->table_module.'_uuid', '=', $get_serial_id[$this->table_module.'_uuid'])
																			->update($insert);

										if (!isEmpty($ownerData) AND $ownerData != $users_id)
										{
											if (isEmpty($listUserIdReceived[$ownerData])) $listUserIdReceived[$ownerData] = 0;
											$listUserIdReceived[$ownerData]++;
										}

										$last_id = $get_serial_id[$this->table_module.'_serial_id'];
									}
									else
									{
										$save_core = $this->model_class::create($insert);
										if (!isEmpty($ownerData) AND $ownerData != $users_id)
										{
											if (isEmpty($listUserIdReceived[$ownerData])) $listUserIdReceived[$ownerData] = 0;
											$listUserIdReceived[$ownerData]++;
										}

										$last_id = $save_core[$this->table_module.'_serial_id'];

										// START : Save booking custom values
										$booking_insert[$this->table_module.'_serial_id'] = $last_id;
										$booking_insert['company_id']											= $company_id;
										$this->model_custom_values__class::create($booking_insert);
										// END : Save booking custom values
									}
									
									$get_serial_id  = null;

								}
								else
								{
									$save_core = $this->model_class::create($insert);
									if (!isEmpty($ownerData) AND $ownerData != $users_id)
									{
										if (isEmpty($listUserIdReceived[$ownerData])) $listUserIdReceived[$ownerData] = 0;
										$listUserIdReceived[$ownerData]++;
									}

									$last_id = $save_core[$this->table_module.'_serial_id'];

									// START : Save booking custom values
									$booking_insert[$this->table_module.'_serial_id'] = $last_id;
									$booking_insert['company_id']											= $company_id;
									$this->model_custom_values__class::create($booking_insert);
									// END : Save booking custom values
								}	
							}
						}
					}
					else
					{
						$save_core = $this->model_class::create($insert);
						if (!isEmpty($ownerData) AND $ownerData != $users_id)
						{
							if (isEmpty($listUserIdReceived[$ownerData])) $listUserIdReceived[$ownerData] = 0;
							$listUserIdReceived[$ownerData]++;
						}

						$last_id = $save_core[$this->table_module.'_serial_id'];

						// START : Save booking custom values
						$booking_insert[$this->table_module.'_serial_id'] = $last_id;
						$booking_insert['company_id']											= $company_id;
						$this->model_custom_values__class::create($booking_insert);
						// END : Save booking custom values
					}
				}
				else
				{
					$save_core = $this->model_class::create($insert);
					if (!isEmpty($ownerData) AND $ownerData != $users_id)
					{
						if (isEmpty($listUserIdReceived[$ownerData])) $listUserIdReceived[$ownerData] = 0;
						$listUserIdReceived[$ownerData]++;
					}

					$last_id = $save_core[$this->table_module.'_serial_id'];
					$format_generate_unique_id = $this->model_generate_custom_unique_id::select('format_custom_unique_id')
					->where('modules', '=', $this->table_module)
					->where('company_id', '=', $company_id)
					->orderBy('id', 'DESC')
					->first();

					if(!isEmpty($format_generate_unique_id))
						{
							$newformat = json_decode($format_generate_unique_id['format_custom_unique_id'], true);
							$newformat = array_values($newformat)[0];

							$previous_customize_id = $this->model_modules_customize_id::select('last_customize_id')
										->where('company_id', '=', $company_id)
										->where('module','=', $this->table_module)
										->first();

							if(isEmpty($previous_customize_id) AND $previous_customize_id == null )
							{
								$data_cutomize_id['company_id']						= $company_id;
								$data_cutomize_id['module']								= $this->table_module;
								$data_cutomize_id['last_customize_id']    = 0;
								$save_data_customide_id = $this->model_modules_customize_id::create($data_cutomize_id);

								$previous_customize_id = $this->model_modules_customize_id::select('last_customize_id')
																							->where('company_id', '=', $company_id)
																							->where('module','=', $this->table_module)
																							->first();
							}


										$last_customize_id = json_decode(json_encode($previous_customize_id), true);
										$last_customize_id = $last_customize_id['last_customize_id']+1; 

										$update_request[$this->table_module.'_unique_id']	= $newformat['generate_custom_char'].'-'.str_pad($last_customize_id, $newformat['generate_custom_total'], '0', STR_PAD_LEFT);

											$this->model_class::where($this->table_module.'_serial_id', '=', $last_id)
																				->where('company_id', '=', $company_id)
																				->update($update_request);

										$update_modules_customize_id['last_customize_id']	= $last_customize_id;

										$this->model_modules_customize_id::where('module', '=', $this->table_module)
																			->where('company_id', '=', $company_id)
																			->update($update_modules_customize_id);
					 	}
							 else
					 	{
										$insert[$this->table_module.'_unique_id']	= strtoupper($this->table_module).'-'.sprintf("%'.010d", $company_id.$last_id);

										$this->model_class::where($this->table_module.'_serial_id', '=', $last_id)
															->where('company_id', '=', $company_id)
															->update($insert);
						}
					// START : Save booking custom values
					$booking_insert[$this->table_module.'_serial_id'] = $last_id;
					$booking_insert['company_id']											= $company_id;
					$this->model_custom_values__class::create($booking_insert);
					// END : Save booking custom values
				}

				// Save custom fields
				if ( isset($out_custom[$key]) ) 
				{
					$data_custom = array(); // Define variable
					foreach ($out_custom[$key] as $key2 => $value2) 
					{
						$prefix_custom  = substr($key2, 0, 7);
						if ($prefix_custom == "custom_") 
						{
							$key2 = substr_replace($key2, '', 0, 7);
						}
						// $key2 = name fields, convert from name to id (custom fields)
						// $GetCustomFieldsSerialId 		= $this->GetCustomFieldsSerialId($key2, $company_id);
						$library_values_maps_by_name 	= $sys->library_values_maps_by_name($key2, $this->table_module, $company_id);

            $data_custom[$library_values_maps_by_name] 	= $value2;
            
						//// ------------- ////
						//    THIS IS LOG    //
						if($value2 != null OR $value2 != '')
						{
              $out[$key][$this->table_module.'_custom_values_text'][$library_values_maps_by_name] = $value2;
						}
						//// ------------- ////
						//    END IS LOG    //
					}
				  
					$customValuesSet = $this->model_custom_values__class::where('company_id', '=', $company_id)
											->where($this->table_module.'_serial_id', '=', $last_id);

					if ($customValuesSet->count() > 0) {
						$this->model_custom_values__class::where('company_id', '=', $company_id)
							->where($this->table_module.'_serial_id', '=', $last_id)
							->update($data_custom);
					} else {
						$data_custom['company_id'] = $company_id;
						$data_custom[$this->table_module.'_serial_id'] = $last_id;
						$this->model_custom_values__class::create($data_custom);
					}
				}

				unset($out[$key]['created_by']);
				unset($out[$key]['modified_by']);
				unset($out[$key]['date_created']);
				unset($out[$key]['date_modified']);
				$syslog_action = $sys->log_save($out[$key], $this->table_module, $company_id);
				if ( !isEmpty($syslog_action) ) 
				{
					$sys->sys_api_syslog( $syslog_action, 'import', $this->table_module, $last_id, $users_id, $company_id );
				}

				if ( isset($value[$this->table_module.'_parent_type']) AND isset($value[$this->table_module.'_parent_id']) )
				{
					if ( $value[$this->table_module.'_parent_type'] == 'contacts' OR $value[$this->table_module.'_parent_type'] == 'leads' OR $value[$this->table_module.'_parent_type'] == 'org' )
					{
						$rel = [];
						if(isset($get_serial_id)){
							if($get_serial_id == null){
								if ($value[$this->table_module.'_parent_id'] > 0)
								{
									$rel['rel_from_module'] = $this->table_module;
									$rel['rel_from_id'] = $last_id;
									$rel['rel_to_module'] = $value[$this->table_module.'_parent_type'];
									$rel['rel_to_id'] = $value[$this->table_module.'_parent_id'];
									$rel['company_id'] = $company_id;
	
									// Save sysrel
									$this->model_sys_rel_class::create($rel);
								}
							}else{
								// $rel['rel_from_module'] = $this->table_module;
								// $rel['rel_from_id'] = $last_id;
								$rel['rel_to_module'] = $value[$this->table_module.'_parent_type'];
								$rel['rel_to_id'] = $value[$this->table_module.'_parent_id'];

								// Save sysrel
								$this->model_sys_rel_class::where('rel_from_module',$this->table_module)
								->where('rel_from_id',$last_id)->update($rel);
							}
						}else{

							if($this->model_sys_rel_class::where('rel_from_module',$this->table_module)
							->where('rel_from_id', $last_id)->count() > 0){
								$this->model_sys_rel_class::where('rel_from_module',$this->table_module)
								->where('rel_from_id', $last_id)->update([
									'rel_to_module' => $value[$this->table_module.'_parent_type'],
									'rel_to_id' => $value[$this->table_module.'_parent_id']
								]);
							}else{
								if ($value[$this->table_module.'_parent_id'] > 0)
								{
									$rel['rel_from_module'] = $this->table_module;
									$rel['rel_from_id'] = $last_id;
									$rel['rel_to_module'] = $value[$this->table_module.'_parent_type'];
									$rel['rel_to_id'] = $value[$this->table_module.'_parent_id'];
									$rel['company_id'] = $company_id;
									// Save sysrel
									$this->model_sys_rel_class::create($rel);
								}
							}
						}
					}
				}

				elasticAddData($last_id, $company_id, $this->table_module);
			}

			// Send request to push notification
			$sys_push = new sys_push();
			$requestPushNotif = [
				"user_id" => $users_id,
			];
			$sys_push->broadcastBulkAssignment($requestPushNotif, $listUserIdReceived, $company_id, $this->table_module);
		}

    return true;
	}

	public function GetCustomFieldsSerialId($name_custom_fields='', $company_id=0)
	{
		$result = '';

		$data = $this->model_custom_fields_class::where($this->table_module.'_custom_fields_name', '=', $name_custom_fields)
																						->where('company_id', '=', $company_id)
																						->first();

		if ( countCustom($data) > 0 ) 
		{
			$result = $data[$this->table_module.'_custom_fields_serial_id'];
		}

		return $result;
	}

	public function DropdownAll($company_id)
	{
		$result = array();

		$GetDropdown = $this->model_dropdown_class::where('company_id', '=', $company_id)
												->where('deleted', '=', Config('setting.NOT_DELETED'))
												->get();

		if (countCustom($GetDropdown) > 0 ) 
		{
			$result = $GetDropdown->toArray();
		}

		return $result;
	}

	# Created By Pratama Gilang
  # 14-01-2019
  # For get Data Export All data
  # running for url /v1/leads/exportAll
  # running on controller exportAll
	public function exportProcess($criteria=array(), $fields=array(), $input=array(), $data_roles=TRUE, $data_filter=TRUE)
	{
	 	$sys = new sys();

		# DEFINED VARIABLE
		$listFieldsCustom 		= $fields['listFieldsCustom']; // Get Fields Custom
		$query_count 					= 0; // count query : default
		# END

		# LIST CORE FIELDS AND CUSTOM FIELDS (MERGE)
		$fieldsName 			= $this->select_fieldsName($fields); // list fields in core field and custom fields. 
		# END
		
		# CHANGE OWNER_ID TO OWNER_NAME
		$checkOwner 	= in_array($this->table_module.'.'.$this->table_module.'_owner', $fieldsName); // check if owner available in $fieldsName

		if($checkOwner === TRUE)  
		{
			$fieldsName 	= array_merge($fieldsName, array('users.name as '.$this->table_module.'_owner')); 
		}
		//$criteria['order_by'] = ($criteria['order_by'] == $this->table_module."_owner") ? "users.name" : $criteria['order_by']; // if order owner, then order by users.name ASC/DESC
		# END 

		# CHANGE CREATED_ID TO CREATED_NAME
		$checkCreated 	= in_array($this->table_module.'.created_by', $fieldsName); // check if created_by available in $fieldsName
		if($checkCreated === TRUE)  
		{
			$fieldsName 	= array_merge($fieldsName, array('users_created.name as created_by')); 
		}
		// $criteria['order_by'] = ($criteria['order_by'] == "created_by") ? "users_created.name" : $criteria['order_by']; // if order created_by, then order by users_created.name ASC/DESC
		# END

		# CHANGE MODIFIED_ID TO MODIFIED_NAME
		$checkModified 	= in_array($this->table_module.'.modified_by', $fieldsName); // check if modified_by available in $fieldsName
		if($checkModified === TRUE)  
		{
			$fieldsName 	= array_merge($fieldsName, array('users_modified.name as modified_by')); 
		}
		// $criteria['order_by'] = ($criteria['order_by'] == "modified_by") ? "users_modified.name" : $criteria['order_by']; // if order modified_by, then order by users_modified.name ASC/DESC
		# END

		# CHANGE DEALS_SERIAL_ID TO DEALS_UUID and DEALS_NAME  
		$checkDealSerialId 	= in_array($this->table_module.'.deals_serial_id', $fieldsName); // check if owner available in $fieldsName
		if($checkDealSerialId === TRUE)  
		{
			$fieldsName 	= array_merge($fieldsName, array('deals.deals_uuid', 'deals.deals_name')); 
		} 
		# END 

		# CHANGE PROJECTS_SERIAL_ID TO PROJECTS_UUID and PROJECTS_NAME  
		$checkProjectsSerialId 	= in_array($this->table_module.'.projects_serial_id', $fieldsName); // check if projects available in $fieldsName
		if($checkProjectsSerialId === TRUE)  
		{
			$fieldsName 	= array_merge($fieldsName, array('projects.projects_uuid', 'projects.projects_name')); 
		} 
		# END 

		# CHANGE ISSUE_SERIAL_ID TO ISSUE_UUID and ISSUE_NAME  
		$checkIssueSerialId 	= in_array($this->table_module.'.issue_serial_id', $fieldsName); // check if issue available in $fieldsName
		if($checkIssueSerialId === TRUE)  
		{
			$fieldsName 	= array_merge($fieldsName, array('issue.issue_uuid', 'issue.issue_name')); 
		} 
		# END

		# CONVERT TO ROW QUERY FORMAT
		$fieldsNameConvert	= $this->convertFieldsName($fieldsName);
		# END 

		# SELECT QUERY DYNAMIC BY $fieldsName
		$b = 'b';
		$fieldsNameSysRel = "sys_rel.rel_to_module as ".$this->table_module."_parent_type, sys_rel.rel_to_id as ".$this->table_module."_parent_id,
											(
												CASE   
													WHEN sys_rel.rel_to_module = 'leads' THEN ( SELECT CONCAT(COALESCE(leads.leads_first_name, ''),' ', COALESCE(leads.leads_last_name, '')) FROM leads WHERE leads.leads_serial_id = sys_rel.rel_to_id LIMIT 1)  
													WHEN sys_rel.rel_to_module = 'contacts' THEN ( SELECT CONCAT(COALESCE(contacts.contacts_first_name, ''),' ', COALESCE(contacts.contacts_last_name, '')) FROM contacts WHERE contacts.contacts_serial_id = sys_rel.rel_to_id LIMIT 1) 
													WHEN sys_rel.rel_to_module = 'org'  THEN ( SELECT org_name FROM org WHERE org.org_serial_id = sys_rel.rel_to_id LIMIT 1) 
													WHEN sys_rel.rel_to_module is NULL THEN ''
												END
												) as ".$this->table_module."_parent_id";
		$query = $this->model_class::select(DB::raw($fieldsNameConvert.",".$fieldsNameSysRel));
		# END 

		# LEFT JOIN WITH SYS REL
		$query->leftjoin('sys_rel', function($join) use ($criteria)
		        { 
		            $join->on('sys_rel.rel_from_id', '=', $this->table_module.'.'.$this->table_module.'_serial_id')
		            ->where('sys_rel.rel_from_module', '=', $this->table_module);
		        });
		if ($criteria['order_by'] == $this->table_module."_parent_id" || $criteria['order_by'] == $this->table_module . "." . $this->table_module."_parent_id")
		{
			$criteria['order_by'] 	= $this->table_module."_parent_id";
		}
		# END 

		$temp_alias = array();
		if (countCustom($listFieldsCustom) > 0) // 	if fields custom available
		{
			$query->leftjoin($this->table_module.'_custom_values_ as mcv', function($join) use ($criteria)
							{
								$join->on('mcv.'.$this->table_module.'_serial_id', '=', $this->table_module.'.'.$this->table_module.'_serial_id')
								->where('mcv.company_id', '=', $criteria['company_id']);
							});

		}
		#END

		# IF OWNER AVAILABLE IN $fieldsName
		if ($checkOwner === TRUE || $data_filter == TRUE || (isset($_GET['subaction']) && $_GET['subaction'] == "search"))
		{
			//run if owner show in listing OR 
			//run if data_filter TRUE OR
			//run if search feature true
			$query->leftjoin('users', 'users.id', '=', $this->table_module.'.'.$this->table_module.'_owner');
		}

		# IF CREATED AVAILABLE IN $fieldsName
		if ($checkCreated === TRUE || (isset($_GET['subaction']) && $_GET['subaction'] == "search"))
		{
			//run if created show in listing OR 
			//run if search feature true
			$query->leftjoin('users as users_created', 'users_created.id', '=', $this->table_module.'.created_by');
		}

		# IF MODIFIED AVAILABLE IN $fieldsName
		if ($checkModified === TRUE || (isset($_GET['subaction']) && $_GET['subaction'] == "search"))
		{
			//run if modified show in listing OR 
			//run if search feature true
			$query->leftjoin('users as users_modified', 'users_modified.id', '=', $this->table_module.'.modified_by');
		}

		# IF DEALS_SERIAL_ID AVAILABLE IN $fieldsName
		$checkDealSerialId 	= in_array($this->table_module.'.deals_serial_id', $fieldsName); // check if owner available in $fieldsName
		if($checkDealSerialId === TRUE)  
		{
			$query->leftjoin('deals', 'deals.deals_serial_id', '=', $this->table_module.'.deals_serial_id');
		}

		# IF PROJECTS_SERIAL_ID AVAILABLE IN $fieldsName
		$checkProjectsSerialId 	= in_array($this->table_module.'.projects_serial_id', $fieldsName); // check if projects available in $fieldsName
		if($checkProjectsSerialId === TRUE)  
		{
			$query->leftjoin('projects', 'projects.projects_serial_id', '=', $this->table_module.'.projects_serial_id');
		}

		# IF ISSUE_SERIAL_ID AVAILABLE IN $fieldsName
		$checkIssueSerialId 	= in_array($this->table_module.'.issue_serial_id', $fieldsName); // check if issue available in $fieldsName
		if($checkIssueSerialId === TRUE)  
		{
			$query->leftjoin('issue', 'issue.issue_serial_id', '=', $this->table_module.'.issue_serial_id');
		}

		$myRecordOnlyCheck 	= $this->myRecordOnlyCheck($criteria['company_id'], $criteria['users_id']); //if filter view " (You) " Checked

		# FILTER FEATURE
		if ($data_filter == TRUE)
		{
			//Running filter when $data_filter TRUE
			$filterView 				= $this->viewChecked($criteria['company_id'], $criteria['users_id']); // checked filter view active
			$filterViewName 		= $filterView[$this->table_module.'_view_name'];
			
			$filterCriteria 			= $this->generate_view($filterView, $criteria['company_id'], $criteria['users_id']); // get the selected filter 	

			$filterCriteria 			= $this->data_search($filterCriteria, $criteria['company_id'], $temp_alias, $listFieldsCustom, $b); // generate format for use filter feature
		
			if(isset($filterCriteria['temp']))
			{
				if (countCustom($listFieldsCustom) == 0) // if fields custom not available
				{
					$query->leftjoin($this->table_module.'_custom_values_ as mcv', function($join) use ($criteria)
								{
									$join->on('mcv.'.$this->table_module.'_serial_id', '=', $this->table_module.'.'.$this->table_module.'_serial_id')
									->where('mcv.company_id', '=', $criteria['company_id']);
								});
				}
			}
			

			$filterCriteriaCore 	= $filterCriteria['result']['core']; // filter by core field type
			$filterCriteriaDate 	= $filterCriteria['result']['date']; // filter by date type
			$filterCriteriaCustom = $filterCriteria['result']['custom']; // filter by custom field type

			if ($myRecordOnlyCheck === TRUE)
		  {
		  	// if user choosen filter "You", then owner by sesson users_id login
				$query->where($this->table_module.'_owner', '=', $criteria['users_id']);
			}
			elseif (countCustom($filterView) > 0 && $filterViewName != 'Everyone' && $filterViewName != 'You') 
			{
				// get users statuc active or deactive
				$get_users = $this->model_users_class::select('users_status')
																							 ->leftjoin('users_company as comp','comp.users_id','=','users.id')
																							 ->where('id','=',$filterView['users_id'])
																							 ->where('company_id','=',$filterView['company_id'])
																							 ->first();

				// for check if users deactive
				if($get_users['users_status'] == 'deactive')
				{
					// get current users active
					$get_active = $this->model_view_class::select($this->table_module.'_view_serial_id', $this->table_module.'_view_name')
																								 ->where($this->table_module.'_view_name','=','Everyone')
																								 ->where('users_id','=', $criteria['users_id'])
																								 ->where('company_id','=', $criteria['company_id'])
																								 ->first();

					// update contact_view_serial_id into default
					$update = $this->model_view_checked_class::where('users_id','=',$criteria['users_id'])
																						 			->where('company_id','=',$criteria['company_id'])
																									->update([$this->table_module.'_view_serial_id' => $get_active[$this->table_module.'_view_serial_id']]);

					// change filter into default/Everyone when load data
					$filterView[$this->table_module.'_view_serial_id'] = $get_active[$this->table_module.'_view_serial_id'];
					$filterView[$this->table_module.'_view_name'] = 'Everyone';

					// change criteria into empty
					$filterCriteriaCore = array();
					$filterCriteriaDate = array();
					$filterCriteriaCustom = array();
				}
				
				$query_count = 1; // count query with left join
				//if user choosen filter, except filter 'Everyone' and 'You' 
				$checkFilterByOwner = in_array($this->table_module.'.'.$this->table_module.'_owner', array_column($filterCriteriaCore, 0)); 
				if ($checkFilterByOwner == TRUE) // if filter data by owner
				{
		      $key_filter_owner = array_search($this->table_module.'.'.$this->table_module.'_owner', array_column($filterCriteriaCore, 0)); // get key, position owner in array		      
		      if (is_array($filterCriteriaCore[$key_filter_owner][2]) && countCustom($filterCriteriaCore[$key_filter_owner][2]) > 0)
		      {
		      	// if filter data by multi owner
		      	if ($filterCriteriaCore[$key_filter_owner][1] == "=") // when owner by IS 
		      	{
			      	$query->whereIn($this->table_module.'.'.$this->table_module.'_owner', $filterCriteriaCore[$key_filter_owner][2]);

		      	}elseif ($filterCriteriaCore[$key_filter_owner][1] == "!=") // when owner by isnt
		      	{
			      	$query->whereNotIn($this->table_module.'.'.$this->table_module.'_owner', $filterCriteriaCore[$key_filter_owner][2]);
		      	}
		      }else
		      {
		      	// if filter data by single owner
						$query->where('users.name', $filterCriteriaCore[$key_filter_owner][1], $filterCriteriaCore[$key_filter_owner][2]);
		      }			
					unset($filterCriteriaCore[$key_filter_owner]); // remove owner in array, by position key
				}
				if (countCustom($filterCriteriaDate) > 0) // if filter data by date
				{
					$date_between 	= $this->date_between($filterCriteriaDate);
					$query->whereRaw($date_between);
				}
				if (countCustom($filterCriteriaCustom) > 0 ) // if filter data by custom fields
				{
					foreach ($filterCriteriaCustom as $value) 
					{
						if ($value[1] == "=" && is_array($value[2])) // operator is 
						{
							$query->whereIn($value[0], $value[2]);
						}
						elseif ($value[1] == "!=" && is_array($value[2])) // operator isn't
						{
							$query->whereNotIn($value[0], $value[2]);
						}
						elseif ( $value[1] === '==' ) // operator is_empty
						{
							$query->where(function ($query) use ($value) 
							{
								$query->where($value[0], '=', $value[2]);
								$query->orWhereNull($value[0]);
							});
						}
						elseif ( $value[1] === '!==' ) // operator is_not_empty
						{
							$query->where(function ($query) use ($value) 
							{
								$query->where($value[0], '!=', $value[2]);
								$query->whereNotNull($value[0]);
							});
						}
						else
						{
							$query->where($value[0], $value[1], $value[2]); // operator contains, start_with and end_with
						}
					}
				}
				$checkParentType 	= in_array($this->table_module.'.'.$this->table_module.'_parent_type', array_column($filterCriteriaCore, 0));
				if ($checkParentType == TRUE) // filter data by calls_parent_type
				{
					$keyParentType 	= array_search($this->table_module.'.'.$this->table_module.'_parent_type', array_column($filterCriteriaCore, 0));
					$parentTypeOpp 			= $filterCriteriaCore[$keyParentType][1]; // get operator
					$parentTypeKeyword 	= $filterCriteriaCore[$keyParentType][2]; // get keyword
					
					$query->where('sys_rel.rel_to_module', $parentTypeOpp, $parentTypeKeyword);
					unset($filterCriteriaCore[$keyParentType]);
				}
				$checkParentId 	= in_array($this->table_module.'.'.$this->table_module.'_parent_id', array_column($filterCriteriaCore, 0));
				if ($checkParentId == TRUE)  // filter data by calls_parent_id
				{
					$filterCriteriaCore = array_values($filterCriteriaCore); // reset key array, to 0 
					$keyParentId 				= array_search($this->table_module.'.'.$this->table_module.'_parent_id', array_column($filterCriteriaCore, 0));
					$parentIdOpp 				= $filterCriteriaCore[$keyParentId][1]; // get operator
					$parentIdKeyword 		= $filterCriteriaCore[$keyParentId][2]; // get keyword
					
					$query->having($this->table_module.'_parent_id', $parentIdOpp, $parentIdKeyword);
					unset($filterCriteriaCore[$keyParentId]);
				}
				// $query->where($filterCriteriaCore);
				if (countCustom($filterCriteriaCore) > 0 ) 
				{
					foreach ($filterCriteriaCore as $key => $value) 
					{
						if ($value[1] == "=" && is_array($value[2])) // operator is 
						{
							$query->whereIn($value[0], $value[2]);
						}
						elseif ($value[1] == "!=" && is_array($value[2])) // operator isn't
						{
							$query->whereNotIn($value[0], $value[2]);
						}
						elseif ( $value[1] === '==' ) 
						{
							$query->where(function ($query) use ($value) 
							{
								$query->where($value[0], '=', $value[2]);
								$query->orWhereNull($value[0]);
							});
						}
						elseif ( $value[1] === '!==' ) 
						{
							$query->where(function ($query) use ($value) 
							{
								$query->where($value[0], '!=', $value[2]);
								$query->whereNotNull($value[0]);
							});
						}
						elseif ($value[1] == "=" && ($value[0] == $this->table_module.".date_created" || $value[0] == $this->table_module.".date_modified" || $value[0] == $this->table_module.".".$this->table_module."_date_start" || $value[0] == $this->table_module.".".$this->table_module."_date_end"))
						{
							$date_range = explode(" - ", $value[2]);
							if (countCustom($date_range) > 1)
							{
								$date_start = date('Y-m-d', strtotime($date_range[0])).' 00:00:00'; 
								$date_end 	= date('Y-m-d', strtotime($date_range[1])).' 23:59:59';

								$query->whereBetween($value[0], [$date_start, $date_end]);
							}
						}
						elseif ($value[1] == "!=" && ($value[0] == $this->table_module.".date_created" || $value[0] == $this->table_module.".date_modified" || $value[0] == $this->table_module.".".$this->table_module."_date_start" || $value[0] == $this->table_module.".".$this->table_module."_date_end"))
						{
							$date_range = explode(" - ", $value[2]);
							if (countCustom($date_range) > 1)
							{
								$date_start = date('Y-m-d', strtotime($date_range[0])).' 00:00:00'; 
								$date_end 	= date('Y-m-d', strtotime($date_range[1])).' 23:59:59';

								$query->whereNotBetween($value[0], [$date_start, $date_end]);
							}
						}
						else
						{
							$query->where($value[0], $value[1], $value[2]); // search data in core fields
						}
					}
				}
				// end
			}
		} 


		$query->where($this->table_module.'.company_id', '=', $criteria['company_id']);
		$query->where($this->table_module.'.deleted', '=', Config('setting.NOT_DELETED'));
		# END

		# PAGINATION MANUALLY
		$perPage 			= $criteria['data_per_page'];
		
		$query->orderBy($criteria['order_by'], $criteria['type_sort']);
		$items 				= $query->limit($perPage)->get();
		$items 				= json_decode(json_encode($items), True);

		return $items;

	}

	# Created By Pratama Gilang
  # 14-01-2019
  # For Count All data when ready export
  # running for url /v1/leads/exportAll
  # running on controller exportAll
	public function countAllListData($company_id=0)
	{
		$count = $this->model_class::where('company_id', '=', $company_id)
									->where('deleted', '=', Config('setting.NOT_DELETED'))
									->count();

		return $count;
	}


	# Created By Pratama Gilang
  # 14-01-2019
  # For though data Export All
  # running for url /v1/leads/exportAll
  # running on controller exportAll
  # return [['First Name', 'Last Name']]
	public function dataProcessExport($fields=array(), $data=array())
	{
		$label = array();
		$result_label = array();
		$result_data = array();
		$result = array();

		foreach ($data as $key => $value) 
		{
			foreach ($fields as $key2 => $value2) 
			{
				if (isset($value2[$this->table_module.'_fields_name'])) 
				{
					if (isset($value2[$this->table_module.'_fields_status']) && $value2[$this->table_module.'_fields_status'] !== Config('setting.fields_status_inactive'))
					{
						if (
							$value2[$this->table_module.'_fields_name'] != $this->table_module.'_last_module'
							AND $value2[$this->table_module.'_fields_name'] != $this->table_module.'_last_date'
							AND $value2[$this->table_module.'_fields_name'] != $this->table_module.'_last_id') 
						{
							$label[$value2[$this->table_module.'_fields_name']] = $value2[$this->table_module.'_fields_label'];					
						}
					}
				}
				elseif (isset($value2[$this->table_module.'_custom_fields_name']))
				{
					if (isset($value2[$this->table_module.'_custom_fields_status']) && $value2[$this->table_module.'_custom_fields_status'] !== Config('setting.custom_fields_status_inactive'))
					{
						$label['custommm_'.$value2[$this->table_module.'_custom_fields_serial_id']] = '- '.$value2[$this->table_module.'_custom_fields_label'];
					}
				}
			}	

			foreach ($value as $key3 => $value3) 
			{
				if ($key3 == 'documents') 
				{
					$label['documents'] = 'Documents';
				}
				elseif ($key3 == $this->table_module.'_parent_id')
				{
					$label['Organization'] = "Organization";
					$label['Org Unique ID'] = "Org Unique ID";
				}
		        else if ($key3 == $this->table_module.'_uuid') 
		        {
		          $label[$this->table_module.'_uuid'] = ucfirst($this->table_module).' UUID';
		        }
		        else if ($key3 == $this->table_module.'_uuid_link')
		        {
		          $label[$this->table_module.'_uuid_link'] = 'Detail ' . ucfirst($this->table_module);
		        }
		        elseif ($key3 == 'parent_uuid')
		        {
		          $label['parent_uuid'] = 'Related ID';
		        }
			}
		}

		// # set header excel
		if (countCustom($label) > 0) 
		{
			foreach ($label as $key => $value) 
			{
				# set header excel
				$result[0][] = $value;
				$i = 1;
				foreach ($data as $key_data => $value_data) 
				{
					foreach ($value_data as $key_data2 => $value_data2) 
					{
						if ($key == $key_data2) 
						{
              if ($key == $this->table_module.'_uuid_link') {

                $toDetail = "";
                if(env("APP_ENV") == "local") {
                  $toDetail = 'http://localhost/frontend/'.$this->table_module.'/detail/';
                } else {
                  $toDetail = 'https://crmv5.barantum.com/'.$this->table_module.'/detail/';
                }
                $result[$i][] = $toDetail.$value_data2;

              } else {
                $result[$i][] = $value_data2; 
              }
						}
					}
					$i++;
				}
			}
		}

		return $result;	
	}

	public function ExplodeValidationEdit($form, $parameter='_fields_validation')
	{
		foreach ($form as $key => $value) 
		{
			if ( $key == $this->table_module.$parameter ) 
			{
				if ( !isEmpty($value) ) 
				{
					$explode = explode('|', $value);

					$form[$this->table_module.$parameter] = $explode;
				}
				else
				{
					// define start
					$form[$this->table_module.$parameter] = '';
				}
			}
		}

		return $form;
	}

	public function list_options($dropdown_name)
	{
		$result = array();

		$dropdown_serial_id = $this->model_dropdown_class::select('dropdown_serial_id')->where('dropdown_name', '=', $dropdown_name)->first();

		if ( countCustom($dropdown_serial_id) > 0 ) 
		{
			$list_options = $this->model_dropdown_options_class::where('dropdown_serial_id', '=', $dropdown_serial_id['dropdown_serial_id'])->get();

			$options = array();
			if ( countCustom($list_options) > 0 ) 
			{
				$options = $list_options->toArray();
			}
			$result = array(
												'list' => $options,
												'dropdown_serial_id' => $dropdown_serial_id['dropdown_serial_id'],
											);
		}

		return $result;
	}

	public function listOptionsGroup($dropdown_name)
	{
		$sys = new sys();

		$result = array();
		$group_label = array();
		$options = array();
		$radio = array();

		$dropdown_serial_id = $this->model_dropdown_class::select('dropdown_serial_id')->where('dropdown_name', '=', $dropdown_name)->first();

		if ( countCustom($dropdown_serial_id) > 0 ) 
		{
			$list_options = $this->model_dropdown_options_class::where('dropdown_serial_id', '=', $dropdown_serial_id['dropdown_serial_id'])->get();
			if ( countCustom($list_options) > 0 ) 
			{
				$list_options = $list_options->toArray();
				
				$dropdown_options_group = $sys->unique_multidim_array($list_options, 'dropdown_options_group');

				# 1. get dropdown_group
				foreach ($dropdown_options_group as $key => $value) 
				{
					$group_label[] = $value['dropdown_options_group'];
				}

				# 2. option new
				foreach ($list_options as $key => $value) 
				{
					if (isset($value['dropdown_options_group'])) 
	        {
	            foreach ($group_label as $key2 => $value2) 
	            {
	                if ($value['dropdown_options_group'] == $value2) 
	                {
	                		$radio[$key2] 		= 'new';
	                		$options[$key2][] = $value['dropdown_options_value'];
	                }
	            }
	        } 
				}

				$result = array(
													'dropdown_option_group' => $group_label,
													'radio' => $radio,
													'options_new' => $options,
													'dropdown_serial_id' => $dropdown_serial_id['dropdown_serial_id'],
												);
			}
		}

		return $result;

	}

	# Created By Pratama Gilang
  # 14-01-2019
  # For Export Get form
	public function GetFormCustomizeExport($company_id=0)
	{
		$sys = new sys();

		$core_fields = $this->model_fields_class::get()->toArray();

		$core_change = $this->model_fields_class::select('b.*')
                           	->leftjoin($this->table_module.'_fields_change as b', 'b.'.$this->table_module.'_fields_serial_id', '=', $this->table_module.'_fields.'.$this->table_module.'_fields_serial_id')
                           	->where('b.company_id', '=', $company_id)
		                        ->get();

		if (countCustom($core_change) > 0 )
		{
			$temp_core_fields = $core_fields;
			foreach ($core_change as $key => $value) 
			{
				$key_change = array_search($value[$this->table_module.'_fields_serial_id'], array_column($temp_core_fields, $this->table_module.'_fields_serial_id'));
				if ( $key_change !== false )
				{
					$core_fields[$key_change][$this->table_module.'_fields_label'] 				= $value[$this->table_module.'_fields_change_label'];
					$core_fields[$key_change][$this->table_module.'_fields_validation'] 	= $value[$this->table_module.'_fields_change_validation'];
					$core_fields[$key_change][$this->table_module.'_fields_status'] 			= $value[$this->table_module.'_fields_change_status'];
					$core_fields[$key_change][$this->table_module.'_fields_options'] 			= $value[$this->table_module.'_fields_change_options'];
					$core_fields[$key_change][$this->table_module.'_fields_quick'] 				= $value[$this->table_module.'_fields_change_quick'];
				}
			}
		}

		foreach ($core_fields as $key => $value) 
		{
				$core_fields[$key][$this->table_module.'_fields_sorting_put_header'] 	= Config('setting.fields_sorting_not_put_header');
		}
	

		$select = $this->table_module.'_custom_fields_serial_id,'.
				$this->table_module.'_custom_fields_name,'.
				$this->table_module.'_custom_fields_label,'.
				$this->table_module.'_custom_fields_data_type,'.
				$this->table_module.'_custom_fields_input_type,'.
				$this->table_module.'_custom_fields_function,'.
				$this->table_module.'_custom_fields_options,'.
				$this->table_module.'_custom_fields_validation,'.
				$this->table_module.'_custom_fields_sorting as '.$this->table_module.'_fields_sorting,'.
				$this->table_module.'_custom_fields_status,'.
				$this->table_module.'_custom_fields_quick,'.
				$this->table_module.'_custom_fields_put_header';

		$custom_fields = $this->model_custom_fields_class::select(DB::raw($select))
																											->where( $this->table_module.'_custom_fields_status', '=', Config('setting.custom_fields_status_active'))
																											->where( 'company_id', '=', $company_id)
																											->get()->toArray();

		$result = array_merge($core_fields, $custom_fields);
		
		$result = $this->GetFormActive($result);

		// This is for sorting, sorting core fields and custom fields
		$result = $sys->array_sort_custom( $result, $this->table_module.'_fields_sorting', SORT_ASC);
		
		return $result;
	}

	# Created By Pratama Gilang
  # 15-01-2019
  # For Export Get form and manupalation data array
	public function GetFormExport($company_id=0)
	{
		$sys = new sys();

		// Define variable
		$result = array();

		$core_export = $this->model_export_class::where('company_id', '=', $company_id) ->get();

		if ( countCustom($core_export) > 0 ) 
		{
			$core_fields = $this->model_fields_class::get()->toArray();
			
			//ADD BY AGUNG -> 04/04/2019
			//CHECK CORE FIELDS CHANGE AND REPLACE
			$core_change = $this->model_fields_class::select('b.*')
															->leftjoin($this->table_module.'_fields_change as b', 'b.'.$this->table_module.'_fields_serial_id', '=', $this->table_module.'_fields.'.$this->table_module.'_fields_serial_id')
															->where('b.company_id', '=', $company_id)
															->get();

			if ( countCustom($core_change) > 0 )
			{
				$temp_core_fields = $core_fields;
				foreach ($core_change as $key => $value) 
				{
					$key_change = array_search($value[$this->table_module.'_fields_serial_id'], array_column($temp_core_fields, $this->table_module.'_fields_serial_id'));
					if ( $key_change !== false )
					{
							$core_fields[$key_change][$this->table_module.'_fields_label'] 				= $value[$this->table_module.'_fields_change_label'];
							$core_fields[$key_change][$this->table_module.'_fields_validation'] 	= $value[$this->table_module.'_fields_change_validation'];
							$core_fields[$key_change][$this->table_module.'_fields_status'] 			= $value[$this->table_module.'_fields_change_status'];
							$core_fields[$key_change][$this->table_module.'_fields_options'] 			= $value[$this->table_module.'_fields_change_options'];
							$core_fields[$key_change][$this->table_module.'_fields_quick'] 				= $value[$this->table_module.'_fields_change_quick'];
					}
				}
			}

			// END CHECK CORE FIELDS CHANGE AND REPLACE

			$total = countCustom($core_fields);

			$select = $this->table_module.'_custom_fields_serial_id,'.
					$this->table_module.'_custom_fields_name,'.
					$this->table_module.'_custom_fields_label,'.
					$this->table_module.'_custom_fields_data_type,'.
					$this->table_module.'_custom_fields_input_type,'.
					$this->table_module.'_custom_fields_function,'.
					$this->table_module.'_custom_fields_options,'.
					$this->table_module.'_custom_fields_validation,'.
					$this->table_module.'_custom_fields_sorting as '.$this->table_module.'_fields_sorting,'.
					$this->table_module.'_custom_fields_status,'.
					$this->table_module.'_custom_fields_quick,'.
					$this->table_module.'_custom_fields_put_header';

			$custom_fields = $this->model_custom_fields_class::select(DB::raw($select))
																												->where( $this->table_module.'_custom_fields_status', '=', Config('setting.custom_fields_status_active'))
																												->where( 'company_id', '=', $company_id)
																												->get()->toArray();

			$result = array_merge($core_fields, $custom_fields);
			

			if ( countCustom($core_export) > 0 )
			{
				$core_export = $core_export->toArray();
				foreach ($core_export as $key => $value) 
				{
					if ( $value[$this->table_module.'_export_fields_type'] == Config('setting.export_fields_type_core') ) 
					{
						$key_sorting 				= array_search($value[$this->table_module.'_fields_serial_id'], array_column($result, $this->table_module.'_fields_serial_id'));
						if ( $key_sorting !== false )
						{
							$result[$key_sorting][$this->table_module.'_fields_sorting'] 			= $value[$this->table_module.'_export_sorting'];
							$result[$key_sorting][$this->table_module.'_fields_put_header'] 	= $value[$this->table_module.'_export_checked'];
						}
						$result[$key_sorting][$this->table_module.'_export_fields_type'] = $value[$this->table_module.'_export_fields_type'];
						$result[$key_sorting][$this->table_module.'_export_checked'] = $value[$this->table_module.'_export_checked'];
					}
					elseif ( $value[$this->table_module.'_export_fields_type'] == Config('setting.export_fields_type_custom') ) 
					{
						$key_sorting_custom = array_search($value[$this->table_module.'_fields_serial_id'], array_column($result, $this->table_module.'_custom_fields_serial_id'));
						$key_sorting_custom = $key_sorting_custom + $total;
						if ( $key_sorting_custom !== false )
						{
							$result[$key_sorting_custom][$this->table_module.'_fields_sorting'] 		= $value[$this->table_module.'_export_sorting'];
							$result[$key_sorting_custom][$this->table_module.'_fields_put_header'] 	= $value[$this->table_module.'_export_checked'];
						}
						$result[$key_sorting_custom][$this->table_module.'_export_fields_type'] = $value[$this->table_module.'_export_fields_type'];
						$result[$key_sorting_custom][$this->table_module.'_export_checked'] = $value[$this->table_module.'_export_checked'];
					}
				}
			}
			else
			{
				foreach ($result as $key => $value) 
				{
						$result[$key][$this->table_module.'_fields_put_header'] 	= 0;
						$result[$key][$this->table_module.'_export_fields_type']	= null;
						$result[$key][$this->table_module.'_export_checked']		= null;
				}
			}

			// This is for selection form only status active
			$result = $this->GetFormActive($result);	
			
			$result = $sys->array_sort_custom( $result, $this->table_module.'_fields_sorting', SORT_ASC);
		}

		return $result;
	}

	public function unset_custom_fields_notactive($result=array())
	{
		foreach ($result as $key => $value) 
		{
			if ( isset($value[$this->table_module.'_custom_fields_status']) AND $value[$this->table_module.'_custom_fields_status']== Config('setting.custom_fields_status_inactive') )
			{
				unset($result[$key]);
			}
		}

		return $result;
	}

	# Created By Pratama Gilang
  # 15-01-2019
  # For Cek export table (etc : leads_export, deals_export, ..)
  # to use customize export
	public function check_export($company_id=0)
	{
		$result = array();

		$query = $this->model_export_class::where('company_id', '=', $company_id)
						->orderBy($this->table_module.'_export_sorting', 'ASC')
						->get();

		if (countCustom($query) > 0 ) 
		{
			$result = $query->toArray();
		}

		return $result;
	}
	# Created By Pratama Gilang
  # 16-01-2019
  # For save customize export
	public function save_customize_export($request=array(), $company_id=0)
	{
		if ( isset($request[$this->table_module.'_export']) AND $request[$this->table_module.'_export'] == Config('setting.CUSTOMIZE_EXPORT_STANDARD') ) 
		{
			// Delete export
			$this->model_export_class::where('company_id', '=', $company_id)->delete();
		}
		elseif ( isset($request[$this->table_module.'_export']) AND $request[$this->table_module.'_export'] == Config('setting.CUSTOMIZE_EXPORT_CUSTOM') )
		{
			if ( !isset($request['checkbox_custom']) AND !isset($request['checkbox_core']) ) 
			{
				// Delete export
				$this->model_export_class::where('company_id', '=', $company_id)->delete();
			}
			else
			{
				// Delete export
				$this->model_export_class::where('company_id', '=', $company_id)->delete();

				foreach ($request['layout'] as $key => $value) 
				{
					$trim_custom = substr($value, -1, 1); // output c, if c (custom fields)
			
					if ( $trim_custom != 'c' )
					{
						$export_insert[$this->table_module.'_export_sorting']			= $key;
						$export_insert[$this->table_module.'_fields_serial_id']		= $value;
						$export_insert[$this->table_module.'_export_fields_type']	= Config('setting.export_fields_type_core');
						$export_insert['company_id']															= $company_id;
						// Insert
						$this->model_export_class::create($export_insert);

					}
					else
					{
						// For Update Custom
						$value = substr($value, 0, -1);
						$export_insert[$this->table_module.'_export_sorting']			= $key;
						$export_insert[$this->table_module.'_fields_serial_id']		= $value;
						$export_insert[$this->table_module.'_export_fields_type']	= Config('setting.export_fields_type_custom');
						$export_insert['company_id']															= $company_id;
						// Insert
						$this->model_export_class::create($export_insert);
					}
				}

				


				// Insert export ( CORE )
				if ( isset($request['checkbox_core']) AND countCustom($request['checkbox_core']) > 0 ) 
				{
					foreach ($request['checkbox_core'] as $key => $value) 
					{
						foreach ($request['layout'] as $key2 => $value2) 
						{
							if ( $value == $value2 ) 
							{
								$export_core[$this->table_module.'_export_checked']			= Config('setting.export_not_checked');

								$this->model_export_class::where($this->table_module.'_fields_serial_id', '=', $value)
																				->where($this->table_module.'_export_fields_type', '=', Config('setting.export_fields_type_core'))
																				->where('company_id', '=', $company_id)
																				->update($export_core);
							}
						}
					}
				}

				// Insert export ( CUSTOM )
				if ( isset($request['checkbox_custom']) AND countCustom($request['checkbox_custom']) > 0 ) 
				{
					foreach ($request['checkbox_custom'] as $key => $value) 
					{
						foreach ($request['layout'] as $key2 => $value2) 
						{
							$trim_custom = substr($value2, -1, 1); // output c, if c (custom fields)

							if ( $trim_custom == 'c' )
							{
								// For Update Custom
								$value2 = substr($value2, 0, -1);
								if ( $value == $value2 ) 
								{
									$export_core[$this->table_module.'_export_checked']			= Config('setting.export_not_checked');

									$this->model_export_class::where($this->table_module.'_fields_serial_id', '=', $value)
																					->where($this->table_module.'_export_fields_type', '=', Config('setting.export_fields_type_custom'))
																					->where('company_id', '=', $company_id)
																					->update($export_core);
								}
							}
						}
					}
				}
			}
		}

		return true;
	}

	# Created By Pratama Gilang
  # 16-01-2019
  # For get data before export to xlsx
	public function DownloadExport($data=array(), $company_id=0)
	{
		// Define variable
		$sys = new sys();
		$a 							= 'a'; 			// For alias join table
		$select 				= array();	// For select query
		$count_custom 	= array();	// For foreach join custom values
		$export 				= array();	// For result export

		// Check Export
		$check_export = $this->check_export($company_id);
		
		if ( countCustom($check_export) > 0 ) 
		{
			$check_export_form    = $this->GetFormExport($company_id); // Get core fields & custom fields
			if ( countCustom($check_export_form) > 0 ) 
			{
				$form = array();
				foreach ($check_export_form as $key => $value) 
				{
					if (isset($value[$this->table_module.'_fields_put_header']) AND $value[$this->table_module.'_fields_put_header'] == 1) 
					{
						$form[] = $value;
					}
				}

				$fields = $form;
			}

			foreach ($check_export as $key => $value) 
			{
				if ( $value[$this->table_module.'_export_fields_type'] == Config('setting.export_fields_type_core') AND $value[$this->table_module.'_export_checked'] == Config('setting.export_not_checked') ) 
				{
					$query_fields_name 	= $this->model_fields_class::where($this->table_module.'_fields_serial_id', '=', $value[$this->table_module.'_fields_serial_id'])->first();
					if ( $query_fields_name[$this->table_module.'_fields_name'] == $this->table_module.'_parent_type' ) 
					{
						$select[]	= "(
												CASE   
													WHEN sys_rel.rel_to_module = 'leads' THEN 'Lead'
													WHEN sys_rel.rel_to_module = 'contacts' THEN 'Contact'
													WHEN sys_rel.rel_to_module = 'org'  THEN 'Organization'
													WHEN sys_rel.rel_to_module is NULL THEN ''
												END
												) as ".$this->table_module."_parent_type";
					}
					elseif ( $query_fields_name[$this->table_module.'_fields_name'] == $this->table_module.'_parent_id' ) 
					{
						$select[]	= "(
												CASE   
													WHEN sys_rel.rel_to_module = 'leads' THEN ( SELECT CONCAT(COALESCE(leads.leads_salutation, ''), ' ', COALESCE(leads.leads_first_name, ''),' ', COALESCE(leads.leads_last_name, '')) FROM leads WHERE leads.leads_serial_id = sys_rel.rel_to_id LIMIT 1)  
													WHEN sys_rel.rel_to_module = 'contacts' THEN ( SELECT CONCAT(COALESCE(contacts.contacts_salutation, ''),' ', COALESCE(contacts.contacts_first_name, ''),' ', COALESCE(contacts.contacts_last_name, ''),' @', COALESCE(org.org_name, '')) FROM contacts LEFT JOIN sys_rel zz ON zz.rel_from_id = contacts.contacts_serial_id AND zz.rel_from_module = 'contacts' LEFT JOIN org ON org.org_serial_id = zz.rel_to_id WHERE contacts.contacts_serial_id = sys_rel.rel_to_id LIMIT 1)  
													WHEN sys_rel.rel_to_module = 'org'  THEN ( SELECT org_name FROM org WHERE org.org_serial_id = sys_rel.rel_to_id LIMIT 1) 
													WHEN sys_rel.rel_to_module is NULL THEN ''
												END
												) as ".$this->table_module."_parent_id";
						$select[]	= "(
													CASE   
														WHEN sys_rel.rel_to_module = 'leads' THEN ( SELECT leads_unique_id FROM leads WHERE leads.leads_serial_id = sys_rel.rel_to_id LIMIT 1)  
														WHEN sys_rel.rel_to_module = 'contacts' THEN ( SELECT contacts_unique_id FROM contacts LEFT JOIN sys_rel zz ON zz.rel_from_id = contacts.contacts_serial_id AND zz.rel_from_module = 'contacts' LEFT JOIN org ON org.org_serial_id = zz.rel_to_id WHERE contacts.contacts_serial_id = sys_rel.rel_to_id LIMIT 1)
														WHEN sys_rel.rel_to_module = 'org'  THEN ( SELECT org_unique_id FROM org WHERE org.org_serial_id = sys_rel.rel_to_id LIMIT 1) 
														WHEN sys_rel.rel_to_module = 'projects' THEN ( SELECT projects_unique_id FROM projects WHERE projects.projects_serial_id = sys_rel.rel_to_id LIMIT 1 )
														WHEN sys_rel.rel_to_module is NULL THEN ''
													END
													) as parent_uuid";
					}
					elseif ( $query_fields_name[$this->table_module.'_fields_name'] == $this->table_module.'_owner' ) 
					{
						$select[]		= 'users.name as '.$this->table_module.'_owner';
					}
					elseif ( $query_fields_name[$this->table_module.'_fields_name'] == 'created_by' ) 
					{
						$select[]		= 'users_created.name as created_by';
					}
					elseif ( $query_fields_name[$this->table_module.'_fields_name'] == 'modified_by' ) 
					{
						$select[]		= 'users_modified.name as modified_by';
					}
					elseif ( $query_fields_name[$this->table_module.'_fields_name'] == 'date_created' )
					{
						$select[]		= "DATE_FORMAT(DATE_ADD(".$this->table_module.".date_created, INTERVAL 7 HOUR), '%Y-%m-%d %H:%i') as date_created";
					}
					elseif ( $query_fields_name[$this->table_module.'_fields_name'] == 'date_modified' )
					{
						$select[]		= "DATE_FORMAT(DATE_ADD(".$this->table_module.".date_modified, INTERVAL 7 HOUR), '%Y-%m-%d %H:%i') as date_modified";
					}
					elseif ( $query_fields_name[$this->table_module.'_fields_name'] == $this->table_module.'_date_start' )
					{
						$select[]		= "DATE_FORMAT(DATE_ADD(".$this->table_module.".".$this->table_module."_date_start, INTERVAL 7 HOUR), '%Y-%m-%d %H:%i') as ".$this->table_module."_date_start";
					}
					elseif ( $query_fields_name[$this->table_module.'_fields_name'] == $this->table_module.'_date_end' )
					{
						$select[]		= "DATE_FORMAT(DATE_ADD(".$this->table_module.".".$this->table_module."_date_end, INTERVAL 7 HOUR), '%Y-%m-%d %H:%i') as ".$this->table_module."_date_end";
					}
					elseif ( $query_fields_name[$this->table_module.'_fields_name'] == 'deals_serial_id' )
					{
						$select[]		= "deals.deals_name as deals_serial_id";
					}
					elseif ( $query_fields_name[$this->table_module.'_fields_name'] == 'projects_serial_id' )
					{
						$select[]		= "projects.projects_name as projects_serial_id";
					}
					elseif ( $query_fields_name[$this->table_module.'_fields_name'] == 'issue_serial_id' )
					{
						$select[]		= "issue.issue_name as issue_serial_id";
					}
					else
					{
						$select[] 	= $this->table_module.'.'.$query_fields_name[$this->table_module.'_fields_name'];
					}
					
				}
				elseif ( $value[$this->table_module.'_export_fields_type'] == Config('setting.export_fields_type_custom') AND $value[$this->table_module.'_export_checked'] == Config('setting.export_not_checked') ) 
				{
					$library_values_maps_by_id = $sys->library_values_maps_by_id($value[$this->table_module.'_fields_serial_id'], $this->table_module, $company_id);
					if (!isEmpty($library_values_maps_by_id)) 
					{
						$select[] 					= 'mcv.'.$library_values_maps_by_id.' as custommm_'.$value[$this->table_module.'_fields_serial_id'];
					}

					$count_custom[$a]		= $a++;
				}
			}

			$select[] = "GROUP_CONCAT((SELECT documents.documents_name FROM documents WHERE documents.documents_serial_id = sys_rel_doc.rel_from_id) SEPARATOR ' ||||| ') as documents";
		}
		else
		{
			$form 				= $this->GetAllCoreFields();
			$form 				= $this->GetCoreFieldsChange($form, $company_id); //get core fields from tbl module_fields_change
			$form 				=	$this->GetFormActive($form);
			$form_custom 		= $this->GetCustomFields($company_id);
			$fields 			= array_merge($form, $form_custom);
			
			if (countCustom($form) > 0 ) 
			{
				foreach ($form as $key => $value) 
				{
					if ( $value[$this->table_module.'_fields_status'] == Config('setting.fields_status_active') )
					{
						if ( $value[$this->table_module.'_fields_name'] == $this->table_module.'_parent_type' ) 
						{
							$select[]	= "(
													CASE   
														WHEN sys_rel.rel_to_module = 'leads' THEN 'Lead'
														WHEN sys_rel.rel_to_module = 'contacts' THEN 'Contact'
														WHEN sys_rel.rel_to_module = 'org'  THEN 'Organization'
														WHEN sys_rel.rel_to_module is NULL THEN ''
													END
													) as ".$this->table_module."_parent_type";
						}
						elseif ( $value[$this->table_module.'_fields_name'] == $this->table_module.'_parent_id' ) 
						{
							$select[]	= "(
													CASE   
														WHEN sys_rel.rel_to_module = 'leads' THEN ( SELECT CONCAT(COALESCE(leads.leads_salutation, ''), ' ', COALESCE(leads.leads_first_name, ''),' ', COALESCE(leads.leads_last_name, '')) FROM leads WHERE leads.leads_serial_id = sys_rel.rel_to_id LIMIT 1)  
														WHEN sys_rel.rel_to_module = 'contacts' THEN ( SELECT CONCAT(COALESCE(contacts.contacts_salutation, ''),' ', COALESCE(contacts.contacts_first_name, ''),' ', COALESCE(contacts.contacts_last_name, ''),' @', COALESCE(org.org_name, '')) FROM contacts LEFT JOIN sys_rel zz ON zz.rel_from_id = contacts.contacts_serial_id AND zz.rel_from_module = 'contacts' LEFT JOIN org ON org.org_serial_id = zz.rel_to_id WHERE contacts.contacts_serial_id = sys_rel.rel_to_id LIMIT 1)
														WHEN sys_rel.rel_to_module = 'org'  THEN ( SELECT org_name FROM org WHERE org.org_serial_id = sys_rel.rel_to_id LIMIT 1) 
														WHEN sys_rel.rel_to_module is NULL THEN ''
													END
													) as ".$this->table_module."_parent_id";
							$select[]	= "(
													CASE   
														WHEN sys_rel.rel_to_module = 'leads' THEN ( SELECT leads_unique_id FROM leads WHERE leads.leads_serial_id = sys_rel.rel_to_id LIMIT 1)  
														WHEN sys_rel.rel_to_module = 'contacts' THEN ( SELECT contacts_unique_id FROM contacts LEFT JOIN sys_rel zz ON zz.rel_from_id = contacts.contacts_serial_id AND zz.rel_from_module = 'contacts' LEFT JOIN org ON org.org_serial_id = zz.rel_to_id WHERE contacts.contacts_serial_id = sys_rel.rel_to_id LIMIT 1)
														WHEN sys_rel.rel_to_module = 'org'  THEN ( SELECT org_unique_id FROM org WHERE org.org_serial_id = sys_rel.rel_to_id LIMIT 1) 
														WHEN sys_rel.rel_to_module = 'projects' THEN ( SELECT projects_unique_id FROM projects WHERE projects.projects_serial_id = sys_rel.rel_to_id LIMIT 1 )
														WHEN sys_rel.rel_to_module is NULL THEN ''
													END
													) as parent_uuid";
						}
						elseif ( $value[$this->table_module.'_fields_name'] == $this->table_module.'_owner' ) 
						{
							$select[]		= 'users.name as '.$this->table_module.'_owner';
						}
						elseif ( $value[$this->table_module.'_fields_name'] == 'created_by' ) 
						{
							$select[]		= 'users_created.name as created_by';
						}
						elseif ( $value[$this->table_module.'_fields_name'] == 'modified_by' ) 
						{
							$select[]		= 'users_modified.name as modified_by';
						}
						elseif ( $value[$this->table_module.'_fields_name'] == 'date_created' )
						{
							$select[]		= "DATE_FORMAT(DATE_ADD(".$this->table_module.".date_created, INTERVAL 7 HOUR), '%Y-%m-%d %H:%i') as date_created";
						}
						elseif ( $value[$this->table_module.'_fields_name'] == 'date_modified' )
						{
							$select[]		= "DATE_FORMAT(DATE_ADD(".$this->table_module.".date_modified, INTERVAL 7 HOUR), '%Y-%m-%d %H:%i') as date_modified";
						}
						elseif ( $value[$this->table_module.'_fields_name'] == $this->table_module.'_date_start' )
						{
							$select[]		= "DATE_FORMAT(DATE_ADD(".$this->table_module.".".$this->table_module."_date_start, INTERVAL 7 HOUR), '%Y-%m-%d %H:%i') as ".$this->table_module."_date_start";
						}
						elseif ( $value[$this->table_module.'_fields_name'] == $this->table_module.'_date_end' )
						{
							$select[]		= "DATE_FORMAT(DATE_ADD(".$this->table_module.".".$this->table_module."_date_end, INTERVAL 7 HOUR), '%Y-%m-%d %H:%i') as ".$this->table_module."_date_end";
						}
						elseif ( $value[$this->table_module.'_fields_name'] == 'deals_serial_id' )
						{
							$select[]		= "deals.deals_name as deals_serial_id";
						}
						elseif ( $value[$this->table_module.'_fields_name'] == 'projects_serial_id' )
						{
							$select[]		= "projects.projects_name as projects_serial_id";
						}
						elseif ( $value[$this->table_module.'_fields_name'] == 'issue_serial_id' )
						{
							$select[]		= "issue.issue_name as issue_serial_id";
						}
						elseif ( $value[$this->table_module.'_fields_name'] == 'tickets_serial_id' )
						{
							$select[]		= "IF((tickets.tickets_unique_id or IF(tickets.tickets_unique_id or tickets.tickets_unique_id != '',true,false)) or (tickets.tickets_name or IF(tickets.tickets_name or tickets.tickets_name != '',true,false)), CONCAT(COALESCE(CONCAT('[',NULLIF(tickets.tickets_unique_id,''),']'),'[No Unique ID]'),' - ',COALESCE(NULLIF(tickets.tickets_name,''),'No Subject')) , '-' ) as tickets_serial_id";
						}
						else
						{
							$select[] 	= $this->table_module.'.'.$value[$this->table_module.'_fields_name'];
						}
					}
				}
			}

			if (countCustom($form_custom) > 0 ) 
			{
				foreach ($form_custom as $key => $value) 
				{
					$select[] 					= 'mcv.'.$value[$this->table_module.'_custom_values_maps'].' as custommm_'.$value[$this->table_module.'_custom_fields_serial_id'];

					$count_custom[$a]		= $a++;
				}
			}

			$select[] = "GROUP_CONCAT((SELECT documents.documents_name FROM documents WHERE documents.documents_serial_id = sys_rel_doc.rel_from_id) SEPARATOR ' ||||| ') as documents";
		}

    $select[] = $this->table_module.".".$this->table_module."_uuid AS ".$this->table_module."_uuid";
    $select[] = $this->table_module.".".$this->table_module."_uuid AS ".$this->table_module."_uuid_link";

		$select = implode (", ", $select);

		$query = $this->model_class::select(DB::raw($select));

		if (countCustom($count_custom) > 0 ) 
		{
			$query->leftjoin($this->table_module.'_custom_values_ as mcv', function($join) use ($company_id)
							{
								$join->on('mcv.'.$this->table_module.'_serial_id', '=', $this->table_module.'.'.$this->table_module.'_serial_id')
								->where('mcv.company_id', '=', $company_id);
							});
		}

		$result = $query->leftjoin('sys_rel as sys_rel_doc', function($join)
						        { 
						            $join->on('sys_rel_doc.rel_to_id', '=', $this->table_module.".".$this->table_module."_serial_id")
						            ->where('sys_rel_doc.rel_to_module', '=', $this->table_module)
						            ->where('sys_rel_doc.rel_from_module', '=', "documents");
						        })
										->leftjoin('sys_rel', function($join)
						        { 
						            $join->on('sys_rel.rel_from_id', '=', $this->table_module.'.'.$this->table_module.'_serial_id')
						            ->where('sys_rel.rel_from_module', '=', $this->table_module);
						        })
										->leftjoin('users', 'users.id', '=', $this->table_module.'.'.$this->table_module.'_owner')
										->leftjoin('users as users_created', 'users_created.id', '=', $this->table_module.'.created_by')
										->leftjoin('users as users_modified', 'users_modified.id', '=', $this->table_module.'.modified_by')
										->leftjoin('deals', 'deals.deals_serial_id', '=', $this->table_module.'.deals_serial_id')
										->leftjoin('projects', 'projects.projects_serial_id', '=', $this->table_module.'.projects_serial_id')
										->leftjoin('issue', 'issue.issue_serial_id', '=', $this->table_module.'.issue_serial_id')
										->leftjoin('tickets', 'tickets.tickets_serial_id', '=', $this->table_module.'.tickets_serial_id')
										->where($this->table_module.'.company_id', '=', $company_id)
										->where($this->table_module.'.deleted', '=', Config('setting.NOT_DELETED'))
										->whereIn($this->table_module.'.'.$this->table_module.'_uuid', $data)
										->GroupBy($this->table_module.'.'.$this->table_module.'_serial_id')
										->get();

		if ( countCustom($result) > 0 ) 
		{
			$result = $result->toArray();
	
			foreach ($result as $key => $value) 
			{
				$hard_org = '';
				$org_unique_id = '' ;

				foreach ($value as $key2 => $value2) 
				{
					$trim_key2 = substr($key2, 0, 9); // Result custommm_

					if ( $trim_key2 == 'custommm_' ) // For Custom
					{
            $trim_fix = substr($key2, 9);

            $GetCustomFields = $this->model_custom_fields_class::where($this->table_module.'_custom_fields_serial_id', '=', $trim_fix)
                                                                ->where('company_id', '=', $company_id)
                                                                ->first();
            // Add mask money to monetary converted data
            if ( countCustom($GetCustomFields) > 0 )
            {
              if($GetCustomFields[$this->table_module.'_custom_fields_input_type'] == 'monetary')
              {
                 $result[$key][$key2] = number_format(intval($value2));
              }
              else
              {
                 $result[$key][$key2] = $value2;
              }
            }
					}
					else // For core
					{
						if ( $key2 == 'documents' )
						{
							// $value2 = $value2;
							if ( !isEmpty($value2) )
							{
								$temp = array(); // For set default temp
								$value2 = explode(' ||||| ', $value2); // For split value documents name
								$sys_api_documents = sys_api('Documents');
								foreach ($value2 as $key3 => $value3)
								{
									$documents_name = $value3;
									$temp[] = $sys_api_documents->getDocumentsFromAwsExport($documents_name, $company_id);
								}
								$value2 = implode("\n", $temp);
							}
						}
						elseif ( $key2 == $this->table_module.'_parent_id' )
						{
							// $value2 = $value2;

							if (isset($value[$this->table_module.'_parent_type'])) 
							{
								if ( $value[$this->table_module.'_parent_type'] == 'Contact' )
								{ 
									$explode 	= explode('@', $value2);
								
									$value2  	= isset($explode[0]) ? $explode[0] : '';
								
									$hard_org 	= isset($explode[1]) ? $explode[1] : '';
								}
								elseif ( $value[$this->table_module.'_parent_type'] == 'Organization')
								{
									$explode 	= explode('|||||', $value2);
									$value2  	= isset($explode[0]) ? $explode[0] : '';
									$org_unique_id 	= isset($explode[1]) ? $explode[1] : '';
								}
							}
						}

						$result[$key][$key2] = isEmpty($value2) ? '-' : $value2;
					}
				}

				$result[$key]['Organization'] = $hard_org;
				$result[$key]['Org Unique ID'] = $org_unique_id;
			}
		}
		# create layout data Export label and data
		# return [['First Name', 'Last Name']]
		# created by : pratama gilang
		# 14-02-2020
		$result = $this->dataProcessExport($fields, $result);

		return $result;
	}
	# Created By Prihan Firmanullah
  # 16-01-2019
  # For get Roles Edit And Delete
	public function getRoles($data=array(), $company_id=0, $users_id=0)
	{
		$sys = new sys();
		if ( countCustom($data) > 0 )
		{
			$data_roles = $sys->get_roles($this->table_module, $company_id, $users_id); // Get data roles edit
			foreach ($data['data'] as $key => $value)
			{
				// Define variable
				$roles_edit 	= 'true'; // Default roles edit
				$roles_delete = 'true'; // Default roles delete
				$owner_id 		= $value['owner_id'];
				
				if ( countCustom($data_roles['contents_edit']) > 0 )
				{
					$roles_exists = array_search($owner_id, $data_roles['contents_edit']);
					if ( $roles_exists === false )
					{
						$roles_edit = 'false'; // Disable roles edit
					}
				}

				if ( countCustom($data_roles['contents_delete']) > 0 )
				{
					$roles_exists = array_search($owner_id, $data_roles['contents_delete']);
					if ( $roles_exists === false )
					{
						$roles_delete = 'false'; // Disable roles edit
					}
				}
				// Final result : Edit disable or enable
				$data['data'][$key]['editable'] = $roles_edit;
				$data['data'][$key]['deleteable'] = $roles_delete;
			}
		}

		return $data;
	}

		//calendar view
		public function dataCalendar($users_id=0, $company_id=0)
	{	
    $sys       = new sys();
		$sys_users = new sys_users;
		$teams 		= $sys_users->usersTeamDetail(isset($_GET['uuid']) ? $_GET['uuid'] : 0, $company_id);
		$users_uuid = $sys_users->usersData(isset($_GET['uuid']) ? $_GET['uuid'] : 0);
		$content[0]	= array($this->table_module.'_owner', '=', $users_id);
		
		$data 		= $this->model_class::where($content)
										 ->where('company_id', '=', $company_id)
										 ->where('deleted', '=', Config('setting.NOT_DELETED'))
										 ->get()
										 ->toArray();

    // ROLE
    $roles 							= $sys->get_roles($this->table_module, $company_id, $users_id); 
		$myRecordOnlyCheck 	= $this->myRecordOnlyCheck($company_id, $users_id); //if filter view " (You) " Checked
    // ROLE

		if(isset($_GET['filter_by']) AND $_GET['filter_by'] == 'team' AND isset($_GET['uuid']) AND $_GET['uuid'] == $teams['teams_uuid'])
		{
			unset($data);
			$teams_users = $sys_users->usersMapList($teams['teams_serial_id'], $company_id);

			$data = array();
			foreach ($teams_users as $key => $value) 
			{
				$content   = array( array($this->table_module.'_owner', '=', $value['users_id']));
				
				$query		 = $this->model_class::where($content)
												->where('company_id', '=', $company_id)
										 		->where('deleted', '=', Config('setting.NOT_DELETED'));
                        
				if (countCustom($roles['contents']) > 0 && $myRecordOnlyCheck === FALSE) 
        {
            $query->whereIn($this->table_module.'.'.$this->table_module.'_owner', $roles['contents']);
        }
        $query      = $query->get()
                      ->toArray();
				if(countCustom($query) > 0)
				{				  				
					$temp = $query;
					$data = array_merge($data, $temp);
				}

			}

		}

		if(isset($_GET['filter_by']) AND $_GET['filter_by'] == 'users' AND isset($_GET['uuid']) AND isset($users_uuid['users_uuid']) AND $_GET['uuid'] == $users_uuid['users_uuid'])
		{
			unset($content[0]);
			if($_GET['uuid'] == "")
			{
				$content[]   = array($this->table_module.'_owner', '!=', 0);
				$query 		 = $this->model_class::where($content)
										 ->where('company_id', '=', $company_id)
										 ->where('deleted', '=', Config('setting.NOT_DELETED'));

				if (countCustom($roles['contents']) > 0 && $myRecordOnlyCheck === FALSE) 
        {
            $query->whereIn($this->table_module.'.'.$this->table_module.'_owner', $roles['contents']);
        }
        $query      =$query->get()
										  ->toArray();
				$data = $query;
			}
			else
			{
				$content[]   = array($this->table_module.'_owner', '=', $users_uuid['id']);
				$query 		 = $this->model_class::where($content)
										 ->where('company_id', '=', $company_id)
										 ->where('deleted', '=', Config('setting.NOT_DELETED'));
				if (countCustom($roles['contents']) > 0 && $myRecordOnlyCheck === FALSE) 
        {
            $query->whereIn($this->table_module.'.'.$this->table_module.'_owner', $roles['contents']);
        }
        $query      = $query->get()
                      ->toArray();
				$data = $query;
			}
			
		}

		return $data;
	}

	public function googleCalendar($users_id)
	{
		$fieldsNameSysRel = "sys_rel.rel_to_module as ".$this->table_module."_parent_type, sys_rel.rel_to_id as ".$this->table_module."_parent_id,
											(
												CASE   
													WHEN sys_rel.rel_to_module = 'leads' THEN ( SELECT CONCAT(COALESCE(leads.leads_first_name, ''),' ', COALESCE(leads.leads_last_name, '')) FROM leads WHERE leads.leads_serial_id = sys_rel.rel_to_id LIMIT 1)  
													WHEN sys_rel.rel_to_module = 'contacts' THEN ( SELECT CONCAT(COALESCE(contacts.contacts_first_name, ''),' ', COALESCE(contacts.contacts_last_name, '')) FROM contacts WHERE contacts.contacts_serial_id = sys_rel.rel_to_id LIMIT 1) 
													WHEN sys_rel.rel_to_module = 'org'  THEN ( SELECT org_name FROM org WHERE org.org_serial_id = sys_rel.rel_to_id LIMIT 1) 
													WHEN sys_rel.rel_to_module is NULL THEN ''
												END
												) as ".$this->table_module."_parent_name";

		$data = $this->model_class::select(DB::raw($this->table_module.".*,".$fieldsNameSysRel))
									->where($this->table_module.'_owner', '=', $users_id)
									//->where($this->table_module.'_status', '=', 'Planned')
									->whereNotNull($this->table_module.'_date_start')
									->whereNotNull($this->table_module.'_date_end')
									->where($this->table_module.'_date_start', '>=', date("Y-m-d H:i:s", strtotime("-3 days")))
									->where('deleted', '=', Config('setting.NOT_DELETED'))
									->orderby($this->table_module.'_date_start', 'ASC')
									// ->where($this->table_module.'_serial_id', '=', 2)
									->leftjoin('sys_rel', function($join)
					        { 
					            $join->on('sys_rel.rel_from_id', '=', $this->table_module.'.'.$this->table_module.'_serial_id')
					            ->where('sys_rel.rel_from_module', '=', $this->table_module);
					        })
									->get()->toArray();
		//print "<pre>"; print_r($data); exit();

		return $data;
	}

	# Created By Gilang Pratama
	# 25-02-2020
	# Get Form quick create
	public function GetQuickForm($company_id=0 ,$agents_new_='')
	{
		$sys = new sys();

		$core_fields = $this->model_fields_class::where($this->table_module.'_fields_name', '!=', 'date_modified')
							->where($this->table_module.'_fields_name', '!=', 'date_created')
							->where($this->table_module.'_fields_name', '!=', 'created_by')
							->where($this->table_module.'_fields_name', '!=', 'modified_by')
							->where($this->table_module.'_fields_name', '!=', $this->table_module.'_date_end')
							->where($this->table_module.'_fields_name', '!=', $this->table_module.'_parent_type')
							->where($this->table_module.'_fields_name', '!=', $this->table_module.'_parent_id')
							->get()->toArray();


		$core_change = $this->model_fields_class::select('b.*')
                           	->leftjoin($this->table_module.'_fields_change as b', 'b.'.$this->table_module.'_fields_serial_id', '=', $this->table_module.'_fields.'.$this->table_module.'_fields_serial_id')
                           	->where('b.company_id', '=', $company_id)
							->where($this->table_module.'_fields_change_quick', '=', Config('setting.custom_fields_quick_active'))
							->get();

		$core_sorting = $this->model_fields_class::select('b.*')
                           	->leftjoin($this->table_module.'_fields_sorting as b', 'b.'.$this->table_module.'_fields_serial_id', '=', $this->table_module.'_fields.'.$this->table_module.'_fields_serial_id')
							->where('b.company_id', '=', $company_id)
							->get();

		// For handle duplicate data in fields change & fields sorting
		$temp_core_fields = $core_fields;

		if ( countCustom($core_change) > 0 )
		{
			foreach ($core_change as $key => $value) 
			{
				$key_change = array_search($value[$this->table_module.'_fields_serial_id'], array_column($temp_core_fields, $this->table_module.'_fields_serial_id'));

				if ( $key_change !== false )
				{
						$core_fields[$key_change][$this->table_module.'_fields_label'] 		= $value[$this->table_module.'_fields_change_label'];
						$core_fields[$key_change][$this->table_module.'_fields_validation'] = $value[$this->table_module.'_fields_change_validation'];
						$core_fields[$key_change][$this->table_module.'_fields_status'] 	= $value[$this->table_module.'_fields_change_status'];
						$core_fields[$key_change][$this->table_module.'_fields_options'] 	= $value[$this->table_module.'_fields_change_options'];
						$core_fields[$key_change][$this->table_module.'_fields_quick'] 		= $value[$this->table_module.'_fields_change_quick'];
						if ( !empty($value[$this->table_module.'_fields_change_input_type']) ) 
						{
							$core_fields[$key_change][$this->table_module.'_fields_input_type']	= $value[$this->table_module.'_fields_change_input_type'];
						}
					// }
				}
			}
		}

		/* 
		* filter apabila status deactive tidak perlu dimunculkan 
		* by. lintang
		*/

		$core_fields = array_filter($core_fields, function($var) {
			return $var[$this->table_module.'_fields_status'] !== 0;
		});
		// For handle not increment key array, handle problem in array search & array column
		$core_fields = array_values($core_fields);

		if ( countCustom($core_sorting) > 0 )
		{
			foreach ($core_sorting as $key => $value) 
			{
				$key_sorting = array_search($value[$this->table_module.'_fields_serial_id'], array_column($core_fields, $this->table_module.'_fields_serial_id'));
				if ( $key_sorting !== false )
				{
					$core_fields[$key_sorting][$this->table_module.'_fields_sorting'] 						= $value[$this->table_module.'_fields_sorting'];
					$core_fields[$key_sorting][$this->table_module.'_fields_sorting_put_header'] 	= $value[$this->table_module.'_fields_sorting_put_header'];
				}
			}
		}
		else
		{
			foreach ($core_fields as $key => $value) 
			{
					$core_fields[$key][$this->table_module.'_fields_sorting_put_header'] 	= Config('setting.fields_sorting_not_put_header');
			}
		}


		$select = $this->table_module.'_custom_fields_serial_id,'.
				$this->table_module.'_custom_fields_name,'.
				$this->table_module.'_custom_fields_label,'.
				$this->table_module.'_custom_fields_data_type,'.
				$this->table_module.'_custom_fields_input_type,'.
				$this->table_module.'_custom_fields_function,'.
				$this->table_module.'_custom_fields_options,'.
				$this->table_module.'_custom_fields_validation,'.
				$this->table_module.'_custom_fields_sorting as '.$this->table_module.'_fields_sorting,'.
				$this->table_module.'_custom_fields_status,'.
				$this->table_module.'_custom_fields_quick,'.
				$this->table_module.'_custom_fields_put_header';

		$custom_fields = $this->model_custom_fields_class::select(DB::raw($select))
																											->where( $this->table_module.'_custom_fields_status', '=', Config('setting.custom_fields_status_active'))
																											->where( 'company_id', '=', $company_id)
																											->where($this->table_module.'_custom_fields_quick', '=', Config('setting.custom_fields_quick_active'))
																											->get()->toArray();

		foreach ($custom_fields as $key => $value) 
		{
			$custom_fields[$key][$this->table_module.'_custom_fields_name'] = 'custom_'.$custom_fields[$key][$this->table_module.'_custom_fields_name'];
		}

		$result = array_merge($core_fields, $custom_fields);

		// This is for selection form only status active
		$result = $this->GetFormActive($result);

		// This is for selection form only Quick active
		$result = $this->GetQuickFormActive($result);

		// This is for explode and split validation - required
		$result = $this->GetFormValidation($result);

		$result = $this->GetFormExtra($result, $company_id);
		
		$result = $this->getAllFieldsCondition($result, $company_id);

		$result = $sys->array_sort_custom( $result, $this->table_module.'_fields_sorting', SORT_ASC);

		return $result;
	}

	public function related_projects($request=array(), $company_id=0)
	{
		// Define variable
		$result = array();

		if ( isset($request['deals_serial_id']) AND $request['deals_serial_id'] != '' ) 
		{
			$deals_serial_id = $request['deals_serial_id'];

			$query = $this->model_deals_class::select(DB::raw('b.projects_name as dropdown_options_label, b.projects_serial_id as dropdown_options_value'))
																			->leftJoin('projects as b', 'b.projects_serial_id', '=', 'deals.projects_serial_id')
																			->where('deals.deals_serial_id', '=', $deals_serial_id)
																			->where('deals.company_id', '=', $company_id)
																			->get();
			if ( countCustom($query) > 0 ) 
			{
				$result = $query->toArray();
			}
		}

		return $result;
	}

	// for join in table sys_rel : parent type and related to
	public function join_sys_rel_edit($data=array(), $company_id=0)
	{
		if (countCustom($data) > 0) 
		{
				$serial_id 				= $data[$this->table_module.'_serial_id'];
				$table 						= "";
				$module_serial_id = "";
				$module_content 	= "";
				$module_uuid 			= "";

				$sys_rel 	= $this->model_sys_rel_class::where('rel_from_module', '=', $this->table_module)
													->where('rel_from_id', '=', $serial_id)
													->first();
				if (countCustom($sys_rel) > 0) 
				{
					if ($sys_rel['rel_to_module'] == "deals") 
					{
						$select 	= "deals.deals_name";
						$table 		= "deals";
					}
					elseif ($sys_rel['rel_to_module'] == "leads") 
					{
						$select 	= "CONCAT(COALESCE(leads.leads_first_name, ''),' ', COALESCE(leads.leads_last_name, ''))";
						$table 		= "leads";
					}
					elseif ($sys_rel['rel_to_module']  == "contacts") 
					{
						$select 	= "CONCAT(COALESCE(contacts.contacts_first_name, ''),' ', COALESCE(contacts.contacts_last_name, ''))";
						$table 		= "contacts";
					}
					elseif ($sys_rel['rel_to_module'] == "org") 
					{
						$select 	= "org.org_name";
						$table 		= "org";
					}
					elseif ($sys_rel['rel_to_module'] == "calls") 
					{
						$select 	= "calls.calls_name";
						$table 		= "calls";
					}
					elseif ($sys_rel['rel_to_module'] == "meetings") 
					{
						$select 	= "meetings.meetings_name";
						$table 		= "meetings";
					}
					elseif ($sys_rel['rel_to_module'] == "notes") 
					{
						$select 	= "notes.notes_name";
						$table 		= "notes";
					}
					elseif ($sys_rel['rel_to_module'] == "tasks") 
					{
						$select 	= "tasks.tasks_name";
						$table 		= "tasks";
					}
					elseif ($sys_rel['rel_to_module'] == "emails") 
					{
						$select 	= "emails.emails_name";
						$table 		= "emails";
					}

					if($table != "" AND ($sys_rel['rel_to_id']!=null || $sys_rel['rel_to_id']!=0 || $sys_rel['rel_to_id']!=''))
					{
						$uuidField = $table.'_serial_id AS rel_to_id';

						$query = collect(DB::table($table)
									->select(DB::raw($select." AS module_content, ".$uuidField))
									->where($table.'_serial_id', '=', $sys_rel['rel_to_id'])
									->where('company_id','=', $company_id)
									->first())->toArray();

						$module_content = (isset($query['module_content'])) ? $query['module_content'] : '';
						$rel_to_id 			= (isset($query['rel_to_id'])) ? $query['rel_to_id'] : '';

						$data 	= array_add($data, $this->table_module.'_parent_type', $table);
						$data		= array_add($data, $this->table_module.'_parent_id', $module_content);
						$data		= array_add($data, $this->table_module.'_parent_id_id', $rel_to_id);

					}
					else
					{
						$data 	= array_add($data, $this->table_module.'_parent_type', '');
						$data		= array_add($data, $this->table_module.'_parent_id', '');
						$data		= array_add($data, $this->table_module.'_parent_id_id', '');
					}
				}
				else
				{
						$data 	= array_add($data, $this->table_module.'_parent_type', '');
						$data		= array_add($data, $this->table_module.'_parent_id', '');
						$data		= array_add($data, $this->table_module.'_parent_id_id', '');
				}
		}
		
		return $data;
	}


	# Created By Pratama Gilang
	# 13-03-2020
	# For Export All check filter
	public function listDataExport($criteria=array(), $fields=array(), $input=array(), $data_roles=TRUE, $data_filter=TRUE)
	{
		$sys = new sys();

		# DEFINED VARIABLE
		$listFieldsCustom 		= $fields['listFieldsCustom']; // Get Fields Custom
		$query_count 					= 0; // count query : default
		# END

		# LIST CORE FIELDS AND CUSTOM FIELDS (MERGE)
		$fieldsName 			= $this->select_fieldsName($fields); // list fields in core field and custom fields. 
		# END
		# CHANGE OWNER_ID TO OWNER_NAME
		$checkOwner 	= in_array($this->table_module.'.'.$this->table_module.'_owner', $fieldsName); // check if owner available in $fieldsName
		if($checkOwner === TRUE)  
		{
			$fieldsName 	= array_merge($fieldsName, array('users.name as '.$this->table_module.'_owner')); 
		}
		# END

		# CHANGE CREATED_ID TO CREATED_NAME
		$checkCreated 	= in_array($this->table_module.'.created_by', $fieldsName); // check if created_by available in $fieldsName
		if($checkCreated === TRUE)  
		{
			$fieldsName 	= array_merge($fieldsName, array('users_created.name as created_by')); 
		}
		# END

		# CHANGE MODIFIED_ID TO MODIFIED_NAME
		$checkModified 	= in_array($this->table_module.'.modified_by', $fieldsName); // check if modified_by available in $fieldsName
		if($checkModified === TRUE)  
		{
			$fieldsName 	= array_merge($fieldsName, array('users_modified.name as modified_by')); 
		}
		# END

		# CHANGE DEALS_SERIAL_ID TO DEALS_UUID and DEALS_NAME  
		$checkDealSerialId 	= in_array($this->table_module.'.deals_serial_id', $fieldsName); // check if owner available in $fieldsName
		if($checkDealSerialId === TRUE)  
		{
			$fieldsName 	= array_merge($fieldsName, array('deals.deals_uuid', 'deals.deals_name')); 
		} 
		# END 

		# CHANGE PROJECTS_SERIAL_ID TO PROJECTS_UUID and PROJECTS_NAME  
		$checkProjectsSerialId 	= in_array($this->table_module.'.projects_serial_id', $fieldsName); // check if projects available in $fieldsName
		if($checkProjectsSerialId === TRUE)  
		{
			$fieldsName 	= array_merge($fieldsName, array('projects.projects_uuid', 'projects.projects_name')); 
		} 
		# END 

		# CHANGE ISSUE_SERIAL_ID TO ISSUE_UUID and ISSUE_NAME  
		$checkIssueSerialId 	= in_array($this->table_module.'.issue_serial_id', $fieldsName); // check if issue available in $fieldsName
		if($checkIssueSerialId === TRUE)  
		{
			$fieldsName 	= array_merge($fieldsName, array('issue.issue_uuid', 'issue.issue_name')); 
		} 
		# END
		
		# CHANGE TICKETS_SERIAL_ID TO TICKETS_UUID and TICKETS_NAME  
		$checkTicketsSerialId 	= in_array($this->table_module.'.tickets_serial_id', $fieldsName); // check if tickets available in $fieldsName
		if($checkTicketsSerialId === TRUE)  
		{
			$fieldsName 	= array_merge($fieldsName, array('tickets.tickets_uuid', 'tickets.tickets_name')); 
		} 
		# END

		# CONVERT TO ROW QUERY FORMAT
		$fieldsNameConvert	= $this->convertFieldsName($fieldsName);
		# END 

		# SELECT QUERY DYNAMIC BY $fieldsName
		$b = 'b';

		$fieldsNameSysRel = "sys_rel.rel_to_module as ".$this->table_module."_parent_type, sys_rel.rel_to_id as ".$this->table_module."_parent_id,
											(
												CASE   
													WHEN sys_rel.rel_to_module = 'leads' THEN ( SELECT CONCAT(COALESCE(leads.leads_first_name, ''),' ', COALESCE(leads.leads_last_name, '')) FROM leads WHERE leads.leads_serial_id = sys_rel.rel_to_id LIMIT 1)  
													WHEN sys_rel.rel_to_module = 'contacts' THEN ( SELECT CONCAT(COALESCE(contacts.contacts_first_name, ''),' ', COALESCE(contacts.contacts_last_name, '')) FROM contacts WHERE contacts.contacts_serial_id = sys_rel.rel_to_id LIMIT 1) 
													WHEN sys_rel.rel_to_module = 'org'  THEN ( SELECT org_name FROM org WHERE org.org_serial_id = sys_rel.rel_to_id LIMIT 1) 
													WHEN sys_rel.rel_to_module = 'projects' THEN ( SELECT projects_name FROM projects WHERE projects.projects_serial_id = sys_rel.rel_to_id LIMIT 1 )
													WHEN sys_rel.rel_to_module is NULL THEN ''
												END
												) as ".$this->table_module."_parent_name";
		$query = $this->model_class::select($this->table_module . "." . $this->table_module . "_uuid");
		# END 


		# LEFT JOIN WITH SYS REL
		$query->leftjoin('sys_rel', function($join) use ($criteria)
		        { 
		            $join->on('sys_rel.rel_from_id', '=', $this->table_module.'.'.$this->table_module.'_serial_id')
		            ->where('sys_rel.rel_from_module', '=', $this->table_module);
		        });
		# END 

		# LEFT JOIN WITH CUSTOM FIELDS
		$temp_alias = array();
		if (countCustom($listFieldsCustom) > 0) // 	if fields custom available
		{
			$query->leftjoin($this->table_module.'_custom_values_ as mcv', function($join) use ($criteria)
							{
								$join->on('mcv.'.$this->table_module.'_serial_id', '=', $this->table_module.'.'.$this->table_module.'_serial_id')
								->where('mcv.company_id', '=', $criteria['company_id']);
							});

		}
			
		# IF OWNER AVAILABLE IN $fieldsName
		if ($checkOwner === TRUE || $data_filter == TRUE || (isset($_GET['subaction']) && $_GET['subaction'] == "search"))
		{
			//run if owner show in listing OR 
			//run if data_filter TRUE OR
			//run if search feature true
			$query->leftjoin('users', 'users.id', '=', $this->table_module.'.'.$this->table_module.'_owner');
		}

		# IF CREATED AVAILABLE IN $fieldsName
		if ($checkCreated === TRUE || (isset($_GET['subaction']) && $_GET['subaction'] == "search"))
		{
			//run if created show in listing OR 
			//run if search feature true
			$query->leftjoin('users as users_created', 'users_created.id', '=', $this->table_module.'.created_by');
		}

		# IF MODIFIED AVAILABLE IN $fieldsName
		if ($checkModified === TRUE || (isset($_GET['subaction']) && $_GET['subaction'] == "search"))
		{
			//run if modified show in listing OR 
			//run if search feature true
			$query->leftjoin('users as users_modified', 'users_modified.id', '=', $this->table_module.'.modified_by');
		}

		# IF DEALS_SERIAL_ID AVAILABLE IN $fieldsName
		$checkDealSerialId 	= in_array($this->table_module.'.deals_serial_id', $fieldsName); // check if owner available in $fieldsName
		if($checkDealSerialId === TRUE)  
		{
			$query->leftjoin('deals', 'deals.deals_serial_id', '=', $this->table_module.'.deals_serial_id');
		}

		# IF PROJECTS_SERIAL_ID AVAILABLE IN $fieldsName
		$checkProjectsSerialId 	= in_array($this->table_module.'.projects_serial_id', $fieldsName); // check if projects available in $fieldsName
		if($checkProjectsSerialId === TRUE)  
		{
			$query->leftjoin('projects', 'projects.projects_serial_id', '=', $this->table_module.'.projects_serial_id');
		}

		# IF ISSUE_SERIAL_ID AVAILABLE IN $fieldsName
		$checkIssueSerialId 	= in_array($this->table_module.'.issue_serial_id', $fieldsName); // check if issue available in $fieldsName
		if($checkIssueSerialId === TRUE)  
		{
			$query->leftjoin('issue', 'issue.issue_serial_id', '=', $this->table_module.'.issue_serial_id');
		}

		# IF TICKETS_SERIAL_ID AVAILABLE IN $fieldsName
		$checkTicketsSerialId 	= in_array($this->table_module.'.tickets_serial_id', $fieldsName); // check if issue available in $fieldsName
		if($checkTicketsSerialId === TRUE)  
		{
			$query->leftjoin('tickets', 'tickets.tickets_serial_id', '=', $this->table_module.'.tickets_serial_id');
		}

		# DATA ROLES
		$roles 							= $this->get_roles($this->table_module, $criteria['company_id'], $criteria['users_id']);
		$myRecordOnlyCheck 	= $this->myRecordOnlyCheck($criteria['company_id'], $criteria['users_id']); //if filter view " (You) " Checked
		if ($data_roles == TRUE && countCustom($roles['contents']) > 0 && $myRecordOnlyCheck === FALSE) 
		{
			//$roles['contents'] is container member inside roles by users_id, example : team view then $roles['contents'] = array('11', '12', '13') 
			//if member != empty , then running this block
			//more detail about $roles, please check get_roles()
			$query->whereIn($this->table_module.'.'.$this->table_module.'_owner', $roles['contents']);
		}

		# FILTER FEATURE
		if ($data_filter == TRUE)
		{
			//Running filter when $data_filter TRUE
			$filterView 				= $this->viewChecked($criteria['company_id'], $criteria['users_id']); // checked filter view active
			$filterViewName 		= $filterView[$this->table_module.'_view_name'];
			
			$filterCriteria 			= $this->generate_view($filterView, $criteria['company_id'], $criteria['users_id']); // get the selected filter 	

			$filterCriteria 			= $this->data_search($filterCriteria, $criteria['company_id'], $temp_alias, $listFieldsCustom, $b); // generate format for use filter feature
			
			if(isset($filterCriteria['temp']))
			{
				if (countCustom($listFieldsCustom) == 0) // if fields custom not available
				{
					$query->leftjoin($this->table_module.'_custom_values_ as mcv', function($join) use ($criteria)
								{
									$join->on('mcv.'.$this->table_module.'_serial_id', '=', $this->table_module.'.'.$this->table_module.'_serial_id')
									->where('mcv.company_id', '=', $criteria['company_id']);
								});
				}
			}
			

			$filterCriteriaCore 	= $filterCriteria['result']['core']; // filter by core field type
			$filterCriteriaDate 	= $filterCriteria['result']['date']; // filter by date type
			$filterCriteriaCustom = $filterCriteria['result']['custom']; // filter by custom field type

			if ($myRecordOnlyCheck === TRUE)
		  	{
		  	// if user choosen filter "You", then owner by sesson users_id login
				$query->where($this->table_module.'_owner', '=', $criteria['users_id']);
			}
			elseif (countCustom($filterView) > 0 && $filterViewName != 'Everyone' && $filterViewName != 'You') 
			{
				// get users statuc active or deactive
				$get_users = $this->model_users_class::select('users_status')
									->leftjoin('users_company as comp','comp.users_id','=','users.id')
									->where('id','=',$filterView['users_id'])
									->where('company_id','=',$filterView['company_id'])
									->first();

				// for check if users deactive
				if($get_users['users_status'] == 'deactive')
				{
					// get current users active
					$get_active = $this->model_view_class::select($this->table_module.'_view_serial_id', $this->table_module.'_view_name')
										->where($this->table_module.'_view_name','=','Everyone')
										->where('users_id','=', $criteria['users_id'])
										->where('company_id','=', $criteria['company_id'])
										->first();

					// update contact_view_serial_id into default
					$update = $this->model_view_checked_class::where('users_id','=',$criteria['users_id'])
									->where('company_id','=',$criteria['company_id'])
									->update([$this->table_module.'_view_serial_id' => $get_active[$this->table_module.'_view_serial_id']]);

					// change filter into default/Everyone when load data
					$filterView[$this->table_module.'_view_serial_id'] = $get_active[$this->table_module.'_view_serial_id'];
					$filterView[$this->table_module.'_view_name'] = 'Everyone';

					// change criteria into empty
					$filterCriteriaCore = array();
					$filterCriteriaDate = array();
					$filterCriteriaCustom = array();
				}
				
				$query_count = 1; // count query with left join
				//if user choosen filter, except filter 'Everyone' and 'You' 
				$checkFilterByOwner = in_array($this->table_module.'.'.$this->table_module.'_owner', array_column($filterCriteriaCore, 0)); 

				if ($checkFilterByOwner == TRUE) // if filter data by owner
				{
		      $key_filter_owner = array_search($this->table_module.'.'.$this->table_module.'_owner', array_column($filterCriteriaCore, 0)); // get key, position owner in array		      
		      if (is_array($filterCriteriaCore[$key_filter_owner][2]) && countCustom($filterCriteriaCore[$key_filter_owner][2]) > 0)
		      {
		      	// if filter data by multi owner
		      	if ($filterCriteriaCore[$key_filter_owner][1] == "=") // when owner by IS 
		      	{
			      	$query->whereIn($this->table_module.'.'.$this->table_module.'_owner', $filterCriteriaCore[$key_filter_owner][2]);

		      	}elseif ($filterCriteriaCore[$key_filter_owner][1] == "!=") // when owner by isnt
		      	{
			      	$query->whereNotIn($this->table_module.'.'.$this->table_module.'_owner', $filterCriteriaCore[$key_filter_owner][2]);
		      	}
		      }else
		      {
				// if filter data by single owner
				$query->join('users', 'users.id', $this->table_module . '.' . $this->table_module . '_owner');
				$query->where('users.name', $filterCriteriaCore[$key_filter_owner][1], $filterCriteriaCore[$key_filter_owner][2]);
		      }			
					unset($filterCriteriaCore[$key_filter_owner]); // remove owner in array, by position key
				}
				if (countCustom($filterCriteriaDate) > 0) // if filter data by date
				{
					$date_between 	= $this->date_between($filterCriteriaDate);
					$query->whereRaw($date_between);
				}
				if (countCustom($filterCriteriaCustom) > 0 ) // if filter data by custom fields
				{
					foreach ($filterCriteriaCustom as $value) 
					{
						if ($value[1] == "=" && is_array($value[2])) // operator is 
						{
							$query->whereIn($value[0], $value[2]);
						}
						elseif ($value[1] == "!=" && is_array($value[2])) // operator isn't
						{
							$query->whereNotIn($value[0], $value[2]);
						}
						elseif ( $value[1] === '==' ) // operator is_empty
						{
							$query->where(function ($query) use ($value) 
							{
								$query->where($value[0], '=', $value[2]);
								$query->orWhereNull($value[0]);
							});
						}
						elseif ( $value[1] === '!==' ) // operator is_not_empty
						{
							$query->where(function ($query) use ($value) 
							{
								$query->where($value[0], '!=', $value[2]);
								$query->whereNotNull($value[0]);
							});
						}
						else
						{
							$query->where($value[0], $value[1], $value[2]); // operator contains, start_with and end_with
						}
					}
				}
				$checkParentType 	= in_array($this->table_module.'.'.$this->table_module.'_parent_type', array_column($filterCriteriaCore, 0));
				if ($checkParentType == TRUE) // filter data by calls_parent_type
				{
					$keyParentType 	= array_search($this->table_module.'.'.$this->table_module.'_parent_type', array_column($filterCriteriaCore, 0));
					$parentTypeOpp 			= $filterCriteriaCore[$keyParentType][1]; // get operator
					$parentTypeKeyword 	= $filterCriteriaCore[$keyParentType][2]; // get keyword
					
					$query->where('sys_rel.rel_to_module', $parentTypeOpp, $parentTypeKeyword);
					unset($filterCriteriaCore[$keyParentType]);
				}
				$checkParentId 	= in_array($this->table_module.'.'.$this->table_module.'_parent_id', array_column($filterCriteriaCore, 0));
				if ($checkParentId == TRUE)  // filter data by calls_parent_id
				{
					$filterCriteriaCore = array_values($filterCriteriaCore); // reset key array, to 0 
					$keyParentId 				= array_search($this->table_module.'.'.$this->table_module.'_parent_id', array_column($filterCriteriaCore, 0));
					$parentIdOpp 				= $filterCriteriaCore[$keyParentId][1]; // get operator
					$parentIdKeyword 		= $filterCriteriaCore[$keyParentId][2]; // get keyword
					
					$query->having($this->table_module.'_parent_id', $parentIdOpp, $parentIdKeyword);
					unset($filterCriteriaCore[$keyParentId]);
				}
				// $query->where($filterCriteriaCore);
				if ( countCustom($filterCriteriaCore) > 0 ) 
				{
					foreach ($filterCriteriaCore as $key => $value) 
					{
						if ($value[1] == "=" && is_array($value[2])) // operator is 
						{
							$query->whereIn($value[0], $value[2]);
						}
						elseif ($value[1] == "!=" && is_array($value[2])) // operator isn't
						{
							$query->whereNotIn($value[0], $value[2]);
						}
						elseif ( $value[1] === '==' ) 
						{
							$query->where(function ($query) use ($value) 
							{
								$query->where($value[0], '=', $value[2]);
								$query->orWhereNull($value[0]);
							});
						}
						elseif ( $value[1] === '!==' ) 
						{
							$query->where(function ($query) use ($value) 
							{
								$query->where($value[0], '!=', $value[2]);
								$query->whereNotNull($value[0]);
							});
						}
						elseif ($value[1] == "=" && ($value[0] == $this->table_module.".date_created" || $value[0] == $this->table_module.".date_modified" || $value[0] == $this->table_module.".".$this->table_module."_date_start" || $value[0] == $this->table_module.".".$this->table_module."_date_end"))
						{
							$date_range = explode(" - ", $value[2]);
							if (countCustom($date_range) > 1)
							{
								$date_start = date('Y-m-d', strtotime($date_range[0])).' 00:00:00'; 
								$date_end 	= date('Y-m-d', strtotime($date_range[1])).' 23:59:59';

								$query->whereBetween($value[0], [$date_start, $date_end]);
							}
						}
						elseif ($value[1] == "!=" && ($value[0] == $this->table_module.".date_created" || $value[0] == $this->table_module.".date_modified" || $value[0] == $this->table_module.".".$this->table_module."_date_start" || $value[0] == $this->table_module.".".$this->table_module."_date_end"))
						{
							$date_range = explode(" - ", $value[2]);
							if (countCustom($date_range) > 1)
							{
								$date_start = date('Y-m-d', strtotime($date_range[0])).' 00:00:00'; 
								$date_end 	= date('Y-m-d', strtotime($date_range[1])).' 23:59:59';

								$query->whereNotBetween($value[0], [$date_start, $date_end]);
							}
						}
						else
						{
							$query->where($value[0], $value[1], $value[2]); // search data in core fields
						}
					}
				}
				// end
			}
		} 

		# SEARCH FEATURE
		if (isset($input['subaction']) && $input['subaction'] == "search")
		{
			$query_count = 1; // count query with left join
			
			//ADD BY AGUNG -> 15/03/2019 -> SEARCHING OWNER BY TEAMS
			// HANDLE SEACH DEALS OWNER BY TEAM
			if(isset($input[$this->table_module.'_owner_opp']) AND isset($input[$this->table_module.'_owner']))
			{
				if($input[$this->table_module.'_owner_opp'] == "is" OR $input[$this->table_module.'_owner_opp'] == "isnt")
				{
					if(is_array($input[$this->table_module.'_owner']))
					{
						if(countCustom($input[$this->table_module.'_owner']) > 0 OR !empty($input[$this->table_module.'_owner']))
						{
							$search_owner = array();
							foreach($input[$this->table_module.'_owner'] as $key_owner => $val_owner)
							{
								$owner_id = $val_owner;
								if (is_array($val_owner)) 
								{
									$owner_id = $val_owner[0];
								}
								$search_owner[$key_owner] = explode("!@#$%^&*()", $owner_id);
								if(isset($search_owner[$key_owner][1]))
								{
									$input[$this->table_module.'_owner'][$key_owner] = $search_owner[$key_owner][1];
								}
							}
	
						}
					}
					else
					{
						$search_owner = explode("!@#$%^&*()", $input[$this->table_module.'_owner']);
						if(isset($search_owner[1]))
						{
							$input[$this->table_module.'_owner'] = array($search_owner[1]);
						}
					}
				}

			}
			// END HANDLE SEACH DEALS OWNER BY TEAM
			
			//when use search feature, running this block code
			$searchCriteria 			= $this->data_search($input, $criteria['company_id']);
			$searchCriteriaCore 	= $searchCriteria['result']['core']; // filter by core field type
			$searchCriteriaDate 	= $searchCriteria['result']['date']; // filter by type date
			$searchCriteriaCustom = $searchCriteria['result']['custom']; // filter by custom field type
			$searchCriteriaDateCustom = $searchCriteria['result']['date_custom']; // filter by custom field type

      		$checkSearchByOwner = in_array($this->table_module.'.'.$this->table_module.'_owner', array_column($searchCriteriaCore, 0)); 
			if ($checkSearchByOwner == TRUE) // if search data by owner
			{
				$key_search_owner = array_search($this->table_module.'.'.$this->table_module.'_owner', array_column($searchCriteriaCore, 0)); // get key, position owner in array	      
				if (is_array($searchCriteriaCore[$key_search_owner][2]) && countCustom($searchCriteriaCore[$key_search_owner][2]) > 0)
				{
					// if search data by multi owner
					if ($searchCriteriaCore[$key_search_owner][1] == "=") // when owner by IS 
					{
						$query->whereIn($this->table_module.'.'.$this->table_module.'_owner', $searchCriteriaCore[$key_search_owner][2]);

					}elseif ($searchCriteriaCore[$key_search_owner][1] == "!=") // when owner by isnt
					{
						$query->whereNotIn($this->table_module.'.'.$this->table_module.'_owner', $searchCriteriaCore[$key_search_owner][2]);
					}
				}else
				{
					// if search data by single owner
					$query->join('users AS userowner', 'userowner.id', $this->table_module . '.' . $this->table_module . '_owner');
					$query->where('userowner.name', $searchCriteriaCore[$key_search_owner][1], $searchCriteriaCore[$key_search_owner][2]);
				}			
				// unset($searchCriteriaCore[$key_search_owner]); // remove owner in array, by position key
				array_splice($searchCriteriaCore, $key_search_owner, 1);
			}

			$checkSearchByCreated = in_array($this->table_module.'.created_by', array_column($searchCriteriaCore, 0)); 
			if ($checkSearchByCreated == TRUE) // if search data by created
			{
				$key_search_created = array_search($this->table_module.'.created_by', array_column($searchCriteriaCore, 0)); // get key, position created in array
				if (is_array($searchCriteriaCore[$key_search_created][2]) && countCustom($searchCriteriaCore[$key_search_created][2]) > 0)
				{
					// if search data by multi created
					if ($searchCriteriaCore[$key_search_created][1] == "=") // when owner by IS 
					{
						$query->whereIn($this->table_module.'.created_by', $searchCriteriaCore[$key_search_created][2]);

					}elseif ($searchCriteriaCore[$key_search_created][1] == "!=") // when owner by isnt
					{
						$query->whereNotIn($this->table_module.'.created_by', $searchCriteriaCore[$key_search_created][2]);
					}
				}else
				{
					// if search data by single created
					$query->join('users AS usercreated', 'usercreated.id', $this->table_module . '.' . $this->table_module . '_owner');
					$query->where('usercreated.name', $searchCriteriaCore[$key_filter_owner][1], $searchCriteriaCore[$key_filter_owner][2]);
				}			
				// unset($searchCriteriaCore[$key_search_created]); // remove created in array, by position key
				array_splice($searchCriteriaCore, $key_search_created, 1);

			}

			$checkSearchByModified = in_array($this->table_module.'.modified_by', array_column($searchCriteriaCore, 0)); 
			if ($checkSearchByModified == TRUE) // if search data by modified
			{
				$key_search_modified = array_search($this->table_module.'.modified_by', array_column($searchCriteriaCore, 0)); // get key, position modified in array
				if (is_array($searchCriteriaCore[$key_search_modified][2]) && countCustom($searchCriteriaCore[$key_search_modified][2]) > 0)
				{
					// if search data by multi modified
					if ($searchCriteriaCore[$key_search_modified][1] == "=") // when owner by IS 
					{
						$query->whereIn($this->table_module.'.modified_by', $searchCriteriaCore[$key_search_modified][2]);

					}elseif ($searchCriteriaCore[$key_search_modified][1] == "!=") // when owner by isnt
					{
						$query->whereNotIn($this->table_module.'.modified_by', $searchCriteriaCore[$key_search_modified][2]);
					}
				}else
				{
					// if search data by single modified
					$query->where('users_modified.name', $searchCriteriaCore[$key_search_modified][1], $searchCriteriaCore[$key_search_modified][2]);
				}			
				// unset($searchCriteriaCore[$key_search_modified]); // remove modified in array, by position key
				array_splice($searchCriteriaCore, $key_search_modified, 1);
			}

			if (countCustom($searchCriteriaDate) > 0) // if search data by date
			{
				$date_between 	= $this->date_between($searchCriteriaDate);
				$query->whereRaw($date_between);
			}

			if (countCustom($searchCriteriaDateCustom) > 0) // if search data by date
			{
				$date_between_custom 	= $this->date_between_custom($searchCriteriaDateCustom);

				$query->whereRaw($date_between_custom);
			}

			// Update By Rendi 11.03.2019
			if (countCustom($searchCriteriaCustom) > 0 ) // if search data by custom fields
			{
				foreach ($searchCriteriaCustom as $value) 
				{
					if ($value[1] == "=" && is_array($value[2])) // operator is 
					{
						// only for custom multipleoption
						$fields = explode('.', $value[0]);
						$fields_type = $this->model_custom_fields_class::select($this->table_module.'_custom_fields_input_type')
																		->where($this->table_module.'_custom_values_maps','=',$fields[1])
																		->where('company_id','=',$criteria['company_id'])
																		->first();
						if ($fields_type[$this->table_module.'_custom_fields_input_type'] === 'multipleoption') 
						{
							$query->where(function ($query) use ($value) 
							{
								foreach ($value[2] as $value2) 
								{
									$query->orwhere($value[0], 'LIKE', '%'.$value2.'%');
								}
							});
						}// end
						else
						{
							$query->whereIn($value[0], $value[2]);
						}
					}
					elseif ($value[1] == "IN") // operator isn't
					{
						$query->whereIn($value[0],$value[2]);
					}
					elseif ($value[1] == "!=" && is_array($value[2])) // operator isn't
					{
						$query->whereNotIn($value[0], $value[2]);
					}elseif ($value[2] === "%%") {

					}
					else
					{
						$query->where($value[0], $value[1], $value[2]); // Like
					}
				}
			}
			$checkParentType 	= in_array($this->table_module.'.'.$this->table_module.'_parent_type', array_column($searchCriteriaCore, 0));
			if ($checkParentType == TRUE) // search data by calls_parent_type
			{
				$keyParentType 	= array_search($this->table_module.'.'.$this->table_module.'_parent_type', array_column($searchCriteriaCore, 0));
				$parentTypeOpp 			= $searchCriteriaCore[$keyParentType][1]; // get operator
				$parentTypeKeyword 	= $searchCriteriaCore[$keyParentType][2]; // get keyword
				
				$query->where('sys_rel.rel_to_module', $parentTypeOpp, $parentTypeKeyword);
				unset($searchCriteriaCore[$keyParentType]);
			}
			$checkParentId 	= in_array($this->table_module.'.'.$this->table_module.'_parent_id', array_column($searchCriteriaCore, 0));
			if ($checkParentId == TRUE)  // search data by calls_parent_id
			{
				$searchCriteriaCore = array_values($searchCriteriaCore); // reset key array, to 0 
				$keyParentId 				= array_search($this->table_module.'.'.$this->table_module.'_parent_id', array_column($searchCriteriaCore, 0));
				$parentIdOpp 				= $searchCriteriaCore[$keyParentId][1]; // get operator
				$parentIdKeyword 		= $searchCriteriaCore[$keyParentId][2]; // get keyword
				
				$query->having($this->table_module.'_parent_id', $parentIdOpp, $parentIdKeyword);
				unset($searchCriteriaCore[$keyParentId]);
			}

			// ADD BY ANDRIAN
			// FOR SOLVED SEARCH 'is_empty' and 'is_not_empty'
			if ( countCustom($searchCriteriaCore) ) 
			{
				foreach ($searchCriteriaCore as $key => $value) 
				{
					if ($value[1] == "=" && is_array($value[2])) // operator is 
					{
						$query->whereIn($value[0], $value[2]);
					}
					elseif ($value[1] == "!=" && is_array($value[2])) // operator isn't
					{
						$query->whereNotIn($value[0], $value[2]);
					}
					elseif ( $value[1] === '==' ) 
					{
						$query->where(function ($query) use ($value) 
						{
							$query->where($value[0], '=', $value[2]);
							$query->orWhereNull($value[0]);
						});
					}
					elseif ( $value[1] === '!==' ) 
					{
						$query->where(function ($query) use ($value) 
						{
							$query->where($value[0], '!=', $value[2]);
							$query->whereNotNull($value[0]);
						});
					}
					else
					{
						$query->where($value[0], $value[1], $value[2]); // search data in core fields
					}
				}
			}
			// END ADD BY ANDRIAN
			// END FOR SOLVED SEARCH 'is_empty' and 'is_not_empty'
		} 

		# SEARCH GLOBAL FEATURE
		if (isset($_GET['subaction']))
		{
			if ($_GET['subaction'] == "search_global") 
			{
				$query_count = 1; // count query with left join

				$keyword 	= $criteria['keyword'];
				$fields 	= array();

				foreach ($fieldsName as $value) 
				{
					$name 			= $value;
					$exp_value 	= explode(" ", $value);
					if (is_array($exp_value) && isset($exp_value[2])) 
					{
						$fields[]		= $exp_value[0];
					}else{
						$fields[] 	= $name;
					}
				}

				$query->where(function ($query) use ($fields, $keyword) 
				{
					for ($i=0; $i < countCustom($fields); $i++) 
					{ 
						$query->orWhere($fields[$i], 'LIKE', $keyword.'%');
					}
				});
			}
		} 

		$query->where($this->table_module.'.company_id', '=', $criteria['company_id']);
		$query->where($this->table_module.'.deleted', '=', Config('setting.NOT_DELETED'));
		# END
	
		$result 				= $query->get();

		if (countCustom($result) > 0)
		{
			$result = $result->toArray();
		}

		return $result;
	}
		
	/*
	* Export All data
	* by. lintang 
	*/
	public function listDataExportAll($criteria=array(), $fields=array(), $input=array(), $data_roles=TRUE, $data_filter=TRUE) {
		$sys = new sys();

		$listFieldsCustom = $fields['listFieldsCustom']; // Get Fields Custom
		# SELECT QUERY DYNAMIC BY $fieldsName
		$fieldsName = $this->select_fieldsName($fields); // list fields in core field and custom fields.
		# LEFT JOIN WITH CUSTOM FIELDS
		$a = 'a'; 		// For alias join table
		$b = 'b';
		$check_export = $this->check_export($criteria["company_id"]);
		$count_custom = [];
		$select = [];
		$company_id = $criteria["company_id"];
		$cek_filter_is = FALSE;
		if ( countCustom($check_export) > 0 ) 
		{
			$check_export_form    = $this->GetFormExport($criteria["company_id"]); // Get core fields & custom fields
			if ( countCustom($check_export_form) > 0 ) 
			{
				$form = array();
				foreach ($check_export_form as $key => $value) 
				{
					if (isset($value[$this->table_module.'_fields_put_header']) AND $value[$this->table_module.'_fields_put_header'] == 1) 
					{
						$form[] = $value;
					}
				}

				$fields = $form;
			}

			foreach ($check_export as $key => $value) 
			{
				if ( $value[$this->table_module.'_export_fields_type'] == Config('setting.export_fields_type_core') AND $value[$this->table_module.'_export_checked'] == Config('setting.export_not_checked') ) 
				{
					$query_fields_name 	= $this->model_fields_class::where($this->table_module.'_fields_serial_id', '=', $value[$this->table_module.'_fields_serial_id'])->first();
					if ( $query_fields_name[$this->table_module.'_fields_name'] == $this->table_module.'_parent_type' ) 
					{
						$select[]	= "(
												CASE   
													WHEN sys_rel.rel_to_module = 'leads' THEN 'Lead'
													WHEN sys_rel.rel_to_module = 'contacts' THEN 'Contact'
													WHEN sys_rel.rel_to_module = 'org'  THEN 'Organization'
													WHEN sys_rel.rel_to_module is NULL THEN ''
												END
												) as ".$this->table_module."_parent_type";
					}
					elseif ( $query_fields_name[$this->table_module.'_fields_name'] == $this->table_module.'_parent_id' ) 
					{
						$select[]	= "(
													CASE   
														WHEN sys_rel.rel_to_module = 'leads' THEN ( SELECT CONCAT(COALESCE(leads.leads_first_name, ''),' ', COALESCE(leads.leads_last_name, '')) FROM leads WHERE leads.leads_serial_id = sys_rel.rel_to_id LIMIT 1)  
														WHEN sys_rel.rel_to_module = 'contacts' THEN ( SELECT CONCAT(COALESCE(contacts.contacts_salutation, ''),' ', COALESCE(contacts.contacts_first_name, ''),' ', COALESCE(contacts.contacts_last_name, ''),' @', COALESCE(org.org_name, ''),' @', COALESCE(org.org_unique_id, '')) FROM contacts LEFT JOIN sys_rel zz ON zz.rel_from_id = contacts.contacts_serial_id AND zz.rel_from_module = 'contacts' LEFT JOIN org ON org.org_serial_id = zz.rel_to_id WHERE contacts.contacts_serial_id = sys_rel.rel_to_id LIMIT 1)
														WHEN sys_rel.rel_to_module = 'org'  THEN ( SELECT org_name FROM org WHERE org.org_serial_id = sys_rel.rel_to_id LIMIT 1) 
														WHEN sys_rel.rel_to_module = 'projects' THEN ( SELECT projects_name FROM projects WHERE projects.projects_serial_id = sys_rel.rel_to_id LIMIT 1 )
														WHEN sys_rel.rel_to_module is NULL THEN ''
													END
													) as ".$this->table_module."_parent_id";
						$select[]	= "(
													CASE   
														WHEN sys_rel.rel_to_module = 'leads' THEN ( SELECT leads_unique_id FROM leads WHERE leads.leads_serial_id = sys_rel.rel_to_id LIMIT 1)  
														WHEN sys_rel.rel_to_module = 'contacts' THEN ( SELECT contacts_unique_id FROM contacts LEFT JOIN sys_rel zz ON zz.rel_from_id = contacts.contacts_serial_id AND zz.rel_from_module = 'contacts' LEFT JOIN org ON org.org_serial_id = zz.rel_to_id WHERE contacts.contacts_serial_id = sys_rel.rel_to_id LIMIT 1)
														WHEN sys_rel.rel_to_module = 'org'  THEN ( SELECT org_unique_id FROM org WHERE org.org_serial_id = sys_rel.rel_to_id LIMIT 1) 
														WHEN sys_rel.rel_to_module = 'projects' THEN ( SELECT projects_unique_id FROM projects WHERE projects.projects_serial_id = sys_rel.rel_to_id LIMIT 1 )
														WHEN sys_rel.rel_to_module is NULL THEN ''
													END
													) as parent_uuid";
					}
					elseif ( $query_fields_name[$this->table_module.'_fields_name'] == $this->table_module.'_owner' ) 
					{
						$select[]		= 'users.name as '.$this->table_module.'_owner';
					}
					elseif ( $query_fields_name[$this->table_module.'_fields_name'] == 'created_by' ) 
					{
						$select[]		= 'users_created.name as created_by';
					}
					elseif ( $query_fields_name[$this->table_module.'_fields_name'] == 'modified_by' ) 
					{
						$select[]		= 'users_modified.name as modified_by';
					}
					elseif ( $query_fields_name[$this->table_module.'_fields_name'] == 'date_created' )
					{
						$select[]		= "DATE_FORMAT(DATE_ADD(".$this->table_module.".date_created, INTERVAL 7 HOUR), '%Y-%m-%d %H:%i') as date_created";
					}
					elseif ( $query_fields_name[$this->table_module.'_fields_name'] == 'date_modified' )
					{
						$select[]		= "DATE_FORMAT(DATE_ADD(".$this->table_module.".date_modified, INTERVAL 7 HOUR), '%Y-%m-%d %H:%i') as date_modified";
					}
					elseif ( $query_fields_name[$this->table_module.'_fields_name'] == $this->table_module.'_date_start' )
					{
						$select[]		= "DATE_FORMAT(DATE_ADD(".$this->table_module.".".$this->table_module."_date_start, INTERVAL 7 HOUR), '%Y-%m-%d %H:%i') as ".$this->table_module."_date_start";
					}
					elseif ( $query_fields_name[$this->table_module.'_fields_name'] == $this->table_module.'_date_end' )
					{
						$select[]		= "DATE_FORMAT(DATE_ADD(".$this->table_module.".".$this->table_module."_date_end, INTERVAL 7 HOUR), '%Y-%m-%d %H:%i') as ".$this->table_module."_date_end";
					}
					elseif ( $query_fields_name[$this->table_module.'_fields_name'] == 'deals_serial_id' )
					{
						$select[]		= "deals.deals_name as deals_serial_id";
					}
					elseif ( $query_fields_name[$this->table_module.'_fields_name'] == 'projects_serial_id' )
					{
						$select[]		= "projects.projects_name as projects_serial_id";
					}
					elseif ( $query_fields_name[$this->table_module.'_fields_name'] == 'issue_serial_id' )
					{
						$select[]		= "issue.issue_name as issue_serial_id";
					}
					elseif ( $query_fields_name[$this->table_module.'_fields_name'] == 'tickets_serial_id' )
			        {
			            $select[]		= "IF((tickets.tickets_unique_id or IF(tickets.tickets_unique_id or tickets.tickets_unique_id != '',true,false)) or (tickets.tickets_name or IF(tickets.tickets_name or tickets.tickets_name != '',true,false)), CONCAT(COALESCE(CONCAT('[',NULLIF(tickets.tickets_unique_id,''),']'),'[No Unique ID]'),' - ',COALESCE(NULLIF(tickets.tickets_name,''),'No Subject')) , '-' ) as tickets_serial_id";
			         }
					else
					{
						$select[] 	= $this->table_module.'.'.$query_fields_name[$this->table_module.'_fields_name'];
					}
					
				}
				elseif ( $value[$this->table_module.'_export_fields_type'] == Config('setting.export_fields_type_custom') AND $value[$this->table_module.'_export_checked'] == Config('setting.export_not_checked') ) 
				{
					$library_values_maps_by_id = $sys->library_values_maps_by_id($value[$this->table_module.'_fields_serial_id'], $this->table_module, $criteria["company_id"]);
					if (!isEmpty($library_values_maps_by_id)) 
					{
						$select[] 					= 'mcv.'.$library_values_maps_by_id.' as custommm_'.$value[$this->table_module.'_fields_serial_id'];
					}

					$count_custom[$a]		= $a++;
				}
			}
		}
		else
		{
			$form 				= $this->GetAllCoreFields();
			$form 				= $this->GetCoreFieldsChange($form, $criteria["company_id"]); //get core fields from tbl module_fields_change
			$form 				=	$this->GetFormActive($form);
			$form_custom 		= $this->GetCustomFields($criteria["company_id"]);
			$fields 			= array_merge($form, $form_custom);
			
			if (countCustom($form) > 0 ) 
			{
				foreach ($form as $key => $value) 
				{
					if ( $value[$this->table_module.'_fields_status'] == Config('setting.fields_status_active') )
					{
						if ( $value[$this->table_module.'_fields_name'] == $this->table_module.'_parent_type' ) 
						{
							$select[]	= "(
													CASE   
														WHEN sys_rel.rel_to_module = 'leads' THEN 'Lead'
														WHEN sys_rel.rel_to_module = 'contacts' THEN 'Contact'
														WHEN sys_rel.rel_to_module = 'org'  THEN 'Organization'
														WHEN sys_rel.rel_to_module is NULL THEN ''
													END
													) as ".$this->table_module."_parent_type";
						}
						elseif ( $value[$this->table_module.'_fields_name'] == $this->table_module.'_parent_id' ) 
						{
							$select[]	= "(
													CASE   
														WHEN sys_rel.rel_to_module = 'leads' THEN ( SELECT CONCAT(COALESCE(leads.leads_first_name, ''),' ', COALESCE(leads.leads_last_name, '')) FROM leads WHERE leads.leads_serial_id = sys_rel.rel_to_id LIMIT 1)  
														WHEN sys_rel.rel_to_module = 'contacts' THEN ( SELECT CONCAT(COALESCE(contacts.contacts_salutation, ''),' ', COALESCE(contacts.contacts_first_name, ''),' ', COALESCE(contacts.contacts_last_name, ''),' @', COALESCE(org.org_name, ''),' @', COALESCE(org.org_unique_id, '')) FROM contacts LEFT JOIN sys_rel zz ON zz.rel_from_id = contacts.contacts_serial_id AND zz.rel_from_module = 'contacts' LEFT JOIN org ON org.org_serial_id = zz.rel_to_id WHERE contacts.contacts_serial_id = sys_rel.rel_to_id LIMIT 1)
														WHEN sys_rel.rel_to_module = 'org'  THEN ( SELECT org_name FROM org WHERE org.org_serial_id = sys_rel.rel_to_id LIMIT 1) 
														WHEN sys_rel.rel_to_module = 'projects' THEN ( SELECT projects_name FROM projects WHERE projects.projects_serial_id = sys_rel.rel_to_id LIMIT 1 )
														WHEN sys_rel.rel_to_module is NULL THEN ''
													END
													) as ".$this->table_module."_parent_id";
							$select[]	= "(
													CASE   
														WHEN sys_rel.rel_to_module = 'leads' THEN ( SELECT leads_unique_id FROM leads WHERE leads.leads_serial_id = sys_rel.rel_to_id LIMIT 1)  
														WHEN sys_rel.rel_to_module = 'contacts' THEN ( SELECT contacts_unique_id FROM contacts LEFT JOIN sys_rel zz ON zz.rel_from_id = contacts.contacts_serial_id AND zz.rel_from_module = 'contacts' LEFT JOIN org ON org.org_serial_id = zz.rel_to_id WHERE contacts.contacts_serial_id = sys_rel.rel_to_id LIMIT 1)
														WHEN sys_rel.rel_to_module = 'org'  THEN ( SELECT org_unique_id FROM org WHERE org.org_serial_id = sys_rel.rel_to_id LIMIT 1) 
														WHEN sys_rel.rel_to_module = 'projects' THEN ( SELECT projects_unique_id FROM projects WHERE projects.projects_serial_id = sys_rel.rel_to_id LIMIT 1 )
														WHEN sys_rel.rel_to_module is NULL THEN ''
													END
													) as parent_uuid";
						}
						elseif ( $value[$this->table_module.'_fields_name'] == $this->table_module.'_owner' ) 
						{
							$select[]		= 'users.name as '.$this->table_module.'_owner';
						}
						elseif ( $value[$this->table_module.'_fields_name'] == 'created_by' ) 
						{
							$select[]		= 'users_created.name as created_by';
						}
						elseif ( $value[$this->table_module.'_fields_name'] == 'modified_by' ) 
						{
							$select[]		= 'users_modified.name as modified_by';
						}
						elseif ( $value[$this->table_module.'_fields_name'] == 'date_created' )
						{
							$select[]		= "DATE_FORMAT(DATE_ADD(".$this->table_module.".date_created, INTERVAL 7 HOUR), '%Y-%m-%d %H:%i') as date_created";
						}
						elseif ( $value[$this->table_module.'_fields_name'] == 'date_modified' )
						{
							$select[]		= "DATE_FORMAT(DATE_ADD(".$this->table_module.".date_modified, INTERVAL 7 HOUR), '%Y-%m-%d %H:%i') as date_modified";
						}
						elseif ( $value[$this->table_module.'_fields_name'] == $this->table_module.'_date_start' )
						{
							$select[]		= "DATE_FORMAT(DATE_ADD(".$this->table_module.".".$this->table_module."_date_start, INTERVAL 7 HOUR), '%Y-%m-%d %H:%i') as ".$this->table_module."_date_start";
						}
						elseif ( $value[$this->table_module.'_fields_name'] == $this->table_module.'_date_end' )
						{
							$select[]		= "DATE_FORMAT(DATE_ADD(".$this->table_module.".".$this->table_module."_date_end, INTERVAL 7 HOUR), '%Y-%m-%d %H:%i') as ".$this->table_module."_date_end";
						}
						elseif ( $value[$this->table_module.'_fields_name'] == 'deals_serial_id' )
						{
							$select[]		= "deals.deals_name as deals_serial_id";
						}
						elseif ( $value[$this->table_module.'_fields_name'] == 'projects_serial_id' )
						{
							$select[]		= "projects.projects_name as projects_serial_id";
						}
						elseif ( $value[$this->table_module.'_fields_name'] == 'issue_serial_id' )
						{
							$select[]		= "issue.issue_name as issue_serial_id";
						}
						elseif ( $value[$this->table_module.'_fields_name'] == 'tickets_serial_id' )
			            {
			              $select[]		= "IF((tickets.tickets_unique_id or IF(tickets.tickets_unique_id or tickets.tickets_unique_id != '',true,false)) or (tickets.tickets_name or IF(tickets.tickets_name or tickets.tickets_name != '',true,false)), CONCAT(COALESCE(CONCAT('[',NULLIF(tickets.tickets_unique_id,''),']'),'[No Unique ID]'),' - ',COALESCE(NULLIF(tickets.tickets_name,''),'No Subject')) , '-' ) as tickets_serial_id";
			            }
						else
						{
							$select[] 	= $this->table_module.'.'.$value[$this->table_module.'_fields_name'];
						}
					}
				}
			}

			if (countCustom($form_custom) > 0 ) 
			{
				foreach ($form_custom as $key => $value) 
				{
					$select[] 					= 'mcv.'.$value[$this->table_module.'_custom_values_maps'].' as custommm_'.$value[$this->table_module.'_custom_fields_serial_id'];

					$count_custom[$a]		= $a++;
				}
			}
		}

		$select[] = "(
								    SELECT
								      GROUP_CONCAT(
								        COALESCE(documents.documents_name)
								        SEPARATOR ' ||||| '
								      )
								    FROM documents
								    WHERE documents.documents_serial_id IN (
								      SELECT
								        sys_rel.rel_from_id
								      FROM sys_rel
								      WHERE sys_rel.rel_from_module = 'documents'
								      AND sys_rel.rel_to_module = '{$this->table_module}'
								      AND sys_rel.rel_to_id = ".$this->table_module.".".$this->table_module."_serial_id
								    )
								  ) as documents";

	    $select[] = $this->table_module.".".$this->table_module."_uuid AS ".$this->table_module."_uuid";
	    $select[] = $this->table_module.".".$this->table_module."_uuid AS ".$this->table_module."_uuid_link";

		$fieldsNameConvert	= $this->convertFieldsName($select);
		
		$fieldsNameSysRel = "sys_rel.rel_to_module as ".$this->table_module."_parent_type, sys_rel.rel_to_id as ".$this->table_module."_parent_id";
											
		// $query = $this->model_class::select(DB::raw($fieldsNameConvert.",".$fieldsNameSysRel));
		$query = $this->model_class::select(DB::raw($fieldsNameConvert));
		# END 


		# LEFT JOIN WITH SYS REL
		$query->leftjoin('sys_rel', function($join) use ($criteria)
				{ 
					$join->on('sys_rel.rel_from_id', '=', $this->table_module.'.'.$this->table_module.'_serial_id')
					->where('sys_rel.rel_from_module', '=', $this->table_module);
				});
		# END 

		# LEFT JOIN WITH CUSTOM FIELDS
		$temp_alias = array();
		if (countCustom($listFieldsCustom) > 0 || countCustom($count_custom) > 0) // 	if fields custom available
		{
			$query->leftjoin($this->table_module.'_custom_values_ as mcv', function($join) use ($criteria)
							{
								$join->on('mcv.'.$this->table_module.'_serial_id', '=', $this->table_module.'.'.$this->table_module.'_serial_id')
								->where('mcv.company_id', '=', $criteria['company_id']);
							});

		}
			
		# IF OWNER AVAILABLE IN $fieldsName
		$query->leftjoin('users', 'users.id', '=', $this->table_module.'.'.$this->table_module.'_owner');

		# IF CREATED AVAILABLE IN $fieldsName
		$query->leftjoin('users as users_created', 'users_created.id', '=', $this->table_module.'.created_by');

		# IF MODIFIED AVAILABLE IN $fieldsName
		$query->leftjoin('users as users_modified', 'users_modified.id', '=', $this->table_module.'.modified_by');

		# IF DEALS_SERIAL_ID AVAILABLE IN $fieldsName
		$query->leftjoin('deals', 'deals.deals_serial_id', '=', $this->table_module.'.deals_serial_id');

		# IF PROJECTS_SERIAL_ID AVAILABLE IN $fieldsName
		$query->leftjoin('projects', 'projects.projects_serial_id', '=', $this->table_module.'.projects_serial_id');

		# IF ISSUE_SERIAL_ID AVAILABLE IN $fieldsName
		$query->leftjoin('issue', 'issue.issue_serial_id', '=', $this->table_module.'.issue_serial_id');

		# IF TICKETS_SERIAL_ID AVAILABLE IN $fieldsName
		$query->leftjoin('tickets', 'tickets.tickets_serial_id', '=', $this->table_module.'.tickets_serial_id');

		$table_module = $this->table_module;

		# DATA ROLES
		$roles 							= $this->get_roles($this->table_module, $criteria['company_id'], $criteria['users_id']);
		$myRecordOnlyCheck 	= $this->myRecordOnlyCheck($criteria['company_id'], $criteria['users_id']); //if filter view " (You) " Checked
		if ($data_roles == TRUE && countCustom($roles['contents']) > 0 && $myRecordOnlyCheck === FALSE) 
		{
			//$roles['contents'] is container member inside roles by users_id, example : team view then $roles['contents'] = array('11', '12', '13') 
			//if member != empty , then running this block
			//more detail about $roles, please check get_roles()
			$query->whereIn($this->table_module.'.'.$this->table_module.'_owner', $roles['contents']);
		}

		# FILTER FEATURE
		$relatedfilter = FALSE; /* Flag untk cek apabila ada filter related */
		if ($data_filter == TRUE)
		{
			//Running filter when $data_filter TRUE
			$filterView 				= $this->viewChecked($criteria['company_id'], $criteria['users_id']); // checked filter view active
			$filterViewName 		= $filterView[$this->table_module.'_view_name'];
			
			$filterCriteria 			= $this->generate_view($filterView, $criteria['company_id'], $criteria['users_id']); // get the selected filter 	

			$filterCriteria 			= $this->data_search($filterCriteria, $criteria['company_id'], $temp_alias, $listFieldsCustom, $b); // generate format for use filter feature
			

			$filterCriteriaCore 	= $filterCriteria['result']['core']; // filter by core field type
			$filterCriteriaDate 	= $filterCriteria['result']['date']; // filter by date type
			$filterCriteriaCustom = $filterCriteria['result']['custom']; // filter by custom field type

			if ($myRecordOnlyCheck === TRUE)
			{
				// if user choosen filter "You", then owner by sesson users_id login
				$query->where($this->table_module . "." . $this->table_module.'_owner', '=', $criteria['users_id']);
			}
			elseif (countCustom($filterView) > 0 && $filterViewName != 'Everyone' && $filterViewName != 'You') 
			{
				// get users statuc active or deactive
				$get_users = $this->model_users_class::select('users_status')
									->leftjoin('users_company as comp','comp.users_id','=','users.id')
									->where('id','=',$filterView['users_id'])
									->where('company_id','=',$filterView['company_id'])
									->first();

				// for check if users deactive
				if($get_users['users_status'] == 'deactive')
				{
					// get current users active
					$get_active = $this->model_view_class::select($this->table_module.'_view_serial_id', $this->table_module.'_view_name')
										->where($this->table_module.'_view_name','=','Everyone')
										->where('users_id','=', $criteria['users_id'])
										->where('company_id','=', $criteria['company_id'])
										->first();

					// update contact_view_serial_id into default
					$update = $this->model_view_checked_class::where('users_id','=',$criteria['users_id'])
									->where('company_id','=',$criteria['company_id'])
									->update([$this->table_module.'_view_serial_id' => $get_active[$this->table_module.'_view_serial_id']]);

					// change filter into default/Everyone when load data
					$filterView[$this->table_module.'_view_serial_id'] = $get_active[$this->table_module.'_view_serial_id'];
					$filterView[$this->table_module.'_view_name'] = 'Everyone';

					// change criteria into empty
					$filterCriteriaCore = array();
					$filterCriteriaDate = array();
					$filterCriteriaCustom = array();
				}
				
				$query_count = 1; // count query with left join
				//if user choosen filter, except filter 'Everyone' and 'You' 
				$checkFilterByOwner = in_array($this->table_module.'.'.$this->table_module.'_owner', array_column($filterCriteriaCore, 0)); 

				if ($checkFilterByOwner == TRUE) // if filter data by owner
				{
					$key_filter_owner = array_search($this->table_module.'.'.$this->table_module.'_owner', array_column($filterCriteriaCore, 0)); // get key, position owner in array		      
					if (is_array($filterCriteriaCore[$key_filter_owner][2]) && countCustom($filterCriteriaCore[$key_filter_owner][2]) > 0)
					{
						// if filter data by multi owner
						if ($filterCriteriaCore[$key_filter_owner][1] == "=") // when owner by IS 
						{
							$query->whereIn($this->table_module.'.'.$this->table_module.'_owner', $filterCriteriaCore[$key_filter_owner][2]);

						}elseif ($filterCriteriaCore[$key_filter_owner][1] == "!=") // when owner by isnt
						{
							$query->whereNotIn($this->table_module.'.'.$this->table_module.'_owner', $filterCriteriaCore[$key_filter_owner][2]);
						}
					}else
					{
					// if filter data by single owner
					$query->join('users AS singleuser', 'singleuser.id', $this->table_module . '.' . $this->table_module . '_owner');
					$query->where('singleuser.name', $filterCriteriaCore[$key_filter_owner][1], $filterCriteriaCore[$key_filter_owner][2]);
					}			
					unset($filterCriteriaCore[$key_filter_owner]); // remove owner in array, by position key
                    $filterCriteriaCore = array_values($filterCriteriaCore);
				}
				if (countCustom($filterCriteriaDate) > 0) // if filter data by date
				{
					$date_between 	= $this->date_between($filterCriteriaDate);
					$query->whereRaw($date_between);
				}
				if (countCustom($filterCriteriaCustom) > 0 ) // if filter data by custom fields
				{
					foreach ($filterCriteriaCustom as $value) 
					{
						if ($value[1] == "=" && is_array($value[2])) // operator is 
						{
							$query->whereIn($value[0], $value[2]);
						}
						elseif ($value[1] == "!=" && is_array($value[2])) // operator isn't
						{
							$query->whereNotIn($value[0], $value[2]);
						}
						elseif ( $value[1] === '==' ) // operator is_empty
						{
							$query->where(function ($query) use ($value) 
							{
								$query->where($value[0], '=', $value[2]);
								$query->orWhereNull($value[0]);
							});
						}
						elseif ( $value[1] === '!==' ) // operator is_not_empty
						{
							$query->where(function ($query) use ($value) 
							{
								$query->where($value[0], '!=', $value[2]);
								$query->whereNotNull($value[0]);
							});
						}
						else
						{
							$query->where($value[0], $value[1], $value[2]); // operator contains, start_with and end_with
						}
					}
				}
				$checkParentType 	= in_array($this->table_module.'.'.$this->table_module.'_parent_type', array_column($filterCriteriaCore, 0));
				if ($checkParentType == TRUE) // search data by calls_parent_type
				{
					$keyParentType 	= array_search($this->table_module.'.'.$this->table_module.'_parent_type', array_column($filterCriteriaCore, 0));
					$parentTypeOpp 			= $filterCriteriaCore[$keyParentType][1]; // get operator
					$parentTypeKeyword 	= $filterCriteriaCore[$keyParentType][2]; // get keyword
					if (is_array($parentTypeKeyword)) {
	
						$checkParentId 	= in_array($this->table_module.'.'.$this->table_module.'_parent_id', array_column($filterCriteriaCore, 0));
						if ($checkParentId) {
							$filterCriteriaCore = array_values($filterCriteriaCore); // reset key array, to 0 
							$keyParentId 		= array_search($this->table_module.'.'.$this->table_module.'_parent_id', array_column($filterCriteriaCore, 0));
							$parentIdOpp 		= $filterCriteriaCore[$keyParentId][1]; // get operator
							$parentIdKeyword 	= $filterCriteriaCore[$keyParentId][2]; // get keyword
							
							$whererelated = false;
							$cek_filter_is = TRUE;
							$query->where(function($whereparent) use ($parentTypeKeyword, $parentTypeOpp, $parentIdOpp, $parentIdKeyword) {
								foreach ($parentTypeKeyword as $key => $value) {
									$whereparent->{$key==0 ? 'where' : 'orWhere'}(function($whereparentid) use ($key, $parentTypeOpp, $value, $parentIdOpp, $parentIdKeyword){
										$whereparentid->where('sys_rel.rel_to_module', $parentTypeOpp, $value);
										if ($parentIdOpp <> "LIKE" AND $parentIdOpp <> "NOT LIKE") {
											$whererelated = true;
											$whereparentid->whereIn('sys_rel.rel_to_id', $parentIdKeyword);
										}
									});
								}		
							});

							if ($whererelated) {
								/* relatedt to / parent id berisikan id / menggunakan operator is */
								unset($filterCriteriaCore[$keyParentId]);
							}
						} else {
							$query->where(function($whereparent) use ($parentTypeKeyword, $parentTypeOpp) {
								foreach ($parentTypeKeyword as $key => $value) {
									$whereparent->{$key==0 ? 'where' : 'orWhere'}('sys_rel.rel_to_module', '=', $value);
								}	
							});
						}
					} else {
						$query->where('sys_rel.rel_to_module', $parentTypeOpp, $parentTypeKeyword);
					}
					unset($filterCriteriaCore[$keyParentType]);
				}
	
				$checkParentId 	= in_array($this->table_module.'.'.$this->table_module.'_parent_id', array_column($filterCriteriaCore, 0));
				if ($checkParentId == TRUE)  // search data by calls_parent_id
				{
					$filterCriteriaCore = array_values($filterCriteriaCore); // reset key array, to 0 
					$keyParentId 		= array_search($this->table_module.'.'.$this->table_module.'_parent_id', array_column($filterCriteriaCore, 0));
					$parentIdOpp 		= $filterCriteriaCore[$keyParentId][1]; // get operator
					$parentIdKeyword 	= $filterCriteriaCore[$keyParentId][2]; // get keyword
					
					if ($parentIdOpp == "LIKE" OR $parentIdOpp == "NOT LIKE") {
						$query->having($this->table_module.'_parent_name', $parentIdOpp, $parentIdKeyword);
					}elseif($parentIdOpp == "=" AND $cek_filter_is == FALSE){
						if(!is_array($parentIdKeyword))
						{
							$parentIdKeyword = explode(" ",$parentIdKeyword);
						}
						$query->wherein('sys_rel.rel_to_id', $parentIdKeyword);
					}elseif($parentIdOpp == "!="){
						if(is_numeric($parentIdKeyword))
						{
							$query->whereNotin('sys_rel.rel_to_id', $parentIdKeyword);
						}else{
							$query->whereNotNull('sys_rel.rel_to_id');
						}
					}elseif($parentIdOpp == "=="){
						$query->whereNull('sys_rel.rel_to_id');
					}
					
					unset($filterCriteriaCore[$keyParentId]);
				}
				// $query->where($filterCriteriaCore);
				if ( countCustom($filterCriteriaCore) > 0 ) 
				{
					foreach ($filterCriteriaCore as $key => $value) 
					{
						if ($value[1] == "=" && is_array($value[2])) // operator is 
						{
							$query->whereIn($value[0], $value[2]);
						}
						elseif ($value[1] == "!=" && is_array($value[2])) // operator isn't
						{
							$query->whereNotIn($value[0], $value[2]);
						}
						elseif ( $value[1] === '==' ) 
						{
							$query->where(function ($query) use ($value) 
							{
								$query->where($value[0], '=', $value[2]);
								$query->orWhereNull($value[0]);
							});
						}
						elseif ( $value[1] === '!==' ) 
						{
							$query->where(function ($query) use ($value) 
							{
								$query->where($value[0], '!=', $value[2]);
								$query->whereNotNull($value[0]);
							});
						}
						elseif ($value[1] == "=" && ($value[0] == $this->table_module.".date_created" || $value[0] == $this->table_module.".date_modified" || $value[0] == $this->table_module.".".$this->table_module."_date_start" || $value[0] == $this->table_module.".".$this->table_module."_date_end"))
						{
							$date_range = explode(" - ", $value[2]);
							if (countCustom($date_range) > 1)
							{
								$date_start = date('Y-m-d', strtotime($date_range[0])).' 00:00:00'; 
								$date_end 	= date('Y-m-d', strtotime($date_range[1])).' 23:59:59';

								$query->whereBetween($value[0], [$date_start, $date_end]);
							}
						}
						elseif ($value[1] == "!=" && ($value[0] == $this->table_module.".date_created" || $value[0] == $this->table_module.".date_modified" || $value[0] == $this->table_module.".".$this->table_module."_date_start" || $value[0] == $this->table_module.".".$this->table_module."_date_end"))
						{
							$date_range = explode(" - ", $value[2]);
							if (countCustom($date_range) > 1)
							{
								$date_start = date('Y-m-d', strtotime($date_range[0])).' 00:00:00'; 
								$date_end 	= date('Y-m-d', strtotime($date_range[1])).' 23:59:59';

								$query->whereNotBetween($value[0], [$date_start, $date_end]);
							}
						}
						else
						{
							$query->where($value[0], $value[1], $value[2]); // search data in core fields
						}
					}
				}
				// end
			}
		}

		# SEARCH FEATURE
		if (isset($input['subaction']) && $input['subaction'] == "search")
		{
			$query_count = 1; // count query with left join
			
			//ADD BY AGUNG -> 15/03/2019 -> SEARCHING OWNER BY TEAMS
			// HANDLE SEACH DEALS OWNER BY TEAM
			if(isset($input[$this->table_module.'_owner_opp']) AND isset($input[$this->table_module.'_owner']))
			{
				if($input[$this->table_module.'_owner_opp'] == "is" OR $input[$this->table_module.'_owner_opp'] == "isnt")
				{
					if(is_array($input[$this->table_module.'_owner']))
					{
						if(countCustom($input[$this->table_module.'_owner']) > 0 OR !empty($input[$this->table_module.'_owner']))
						{
							$search_owner = array();
							foreach($input[$this->table_module.'_owner'] as $key_owner => $val_owner)
							{
								$owner_id = $val_owner;
								if (is_array($val_owner)) 
								{
									$owner_id = $val_owner[0];
								}
								$search_owner[$key_owner] = explode("!@#$%^&*()", $owner_id);
								if(isset($search_owner[$key_owner][1]))
								{
									$input[$this->table_module.'_owner'][$key_owner] = $search_owner[$key_owner][1];
								}
							}
	
						}
					}
					else
					{
						$search_owner = explode("!@#$%^&*()", $input[$this->table_module.'_owner']);
						if(isset($search_owner[1]))
						{
							$input[$this->table_module.'_owner'] = array($search_owner[1]);
						}
					}
				}

			}
			// END HANDLE SEACH DEALS OWNER BY TEAM
			
			//when use search feature, running this block code
			$searchCriteria 			= $this->data_search($input, $criteria['company_id']);
			$searchCriteriaCore 	= $searchCriteria['result']['core']; // filter by core field type
			$searchCriteriaDate 	= $searchCriteria['result']['date']; // filter by type date
			$searchCriteriaCustom = $searchCriteria['result']['custom']; // filter by custom field type
			$searchCriteriaDateCustom = $searchCriteria['result']['date_custom']; // filter by custom field type

			$checkSearchByOwner = in_array($this->table_module.'.'.$this->table_module.'_owner', array_column($searchCriteriaCore, 0)); 
			if ($checkSearchByOwner == TRUE) // if search data by owner
			{
				$key_search_owner = array_search($this->table_module.'.'.$this->table_module.'_owner', array_column($searchCriteriaCore, 0)); // get key, position owner in array	      
				if (is_array($searchCriteriaCore[$key_search_owner][2]) && countCustom($searchCriteriaCore[$key_search_owner][2]) > 0)
				{
					// if search data by multi owner
					if ($searchCriteriaCore[$key_search_owner][1] == "=") // when owner by IS 
					{
						$query->whereIn($this->table_module.'.'.$this->table_module.'_owner', $searchCriteriaCore[$key_search_owner][2]);

					}elseif ($searchCriteriaCore[$key_search_owner][1] == "!=") // when owner by isnt
					{
						$query->whereNotIn($this->table_module.'.'.$this->table_module.'_owner', $searchCriteriaCore[$key_search_owner][2]);
					}
				}else
				{
					// if search data by single owner
					$query->join('users AS userowner', 'userowner.id', $this->table_module . '.' . $this->table_module . '_owner');
					$query->where('userowner.name', $searchCriteriaCore[$key_search_owner][1], $searchCriteriaCore[$key_search_owner][2]);
				}			
				// unset($searchCriteriaCore[$key_search_owner]); // remove owner in array, by position key
				array_splice($searchCriteriaCore, $key_search_owner, 1);
			}

			$checkSearchByCreated = in_array($this->table_module.'.created_by', array_column($searchCriteriaCore, 0)); 
			if ($checkSearchByCreated == TRUE) // if search data by created
			{
				$key_search_created = array_search($this->table_module.'.created_by', array_column($searchCriteriaCore, 0)); // get key, position created in array
				if (is_array($searchCriteriaCore[$key_search_created][2]) && countCustom($searchCriteriaCore[$key_search_created][2]) > 0)
				{
					// if search data by multi created
					if ($searchCriteriaCore[$key_search_created][1] == "=") // when owner by IS 
					{
						$query->whereIn($this->table_module.'.created_by', $searchCriteriaCore[$key_search_created][2]);

					}elseif ($searchCriteriaCore[$key_search_created][1] == "!=") // when owner by isnt
					{
						$query->whereNotIn($this->table_module.'.created_by', $searchCriteriaCore[$key_search_created][2]);
					}
				}else
				{
					// if search data by single created
					$query->join('users AS usercreated', 'usercreated.id', $this->table_module . '.' . $this->table_module . '_owner');
					$query->where('usercreated.name', $searchCriteriaCore[$key_filter_owner][1], $searchCriteriaCore[$key_filter_owner][2]);
				}			
				// unset($searchCriteriaCore[$key_search_created]); // remove created in array, by position key
				array_splice($searchCriteriaCore, $key_search_created, 1);

			}

			$checkSearchByModified = in_array($this->table_module.'.modified_by', array_column($searchCriteriaCore, 0)); 
			if ($checkSearchByModified == TRUE) // if search data by modified
			{
				$key_search_modified = array_search($this->table_module.'.modified_by', array_column($searchCriteriaCore, 0)); // get key, position modified in array
				if (is_array($searchCriteriaCore[$key_search_modified][2]) && countCustom($searchCriteriaCore[$key_search_modified][2]) > 0)
				{
					// if search data by multi modified
					if ($searchCriteriaCore[$key_search_modified][1] == "=") // when owner by IS 
					{
						$query->whereIn($this->table_module.'.modified_by', $searchCriteriaCore[$key_search_modified][2]);

					}elseif ($searchCriteriaCore[$key_search_modified][1] == "!=") // when owner by isnt
					{
						$query->whereNotIn($this->table_module.'.modified_by', $searchCriteriaCore[$key_search_modified][2]);
					}
				}else
				{
					// if search data by single modified
					$query->where('users_modified.name', $searchCriteriaCore[$key_search_modified][1], $searchCriteriaCore[$key_search_modified][2]);
				}			
				// unset($searchCriteriaCore[$key_search_modified]); // remove modified in array, by position key
				array_splice($searchCriteriaCore, $key_search_modified, 1);
			}

			if (countCustom($searchCriteriaDate) > 0) // if search data by date
			{
				$date_between 	= $this->date_between($searchCriteriaDate);
				$query->whereRaw($date_between);
			}

			if (countCustom($searchCriteriaDateCustom) > 0) // if search data by date
			{
				$date_between_custom 	= $this->date_between_custom($searchCriteriaDateCustom);

				$query->whereRaw($date_between_custom);
			}

			// Update By Rendi 11.03.2019
			if (countCustom($searchCriteriaCustom) > 0 ) // if search data by custom fields
			{
				foreach ($searchCriteriaCustom as $value) 
				{
					if ($value[1] == "=" && is_array($value[2])) // operator is 
					{
						// only for custom multipleoption
						$fieldmaps = explode('.', $value[0]);
						$fields_type = $this->model_custom_fields_class::select($this->table_module.'_custom_fields_input_type')
																		->where($this->table_module.'_custom_values_maps','=',$fieldmaps[1])
																		->where('company_id','=',$criteria['company_id'])
																		->first();
						if ($fields_type[$this->table_module.'_custom_fields_input_type'] === 'multipleoption') 
						{
							$query->where(function ($query) use ($value) 
							{
								foreach ($value[2] as $value2) 
								{
									$query->orwhere($value[0], 'LIKE', '%'.$value2.'%');
								}
							});
						}// end
						else
						{
							$query->whereIn($value[0], $value[2]);
						}
					}
					elseif ($value[1] == "IN") // operator isn't
					{
						$query->whereIn($value[0],$value[2]);
					}
					elseif ($value[1] == "!=" && is_array($value[2])) // operator isn't
					{
						$query->whereNotIn($value[0], $value[2]);
					}elseif ($value[2] === "%%") {

					}
					else
					{
						$query->where($value[0], $value[1], $value[2]); // Like
					}
				}
			}

			$checkParentType 	= in_array($this->table_module.'.'.$this->table_module.'_parent_type', array_column($searchCriteriaCore, 0));
            if ($checkParentType == TRUE) // search data by calls_parent_type
			{
				$keyParentType 	= array_search($this->table_module.'.'.$this->table_module.'_parent_type', array_column($searchCriteriaCore, 0));
				$parentTypeOpp 			= $searchCriteriaCore[$keyParentType][1]; // get operator
				$parentTypeKeyword 	= $searchCriteriaCore[$keyParentType][2]; // get keyword
				if (is_array($parentTypeKeyword)) {

					$checkParentId 	= in_array($this->table_module.'.'.$this->table_module.'_parent_id', array_column($searchCriteriaCore, 0));
					if ($checkParentId) {
						$searchCriteriaCore = array_values($searchCriteriaCore); // reset key array, to 0 
						$keyParentId 		= array_search($this->table_module.'.'.$this->table_module.'_parent_id', array_column($searchCriteriaCore, 0));
						$parentIdOpp 		= $searchCriteriaCore[$keyParentId][1]; // get operator
						$parentIdKeyword 	= $searchCriteriaCore[$keyParentId][2]; // get keyword

						$whererelated = false;
						$cek_filter_is = TRUE;
						$query->where(function($whereparent) use ($parentTypeKeyword, $parentTypeOpp, $parentIdOpp, $parentIdKeyword) {
							foreach ($parentTypeKeyword as $key => $value) {
								$whereparent->{$key==0 ? 'where' : 'orWhere'}(function($whereparentid) use ($key, $parentTypeOpp, $value, $parentIdOpp, $parentIdKeyword){
									$whereparentid->where('sys_rel.rel_to_module', $parentTypeOpp, $value);
									if ($parentIdOpp <> "LIKE" AND $parentIdOpp <> "NOT LIKE") {
										$whererelated = true;
										$whereparentid->whereIn('sys_rel.rel_to_id', $parentIdKeyword);
									}
								});
							}	
						});

						if ($whererelated) {
							/* relatedt to / parent id berisikan id / menggunakan operator is */
							unset($searchCriteriaCore[$keyParentId]);
						}
					} else {
						$query->where(function($whereparent) use ($parentTypeKeyword, $parentTypeOpp) {
							foreach ($parentTypeKeyword as $key => $value) {
								$whereparent->{$key==0 ? 'where' : 'orWhere'}('sys_rel.rel_to_module', '=', $value);
							}	
						});
					}
				} else {
						if($parentTypeOpp == 'LIKE'){
							$countValue = $this->model_fields_class::select('dropdown_options.dropdown_options_value')
															->join('dropdown', 'dropdown.dropdown_name', $this->table_module.'_fields.' . $this->table_module . '_fields_options')
															->join('dropdown_options', 'dropdown_options.dropdown_serial_id', 'dropdown.dropdown_serial_id')
															->where($this->table_module . '_fields_name','=',$this->table_module.'_parent_type')
															->where('dropdown_options.dropdown_options_label', $parentTypeOpp, $parentTypeKeyword)
															->first();

							if(isset($countValue['dropdown_options_value']) && $countValue['dropdown_options_value'] == "org")
							{
								$query->where('sys_rel.rel_to_module', $parentTypeOpp, '%'.$countValue['dropdown_options_value'].'%');
							}
							else
							{
								$query->where('sys_rel.rel_to_module', $parentTypeOpp, $parentTypeKeyword);
							}
						}else
						{
							$query->where('sys_rel.rel_to_module', $parentTypeOpp, $parentTypeKeyword);
						}
				}
				unset($searchCriteriaCore[$keyParentType]);
			}

			$checkParentId 	= in_array($this->table_module.'.'.$this->table_module.'_parent_id', array_column($searchCriteriaCore, 0));
			if ($checkParentId == TRUE)  // search data by calls_parent_id
			{
				$searchCriteriaCore = array_values($searchCriteriaCore); // reset key array, to 0 
				$keyParentId 		= array_search($this->table_module.'.'.$this->table_module.'_parent_id', array_column($searchCriteriaCore, 0));
				$parentIdOpp 		= $searchCriteriaCore[$keyParentId][1]; // get operator
				$parentIdKeyword 	= $searchCriteriaCore[$keyParentId][2]; // get keyword
				
				if ($parentIdOpp == "LIKE" OR $parentIdOpp == "NOT LIKE") {
					$query->having($this->table_module.'_parent_name', $parentIdOpp, $parentIdKeyword);
				}elseif($parentIdOpp == "=" AND $cek_filter_is == FALSE){
					if(!is_array($parentIdKeyword))
					{
						$parentIdKeyword = explode(" ",$parentIdKeyword);
					}
					$query->wherein('sys_rel.rel_to_id', $parentIdKeyword);
				}elseif($parentIdOpp == "!="){
						if(is_numeric($parentIdKeyword))
						{
							$query->whereNotin('sys_rel.rel_to_id', $parentIdKeyword);
						}else{
							$query->whereNotNull('sys_rel.rel_to_id');
						}
				}elseif($parentIdOpp == "=="){
					$query->whereNull('sys_rel.rel_to_id');
				}
				
				unset($searchCriteriaCore[$keyParentId]);
			}

			// ADD BY ANDRIAN
			// FOR SOLVED SEARCH 'is_empty' and 'is_not_empty'
			if ( countCustom($searchCriteriaCore) ) 
			{
				foreach ($searchCriteriaCore as $key => $value) 
				{
					if ($value[1] == "=" && is_array($value[2])) // operator is 
					{
						$query->whereIn($value[0], $value[2]);
					}
					elseif ($value[1] == "!=" && is_array($value[2])) // operator isn't
					{
						$query->whereNotIn($value[0], $value[2]);
					}
					elseif ( $value[1] === '==' ) 
					{
						$query->where(function ($query) use ($value) 
						{
							$query->where($value[0], '=', $value[2]);
							$query->orWhereNull($value[0]);
						});
					}
					elseif ( $value[1] === '!==' ) 
					{
						$query->where(function ($query) use ($value) 
						{
							$query->where($value[0], '!=', $value[2]);
							$query->whereNotNull($value[0]);
						});
					}
					else
					{
						$query->where($value[0], $value[1], $value[2]); // search data in core fields
					}
				}
			}
			// END ADD BY ANDRIAN
			// END FOR SOLVED SEARCH 'is_empty' and 'is_not_empty'
		}

		# SEARCH GLOBAL FEATURE
		if (isset($_GET['subaction']))
		{
			if ($_GET['subaction'] == "search_global") 
			{
				$query_count = 1; // count query with left join

				$keyword 	= $criteria['keyword'];
				$fieldsglobalfeature 	= array();

				foreach ($fieldsName as $value) 
				{
					$name 			= $value;
					$exp_value 	= explode(" ", $value);
					if (is_array($exp_value) && isset($exp_value[2])) 
					{
						$fieldsglobalfeature[]		= $exp_value[0];
					}else{
						$fieldsglobalfeature[] 	= $name;
					}
				}

				$query->where(function ($query) use ($fieldsglobalfeature, $keyword) 
				{
					for ($i=0; $i < countCustom($fieldsglobalfeature); $i++) 
					{ 
						$query->orWhere($fieldsglobalfeature[$i], 'LIKE', $keyword.'%');
					}
				});
			}
		} 

		$query->where($this->table_module.'.company_id', '=', $criteria['company_id']);
		$query->where($this->table_module.'.deleted', '=', Config('setting.NOT_DELETED'));

		 if ($relatedfilter == FALSE) {
      		$items_count 	= $query->distinct($this->table_module . "." . $this->table_module . "_serial_id")->count($this->table_module . "." . $this->table_module . "_serial_id");
    	}
		# END

		if ($relatedfilter == TRUE) {
			$criteria['total_data'] = $query->get()->count();
		} else {
			$criteria['total_data'] = $items_count;
		}

		if (in_array($criteria['company_id'], Config("setting.company_testing"))) {
			$result = $query->toSql();

			foreach($query->getBindings() as $binding)
			{
				$value = is_numeric($binding) ? $binding : "'".$binding."'";
				$result = preg_replace('/\?/', $value, $result, 1);
			}

			$process_queue = saveImportQueue($fields, $result, $this->module, $criteria);
		} 
		elseif (in_array('*', Config("setting.company_testing"))) 
		{
			$result = $query->toSql();
			$result = str_replace("distinct", "", $result);

			foreach($query->getBindings() as $binding)
			{
				$value = is_numeric($binding) ? $binding : "'".$binding."'";
				$result = preg_replace('/\?/', $value, $result, 1);
			}

			$process_queue = saveImportQueue($fields, $result, $this->module, $criteria);
		}
		else 
		{
			$result 				= $query->get();

			if (countCustom($result) > 0)
			{
				$result = $result->toArray();
			}

			if ( countCustom($result) > 0 ) 
			{
				foreach ($result as $key => $value) 
				{
					/*
					* Cek Monetary field
					*/
					$MonetaryCustomFields = $this->model_custom_fields_class::select($this->table_module."_custom_fields_serial_id", $this->table_module."_custom_values_maps")
									->where('company_id', '=', $criteria["company_id"])
									->where($this->table_module . "_custom_fields_input_type", "monetary")
									->get();

					$MonetaryCustomFields = countCustom($MonetaryCustomFields) > 0 ? $MonetaryCustomFields->toArray() : [];
					$hard_org = '';
					$org_unique_id = '' ;
	
					foreach ($value as $key2 => $value2) 
					{
						$trim_key2 = substr($key2, 0, 9); // Result custommm_
	
						if ( $trim_key2 == 'custommm_' ) // For Custom
						{
							$trim_fix = substr($key2, 9);
	
							// Add mask money to monetary converted data
							if (!isEmpty($MonetaryCustomFields))
							{
								
								$MonetaryExist = array_filter($MonetaryCustomFields, function($var) use ($trim_fix){
														return $var[$this->table_module."_custom_fields_serial_id"] == $trim_fix;
													});
									$result[$key][$key2] = $value2;
							} else {
								$result[$key][$key2] = $value2;
							}
						}
						else // For core
						{
							if ( $key2 == 'documents' )
							{
								// $value2 = $value2;
								if ( !isEmpty($value2) )
								{
									$temp = array(); // For set default temp
									$value2 = explode(' ||||| ', $value2); // For split value documents name
									$sys_api_documents = sys_api('Documents');
									foreach ($value2 as $key3 => $value3)
									{
										$documents_name = $value3;
										$temp[] = $sys_api_documents->getDocumentsFromAwsExport($documents_name, $company_id);
									}
									$value2 = implode("\n", $temp);
								}
							}
							elseif ( $key2 == $this->table_module.'_parent_id' )
							{
								// $value2 = $value2;
	
								if (isset($value[$this->table_module.'_parent_type'])) 
								{
									if ( $value[$this->table_module.'_parent_type'] == 'Contact' )
									{ 
										$explode 	= explode('@', $value2);
									
										$value2  	= isset($explode[0]) ? $explode[0] : '';
									
										$hard_org 	= isset($explode[1]) ? $explode[1] : '';
									}
									elseif ( $value[$this->table_module.'_parent_type'] == 'Organization')
									{
										$explode 	= explode('|||||', $value2);
										$value2  	= isset($explode[0]) ? $explode[0] : '';
										$org_unique_id 	= isset($explode[1]) ? $explode[1] : '';
									}
								}
							}
	
							$result[$key][$key2] = isEmpty($value2) ? '-' : $value2;
						}
					}
	
					$result[$key]['Organization'] = $hard_org;
					$result[$key]['Org Unique ID'] = $org_unique_id;
				}
			}			
			$result = $this->dataProcessExport2($result, $fields);
		}

		$data_users = sys_api('Users')->get_users_by_id($criteria['users_id']);
		$name = '';
		if (!isEmpty($data_users)) 
		{
			$name = $data_users['name'];
		}		
		
		$syslog_action = "Users : ".$name." &#8594; Export All : ".number_format($criteria['total_data'])." Data";
		$syslog 	= $sys->sys_api_syslog( $syslog_action, 'export_all', $this->table_module, '', $criteria['users_id'], $criteria['company_id'] );

		return $result;
	}

	public function dataProcessExport2($data, $fields) {
		$label = array();
		$result_label = array();
		$result_data = array();
		$result = array();	

		foreach ($data as $key => $value) 
		{
			foreach ($fields as $key2 => $value2) 
			{
				if (isset($value2[$this->table_module.'_fields_name'])) 
				{
					if (isset($value2[$this->table_module.'_fields_status']) && $value2[$this->table_module.'_fields_status'] !== Config('setting.fields_status_inactive'))
					{
						// if (
						// 	$value2[$this->table_module.'_fields_name'] != $this->table_module.'_last_module'
						// 	AND $value2[$this->table_module.'_fields_name'] != $this->table_module.'_last_date'
						// 	AND $value2[$this->table_module.'_fields_name'] != $this->table_module.'_last_id') 
						// {
							$label[$value2[$this->table_module.'_fields_name']] = $value2[$this->table_module.'_fields_label'];					
						// }
					}
				}
				elseif (isset($value2[$this->table_module.'_custom_fields_name']))
				{
					if (isset($value2[$this->table_module.'_custom_fields_status']) && $value2[$this->table_module.'_custom_fields_status'] !== Config('setting.custom_fields_status_inactive'))
					{
						$label['custommm_'.$value2[$this->table_module.'_custom_fields_serial_id']] = $value2[$this->table_module.'_custom_fields_label'];
					}
				}
			}	

			foreach ($value as $key3 => $value3) 
			{
				if ($key3 == 'documents') 
				{
					$label['documents'] = 'Documents';
				}
        else if ($key3 == $this->table_module.'_uuid') 
        {
          $label[$this->table_module.'_uuid'] = ucfirst($this->table_module).' UUID';
        }
        else if ($key3 == $this->table_module.'_uuid_link')
        {
          $label[$this->table_module.'_uuid_link'] = 'Detail ' . ucfirst($this->table_module);
        }
			}
		}
		

		// # set header excel
		if (countCustom($label) > 0) 
		{
			foreach ($label as $key => $value) 
			{
				# set header excel
				$result[0][] = $value;
				$i = 1;
				foreach ($data as $key_data => $value_data) 
				{
					foreach ($value_data as $key_data2 => $value_data2) 
					{
						if ($key_data2 == $this->table_module . "_last_id" && $key == $this->table_module . "_last_id") {
							$result[$i][] = $value_data2;
						} else if ($key_data2 == $this->table_module . "_first_id" && $key == $this->table_module . "_first_id") {
							$result[$i][] = $value_data2;
						} else if ($key_data2 == $this->table_module . "_parent_id" && $key == $this->table_module . "_parent_id") {
							$result[$i][] = $data[$key_data][$this->table_module . "_parent_name"];
						} else if ($key_data2 == $this->table_module.'_uuid_link' && $key == $this->table_module.'_uuid_link') {

              $toDetail = "";
              if(env("APP_ENV") == "local") {
                $toDetail = 'http://localhost/frontend/'.$this->table_module.'/detail/';
              } else {
                $toDetail = 'https://crmv5.barantum.com/'.$this->table_module.'/detail/';
              }
              $result[$i][] = $toDetail.$value_data2;

            } else if ($key_data2 == $key && $key != $this->table_module . "_last_id" && $key != $this->table_module . "_first_id") {

							$result[$i][] = $value_data2;
						}
					}
					$i++;
				}
			}
		}

		return $result;	
	}

	public function DownloadExportAll($data = [], $list_fields = [], $company_id){
		$uuid = Str::uuid();
		$fields = [];

		$path = Config('setting.exportallpath');

		if(!File::isDirectory($path)){

			File::makeDirectory($path, 0777, true, true);
	
		}

		$filename = $path . "/" . $this->table_module . "-" . $uuid . ".csv";
		$output = fopen($filename, 'w+');
		$delimeter = ';';

		$resfields = $data;
		if (countCustom($resfields) > 0) {
			$title = $resfields[0];

			foreach( $title as $value )
			{
				fputs($output, $value);
				fputs($output, $delimeter);
			}
			
			$data = array_values(array_splice($resfields, 1));
			
			fputs($output, "\r\n");
			if ($data > 0 ) {
				
				foreach($data as $keyrow => $row)
				{
					foreach($row as $keyval => $val)
					{
						$val = str_replace(array("\n", "\r", ";"), ' ', $val);
					
						fputs($output, '"'.$val.'"');
						fputs($output, $delimeter);
					}   
					fputs($output, "\r\n");
				}
			}			
		}

		$access = 'public';
		$upload = File::get($filename);
		$storage 	= Storage::disk('s3')->put('company_' . $company_id . '/file/' . $this->table_module . "-" . $uuid . ".csv", $upload, $access);
		$urlaws 	= Storage::disk('s3')->url('company_' . $company_id . '/file/' . $this->table_module . "-" . $uuid . ".csv");
		File::delete($filename);
		
		return $urlaws;
	}

	public function getCustomValuesIdStd($id_custom_values=array())
	{
		$results = array();
		foreach ($id_custom_values as $key => $value) 
			{
				$get_id = str_replace('c_', '', $key);

				$results[$get_id]['values'][] = $value;
				$results[$get_id]['id'][] = '';
			}

		return $results;
	}

	public function GetNameModifiedAndCreated($data)
	{
		$modified_by = isset($data['modified_by']) ? $data['modified_by'] : 0;
		if ($modified_by != 0 OR $modified_by != null)
		{
			$name = $this->model_users_class::where('id', '=', $modified_by)->get()->toArray();

			if ( countCustom($name) > 0 ) 
			{
				$data['modified_by_name'] = $name[0]['name'];
				$data['modified_by']      = $modified_by;
			}
		}

		$created_by = $data['created_by'];
		if ($created_by != 0 OR $created_by != null)
		{
			$name = $this->model_users_class::where('id', '=', $created_by)->get()->toArray();
			if ( countCustom($name) > 0 ) 
			{
				$data['created_by_name'] = $name[0]['name'];
				$data['created_by']      = $created_by;
			}
		}

		return $data;
	}

	public function ChangeDateFormat($data, $company_id)
	{
		$new_format = $this->model_class::select(DB::raw("DATE_FORMAT(date_created, '%d %M %Y %H:%i') as date_created,
			DATE_FORMAT(date_modified, '%d %M %Y %H:%i') as date_modified,
			DATE_FORMAT(".$this->table_module."_date_start, '%d %M %Y %H:%i') as ".$this->table_module."_date_start,
			DATE_FORMAT(".$this->table_module."_date_end, '%d %M %Y %H:%i') as ".$this->table_module."_date_end"))
								->where($this->table_module.'_serial_id', '=', $data[$this->table_module.'_serial_id'])
								->where('company_id', $company_id)
								->first();

		$data['date_created']  = $new_format['date_created'];
		$data['date_modified'] = $new_format['date_modified'];
		$data[$this->table_module.'_date_start'] = $new_format[$this->table_module.'_date_start'];
		$data[$this->table_module.'_date_end']   = $new_format[$this->table_module.'_date_end'];

		return $data;
	}

	public function getTeamsNameByOwnerDetail($data=array(), $company_id=0)
	{
		$teams_name = array();
		$result = array();
		if(countCustom($data) > 0)
		{
			if(isset($data[$this->table_module.'_owner_id']))
			{
				$teams = $this->model_teams_class::select('users_teams.teams_name')
																					->leftJoin('users_teams_map as utm', 'utm.teams_serial_id', '=', 'users_teams.teams_serial_id')
																					->where('users_teams.company_id' , '=', $company_id)
																					->where('users_teams.deleted', '=', config('setting.NOT_DELETED'))
																					->where('utm.users_id', '=', $data[$this->table_module.'_owner_id'])
																					->get();
				if(countCustom($teams) > 0)
				{
					$teams = $teams->toArray();
					
					$total_teams = countCustom($teams);
					$teams_name = array();

					foreach($teams as $key_teams => $val_teams)
					{
						$teams_name[] = $val_teams['teams_name'];
					}
				}

				$result = $teams_name;
			}
		}

		return $result;
	}

	public function save_log_campaign_call($log_string = "", $company_id=0)
	{
		#save data Log
		$folder = "assets/campaign_log/".$company_id;
		$file_name = date('Y-m-d').".txt";
    	
    if(!is_dir($folder))
    {
      mkdir($folder);
    }
    
    $full_path = './'.$folder.'/'. $file_name;

		if (!file_exists($full_path)) 
		{
			$handle = fopen($full_path, 'a') or die('Cannot open file:  '.$full_path); //implicitly creates file
			$log_string = explode('|||', $log_string);
			if (isset($log_string[0])) 
			{
				$data = $log_string[0];
				fwrite($handle, $data);			
			}

			if (isset($log_string[1])) {
				$data = "\n".$log_string[1];
				fwrite($handle, $data);
			}
		}
		else
		{
			$handle = fopen($full_path, 'a') or die('Cannot open file:  '.$full_path); //implicitly creates file
			$log_string = explode('|||', $log_string);
			if (isset($log_string[0])) 
			{
				$data = "\n".$log_string[0];
				fwrite($handle, $data);			
			}

			if (isset($log_string[1])) {
				$data = "\n".$log_string[1];
				fwrite($handle, $data);
			}
		}

		return true;
	}

	public function get_history_pbx($input=array(), $company_id=0, $users_id='')
	{
		$result = array();
		$from_module = isset($input['module']) ? $input['module'] : '';
		$from_serial = isset($input['serial_id']) ? $input['serial_id'] : '';

		$query_calls 		= $this->model_class::select(DB::raw($this->table_module.'.*, users.name as name_owner'))
																				 	 	->leftjoin('sys_rel', $this->table_module.'.'.$this->table_module.'_serial_id', '=', 'sys_rel.rel_from_id')
																						->leftjoin('users', 'users.id', '=', 'calls.calls_owner')
																						->where('sys_rel.rel_to_module', '=', $from_module)
																						->where('sys_rel.rel_to_id', '=', $from_serial)
																						->where('sys_rel.rel_from_module', '=', $this->table_module)
																						->where('calls.calls_status', '=', 'Planned')
																						->where('calls.calls_owner', "=", $users_id)
																						->where($this->table_module.'.company_id', '=', $company_id)
																						->where($this->table_module.'.deleted', '=', Config('setting.NOT_DELETED'))
																						->get();

		if (!isEmpty($query_calls)) 
		{
			$result = $query_calls->toArray();
		}
		
		return $result;
	}

	public function moduleView($uuid, $company_id)
	{
		$result 			= array();
		$fieldsname 	= array(
									$this->table_module."_view_uuid", 
									$this->table_module."_view_serial_id", 
									$this->table_module."_view_name", 
									$this->table_module."_view_visibility",
									);
		$data 	= $this->model_view_class::select($fieldsname)
									->where($this->table_module.'_view_uuid', '=', $uuid)
									->where('company_id', '=', $company_id)
									->first();
		if (countCustom($data) > 0) 
		{
			$result = $data->toArray();
		}

    return $result;
	}

	public function moduleViewCriteria($view_serial_id=0, $company_id = 0)
	{
		$sys = new sys();
		$data 	= $this->model_view_criteria_class::select($this->table_module.'_view_criteria.*', 'b.'.$this->table_module.'_fields_name', 'c.'.$this->table_module.'_custom_fields_name',  'b.'.$this->table_module.'_fields_input_type', 'c.'.$this->table_module.'_custom_fields_input_type')
									->leftjoin($this->table_module.'_fields as b', 'b.'.$this->table_module.'_fields_serial_id', '=', $this->table_module.'_view_criteria.'.$this->table_module.'_fields_serial_id')
									->leftjoin($this->table_module.'_custom_fields as c', 'c.'.$this->table_module.'_custom_fields_serial_id', '=', $this->table_module.'_view_criteria.'.$this->table_module.'_fields_serial_id')
									->where($this->table_module.'_view_criteria.'.$this->table_module.'_view_serial_id', '=', $view_serial_id)
									->get();
		if (countCustom($data) > 0) 
		{
			$data 						= $data->toArray();
			foreach ($data as $key => $row) 
			{
				$contents 	 = array();

				if($row[$this->table_module.'_view_criteria_type'] != '0')
				{
					if(isset($row[$this->table_module.'_custom_fields_name']))
					{
						$data[$key][$this->table_module.'_fields_name'] =  $row[$this->table_module.'_custom_fields_name'];
						$data[$key][$this->table_module.'_fields_input_type'] =  $row[$this->table_module.'_custom_fields_input_type'];
					}
				}else{
					$data[$key][$this->table_module.'_custom_fields_name'] =  $row[$this->table_module.'_fields_name'];
					$data[$key][$this->table_module.'_custom_fields_input_type'] =  $row[$this->table_module.'_fields_input_type'];
				}
				
				$field_serial_id  	= $row[$this->table_module.'_fields_serial_id'];
				$criteria_operator	= $row[$this->table_module.'_view_criteria_operator'];
				$criteria_value 	= $row[$this->table_module.'_view_criteria_value'];
				$criteria_type 		= $row[$this->table_module.'_view_criteria_type'];

				if ($sys->isJSON($criteria_value)) 
				{
					$criteria_value 	= json_decode($row[$this->table_module.'_view_criteria_value']);
				}
				
				                        
				if($criteria_type != '0')
				{
					if(isset($row[$this->table_module.'_custom_fields_name']))
					{
						$data[$key][$this->table_module.'_fields_name'] =  $row[$this->table_module.'_custom_fields_name'];
					}
				}

				$criteria_value 	= str_replace('"', "", $criteria_value);				
				$data[$key][$this->table_module.'_view_criteria_value'] = $criteria_value;
				$data[$key]['contents'] 	= $contents;
			}
		}

	  return $data;
	}

	public function setContentFilter($fields, $data, $company_id, $users_id)
	{
		// print_r($fields);
		foreach ($fields as $key => $value) 
		{
			if ($value['status_checked'] == 'checked') 
			{
				$getKeys = false;
				if (countCustom($data) > 0) 
				{
					if ($value['fields_type'] == '0') 
					{
						$getKeys 	= array_search($value[$this->table_module.'_fields_name'], array_column($data, $this->table_module.'_fields_name'));	
					}
					else
					{
						$getKeys 	= array_search($value[$this->table_module.'_custom_fields_name'], array_column($data, $this->table_module.'_custom_fields_name'));
					}
				}

				if ( $getKeys !== false )
				{
					if ($value[$this->table_module.'_fields_name'] == $this->table_module.'_parent_id') {
						$parentid = $data[$getKeys][$this->table_module.'_view_criteria_value'];
						$datarelated = [];
						if (is_array($parentid)) {
							foreach ($parentid as $keyparent => $valueparent) {
								$related = $this->{'ajax_parent'}($valueparent->type,$company_id, $users_id, '', $valueparent->id);
								foreach ($related['data'] as $relkey => $relvalue) {
									$datarelated[] = [
										'dropdown_options_reltype' => $related['reltype'],
										'dropdown_options_value' => $relvalue->dropdown_options_value,
										'dropdown_options_label' => $relvalue->dropdown_options_label
									];
								}
							};
							$fields[$key]['content'] = $datarelated;
						} else {
							$fields[$key]['content'] = $parentid;
						}
					} else {
						$fields[$key]['content'] = $data[$getKeys][$this->table_module.'_view_criteria_value'];
					}
					
					$fields[$key]['view_criteria_operator'] = $data[$getKeys][$this->table_module.'_view_criteria_operator'];
				}
				else
				{
					$fields[$key]['content'] = '';
					$fields[$key]['view_criteria_operator'] = '';
				}
			}
			else
			{
				$fields[$key]['content'] = '';
				$fields[$key]['view_criteria_operator'] = '';
			}
		}

		return $fields;
	}

		// get identity filter 'You' from deals_view
		public function defaultFilter($company_id, $users_id )
	{
		$query = $this->model_view_class::where('users_id', '=', $users_id)
							->where('company_id', '=', $company_id)
							->where($this->table_module.'_view_name', '=', 'Everyone')
							->first();

		if(isset($query))
		{
			$result = $query->toArray();
		}
		return $result;
	}

	public function date_between($data_search_date=array())
	{
		# defined variable
		$date 		= "";
		$result 	= "";

		$curdate 			= date("Y-m-d"); // default currant date is gmt+0
		$curdatetime 	= date("Y-m-d H:i:s");
		$curmonth 		= date("m");
		$curyears 		= date("Y");
		$curdate 			= date("Y-m-d", strtotime("+7 hours", strtotime($curdatetime))); // +7 in current date
		// DATE_ADD(leads.date_created, INTERVAL 7 HOUR ) BETWEEN '2017-06-04 00:00:00' AND '2017-06-04 23:59:59' AND 
		// DATE_ADD(leads.date_modified, INTERVAL 7 HOUR ) BETWEEN '2017-06-04 00:00:00' AND '2017-06-04 23:59:59'

		if (countCustom($data_search_date) > 0) 
		{
			// today, last_7_days, next_7_days, last_30_days, next_30_days, 
			// this_month, last_month, next_month, 
			// this_year, last_year, next_year, 
			// select_date, before, after, yesterday
			
			foreach ($data_search_date as $key => $value) 
			{
				$field_name 	= $value[0];
				$operator 		= $value[1];
				$keyword 			= $value[2];

				if ($operator == "today")
				{
					$date_first 		= date("Y:m:d H:i:s",strtotime($curdate." 00:00:00 - 7 HOUR"));
					$date_second 		= date("Y:m:d H:i:s",strtotime($curdate." 23:59:59 - 7 HOUR"));

					$date 					= $field_name." BETWEEN '".$date_first."' AND '".$date_second."'";
				}
				elseif ($operator == "last_5_days")
				{
					$date_first 		= date("Y:m:d H:i:s",strtotime(date("Y-m-d", strtotime("-5 days", strtotime($curdate)))." 00:00:00 -7 HOUR"));
			 		$date_second 		= date("Y:m:d H:i:s",strtotime(date("Y-m-d", strtotime("-1 days", strtotime($curdate)))." 23:59:59 -7 HOUR"));

					$date 					= $field_name." BETWEEN '".$date_first."' AND '".$date_second."'";
				}
				elseif ($operator == "last_7_days")
				{
					$date_first 		= date("Y:m:d H:i:s",strtotime(date("Y-m-d", strtotime("-7 days", strtotime($curdate)))." 00:00:00 -7 HOUR"));
			 		$date_second 		= date("Y:m:d H:i:s",strtotime(date("Y-m-d", strtotime("-1 days", strtotime($curdate)))." 23:59:59 -7 HOUR"));

					$date 					= $field_name." BETWEEN '".$date_first."' AND '".$date_second."'";
				}
				elseif ($operator == "next_7_days")
				{
					$date_first 		= date("Y:m:d H:i:s",strtotime(date("Y-m-d", strtotime("+1 days", strtotime($curdate)))." 00:00:00 -7 HOUR"));
					$date_second 		= date("Y:m:d H:i:s",strtotime(date("Y-m-d", strtotime("+7 days", strtotime($curdate)))." 23:59:59 -7 HOUR"));

					$date 					= $field_name." BETWEEN '".$date_first."' AND '".$date_second."'";
				}
				elseif ($operator == "last_30_days")
				{
					$date_first 		= date("Y:m:d H:i:s",strtotime(date("Y-m-d", strtotime("-30 days", strtotime($curdate)))." 00:00:00 -7 HOUR"));
			 		$date_second 		= date("Y:m:d H:i:s",strtotime(date("Y-m-d", strtotime("-1 days", strtotime($curdate)))." 23:59:59 -7 HOUR"));

					$date 					= $field_name." BETWEEN '".$date_first."' AND '".$date_second."'";
				}
				elseif ($operator == "next_30_days")
				{
					$date_first 		= date("Y:m:d H:i:s",strtotime(date("Y-m-d", strtotime("+1 days", strtotime($curdate)))." 00:00:00 -7 HOUR"));
			 		$date_second 		= date("Y:m:d H:i:s",strtotime(date("Y-m-d", strtotime("+30 days", strtotime($curdate)))." 23:59:59 -7 HOUR"));

					$date 					= $field_name." BETWEEN '".$date_first."' AND '".$date_second."'";
				}
				elseif ($operator == "this_month")
				{
					$first_date_this_month   = date("Y:m:d H:i:s",strtotime(date("Y-m-d", strtotime("first day of this month")) . " 00:00:00 -7 HOUR"));
					$end_date_this_month     = date("Y:m:d H:i:s",strtotime(date("Y-m-d", strtotime("last day of this month")) . " 23:59:59 - 7 HOUR"));

					$date           = "(".$field_name." BETWEEN '".$first_date_this_month."' AND '".$end_date_this_month."')";


				}
				elseif ($operator == "last_month")
				{
					$first_date_last_month   = date("Y:m:d H:i:s",strtotime(date("Y-m-d", strtotime("first day of last month")) . " 00:00:00 -7 HOUR"));
					$end_date_last_month     = date("Y:m:d H:i:s",strtotime(date("Y-m-d", strtotime("last day of last month")) . " 23:59:59 -7 HOUR"));

					$date = "(".$field_name." BETWEEN '".$first_date_last_month."' AND '".$end_date_last_month."')";
				}
				elseif ($operator == "next_month")
				{
					$first_date_next_month   = date("Y:m:d H:i:s",strtotime(date("Y-m-d", strtotime("first day of next month")) . " 00:00:00 -7 HOUR"));
					$end_date_next_month     = date("Y:m:d H:i:s",strtotime(date("Y-m-d", strtotime("last day of next month")) . " 23:59:59 -7 HOUR"));

					$date = "(".$field_name." BETWEEN '".$first_date_next_month."' AND '".$end_date_next_month."')";
				}
				elseif ($operator == "this_year")
				{
					$first_date_this_year   = date("Y:m:d H:i:s",strtotime(date("Y-m-d", strtotime("first day of january this year")) . " 00:00:00 - 7 HOUR"));
					$end_date_this_year    = date("Y:m:d H:i:s",strtotime(date("Y-m-d", strtotime("last day of december this year")) . " 23:59:59 - 7 HOUR"));

					$date = "(".$field_name." BETWEEN '".$first_date_this_year."' AND '".$end_date_this_year."')";
				}
				elseif ($operator == "last_year")
				{
					$first_date_last_year   = date("Y:m:d H:i:s",strtotime(date("Y-m-d",strtotime("first day of january last year")) . " 00:00:00 - 7 HOUR"));
					$end_date_last_year    = date("Y:m:d H:i:s",strtotime(date("Y-m-d", strtotime("last day of december last year")) . " 23:59:59 - 7 HOUR"));

					$date = "(".$field_name." BETWEEN '".$first_date_last_year."' AND '".$end_date_last_year."')";
				}
				elseif ($operator == "next_year")
				{
					$first_date_next_year   = date("Y:m:d H:i:s",strtotime(date("Y-m-d", strtotime("first day of january next year")) . " 00:00:00 - 7 HOUR"));
					$end_date_next_year    = date("Y:m:d H:i:s",strtotime(date("Y-m-d", strtotime("last day of december next year")) . " 23:59:59 - 7 HOUR"));

					$date = "(".$field_name." BETWEEN '".$first_date_next_year."' AND '".$end_date_next_year."')";
				}
				elseif ($operator == "select_date")
				{
					$exp_keyword 		= explode("and", $keyword);
					$date_first 		= date("Y-m-d", strtotime($exp_keyword[0]))." 00:00:00";
			 		$date_second 		= date("Y-m-d", strtotime($exp_keyword[1]))." 23:59:59";

					$date = "{$field_name} BETWEEN '".date("Y-m-d H:i:s", strtotime("{$date_first} -7 HOUR"))."' AND '".date("Y-m-d H:i:s", strtotime("{$date_second} -7 HOUR"))."'";
				}
				elseif ($operator == "before")
				{
					$before_date		= date("Y:m:d H:i:s",strtotime(date("Y-m-d", strtotime($keyword))." -7 HOUR"));
					if($field_name == $this->table_module.'.'.$this->table_module.'_last_date')
					{
					$date 					= "(".$field_name." <= '".$before_date."' OR ".$field_name." IS NULL )";
					}
					else
					{
						$date 					= $field_name." <= '".$before_date."'";
					}

				}
				elseif ($operator == "after")
				{
					$after_date 		= date("Y:m:d H:i:s",strtotime(date("Y-m-d", strtotime($keyword))." -7 HOUR"));
					$date 					= $field_name." >= '".$after_date."'";
				}
				elseif ($operator == "yesterday")
				{
					$yesterday 			= date("Y-m-d", strtotime("-1 days", strtotime($curdate)));
					$date_first 		= date("Y:m:d H:i:s",strtotime($yesterday." 00:00:00 -7 HOUR"));
					$date_second 		= date("Y:m:d H:i:s",strtotime($yesterday." 23:59:59 -7 HOUR"));

					$date 					= $field_name." BETWEEN '".$date_first."' AND '".$date_second."'";
				}

				$and = "";
				if ($key < countCustom($data_search_date)-1) {
					$and = " and ";
				}
				$result .= $date. $and;
			}
		}
		
		return $result;
	}

	public function GetUsersInformation($users_id)
	{
		// define variable 
		$result = array();

		$GetUsersInformation = $this->model_users_information_class::where('users_id', '=', $users_id)->first();

		if ( countCustom($GetUsersInformation) > 0 ) 
		{
			$result = $GetUsersInformation->toArray();
		}

		return $result;
	}

	// PBX
	public function PBXSave($request, $company_id, $users_id)
	{
		// modified by : gilang
    // save calls parent org
		if ( isset($request['org_serial_id'])) 
		{
      if ($request['org_serial_id'] != '') 
      {
          $request['module'] = 'org';
          $request['serial_id'] = $request['org_serial_id'];
      }
    }
 
    unset($request['org_serial_id']); //unset input type hidden org_serial_id

		$uuid4 = $this->uuid::uuid4();

		$input = $request;

		// for purposing save sysrel
		if (isset($request['serial_id']) && !empty($request['serial_id']) && isset($request['module']) && !empty($request['module'])) {
			$input['calls_parent_type'] = $request['module'];
			$input['calls_parent_id'] = $request['serial_id'];
		}

		unset($request['_token']);
		unset($request['module']); // unset for sys_rel
		unset($request['serial_id']); // unset for sys_rel
		unset($request['phone_number']); // espessially for incoming calls
		unset($request['calls_custom_values_text']); // espessially for incoming calls
		
		unset($request['src']); // for zoiper
		unset($request['dst']); // for zoiper
		unset($request['calls_date_start']);
		unset($request['calls_date_start_click']);
		unset($request['calls_date_end']);
		unset($request['calls_date_end_click']);
		unset($request['calls_serial_id']);

		unset($request['channel']);
		unset($request['uniqueid']);

		$input_custom = array();
		foreach ($request as $key => $value) 
		{
			$prefix_custom  = substr($key, 0, 7);
			if ($prefix_custom == "custom_") 
			{
				$fields_name = substr_replace($key, '', 0, 7);
				$input_custom[$fields_name] = $request[$key];
				unset($request[$key]);
			}
		}

		$request[$this->table_module.'_uuid'] 			= $uuid4->toString();
		$request[$this->table_module.'_owner'] 			= $users_id;
		$request[$this->table_module.'_status'] 		= 'Held';

		if (isset($input['calls_date_start_click'])) 
		{
			$request[$this->table_module.'_date_start'] = date('Y-m-d H:i:s', strtotime($input['calls_date_start_click'].':00'));
		}
		else
		{
			$request[$this->table_module.'_date_start'] = date('Y-m-d H:i:s', strtotime($input['calls_date_start']));			
		}
		$request[$this->table_module.'_date_end'] 	= date('Y-m-d H:i:s');

		if (isset($request['calldate'])) 
		{
			$start_date = $request['calldate'];
			unset($request['calldate']);
	  	$billsec = $request['billsec'];
	  	$end_date = date('Y-m-d H:i:s', strtotime($start_date. "+".$billsec." seconds"));

	  	$dateTime1 = date_create($end_date);
	  	$dateTime2 = date_create($start_date);

	  	$datediff 		= date_diff($dateTime1, $dateTime2);
	  	$request[$this->table_module.'_duration_hours'] 		= sprintf("%02d", $datediff->format("%h"));
			$request[$this->table_module.'_duration_minutes'] 	= sprintf("%02d", $datediff->format("%i"));
			$request[$this->table_module.'_date_start'] 				= date('Y-m-d H:i:s', strtotime($start_date. "-7 hours"));
			$request[$this->table_module.'_date_end'] 					= date('Y-m-d H:i:s', strtotime($end_date. "-7 hours"));
		}
		else
		{
			// $datetime1 		= date_create($request[$this->table_module.'_date_end']);
			// $datetime2 		= date_create($request[$this->table_module.'_date_start']);
			// $datediff 		= date_diff($datetime1, $datetime2);
			$request['calls_duration_hours'] 		= '00';
			$request['calls_duration_minutes'] 		= '00';
		}


		$request['created_by'] 			= $users_id;
		$request['date_created'] 		= date('Y-m-d H:i:s');
		$request['company_id'] 			= $company_id;
		
		foreach($request as $key => $value)
		{
			if(is_array($value))
			{
				unset($request[$key]);
				$request[$key] = implode(" ",$value);
			}
		}
		// SAVE CORE CALLS
		$save = $this->model_class::create($request);
		$last_id = $save->calls_serial_id;

		/*SAVE CUSTOM FIELDS HERE*/
		$input_custom[$this->table_module.'_serial_id'] = $last_id;
		$save_custom = $this->save_data_custom($input_custom, $company_id, $users_id);

		// if (isset($input["module"]) AND isset($input["serial_id"]) AND $input["serial_id"] > 0)
		// {
		// 	// SAVE SYS_REL
		// 	$rel['rel_from_module'] = 'calls';
		// 	$rel['rel_from_id'] 		= $last_id;
		// 	$rel['rel_to_module'] 	= $input['module'];
		// 	$rel['rel_to_id'] 			= $input['serial_id'];
		// 	$rel_save = $this->model_sys_rel_class::create($rel);
		// }

		# SAVE SYS REL : PARENTY_TYPE AND RELATED TO
	  if ( !empty($input[$this->table_module.'_parent_type']) AND !empty($input[$this->table_module.'_parent_id']) )
	  {
	  	// Insert Relation rel_sys
		  $rel['rel_from_module'] = $this->table_module;
		  $rel['rel_from_id'] 		= $last_id;
		  $rel['rel_to_module']   = $input[$this->table_module.'_parent_type'];
	    $rel['rel_to_id'] 			= $input[$this->table_module.'_parent_id'];
	    $rel['company_id'] 			= $company_id;
		  $save_rel_sys 	= $this->model_sys_rel_class::create($rel);
		}

		# SAVE AND SYNC DATA WITH GOOGLE CALENDER
		if (app()->environment() == 'production')
		{
			//Enable feature only if environment = production (disable if in developer and release)
			// $googleCalender 	= $this->saveGoogleCalender($request, $company_id, $users_id);
		}
		# END 

		// Auto Generate Unique ID
		if ( empty($request[$this->table_module.'_unique_id']) )
		{
			$update_request[$this->table_module.'_unique_id']	= 'CALLS-'.sprintf("%'.010d", $company_id.$last_id);
			$this->model_class::where($this->table_module.'_serial_id', '=', $last_id)
												->where('company_id', '=', $company_id)
												->update($update_request);
		}

	  return $last_id;

	}

	public function save_last_activities($request=array(), $this_id=0, $company_id=0)
	{	
		$sys = new sys();

		if(!empty($request['deals_serial_id']) AND $request['deals_serial_id'] != '' AND $request['deals_serial_id'] != 0)
    {
        $deals_id         = $request['deals_serial_id'];
        $last_module      = $this->table_module;
        $last_id          = $this_id;
        // $company_id       = $company_id;
        // Query last activities
        $sys->sys_api_last_activities_deals( $deals_id, $last_module, $last_id, $company_id);
    }

    if(!empty($request['projects_serial_id']) AND $request['projects_serial_id'] != '' AND $request['projects_serial_id'] != 0)
    {
        $projects_id     = $request['projects_serial_id'];
        $last_module     = $this->table_module;
        $last_id         = $this_id;
        // $company_id      = $company_id;
        // Query last activities
        $sys->sys_api_last_activities_projects( $projects_id, $last_module, $last_id, $company_id);
    }

    if(!empty($request['issue_serial_id']) AND $request['issue_serial_id'] != '' AND $request['issue_serial_id'] != 0)
    {
        $issue_id       = $request['issue_serial_id'];
        $last_module    = $this->table_module;
        $last_id        = $this_id;
        // $company_id     = $company_id;
        // Query last activities
        $sys->sys_api_last_activities_issue( $issue_id, $last_module, $last_id, $company_id);
    }

    if(!empty($request['tickets_serial_id']) AND $request['tickets_serial_id'] != '' AND $request['tickets_serial_id'] != 0)
    {
        $tickets_id    	= $request['tickets_serial_id'];
        $last_module    = $this->table_module;
        $last_id        = $this_id;
        // $company_id     = $company_id;
        // Query last activities
        $sys->sys_api_last_activities_tickets( $tickets_id, $last_module, $last_id, $company_id);
    }


		if ( !empty($request[$this->table_module.'_parent_type']) AND !empty($request[$this->table_module.'_parent_id']) )
		{
			// check contact any have org on sysrel
			// add by gilang
			$rel_from_module = $request[$this->table_module.'_parent_type'];
			$rel_from_id		 = $request[$this->table_module.'_parent_id'];
			$rel_to_module	 = 'org';
			
			$data_org = $this->model_sys_rel_class::where('rel_from_module', '=', $rel_from_module)
																				->where('rel_from_id', '=', $rel_from_id)
																				->where('rel_to_module', '=', $rel_to_module)
																				->first();
																				
			if (countCustom($data_org) > 0){
				$customers_module = 'org';
        $customers_id     = $data_org['rel_to_id'];
        $last_module      = $this->table_module;
        $last_id          = $this_id;
        // $company_id       = $company_id;
        // Query last activities
	      $sys->sys_api_last_activities($customers_module, $customers_id, $last_module, $last_id, $company_id);
			}
			// end check contact any have org on sysrel
			
			if (isset($request['org_serial_id'])) {
				if ($request['org_serial_id'] != $request[$this->table_module.'_parent_id']) {
					$customers_module = 'org';
					$customers_id 		= $request['org_serial_id'];
					$last_module 			= $this->table_module;
					$last_id 					= $this_id;
					// $company_id 			= $company_id;
					$sys->sys_api_last_activities($customers_module, $customers_id, $last_module, $last_id, $company_id);
				}
			}

			// if (isset($request['org_parent_type']) && !empty($request['org_parent_type']) && isset($request['org_parent_id']) && !empty($request['org_parent_id'])) {
			// 	if ($request['org_parent_id'] != $request[$this->table_module.'_parent_id']) {
			// 		$customers_module = $request['org_parent_type'];
			// 		$customers_id 		= $request['org_parent_id'];
			// 		$last_module 			= $this->table_module;
			// 		$last_id 					= $this_id;
			// 		// $company_id 			= $company_id;
			// 		$sys->sys_api_last_activities($customers_module, $customers_id, $last_module, $last_id, $company_id);
			// 	}
			// }
			
			$customers_module = $request[$this->table_module.'_parent_type'];
			$customers_id 		= $request[$this->table_module.'_parent_id'];
			$last_module 			= $this->table_module;
			$last_id 					= $this_id;
			// $company_id 			= $company_id;
			// Query last activities
			$sys->sys_api_last_activities($customers_module, $customers_id, $last_module, $last_id, $company_id);
		}

		return true;
	}

	# Created By Andika Tri H
  # 6-10-2022
	#For SAVE UPCOMING ACTIVITY
	public function save_upcoming_activities($request=array(), $this_id=0, $company_id=0)
	{	
		$sys = new sys();

		if(!empty($request['deals_serial_id']) AND $request['deals_serial_id'] != '' AND $request['deals_serial_id'] != 0)
		{
			$upcoming_id         = $request['deals_serial_id'];
			$upcoming_module         = 'deals';
		}
		else if(!empty($request['projects_serial_id']) AND $request['projects_serial_id'] != '' AND $request['projects_serial_id'] != 0)
		{
			$upcoming_id     = $request['projects_serial_id'];
			$upcoming_module         = 'projects';
		}
		else if(!empty($request['issue_serial_id']) AND $request['issue_serial_id'] != '' AND $request['issue_serial_id'] != 0)
		{
			$upcoming_id       = $request['issue_serial_id'];
			$upcoming_module         = 'issue';
		}
		else if(!empty($request['tickets_serial_id']) AND $request['tickets_serial_id'] != '' AND $request['tickets_serial_id'] != 0)
		{
			$upcoming_id    	= $request['tickets_serial_id'];
			$upcoming_module         = 'tickets';
		}
		else{
			$upcoming_id    	= 0;
			$upcoming_module         = '';
		}
		
		if ( !empty($request[$this->table_module.'_parent_type']) AND !empty($request[$this->table_module.'_parent_id']) )
		{
			$customers_module = $request[$this->table_module.'_parent_type'];
			$customers_id 		= $request[$this->table_module.'_parent_id'];
			$last_module 			= $this->table_module;
			$last_id 					= $this_id;

			$sys->sys_api_upcoming_activities($customers_module, $customers_id, $last_module, $last_id, $company_id, $upcoming_id, $upcoming_module);
			// Query upcoming activities
		}

		// if (isset($request['org_parent_type']) && !empty($request['org_parent_type']) && isset($request['org_parent_id']) && !empty($request['org_parent_id'])) {
		// 	$customers_module = $request['org_parent_type'];
		// 	$customers_id 		= $request['org_parent_id'];
		// 	$last_module 			= $this->table_module;
		// 	$last_id 					= $this_id;
			
		// 	$sys->sys_api_upcoming_activities($customers_module, $customers_id, $last_module, $last_id, $company_id, $upcoming_id, $upcoming_module);
		// 	// Query upcoming activities
		// }

		return true;
	}
	#End SAVE UPCOMING ACTIVITY
	
	//SAVE FIRST ACTIVITY
	public function save_first_activities($request=array(), $this_id=0, $company_id=0)
	{
		$sys = new sys();
		
		if(isset($request[$this->table_module.'_parent_type']) AND isset($request[$this->table_module.'_parent_id']))
		{

			$parent_module = $request[$this->table_module.'_parent_type'];
			$parent_serial_id = $request[$this->table_module.'_parent_id'];
	
			if($parent_module == "leads" OR $parent_module == "contacts" OR $parent_module == "org" )
			{
				$parent_module_class = 'model_'.$parent_module.'_class';

				$fist_id =	$this->{$parent_module_class}::select($parent_module."_first_id","first_activity_date")
																		->where("company_id", "=",  $company_id)
																		->where($parent_module."_serial_id", "=",  $parent_serial_id)
																		->where("deleted", "=", config('setting.NOT_DELETED'))
																		->first();
	

				if(countCustom($fist_id) > 0)
				{
					$fist_id = $fist_id->toArray();
					
					if($fist_id[$parent_module."_first_id"] == 0)
					{
						$first_activity_date=date('Y-m-d H:i:s');
						if(isset($request[$this->table_module.'_date_start'])){
							$first_activity_date = date('Y-m-d H:i:s', strtotime($request[$this->table_module.'_date_start']));
						}
						$update = array(
							$parent_module."_first_id"			 	=> 	$this_id,
							$parent_module."_first_module"	 	=>	$this->table_module,
							"first_activity_id"			 					=> 	$this_id,
							"first_activity_module"			 			=> 	$this->table_module,
							"first_activity_date"							=>	$first_activity_date,
							"last_activity_module" 						=> 	$this->table_module,
							"last_activity_id" 								=> 	$this_id,
							"last_activity_date"							=> 	$first_activity_date,
							"upcoming_activity_id"			 			=> 	$this_id,
							"upcoming_activity_module"	 			=>	$this->table_module,
						);
						$this->{$parent_module_class}::where("company_id", "=",  $company_id)
																					->where($parent_module."_serial_id", "=",  $parent_serial_id)
																					->where("deleted", "=",  config('setting.NOT_DELETED'))
																					->update($update); 

						if(!empty($request['deals_serial_id']) AND $request['deals_serial_id'] != '' AND $request['deals_serial_id'] != 0)
						{
								$deals_id         = $request['deals_serial_id'];
								$first_module      = $this->table_module;
								$first_id          = $this_id;
								// $company_id       = $company_id;
								$first_date				=	$first_activity_date;
								// Query first activities
								$sys->sys_api_first_activities_deals( $deals_id, $first_module, $first_id, $company_id, $first_date);
						}

						if(!empty($request['projects_serial_id']) AND $request['projects_serial_id'] != '' AND $request['projects_serial_id'] != 0)
						{
								$projects_id     = $request['projects_serial_id'];
								$first_module     = $this->table_module;
								$first_id         = $this_id;
								// $company_id      = $company_id;
								$first_date				=	$first_activity_date;
								// Query first activities
								$sys->sys_api_first_activities_projects( $projects_id, $first_module, $first_id, $company_id, $first_date);
						}

						if(!empty($request['issue_serial_id']) AND $request['issue_serial_id'] != '' AND $request['issue_serial_id'] != 0)
						{
								$issue_id       = $request['issue_serial_id'];
								$first_module    = $this->table_module;
								$first_id        = $this_id;
								// $company_id     = $company_id;
								$first_date				=	$first_activity_date;
								// Query first activities
								$sys->sys_api_first_activities_issue( $issue_id, $first_module, $first_id, $company_id, $first_date);
						}

						if(!empty($request['tickets_serial_id']) AND $request['tickets_serial_id'] != '' AND $request['tickets_serial_id'] != 0)
						{
								$tickets_id    	= $request['tickets_serial_id'];
								$first_module    = $this->table_module;
								$first_id        = $this_id;
								// $company_id     = $company_id;
								$first_date				=	$first_activity_date;
								// Query first activities
								$sys->sys_api_first_activities_tickets( $tickets_id, $first_module, $first_id, $company_id, $first_date);
						}
					}
				}
				
			}

			// if(isset($request['org_parent_type']) && !empty($request['org_parent_type']) && isset($request['org_parent_id']) && !empty($request['org_parent_id']))
			// {
			// 	$parent_module = $request['org_parent_type'];
			// 	$parent_serial_id = $request['org_parent_id'];
			// 	$parent_module_class = 'model_'.$parent_module.'_class';
				
			// 	$fist_id =	$this->{$parent_module_class}::select($parent_module."_first_id","first_activity_date")
			// 															->where("company_id", "=",  $company_id)
			// 															->where($parent_module."_serial_id", "=",  $parent_serial_id)
			// 															->where("deleted", "=", config('setting.NOT_DELETED'))
			// 															->first();
	
			// 	if(countCustom($fist_id) > 0)
			// 	{
			// 		$fist_id = $fist_id->toArray();
					
			// 		if($fist_id[$parent_module."_first_id"] == 0)
			// 		{
			// 			$first_activity_date=date('Y-m-d H:i:s');
			// 			if(isset($request[$this->table_module.'_date_start'])){
			// 				$first_activity_date = date('Y-m-d H:i:s', strtotime($request[$this->table_module.'_date_start']));
			// 			}
			// 			$update = array(
			// 				$parent_module."_first_id"			 	=> 	$this_id,
			// 				$parent_module."_first_module"	 	=>	$this->table_module,
			// 				"first_activity_id"			 					=> 	$this_id,
			// 				"first_activity_module"			 			=> 	$this->table_module,
			// 				"first_activity_date"							=>	$first_activity_date,
			// 				"last_activity_module" 						=> 	$this->table_module,
			// 				"last_activity_id" 								=> 	$this_id,
			// 				"last_activity_date"							=> 	$first_activity_date,
			// 				"upcoming_activity_id"			 			=> 	$this_id,
			// 				"upcoming_activity_module"	 			=>	$this->table_module,
			// 			);
			// 			$this->{$parent_module_class}::where("company_id", "=",  $company_id)
			// 																		->where($parent_module."_serial_id", "=",  $parent_serial_id)
			// 																		->where("deleted", "=",  config('setting.NOT_DELETED'))
			// 																		->update($update); 

			// 			if(!empty($request['deals_serial_id']) AND $request['deals_serial_id'] != '' AND $request['deals_serial_id'] != 0)
			// 			{
			// 					$deals_id         = $request['deals_serial_id'];
			// 					$first_module      = $this->table_module;
			// 					$first_id          = $this_id;
			// 					// $company_id       = $company_id;
			// 					$first_date				=	$first_activity_date;
			// 					// Query first activities
			// 					$sys->sys_api_first_activities_deals( $deals_id, $first_module, $first_id, $company_id, $first_date);
			// 			}

			// 			if(!empty($request['projects_serial_id']) AND $request['projects_serial_id'] != '' AND $request['projects_serial_id'] != 0)
			// 			{
			// 					$projects_id     = $request['projects_serial_id'];
			// 					$first_module     = $this->table_module;
			// 					$first_id         = $this_id;
			// 					// $company_id      = $company_id;
			// 					$first_date				=	$first_activity_date;
			// 					// Query first activities
			// 					$sys->sys_api_first_activities_projects( $projects_id, $first_module, $first_id, $company_id, $first_date);
			// 			}

			// 			if(!empty($request['issue_serial_id']) AND $request['issue_serial_id'] != '' AND $request['issue_serial_id'] != 0)
			// 			{
			// 					$issue_id       = $request['issue_serial_id'];
			// 					$first_module    = $this->table_module;
			// 					$first_id        = $this_id;
			// 					// $company_id     = $company_id;
			// 					$first_date				=	$first_activity_date;
			// 					// Query first activities
			// 					$sys->sys_api_first_activities_issue( $issue_id, $first_module, $first_id, $company_id, $first_date);
			// 			}

			// 			if(!empty($request['tickets_serial_id']) AND $request['tickets_serial_id'] != '' AND $request['tickets_serial_id'] != 0)
			// 			{
			// 					$tickets_id    	= $request['tickets_serial_id'];
			// 					$first_module    = $this->table_module;
			// 					$first_id        = $this_id;
			// 					// $company_id     = $company_id;
			// 					$first_date				=	$first_activity_date;
			// 					// Query first activities
			// 					$sys->sys_api_first_activities_tickets( $tickets_id, $first_module, $first_id, $company_id, $first_date);
			// 			}
			// 		}
			// 	}
			// }

		}
		return true;
	}

	public function SaveCallsPbxRecording($request, $calls_serial_id, $company_id)
	{
		$insert['calls_pbx_recording_date'] 	= date('Y-m-d');
		$insert['calls_serial_id'] 						= $calls_serial_id;
		$insert['src'] 												= $request['src'];
		$insert['dst'] 												= $request['dst'];
		$insert['company_id'] 								= $company_id;
		$insert['channel'] 										= $request['channel'];
		$insert['uniqueid']		 								= $request['uniqueid'];

		$this->model_pbx_recording_class::create($insert);

		return true;
	}

	public function UpdatePbxIncoming($users_id, $company_id, $request)
	{
		$sys = new sys();
		$update_data['status'] = 1;

		$pbx_extention = $this->GetUsersInformation($users_id);

		$update = $this->model_pbx_incoming_class::where('company_id', '=', $company_id)
																						->where('phone_number', '=', $request['src'])
																						->where('ext', '=', $request['dst'])
																						->update($update_data);

		return true;
	}

	public function PBXUpdate($request, $company_id, $users_id)
	{
		$sys = new sys();
		if ( isset($request['org_serial_id'])) {
      if ($request['org_serial_id'] != '') {
          $request['module'] = 'org';
          $request['serial_id'] = $request['org_serial_id'];
      }
    }
    unset($request['org_serial_id']);
		unset($request['sharing']);
		unset($request['file']);

		# Created By Pratama Gilang
    # 29-11-2019
    # For Slice Custom fields And Core Fields
		$input_custom = array();
		foreach ($request as $key => $value) 
		{
			$prefix_custom  = substr($key, 0, 7);
			if ($prefix_custom == "custom_") 
			{
				$fields_name = substr_replace($key, '', 0, 7); ;
				$input_custom[$fields_name] = $request[$key];
				unset($request[$key]);
			}
		}

		// NOTE : variable optonal_condition for updateajax, for skip update sys_rel

		$uuid4 = $this->uuid::uuid4();
		$data_serial_id = $this->GetSerialIdByUuid($request[$this->table_module.'_uuid'], $company_id); // Get data_serial_id. result => 1/2/3/4/5,etc

		// Load function in this helper
		if (isset($request[$this->table_module.'_uuid'])) 
		{
				$get_rel_serial_id = $this->GetDataByPbx($request[$this->table_module.'_uuid']); //get data by data_uuid
		}

		# UPDATE CUSTOM FIELDS
		// Checking for value array, convert value to json
		foreach ($input_custom as $key => $value) 
		{				
			$custom_id = $this->get_custom_fields_by_id($key, $company_id);

			if (!isEmpty($custom_id)) 
			{
				$request[$this->table_module.'_custom_values_text'][$custom_id[$this->table_module.'_custom_fields_serial_id']] = $value;
			}

			// Query for get values_maps by custom_fields_serial_id
			$key = $sys->library_values_maps_by_name($key, $this->table_module, $company_id);

			if ( is_array($value) )
			{
				$value_json 	= json_encode($value, TRUE);
				$update[$key] 	= $value_json;
			}
			elseif ( is_object($value) ) 
			{
				# if input type custom is file
				$file = $this->AWS_SaveDataFile($value, $company_id);
				$update[$key] = $file;
			}	
			else
			{
				if(strpos($value,'|') ==  TRUE)
				{
					#For split teks Before result exp. 93234-23453245-5fdsg|Barantum
					$value_text 		= substr($value, strpos($value,'|')+1); //get value teks exp. org : Barantum
					$value_related_uuid = substr($value, 0, strpos($value,'|')); // get value uuid. org: 324-235df-kffgfd
					#END
					$update[$key] 		= $value_text;
				}
				else
				{
					$update[$key] 			= $value;
				}
			}
		}
	  # END 

	  if (isset($update) AND countCustom($update) > 0) 
		{
			// Update custom values
			$query = $this->model_custom_values__class::where('company_id', '=', $company_id)
										->where($this->table_module.'_serial_id', '=', $data_serial_id)
										->update($update);
		}

		# UPDATE CORE FIELDS
		// Get all data input Post
		$input = $request;
		$date_now   	= date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s').' +7 Hours')); 
		if (isset($request['calls_date_start_click'])) 
		{
			$input[$this->table_module.'_date_start'] 	= $request['calls_date_start_click'];
		}
		else
		{
			$input[$this->table_module.'_date_start'] 	= $request['calls_date_start'];			
		}
		$input[$this->table_module.'_date_end'] 	= $date_now;

	  // Unset data custom field
		unset($input["_token"]);
		unset($input['last_view']);
		unset($input[$this->table_module."_custom_values_text"]);
		unset($input[$this->table_module."_parent_type"]);
		unset($input[$this->table_module."_parent_id"]);
		unset($input['create_another']);
		unset($input['calls_date_start_click']);
		unset($input['calls_date_end_click']);
		unset($input['module']);
		unset($input['serial_id']);
		unset($input['src']); // for zoiper
		unset($input['dst']); // for zoiper

		unset($input['channel']); 
		unset($input['uniqueid']); 
		unset($input['calldate']); 

		$input['date_modified']								= date('Y-m-d H:i:s');
		$input[$this->table_module.'_status'] = 'Held';
		$input['modified_by']									= $users_id;

		$datetime1 		= date_create($date_now);
		$datetime2 		= date_create($input[$this->table_module.'_date_start']);
		$datediff 		= date_diff($datetime1, $datetime2);
		$input['calls_duration_hours'] 		= sprintf("%02d", $datediff->format("%h"));
		$input['calls_duration_minutes'] 		= sprintf("%02d", $datediff->format("%i"));

		// this is for strtotime input type = datetime
		if ( isset($input[$this->table_module.'_date_start']) && $input[$this->table_module.'_date_start'] != "" )
		{
			$input[$this->table_module.'_date_start'] = date('Y-m-d H:i:s', strtotime($input[$this->table_module.'_date_start']. "-7 hours"));
		}
		if ( isset($input[$this->table_module.'_date_end']) && $input[$this->table_module.'_date_end'] != "" )
		{
			$input[$this->table_module.'_date_end'] = date('Y-m-d H:i:s', strtotime($input[$this->table_module.'_date_end']. "-7 hours"));
		}
		// Insert into database core data
	  $update = $this->model_class::where($this->table_module.'_serial_id', '=', $data_serial_id)
	  									->update($input);
	  # END
		$input[$this->table_module.'_date_start'] = date('Y-m-d H:i:s', strtotime($input[$this->table_module.'_date_start']. "+7 hours"));
		$input[$this->table_module.'_date_end'] 	= $date_now;
		$input['date_modified']										= date('Y-m-d H:i:s', strtotime($input['date_modified']. "+7 hours"));
		
		return $input;
	}

	public function GetDataByPbx($data_uuid='')
	{
		// define variable
		$result = array();

		// Get data find by data_uuid
		$calls_fields = "calls.*, DATE_FORMAT(calls.calls_date_start,'%d-%m-%Y %H:%i') as calls_date_start, DATE_FORMAT(calls.calls_date_end, '%d-%m-%Y %H:%i') as calls_date_end";
		$result = $this->model_class::select(DB::raw($calls_fields))
													->where( $this->table_module.'_uuid', '=', $data_uuid)
													->first();

		if ( countCustom($result) > 0 ) {
			if ( $this->json_mode === TRUE) {
				$result = $result->toJson(); //put listing result in json
			}
			else {
				$result = $result->toArray(); // put listing result in array
			}
		}

		return $result;
	}

	# Created By Prihan Firmanullah
  # 12-08-2020
  # For get Roles Edit And Delete
	public function getRolesSingle($data=array(), $company_id=0, $users_id=0)
	{
		$sys = new sys();
		if (!isEmpty($data))
		{
			$data_roles = $sys->get_roles($this->table_module, $company_id, $users_id); // Get data roles edit
			// Define variable
			$roles_edit 	= 'true'; // Default roles edit
			$roles_delete = 'true'; // Default roles delete
			$roles_view = 'true'; // Default roles view
			$owner_id 		= $data[$this->table_module.'_owner_id'];
			
			if (!isEmpty($data_roles['contents_edit']))
			{
				$roles_exists = array_search($owner_id, $data_roles['contents_edit']);
				if ( $roles_exists === false )
				{
					$roles_edit = 'false'; // Disable roles edit
				}
			}

			if (!isEmpty($data_roles['contents_delete']))
			{
				$roles_exists = array_search($owner_id, $data_roles['contents_delete']);
				if ( $roles_exists === false )
				{
					$roles_delete = 'false'; // Disable roles edit
				}
			}

			if (!isEmpty($data_roles['contents']))
			{
				$roles_exists = array_search($owner_id, $data_roles['contents']);
				if ( $roles_exists === false )
				{
					$roles_view = 'false'; // Disable roles edit
				}
			}
			// Final result : Edit disable or enable
			$data['editable'] = $roles_edit;
			$data['deleteable'] = $roles_delete;
			$data['viewable'] = $roles_view;
		}

		return $data;
	}

	public function AWS_SaveDataFile($file, $company_id='')
	{
		$data_file = date("YmdHis_").$file->getClientOriginalName();

		$upload =  $file;

		$mime_type = $file->getClientMimeType();

		$content_types 	 = [
	    'image/png', // png
	    'image/jpeg', //jpeg
	    'image/jpg', //jpg
	    'image/gif', // gif
		];

		$access = 'public';

		$storage 	= Storage::disk('s3')->put('company_'.$company_id.'/file/'.$this->table_module.'/'.$data_file, file_get_contents($upload), $access);
		$imageName = Storage::disk('s3')->url($data_file);

		if($storage === true)
		{
			// For upload thumbnail to aws s3
			if($mime_type == $content_types[0] OR $mime_type == $content_types[1] OR $mime_type == $content_types[2] OR $mime_type == $content_types[3])
			{
				// save thumbs lg
				$image_thumb = Image::make($upload)->resize(300, null, function ($constraint) 
				{
					$constraint->aspectRatio();
				});

				$image_thumb = $image_thumb->stream();
				Storage::disk('s3')->put('company_'.$company_id.'/file/'.$this->table_module.'/thumbs_lg/'.$data_file, $image_thumb->__toString(),$access);

				// save thumbs sm
				$image_thumb = Image::make($upload)->resize(50,50);
				$image_thumb = $image_thumb->stream();
				Storage::disk('s3')->put('company_'.$company_id.'/file/'.$this->table_module.'/thumbs_sm/'.$data_file, $image_thumb->__toString(),$access);
			}
		}
		
		return $data_file;
	}

	public function AWS_GetFileCustomFields_sm($filename='', $company_id='')
	{
		$url = '';
		if ( !isEmpty($filename) ) 
		{
			$objectKey = $filename;

			$s3 = Storage::disk('s3');

				$client = $s3->getClient();

			 $expiry = "+1440 minute";

			$command = $client->getCommand('GetObject', [
			    'Bucket' => Config('filesystems.disks.s3.bucket'),
			    'Key'    => 'company_'.$company_id.'/file/'.$this->table_module.'/'.$objectKey
			]);

				$request = $client->createPresignedRequest($command, $expiry);
			
			$url = (string) $request->getUri();
		}

		return $url;
	}

	public function AWS_GetFileCustomFields($filename='', $company_id='')
	{
		$url = '';
		if ( !isEmpty($filename) ) 
		{
			$objectKey = $filename;

			$s3 = Storage::disk('s3');

			$client = $s3->getClient();

			$expiry = "+1440 minute";

			$command = $client->getCommand('GetObject', [
			    'Bucket' => Config('filesystems.disks.s3.bucket'),
			    'Key'    => 'company_'.$company_id.'/file/'.$this->table_module.'/'.$objectKey
			]);
		 
			$request = $client->createPresignedRequest($command, $expiry);
			
			$url = (string) $request->getUri();
		}

		return $url;
	}

	public function getIncomingCalls($company_id, $users_id)
	{
		$result = array();
		$timeStart = date('Y-m-d H:i:s', strtotime(date('Y-m-d')." 00:00:00"));
		$timeEnd = date('Y-m-d H:i:s', strtotime(date('Y-m-d')." 23:59:59"));
		
		$select = "notifications_to_users, notifications_is_read, notifications_admin_is_read, notifications.company_id, calls_serial_id, calls_uuid, calls_name, DATE_FORMAT(calls_date_start,'%Y-%m-%d %H:%i:%s') + INTERVAL 7 HOUR as calls_date_start, calls_owner";
		
		// $query = $this->model_class::select(DB::raw($select))
		// 													->whereBetween('calls_date_start', [$timeStart, $timeEnd])
		// 													->where('company_id', '=', $company_id)
		// 													->where('calls_owner', '=', $users_id)
		// 													->where('deleted',  Config('setting.NOT_DELETED'))
		// 													->get();

		$data_user = $this->model_users_class::select('id','users_type')
									->where('id','=',$users_id)
									->first();

		$query = $this->model_notifications_class::select(DB::raw($select))
								->leftjoin('calls','calls.calls_serial_id','=','notifications.notifications_rel_to_id')
								->where('notifications.notifications_rel_to_module','=','calls')
								->where('notifications.company_id', '=', $company_id)
								->where('notifications.notifications_to_users', '=', $users_id)
								->whereBetween('calls.calls_date_start', [$timeStart, $timeEnd])
								->where('calls.deleted',  Config('setting.NOT_DELETED'));

		if($data_user['users_type'] == 'admin')
		{
			$query = $query->where('notifications.notifications_admin_is_read', '=', Config('setting.notifications_admin_is_unread'));
		}
		elseif($data_user['users_type'] == 'employee')
		{
			$query = $query->where('notifications.notifications_is_read', '=', Config('setting.notifications_is_unread'));
		}

		$query = $query->get();


		if(countCustom($query) > 0)
		{
				$result = json_decode(json_encode($query), true);
		}

		return $result;
	}

	//Add selected data to users_pinned database
	public function pinAddData($data_uuid, $users_pinned_name, $users_pinned_module, $user_id, $company_id)
	{
		//Set input variable
		$input['users_pinned_uuid'] 	= $data_uuid;
		$input['users_pinned_name']		= $users_pinned_name;
		$input['users_pinned_module']	= $users_pinned_module;
		$input['users_id']				= $user_id;
		$input['company_id']			= $company_id;
		$input['deleted']				= (int) Config('setting.NOT_DELETED');
		
		//Check existing data
		$data = $this->model_users_pinned::where('users_pinned_uuid', '=', $data_uuid)
									     ->count();

		//Perform create or update
		if($data > 0)
		{
			$input['date_modified']			= date('Y-m-d H:i:s');	
			$update_input = $this->model_users_pinned::
			where('users_pinned_uuid', '=', $data_uuid)->update($input);
		}
		else
		{	
			$input['date_created']			= date('Y-m-d H:i:s');
			$input['date_modified']			= date('Y-m-d H:i:s');			     
			$create_input = $this->model_users_pinned::create($input);
		}

		return true;
	}

	//Check pinned data for validation of double data
	public function pinValidate($data_uuid)
	{
		$data = $this->model_users_pinned::where('users_pinned_uuid', '=', $data_uuid)
									     ->where('deleted', '=', Config('setting.NOT_DELETED'))
									     ->count();
		return $data;
	}

	//Remove pinned data using soft delete
	public function pinDelete($data_uuid)
	{
		$delete['deleted'] = (int) Config('setting.DELETED');
		$delete['date_modified'] = date('Y-m-d H:i:s');

		$data = $this->model_users_pinned::where('users_pinned_uuid', '=', $data_uuid)
								  ->update($delete);
		return $data;
	}

	//Get data from database with status pinned or unpinend
	public function pinGetData($data=array())
	{
		foreach ($data as $key => $value) {

			$pinned = $this->model_users_pinned::select('users_pinned_uuid')
											  ->where('users_pinned_uuid', '=', $value[$this->table_module.'_uuid'])
											  ->where('deleted', '=', Config('setting.NOT_DELETED'))
											  ->first();

		  	if (countCustom($pinned)>0) 
				{
					$data[$key] 		= array_add($data[$key], 'users_pinned', 1);
				}
			else
				{
					$data[$key] 		= array_add($data[$key], 'users_pinned', 0);
				}

		}
		
		return $data;
	}

	public function updateStatusCampaighRead($input, $company_id)
	{
		$serial_id = $input['notif_serial_id'];
		$update[$this->table_module.'_campaign_tagged_status'] = 0;
		$notify = DB::table('calls_campaign_tagged')->where('calls_campaign_tagged_serial_id', '=', $serial_id)->update($update);

		return true;
	}

	public function getParentUUID($data=array(), $company_id=0)
	{
		if (countCustom($data['data']) > 0) 
		{
			foreach ($data['data'] as $key => $value) // looping $data
			{
				$table_module 		= $value[$this->table_module.'_parent_type']; // get name table 
				$table_module_id 	= $value[$this->table_module.'_parent_id']; // get serial_id

				if ($table_module != "" && $table_module_id != "") // if table not empty AND serial_id not empty
				{
					$query 				= DB::table($table_module)->select($table_module.'_uuid')
														->where($table_module.'_serial_id', '=', $table_module_id)
														->where('company_id', '=', $company_id)
														->where('deleted', '=', Config('setting.NOT_DELETED'))
														->first(); // process get data in table
					if (countCustom($query) > 0) 
					{
						$query 			= json_decode(json_encode($query), true); // convert object to array
						$data['data'][$key] = array_add($data['data'][$key], $this->table_module.'_parent_uuid', $query[$table_module.'_uuid']);

					}else
					{
						$data['data'][$key]	= array_add($data['data'][$key], $this->table_module.'_parent_uuid', '');
					}
				}else
				{
					$data['data'][$key]	= array_add($data['data'][$key], $this->table_module.'_parent_uuid', '');
				}
			}
		}
		
		return $data;
	}

	# ADD BY GILANG PRATAMA
	# SATURDAY, 07 SEPTEMBER 2019
	# TO GET RECORDING CLICK TO CALL
	public function getRecord($request=array(), $user_id=0)
	{
		# define variable
		$get_unique_id = array();
		$uniqueid = '';
		$data = array();
		
		$serial_id = !empty($request['serial_id']) ? $request['serial_id'] : '';
		$GetUsersInformation = $this->GetUsersInformation($user_id);

		if (!isEmpty($serial_id) OR $serial_id != '') 
		{
			$get_unique_id = $this->model_pbx_recording_class::select(DB::raw('uniqueid'))
														->where($this->table_module.'_serial_id', '=', $serial_id)
														->first();

			if (countCustom($get_unique_id) > 0) 
			{
				$get_unique_id = $get_unique_id->toArray();
				$uniqueid = urlencode($get_unique_id['uniqueid']);
			}

			$link_url = "https://".$GetUsersInformation['url_recording']."/tool/get_call.php?uniqueid=".$uniqueid;

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

					  $recordingfile 	= $explode[$count];

					  $data = [
					  	'recordingfile' => $recordingfile,
					  	'billsec' 			=> $json_decode["billsec"]
					  ];

					  $date_start_format = '';
					  $date_end_format = '';

					  if (isset($json_decode['calldate'])) 
					  {
					  	$start_date = $json_decode['calldate'];
					  	$billsec = $json_decode['billsec'];
					  	$end_date = date('Y-m-d H:i:s', strtotime($start_date. "+".$billsec." seconds"));

					  	$dateTime1 = date_create($end_date);
					  	$dateTime2 = date_create($start_date);

					  	$datediff 		= date_diff($dateTime1, $dateTime2);
					  	$data[$this->table_module.'_duration_hours'] 		= sprintf("%02d", $datediff->format("%h"));
							$data[$this->table_module.'_duration_minutes'] 	= sprintf("%02d", $datediff->format("%i"));
							$data[$this->table_module.'_date_start'] 				= date('Y-m-d H:i:s', strtotime($start_date. "-7 hours"));
							$data[$this->table_module.'_date_end'] 					= date('Y-m-d H:i:s', strtotime($end_date. "-7 hours"));
							
							$date_start_format = date('d F Y H:i', strtotime($start_date));
							$date_end_format   = date('d F Y H:i', strtotime($end_date));
					  }

					  $update = $this->model_class::where($this->table_module.'_serial_id', '=', $serial_id)->update($data);

					  # Only use for format date
						$data[$this->table_module.'_date_start_format'] = $date_start_format;
						$data[$this->table_module.'_date_end_format'] = $date_end_format;

					}
			  }
	    }
		}

		return $data;
	}

	public function check_recording($serial_id=0, $company_id=0)
	{
		// Define variable
		$result = 0; # default no get record

		$check = $this->model_pbx_recording_class::where($this->table_module.'_serial_id', '=', $serial_id)
																						->where('company_id', '=', $company_id)
																						->first();

		if ( countCustom($check) > 0 ) 
		{
			$check = json_decode(json_encode($check), true);
			$result = 1; # get record
			if ($check['uniqueid'] == '' || $check['uniqueid'] == null || $check['uniqueid'] == 'undefined') 
			{
				$result = 0; # no get record
			}
		}

		return $result;
	}

	# ADD BY GILANG PRATAMA
	# SATURDAY, 09 SEPTEMBER 2019
	# TO GET RECORDING CLICK TO CALL
	public function getListRecord($data=array(), $company_id=0)
	{
		if (countCustom($data) > 0) 
		{
			foreach ($data as $key => $value) 
			{
				$get_record = $this->model_class::select(DB::raw('recordingfile, pbx.uniqueid'))
													->leftjoin('calls_pbx_recording as pbx', 'pbx.calls_serial_id', '=', $this->table_module.'.calls_serial_id')
													 ->where($this->table_module.'.'.$this->table_module.'_serial_id', '=', $value[$this->table_module.'_serial_id'])
													 ->where($this->table_module.'.company_id', '=', $company_id)
													 ->first();

				$data[$key]['recording'] = array(
					'recordingfile' =>  '',
          'uniqueid' => '',
          'status' => 0
				);
				if (countCustom($get_record) > 0) 
				{
					$get_record = json_decode(json_encode($get_record), true);
					$get_record['status'] = 1;
					if ($get_record['uniqueid'] == '' || $get_record['uniqueid'] == null || $get_record['uniqueid'] == 'undefined' ) 
					{
						$get_record['status'] = 0;
					}

					$data[$key]['recording'] = $get_record;
					$data[$key]['recording']['recordingfile'] = urlencode($get_record['recordingfile']);
				}
			}
		}

		return $data;
	}

	public function getOrgIfparentTypeContacts($data=array())
	{
		$dummy = $data['data'];
		$dat = array();

		if(countCustom($dummy) > 0)
		{
			foreach ($dummy as $key => $value) 
			{
				if ($value[$this->table_module.'_parent_type'] == 'contacts') 
				{
					$query = $this->model_contacts_class::select('org.org_name','org.org_uuid')
								->leftjoin('sys_rel', function($join)
								{
									$join->on('sys_rel.rel_from_id', '=', 'contacts.contacts_serial_id')
									->where('sys_rel.rel_from_module', '=', 'contacts')
									->where('sys_rel.rel_to_module', '=', 'org');
								});
					$query = $query->leftjoin('org', 'org.org_serial_id', '=', 'sys_rel.rel_to_id'); // This id for get
					$query = $query->where('contacts.contacts_serial_id', '=', $value[$this->table_module.'_parent_id'])->first(); 

					if (countCustom($query) > 0) 
					{
						$org = $query->toArray();
						$data['data'][$key]['org_name'] = $org['org_name'];
						$data['data'][$key]['org_uuid'] = $org['org_uuid'];
					}
				}
			}
		}

		return $data;
	}

	public function GetDataById($serial_id)
	{
		// define variable
		$result = array();

		// Get data find by data_uuid
		$result = $this->model_class::where( $this->table_module.'_serial_id', '=', $serial_id)->first();

		if ( countCustom($result) > 0 )
		{
			if ( $this->json_mode === TRUE) {
				$result = $result->toJson(); //put listing data in json
			}
			else {
				$result = $result->toArray(); // put listing data in array
			}
		}

		return $result;
	}

	public function convertOldFilterToV5(array $data, int $company_id, int $users_id){
		foreach ($data as $key => $value){
			if(isset($value['view_criteria_operator']) && $value['view_criteria_operator'] == 'is' && ($value[$this->table_module.'_fields_input_type'] == 'date' ||  $value[$this->table_module.'_fields_input_type'] == 'datetime')){
				$data[$key]['view_criteria_operator'] = 'select_date';
				$data[$key]['content'] = explode(' - ', $data[$key]['content']);
			}
		}
		return $data;
	}

	public function convertFilterUpdate(array $data, int $company_id, int $users_id){
		foreach ($data['filters_fields'] as $key => $value) {
			$old_data = [];
			if($value['operator'] == 'select_date'){
				$old_data = $this->model_view_criteria_class::
				where($this->table_module.'_view_serial_id', '=', $data[$this->table_module.'_view_serial_id'])->
				where($this->table_module.'_fields_serial_id','=',$value['fields_serial_id'])->first();
				if($old_data != null){
					$old_data = $old_data->toArray();
					foreach ($old_data as $old_key => $old_value) {
						if($old_data[$this->table_module . '_view_criteria_operator'] === 'is'){
							$data['filters_fields'][$key]['operator'] = "is";
							$data['filters_fields'][$key]['fields_value'] = implode(' - ', $value['fields_value']);
						}
					}
				}
			}
		}
		return $data;
	}
	// Convert date
	public function convertDate($valueDate)
	{
		$excel_date = $valueDate; //here is that value 41621 or 41631
		$unix_date = ($excel_date - 25569) * 86400;
		$excel_date = 25569 + ($unix_date / 86400);
		$unix_date = ($excel_date - 25569) * 86400;
		$convert_date = gmdate("Y-m-d", $unix_date);

		return $convert_date;
	}

	public function date_between_custom($data_search_date=array())
	{
		# defined variable
		$date 		= "";
		$result 	= "";

		$curdate 			= date("Y-m-d"); // default currant date is gmt+0
		$curdatetime 	= date("Y-m-d H:i:s");
		$curmonth 		= date("m");
		$curyears 		= date("Y");
		// $curdate 			= date("Y-m-d", strtotime("+7 hours", strtotime($curdatetime))); // +7 in current date
		// DATE_ADD(leads.date_created, INTERVAL 7 HOUR ) BETWEEN '2017-06-04 00:00:00' AND '2017-06-04 23:59:59' AND 
		// DATE_ADD(leads.date_modified, INTERVAL 7 HOUR ) BETWEEN '2017-06-04 00:00:00' AND '2017-06-04 23:59:59'

		if (countCustom($data_search_date) > 0) 
		{
			// today, last_7_days, next_7_days, last_30_days, next_30_days, 
			// this_month, last_month, next_month, 
			// this_year, last_year, next_year, 
			// select_date, before, after, yesterday
			
			foreach ($data_search_date as $key => $value) 
			{
				$field_name 	= $value[0];
				$operator 		= $value[1];
				$keyword 			= $value[2];

				if ($operator == "today")
				{
					$date_first 		= $curdate;
					$date_second 		= $curdate;
					
					$date 					= "STR_TO_DATE(".$field_name.", '%d-%m-%Y %r') BETWEEN '".$date_first."' AND '".$date_second."'";
				}
				elseif ($operator == "last_5_days")
				{
					$date_first 		= date("Y-m-d", strtotime("-5 days", strtotime($curdate)));
			 		$date_second 		= date("Y-m-d", strtotime("-1 days", strtotime($curdate)));
					$date 					= "STR_TO_DATE(".$field_name.", '%d-%m-%Y %r') BETWEEN '".$date_first."' AND '".$date_second."'"; 
				}
				elseif ($operator == "last_7_days")
				{
					$date_first 		= date("Y-m-d", strtotime("-7 days", strtotime($curdate)));
			 		$date_second 		= date("Y-m-d", strtotime("-1 days", strtotime($curdate)));
					$date 					= "STR_TO_DATE(".$field_name.", '%d-%m-%Y %r') BETWEEN '".$date_first."' AND '".$date_second."'"; 
				}
				elseif ($operator == "next_7_days")
				{
					$date_first 		= date("Y-m-d", strtotime("+1 days", strtotime($curdate)));
					$date_second 		= date("Y-m-d", strtotime("+7 days", strtotime($curdate)));
					$date 					= "STR_TO_DATE(".$field_name.", '%d-%m-%Y %r') BETWEEN '".$date_first."' AND '".$date_second."'"; 
				}
				elseif ($operator == "last_30_days")
				{
					$date_first 		= date("Y-m-d", strtotime("-30 days", strtotime($curdate)));
			 		$date_second 		= date("Y-m-d", strtotime("-1 days", strtotime($curdate)));

					$date 					= "STR_TO_DATE(".$field_name.", '%d-%m-%Y %r') BETWEEN '".$date_first."' AND '".$date_second."'"; 
				}
				elseif ($operator == "next_30_days")
				{
					$date_first 		= date("Y-m-d", strtotime("+1 days", strtotime($curdate)));
			 		$date_second 		= date("Y-m-d", strtotime("+30 days", strtotime($curdate)));

					$date 					= "STR_TO_DATE(".$field_name.", '%d-%m-%Y %r') BETWEEN '".$date_first."' AND '".$date_second."'"; 
				}
				elseif ($operator == "this_month")
				{
					$date_first 		= date("Y-m-1", strtotime($curdate));
			 		$date_second 		= date("Y-m-t", strtotime($curdate));

					$date 					= "STR_TO_DATE(".$field_name.", '%d-%m-%Y %r') BETWEEN '".$date_first."' AND '".$date_second."'"; 
				}
				elseif ($operator == "last_month")
				{
					$date_first 		= date("Y-m-1", strtotime('-1 month', strtotime($curdate)));
					$date_second 			= date("Y-m-t", strtotime('-1 month', strtotime($curdate)));

					$date 					= "STR_TO_DATE(".$field_name.", '%d-%m-%Y %r') BETWEEN '".$date_first."' AND '".$date_second."'";
				}
				elseif ($operator == "next_month")
				{
					$date_first 		= date("Y-m-1", strtotime('+1 month', strtotime($curdate)));
					$date_second 			= date("Y-m-t", strtotime('+1 month', strtotime($curdate)));

					$date 					= "STR_TO_DATE(".$field_name.", '%d-%m-%Y %r') BETWEEN '".$date_first."' AND '".$date_second."'";
					$date = "SUBSTR(".$field_name.",4,7) = '".date("m-Y", strtotime('-1 month'))."'";  
				}
				elseif ($operator == "this_year")
				{
					$date_first 		= date("Y-1-1");
					$date_second 		= date("Y-12-31");

					$date 					= "STR_TO_DATE(".$field_name.", '%d-%m-%Y %r') BETWEEN '".$date_first."' AND '".$date_second."'";
					 
				}
				elseif ($operator == "last_year")
				{
					$date_first 			= date("Y-1-1", strtotime('-1 year', strtotime($curdate)));
					$date_second 			= date("Y-12-31", strtotime('-1 year', strtotime($curdate)));

					$date 					= "STR_TO_DATE(".$field_name.", '%d-%m-%Y %r') BETWEEN '".$date_first."' AND '".$date_second."'"; 
				}
				elseif ($operator == "next_year")
				{
					$date_first 			= date("Y-1-1", strtotime('+1 year', strtotime($curdate)));
					$date_second 			= date("Y-12-31", strtotime('+1 year', strtotime($curdate)));
					
					$date 					= "STR_TO_DATE(".$field_name.", '%d-%m-%Y %r') BETWEEN '".$date_first."' AND '".$date_second."'"; 
				}
				elseif ($operator == "select_date")
				{
					$exp_keyword 		= explode("and", $keyword);	
					$date_first 		= date("Y-m-d", strtotime($exp_keyword[0]));
			 		$date_second 		= date("Y-m-d", strtotime($exp_keyword[1]));

					$date 					= "STR_TO_DATE(".$field_name.", '%d-%m-%Y %r') BETWEEN '".$date_first."' AND '".$date_second."'"; 
				}
				elseif ($operator == "before")
				{	
					$before_date		= date("Y-m-d", strtotime($keyword));
					if($field_name == $this->table_module.'.'.$this->table_module.'_last_date')
					{
						$date 					= "STR_TO_DATE(".$field_name.", ''%d-%m-%Y %r'') <= '".$before_date."' OR ".$field_name." IS NULL "; 
					}
					else
					{
						$date 					= "STR_TO_DATE(".$field_name.", ''%d-%m-%Y %r'') <= '".$before_date."'";
					}

				}
				elseif ($operator == "after")
				{
					$after_date 		= date("Y-m-d", strtotime($keyword));
					$date 					= "STR_TO_DATE(".$field_name.", ''%d-%m-%Y %r'') >= '".$before_date."'";
				}
				elseif ($operator == "yesterday")
				{
					$date = $field_name." = ". date("d-m-Y", strtotime('-1 days')); 
				}

				$and = "";
				if ($key < countCustom($data_search_date)-1) {
					$and = " and ";
				}
				$result .= $date. $and;
			}
		}
		
		return $result;
	}

	public function getAllFilteredId($input, $fields, $criteria)
	{
		$listFieldsCustom 		= $fields['listFieldsCustom']; // Get Fields Custom
		$query = $this->model_class::select($this->table_module . '.' . $this->table_module . '_uuid AS uuid', $this->table_module . '.' . $this->table_module . '_owner AS owner_id');

		# DATA ROLES
		$roles 	= $this->get_roles($this->table_module, $criteria['company_id'], $criteria['users_id']);
		$myRecordOnlyCheck 	= $this->myRecordOnlyCheck($criteria['company_id'], $criteria['users_id']); //if filter view " (You) " Checked

		if (countCustom($roles['contents']) > 0 && $myRecordOnlyCheck === FALSE) 
		{
			//$roles['contents'] is container member inside roles by users_id, example : team view then $roles['contents'] = array('11', '12', '13') 
			//if member != empty , then running this block
			//more detail about $roles, please check get_roles()
			$query->whereIn($this->table_module.'.'.$this->table_module.'_owner', $roles['contents']);
		}

		//Running filter when $data_filter TRUE
		$filterView 	= $this->viewChecked($criteria['company_id'], $criteria['users_id']); // checked filter view active
		$filterViewName = $filterView[$this->table_module.'_view_name'];
		
		$filterCriteria = $this->generate_view($filterView, $criteria['company_id'], $criteria['users_id']); // get the selected filter 	

		$filterCriteria = $this->data_search($filterCriteria, $criteria['company_id'], [], $listFieldsCustom, 'b'); // generate format for use filter feature
		
		if(isset($filterCriteria['temp']))
		{
			if (countCustom($listFieldsCustom) == 0) // if fields custom not available
			{
				$query->leftjoin($this->table_module.'_custom_values_ as mcv', function($join) use ($criteria)
							{
								$join->on('mcv.'.$this->table_module.'_serial_id', '=', $this->table_module.'.'.$this->table_module.'_serial_id')
								->where('mcv.company_id', '=', $criteria['company_id']);
							});
			}
		}

		$filterCriteriaCore = $filterCriteria['result']['core']; // filter by core field type
		$filterCriteriaDate = $filterCriteria['result']['date']; // filter by date type
		$filterCriteriaCustom = $filterCriteria['result']['custom']; // filter by custom field type

		if ($myRecordOnlyCheck === TRUE)
		{
			// if user choosen filter "You", then owner by sesson users_id login
			$query->where($this->table_module.'_owner', '=', $criteria['users_id']);
		}
		elseif (countCustom($filterView) > 0 && $filterViewName != 'Everyone' && $filterViewName != 'You') 
		{
			// get users statuc active or deactive
			$get_users = $this->model_users_class::select('users_status')
							->leftjoin('users_company as comp','comp.users_id','=','users.id')
							->where('id','=',$filterView['users_id'])
							->where('company_id','=',$filterView['company_id'])
							->first();

			// for check if users deactive
			if($get_users['users_status'] == 'deactive')
			{
				// get current users active
				$get_active = $this->model_view_class::select($this->table_module.'_view_serial_id', $this->table_module.'_view_name')
								->where($this->table_module.'_view_name','=','Everyone')
								->where('users_id','=', $criteria['users_id'])
								->where('company_id','=', $criteria['company_id'])
								->first();

				// update contact_view_serial_id into default
				$update = $this->model_view_checked_class::where('users_id','=',$criteria['users_id'])
							->where('company_id','=',$criteria['company_id'])
							->update([$this->table_module.'_view_serial_id' => $get_active[$this->table_module.'_view_serial_id']]);

				// change filter into default/Everyone when load data
				$filterView[$this->table_module.'_view_serial_id'] = $get_active[$this->table_module.'_view_serial_id'];
				$filterView[$this->table_module.'_view_name'] = 'Everyone';

				// change criteria into empty
				$filterCriteriaCore = array();
				$filterCriteriaDate = array();
				$filterCriteriaCustom = array();
			}
			  
			$query_count = 1; // count query with left join
			//if user choosen filter, except filter 'Everyone' and 'You' 
			$checkFilterByOwner = in_array($this->table_module.'.'.$this->table_module.'_owner', array_column($filterCriteriaCore, 0)); 

			if ($checkFilterByOwner == TRUE) // if filter data by owner
			{
				$key_filter_owner = array_search($this->table_module.'.'.$this->table_module.'_owner', array_column($filterCriteriaCore, 0)); // get key, position owner in array		      
				if (is_array($filterCriteriaCore[$key_filter_owner][2]) && countCustom($filterCriteriaCore[$key_filter_owner][2]) > 0)
				{
					// if filter data by multi owner
					if ($filterCriteriaCore[$key_filter_owner][1] == "=") // when owner by IS 
					{
						$query->whereIn($this->table_module.'.'.$this->table_module.'_owner', $filterCriteriaCore[$key_filter_owner][2]);

					}
					elseif ($filterCriteriaCore[$key_filter_owner][1] == "!=") // when owner by isnt
					{
						$query->whereNotIn($this->table_module.'.'.$this->table_module.'_owner', $filterCriteriaCore[$key_filter_owner][2]);
					}
				} else {
					// if filter data by single owner
					$query->where('users.name', $filterCriteriaCore[$key_filter_owner][1], $filterCriteriaCore[$key_filter_owner][2]);
				}			
				unset($filterCriteriaCore[$key_filter_owner]); // remove owner in array, by position key
			}
			if (countCustom($filterCriteriaDate) > 0) // if filter data by date
			{
				$date_between 	= $this->date_between($filterCriteriaDate);
				$query->whereRaw($date_between);
			}
			if (countCustom($filterCriteriaCustom) > 0 ) // if filter data by custom fields
			{
				foreach ($filterCriteriaCustom as $value) 
				{
					if ($value[1] == "=" && is_array($value[2])) // operator is 
					{
						$query->whereIn($value[0], $value[2]);
					}
					elseif ($value[1] == "!=" && is_array($value[2])) // operator isn't
					{
						$query->whereNotIn($value[0], $value[2]);
					}
					elseif ( $value[1] === '==' ) // operator is_empty
					{
						$query->where(function ($query) use ($value) 
						{
							$query->where($value[0], '=', $value[2]);
							$query->orWhereNull($value[0]);
						});
					}
					elseif ( $value[1] === '!==' ) // operator is_not_empty
					{
						$query->where(function ($query) use ($value) 
						{
							$query->where($value[0], '!=', $value[2]);
							$query->whereNotNull($value[0]);
						});
					}
					else
					{
						$query->where($value[0], $value[1], $value[2]); // operator contains, start_with and end_with
					}
				}
			}
			$checkParentType 	= in_array($this->table_module.'.'.$this->table_module.'_parent_type', array_column($filterCriteriaCore, 0));
			if ($checkParentType == TRUE) // filter data by calls_parent_type
			{
				$keyParentType 	= array_search($this->table_module.'.'.$this->table_module.'_parent_type', array_column($filterCriteriaCore, 0));
				$parentTypeOpp 			= $filterCriteriaCore[$keyParentType][1]; // get operator
				$parentTypeKeyword 	= $filterCriteriaCore[$keyParentType][2]; // get keyword
				
				$query->where('sys_rel.rel_to_module', $parentTypeOpp, $parentTypeKeyword);
				unset($filterCriteriaCore[$keyParentType]);
			}
			$checkParentId 	= in_array($this->table_module.'.'.$this->table_module.'_parent_id', array_column($filterCriteriaCore, 0));
			if ($checkParentId == TRUE)  // filter data by calls_parent_id
			{
				$filterCriteriaCore = array_values($filterCriteriaCore); // reset key array, to 0 
				$keyParentId 		= array_search($this->table_module.'.'.$this->table_module.'_parent_id', array_column($filterCriteriaCore, 0));
				$parentIdOpp 		= $filterCriteriaCore[$keyParentId][1]; // get operator
				$parentIdKeyword 	= $filterCriteriaCore[$keyParentId][2]; // get keyword
				
				$query->having($this->table_module.'_parent_id', $parentIdOpp, $parentIdKeyword);
				unset($filterCriteriaCore[$keyParentId]);
			}
			// $query->where($filterCriteriaCore);
			if ( countCustom($filterCriteriaCore) > 0 ) 
			{
				foreach ($filterCriteriaCore as $key => $value) 
				{
					if ($value[1] == "=" && is_array($value[2])) // operator is 
					{
						$query->whereIn($value[0], $value[2]);
					}
					elseif ($value[1] == "!=" && is_array($value[2])) // operator isn't
					{
						$query->whereNotIn($value[0], $value[2]);
					}
					elseif ( $value[1] === '==' ) 
					{
						$query->where(function ($query) use ($value) 
						{
							$query->where($value[0], '=', $value[2]);
							$query->orWhereNull($value[0]);
						});
					}
					elseif ( $value[1] === '!==' ) 
					{
						$query->where(function ($query) use ($value) 
						{
							$query->where($value[0], '!=', $value[2]);
							$query->whereNotNull($value[0]);
						});
					}
					elseif ($value[1] == "=" && ($value[0] == $this->table_module.".date_created" || $value[0] == $this->table_module.".date_modified" || $value[0] == $this->table_module.".".$this->table_module."_date_start" || $value[0] == $this->table_module.".".$this->table_module."_date_end"))
					{
						$date_range = explode(" - ", $value[2]);
						if (countCustom($date_range) > 1)
						{
							$date_start = date('Y-m-d', strtotime($date_range[0])).' 00:00:00'; 
							$date_end 	= date('Y-m-d', strtotime($date_range[1])).' 23:59:59';

							$query->whereBetween($value[0], [$date_start, $date_end]);
						}
					}
					elseif ($value[1] == "!=" && ($value[0] == $this->table_module.".date_created" || $value[0] == $this->table_module.".date_modified" || $value[0] == $this->table_module.".".$this->table_module."_date_start" || $value[0] == $this->table_module.".".$this->table_module."_date_end"))
					{
						$date_range = explode(" - ", $value[2]);
						if (countCustom($date_range) > 1)
						{
							$date_start = date('Y-m-d', strtotime($date_range[0])).' 00:00:00'; 
							$date_end 	= date('Y-m-d', strtotime($date_range[1])).' 23:59:59';

							$query->whereNotBetween($value[0], [$date_start, $date_end]);
						}
					}
					else
					{
						$query->where($value[0], $value[1], $value[2]); // search data in core fields
					}
				}
			}
			  // end
		}

		$query_count = 1; // count query with left join
			
		//ADD BY AGUNG -> 15/03/2019 -> SEARCHING OWNER BY TEAMS
		// HANDLE SEACH DEALS OWNER BY TEAM
		if(isset($input[$this->table_module.'_owner_opp']) AND isset($input[$this->table_module.'_owner']))
		{
			if($input[$this->table_module.'_owner_opp'] == "is" OR $input[$this->table_module.'_owner_opp'] == "isnt")
			{
				if(is_array($input[$this->table_module.'_owner']))
				{
					if(countCustom($input[$this->table_module.'_owner']) > 0 OR !empty($input[$this->table_module.'_owner']))
					{
						$search_owner = array();
						foreach($input[$this->table_module.'_owner'] as $key_owner => $val_owner)
						{
							$owner_id = $val_owner;
							if (is_array($val_owner)) 
							{
								$owner_id = $val_owner[0];
							}
							$search_owner[$key_owner] = explode("!@#$%^&*()", $owner_id);
							if(isset($search_owner[$key_owner][1]))
							{
								$input[$this->table_module.'_owner'][$key_owner] = $search_owner[$key_owner][1];
							}
						}

					}
				}
				else
				{
					$search_owner = explode("!@#$%^&*()", $input[$this->table_module.'_owner']);
					if(isset($search_owner[1]))
					{
						$input[$this->table_module.'_owner'] = array($search_owner[1]);
					}
				}
			}
		}
		// END HANDLE SEACH DEALS OWNER BY TEAM
			
		//when use search feature, running this block code
		$searchCriteria 	= $this->data_search($input, $criteria['company_id']);

		$searchCriteriaCore = $searchCriteria['result']['core']; // filter by core field type
		$searchCriteriaDate = $searchCriteria['result']['date']; // filter by type date
		$searchCriteriaCustom = $searchCriteria['result']['custom']; // filter by custom field type
		$searchCriteriaDateCustom = $searchCriteria['result']['date_custom']; // filter by custom field type

		$checkSearchByOwner = in_array($this->table_module.'.'.$this->table_module.'_owner', array_column($searchCriteriaCore, 0)); 
		if ($checkSearchByOwner == TRUE) // if search data by owner
		{
			$key_search_owner = array_search($this->table_module.'.'.$this->table_module.'_owner', array_column($searchCriteriaCore, 0)); // get key, position owner in array	      
			if (is_array($searchCriteriaCore[$key_search_owner][2]) && countCustom($searchCriteriaCore[$key_search_owner][2]) > 0)
			{
				// if search data by multi owner
				if ($searchCriteriaCore[$key_search_owner][1] == "=") // when owner by IS 
				{
					$query->whereIn($this->table_module.'.'.$this->table_module.'_owner', $searchCriteriaCore[$key_search_owner][2]);

				}elseif ($searchCriteriaCore[$key_search_owner][1] == "!=") // when owner by isnt
				{
					$query->whereNotIn($this->table_module.'.'.$this->table_module.'_owner', $searchCriteriaCore[$key_search_owner][2]);
				}
			}else
			{
				// if search data by single owner
						$query->where('users.name', $searchCriteriaCore[$key_search_owner][1], $searchCriteriaCore[$key_search_owner][2]);
			}			
			// unset($searchCriteriaCore[$key_search_owner]); // remove owner in array, by position key
			array_splice($searchCriteriaCore, $key_search_owner, 1);
		}

		$checkSearchByCreated = in_array($this->table_module.'.created_by', array_column($searchCriteriaCore, 0)); 
		if ($checkSearchByCreated == TRUE) // if search data by created
		{
			$key_search_created = array_search($this->table_module.'.created_by', array_column($searchCriteriaCore, 0)); // get key, position created in array
			if (is_array($searchCriteriaCore[$key_search_created][2]) && countCustom($searchCriteriaCore[$key_search_created][2]) > 0)
			{
				// if search data by multi created
				if ($searchCriteriaCore[$key_search_created][1] == "=") // when owner by IS 
				{
					$query->whereIn($this->table_module.'.created_by', $searchCriteriaCore[$key_search_created][2]);

				}elseif ($searchCriteriaCore[$key_search_created][1] == "!=") // when owner by isnt
				{
					$query->whereNotIn($this->table_module.'.created_by', $searchCriteriaCore[$key_search_created][2]);
				}
			} else {
				// if search data by single created
						$query->where('users_created.name', $searchCriteriaCore[$key_search_created][1], $searchCriteriaCore[$key_search_created][2]);
			}			
			// unset($searchCriteriaCore[$key_search_created]); // remove created in array, by position key
			array_splice($searchCriteriaCore, $key_search_created, 1);
		}

		$checkSearchByModified = in_array($this->table_module.'.modified_by', array_column($searchCriteriaCore, 0)); 
		if ($checkSearchByModified == TRUE) // if search data by modified
		{
			$key_search_modified = array_search($this->table_module.'.modified_by', array_column($searchCriteriaCore, 0)); // get key, position modified in array
			if (is_array($searchCriteriaCore[$key_search_modified][2]) && countCustom($searchCriteriaCore[$key_search_modified][2]) > 0)
			{
				// if search data by multi modified
				if ($searchCriteriaCore[$key_search_modified][1] == "=") // when owner by IS 
				{
					$query->whereIn($this->table_module.'.modified_by', $searchCriteriaCore[$key_search_modified][2]);

				}
				elseif ($searchCriteriaCore[$key_search_modified][1] == "!=") // when owner by isnt
				{
					$query->whereNotIn($this->table_module.'.modified_by', $searchCriteriaCore[$key_search_modified][2]);
				}
			} else {
				// if search data by single modified
				$query->where('users_modified.name', $searchCriteriaCore[$key_search_modified][1], $searchCriteriaCore[$key_search_modified][2]);
			}
			// unset($searchCriteriaCore[$key_search_modified]); // remove modified in array, by position key
			array_splice($searchCriteriaCore, $key_search_modified, 1);
		}

		if (countCustom($searchCriteriaDate) > 0) // if search data by date
		{
			$date_between 	= $this->date_between($searchCriteriaDate);
			$query->whereRaw($date_between);
		}

		if (countCustom($searchCriteriaDateCustom) > 0) // if search data by date
		{
			$date_between_custom 	= $this->date_between_custom($searchCriteriaDateCustom);

			$query->whereRaw($date_between_custom);
		}

		// Update By Rendi 11.03.2019
		if (countCustom($searchCriteriaCustom) > 0 ) // if search data by custom fields
		{
			foreach ($searchCriteriaCustom as $value) 
			{
				if ($value[1] == "=" && is_array($value[2])) // operator is 
				{
					// only for custom multipleoption
					$fields = explode('.', $value[0]);
					$fields_type = $this->model_custom_fields_class::select($this->table_module.'_custom_fields_input_type')
										->where($this->table_module.'_custom_values_maps','=',$fields[1])
										->where('company_id','=',$criteria['company_id'])
										->first();
										
					if ($fields_type[$this->table_module.'_custom_fields_input_type'] === 'multipleoption') 
					{
						$query->where(function ($query) use ($value) 
						{
							foreach ($value[2] as $value2) 
							{
								$query->orwhere($value[0], 'LIKE', '%'.$value2.'%');
							}
						});
					}// end
					else
					{
						$query->whereIn($value[0], $value[2]);
					}
				}
				elseif ($value[1] == "IN") // operator isn't
				{
					$query->whereIn($value[0],$value[2]);
				}
				elseif ($value[1] == "!=" && is_array($value[2])) // operator isn't
				{
					$query->whereNotIn($value[0], $value[2]);
				}elseif ($value[2] === "%%") {

				}
				else
				{
					$query->where($value[0], $value[1], $value[2]); // Like
				}
			}
		}

		$checkParentType 	= in_array($this->table_module.'.'.$this->table_module.'_parent_type', array_column($searchCriteriaCore, 0));
		if ($checkParentType == TRUE) // search data by calls_parent_type
		{
			$keyParentType 	= array_search($this->table_module.'.'.$this->table_module.'_parent_type', array_column($searchCriteriaCore, 0));
			$parentTypeOpp 			= $searchCriteriaCore[$keyParentType][1]; // get operator
			$parentTypeKeyword 	= $searchCriteriaCore[$keyParentType][2]; // get keyword
			
			$query->where('sys_rel.rel_to_module', $parentTypeOpp, $parentTypeKeyword);
			unset($searchCriteriaCore[$keyParentType]);
		}

		$checkParentId 	= in_array($this->table_module.'.'.$this->table_module.'_parent_id', array_column($searchCriteriaCore, 0));
		if ($checkParentId == TRUE)  // search data by calls_parent_id
		{
			$searchCriteriaCore = array_values($searchCriteriaCore); // reset key array, to 0 
			$keyParentId 				= array_search($this->table_module.'.'.$this->table_module.'_parent_id', array_column($searchCriteriaCore, 0));
			$parentIdOpp 				= $searchCriteriaCore[$keyParentId][1]; // get operator
			$parentIdKeyword 		= $searchCriteriaCore[$keyParentId][2]; // get keyword
			
			$query->having($this->table_module.'_parent_id', $parentIdOpp, $parentIdKeyword);
			unset($searchCriteriaCore[$keyParentId]);
		}

		// ADD BY ANDRIAN
		// FOR SOLVED SEARCH 'is_empty' and 'is_not_empty'
		if ( countCustom($searchCriteriaCore) ) 
		{
			foreach ($searchCriteriaCore as $key => $value) 
			{
				if ($value[1] == "=" && is_array($value[2])) // operator is 
				{
					$query->whereIn($value[0], $value[2]);
				}
				elseif ($value[1] == "!=" && is_array($value[2])) // operator isn't
				{
					$query->whereNotIn($value[0], $value[2]);
				}
				elseif ( $value[1] === '==' ) 
				{
					$query->where(function ($query) use ($value) 
					{
						$query->where($value[0], '=', $value[2]);
						$query->orWhereNull($value[0]);
					});
				}
				elseif ( $value[1] === '!==' ) 
				{
					$query->where(function ($query) use ($value) 
					{
						$query->where($value[0], '!=', $value[2]);
						$query->whereNotNull($value[0]);
					});
				}
				else
				{
					$query->where($value[0], $value[1], $value[2]); // search data in core fields
				}
			}
		}

		$getAllFilteredId = $query->get()->toArray();
		return $getAllFilteredId;
	}

	public function getListCount($criteria=array(), $fields=array(), $input=array(), $data_roles=TRUE, $data_filter=TRUE)
	{
		$sys = new sys();
		$fieldsName = $this->select_fieldsName($fields); // list fields in core field and custom fields.
		# DEFINED VARIABLE
		$listFieldsCustom 		= $fields['listFieldsCustom']; // Get Fields Custom
		# END

		# CONVERT TO ROW QUERY FORMAT
		// $fieldsNameConvert	= $this->convertFieldsName($fieldsName);
		# END 

		# SELECT QUERY DYNAMIC BY $fieldsName
		$b = 'b';

		$fieldsNameSysRel = "sys_rel.rel_to_module as ".$this->table_module."_parent_type, sys_rel.rel_to_id as ".$this->table_module."_parent_id,
											(
												CASE   
													WHEN sys_rel.rel_to_module = 'leads' THEN ( SELECT CONCAT(COALESCE(leads.leads_first_name, ''),' ', COALESCE(leads.leads_last_name, '')) FROM leads WHERE leads.leads_serial_id = sys_rel.rel_to_id LIMIT 1)  
													WHEN sys_rel.rel_to_module = 'contacts' THEN ( SELECT CONCAT(COALESCE(contacts.contacts_first_name, ''),' ', COALESCE(contacts.contacts_last_name, '')) FROM contacts WHERE contacts.contacts_serial_id = sys_rel.rel_to_id LIMIT 1) 
													WHEN sys_rel.rel_to_module = 'org'  THEN ( SELECT org_name FROM org WHERE org.org_serial_id = sys_rel.rel_to_id LIMIT 1) 
													WHEN sys_rel.rel_to_module = 'projects' THEN ( SELECT projects_name FROM projects WHERE projects.projects_serial_id = sys_rel.rel_to_id LIMIT 1 )
													WHEN sys_rel.rel_to_module is NULL THEN ''
												END
												) as ".$this->table_module."_parent_name";
		// $query = $this->model_class::select(DB::raw($fieldsNameConvert.",".$fieldsNameSysRel));

		$query = $this->model_class::select(DB::raw('count('.$this->table_module.'.'.$this->table_module.'_serial_id) as data_count'));
		# END 


		# LEFT JOIN WITH SYS REL
		$query->leftjoin('sys_rel', function($join) use ($criteria)
		        { 
		            $join->on('sys_rel.rel_from_id', '=', $this->table_module.'.'.$this->table_module.'_serial_id')
		            ->where('sys_rel.rel_from_module', '=', $this->table_module);
		        });
		# END 

		# LEFT JOIN WITH CUSTOM FIELDS
		$temp_alias = array();
		if (countCustom($listFieldsCustom) > 0) // 	if fields custom available
		{
			$query->leftjoin($this->table_module.'_custom_values_ as mcv', function($join) use ($criteria)
							{
								$join->on('mcv.'.$this->table_module.'_serial_id', '=', $this->table_module.'.'.$this->table_module.'_serial_id')
								->where('mcv.company_id', '=', $criteria['company_id']);
							});

		}
			
		# DATA ROLES
		$roles 							= $this->get_roles($this->table_module, $criteria['company_id'], $criteria['users_id']);
		$myRecordOnlyCheck 	= $this->myRecordOnlyCheck($criteria['company_id'], $criteria['users_id']); //if filter view " (You) " Checked
		if ($data_roles == TRUE && countCustom($roles['contents']) > 0 && $myRecordOnlyCheck === FALSE) 
		{
			//$roles['contents'] is container member inside roles by users_id, example : team view then $roles['contents'] = array('11', '12', '13') 
			//if member != empty , then running this block
			//more detail about $roles, please check get_roles()
			$query->whereIn($this->table_module.'.'.$this->table_module.'_owner', $roles['contents']);
		}

		# FILTER FEATURE
		if ($data_filter == TRUE)
		{
			//Running filter when $data_filter TRUE
			$filterView 				= $this->viewChecked($criteria['company_id'], $criteria['users_id']); // checked filter view active
			$filterViewName 		= $filterView[$this->table_module.'_view_name'];
			
			$filterCriteria 			= $this->generate_view($filterView, $criteria['company_id'], $criteria['users_id']); // get the selected filter 	

			$filterCriteria 			= $this->data_search($filterCriteria, $criteria['company_id'], $temp_alias, $listFieldsCustom, $b); // generate format for use filter feature
			
			if(isset($filterCriteria['temp']))
			{
				if (countCustom($listFieldsCustom) == 0) // if fields custom not available
				{
					$query->leftjoin($this->table_module.'_custom_values_ as mcv', function($join) use ($criteria)
								{
									$join->on('mcv.'.$this->table_module.'_serial_id', '=', $this->table_module.'.'.$this->table_module.'_serial_id')
									->where('mcv.company_id', '=', $criteria['company_id']);
								});
				}
			}
			

			$filterCriteriaCore 	= $filterCriteria['result']['core']; // filter by core field type
			$filterCriteriaDate 	= $filterCriteria['result']['date']; // filter by date type
			$filterCriteriaCustom = $filterCriteria['result']['custom']; // filter by custom field type

			if ($myRecordOnlyCheck === TRUE)
		  {
		  	// if user choosen filter "You", then owner by sesson users_id login
				$query->where($this->table_module.'_owner', '=', $criteria['users_id']);
			}
			elseif (countCustom($filterView) > 0 && $filterViewName != 'Everyone' && $filterViewName != 'You') 
			{
				// get users statuc active or deactive
				$get_users = $this->model_users_class::select('users_status')
																							 ->leftjoin('users_company as comp','comp.users_id','=','users.id')
																							 ->where('id','=',$filterView['users_id'])
																							 ->where('company_id','=',$filterView['company_id'])
																							 ->first();

				// for check if users deactive
				if($get_users['users_status'] == 'deactive')
				{
					// get current users active
					$get_active = $this->model_view_class::select($this->table_module.'_view_serial_id', $this->table_module.'_view_name')
																								 ->where($this->table_module.'_view_name','=','Everyone')
																								 ->where('users_id','=', $criteria['users_id'])
																								 ->where('company_id','=', $criteria['company_id'])
																								 ->first();

					// update contact_view_serial_id into default
					$update = $this->model_view_checked_class::where('users_id','=',$criteria['users_id'])
																						 			->where('company_id','=',$criteria['company_id'])
																									->update([$this->table_module.'_view_serial_id' => $get_active[$this->table_module.'_view_serial_id']]);

					// change filter into default/Everyone when load data
					$filterView[$this->table_module.'_view_serial_id'] = $get_active[$this->table_module.'_view_serial_id'];
					$filterView[$this->table_module.'_view_name'] = 'Everyone';

					// change criteria into empty
					$filterCriteriaCore = array();
					$filterCriteriaDate = array();
					$filterCriteriaCustom = array();
				}
				
				$query_count = 1; // count query with left join
				//if user choosen filter, except filter 'Everyone' and 'You' 
				$checkFilterByOwner = in_array($this->table_module.'.'.$this->table_module.'_owner', array_column($filterCriteriaCore, 0)); 

				if ($checkFilterByOwner == TRUE) // if filter data by owner
				{
		      $key_filter_owner = array_search($this->table_module.'.'.$this->table_module.'_owner', array_column($filterCriteriaCore, 0)); // get key, position owner in array		      
		      if (is_array($filterCriteriaCore[$key_filter_owner][2]) && countCustom($filterCriteriaCore[$key_filter_owner][2]) > 0)
		      {
		      	// if filter data by multi owner
		      	if ($filterCriteriaCore[$key_filter_owner][1] == "=") // when owner by IS 
		      	{
			      	$query->whereIn($this->table_module.'.'.$this->table_module.'_owner', $filterCriteriaCore[$key_filter_owner][2]);

		      	}elseif ($filterCriteriaCore[$key_filter_owner][1] == "!=") // when owner by isnt
		      	{
			      	$query->whereNotIn($this->table_module.'.'.$this->table_module.'_owner', $filterCriteriaCore[$key_filter_owner][2]);
		      	}
		      }else
		      {
		      	// if filter data by single owner
						$query->where('users.name', $filterCriteriaCore[$key_filter_owner][1], $filterCriteriaCore[$key_filter_owner][2]);
		      }			
					unset($filterCriteriaCore[$key_filter_owner]); // remove owner in array, by position key
				}
				if (countCustom($filterCriteriaDate) > 0) // if filter data by date
				{
					$date_between 	= $this->date_between($filterCriteriaDate);
					$query->whereRaw($date_between);
				}
				if (countCustom($filterCriteriaCustom) > 0 ) // if filter data by custom fields
				{
					foreach ($filterCriteriaCustom as $value) 
					{
						if ($value[1] == "=" && is_array($value[2])) // operator is 
						{
							$query->whereIn($value[0], $value[2]);
						}
						elseif ($value[1] == "!=" && is_array($value[2])) // operator isn't
						{
							$query->whereNotIn($value[0], $value[2]);
						}
						elseif ( $value[1] === '==' ) // operator is_empty
						{
							$query->where(function ($query) use ($value) 
							{
								$query->where($value[0], '=', $value[2]);
								$query->orWhereNull($value[0]);
							});
						}
						elseif ( $value[1] === '!==' ) // operator is_not_empty
						{
							$query->where(function ($query) use ($value) 
							{
								$query->where($value[0], '!=', $value[2]);
								$query->whereNotNull($value[0]);
							});
						}
						else
						{
							$query->where($value[0], $value[1], $value[2]); // operator contains, start_with and end_with
						}
					}
				}
				$checkParentType 	= in_array($this->table_module.'.'.$this->table_module.'_parent_type', array_column($filterCriteriaCore, 0));
				if ($checkParentType == TRUE) // filter data by calls_parent_type
				{
					$keyParentType 	= array_search($this->table_module.'.'.$this->table_module.'_parent_type', array_column($filterCriteriaCore, 0));
					$parentTypeOpp 			= $filterCriteriaCore[$keyParentType][1]; // get operator
					$parentTypeKeyword 	= $filterCriteriaCore[$keyParentType][2]; // get keyword
					
					$query->where('sys_rel.rel_to_module', $parentTypeOpp, $parentTypeKeyword);
					unset($filterCriteriaCore[$keyParentType]);
				}
				$checkParentId 	= in_array($this->table_module.'.'.$this->table_module.'_parent_id', array_column($filterCriteriaCore, 0));
				if ($checkParentId == TRUE)  // filter data by calls_parent_id
				{
					$filterCriteriaCore = array_values($filterCriteriaCore); // reset key array, to 0 
					$keyParentId 				= array_search($this->table_module.'.'.$this->table_module.'_parent_id', array_column($filterCriteriaCore, 0));
					$parentIdOpp 				= $filterCriteriaCore[$keyParentId][1]; // get operator
					$parentIdKeyword 		= $filterCriteriaCore[$keyParentId][2]; // get keyword
					
					$query->having($this->table_module.'_parent_id', $parentIdOpp, $parentIdKeyword);
					unset($filterCriteriaCore[$keyParentId]);
				}
				// $query->where($filterCriteriaCore);
				if ( countCustom($filterCriteriaCore) > 0 ) 
				{
					foreach ($filterCriteriaCore as $key => $value) 
					{
						if ($value[1] == "=" && is_array($value[2])) // operator is 
						{
							$query->whereIn($value[0], $value[2]);
						}
						elseif ($value[1] == "!=" && is_array($value[2])) // operator isn't
						{
							$query->whereNotIn($value[0], $value[2]);
						}
						elseif ( $value[1] === '==' ) 
						{
							$query->where(function ($query) use ($value) 
							{
								$query->where($value[0], '=', $value[2]);
								$query->orWhereNull($value[0]);
							});
						}
						elseif ( $value[1] === '!==' ) 
						{
							$query->where(function ($query) use ($value) 
							{
								$query->where($value[0], '!=', $value[2]);
								$query->whereNotNull($value[0]);
							});
						}
						elseif ($value[1] == "=" && ($value[0] == $this->table_module.".date_created" || $value[0] == $this->table_module.".date_modified" || $value[0] == $this->table_module.".".$this->table_module."_date_start" || $value[0] == $this->table_module.".".$this->table_module."_date_end"))
						{
							$date_range = explode(" - ", $value[2]);
							if (countCustom($date_range) > 1)
							{
								$date_start = date('Y-m-d', strtotime($date_range[0])).' 00:00:00'; 
								$date_end 	= date('Y-m-d', strtotime($date_range[1])).' 23:59:59';

								$query->whereBetween($value[0], [$date_start, $date_end]);
							}
						}
						elseif ($value[1] == "!=" && ($value[0] == $this->table_module.".date_created" || $value[0] == $this->table_module.".date_modified" || $value[0] == $this->table_module.".".$this->table_module."_date_start" || $value[0] == $this->table_module.".".$this->table_module."_date_end"))
						{
							$date_range = explode(" - ", $value[2]);
							if (countCustom($date_range) > 1)
							{
								$date_start = date('Y-m-d', strtotime($date_range[0])).' 00:00:00'; 
								$date_end 	= date('Y-m-d', strtotime($date_range[1])).' 23:59:59';

								$query->whereNotBetween($value[0], [$date_start, $date_end]);
							}
						}
						else
						{
							$query->where($value[0], $value[1], $value[2]); // search data in core fields
						}
					}
				}
				// end
			}
		} 


		# SEARCH FEATURE
		if (isset($_GET['subaction']) && $_GET['subaction'] == "search")
		{
			$query_count = 1; // count query with left join
			
			//ADD BY AGUNG -> 15/03/2019 -> SEARCHING OWNER BY TEAMS
			// HANDLE SEACH DEALS OWNER BY TEAM
			if(isset($input[$this->table_module.'_owner_opp']) AND isset($input[$this->table_module.'_owner']))
			{
				if($input[$this->table_module.'_owner_opp'] == "is" OR $input[$this->table_module.'_owner_opp'] == "isnt")
				{
					if(is_array($input[$this->table_module.'_owner']))
					{
						if(countCustom($input[$this->table_module.'_owner']) > 0 OR !empty($input[$this->table_module.'_owner']))
						{
							$search_owner = array();
							foreach($input[$this->table_module.'_owner'] as $key_owner => $val_owner)
							{
								$owner_id = $val_owner;
								if (is_array($val_owner)) 
								{
									$owner_id = $val_owner[0];
								}
								$search_owner[$key_owner] = explode("!@#$%^&*()", $owner_id);
								if(isset($search_owner[$key_owner][1]))
								{
									$input[$this->table_module.'_owner'][$key_owner] = $search_owner[$key_owner][1];
								}
							}
	
						}
					}
					else
					{
						$search_owner = explode("!@#$%^&*()", $input[$this->table_module.'_owner']);
						if(isset($search_owner[1]))
						{
							$input[$this->table_module.'_owner'] = array($search_owner[1]);
						}
					}
				}

			}
			// END HANDLE SEACH DEALS OWNER BY TEAM
			
			//when use search feature, running this block code
			$searchCriteria 			= $this->data_search($input, $criteria['company_id']);

			$searchCriteriaCore 	= $searchCriteria['result']['core']; // filter by core field type
			$searchCriteriaDate 	= $searchCriteria['result']['date']; // filter by type date
			$searchCriteriaCustom = $searchCriteria['result']['custom']; // filter by custom field type
			$searchCriteriaDateCustom = $searchCriteria['result']['date_custom']; // filter by custom field type

      $checkSearchByOwner = in_array($this->table_module.'.'.$this->table_module.'_owner', array_column($searchCriteriaCore, 0)); 
			if ($checkSearchByOwner == TRUE) // if search data by owner
			{
	      $key_search_owner = array_search($this->table_module.'.'.$this->table_module.'_owner', array_column($searchCriteriaCore, 0)); // get key, position owner in array	      
	      if (is_array($searchCriteriaCore[$key_search_owner][2]) && countCustom($searchCriteriaCore[$key_search_owner][2]) > 0)
	      {
	      	// if search data by multi owner
	      	if ($searchCriteriaCore[$key_search_owner][1] == "=") // when owner by IS 
	      	{
		      	$query->whereIn($this->table_module.'.'.$this->table_module.'_owner', $searchCriteriaCore[$key_search_owner][2]);

	      	}elseif ($searchCriteriaCore[$key_search_owner][1] == "!=") // when owner by isnt
	      	{
		      	$query->whereNotIn($this->table_module.'.'.$this->table_module.'_owner', $searchCriteriaCore[$key_search_owner][2]);
	      	}
	      }else
	      {
	      	// if search data by single owner
					$query->where('users.name', $searchCriteriaCore[$key_search_owner][1], $searchCriteriaCore[$key_search_owner][2]);
	      }			
				// unset($searchCriteriaCore[$key_search_owner]); // remove owner in array, by position key
				array_splice($searchCriteriaCore, $key_search_owner, 1);
			}

			$checkSearchByCreated = in_array($this->table_module.'.created_by', array_column($searchCriteriaCore, 0)); 
			if ($checkSearchByCreated == TRUE) // if search data by created
			{
	      $key_search_created = array_search($this->table_module.'.created_by', array_column($searchCriteriaCore, 0)); // get key, position created in array
	      if (is_array($searchCriteriaCore[$key_search_created][2]) && countCustom($searchCriteriaCore[$key_search_created][2]) > 0)
	      {
	      	// if search data by multi created
	      	if ($searchCriteriaCore[$key_search_created][1] == "=") // when owner by IS 
	      	{
		      	$query->whereIn($this->table_module.'.created_by', $searchCriteriaCore[$key_search_created][2]);

	      	}elseif ($searchCriteriaCore[$key_search_created][1] == "!=") // when owner by isnt
	      	{
		      	$query->whereNotIn($this->table_module.'.created_by', $searchCriteriaCore[$key_search_created][2]);
	      	}
	      }else
	      {
	      	// if search data by single created
					$query->where('users_created.name', $searchCriteriaCore[$key_search_created][1], $searchCriteriaCore[$key_search_created][2]);
	      }			
				// unset($searchCriteriaCore[$key_search_created]); // remove created in array, by position key
				array_splice($searchCriteriaCore, $key_search_created, 1);

			}

			$checkSearchByModified = in_array($this->table_module.'.modified_by', array_column($searchCriteriaCore, 0)); 
			if ($checkSearchByModified == TRUE) // if search data by modified
			{
	      $key_search_modified = array_search($this->table_module.'.modified_by', array_column($searchCriteriaCore, 0)); // get key, position modified in array
	      if (is_array($searchCriteriaCore[$key_search_modified][2]) && countCustom($searchCriteriaCore[$key_search_modified][2]) > 0)
	      {
	      	// if search data by multi modified
	      	if ($searchCriteriaCore[$key_search_modified][1] == "=") // when owner by IS 
	      	{
		      	$query->whereIn($this->table_module.'.modified_by', $searchCriteriaCore[$key_search_modified][2]);

	      	}elseif ($searchCriteriaCore[$key_search_modified][1] == "!=") // when owner by isnt
	      	{
		      	$query->whereNotIn($this->table_module.'.modified_by', $searchCriteriaCore[$key_search_modified][2]);
	      	}
	      }else
	      {
	      	// if search data by single modified
					$query->where('users_modified.name', $searchCriteriaCore[$key_search_modified][1], $searchCriteriaCore[$key_search_modified][2]);
	      }			
				// unset($searchCriteriaCore[$key_search_modified]); // remove modified in array, by position key
				array_splice($searchCriteriaCore, $key_search_modified, 1);
			}

			if (countCustom($searchCriteriaDate) > 0) // if search data by date
			{
				$date_between 	= $this->date_between($searchCriteriaDate);
				$query->whereRaw($date_between);
			}

			if (countCustom($searchCriteriaDateCustom) > 0) // if search data by date
			{
				$date_between_custom 	= $this->date_between_custom($searchCriteriaDateCustom);

				$query->whereRaw($date_between_custom);
			}

			// Update By Rendi 11.03.2019
			if (countCustom($searchCriteriaCustom) > 0 ) // if search data by custom fields
			{
				foreach ($searchCriteriaCustom as $value) 
				{
					if ($value[1] == "=" && is_array($value[2])) // operator is 
					{
						// only for custom multipleoption
						$fields = explode('.', $value[0]);
						$fields_type = $this->model_custom_fields_class::select($this->table_module.'_custom_fields_input_type')
																		->where($this->table_module.'_custom_values_maps','=',$fields[1])
																		->where('company_id','=',$criteria['company_id'])
																		->first();
						if ($fields_type[$this->table_module.'_custom_fields_input_type'] === 'multipleoption') 
						{
							$query->where(function ($query) use ($value) 
							{
								foreach ($value[2] as $value2) 
								{
									$query->orwhere($value[0], 'LIKE', '%'.$value2.'%');
								}
							});
						}// end
						else
						{
							$query->whereIn($value[0], $value[2]);
						}
					}
					elseif ($value[1] == "IN") // operator isn't
					{
						$query->whereIn($value[0],$value[2]);
					}
					elseif ($value[1] == "!=" && is_array($value[2])) // operator isn't
					{
						$query->whereNotIn($value[0], $value[2]);
					}elseif ($value[2] === "%%") {

					}
					else
					{
						$query->where($value[0], $value[1], $value[2]); // Like
					}
				}
			}
			$checkParentType 	= in_array($this->table_module.'.'.$this->table_module.'_parent_type', array_column($searchCriteriaCore, 0));
			if ($checkParentType == TRUE) // search data by calls_parent_type
			{
				$keyParentType 	= array_search($this->table_module.'.'.$this->table_module.'_parent_type', array_column($searchCriteriaCore, 0));
				$parentTypeOpp 			= $searchCriteriaCore[$keyParentType][1]; // get operator
				$parentTypeKeyword 	= $searchCriteriaCore[$keyParentType][2]; // get keyword
				
				$query->where('sys_rel.rel_to_module', $parentTypeOpp, $parentTypeKeyword);
				unset($searchCriteriaCore[$keyParentType]);
			}
			$checkParentId 	= in_array($this->table_module.'.'.$this->table_module.'_parent_id', array_column($searchCriteriaCore, 0));
			if ($checkParentId == TRUE)  // search data by calls_parent_id
			{
				$searchCriteriaCore = array_values($searchCriteriaCore); // reset key array, to 0 
				$keyParentId 				= array_search($this->table_module.'.'.$this->table_module.'_parent_id', array_column($searchCriteriaCore, 0));
				$parentIdOpp 				= $searchCriteriaCore[$keyParentId][1]; // get operator
				$parentIdKeyword 		= $searchCriteriaCore[$keyParentId][2]; // get keyword
				
				// $query->having($this->table_module.'_parent_id', $parentIdOpp, $parentIdKeyword);
				unset($searchCriteriaCore[$keyParentId]);
			}

			// ADD BY ANDRIAN
			// FOR SOLVED SEARCH 'is_empty' and 'is_not_empty'
			if ( countCustom($searchCriteriaCore) ) 
			{
				foreach ($searchCriteriaCore as $key => $value) 
				{
					if ($value[1] == "=" && is_array($value[2])) // operator is 
					{
						$query->whereIn($value[0], $value[2]);
					}
					elseif ($value[1] == "!=" && is_array($value[2])) // operator isn't
					{
						$query->whereNotIn($value[0], $value[2]);
					}
					elseif ( $value[1] === '==' ) 
					{
						$query->where(function ($query) use ($value) 
						{
							$query->where($value[0], '=', $value[2]);
							$query->orWhereNull($value[0]);
						});
					}
					elseif ( $value[1] === '!==' ) 
					{
						$query->where(function ($query) use ($value) 
						{
							$query->where($value[0], '!=', $value[2]);
							$query->whereNotNull($value[0]);
						});
					}
					else
					{
						$query->where($value[0], $value[1], $value[2]); // search data in core fields
					}
				}
			}
			// END ADD BY ANDRIAN
			// END FOR SOLVED SEARCH 'is_empty' and 'is_not_empty'
		} 

		# SEARCH GLOBAL FEATURE
		if (isset($_GET['subaction']))
		{
			if ($_GET['subaction'] == "search_global") 
			{
				$query_count = 1; // count query with left join

				$keyword 	= $criteria['keyword'];
				$fields 	= array();

				foreach ($fieldsName as $value) 
				{
					$name 			= $value;
					$exp_value 	= explode(" ", $value);
					if (is_array($exp_value) && isset($exp_value[2])) 
					{
						$fields[]		= $exp_value[0];
					}else{
						$fields[] 	= $name;
					}
				}

				$query->where(function ($query) use ($fields, $keyword) 
				{
					for ($i=0; $i < countCustom($fields); $i++) 
					{ 
						$query->orWhere($fields[$i], 'LIKE', $keyword.'%');
					}
				});
			}
		} 

		$query->where($this->table_module.'.company_id', '=', $criteria['company_id']);
		$query->where($this->table_module.'.deleted', '=', Config('setting.NOT_DELETED'));
		# END
	
		$query 				= $query->first();

		$query = json_decode(json_encode($query), TRUE);

		$result = 0;
		if (!isEmpty($query)) 
		{
			$result = $query['data_count'];
		}

		return $result;
	}

	public function ajax_parent($table,$company_id, $users_id, $keyword, $id = 0) {
		$sys = new sys();
		
		$limit = 10;
		$contentAvailable = $sys->get_roles($table, $company_id, $users_id);
		$owner = $contentAvailable['contents'];
		$data = array();

		if($table != '')
		{
			if($table == 'contacts' || $table == 'leads')
			{
				$rdata = DB::table($table)
							->select(DB::raw($table.'_serial_id AS dropdown_options_value'), DB::raw("CONCAT(".$table."_first_name, ' ', COALESCE(".$table."_last_name, '')) AS dropdown_options_label"))
							->where('deleted','=',Config('setting.NOT_DELETED'))
							->where('company_id','=', $company_id);
			}else{
				$rdata = DB::table($table)
						->select(DB::raw($table.'_serial_id AS dropdown_options_value'), DB::raw($table."_name AS dropdown_options_label"))
						->where('deleted', 0)
						->where('company_id', $company_id);
			}
			
			if(!isEmpty($owner)){
				$rdata = $rdata->whereIn($table.'_owner', $owner);
			}
	
			if ($id > 0) {
				$rdata = $rdata->where($table."_serial_id", $id);
			}
	
			if ($keyword != '') {
				if (is_array($keyword)) {
					$rdata = $rdata->wherein($table.'_serial_id',$keyword);
				}else{
					if($table == 'contacts' OR $table == 'leads')
					{
						$rdata = $rdata->where(function($query) use ($keyword,$table){
										$query->where($table."_first_name", "like", "%$keyword%")
											->orWhere($table."_last_name", "like", "%$keyword%");
									});
					}
					else {
						$rdata = $rdata->where($table."_name", "like", "%$keyword%");
					}
	
				}
			}
						
			$data = $rdata->take($limit)->get()->toArray();
		}
		
		$res['data'] = $data;
		$res['reltype'] = $table;

		return $res;
	}

	public function checkCustomFieldsIdByName($name,$company_id)
	{
	  $query =  $this->model_custom_fields_class::select($this->table_module.'_custom_fields_serial_id')
				->where($this->table_module.'_custom_fields_name','=',$name)
				->where('company_id','=',$company_id)
				->first();
	  if(count(array($query)))
	  {
		return $query[$this->table_module.'_custom_fields_serial_id'];
	  }
	  return false;
	}

	public function simpanTag($calls_tags, $serial_id, $company_id, $users_id, $table_module) {
		$newtagsuuid = [];
		foreach ($calls_tags as $key => $value) {
			if (is_array($value)) {
				$type = isset($value['type']) ? $value['type'] : '';
				$label = isset($value['label']) ? $value['label'] : '';
				$color = isset($value['color']) ? $value['color'] : '';
				$tagsuuid = isset($value['value']) ? $value['value'] : '';
			}

			if (is_object($value)) {
				$type = isset($value->type) ? $value->type : '';
				$label = isset($value->label) ? $value->label : '';
				$color = isset($value->color) ? $value->color : '';
				$tagsuuid = isset($value->value) ? $value->value : '';
			}
			
			if ($type == 'newtag') {
				$tag = $this->model_tag_class::select('tags_serial_id')
																					->where('tags_name',$label)
																					->where('color', $color)
																					->where('company_id', $company_id)
																					->first();
				if (countCustom($tag) == 0)
				{
					$datatag = [
						'tags_uuid'		=> $this->uuid::uuid4()->toString(),
						'tags_name'		=> $label,
						'color'			=> $color,
						'company_id'	=> $company_id,
						'date_created'	=> date('Y-m-d H:i:s'),
						'created_by'	=> $users_id
					];

					$tag = $this->model_tag_class::create($datatag);
				}
			} else {
				$tag = $this->model_tag_class::select('tags_serial_id')
							->where('tags_uuid', $tagsuuid)
							->where('color', $color)
							->where('company_id', $company_id)
							->first();

				if (!$tag) {
					$datatag = [
						'tags_uuid'		=> $this->uuid::uuid4()->toString(),
						'tags_name'		=> $label,
						'color'			=> $color,
						'company_id'	=> $company_id,
						'date_created'	=> date('Y-m-d H:i:s'),
						'created_by'	=> $users_id
					];
	
					$tag = $this->model_tag_class::create($datatag);
				}
			}

			$tagMaps = $this->model_tags_map_class::select('tags_map_serial_id','tags_serial_id')
																					->where('tags_serial_id',$tag->tags_serial_id)
																					->where('tags_map_module_name', $table_module)
																					->where('tags_map_data_id', $serial_id)
																					->where('company_id', $company_id)
																					->first();

			if (countCustom($tagMaps) == 0)
			{
				$data = [
					'tags_map_uuid'			=> $this->uuid::uuid4()->toString(),
					'tags_serial_id'		=> $tag->tags_serial_id,
					'tags_map_module_name'	=> $table_module,
					'tags_map_data_id'		=> $serial_id,
					'company_id'			=> $company_id,
					'date_created'			=> date('Y-m-d H:i:s'),
					'created_by'			=> $users_id					
				];

				$this->model_tags_map_class::create($data);

				$this->model_syslog_class::create([
					'syslog_action' => 'Update Tags on ' . $table_module . ' with Tags Name ' . $label,
					'syslog_action_type' => 'create',
					'syslog_module_name' => $table_module,
					'syslog_data_id'    => $serial_id,
					'syslog_date_created' => date('Y-m-d H:i:s'),
					'syslog_created_by'   =>  $users_id,
					'company_id'  		=> $company_id,
					'syslog_options'    => 0,
					'syslog_action_from' => 'web',
					'syslog_ip'          => '::1'
				]);

				$newtagsuuid['tags_map_uuid'][] = $data['tags_map_uuid'];
			}
		}

		return $newtagsuuid;
	}

	public function removeTag($contacts_tags, $serial_id, $company_id, $users_id, $table_module) {
		/* Remove */
		if (countCustom($contacts_tags) > 0) {
			$uuid = isset($contacts_tags['tags_map_uuid']) ? $contacts_tags['tags_map_uuid'] : array_column($contacts_tags, 'tags_map_uuid');;
			
			$tags = DB::table('tags_map')
					->select('tags_map_serial_id', 'tags_map_serial_id', 'tags_name', 'tags_map_data_id')
					->join('tags', 'tags.tags_serial_id', 'tags_map.tags_serial_id')
					->where('tags_map.company_id', $company_id)
					->where('tags_map_data_id', $serial_id)
					->whereNotIn('tags_map_uuid', $uuid)
					->where('tags_map_module_name', $table_module)
					->get();
			
			if (countCustom($tags) > 0) {
				foreach ($tags as $key => $value) {
					$this->model_syslog_class::create([
						'syslog_action' => 'Delete Tags on '.$table_module.', Tags Name '. $value->tags_name . ' Removed',
						'syslog_action_type' => 'removetag',
						'syslog_module_name' => $table_module,
						'syslog_data_id'    => $value->tags_map_data_id,
						'syslog_date_created' => date('Y-m-d H:i:s'),
						'syslog_created_by'   =>  $users_id,
						'company_id'  		=> $company_id,
						'syslog_options'    => 0,
						'syslog_action_from' => 'web',
						'syslog_ip'          => '::1'
					]);
				}

				$tags_map_serial_idArr = array_column($tags->toArray(), 'tags_map_serial_id');
				if ($tags_map_serial_idArr > 0) {
					$this->model_tags_map_class::where('tags_map_data_id', $serial_id)->whereIn('tags_map_serial_id', $tags_map_serial_idArr)->delete();
				}
			}
		} else {
			$tags = DB::table('tags_map')
						->select('tags_map_serial_id', 'tags_map_serial_id', 'tags_name', 'tags_map_data_id')
						->join('tags', 'tags.tags_serial_id', 'tags_map.tags_serial_id')
						->where('tags_map.company_id', $company_id)
						->where('tags_map_data_id', $serial_id)
						->where('tags_map_module_name', $table_module)
						->get();

			foreach ($tags as $key => $value) {
				$this->model_syslog_class::create([
					'syslog_action' => 'Delete Tags on '.$table_module.', Tags Name '. $value->tags_name . ' Removed',
					'syslog_action_type' => 'removetag',
					'syslog_module_name' => $table_module,
					'syslog_data_id'    => $value->tags_map_data_id,
					'syslog_date_created' => date('Y-m-d H:i:s'),
					'syslog_created_by'   =>  $users_id,
					'company_id'  		=> $company_id,
					'syslog_options'    => 0,
					'syslog_action_from' => 'web',
					'syslog_ip'          => '::1'
				]);
			}
			
			if (countCustom($tags) > 0) {
				$this->model_tags_map_class::where('tags_map_data_id', $serial_id)->delete();				
			}
		}
	}

	public function getTagsMap($uuid, $company_id, $module) {
		$parent = $this->model_class::selectRaw($module . "_serial_id as id")->where($module . '_uuid', $uuid)->first();
		$data = $this->model_tags_map_class::select('tags_map.tags_map_uuid', DB::raw('tags.tags_uuid AS value'), DB::raw('tags.tags_name AS label'), 'tags.color')
					->join('tags', 'tags.tags_serial_id', 'tags_map.tags_serial_id')
					->where('tags_map.company_id', $company_id)
					->where('tags_map.tags_map_module_name', $module)
					->where('tags_map.tags_map_data_id', $parent->id)
					->get();

		return $data;
	}
	// get data tags for listing
	public function getTagsMapData($data=array(), $company_id, $module) {
		$data_tags = array();
		if (countCustom($data) > 0)
		{	
			foreach ($data['data'] as $key => $value)
			{	
						 $query = $this->model_tags_map_class::select(DB::raw('tags_map.tags_map_uuid,tags.tags_uuid AS value,tags.tags_name AS label,tags.color'))
						->join('tags', 'tags.tags_serial_id', 'tags_map.tags_serial_id')
						->where('tags_map.company_id', $company_id)
						->where('tags_map.tags_map_module_name', $module)
						->where('tags_map.tags_map_data_id', $value[$module.'_serial_id'])
						->get();
						
						if (countCustom($query) > 0) {
							$query = json_decode(json_encode($query),true);
							$data_tags[$key] = $query;
						} else  {
							$data_tags[$key] = [];
						}
			}
		}
		return $data_tags;
	}
	// Get All Data
	public function dataTags($company_id, $table_module)
    {
        $data_Tags = array();
        $query = $this->model_tags_map_class::select(DB::raw('tags.tags_name,tags.tags_serial_id,color'))
        ->join('tags', 'tags.tags_serial_id', 'tags_map.tags_serial_id')
        ->where('tags_map.company_id', $company_id)
        ->where('tags_map.tags_map_module_name', $table_module)
        ->orderBy('tags.tags_serial_id', 'desc')
        ->groupBy('tags.tags_serial_id')
        ->get();

        if (countCustom($query) > 0) {
            $query = json_decode(json_encode($query),true);
            $data_Tags = $query;
        } else  {
            $data_Tags = [];
        }
    
        return $data_Tags;        
    }

		public function removeTagByuuid($uuid, $company_id, $users_id, $table_module) {
		$tags = $this->model_tags_map_class::select('tags_map_serial_id', 'tags_map_serial_id', 'tags_name', 'tags_map_data_id')
					->join('tags', 'tags.tags_serial_id', 'tags_map.tags_serial_id')
					->where('tags_map_uuid', $uuid)
					->where('tags_map.company_id', $company_id)
					->first();

		$this->model_syslog_class::create([
			'syslog_action' => 'delete Tags on '.$table_module.', Tags Name '. $tags->tags_name . ' Removed',
			'syslog_action_type' => 'removetag',
			'syslog_module_name' => $table_module,
			'syslog_data_id'    => $tags->tags_map_data_id,
			'syslog_date_created' => date('Y-m-d H:i:s'),
			'syslog_created_by'   =>  $users_id,
			'company_id'  		=> $company_id,
			'syslog_options'    => 0,
			'syslog_action_from' => 'web',
			'syslog_ip'          => '::1'
		]);
		
		$this->model_tags_map_class::where('company_id', $company_id)->where('tags_map_serial_id', $tags->tags_map_serial_id)->delete();
	}

	public function elasticListing($criteria=array(), $fields=array(), $input=array(), $data_roles=TRUE, $data_filter=TRUE)
	{
		$sys 			= new sys();
		$_source = array();
		$temp_alias = array();
		$listFieldsCustom 		= $fields['listFieldsCustom']; // Get Fields Custom

		//Running filter when $data_filter TRUE
		$filterView 		= $this->viewChecked($criteria['company_id'], $criteria['users_id']); // checked filter view active
		$filterViewName = $filterView[$this->table_module.'_view_name'];

		$filterCriteria = $this->generate_view($filterView, $criteria['company_id'], $criteria['users_id']); // get the selected filter 	

		if ( !empty($filterCriteria[$this->table_module.'_parent_id_opp']) ) 
		{
			if ($filterCriteria[$this->table_module.'_parent_id_opp'] != 'is' && $filterCriteria[$this->table_module.'_parent_id_opp'] != 'isnt') 
			{
				$filterCriteria[$this->table_module.'_parent_name_opp'] = $filterCriteria[$this->table_module.'_parent_id_opp'];

				if (!empty($filterCriteria[$this->table_module.'_parent_id'])) 
				{
					$filterCriteria[$this->table_module.'_parent_name'] = $filterCriteria[$this->table_module.'_parent_id'];
				}
				else
				{
					if ($filterCriteria[$this->table_module.'_parent_id_opp'] == 'is_empty' || $filterCriteria[$this->table_module.'_parent_id_opp'] == 'is_not_empty') 
					{
						$filterCriteria[$this->table_module.'_parent_name'] = '';
					}
				}
				unset($filterCriteria[$this->table_module.'_parent_id_opp']);
				unset($filterCriteria[$this->table_module.'_parent_id']);
			}
		}

		if ( !empty($filterCriteria['deals_serial_id_opp']) ) 
		{
			if ($filterCriteria['deals_serial_id_opp'] != 'is' && $filterCriteria['deals_serial_id_opp'] != 'isnt') 
			{
				$filterCriteria['deals_name_opp'] = $filterCriteria['deals_serial_id_opp'];

				if (!empty($filterCriteria['deals_serial_id'])) 
				{
					$filterCriteria['deals_name'] = $filterCriteria['deals_serial_id'];
				}
				else
				{
					if ($filterCriteria['deals_serial_id_opp'] == 'is_empty' || $filterCriteria['deals_serial_id_opp'] == 'is_not_empty') 
					{
						$filterCriteria['deals_name'] = '';
					}
				}
				unset($filterCriteria['deals_serial_id_opp']);
				unset($filterCriteria['deals_serial_id']);
			}
		}

		if ( !empty($filterCriteria['tickets_serial_id_opp']) ) 
		{
			if ($filterCriteria['tickets_serial_id_opp'] != 'is' && $filterCriteria['tickets_serial_id_opp'] != 'isnt') 
			{
				$filterCriteria['tickets_name_opp'] = $filterCriteria['tickets_serial_id_opp'];

				if (!empty($filterCriteria['tickets_serial_id_opp'])) 
				{
					$filterCriteria['tickets_name'] = $filterCriteria['tickets_serial_id'];
				}
				else
				{
					if ($filterCriteria['tickets_serial_id_opp'] == 'is_empty' || $filterCriteria['tickets_serial_id_opp'] == 'is_not_empty') 
					{
						$filterCriteria['tickets_name'] = '';
					}
				}
				unset($filterCriteria['tickets_serial_id_opp']);
				unset($filterCriteria['tickets_serial_id']);
			}
		}

		if ( !empty($filterCriteria['projects_serial_id_opp']) ) 
		{
			if ($filterCriteria['projects_serial_id_opp'] != 'is' && $filterCriteria['projects_serial_id_opp'] != 'isnt') 
			{
				$filterCriteria['projects_name_opp'] = $filterCriteria['projects_serial_id_opp'];

				if (!empty($filterCriteria['projects_serial_id_opp'])) 
				{
					$filterCriteria['projects_name'] = $filterCriteria['projects_serial_id'];
				}
				else
				{
					if ($filterCriteria['projects_serial_id_opp'] == 'is_empty' || $filterCriteria['projects_serial_id_opp'] == 'is_not_empty') 
					{
						$filterCriteria['projects_name'] = '';
					}
				}
				unset($filterCriteria['projects_serial_id_opp']);
				unset($filterCriteria['projects_serial_id']);
			}
		}

		if ( !empty($filterCriteria['issue_serial_id_opp']) ) 
		{
			if ($filterCriteria['issue_serial_id_opp'] != 'is' && $filterCriteria['issue_serial_id_opp'] != 'isnt') 
			{
				$filterCriteria['issue_name_opp'] = $filterCriteria['issue_serial_id_opp'];

				if (!empty($filterCriteria['issue_serial_id_opp'])) 
				{
					$filterCriteria['issue_name'] = $filterCriteria['issue_serial_id'];
				}
				else
				{
					if ($filterCriteria['issue_serial_id_opp'] == 'is_empty' || $filterCriteria['issue_serial_id_opp'] == 'is_not_empty') 
					{
						$filterCriteria['issue_name'] = '';
					}
				}
				unset($filterCriteria['issue_serial_id_opp']);
				unset($filterCriteria['issue_serial_id']);
			}
		}
		
		$filterJson = buildSearchJson($filterCriteria, $criteria['company_id'], $this->table_module);

		# DATA ROLES
		$roles 							= $sys->get_roles($this->table_module, $criteria['company_id'], $criteria['users_id']); 

		$myRecordOnlyCheck 	= $this->myRecordOnlyCheck($criteria['company_id'], $criteria['users_id']); //if filter view " (You) " Checked
		
		if ( !empty($input[$this->table_module.'_parent_id_opp']) ) 
		{
			if ($input[$this->table_module.'_parent_id_opp'] != 'is' && $input[$this->table_module.'_parent_id_opp'] != 'isnt') 
			{
				$input[$this->table_module.'_parent_name_opp'] = $input[$this->table_module.'_parent_id_opp'];

				if (!empty($input[$this->table_module.'_parent_id'])) 
				{
					$input[$this->table_module.'_parent_name'] = $input[$this->table_module.'_parent_id'];
				}
				else
				{
					if ($input[$this->table_module.'_parent_id_opp'] == 'is_empty' || $input[$this->table_module.'_parent_id_opp'] == 'is_not_empty') 
					{
						$input[$this->table_module.'_parent_name'] = '';
					}
				}
				unset($input[$this->table_module.'_parent_id_opp']);
				unset($input[$this->table_module.'_parent_id']);
			}
		}
		
		if ( !empty($input['deals_serial_id_opp']) ) 
		{
			if ($input['deals_serial_id_opp'] != 'is' && $input['deals_serial_id_opp'] != 'isnt') 
			{
				$input['deals_name_opp'] = $input['deals_serial_id_opp'];

				if (!empty($input['deals_serial_id'])) 
				{
					$input['deals_name'] = $input['deals_serial_id'];
				}
				else
				{
					if ($input['deals_serial_id_opp'] == 'is_empty' || $input['deals_serial_id_opp'] == 'is_not_empty') 
					{
						$input['deals_name'] = '';
					}
				}
				unset($input['deals_serial_id_opp']);
				unset($input['deals_serial_id']);
			}
		}

		if ( !empty($input['tickets_serial_id_opp']) ) 
		{
			if ($input['tickets_serial_id_opp'] != 'is' && $input['tickets_serial_id_opp'] != 'isnt') 
			{
				$input['tickets_name_opp'] = $input['tickets_serial_id_opp'];

				if (!empty($input['tickets_serial_id'])) 
				{
					$input['tickets_name'] = $input['tickets_serial_id'];
				}
				else
				{
					if ($input['tickets_serial_id_opp'] == 'is_empty' || $input['tickets_serial_id_opp'] == 'is_not_empty') 
					{
						$input['tickets_name'] = '';
					}
				}
				unset($input['tickets_serial_id_opp']);
				unset($input['tickets_serial_id']);
			}
		}

		if ( !empty($input['projects_serial_id_opp']) ) 
		{
			if ($input['projects_serial_id_opp'] != 'is' && $input['projects_serial_id_opp'] != 'isnt') 
			{
				$input['projects_name_opp'] = $input['projects_serial_id_opp'];

				if (!empty($input['projects_serial_id'])) 
				{
					$input['projects_name'] = $input['projects_serial_id'];
				}
				else
				{
					if ($input['projects_serial_id_opp'] == 'is_empty' || $input['projects_serial_id_opp'] == 'is_not_empty') 
					{
						$input['projects_name'] = '';
					}
				}
				unset($input['projects_serial_id_opp']);
				unset($input['projects_serial_id']);
			}
		}

		if ( !empty($input['issue_serial_id_opp'])) 
		{
			if ($input['issue_serial_id_opp'] != 'is' && $input['issue_serial_id_opp'] != 'isnt') 
			{
				$input['issue_name_opp'] = $input['issue_serial_id_opp'];

				if (!empty($input['issue_serial_id'])) 
				{
					$input['issue_name'] = $input['issue_serial_id'];
				}
				else
				{
					if ($input['issue_serial_id_opp'] == 'is_empty' || $input['issue_serial_id_opp'] == 'is_not_empty') 
					{
						$input['issue_name'] = '';
					}
				}
				unset($input['issue_serial_id_opp']);
				unset($input['issue_serial_id']);
			}
		}

		$searchJson = buildSearchJson($input, $criteria['company_id'], $this->table_module);

		if (countCustom($roles['contents']) > 0 && $myRecordOnlyCheck === FALSE) 
		{
			//$roles['contents'] is container member inside roles by users_id, example : team view then $roles['contents'] = array('11', '12', '13') 
			//if member != empty , then running this block
			//more detail about $roles, please check get_roles()
			$searchJson['filter'][] = [
				'terms' => [
					$this->table_module.'_owner' => $roles['contents']
				]
			];
			// $filter['terms'][$field_name] = $c_keyword;
		}


		if ($myRecordOnlyCheck === TRUE)
	  {
	  	// if user choosen filter "You", then owner by sesson users_id login
			$searchJson['must'][] = [
				'match' => [
					$this->table_module.'_owner' => $criteria['users_id']
				]
			];
		}

		$searchJson['must'][] = [
			'match' => [
				'company_id' => $criteria['company_id']
			]
		];

		$searchJson['must'][] = [
			'match' => [
				'deleted' => 0
			]
		];

		if (!empty($filterJson)) 
		{			
			if (!empty($filterJson['must'])) 
			{
				foreach ($filterJson['must'] as $key => $value) 
				{
					$searchJson['must'][] = $value;
				}	
			}

			if (!empty($filterJson['must_not'])) 
			{
				foreach ($filterJson['must_not'] as $key => $value_not) 
				{
					$searchJson['must_not'][] = $value_not;
				}	
			}

			if (!empty($filterJson['filter'])) 
			{
				foreach ($filterJson['filter'] as $key => $value_filter) 
				{
					$searchJson['filter'][] = $value_filter;
				}	
			}
		}

		$from = 0;
		if (isset($input['page'])) 
		{
			$from = ($input['page'] == 1) ? 0 : ($input['page'] - 1) * $criteria['data_per_page'];
		}

		$params['from'] = $from;
		$params['size'] = $criteria['data_per_page'];
		$params['query']['bool'] = $searchJson;

		$elastic_api = Config('setting.elastic_api_uat');
		$elastic_auth = Config('setting.elastic_auth_uat');
		if (app()->environment('local')) 
		{
			$elastic_api = Config('setting.elastic_api_local');
			$elastic_auth = Config('setting.elastic_auth_local');
		}

		$order_by = str_replace($this->table_module.'.', '', $criteria['order_by']);

		$order_by_name = [
			$this->table_module.'_owner', 
			'created_by', 
			'modified_by',
			'contacts_organization'
		];

		if (in_array($order_by, $order_by_name))
		{
			$order_by .= '_name';
		}

		$curl_order = curl_init();

		curl_setopt_array($curl_order, array(
		  CURLOPT_URL => $elastic_api.'/'.$this->table_module.'_'.$criteria['company_id'].'/_mapping/field/'.$order_by,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'GET',
		  CURLOPT_HTTPHEADER => array(
		    'Authorization: '.$elastic_auth
		  ),
		));

		$response_order = curl_exec($curl_order);
		$responses_order = json_decode($response_order, true);
		
		if (!empty($responses_order[$this->table_module.'_'.$criteria['company_id']]['mappings'])) 
		{
			$type_fields = $responses_order[$this->table_module.'_'.$criteria['company_id']]['mappings'][$order_by]['mapping'][$order_by]['type'];
			
			if ($type_fields == 'text') 
			{
				$order_by .= '.keyword';
			}
		}

		$params['sort'] = [
			$order_by => $criteria['type_sort']
		];

		curl_close($curl_order);

		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => $elastic_api.'/'.$this->table_module.'_'.$criteria['company_id'].'/_search',
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'GET',
		  CURLOPT_POSTFIELDS =>json_encode($params),
		  CURLOPT_HTTPHEADER => array(
		    'Content-Type: application/json',
		    'Authorization: '.$elastic_auth
		  ),
		));

		$params_count = $params;
		unset($params_count['from']);
		unset($params_count['sort']);
		unset($params_count['size']);

		$curl_count = curl_init();
		curl_setopt_array($curl_count, array(
		  CURLOPT_URL => $elastic_api.'/'.$this->table_module.'_'.$criteria['company_id'].'/_count',
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'GET',
		  CURLOPT_POSTFIELDS =>json_encode($params_count),
		  CURLOPT_HTTPHEADER => array(
		    'Content-Type: application/json',
		    'Authorization: '.$elastic_auth
		  ),
		));

		$response = curl_exec($curl);
		$response_count = curl_exec($curl_count);
	
		curl_close($curl);
		curl_close($curl_count);

		$responses = json_decode($response, true);
		$responses_count = json_decode($response_count, true);

		if (!empty($responses['error'])) 
		{
			if ($responses['status'] == 400) 
			{
				Log::info('###################################################################');
				Log::info('date => '. date('Y-m-d H:i:s', strtotime('+7 Hours')));
				Log::info('start elastic_api '.$elastic_api.'/'.$this->table_module.'_'.$criteria['company_id'].'/_search');
				Log::info('error', $responses);
				Log::info('###################################################################');
				$_source = array(
					'total' => 0,
					'serial_id' => array(),
				);
			}
		}

		$_source = array(
			'total' => 0,
			'serial_id' => array(),
			'_scroll_id' => array(),
		);
		if (!empty($responses['hits'])) 
		{
			if ($responses['hits']['total']['value'] > 0) 
			{
				$hits = $responses['hits']['hits'];
				$_source['total'] = 0;
				if (!empty($responses_count['count'])) 
				{
					$_source['total'] = $responses_count['count'];
				}

				$_source['serial_id'] = array_column( array_column($hits, '_source'), $this->table_module.'_serial_id');
			}
		}

		return $_source;
	}

	public function listDataIn($criteria=array(), $fields=array(), $input=array(), $data_roles=TRUE, $data_filter=TRUE)
	{
		$sys = new sys();

		# DEFINED VARIABLE
		$listFieldsCustom 		= $fields['listFieldsCustom']; // Get Fields Custom
		$query_count 					= 0; // count query : default
		$fieldsTeamsOwners 		="";
		# END

		# LIST CORE FIELDS AND CUSTOM FIELDS (MERGE)
		$fieldsName 			= $this->select_fieldsName($fields); // list fields in core field and custom fields. 
		# END
		# CHANGE OWNER_ID TO OWNER_NAME
		$checkOwner 	= in_array($this->table_module.'.'.$this->table_module.'_owner', $fieldsName); // check if owner available in $fieldsName
		if($checkOwner === TRUE)
		{
			$fieldsName 	= array_merge($fieldsName, array('users.name as '.$this->table_module.'_owner')); 
			$fieldsTeamsOwners = ", ( SELECT Group_concat(users_teams.teams_name separator '|') from `users_teams` left join `users_teams_map` as `utm` on `utm`.`teams_serial_id` = `users_teams`.`teams_serial_id` where `users_teams`.`company_id` = ".$criteria['company_id']." and `users_teams`.`deleted` = 0 and `utm`.`users_id` = ".$this->table_module."_owner ) as owner_teams_name";
		}
		$criteria['order_by'] = ($criteria['order_by'] == $this->table_module."_owner") ? "users.name" : $criteria['order_by']; // if order owner, then order by users.name ASC/DESC
		# END

		# CHANGE CREATED_ID TO CREATED_NAME
		$checkCreated 	= in_array($this->table_module.'.created_by', $fieldsName); // check if created_by available in $fieldsName
		if($checkCreated === TRUE)  
		{
			$fieldsName 	= array_merge($fieldsName, array('users_created.name as created_by')); 
		}
		$criteria['order_by'] = ($criteria['order_by'] == "created_by") ? "users_created.name" : $criteria['order_by']; // if order created_by, then order by users_created.name ASC/DESC
		# END

		# CHANGE MODIFIED_ID TO MODIFIED_NAME
		$checkModified 	= in_array($this->table_module.'.modified_by', $fieldsName); // check if modified_by available in $fieldsName
		if($checkModified === TRUE)  
		{
			$fieldsName 	= array_merge($fieldsName, array('users_modified.name as modified_by')); 
		}
		$criteria['order_by'] = ($criteria['order_by'] == "modified_by") ? "users_modified.name" : $criteria['order_by']; // if order modified_by, then order by users_modified.name ASC/DESC
		# END

		# CHANGE DEALS_SERIAL_ID TO DEALS_UUID and DEALS_NAME  
		$checkDealSerialId 	= in_array($this->table_module.'.deals_serial_id', $fieldsName); // check if owner available in $fieldsName
		if($checkDealSerialId === TRUE)  
		{
			$fieldsName 	= array_merge($fieldsName, array('deals.deals_uuid', 'deals.deals_name')); 
		} 
		# END 

		# CHANGE PROJECTS_SERIAL_ID TO PROJECTS_UUID and PROJECTS_NAME  
		$checkProjectsSerialId 	= in_array($this->table_module.'.projects_serial_id', $fieldsName); // check if projects available in $fieldsName
		if($checkProjectsSerialId === TRUE)  
		{
			$fieldsName 	= array_merge($fieldsName, array('projects.projects_uuid', 'projects.projects_name')); 
		} 
		# END 

		# CHANGE ISSUE_SERIAL_ID TO ISSUE_UUID and ISSUE_NAME  
		$checkIssueSerialId 	= in_array($this->table_module.'.issue_serial_id', $fieldsName); // check if issue available in $fieldsName
		if($checkIssueSerialId === TRUE)  
		{
			$fieldsName 	= array_merge($fieldsName, array('issue.issue_uuid', 'issue.issue_name')); 
		} 
		# END
		
		# CHANGE TICKETS_SERIAL_ID TO TICKETS_UUID and TICKETS_NAME  
		$checkTicketsSerialId 	= in_array($this->table_module.'.tickets_serial_id', $fieldsName); // check if tickets available in $fieldsName
		if($checkTicketsSerialId === TRUE)  
		{
			$fieldsName 	= array_merge($fieldsName, array('tickets.tickets_uuid', 'tickets.tickets_name')); 
		} 
		# END
		# 

		# CONVERT TO ROW QUERY FORMAT
		$fieldsNameConvert	= $this->convertFieldsName($fieldsName);
		# END 

		# SELECT QUERY DYNAMIC BY $fieldsName
		$b = 'b';
		$leadsConvert = strtotime("now");
		$fieldsNameSysRel = "sys_rel.rel_to_module as ".$this->table_module."_parent_type, sys_rel.rel_to_id as ".$this->table_module."_parent_id,
											(
												CASE   
													WHEN sys_rel.rel_to_module = 'leads' THEN 
														CASE 
															WHEN ( SELECT CONCAT(COALESCE(leads.leads_first_name, ''),' ', COALESCE(leads.leads_last_name, '')) FROM leads WHERE leads.leads_serial_id = sys_rel.rel_to_id AND leads.leads_convert_id != 0 LIMIT 1) IS NULL
															THEN ( SELECT CONCAT(COALESCE(leads.leads_first_name, ''),' ', COALESCE(leads.leads_last_name, '')) FROM leads WHERE leads.leads_serial_id = sys_rel.rel_to_id LIMIT 1)
															ELSE ".$leadsConvert."
														END
													WHEN sys_rel.rel_to_module = 'contacts' THEN ( SELECT CONCAT(COALESCE(contacts.contacts_first_name, ''),' ', COALESCE(contacts.contacts_last_name, '')) FROM contacts WHERE contacts.contacts_serial_id = sys_rel.rel_to_id LIMIT 1) 
													WHEN sys_rel.rel_to_module = 'org'  THEN ( SELECT org_name FROM org WHERE org.org_serial_id = sys_rel.rel_to_id LIMIT 1) 
													WHEN sys_rel.rel_to_module = 'projects' THEN ( SELECT projects_name FROM projects WHERE projects.projects_serial_id = sys_rel.rel_to_id LIMIT 1 )
													WHEN sys_rel.rel_to_module is NULL THEN ''
												END
												) as ".$this->table_module."_parent_name";

		$query = $this->model_class::select(DB::raw($fieldsNameConvert.",".$fieldsNameSysRel.$fieldsTeamsOwners));
		# END 

		// $query->having($this->table_module."_parent_name",'!=',$leadsConvert);
		# LEFT JOIN WITH SYS REL
		$query->leftjoin('sys_rel', function($join) use ($criteria)
		        { 
		            $join->on('sys_rel.rel_from_id', '=', $this->table_module.'.'.$this->table_module.'_serial_id')
		            ->where('sys_rel.rel_from_module', '=', $this->table_module);
		        });
		if ($criteria['order_by'] == $this->table_module."_parent_id" || $criteria['order_by'] == $this->table_module . "." . $this->table_module."_parent_id")
		{
			$criteria['order_by'] 	= $this->table_module."_parent_id";
		}
		# END 

		# LEFT JOIN WITH CUSTOM FIELDS
		$temp_alias = array();
		if (countCustom($listFieldsCustom) > 0) // 	if fields custom available
		{

			$query->leftjoin($this->table_module.'_custom_values_ as mcv', function($join) use ($criteria)
							{
								$join->on('mcv.'.$this->table_module.'_serial_id', '=', $this->table_module.'.'.$this->table_module.'_serial_id')
								->where('mcv.company_id', '=', $criteria['company_id']);
							});

		}
			
		# IF OWNER AVAILABLE IN $fieldsName
		if ($checkOwner === TRUE || $data_filter == TRUE || (isset($_GET['subaction']) && $_GET['subaction'] == "search"))
		{
			//run if owner show in listing OR 
			//run if data_filter TRUE OR
			//run if search feature true
			$query->leftjoin('users', 'users.id', '=', $this->table_module.'.'.$this->table_module.'_owner');
		}

		# IF CREATED AVAILABLE IN $fieldsName
		if ($checkCreated === TRUE || (isset($_GET['subaction']) && $_GET['subaction'] == "search"))
		{
			//run if created show in listing OR 
			//run if search feature true
			$query->leftjoin('users as users_created', 'users_created.id', '=', $this->table_module.'.created_by');
		}

		# IF MODIFIED AVAILABLE IN $fieldsName
		if ($checkModified === TRUE || (isset($_GET['subaction']) && $_GET['subaction'] == "search"))
		{
			//run if modified show in listing OR 
			//run if search feature true
			$query->leftjoin('users as users_modified', 'users_modified.id', '=', $this->table_module.'.modified_by');
		}

		# IF DEALS_SERIAL_ID AVAILABLE IN $fieldsName
		$checkDealSerialId 	= in_array($this->table_module.'.deals_serial_id', $fieldsName); // check if owner available in $fieldsName
		if($checkDealSerialId === TRUE)  
		{
			$query->leftjoin('deals', 'deals.deals_serial_id', '=', $this->table_module.'.deals_serial_id');
		}

		# IF PROJECTS_SERIAL_ID AVAILABLE IN $fieldsName
		$checkProjectsSerialId 	= in_array($this->table_module.'.projects_serial_id', $fieldsName); // check if projects available in $fieldsName
		if($checkProjectsSerialId === TRUE)  
		{
			$query->leftjoin('projects', 'projects.projects_serial_id', '=', $this->table_module.'.projects_serial_id');
		}

		# IF ISSUE_SERIAL_ID AVAILABLE IN $fieldsName
		$checkIssueSerialId 	= in_array($this->table_module.'.issue_serial_id', $fieldsName); // check if issue available in $fieldsName
		if($checkIssueSerialId === TRUE)  
		{
			$query->leftjoin('issue', 'issue.issue_serial_id', '=', $this->table_module.'.issue_serial_id');
		}

		# IF TICKETS_SERIAL_ID AVAILABLE IN $fieldsName
		$checkTicketsSerialId 	= in_array($this->table_module.'.tickets_serial_id', $fieldsName); // check if issue available in $fieldsName
		if($checkTicketsSerialId === TRUE)  
		{
			$query->leftjoin('tickets', 'tickets.tickets_serial_id', '=', $this->table_module.'.tickets_serial_id');
		}			

		$query->whereIn($this->table_module.'.'.$this->table_module.'_serial_id', $criteria['serial_id']);
		$query->where($this->table_module.'.company_id', '=', $criteria['company_id']);
		$query->where($this->table_module.'.deleted', '=', Config('setting.NOT_DELETED'));
		# END

		# PAGINATION MANUALLY
		$page = 1;
		if (isset($input['page'])) 
		{
			$page 			= (!empty($input['page'])) ? $input['page'] : 1;
		}
		$perPage 			= $criteria['data_per_page'];
		$skip 				= ($page - 1) * $perPage;

		$query->orderBy($criteria['order_by'], $criteria['type_sort']);
		$items 				= $query->get();
		$items 				= json_decode(json_encode($items), True);

		# ADD BY GILANG PRATAMA
		# SATURDAY, 09 SEPTEMBER 2019
		# TO GET RECORDING CLICK TO CALL
		if (!isEmpty($items)) 
		{
			$items = $this->getListRecord($items, $criteria['company_id']);
		}

		// $result 	= new $this->paginate_manually($items, $items_count, $perPage, $page);
		// $result->setPath($this->table_module);
		// $result->appends($input);

		$last_page = ceil($criteria['total'] / $perPage);

		$result = [
			'total_data' 						=> $criteria['total'],
			"limit"							=> (int) $perPage,
			"prev_page"					=> ($page == 1) ? 1 : $page - 1, # next page
			"current_page"			=> (int) $page, # next page
			"next_page"					=> ($last_page <= $page) ? '' : $page + 1, # next page
			"last_page"					=> $last_page, # next page
			"total_page"				=> $last_page, # next page
			'data' 							=> $items,

		];

		return $result;
	}

	public function last_workflow_detail($data=array(), $company_id=0)
	{
		$workflow_id = isset($data[$this->table_module.'_last_workflow']) ? $data[$this->table_module.'_last_workflow'] : '';

		$sys = new sys;
		$data[$this->table_module.'_last_workflow_date'] = $sys->set_datetime($data[$this->table_module.'_last_workflow_date']);
		
		$workflow = $this->model_workflow_class::where('workflow_serial_id', '=', $workflow_id)
																						->where('company_id', '=', $company_id)
																						->first();

		if (countCustom($workflow) > 0) 
		{
			$workflow = $workflow->toArray();
			$data[$this->table_module.'_last_workflow_uuid'] = $workflow['workflow_uuid'];
			$data[$this->table_module.'_last_workflow_name'] = $workflow['workflow_name'];
		}
		else
		{
			$data[$this->table_module.'_last_workflow_uuid'] = '';
			$data[$this->table_module.'_last_workflow_name'] = '';
		}
	
		return $data;
	}

	public function getDataByIdForElastic($serial_id='', $company_id=0)
	{
		$result = array();
		
		$select[] = $this->table_module.'.*';
		for ($i = 1; $i <= 100; $i++) 
		{ 
			$select[] = $this->table_module."_custom_values_".$i;
		}

		$leadsConvert = strtotime("now");
		$select[] = "sys_rel.rel_to_module as ".$this->table_module."_parent_type, sys_rel.rel_to_id as ".$this->table_module."_parent_id,
											(
												CASE   
													WHEN sys_rel.rel_to_module = 'leads' THEN 
														CASE 
															WHEN ( SELECT CONCAT(COALESCE(leads.leads_first_name, ''),' ', COALESCE(leads.leads_last_name, '')) FROM leads WHERE leads.leads_serial_id = sys_rel.rel_to_id AND leads.leads_convert_id != 0 LIMIT 1) IS NULL
															THEN ( SELECT CONCAT(COALESCE(leads.leads_first_name, ''),' ', COALESCE(leads.leads_last_name, '')) FROM leads WHERE leads.leads_serial_id = sys_rel.rel_to_id LIMIT 1)
															ELSE ".$leadsConvert."
														END
													WHEN sys_rel.rel_to_module = 'contacts' THEN ( SELECT CONCAT(COALESCE(contacts.contacts_first_name, ''),' ', COALESCE(contacts.contacts_last_name, '')) FROM contacts WHERE contacts.contacts_serial_id = sys_rel.rel_to_id LIMIT 1) 
													WHEN sys_rel.rel_to_module = 'org'  THEN ( SELECT org_name FROM org WHERE org.org_serial_id = sys_rel.rel_to_id LIMIT 1) 
													WHEN sys_rel.rel_to_module = 'projects' THEN ( SELECT projects_name FROM projects WHERE projects.projects_serial_id = sys_rel.rel_to_id LIMIT 1 )
												END
												) as ".$this->table_module."_parent_name";

		$select[] = 'users.name as '.$this->table_module.'_owner_name';
		$select[] = 'users_created.name as created_by_name';
		$select[] = 'users_modified.name as modified_by_name';

		$custom = $this->model_custom_fields_class::where('company_id', '=', $company_id)
											->where($this->table_module.'_custom_fields_input_type','=','person')
											->get()
											->toArray();

		if (!empty($custom)) 
		{
			foreach ($custom as $key => $value) 
			{
				$custom_str = '(CASE ';
				$custom_str .= " WHEN sys_rel.rel_to_module = 'contacts' THEN ( SELECT CONCAT(COALESCE(contacts.contacts_first_name, ''),' ', COALESCE(contacts.contacts_last_name, ''), ' ##### ', COALESCE(contacts.contacts_serial_id, '')) FROM contacts WHERE contacts.contacts_serial_id = ".$value[$this->table_module.'_custom_values_maps']." AND company_id = ".$company_id." LIMIT 1)
				WHEN sys_rel.rel_to_module = 'leads' THEN ( SELECT CONCAT(COALESCE(leads.leads_first_name, ''),' ', COALESCE(leads.leads_last_name, ''), ' ##### ', COALESCE(leads.leads_serial_id, '')) FROM leads WHERE leads.leads_serial_id = ".$value[$this->table_module.'_custom_values_maps']." AND company_id = ".$company_id." LIMIT 1)
				WHEN sys_rel.rel_to_module = 'org' THEN ( SELECT CONCAT(COALESCE(org.org_name, ''), ' ##### ', COALESCE(org.org_serial_id, '')) FROM org WHERE org.org_serial_id = ".$value[$this->table_module.'_custom_values_maps']." AND company_id = ".$company_id." LIMIT 1)
				ELSE ".$value[$this->table_module.'_custom_values_maps'];
				$custom_str .= " END) as ".$value[$this->table_module.'_custom_values_maps'];
				$select[] = $custom_str;
			}
		}

		$select_raw = implode(',', $select);

		$data = $this->model_class::select(DB::raw($select_raw))
									->leftjoin($this->table_module.'_custom_values_ as mcv', function($join) use ($company_id)
									{
										$join->on('mcv.'.$this->table_module.'_serial_id', '=', $this->table_module.'.'.$this->table_module.'_serial_id')
										->where('mcv.company_id', '=', $company_id);
									})
									->leftjoin('sys_rel', function($join)
					        { 
					            $join->on('sys_rel.rel_from_id', '=', $this->table_module.'.'.$this->table_module.'_serial_id')
					            ->where('sys_rel.rel_from_module', '=', $this->table_module);
					        })
									->where($this->table_module.'.deleted', '=', Config('setting.NOT_DELETED'))
					        ->leftjoin('users', 'users.id', '=', $this->table_module.'.'.$this->table_module.'_owner')
					        ->leftjoin('users as users_created', 'users_created.id', '=', $this->table_module.'.created_by')
					        ->leftjoin('users as users_modified', 'users_modified.id', '=', $this->table_module.'.modified_by')
					        ->where($this->table_module.'.'.$this->table_module.'_serial_id', '=', $serial_id)
					        ->where($this->table_module.'.company_id', '=', $company_id)
									->first();	

		if (!isEmpty($data)) 
		{
			$result = $data->toArray();
		}

		return $result;
	}

	public function getLeaderTeam($user_id=0, $company_id=0){
		$result 	= array();
		$team_id 	= array();

		$teams 		= $this->model_teams_map_class::select('teams.*')
																						->leftjoin('users_teams as teams', 'teams.teams_serial_id', '=', 'users_teams_map.teams_serial_id')
																						->where('users_teams_map.users_id', '=', $user_id)
																						->where('users_teams_map.company_id', '=', $company_id)
																						->where('teams.company_id', '=', $company_id)
																						->where('teams.teams_status', '=', Config('setting.ACTIVE'))
																						->where('teams.deleted', '=', Config('setting.NOT_DELETED'))
																						->get()->toArray();

		foreach($teams as $key => $value){
			$team_id[$key] 	= $value['teams_serial_id'];
		}

		if(!isEmpty($team_id)){
			$leader		= array();

			$leader 	= $this->model_teams_map_class::select('users_id')
																							->whereIn('teams_serial_id', $team_id)
																							->where('company_id', '=', $company_id)
																							->where('teams_map_status', '=', Config('setting.ACTIVE'))
																							->where('teams_map_leader','=', Config('setting.teams_map_leader'))
																							->get()->toArray();

			if(!isEmpty($leader)){
				$i = 0;
				foreach($leader as $keyLead => $valueLead){
					if($valueLead['users_id'] !== $user_id){
						$result[$i]	= $valueLead['users_id'];

						$i++;
					}
				}
			}
		}
		return $result;
	}

	public function saveFieldsCondition($input , $serial_id, $typefields , $company_id=0 , $users_id=0){

		$data = $this->model_fields_option_class::where('calls_fields_serial_id' , '=' , $serial_id)
				->where('calls_fields_type' , '=' , $typefields)
				->where('company_id' , '=' , $company_id)->first();
		if (!isEmpty($data)) {

			$update[$this->table_module.'_fields_condition_action_type'] 		= $input["fields_type_action"];
			$update[$this->table_module.'_fields_condition_value'] 					= $input["fields_value"];		
			$update[$this->table_module.'_fields_condition_action_fields'] 	= json_encode($input["fields_options"], true);
			$update['date_modified']																					= date('Y-m-d H:i:s');
			$update['modified_by']																						= $users_id;

			$this->model_fields_option_class::where('calls_fields_serial_id' , '=' , $serial_id)
				->where('calls_fields_type' , '=' , $typefields)
				->where('company_id' , '=' , $company_id)->update($update);

		}else{
				$save[$this->table_module.'_fields_condition_uuid'] 					= $this->example_uuid();
				$save[$this->table_module.'_fields_serial_id'] 								= $serial_id;
				$save[$this->table_module.'_fields_type']											= $input["fields_type"];
				$save[$this->table_module.'_fields_condition_action_type'] 		= $input["fields_type_action"];
				$save[$this->table_module.'_fields_condition_value'] 					= $input["fields_value"];
				$save[$this->table_module.'_fields_condition_action_fields'] 	= json_encode($input["fields_options"], true);
				$save['company_id']																						= $company_id;
				$save['date_created']																					= date('Y-m-d H:i:s');
				$save['created_by']																						= $users_id;

				$this->model_fields_option_class::create($save);
		}
	
	}

	public function getFieldsCondition($serial_id , $company_id=0 , $typefields){
		// type fields 0 for core , 1 for custom fields
		$result = array();
		$data = $this->model_fields_option_class::select(DB::raw('calls_fields_condition_uuid,calls_fields_serial_id, calls_fields_type, 
							calls_fields_condition_value, calls_fields_condition_action_type ,calls_fields_condition_action_type ,calls_fields_condition_action_fields'))
							->where($this->table_module.'_fields_serial_id' , '=' , $serial_id)
							->where($this->table_module.'_fields_type' , '=' , $typefields)
							->where('company_id' , '=' , $company_id)
							->first();
		if (countCustom($data) > 0) {
			$result 	= $data;
		}
		return $result;
	}

	public function deleteFieldsCondition($uuid, $company_id){
		$update_data 	= array();
		
		$data 				= false;

		$query =  $this->model_fields_option_class::where($this->table_module.'_fields_condition_uuid' , '=' , $uuid)
				->where('company_id' , '=' , $company_id)->first();

		if ( countCustom($query) > 0 ){
			$update_data = $query;
		}
			$data = $this->model_fields_option_class::where($this->table_module.'_fields_condition_uuid' , '=' , $uuid)
				->where('company_id' , '=' , $company_id)->delete();
		return $update_data;
	}

	public function getAllFieldsCondition($result=array(),$company_id = 0){
		foreach ($result as $key => $value) 
			{
				if ( isset($value[$this->table_module.'_custom_fields_serial_id']) )
				{
					$id = $value[$this->table_module.'_custom_fields_serial_id'];
					$query = $this->model_fields_option_class::select(DB::raw('calls_fields_condition_uuid,calls_fields_serial_id, calls_fields_type, 
						calls_fields_condition_value, calls_fields_condition_action_type ,calls_fields_condition_action_type ,calls_fields_condition_action_fields'))
						->where('company_id' , '=' , $company_id)
						->where($this->table_module.'_fields_type' , '=' , 1)
						->where($this->table_module.'_fields_serial_id' , '=' , $id)->first();
					
					if (!isEmpty($query))
					{						
						$result[$key]["condition"] = $query->toArray();
					}
				}
				else
				{
					$id = $value[$this->table_module.'_fields_serial_id'];
					$query = $this->model_fields_option_class::select(DB::raw('calls_fields_condition_uuid,calls_fields_serial_id, calls_fields_type, 
						calls_fields_condition_value, calls_fields_condition_action_type ,calls_fields_condition_action_type ,calls_fields_condition_action_fields'))
						->where('company_id' , '=' , $company_id)
						->where($this->table_module.'_fields_type' , '=' , 0)
						->where($this->table_module.'_fields_serial_id' , '=' , $id)->first();
					if (!isEmpty($query))
					{						
						$result[$key]['condition'] = $query->toArray();
					}
				}
		}
		return $result;
	}
	public function save_last_completed_activities($request=array(), $this_id=0, $company_id=0)
	{	
		$sys = new sys();

		if ( !empty($request[$this->table_module.'_parent_type']) AND !empty($request[$this->table_module.'_parent_id']) )
		{
			// check contact any have org on sysrel
			// add by gilang
			$rel_from_module = $request[$this->table_module.'_parent_type'];
			$rel_from_id		 = $request[$this->table_module.'_parent_id'];
			$rel_to_module	 = 'org';
			
			$data_org = $this->model_sys_rel_class::where('rel_from_module', '=', $rel_from_module)
																				->where('rel_from_id', '=', $rel_from_id)
																				->where('rel_to_module', '=', $rel_to_module)
																				->first();
																				
			if (countCustom($data_org) > 0){
				$customers_module = 'org';
        $customers_id     = $data_org['rel_to_id'];
        $last_module      = $this->table_module;
        $last_id          = $this_id;
        // Query last activities
	      $sys->sys_api_last_completed_activities($customers_module, $customers_id, $last_module, $last_id, $company_id);
			}
			// end check contact any have org on sysrel
			
			if (isset($request['org_serial_id'])) {
				if ($request['org_serial_id'] != $request[$this->table_module.'_parent_id']) {
					$customers_module = 'org';
					$customers_id 		= $request['org_serial_id'];
					$last_module 			= $this->table_module;
					$last_id 					= $this_id;
					$sys->sys_api_last_completed_activities($customers_module, $customers_id, $last_module, $last_id, $company_id);
				}
			}
			$customers_module = $request[$this->table_module.'_parent_type'];
			$customers_id 		= $request[$this->table_module.'_parent_id'];
			$last_module 			= $this->table_module;
			$last_id 					= $this_id;
			// Query last activities
			$sys->sys_api_last_completed_activities($customers_module, $customers_id, $last_module, $last_id, $company_id);
		}

		if(!empty($request['deals_serial_id']) AND $request['deals_serial_id'] != '' AND $request['deals_serial_id'] != 0)
    {
        $id         			= $request['deals_serial_id'];
        $last_module      = $this->table_module;
        $last_id          = $this_id;
				$table						= 'deals';

				$sys->sys_api_last_completed_activities_related( $id, $last_module, $last_id, $company_id,$table);
    }

    if(!empty($request['projects_serial_id']) AND $request['projects_serial_id'] != '' AND $request['projects_serial_id'] != 0)
    {
        $id     = $request['projects_serial_id'];
        $last_module     = $this->table_module;
        $last_id         = $this_id;
				$table						= 'projects';
				
		
				$sys->sys_api_last_completed_activities_related( $id, $last_module, $last_id, $company_id,$table);
    }

    if(!empty($request['issue_serial_id']) AND $request['issue_serial_id'] != '' AND $request['issue_serial_id'] != 0)
    {
        $id       = $request['issue_serial_id'];
        $last_module    = $this->table_module;
        $last_id        = $this_id;
				$table						= 'issue';
				
		
				$sys->sys_api_last_completed_activities_related( $id, $last_module, $last_id, $company_id,$table);
    }

    if(!empty($request['tickets_serial_id']) AND $request['tickets_serial_id'] != '' AND $request['tickets_serial_id'] != 0)
    {
        $id    	= $request['tickets_serial_id'];
        $last_module    = $this->table_module;
        $last_id        = $this_id;
				$table						= 'tickets';

				$sys->sys_api_last_completed_activities_related( $id, $last_module, $last_id, $company_id,$table);
    }

		return true;
	}
	public function saveMapping($request = array() , $users_id=0, $company_id=0){

		if(countCustom($request) > 0){

			// 1. Save to table calls_import
			$save[$this->table_module.'_import_uuid'] 	= $this->example_uuid();
			$save[$this->table_module.'_import_name'] 	= $request['mapping_name'];
			$save[$this->table_module.'_import_criteria'] = $request['data'];
			$save['company_id']			= $company_id;
			$save['date_created']		= date('Y-m-d H:i:s');
			$save['created_by']			= $users_id;
			$save_import						= $this->model_import_class::create($save);

		}
	}
	public function getListMapping($company_id){
		$result = array();
		$data = $this->model_import_class::select(DB::raw('calls_import_uuid,calls_import_name,calls_import_criteria'))
							->where('deleted' , '=' , 0)
							->where('company_id' , '=' , $company_id)
							->get();
		if (countCustom($data) > 0) {
			$result 	= $data->toArray();
		}
		return $result;
	}

	public function getFieldsMapping($uuid , $company_id){

		$key_import 		= array(); // data array (key)
		$name_import 		= array(); // data array (value)
		$result = array();
		$criteria = array();

		$data = $this->model_import_class::select(DB::raw('calls_import_uuid,calls_import_criteria,calls_import_name'))
		->where('deleted' , '=' , 0)
		->where('company_id' , '=' , $company_id)
		->where('calls_import_uuid' , '=' , $uuid)
		->first();

		if (countCustom($data) > 0) {

			$result 	= $data;
			$criteria = json_decode($result[$this->table_module.'_import_criteria'], true);

			$GetIDLabel    = $this->GetForm($company_id); // get core and custom fields
			$name_corecustom    = array(); // will contain index of custom fields serial id, and name
			$field= '';
			$field_id = array();
			foreach($GetIDLabel as $key => $value)
			{
				if( isset($value[$this->table_module.'_fields_serial_id']) )
				{
					// unset($GetIDLabel[$key]);
					$name_corecustom[$key] =  $value[$this->table_module.'_fields_name'];
				}
				else
				{
					$name_corecustom[$key] =  $value[$this->table_module.'_custom_fields_name'];
				}
			}

			// unset name_corecustom yg sudah dihapus 
			foreach($criteria as $key=>$value){
				$name_import = $criteria['value'];
				$key_import = $criteria['key'];
				foreach($name_import as $key1=>$value1){
					if ( $name_import[$key1] != 'none' ) 
						{
							$trim_custom = substr($name_import[$key1], 0, 9);
							if ($trim_custom == 'custommm_') 
							{
								// unset custom fields yg tidak aktif
								$field = substr($name_import[$key1], 9);
								$cari = in_array($field, $name_corecustom);
								if($cari == FALSE){
									unset($criteria['key'][$key1]);
									unset($criteria['value'][$key1]);
								}
							}else{
								// unset core fields yg tidak aktif
								$cari = in_array($name_import[$key1], $name_corecustom);
								if($cari == FALSE){
									unset($criteria['key'][$key1]);
									unset($criteria['value'][$key1]);
								}
							}
						}
				}
			}

			$result[$this->table_module.'_import_criteria'] = $criteria;
		}
		return $result;
	}

	public function deleteMapping($uuid, $company_id){

		$delete['deleted'] 	= Config('setting.DELETED'); // deleted row module_view
		$data 	= $this->model_import_class::where('calls_import_uuid', '=' , $uuid)->where('company_id' , '=' , $company_id)->update($delete);

		return TRUE;

	}

	public function GetNameOwnerById($serial_id)
	{
		// define variable
		$result = "";

		if ($serial_id != 0 OR $serial_id != null) {
			$name = $this->model_users_class::where('id', '=', $serial_id)->first();

			$result = $name['name'];
		}

		return $result;
	}
}