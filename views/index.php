<?php
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
			$quote["quote_id"],
			'<a href="'.BASE.AMP.'C=myaccount'.AMP.'id='.$quote["member_id"].'">'.$quote["screen_name"].'</a>',
			$quote["created_at"],
			$quote["updated_at"],
			nl2br(htmlspecialchars($quote["body"])),
			$quote["status"]
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
