<?php
$this->cp->add_to_head(<<<EOS
<style type="text/css" media="screen">
	.status { font-weight: bold; }
	.status.open { color: green; }
	.status.closed { color: red; }
</style>
EOS
);

$this->table->set_template($cp_table_template);
$this->table->set_heading(
	lang("quote_id"),
	lang("submitted_by"),
	lang("created"),
	lang("updated"),
	lang("preview"),
	lang("status")
);

if (count($quotes) > 0) {
	foreach ($quotes as $quote) {
		$this->table->add_row(
			'<a href="'.Qdb_mcp::cp_link_to("edit_quote", array("id" => $quote["quote_id"])).'">'.$quote["quote_id"].'</a>',
			'<a href="'.BASE.AMP.'C=myaccount'.AMP.'id='.$quote["member_id"].'">'.$quote["screen_name"].'</a>',
			$quote["created_at"],
			$quote["updated_at"],
			nl2br(htmlspecialchars($quote["body"])),
			'<span class="status '.$quote["status"].'">'.lang($quote["status"]).'</span>'
		);
	}
} else {
	$this->table->add_row(array(
		"data" => lang("no_quotes_in_database"),
		"colspan" => 6,
		"style" => "text-align:center;"
	));
}

echo $this->table->generate();
?>
