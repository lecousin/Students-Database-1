<?php
class service_create_campaign extends Service{
	public function get_required_rights(){return array();}
	public function input_documentation(){
		?>
<ul>
	<li><code>name</code>: {string} the campaign name</li>
</ul>
<?php	
	}
	public function output_documentation(){
		echo "<ul>";
		echo "<li>{boolean} false if a problem occured</li>";
		echo "<li> {object} {key: id} where id is the one generated by inserting into the database</li>";
		echo "</ul>";
	}
	public function documentation(){}//TODO
	public function execute(&$component,$input){
		if(!isset($input["name"])) {echo "false"; return;}
		else{
			try{
				$key = $component->create_campaign($input["name"]);
			} catch(Exception $e) {
				PNApplication::error($e->getMessage());
			}
			if(PNApplication::has_errors()) echo "false";
			else echo "{key:".$key."}";
		}
	}
}	
?>