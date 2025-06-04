<div class="report-block">
	<h2><?= $this->language->get('report_title') ?></h2>
	<div class="content">
		<div class="content row">
			<label class="col-sm-3"><?= $this->language->get('text_total') ?>:</label>
			<div class="col-sm-9"><?= (int)$session_data['total'] ?></div>
		</div>	
		<div class="content row">
			<label class="col-sm-3"><?= $this->language->get('text_added') ?>:</label>
			<div class="col-sm-9"><?= (int)$session_data['added'] ?></div>
		</div>
		<div class="content row">
			<label class="col-sm-3"><?= $this->language->get('text_updated') ?>:</label>
			<div class="col-sm-9"><?= (int)$session_data['updated'] ?></div>
		</div>
		<div class="content row">
			<label class="col-sm-3"><?= $this->language->get('text_error') ?>:</label>
			<div class="col-sm-9"><?= (int)$session_data['error'] ?></div>
		</div>
		<?php if(!empty($session_data['errors'])): ?>
		<div class="errors-details">
			<h3><?= $this->language->get('text_errors') ?>:</h3>
			<p><?= implode(', ', $session_data['errors']) ?></p>
		</div>
		<?php endif ?>
	</div>
</div>