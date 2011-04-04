<?php if (!defined("BASEPATH")) exit("Direct script access not allowed");

class Quote extends CI_Model {
	const table = "qdb_quotes";
	
	var $quote_id;
	var $member_id;
	var $created_at;
	var $updated_at;
	var $status;
	var $body;
	
	var $record;
	var $submitter;
	
	function __construct($record = null) {
		parent::__construct();
		
		if ($record !== null)
			$this->record = $record;
	}
	
	function quote_id() {
		if (!$this->quote_id)
			$this->quote_id = $this->record->quote_id;
		return $this->quote_id;
	}
	
	function member_id() {
		if (!$this->member_id)
			$this->member_id = $this->record->member_id;
		return $this->member_id;
	}
	
	function created_at() {
		if (!$this->created_at)
			$this->created_at = strtotime($this->record->created_at);
		return $this->created_at;
	}
	
	function updated_at() {
		if (!$this->updated_at)
			$this->updated_at = strtotime($this->record->updated_at);
		return $this->updated_at;
	}
	
	function status() {
		if (!$this->status)
			$this->status = $this->record->status;
		return $this->status;
	}
	
	function body() {
		if (!$this->body)
			$this->body = nl2br(htmlentities($this->record->body));
		return $this->body;
	}
	
	function submitter() {
		if (!$this->submitter) {
			$this->load->model("member_model");
			$this->submitter = $this->member_model->get_member_data($this->member_id())->row();
		}
		
		return $this->submitter;
	}
	
	function all($options = array()) {
		$options = $this->query_options($options);
		
		$this->db->order_by($options["order_by"], $options["sort"]);
		$query = $this->db->get(Quote::table, $options["limit"], $options["offset"]);
		$result = array();
		
		foreach ($query->result() as $quote) {
			$result[] = new Quote($quote);
		}
		
		return $result;
	}
	
	private function query_options($options = array()) {
		$defaults = array(
			"limit" => 10,
			"offset" => 0,
			"order_by" => "created_at",
			"sort" => "desc"
		);
		return array_merge($defaults, $options);
	}
}
