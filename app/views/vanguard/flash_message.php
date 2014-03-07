<?php
/**
 * @var string $userMessage
 * @var string $title
 */
$userMessage = Session::get('userMessage');
$title = Session::get('title');
if (!$userMessage) {
	return;
}
?>

<div class="modal fade" id="userMessageModal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title text-center"><?= $title ?></h4>
			</div>
			<div class="modal-body">
				<p class="text-center"><?= $userMessage ?></p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
			</div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div><!-- /.modal -->

