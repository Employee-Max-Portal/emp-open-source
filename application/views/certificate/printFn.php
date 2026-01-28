<style type="text/css">
		@page {
			margin: 0;
			<?php if ($template['page_layout'] == 2) { ?>
			size: A4 landscape;
			<?php } else { ?>
			size: A4 portrait;
			<?php } ?>
		}
		.certificate{
			<?php if ($template['page_layout'] == 2) { ?>
			width: 297mm;
			height: 210mm;
			<?php } else { ?>
			width: 210mm;
			height: 297mm;
			<?php } ?>
			box-sizing: border-box;
			<?php if (empty($template['background'])) { ?>
					background: #fff;
			<?php } else { ?>
				background-image: url("<?=base_url('uploads/certificate/' . $template['background'])?>");
				background-repeat: no-repeat !important;
				background-size: 100% 100% !important;
			<?php } ?>
			padding: <?=$template['top_space'] . 'px ' . $template['right_space'] . 'px ' . $template['bottom_space'] . 'px ' . $template['left_space'] . 'px'?>;
			font-family: Arial;
			page-break-after: always;
			page-break-inside: avoid;
		}
		.certificate:last-child {
			page-break-after: avoid;
		}
		@media print {
			.certificate{
				-webkit-print-color-adjust: exact !important; 
				color-adjust: exact !important;
			}
		}
</style>
<?php
if (count($user_array)) {
	foreach ($user_array as $sc => $userID) {
	?>

<div class="certificate">
	<?=$this->certificate_model->tagsReplace($user_type, $userID, $template, $print_date)?>
</div> 
<?php } } ?>
