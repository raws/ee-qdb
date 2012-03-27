<?php if (!defined("BASEPATH")) exit("Direct script access not allowed");

class Qdb {
	var $return_data = "";
	
	function __construct() {
		$this->EE =& get_instance();
	}
	
	function quotes() {
		$this->EE->load->model("quote", "quotes");
		
		$options = array(
			"quote_id" => $this->EE->TMPL->fetch_param("quote_id"),
			"limit" => $this->EE->TMPL->fetch_param("limit", 10),
			"order_by" => $this->EE->TMPL->fetch_param("order_by", "created_at"),
			"sort" => $this->EE->TMPL->fetch_param("sort", "desc"),
			"colors" => preg_split("/[\s|]+/", $this->EE->TMPL->fetch_param("colors", "black")),
			"page" => $this->EE->TMPL->fetch_param("page", 0),
			"pages" => $this->EE->TMPL->fetch_param("pages", 3)
		);
		$options["offset"] = $options["limit"] * $options["page"];
		
		if ($options["quote_id"] !== FALSE) {
			$quotes = $this->EE->quotes->find_by_quote_id($options["quote_id"]);
		} else {
			$quotes = $this->EE->quotes->all($options);
		}
		
		$vars = array(
			"quotes" => array(),
			"count" => $this->EE->quotes->count
		);
		
		foreach ($quotes as $quote) {
			$data = array(
				"quote.id" => $quote->quote_id(),
				"quote.created" => $quote->created_at(),
				"quote.updated" => $quote->updated_at(),
				"quote.body" => $quote->body(),
				"quote.lines" => $quote->lines($options),
				"quote.status" => $quote->status()
			);
			
			foreach (get_object_vars($quote->submitter()) as $key => $value) {
				$data["quote.submitter.$key"] = $value;
			}
			
			$vars["quotes"][] = $data;
		}
		
		$vars = array($vars); // Create a single-element array so we only render everything once
		$this->return_data .= $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $vars);
		return $this->return_data;
	}
	
	function submit_form_tag() {
		return $this->EE->functions->form_declaration(array(
			"id" => $this->EE->TMPL->form_id,
			"class" => $this->EE->TMPL->form_class,
			"secure" => TRUE,
			"hidden_fields" => array(
				"ACT" => $this->EE->functions->fetch_action_id("Qdb", "submit"),
				"RET" => $this->EE->TMPL->fetch_param("return", $this->EE->functions->fetch_site_index())
			)
		));
	}
	
	function submit() {
		if (in_array($this->EE->session->userdata["group_id"], array(2, 3, 4)) !== FALSE) {
			$this->EE->functions->redirect($this->EE->functions->fetch_site_index());
			return;
		}
		
		if (($quote_body = $this->EE->input->post("quote_body")) && !empty($quote_body) && !preg_match("/^\s*$/", $quote_body)) {
			$this->EE->db->insert("qdb_quotes", array(
				"member_id" => $this->EE->session->userdata["member_id"],
				"created_at" => date("Y-m-d H:i:s"),
				"status" => "open",
				"body" => $quote_body
			));
			$this->EE->functions->redirect($this->EE->functions->create_url($this->EE->input->post("RET")));
		} else {
			$this->EE->functions->redirect($this->EE->functions->fetch_site_index());
		}
	}
}
