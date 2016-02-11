<?php
	class togglUser {
		private $id;
		private $fullname;
		
		public function __construct($id,$fullname) {
			$this->id = $id;
			$this->fullname = $fullname;
		}
		
		public function getId(){
			return $this->id;
		}
		public function getFullName(){
			return $this->fullname;
		}
		
	}
?>