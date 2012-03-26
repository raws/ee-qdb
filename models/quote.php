<?php if (!defined("BASEPATH")) exit("Direct script access not allowed");

class Quote extends CI_Model {
	const table = "qdb_quotes";
	const regex_nickname = "[\w_|\^`\[\]-]+";
	
	var $quote_id;
	var $member_id;
	var $created_at;
	var $updated_at;
	var $status;
	var $body;
	
	var $record;
	var $submitter;
	var $lines;
	var $colors;
	
	var $count;
	
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
	
	function lines($options = array()) {
		if (!$this->lines) {
			$options = $this->lines_options($options);
			$body = $this->record->body;
			$lines = array();

			foreach (preg_split("/[\r\n]+/", $body) as $line) {
				$data = array();
				
				if (preg_match("/^\s*<?[+@~%]*(".Quote::regex_nickname.")[>:]*\s*(.*)$/", $line, $match)) {
					$data["line.type"] = "message";
					$data["line.nick"] = $match[1];
					$data["line.message"] = $match[2];
				} else if (preg_match("/^\s*\*\s*(".Quote::regex_nickname.")\s+(.*)$/", $line, $match)) {
					$data["line.type"] = "action";
					$data["line.nick"] = $match[1];
					$data["line.message"] = $match[2];
				} else if (preg_match("/^\s*\*+\s*(".Quote::regex_nickname.")\s+(.*)$/", $line, $match)) {
					$data["line.type"] = "event";
					$data["line.nick"] = $match[1];
					$data["line.message"] = $match[2];
				} else {
					$data["line.type"] = "unknown";
					$data["line.message"] = $line;
				}
				
				if (isset($data["line.nick"])) {
					$data["line.color"] = $this->nick_to_color($data["line.nick"], $options["colors"]);
				}
				
				$lines[] = $data;
			}
			
			$this->lines = $lines;
		}
		
		return $this->lines;
	}
	
	function submitter() {
		if (!$this->submitter) {
			$this->load->model("member_model");
			$this->submitter = $this->member_model->get_member_data($this->member_id())->row();
		}
		
		return $this->submitter;
	}
	
	function count($options = array()) {
		$options = $this->query_options($options);
		
		$sql = "SELECT COUNT(*) AS count FROM " . $this->db->protect_identifiers(Quote::table, TRUE) . " ";
		$sql .= $this->build_query_conditions($options);
		
		$query = $this->db->query($sql);
		$this->count = intval($query->row()->count);
		
		return $this->count;
	}
	
	function all($options = array()) {
		$options = $this->query_options($options);
		$this->count($options);
		
		$sql = "SELECT * FROM " . $this->db->protect_identifiers(Quote::table, TRUE) . " ";
		$sql .= $this->build_query_conditions($options);
		$sql .= "ORDER BY " . $this->db->protect_identifiers($options["order_by"]) . " " . $options["sort"] . " ";
		if ($options["offset"] > 0) {
			$sql .= "LIMIT " . $options["offset"] . ", " . $options["limit"];
		} else {
			$sql .= "LIMIT " . $options["limit"];
		}
		
		$query = $this->db->query($sql);
		$result = array();
		
		foreach ($query->result() as $quote) {
			$result[] = new Quote($quote);
		}
		
		return $result;
	}
	
	function find_by_quote_id($quote_id) {
		$query = $this->db->get_where(Quote::table, array("quote_id" => $quote_id, "status" => "open"), 1);
		$result = array();
		
		foreach ($query->result() as $quote) {
			$result[] = new Quote($quote);
		}
		
		$this->count = count($result);
		
		return $result;
	}
	
	private function build_query_conditions($options) {
		$sql = "WHERE `status` = 'open' ";
		if (array_key_exists("search", $options) && $options["search"] !== FALSE) {
			$sql .= "AND MATCH (body) AGAINST (" . $this->db->escape($options["search"]) . " IN BOOLEAN MODE) ";
		}
		return $sql;
	}
	
	private function query_options($options = array()) {
		$options = array_merge(array(
			"limit" => 10,
			"offset" => 0,
			"order_by" => "created_at",
			"sort" => "desc"
		), $options);
		
		if (in_array(strtolower($options["order_by"]), array("quote_id", "created_at", "updated_at")) !== TRUE) {
			$options["order_by"] = "created_at";
		}
		
		if (in_array(strtolower($options["sort"]), array("desc", "asc", "random")) !== TRUE) {
			$options["sort"] = "desc";
		}
		
		if (strtolower($options["sort"]) == "random") {
			$options["order_by"] = "RAND()";
			$options["sort"] = "";
		}
		
		return $options;
	}
	
	private function lines_options($options = array()) {
		return array_merge(array(
			"colors" => array("#ffaeae", "#ffc9ad", "#ffe4ad", "#ffffad", "#e4ffad", "#c9ffad",
			                  "#adffe4", "#adffff", "#ade4ff", "#c9adff", "#a4adff", "#ffadff")
		), $options);
	}
	
	private function nick_to_color($nick, $colors) {
		if (!$this->colors) {
			$this->colors = array();
		}
		
		$nick = strrev(strtolower(preg_replace("/[|`_^].*$/", "", $nick))); // Remove tail ends of ghosted or compound nicks
		if (!array_key_exists($nick, $this->colors)) {
			$this->colors[$nick] = $colors[hexdec(hash("crc32", $nick)) % count($colors)];
		}
		
		return $this->colors[$nick];
	}
}
