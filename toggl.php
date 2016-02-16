<?php
	require 'user.php';
	require 'project.php';
	require 'task.php';
		class Toggl {
			private static $wid=780236; //exPanda;
			private static $api_token = '5a72724f373291b7a801d5d492b6cd48';
			public static $ch ;
			
			public static function init(){
				self::$ch = curl_init();
				curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt(self::$ch, CURLOPT_USERPWD, self::$api_token.':api_token' );
				curl_setopt(self::$ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
				curl_setopt(self::$ch, CURLOPT_SSL_VERIFYPEER, false);
				
				
			}
			public static function setWorkspace($wid){
				self::$wid = $wid;
			}
			public static function getWorkspaces(){
				return self::$wid; //ez egyelőre nem kell, expandát adom vissza
			}
			public static function getUsers() {
				curl_setopt(self::$ch, CURLOPT_URL, 'https://www.toggl.com/api/v8/workspaces/'.self::$wid.'/users' ); //users
				$result = curl_exec(self::$ch);
				//echo curl_error($ch) .'->'. curl_errno($ch);
				$r = json_decode($result);
				$arr = array();
				foreach ($r as $row){
					$arr[] = array('id'=>$row->id, 'fullname'=>$row->fullname);//new togglUser($row->id,$row->fullname);
				}
				return $arr;
			}
			
			public static function getProjects() {
				curl_setopt(self::$ch, CURLOPT_URL, 'https://www.toggl.com/api/v8/workspaces/'.self::$wid.'/projects' ); 
				$result = curl_exec(self::$ch);
				//echo curl_error($ch) .'->'. curl_errno($ch);
				$r = json_decode($result);
				$arr = array();
				foreach ($r as $row){
					$arr[] = array('id'=>$row->id, 'name'=>$row->name);//new togglProject($row->id,$row->name);
				}
				
				return $arr;
				
			}
			public static function getTags() {
				curl_setopt(self::$ch, CURLOPT_URL, 'https://www.toggl.com/api/v8/workspaces/'.self::$wid.'/tags' ); 
				$result = curl_exec(self::$ch);
				//echo curl_error($ch) .'->'. curl_errno($ch);
				echo json_encode($result);				
				
			}
			public static function getTasks($filter){
				$params = "";
				foreach ($filter as $name => $key) {
					$params .='&'.$name.'='.$key;
				}

				$url = 'https://toggl.com/reports/api/v2/details?workspace_id='.self::$wid.$params;
				curl_setopt(self::$ch, CURLOPT_URL, $url ); //tasks				
				$result = curl_exec(self::$ch);
				//echo curl_error($ch) .'->'. curl_errno($ch);
				$r = json_decode($result);
				//var_dump($r);
				$arr = array();
				foreach ($r->data as $row){
					if (!in_array('beírva',$row->tags)){
						$search = array('ő','Ő','ű','Ű');
						$replace = array ('ö','Ö','ü','Ü');
						$row->description = str_replace($search, $replace, $row->description);
						$arr[] =  array('id'=>$row->id, 'pid'=>$row->pid,'start'=>$row->start,'dur'=>$row->dur,'description'=>$row->description);//new togglTask($row->id,$row->pid,$row->start,$row->dur, $row->description);
					}
				}
				return $arr;
			}
			
			public static function updateTask($togglPar,$filter){
				$params = "";
				$taskId = $togglPar['taskId'];
				$arr = array();
				if ($taskId!=''){
					$data = array('time_entry'=>$filter);
					$url = 'https://www.toggl.com/api/v8/time_entries/'.$taskId;
					curl_setopt(self::$ch, CURLOPT_URL, $url ); //tasks				
					curl_setopt(self::$ch, CURLOPT_POSTFIELDS, json_encode($data) ); //tasks				
					$result = curl_exec(self::$ch);
					//echo curl_error($ch) .'->'. curl_errno($ch);
					$r = json_encode($result);
					//var_dump($r);
					
					foreach ($r as $row){
						$search = array('ő','Ő','ű','Ű');
						$replace = array ('ö','Ö','ü','Ü');
						$row->description = str_replace($search, $replace, $row->description);
						$arr[] =  array('id'=>$row->id, 'pid'=>$row->pid,'start'=>$row->start,'description'=>$row->description);//new togglTask($row->id,$row->pid,$row->start,$row->dur, $row->description);
					}
				}
				return $arr;
				
			}
		}
?>