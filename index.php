<?php
  header('Access-Control-Allow-Origin: *');  
  header("Content-type: text/html; charset=utf-8");
  require_once 'toggl.php';
  require_once 'mantis.php';
 
  $toggl = new Toggl();
  $toggl->init();
 
  $mantis = new Mantis();
  $mantis->init();
 
 //$statuses = $mantis->getStatuses();
 
  $request  = strstr($_SERVER['REQUEST_URI'],".php");
  $p     = explode("/", $request);
  
  $func = $p[1];
  $r = $_REQUEST;
  
  
//var_dump($r);
	  
    switch  ($func) {
		case 'showMain':
			echo json_encode($r);
			break;
		case 'togglUsers':
			$u = $toggl->getUsers();
			echo json_encode($u);
			break;
		case 'togglProjects':
			echo json_encode($toggl->getProjects());
			break;
		case 'togglTasks':
			$uid = $r['userId'];
			$pid = $r['projectId'];
			$until = $r['until'];
			$filter = array('user_ids'=>$uid,'since'=>'2016-01-01','user_agent'=>'togglToMantis','project_ids'=>$pid,'until'=>$until);
			echo json_encode($toggl->getTasks($filter));
			break;
		case 'Insert':
			//'{"time_entry":{"description":"Meeting with possible clients","tags":[""],"duration":1240,"start":"2013-03-05T07:58:58.000Z","stop":"2013-03-05T08:58:58.000Z","duronly":true,"pid":123,"billable":true}}' \
			$tag= array("beírva");
			$togglPar=$r['togglPar'];
			$mantisPar=$r['mantisPar'];
			$mantisId = $mantis->insertTask($mantisPar);
			$desc = $mantisPar['desc'];
			if (stripos($desc,$mantisId)===false) {
				$desc = str_pad($mantisId,7,'0',STR_PAD_LEFT). ': ' .$desc;
			}
			$filter=array('tags'=>$tag,'description'=>$desc);	
			
			echo json_encode($toggl->updateTask($togglPar, $filter));
			//echo json_encode($mantisId);
			break;
		case 'InsertWithNote':
			//'{"time_entry":{"description":"Meeting with possible clients","tags":[""],"duration":1240,"start":"2013-03-05T07:58:58.000Z","stop":"2013-03-05T08:58:58.000Z","duronly":true,"pid":123,"billable":true}}' \
			$tag= array("beírva");
			$togglPar=$r['togglPar'];
			$mantisPar=$r['mantisPar'];
			$mantisId = $mantis->insertTask($mantisPar);
			$notes = explode("\n",$togglPar['togglDesc']);
			$taskIds = explode("\n",$togglPar['taskIds']);
			$res = "";
			for ($i=0;$i<count($taskIds);$i++) {
				
				$desc = $notes[$i];
				if (stripos($desc,$mantisId)===false) {
					$desc = str_pad($mantisId,7,'0',STR_PAD_LEFT). ': ' .$desc;
				}
				$filter=array('tags'=>$tag,'description'=>$desc);	
				$tid = $taskIds[$i];
				if ($tid!='') {
					$tpar = array('taskId'=>$tid);
					$res += json_encode($toggl->updateTask($tpar, $filter));
				}
			}
			echo json_encode($res);
			break;
		case 'UpdateWithNote':
			//'{"time_entry":{"description":"Meeting with possible clients","tags":[""],"duration":1240,"start":"2013-03-05T07:58:58.000Z","stop":"2013-03-05T08:58:58.000Z","duronly":true,"pid":123,"billable":true}}' \
			$tag= array("beírva");
			$togglPar=$r['togglPar'];
			$mantisPar=$r['mantisPar'];
			$mantisId = $mantis->updateTask($mantisPar);
			$notes = explode("\n",$togglPar['togglDesc']);
			$taskIds = explode("\n",$togglPar['taskIds']);
			$res = "";
			for ($i=0;$i<count($taskIds);$i++) {
				
				$desc = $notes[$i];
				if (stripos($desc,$mantisId)===false) {
					$desc = str_pad($mantisId,7,'0',STR_PAD_LEFT). ': ' .$desc;
				}
				$filter=array('tags'=>$tag,'description'=>$desc);	
				$tid = $taskIds[$i];
				if ($tid!='') {
					$tpar = array('taskId'=>$tid);
					$res += json_encode($toggl->updateTask($tpar, $filter));
				}
			}
			echo json_encode($res);
			break;
		case 'mantisPartners':
			echo json_encode($mantis->getPartners());
			break;
		case 'mantisUsers':
			echo json_encode($mantis->getUsers());
			break;
		case 'mantisMonths':
			echo json_encode($mantis->getMonths());
			break;
		case 'mantisQueryResult':
			$uid = $r['uid'];
			$pid = $r['pid'];
			$filter=array('uid'=>$uid,'pid'=>$pid);	
			echo json_encode($mantis->runQuery($filter));
			break;
		case 'projectAssignCheck':
			$tpid = $r['togglPId'];
			$mpid = $r['mantisPId'];
			$filter=array('tpid'=>$tpid,'mpid'=>$mpid);	
			echo json_encode($mantis->projectAssignCheck($filter));
			break;
		case 'projectAssign':
			$tpid = $r['togglPId'];
			$mpid = $r['mantisPId'];
			$filter=array('tpid'=>$tpid,'mpid'=>$mpid);	
			echo json_encode($mantis->projectAssign($tpid,$mpid));
			break;
		default: 
			echo 'error';
	}
 
  
  
	  
?>