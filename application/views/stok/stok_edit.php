<?php echo form_open('stok/edit/'.$id_bahan, array('id' => 'FormEditBarang')); ?>
<div class="form-horizontal">
	
	<div class="form-group">
		<label class="col-sm-3 control-label">Nama Menu</label>
		<div class="col-sm-8">
			<span><b><?php echo  $nama ?></b></span>
		</div>
	</div>
	
	
	<div class="form-group">
		<label class="col-sm-3 control-label">Stock</label>
		<div class="col-sm-8">
			<input type="text" name="stock" class="form-control">
			
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label">Harga</label>
		<div class="col-sm-8">
			<input type="text" name="harga" class="form-control">
			
		</div>
	</div>
</div>
<?php echo form_close(); ?>

<div id='ResponseInput'></div>

<script>
$(document).ready(function(){
	var Tombol = "<button type='button' class='btn btn-primary' id='SimpanEditBarang'>Update Data</button>";
	Tombol += "<button type='button' class='btn btn-default' data-dismiss='modal'>Tutup</button>";
	$('#ModalFooter').html(Tombol);

	$('#SimpanEditBarang').click(function(){
		$.ajax({
			url: $('#FormEditBarang').attr('action'),
			type: "POST",
			cache: false,
			data: $('#FormEditBarang').serialize(),
			dataType:'json',
			success: function(json){
				if(json.status == 1){ 
					$('#ResponseInput').html(json.pesan);
					setTimeout(function(){ 
				   		$('#ResponseInput').html('');
				    }, 3000);
					$('#my-grid').DataTable().ajax.reload( null, false );
				}
				else {
					$('#ResponseInput').html(json.pesan);
				}
			}
		});
	});
});
</script>