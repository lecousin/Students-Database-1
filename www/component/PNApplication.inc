<?php
require_once("Component.inc");

/**
 * PNApplication is the root point, used to:
 *   - access to any component
 *   - access to the global data model
 *   - indicate errors along the process, that may be then display to the user
 */
class PNApplication {

	/** @var PNApplication */
	public static $instance;

	public static $errors = array();
	/** @var string */
	public $current_domain;
	/** @var string */
	public $local_domain;
	public $components = array();

	/**
	 * Signal an error
	 * @param string $message in plain text, which will be converted into html
	 */
	public static function error($message) {
		if ($message instanceof Exception) {
			$e = $message;
			$message = htmlentities($e->getMessage(), ENT_QUOTES, "UTF-8")."<ul style='margin:0px'>";
			foreach ($e->getTrace() as $trace)
				$message .= "<li>".$trace["file"]." line ".$trace["line"]."</li>";
			$message .= "</ul>";
			array_push(PNApplication::$errors, $message);
			return;
		}
		array_push(PNApplication::$errors, htmlentities($message, ENT_QUOTES, "UTF-8"));
	}
	/**
	 * Signal an error
	 * @param string $html_message in html format
	 */
	public static function error_html($html_message) {
		array_push(PNApplication::$errors, $html_message);
	}

	/**
	 * @return boolean true if there are errors
	 */
	public static function has_errors() { return count(PNApplication::$errors) > 0; }

	/**
	 * Generate error messages to the output in html format
	 */
	public static function print_errors() {
		if (!PNApplication::has_errors()) return;
		foreach (PNApplication::$errors as $e)
			echo "<div style='color:#C00000;font-familiy:Tahoma;font-size:10pt'><img src='".theme::$icons_16["error"]."' style='vertical-align:bottom'/> ".$e."</div>";
		echo "<script type='text/javascript'>";
		foreach (PNApplication::$errors as $e)
			echo "window.top.status_manager.add_status(new window.top.StatusMessageError(null,".json_encode($e)."));"; 
		echo "</script>";
	}
	/**
	 * Generate the output of a JSON service: put the list of errors if any, then the given result 
	 * @param unknown $result
	 */
	public static function print_json_result($result) {
		echo "{";
		if (count(PNApplication::$errors) > 0) {
			echo "errors:[";
			$first = true;
			foreach (PNApplication::$errors as $e) {
				if ($first) $first = false; else echo ",";
				echo json_encode($e);
			}
			echo"],";
		}
		echo "result:";
		if ($result === null) echo "null"; else echo $result;
		echo "}";
	}

	/**
	 * Initialize PNApplication: instantiate and initialize every component
	 */
	public function init() {
		$this->local_domain = file_get_contents("conf/local_domain");
		$this->current_domain = $this->local_domain;
		$components_names = array();
		$dir = @opendir("component");
		while (($filename = readdir($dir)) <> null) {
			if (substr($filename, 0, 1) == ".") continue;
			if (is_dir("component/".$filename)) array_push($components_names, $filename);
		}
		closedir($dir);
		foreach ($components_names as $name) $this->create_component($name);
		$done = array();
		foreach ($this->components as $c) $this->init_component($c, $done);
	}
	private function create_component($name) {
		require_once("component/".$name."/".$name.".inc");
		$c = new $name($name);
		$this->components[$name] = &$c;
		$this->{$name} = &$c;
	}
	private function init_component($c, &$done) {
		if (in_array($c->name, $done)) return;
		array_push($done, $c->name);
		foreach ($c->dependencies() as $dep) {
			if (!isset($this->components[$dep])) die("Missing component '".$dep."' which is a dependency of component '".$c->name."'");
			$this->init_component($this->components[$dep], $done);
		}
		$c->init();
	}

	/**
	 * @return array associative array of domain=>domain_descriptor
	 */
	public function get_domains() {
		return include("conf/domains");
	}
	
	/**
	 * @param string $domain if null it will return the descriptor of the current domain
	 * @return array attributes of the domain
	 */
	public function get_domain_descriptor($domain = null) {
		if ($domain == null) $domain = $this->current_domain;
		$domains = include("conf/domains");
		return $domains[$domain];
	}
}
?>