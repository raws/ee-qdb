<?php if (!defined("BASEPATH")) exit("Direct script access not allowed");

class Qdb {
	var $return_data = "";
	
	function __construct() {
		$this->EE =& get_instance();
	}
	
	function quotes() {
		$this->EE->load->model("quote", "quotes");
		
		$options = array(
			"limit" => $this->EE->TMPL->fetch_param("limit", 0),
			"offset" => 0,
			"order_by" => $this->EE->TMPL->fetch_param("order_by", "created_at"),
			"sort" => $this->EE->TMPL->fetch_param("sort", "desc"),
			"colors" => preg_split("/[\s|]+/", $this->EE->TMPL->fetch_param("colors", "black"))
		);
		
		$vars = array();
		foreach ($this->EE->quotes->all($options) as $quote) {
			$data = array(
				"quote.id" => $quote->quote_id(),
				"quote.created" => $quote->created_at(),
				"quote.updated" => $quote->updated_at(),
				"quote.body" => $quote->body(),
				"quote.lines" => $quote->lines($options),
				"quote.status" => $quote->status()
			);
			
			foreach (get_object_vars($quote->submitter()) as $key => $value)
				$data["quote.submitter.$key"] = $value;
			
			$vars[] = $data;
		}
		
		if (empty($vars)) {
			return $this->EE->TMPL->no_results();
		} else {
			$this->return_data .= $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $vars);
			return $this->return_data;
		}
	}
}
