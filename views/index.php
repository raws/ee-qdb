<?php
$this->cp->add_to_head(<<<EOS
<style type="text/css" media="screen">
	.status { font-weight: bold; }
	.status.open { color: green; }
	.status.closed { color: red; }
	.table-footer { margin-top: 10px; overflow: hidden; text-align: center; }
	.table-footer .left { float: left; }
	.table-footer .right { float: right; }
	.filters { margin-bottom: 20px; }
	#quote-filters-header { margin-bottom: 10px; }
	.filters th:first-child { width: 100px; }
	.filters td:first-child { text-align: right; }
	#quotes tr > *:first-child,
	#quotes tr > *:nth-child(2),
	#quotes tr > *:nth-child(3),
	#quotes tr > *:nth-child(4),
	#quotes tr > *:nth-child(6),
	#quotes tr > *:nth-child(7) { text-align: center; }
	#quotes tr > *:first-child { width: 60px; }
	#quotes tr > *:nth-child(2) { width: 100px; }
	#quotes tr > *:nth-child(3),
	#quotes tr > *:nth-child(4) { width: 130px; }
	#quotes tr > *:nth-child(6) { width: 70px; }
	#quotes tr > *:nth-child(7) { width: 25px; }
</style>
EOS
);

$filters_active = $this->input->get_post("fid") ||
									$this->input->get_post("fmid") ||
									$this->input->get_post("fst") ||
									$this->input->get_post("fb");
?>

<h4 id="quote-filters-header"><a id="quote-filters-toggle" href="#" title="<?=lang("click_to_expand_filters");?>"><?=lang("show_filters");?></a></h4>
<div id="quote-filters" class="filters" style="display:<?=$filters_active ? "block" : "none";?>;">
	<?php
	echo form_open($action_url, array("method" => "get", "id" => "filter-form"));
	echo form_hidden("S", "0");
	echo form_hidden("D", "cp");
	echo form_hidden("C", "addons_modules");
	echo form_hidden("M", "show_module_cp");
	echo form_hidden("module", "qdb");
	
	$this->table->set_template($cp_table_template);
	$this->table->set_heading(lang("name"), lang("value"));
	
	$this->table->add_row(
		lang("filter_quote_id_is"),
		form_input("fid", $this->input->get_post("fid"))
	);
	
	$fmid = $this->input->get_post("fmid");
	$this->table->add_row(
		lang("filter_submitted_by"),
		form_dropdown("fmid", $members, $this->input->get_post("fmid"))
	);
	
	$this->table->add_row(
		lang("filter_status_is"),
		form_dropdown("fst", array("" => "", "open" => lang("open"), "closed" => lang("closed")), $this->input->get_post("fst"))
	);
	
	$this->table->add_row(
		lang("filter_body_contains"),
		form_input("fb", $this->input->get_post("fb"))
	);
	
	echo $this->table->generate();
	$this->table->clear();
	?>
	
	<?=form_submit(array("name" => "submit", "value" => lang("filter_quotes"), "class" => "submit"));?>
	<?=form_reset(array("name" => "reset", "value" => lang("clear_filters"), "id" => "clear-filters", "class" => "submit"));?>
	<?=form_close();?>
</div>

<div id="quotes">
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
			"data" => ($filters_active ? lang("no_quotes_match_filters") : lang("no_quotes_in_database")),
			"colspan" => 7,
			"style" => "text-align:center;"
		));
	}

	echo $this->table->generate();
	?>
</div>

<?php if (isset($quotes) && !empty($quotes) && $total > $per_page): ?>
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
