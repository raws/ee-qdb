<?php if (!defined("BASEPATH")) exit("Direct script access not allowed");

class Qdb {
	var $return_data = "";
	
	function __construct() {
		$this->EE =& get_instance();
	}
	
	function entries() {
		$this->EE->load->model("quote");
		
		$limit = $this->EE->TMPL->fetch_param("limit", 0);
		$offset = 0;
		
		$vars = array();
		foreach (Quote::all($limit, $offset) as $quote) {
			$data = array(
				"quote.id" => $quote->quote_id(),
				"quote.created" => $quote->created_at(),
				"quote.updated" => $quote->updated_at(),
				"quote.body" => $quote->body(),
				"quote.status" => $quote->status()
			);
			
			foreach (get_object_vars($quote->submitter()) as $key => $value)
				$data["quote.submitter.$key"] = $value;
			
			$vars[] = $data;
		}
		
		$this->return_data .= $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $vars);
		return $this->return_data;
	}
}
