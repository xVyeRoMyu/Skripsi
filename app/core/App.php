<?php
    //create a class
    class App {
        //Global variables
        public $controller = "";
        public $method = "";
        public $parameter = "";
        
        //PHP Constructor
        public function __construct(){
            //Default.controller
            $this->initDefaultController("default_page","index","");

            //Display stuctured information (type and value)
            $url = $this -> parseURL();
            //var_dump($url);

            //Handle controller
			if (!empty($url)) {
				if (file_exists('../app/controller/'.$url[0].'.php')) {
					$this->controller = $url[0];
					unset($url[0]);
				} else {
					$this->parameter = array();
				}
			}

            require_once '../app/controller/'.$this->controller.'.php';
            $this->controller = new $this->controller;  //instansiasi object

            //Handle Method
            if(isset($url[1])){
                $name_method=$url[1]; //move method name
				//Check underscore prefix
				if (!$this ->starts_with($name_method,"_")){
					//Check if a method exists
					if(method_exists($this->controller, $name_method)){
						//Change method name
						$this->method = $name_method;
						//Delete element index 1 in array
						unset($url[1]);
					}
                }else{
					//Delete element index 1 in array
					unset($url[1]);
				}
            }

            //handle input parameters
            if(!empty($url)){
                //reset array start from index 0
                $this->parameter=array_values($url);

                //Display array
                //var_dump($this->parameter);
            }else{
                //initalize empty array
                $this->parameter=array();
            }
            
            //Run controller and method with some parameters
            call_user_func_array([$this->controller, $this->method], $this->parameter);
        }
		
		//Check if a string starts with underscore
		private function starts_with($str, $prefix){
			//return a bool
			return strpos ($str, $prefix) === 0;
		}

        //Initialize default global variable
        private function initDefaultController($inicontroller, $inimethod, $iniparam){
            $this->controller = $inicontroller ;
            $this->method = $inimethod;
            $this->parameter = $iniparam;
        }
        
        //PHP Destructor
		public function parseURL(){
			if(isset($_GET['url'])){
			//remove and slash
			$url = rtrim($_GET['url'], '/');

			//remove/filter special chareacter
			$url = filter_var($url, FILTER_SANITIZE_URL);

			//CONVER TO ARRAY
			$url = explode ('/', $url);

			return $url;
		}
	}
}
?>