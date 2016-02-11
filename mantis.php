<?php
	class Mantis {
		private static $dsn="mysql:host=www.expanda.hu;port=3306;dbname=pulykakakas;charset=utf8";
		private static $username = 'expanda';
		private static $password = 'abroszot624';
		private static $options = array(
			//PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
		); 

		private static $db ;
		
		static function init() {
			self::$db = new PDO(self::$dsn, self::$username, self::$password, self::$options);  		
		}
		private static function mstoHM($ms){
			$hours = "";
			$minutes = "";
			if ($ms>0) {
				$hours = str_pad(floor($ms / 1000 / 3600),2,'0',STR_PAD_LEFT) ;
				$minutes = str_pad(floor(($ms - ($hours * 1000 * 3600)) / 1000 / 60),2,'0',STR_PAD_LEFT) ;
			}
			return $hours!=""?($hours.':'.$minutes):"";
			
		}
		public static function getStatuses(){
			
			$sql="select value from mantis_config_table where config_id='status_enum_workflow'";
			$stmt = self::$db->query($sql);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
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
			$stmt = self::$db->query($sql);
			return $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
		}
		public static function getUsers() {
			$sql="select username , id from mantis_user_table where enabled=1 and access_level>=70 order by username";
			$stmt = self::$db->query($sql);
			return $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
		}
		public static function getMonths() {
			$sql="select version from mantis_project_version_table where version not in ('X','Y') order by version desc limit 4";
			$stmt = self::$db->query($sql);
			return $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
		}		
		public static function insertTask($params){
			$uid = $params['uid'];
			$pid = $params['pid'];
			$desc = $params['desc'];
			//echo mb_detect_encoding($desc);
			$ver = $params['month'];
			$durms = $params['durms'];
			$durhm = self::mstoHM($durms);
			$status='50';
			$severity='10';

			$sql = " insert into mantis_bug_text_table (description )";
			$sql.=" values ('$desc') ";
			
			$stmt = self::$db->query($sql);
			$btid =  self::$db->lastInsertId();


			$sql = " insert into mantis_bug_table (fixed_in_version,summary,status,handler_id,reporter_id,project_id,last_updated,date_submitted,severity,bug_text_id,platform)";
			$sql.=" values ('$ver','$desc','$status','$uid','$uid','$pid',now(),now(),$severity,$btid,'$durhm') ";
			
			$stmt = self::$db->query($sql);
			$bid = self::$db->lastInsertId();
			
			/* uj bejelentes */
			$sql = "insert into mantis_bug_history_table (date_modified,user_id,bug_id,field_name,old_value,new_value,type)";
			$sql.=" values (now(),'$uid','$bid','','','',1);";
			$stmt = self::$db->query($sql);
			/* status */
			$sql = "insert into mantis_bug_history_table (date_modified,user_id,bug_id,field_name,old_value,new_value,type)";
			$sql.=" values (now(),'$uid','$bid','status','10','$status',0);";
			$stmt = self::$db->query($sql);

			/* hozzarendeles */
			$sql = "insert into mantis_bug_history_table (date_modified,user_id,bug_id,field_name,old_value,new_value,type)";
			$sql.=" values (now(),'$uid','$bid','handler_id','','$uid',0);";
			$stmt = self::$db->query($sql);
			
			/* megjegyzes ha van */
			if ($params['note'] && $params['note']!='') {
				$note = $params['note'];
				self::addNote($bid,$note,$uid);
			}
			
			return $bid;

		}
		
		public static function addNote($mantisId,$note,$uid){
			$note = stripcslashes($note);
			$sql="insert into mantis_bugnote_text_table (note) values ('$note')";
			$stmt = self::$db->query($sql);
			$noteId = self::$db->lastInsertId();
			
			$sql="insert into mantis_bugnote_table (bug_id,bugnote_text_id,reporter_id,view_state,date_submitted, last_modified,note_type) values ('$mantisId','$noteId','$uid',10,now(),now(),0)";
			$stmt = self::$db->query($sql);
		}
	}
?>