<?php if (!defined("BASEPATH")) exit("Direct script access not allowed");

class Qdb_upd {
	var $version = "1.0.0";
	
	function __construct() {
		$this->EE =& get_instance();
	}
	
	function install() {
		$this->EE->load->dbforge();
		
		$data = array(
			"module_name" => "Qdb",
			"module_version" => $this->version,
			"has_cp_backend" => "y",
			"has_publish_fields" => "n"
		);
		$this->EE->db->insert("modules", $data);
		
		$this->EE->dbforge->add_field(array(
			"quote_id" => array("type" => "int", "constraint" => "10", "unsigned" => TRUE, "auto_increment" => TRUE),
			"member_id" => array("type" => "int", "constraint" => "10", "unsigned" => TRUE, "null" => FALSE),
			"created_at" => array("type" => "datetime", "null" => FALSE),
			"updated_at" => array("type" => "datetime", "null" => TRUE),
			"status" => array("type" => "enum('open', 'closed')", "default" => "open", "null" => FALSE),
			"body" => array("type" => "text", "null" => FALSE)
		));
		$this->EE->dbforge->add_key("quote_id", TRUE);
		$this->EE->dbforge->add_key("created_at");
		$this->EE->dbforge->add_key("status");
		$this->EE->dbforge->create_table("qdb_quotes", TRUE);
		
		return TRUE;
	}
	
	function update($current = "") {
		return FALSE;
	}
	
	function uninstall() {
		$this->EE->load->dbforge();
		
		$this->EE->db->select("module_id");
		$query = $this->EE->db->get_where("modules", array("module_name" => "Qdb"));
		
		$this->EE->db->where("module_id", $query->row("module_id"));
		$this->EE->db->delete("module_member_groups");
		
		$this->EE->db->where("module_name", "Qdb");
		$this->EE->db->delete("modules");
		
		$this->EE->db->where("class", "Qdb");
		$this->EE->db->delete("actions");
		
		$this->EE->dbforge->drop_table("qdb_quotes");
		$this->EE->dbforge->drop_table("qdb_votes");
		
		return TRUE;
	}
}
