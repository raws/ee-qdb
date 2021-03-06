<?php
if (!isset($quote)) {
	$quote["member_id"] = ($this->input->post("member_id") ? $this->input->post("member_id") : $member_id);
	$quote["created_at"] = date("Y-m-d H:i:s");
	$quote["status"] = ($this->input->post("status") ? $this->input->post("status") : "open");
	$quote["body"] = set_value("body");
}
?>

<?=form_open($action_url);?>

<?=validation_errors();?>

<?php
$this->table->set_template($cp_table_template);
$this->table->set_heading(lang("name"), lang("value"));

$this->table->add_row(
	form_label(lang("submitted_by"), "member_id"),
	form_dropdown("member_id", $members, $quote["member_id"])
);
$this->table->add_row(
	form_label(lang("created"), "created_at"),
	form_input("created_at", $quote["created_at"])
);
$this->table->add_row(
	form_label(lang("status"), "status"),
	form_dropdown("status", array("open" => lang("open"), "closed" => lang("closed")), $quote["status"])
);
$this->table->add_row(
	form_label(lang("body"), "body"),
	form_textarea("body", $quote["body"])
);

echo $this->table->generate();
?>

<?=form_submit("submit", lang("save_quote"));?>

<?=form_close();?>
