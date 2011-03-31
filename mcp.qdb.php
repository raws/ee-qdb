<?php if (!defined("BASEPATH")) exit("Direct script access not allowed");

class Qdb_mcp {
	var $per_page = 50;
	
	function __construct() {
		$this->EE =& get_instance();
		
		$this->EE->cp->set_right_nav(array(
			"add_quote" => BASE.AMP."C=addons_modules".AMP."M=show_module_cp".AMP."module=qdb".AMP."method=add_quote"
		));
	}
	
	function index() {
		$this->EE->load->library("pagination");
		$this->EE->load->library("table");
		
		$this->EE->cp->set_variable("cp_page_title", $this->EE->lang->line("qdb_module_name"));
		
		$vars["action_url"] = "C=addons_modules".AMP."M=show_module_cp".AMP."module=qdb".AMP."method=edit_quote";
		$vars["quotes"] = array();
		
		if (!$offset = $this->EE->input->get_post("offset")) {
			$offset = 0;
		}
		$this->EE->db->order_by("quote_id", "desc");
		// TODO SELECT SUBSTRING_INDEX(body, "\n", 1) to select first line of quote
		$query = $this->EE->db->get("qdb_quotes", $offset, $this->per_page);
		
		foreach ($query->result_array() as $row) {
			$vars["quotes"][$row["quote_id"]]["member_id"] = $row["member_id"];
			$vars["quotes"][$row["quote_id"]]["created_at"] = $row["created_at"];
			$vars["quotes"][$row["quote_id"]]["updated_at"] = $row["updated_at"];
			$vars["quotes"][$row["quote_id"]]["status"] = $row["status"];
			$vars["quotes"][$row["quote_id"]]["body"] = $row["body"];
		}
		
		// Pagination
		// $total = $this->EE->db->count_all("qdb_quotes");
		// $pagination_config = $this->pagination_config("index", $total);
		// $this->EE->pagination->initialize($pagination_config);
		// $vars["pagination"] = $this->EE->pagination->create_links();
		
		return $this->EE->load->view("index", $vars, TRUE);
	}
}
