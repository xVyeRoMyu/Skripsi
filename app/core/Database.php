<?php
	class Database {
		private $server_name;
		private $db_name;
		private $user_name;
		private $password;
		private $connection;
		private $error;

		public function __construct() {
			$this->server_name = HOST_DB;
			$this->db_name = NAME_DB;
			$this->user_name = USER_DB;
			$this->password = PASS_DB;
			$this->connect();
		}

		public function connect() {
			try {
				$this->connection = new mysqli(
					$this->server_name,
					$this->user_name,
					$this->password,
					$this->db_name
				);

				if ($this->connection->connect_error) {
					$this->error = $this->connection->connect_error;
					return false;
				}
				return true;
			} catch (Exception $e) {
				$this->error = $e->getMessage();
				return false;
			}
		}

		public function getConnection() {
			return $this->connection;
		}

		public function getError() {
			return $this->error;
		}

		public function begin_transaction() {
			return $this->connection->begin_transaction();
		}

		public function commit() {
			return $this->connection->commit();
		}

		public function rollback() {
			return $this->connection->rollback();
		}

		public function query($sql) {
			$result = $this->connection->query($sql);
			if (!$result) {
				$this->error = $this->connection->error;
			}
			return $result;
		}

		public function prepare($sql) {
			$stmt = $this->connection->prepare($sql);
			if (!$stmt) {
				$this->error = $this->connection->error;
			}
			return $stmt;
		}

		public function close() {
			if ($this->connection instanceof mysqli) {
				$this->connection->close();
			}
		}

		public function __destruct() {
			$this->close();
		}
	}
?>