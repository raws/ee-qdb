<style type="text/css" media="screen">
	table td > p { margin: 0.5em 0!important; }
	table td > *:last-child { margin-bottom: 0!important; }
</style>

<p style="margin-bottom:20px;"><?=lang("import_quotes_intro");?></p>

<?=form_open_multipart($action_url);?>

<?php
$this->table->set_template($cp_table_template);
$this->table->set_heading(lang("name"), lang("value"));

$this->table->add_row(
	form_label(lang("file"), "file"),
	form_upload("file")
);

$this->table->add_row(
	form_label(lang("columns", "columns")),
	form_input("columns", "member_id, created_at, updated_at, status, body") .
		"<p>" . lang("import_quotes_columns_desc") . "</p><p><strong>" . lang("valid_columns") .
		":</strong> member_id, created_at, updated_at, status, body</p>"
);

$this->table->add_row(
	form_label(lang("default_member"), "default_member_id"),
	form_dropdown("default_member_id", $members, $member_id) .
		"<p>" . lang("default_member_desc") . "</p>"
);

echo $this->table->generate();
?>

<?=form_submit("submit", lang("import_quotes"));?>

<?=form_close();?>
