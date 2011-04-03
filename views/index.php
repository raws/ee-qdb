<?php
$this->cp->add_to_head(<<<EOS
<style type="text/css" media="screen">
	.status { font-weight: bold; }
	.status.open { color: green; }
	.status.closed { color: red; }
	.table-footer { margin-top: 10px; overflow: hidden; text-align: center; }
	.table-footer .left { float: left; }
	.table-footer .right { float: right; }
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

if (isset($quotes) && !empty($quotes)) {
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
		"colspan" => 7,
		"style" => "text-align:center;"
	));
}

echo $this->table->generate();
?>

<?php if (isset($quotes) && !empty($quotes)): ?>
<div class="table-footer">
	<div class="left">
		<p><?=sprintf(lang("showing_x_quotes"), $start, $end, $total);?></p>
	</div>
	<?=$pagination;?>
	<div class="right">
		<?=form_submit(array("name" => "submit", "value" => lang("submit"), "class" => "submit"));?>&nbsp;
		<?=form_dropdown("action", $options);?>
	</div>
</div>
<?php endif; ?>

<?=form_close();?>
