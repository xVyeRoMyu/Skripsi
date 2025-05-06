<?php
	class default_page extends Controller {
		public function __construct() {
		}
		
		public function index($name="Guest") {
			$arr_data['title'] = "Jen's House";
			$this->display('template/header', $arr_data);
			$this->display("default/home", $arr_data);
			$this->display('template/footer');
		}

		public function checking() {
			$arr_data['title'] = "Loading..";
			$this->display("default/checking", $arr_data);
		}
		
		public function reservation() {
			$arr_data['title'] = "Reservation";
			$this->display('template/reservation/header', $arr_data);
			$this->display("default/reservation", $arr_data);
			$this->display('template/reservation/footer', $arr_data);
		}
		
		public function faq() {
			$arr_data['title'] = "Frequent Asked Question";
			$this->display('template/faq/header', $arr_data);
			$this->display("default/faq", $arr_data);
			$this->display('template/footer');
		}
		
		public function details() {
			$arr_data['title'] = "Booking Details";
			$this->display('template/details/header', $arr_data);
			$this->display("default/details", $arr_data);
			$this->display('template/details/footer');
		}

		public function login() {
			$arr_data['title'] = "Login";
			$this->display("template/login/header", $arr_data);
			$this->display("enroll/login", $arr_data);
		}
		
		public function register() {
			$arr_data['title'] = "Register";
			$this->display("template/register/header", $arr_data);
			$this->display("enroll/register", $arr_data);
			$this->display("template/register/footer", $arr_data);
		}
		
		public function logout() {
			session_start();
			session_destroy();
			header("Location: " . APP_PATH . "default/home");
			exit;
		}
	}
?>
