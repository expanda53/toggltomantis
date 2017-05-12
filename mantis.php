<?php

	class Mantis {
		private static $dsn="mysql:host=localhost;port=3306;dbname=pulykakakas;charset=utf8";
		private static $username = 'expanda';
		private static $password = 'abroszot624';
		private static $options = array(
			//PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
		); 

		private static $db ;
		
		static function init() {
				try {
					self::$db = new PDO(self::$dsn, self::$username, self::$password, self::$options);  			
					self::$db->exec("set names utf8");
				}
				catch (PDOException $e) {
					print "MySql Connection error!: " . $e->getMessage();
					die();
				}
		}
		
		private static function fetchAll($stmt){
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
				
		}
		private static function query($sql){
				try {
					$stmt = self::$db->query($sql);
				}
				catch (PDOException $e) {
					print "MySql Query error!: " . $e->getMessage();
					$stmt=null;
				}
			return $stmt;
		}
		
		private static function mstoHM($ms){
			$hours = "";
			$minutes = "";
			if ($ms>0) {
				$h = floor($ms / 1000 / 3600);
				$m = floor(($ms - ($h * 1000 * 3600)) / 1000 / 60);
				//felfelé kerekítés:
				
				$min5 = bcdiv($m,5)*5;
				if ($min5<$m) $min5 = $min5 + 5;
				if ($min5>=60) {$h = $h + 1;$min5=0;}

				$hours = str_pad($h,2,'0',STR_PAD_LEFT) ;
				$minutes = str_pad($min5,2,'0',STR_PAD_LEFT) ;
			}
			return $hours!=""?($hours.':'.$minutes):"";
			
		}
		private static function hmToMs($hm){
			$t = explode(":",$hm);
			$hours = $t[0];
			$minutes = $t[1];
			return ($hours * 60 * 60 * 1000) + ($minutes * 60 * 1000);

			
		}
		
		public static function getStatuses(){
			
			$sql="select value from mantis_config_table where config_id='status_enum_workflow'";
			$stmt = self::query($sql);
			$row = self::fetchAll($stmt);
			foreach ($row as $k => $v) {
				$status = unserialize(utf8_decode($v));
			}
			$stat=array();
			foreach ($status as $k => $v){
				$a=explode(',', $v);
				foreach ($a as $val){
					$c=explode(':',$val);
					$d=array($c[0]=>$c[1]);
					if (in_array($c[0],$stat)!==true) $stat[$c[0]]=$c[1];
				}
			}
			return  $stat;			
		}
		
		public static function getPartners() {
			$sql="select name , id from mantis_project_table where enabled=1 order by name";
			$stmt = self::query($sql);
			
			return self::fetchAll($stmt);
		}
		public static function getUsers() {
			$sql="(select 'Mind' as username ,-1 as id from mantis_user_table limit 1) union (select 'üres' as username ,0 as id from mantis_user_table limit 1) union (select username , id from mantis_user_table where enabled=1 and access_level>=70 order by username)";
			$stmt = self::query($sql);
			return self::fetchAll($stmt);
			
		}
		public static function getReporters($params) {
            $pid = $params['pid'];
			$sql="select username , id from mantis_user_table inner join mantis_project_user_list_table on id = user_id where enabled=1 and project_id = $pid ";
            $sql.=" union select username,id from mantis_user_table where enabled=1 and id in (6,7,8,9,11) order by username";
			$stmt = self::query($sql);
			return self::fetchAll($stmt);
			
		}        
		public static function getMonths() {
			$sql="select version from mantis_project_version_table where version not in ('X','Y') order by version desc limit 4";
			//$stmt = self::$db->query($sql);
			//return $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$stmt = self::query($sql);
			return self::fetchAll($stmt);
			
		}		
		public static function insertTask($params){
			$sessionid = uniqid();
			$uid = $params['uid'];
			$pid = $params['pid'];
            $rid = $params['rid'];
			$desc = $params['desc'];
			//echo mb_detect_encoding($desc);
			$ver = $params['month'];
			$durms = $params['durms'];
			$durhm = self::mstoHM($durms);
            $start = $params['start'];
			$status='50';
			$severity='10';
			$sql = " insert into mantis_bug_text_table (description )";
			$sql.=" values ('$desc') ";
			
			$stmt = self::query($sql);
			$btid =  self::$db->lastInsertId();
			//echo $btid = self::$db->insert_id;

			


			$sql = " insert into mantis_bug_table (fixed_in_version,summary,status,handler_id,reporter_id,project_id,last_updated,date_submitted,severity,bug_text_id,platform,os)";
			$sql.=" values ('$ver','$desc','$status','$uid','$rid','$pid',now(),'$start',$severity,$btid,'$durhm','$sessionid') ";
			
			$stmt = self::query($sql);
			$bid = self::$db->lastInsertId();
			//$btid = self::$db->insert_id;
			
			/* uj bejelentes */
			$sql = "insert into mantis_bug_history_table (date_modified,user_id,bug_id,field_name,old_value,new_value,type)";
			$sql.=" values (now(),'$uid','$bid','','','',1);";
			$stmt = self::query($sql);
			/* status */
			$sql = "insert into mantis_bug_history_table (date_modified,user_id,bug_id,field_name,old_value,new_value,type)";
			$sql.=" values (now(),'$uid','$bid','status','10','$status',0);";
			$stmt = self::query($sql);

			/* hozzarendeles */
			$sql = "insert into mantis_bug_history_table (date_modified,user_id,bug_id,field_name,old_value,new_value,type)";
			$sql.=" values (now(),'$uid','$bid','handler_id','','$uid',0);";
			$stmt = self::query($sql);
			
			/* ido */
			$sql = "insert into mantis_bug_history_table (date_modified,user_id,bug_id,field_name,old_value,new_value,type)";
			$sql.=" values (now(),'$uid','$bid','platform','','$durhm',0);";
			$stmt = self::query($sql);

			/* megjegyzes ha van */
			if ($params['note'] && $params['note']!='') {
				$bids = str_pad($bid,7,'0',STR_PAD_LEFT) ;
				$notes = explode("\n",$params['note']);
				foreach ($notes as $note) {
					$note = str_replace($bids.':', '', $note);
					//self::addNote($bid,$note,$uid);
					/* megoldásba rakjuk inkább */
					self::addBugText($bid,$btid,$note,$uid);
				}
				
			}
			
			return $bid;

		}
		
		public static function updateTask($params){
			$sessionid = uniqid();
			$uid = $params['uid'];
			$bid = $params['id'];
			$hm = $params['mantishm'];
			$mantisms = self::hmToMs($hm);
			$ver = $params['month'];
			$durms = $params['durms'];
			$durhm = self::mstoHM($durms + $mantisms);

			$sql = "select platform,bug_text_id from mantis_bug_table where id = '$bid'";
			$stmt = self::query($sql);
			$q = self::fetchAll($stmt);
			//var_dump($q[0]);
			$durhm_old = $q[0]['platform'];
			$btid = $q[0]['bug_text_id'];
			
			
			$sql = "insert into mantis_bug_history_table (date_modified,user_id,bug_id,field_name,old_value,new_value,type)";
			$sql.=" values (now(),'$uid','$bid','platform','$durhm_old','$durhm',0);";
			$stmt = self::query($sql);

			
			$sql = " update mantis_bug_table set platform='$durhm',last_updated=now(),os='$sessionid' where id = '$bid'";
			$stmt = self::$db->query($sql);
			
			/* megjegyzes ha van */
			if ($params['note'] && $params['note']!='') {
				$note = $params['note'];
				$bids = str_pad($bid,7,'0',STR_PAD_LEFT) ;
				$note = str_replace($bids.':', '', $note);
				//self::addNote($bid,$note,$uid);
				/* megoldásba rakjuk inkább */
				self::addBugText($bid,$btid,$note,$uid);
			}
			
			return $bid;

		}
		public static function addNote($mantisId,$note,$uid){
			$note = stripcslashes($note);
			$sql="insert into mantis_bugnote_text_table (note) values ('$note')";
			$stmt = self::query($sql);
			$noteId = self::$db->lastInsertId();
			//$noteId = self::$db->insert_id;
			
			$sql="insert into mantis_bugnote_table (bug_id,bugnote_text_id,reporter_id,view_state,date_submitted, last_modified,note_type) values ('$mantisId','$noteId','$uid',10,now(),now(),0)";
			$stmt = self::query($sql);
		}
		public static function addBugText($mantisId,$btid,$note,$uid){
			$sql = "select id,additional_information as info from mantis_bug_text_table where id = '$btid'";
			$stmt = self::query($sql);
			$q = self::fetchAll($stmt);
			//var_dump($q[0]);
			if ($q[0]['id']=='') {
				//ez az ag elvileg sosem futhat, mert a description mar ki van toltve az insertTask-nal. Kozvetlen mantis felvitelnel meg ugysem engedi addig lezarni, amig nincs leiras, tehat az id mar ott is adott.
				$note=trim($note);
				$note = stripcslashes($note);
				$sql="insert into mantis_bug_text_table (additional_information) values ('$note')";
				$stmt = self::query($sql);
				$btid =  self::$db->lastInsertId();
				$sql="update mantis_bug_table set bug_text_id = '$btid'";
				
			}
			else {
				$sql="";
				$note=trim($note);
				if (stripos($q[0]['info'],$note)===false && stripos($q[0]['info'],stripcslashes($note))===false) {
					if ($q[0]['info']!='') $note = "\r\n" . $note;
					$note = stripcslashes($note);
					$sql="update mantis_bug_text_table set additional_information =concat(coalesce(additional_information,''),'$note') where id = '$btid'";
					
				}
			}
			if ($sql!="" && $note!=''){ 
				$stmt = self::query($sql);
				//megoldás frissítve
				$sql = "insert into mantis_bug_history_table (date_modified,user_id,bug_id,field_name,old_value,new_value,type)";
				$sql.=" values (now(),'$uid','$mantisId','','','',7);";
				$stmt = self::query($sql);
			}
			
		}
		
		public static function runQuery($filter){
			$uid = $filter['uid'];
			$pid = $filter['pid'];
            $uidStr = "";
            if ($uid!='-1') {
                $uidStr = "handler_id in ('$uid') and ";
                $summaryStr = "summary";
            }
            else {
                $summaryStr = "concat(summary,' @',coalesce(mantis_user_table.username,'Üres')) as summary";
                
            }
			$sql = "select mantis_bug_table.id, fixed_in_version,   $summaryStr , status, last_updated, platform from mantis_bug_table left join mantis_user_table on mantis_user_table.id = mantis_bug_table.handler_id where $uidStr project_id = '$pid' and status<81 order by last_updated desc /*limit 30*/";
			$stmt = self::query($sql);
			$rows = self::fetchAll($stmt);
			//var_dump($rows);
			return $rows;
			
		}
		public static function projectAssignCheck($filter){
			//var_dump($filter);
			$mantisId = $filter['mpid'];
			$togglId = $filter['tpid'];
			/* megnézzük melyik mantis projecthez van hozzárendelve */
			$sql="select mantis_project_id as rcount from toggl_project where toggl_project_id = $togglId";
			$stmt = self::query($sql);
			$rows = self::fetchAll($stmt);
			return $rows;
		}

		public static function projectAssign($togglId,$mantisId){
			$sql="CALL toggl_project_assign($togglId,$mantisId)";
			$stmt = self::query($sql);
		}
	}
?>