<?php
	class togglTask {
		private $id;
		private $pid;
		private $description;
		private $duration;
		private $start;
		
		public function __construct($id,$pid,$start,$duration,$desc) {
			$this->id = $id;
			$this->pid = $pid;
			$this->start = $start;
			$this->duration = $duration;
			$this->description = $desc;
		}
		
	}
?>