	<!-- Javascript Body Custom Added -->
	<!-- Modal Delete -->
	<div id="deleteModal" class="modal fade deleteModal" tabindex="-1" role="dialog" aria-hidden="true" aria-labelledby="deleteModalLabel">
    	<div class="modal-dialog" role="document">
        	<div class="modal-content">
            	<div class="modal-header border-0">
                	<h5 class="modal-title" id="deleteModalLabel"><i class="fas fa-trash-alt mr-3 text-danger"></i>Confirm Delete</h5>
                	<button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            	</div>
            	<div class="modal-body">
					<p class="font-weight-bold mb-2">Are you sure you want to delete this?</p>
				</div>
            	<div class="modal-footer border-0">
                	<button type="button" class="btn btn-light text-muted" data-dismiss="modal">Cancel</button>
					<button type="button" id="articleDelete" class="btn btn-danger px-4 ml-2" data-dismiss="modal" data-file="">Delete</button>
            	</div>
        	</div>
    	</div>
	</div>
	<script>
		$(document).ready(function() {
			$('#articleDelete').click(function () {
				var id = $(this).attr("data-id");
				var params = 'id=' + id;
				
				$.ajax({
					type: "GET",
					url: '<?php echo APP_PATH; ?>panel/ajaxDeleteArticle',
					data: params,
					success: function(response) {
						if (response != 0) {
							var alert = JSON.parse(response);
							window.location.replace("<?php echo APP_PATH; ?>panel/manageBlog?action=delete" + "&status=" + alert['status'] + '&message=' + alert['message']);
						} else {
							alert('JavaScript: An unexpected response error occurred!');
						}
					},
					error: function(response) {
						alert('JavaScript: An unexpected error occurred!');
					}
				});
			});

			$('.deleteArticle').click(function () {
				var id = $(this).attr("data-id");
				$('#articleDelete').attr("data-id", id);
			});

			$('#collapseOne').show();
			$('#collapseOne a:nth-child(4)').addClass("active");
		});
	</script>
	<!-- DataTables.net -->
    <script src="../admin/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../admin/vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#dataTableOne').dataTable({
                pageLength: 10,
                columnDefs: [{
                    targets: 'noSort',
                    orderable: false,
                }],
				order: [[0, 'desc']],
            });
        });
    </script>
