<?php if (!defined("BASEPATH")) exit("Direct script access not allowed");

class Qdb_mcp {
	var $per_page = 50;
	
	function __construct() {
		$this->EE =& get_instance();
		
		$this->EE->cp->set_right_nav(array(
			"add_quote" => $this->cp_link_to("add_quote")
		));
	}
	
	function index() {
		$this->EE->load->library("pagination");
		$this->EE->load->library("table");
		
		$this->EE->cp->set_variable("cp_page_title", $this->EE->lang->line("qdb_module_name"));
		
		$vars["action_url"] = $this->cp_link_to("index");
		$vars["quotes"] = array();
		
		if (!$offset = $this->EE->input->get_post("offset")) {
			$offset = 0;
		}
		$this->EE->db->order_by("quote_id", "desc");
		$this->EE->db->select('quote_id, qdb_quotes.member_id, screen_name, created_at, updated_at, status, SUBSTRING_INDEX(body, "\n", 1) AS body');
		$this->EE->db->join("members", "qdb_quotes.member_id = members.member_id", "inner");
		$query = $this->EE->db->get("qdb_quotes", $this->per_page, $offset);
		$vars["quotes"] = $query->result_array();
		
		// Pagination
		// $total = $this->EE->db->count_all("qdb_quotes");
		// $pagination_config = $this->pagination_config("index", $total);
		// $this->EE->pagination->initialize($pagination_config);
		// $vars["pagination"] = $this->EE->pagination->create_links();
		
		return $this->EE->load->view("index", $vars, TRUE);
	}
	
	function add_quote() {
		if ($_SERVER["REQUEST_METHOD"] == "POST") {
			return $this->post_add_quote();
		} else {
			return $this->get_add_quote();
		}
	}
	
	function get_add_quote() {
		$this->EE->load->library("table");
		
		$this->EE->cp->set_variable("cp_page_title", $this->EE->lang->line("add_quote"));
		$this->EE->cp->set_breadcrumb($this->cp_link_to("index"), $this->EE->lang->line("qdb_module_name"));
		
		$vars["action_url"] = $this->cp_path_to("add_quote");
		
		$this->EE->db->select("member_id, screen_name");
		$query = $this->EE->db->get("members");
		foreach ($query->result() as $row) {
			$vars["members"][$row->member_id] = $row->screen_name;
		}
		$vars["member_id"] = $this->EE->session->userdata["member_id"];
		
		return $this->EE->load->view("add_quote", $vars, TRUE);
	}
	
	function post_add_quote() {
		$this->EE->load->library("form_validation");
		
		$this->EE->form_validation->set_rules("member_id", lang("submitted_by"), "required|integer|greater_than[0]");
		$this->EE->form_validation->set_rules("created_at", lang("created"), "trim|required|exact_length[19]");
		$this->EE->form_validation->set_rules("status", lang("status"), "required|callback_validate_status");
		$this->EE->form_validation->set_rules("body", lang("body"), "trim|required");
		
		$this->EE->form_validation->set_error_delimiters('<p class="shun notice">', '</p>');
		
		if ($this->EE->form_validation->run() == FALSE) {
			return $this->get_add_quote();
		} else {
			$data = array(
				"member_id" => $this->EE->input->post("member_id"),
				"created_at" => $this->EE->input->post("created_at"),
				"updated_at" => $this->EE->input->post("created_at"),
				"status" => $this->EE->input->post("status"),
				"body" => $this->EE->input->post("body")
			);
			$this->EE->db->insert("qdb_quotes", $data);
			
			$this->EE->session->set_flashdata("message_success", lang("quote_added_successfully"));
			$this->EE->functions->redirect($this->cp_link_to("index"));
		}
	}
	
	function validate_status($status) {
		return in_array($status, array("open", "closed"));
	}
	
	private function cp_path_to($method) {
		$uri = "C=addons_modules".AMP."M=show_module_cp".AMP."module=qdb";
		if ($method != "index")
			$uri .= AMP."method=$method";
		return $uri;
	}
	
	private function cp_link_to($method) {
		return BASE.AMP.$this->cp_path_to($method);
	}
}
