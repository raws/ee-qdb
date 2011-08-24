<?php if (!defined("BASEPATH")) exit("Direct script access not allowed");

class Qdb_mcp {
	var $per_page = 50;
	
	function __construct() {
		$this->EE =& get_instance();
		
		$this->EE->cp->set_right_nav(array(
			"add_quote" => $this->cp_link_to("add_quote"),
			"import_quotes" => $this->cp_link_to("import_quotes")
		));
	}
	
	function index() {
		if ($_SERVER["REQUEST_METHOD"] == "POST") {
			return $this->post_index();
		} else {
			return $this->get_index();
		}
	}
	
	function get_index() {
		$this->EE->load->library("pagination");
		$this->EE->load->library("table");
		
		$this->EE->cp->set_variable("cp_page_title", $this->EE->lang->line("qdb_module_name"));
		
		$vars["action_url"] = $this->cp_path_to("index");
		$vars["options"] = array(
			"delete" => $this->EE->lang->line("delete_selected"),
			"close" => $this->EE->lang->line("close_selected"),
			"open" => $this->EE->lang->line("open_selected")
		);
		
		$this->EE->db->select(array("member_id", "screen_name"));
		$query = $this->EE->db->get("members");
		$vars["members"][""] = "";
		foreach ($query->result() as $row)
			$vars["members"][$row->member_id] = $row->screen_name;
		
		if (!$offset = $this->EE->input->get_post("page")) {
			$offset = 0;
		}
		
		$filters = $this->process_filters();
		$this->EE->db->from("qdb_quotes");
		$total = $this->EE->db->count_all_results();
		
		// Set up filters again for next query
		$this->process_filters();
		
		$this->EE->db->order_by("created_at", "desc");
		$this->EE->db->select('quote_id, qdb_quotes.member_id, screen_name, created_at, updated_at, status, SUBSTRING_INDEX(body, "\n", 1) AS body', FALSE);
		$this->EE->db->join("members", "qdb_quotes.member_id = members.member_id", "inner");
		$query = $this->EE->db->get("qdb_quotes", $this->per_page, $offset);
		foreach ($query->result_array() as $row) {
			$vars["quotes"][$row["quote_id"]] = $row;
			$vars["quotes"][$row["quote_id"]]["toggle"] = array(
				"name" => "toggle[]",
				"id" => "toggle_".$row["quote_id"],
				"value" => $row["quote_id"],
				"class" => "toggle"
			);
			$body = $vars["quotes"][$row["quote_id"]]["body"];
			if (strlen($body) > 75) {
				$vars["quotes"][$row["quote_id"]]["body"] = substr($body, 0, 75) . "…";
			}
		}
		
		// Pagination
		$config["base_url"] = $this->cp_link_to("index", $filters);
		$config["total_rows"] = $total;
		$config["per_page"] = $this->per_page;
		$config["page_query_string"] = TRUE;
		$config["query_string_segment"] = "page";
		$config["num_links"] = 3;
		$this->EE->pagination->initialize($config);
		$vars["pagination"] = $this->EE->pagination->create_links();
		$vars["start"] = $offset;
		$vars["end"] = (($offset / $this->per_page) + 1) * $this->per_page;
		$vars["total"] = $total;
		$vars["per_page"] = $this->per_page;
		
		$this->EE->cp->load_package_js("index");
		return $this->EE->load->view("index", $vars, TRUE);
	}
	
	function post_index() {
		$quote_ids = $this->EE->input->post("toggle");
		
		if (empty($quote_ids)) {
			$this->EE->functions->redirect($this->cp_link_to("index"));
		}
		
		switch ($this->EE->input->post("action")) {
			case "delete":
				$this->EE->db->where_in("quote_id", $quote_ids);
				$this->EE->db->delete("qdb_quotes");
				$this->EE->session->set_flashdata("message_success", lang("quotes_deleted_successfully"));
				break;
			case "close":
				$this->EE->db->where_in("quote_id", $quote_ids);
				$this->EE->db->update("qdb_quotes", array("status" => "closed"));
				$this->EE->session->set_flashdata("message_success", lang("quotes_closed_successfully"));
				break;
			case "open":
				$this->EE->db->where_in("quote_id", $quote_ids);
				$this->EE->db->update("qdb_quotes", array("status" => "open"));
				$this->EE->session->set_flashdata("message_success", lang("quotes_opened_successfully"));
				break;
		}
		
		$this->EE->functions->redirect($this->cp_link_to("index"));
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
		foreach ($query->result() as $row)
			$vars["members"][$row->member_id] = $row->screen_name;
		$vars["member_id"] = $this->EE->session->userdata["member_id"];
		
		return $this->EE->load->view("_quote", $vars, TRUE);
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
			
			$this->EE->session->set_flashdata("message_success", lang("quote_saved_successfully"));
			$this->EE->functions->redirect($this->cp_link_to("index"));
		}
	}
	
	function edit_quote() {
		if ($_SERVER["REQUEST_METHOD"] == "POST") {
			return $this->post_edit_quote();
		} else {
			return $this->get_edit_quote();
		}
	}
	
	function get_edit_quote() {
		$this->EE->load->library("table");
		
		$this->EE->cp->set_variable("cp_page_title", $this->EE->lang->line("edit_quote"));
		$this->EE->cp->set_breadcrumb($this->cp_link_to("index"), $this->EE->lang->line("qdb_module_name"));
		
		$vars["action_url"] = $this->cp_path_to("edit_quote", array("id" => $this->EE->input->get_post("id")));
		
		$query = $this->EE->db->get_where("qdb_quotes", array("quote_id" => $this->EE->input->get_post("id")), 1);
		$vars["quote"] = $query->row_array();
		
		$this->EE->db->select("member_id, screen_name");
		$query = $this->EE->db->get("members");
		foreach ($query->result() as $row)
			$vars["members"][$row->member_id] = $row->screen_name;
		$vars["member_id"] = $this->EE->session->userdata["member_id"];
		
		return $this->EE->load->view("_quote", $vars, TRUE);
	}
	
	function post_edit_quote() {
		$this->EE->load->library("form_validation");
		
		$this->EE->form_validation->set_rules("member_id", lang("submitted_by"), "required|integer|greater_than[0]");
		$this->EE->form_validation->set_rules("created_at", lang("created"), "trim|required|exact_length[19]");
		$this->EE->form_validation->set_rules("status", lang("status"), "required|callback_validate_status");
		$this->EE->form_validation->set_rules("body", lang("body"), "trim|required");
		
		$this->EE->form_validation->set_error_delimiters('<p class="shun notice">', '</p>');
		
		if ($this->EE->form_validation->run() == FALSE) {
			return $this->get_edit_quote();
		} else {
			$data = array(
				"member_id" => $this->EE->input->post("member_id"),
				"created_at" => $this->EE->input->post("created_at"),
				"updated_at" => date("Y-m-d H:i:s"),
				"status" => $this->EE->input->post("status"),
				"body" => $this->EE->input->post("body")
			);
			$this->EE->db->where("quote_id", $this->EE->input->get_post("id"));
			$this->EE->db->update("qdb_quotes", $data);
			
			$this->EE->session->set_flashdata("message_success", lang("quote_saved_successfully"));
			$this->EE->functions->redirect($this->cp_link_to("index"));
		}
	}
	
	function import_quotes() {
		if ($_SERVER["REQUEST_METHOD"] == "POST") {
			return $this->post_import_quotes();
		} else {
			return $this->get_import_quotes();
		}
	}
	
	function get_import_quotes() {
		$this->EE->load->library("table");
		
		$this->EE->cp->set_variable("cp_page_title", $this->EE->lang->line("import_quotes"));
		$this->EE->cp->set_breadcrumb($this->cp_link_to("index"), $this->EE->lang->line("qdb_module_name"));
		
		$vars["action_url"] = $this->cp_path_to("import_quotes");
		
		$this->EE->db->select(array("member_id", "screen_name"));
		$query = $this->EE->db->get("members");
		foreach ($query->result() as $row)
			$vars["members"][$row->member_id] = $row->screen_name;
		$vars["member_id"] = $this->EE->session->userdata["member_id"];
		
		return $this->EE->load->view("import_quotes", $vars, TRUE);
	}
	
	function post_import_quotes() {
		$config["upload_path"] = sys_get_temp_dir();
		$config["allowed_types"] = "csv|txt";
		$config["encrypt_name"] = TRUE;
		$this->EE->load->library("upload", $config);
		
		if ($this->EE->upload->do_upload("file") == FALSE) {
			$this->EE->session->set_flashdata("message_failure", $this->EE->upload->display_errors());
			return $this->EE->functions->redirect($this->cp_link_to("import_quotes"));
		}
		
		$this->EE->load->library("form_validation");
		$this->EE->form_validation->set_rules("columns", lang("columns"), "trim|required");
		$this->EE->form_validation->set_rules("default_member_id", lang("default_member"), "required");
		if ($this->EE->form_validation->run() == FALSE) {
			$this->EE->session->set_flashdata("message_failure", $this->EE->form_validation->validation_errors());
			return $this->EE->functions->redirect($this->cp_link_to("import_quotes"));
		}
		
		$upload = $this->EE->upload->data();
		$required_columns = array("member_id", "created_at", "status", "body");
		$columns = preg_split("/[\s,]+/", strtolower(trim($this->EE->input->post("columns"))));
		$column_count = count($columns);
		$batch_data = array();
		$count = 0;
		
		$this->EE->db->select("member_id");
		$query = $this->EE->db->get("members");
		$members = $query->result_array();
		$member_ids = array_map(array($this, "map_member_id"), $members);
		$default_member_id = $this->EE->input->post("default_member_id");
		
		if (($handle = fopen($upload["full_path"], "r")) !== FALSE) {
			while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== FALSE) {
				if (count($row) != $column_count) { continue; }
				
				$data = array_combine($columns, $row);
				foreach ($data as $key => $value) {
					switch ($key) {
						case "member_id":
							if (!in_array($value, $member_ids))
								$data[$key] = $default_member_id;
							break;
						case "body":
							$data[$key] = $this->process_body($value);
							break;
						case "created_at":
							$data[$key] = $this->process_datetime($value);
							break;
						case "updated_at":
							$data[$key] = $this->process_datetime($value);
							break;
						case "status":
							$data[$key] = $this->process_status($value);
							break;
					}
					
					if ($data[$key] == FALSE) { continue 2; }
				}
				
				foreach ($required_columns as $column_name) {
					if (!array_key_exists($column_name, $data)) { continue 2; }
				}
				
				$batch_data[] = $data;
				$count++;
			}
			
			fclose($handle);
		} else {
			$this->EE->session->set_flashdata("message_failure", lang("could_not_read_file"));
			$this->EE->functions->redirect($this->cp_link_to("import_quotes"));
		}
		
		$this->EE->db->insert_batch("qdb_quotes", $batch_data);
		
		$this->EE->session->set_flashdata("message_success", $count." ".lang("quotes_imported_successfully"));
		$this->EE->functions->redirect($this->cp_link_to("index"));
	}
	
	function validate_status($status) {
		return in_array($status, array("open", "closed"));
	}
	
	private function map_member_id($input) {
		return $input["member_id"];
	}
	
	private function process_body($input) {
		return str_replace('\\"', '"', trim(strval($input)));
	}
	
	private function process_datetime($input) {
		$input = strval($input);
		
		if (preg_match("/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/", $input)) {
			// MySQL DATETIME
			return $input;
		} else if (preg_match("/^\d+$/", $input)) {
			// Unix timestamp
			return date("Y-m-d H:i:s", intval($input));
		}
		
		return FALSE;
	}
	
	private function process_status($input) {
		$input = strtolower(trim(strval($input)));
		if (in_array($input, array("1", "open", "true", "yes", "y")))
			return "open";
		return "closed";
	}
	
	private function process_filters() {
		$filters = array();
		
		if (($fid = $this->EE->input->get_post("fid")) !== FALSE && !empty($fid)) {
			$this->EE->db->where("quote_id", $fid);
			$filters["fid"] = $fid;
		}
		
		if (($fmid = $this->EE->input->get_post("fmid")) !== FALSE && !empty($fmid)) {
			$this->EE->db->where("qdb_quotes.member_id", $fmid);
			$filters["fmid"] = $fmid;
		}
		
		if (($fst = $this->EE->input->get_post("fst")) !== FALSE && !empty($fst)) {
			$this->EE->db->where("status", $fst);
			$filters["fst"] = $fst;
		}
		
		if (($fb = $this->EE->input->get_post("fb")) !== FALSE && !empty($fb)) {
			$this->EE->db->where("body LIKE", "%$fb%");
			$filters["fb"] = $fb;
		}
		
		return $filters;
	}
	
	static function cp_path_to($method, $query = array()) {
		$uri = "C=addons_modules".AMP."M=show_module_cp".AMP."module=qdb";
		if ($method != "index")
			$uri .= AMP."method=$method";
		foreach ($query as $key => $value)
			$uri .= AMP.$key."=".$value;
		return $uri;
	}
	
	static function cp_link_to($method, $query = array()) {
		return BASE.AMP.Qdb_mcp::cp_path_to($method, $query);
	}
}
