<?php 
class service_get_backup extends Service {
	
	public function getRequiredRights() { return array(); }
	
	public function documentation() {}
	public function inputDocumentation() {}
	public function outputDocumentation() {}
	public function getOutputFormat($input) {
		switch ($input["request"]) {
			case "get_list":
				return "application/json";
		}
		return "application/octet-stream";
	}
	
	public function execute(&$component, $input) {
		if (!file_exists("conf/".PNApplication::$instance->local_domain.".password")) {
			header("HTTP/1.0 403 Access Denied (no password set)");
			return;
		}
		if (!isset($input["password"])) {
			header("HTTP/1.0 403 Access Denied (no password given)");
			return;
		}
		if (sha1($input["password"]) <> file_get_contents("conf/".PNApplication::$instance->local_domain.".password")) {
			// TODO add additional security here to avoid attacks
			header("HTTP/1.0 403 Access Denied (invalid password)");
			return;
		}
		switch ($input["request"]) {
			case "get_list": $this->getList(); break;
			case "get_backup": $this->getBackup($input); break;
			default:
				header("HTTP/1.0 400 Bad Request");
				return;
		}
	}

	private function getList() {
		$backups = array();
		if (file_exists("data/backups")) {
			$versions = array();
			$dir = opendir("data/backups");
			while (($file = readdir($dir)) <> null) {
				if ($file == "." || $file == "..") continue;
				if (!is_dir("data/backups/$file")) continue;
				array_push($versions, $file);
			}
			closedir($dir);
			foreach ($versions as $version) {
				$dir = opendir("data/backups/$version");
				while (($file = readdir($dir)) <> null) {
					if ($file == "." || $file == "..") continue;
					if (!is_dir("data/backups/$version/$file")) continue;
					array_push($backups, array("version"=>$version,"time"=>$file));
				}
				closedir($dir);
			}
		}
		echo json_encode($backups);
	}
	
	private function getBackup($input) {
		$version = $input["version"];
		$time = $input["time"];
		$file = $input["file"];
		if (!file_exists("data/backups/$version/$time/$file.zip")) {
			header("HTTP/1.0 404 File Not Found");
			return;
		}
		header("Accept-Ranges: bytes", true);
		if (isset($_GET["get_size"])) {
			header("File-Size: ".filesize("data/backups/$version/$time/$file.zip"));
			return;
		}
		if (isset($_SERVER["HTTP_RANGE"])) {
			$range = trim($_SERVER["HTTP_RANGE"]);
			if (substr($range,0,6) <> "bytes=") {
				header("HTTP/1.0 404 Bad Request");
				return;
			}
			$range = substr($range,6);
			$i = strpos($range, "-");
			if ($i === false) {
				header("HTTP/1.0 404 Bad Range");
				return;
			}
			$from = intval(substr($range,0,$i));
			$to = intval(substr($range,$i+1));
			if ($to < $from) {
				header("HTTP/1.0 404 Invalid Range");
				return;
			}
			$size = filesize("data/backups/$version/$time/$file.zip");
			if ($to > $size-1) {
				header("HTTP/1.0 404 Range exceed file size");
				return;
			}
			header("HTTP/1.1 206 Partial Content");
			header("Content-Range: bytes $from-$to/$size", true);
			$f = fopen("data/backups/$version/$time/$file.zip","r");
			fseek($f, $from, SEEK_SET);
			while ($from <= $to) {
				$part = fread($f, $to-$from+1);
				if ($part === false) break;
				echo $part;
				$from += strlen($part);
			}
			fclose($f);
		} else
			readfile("data/backups/$version/$time/$file.zip");
	}
	
}
?>