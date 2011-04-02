<?php
$this->cp->add_to_head(<<<EOS
<style type="text/css" media="screen">
	.status { font-weight: bold; }
	.status.open { color: green; }
	.status.closed { color: red; }
	.tableFooter { margin-top: 20px; }
</style>
EOS
);
?>

<?=form_open($action_url);?>

<?php
$this->table->set_template($cp_table_template);
$this->table->set_heading(
	lang("quote_id"),
	lang("submitted_by"),
	lang("created"),
	lang("updated"),
	lang("preview"),
	lang("status"),
	form_checkbox("select_all", "true", FALSE, 'id="select_all" class="toggle_all"')
);

if (count($quotes) > 0) {
	foreach ($quotes as $quote) {
		$this->table->add_row(
			'<a href="'.Qdb_mcp::cp_link_to("edit_quote", array("id" => $quote["quote_id"])).'">'.$quote["quote_id"].'</a>',
			'<a href="'.BASE.AMP.'C=myaccount'.AMP.'id='.$quote["member_id"].'">'.$quote["screen_name"].'</a>',
			$quote["created_at"],
			$quote["updated_at"],
			nl2br(htmlspecialchars($quote["body"])),
			'<span class="status '.$quote["status"].'">'.lang($quote["status"]).'</span>',
			form_checkbox($quote["toggle"])
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

<div class="tableFooter">
	<div class="tableSubmit">
		<?=form_submit(array("name" => "submit", "value" => lang("submit"), "class" => "submit"));?>&nbsp;
		<?=form_dropdown("action", $options);?>
	</div>
</div>

<?=form_close();?>
