<?=form_open($action_url);?>

<?=validation_errors();?>

<?php
$this->table->set_template($cp_table_template);
$this->table->set_heading(lang("name"), lang("value"));

$this->table->add_row(
	form_label(lang("submitted_by"), "member_id"),
	form_dropdown("member_id", $members, ($this->input->post("member_id") ? $this->input->post("member_id") : $member_id))
);
$this->table->add_row(
	form_label(lang("created"), "created_at"),
	form_input("created_at", date("Y-m-d H:i:s"))
);
$this->table->add_row(
	form_label(lang("status"), "status"),
	form_dropdown("status", array("open" => lang("open"), "closed" => lang("closed")), ($this->input->post("status") ? $this->input->post("status") : "open"))
);
$this->table->add_row(
	form_label(lang("body"), "body"),
	form_textarea("body", set_value("body"))
);

echo $this->table->generate();
?>

<?=form_submit("submit", lang("add_quote"));?>

<?=form_close();?>
