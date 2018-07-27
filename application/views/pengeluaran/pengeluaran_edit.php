<?php echo form_open('pengeluaran/edit/'.$pengeluaran->id, array('id' => 'FormEditPengeluaran')); ?>
<div class="form-horizontal">
	<div class="form-group">
		<label class="col-sm-3 control-label">Tanggal</label>
		<div class="col-sm-8">
			<?php 
			echo form_input(array(
				'name' => 'tanggal',
				'class' => 'form-control',
				'value' => $pengeluaran->tanggal
			));
			echo form_hidden('id', $pengeluaran->id);
			?>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label">Nama Barang</label>
		<div class="col-sm-8">
			<?php 
			echo form_input(array(
				'name' => 'nama',
				'class' => 'form-control',
				'value' => $pengeluaran->nama
			));
			?>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label">Stock</label>
		<div class="col-sm-8">
			<?php 
			echo form_input(array(
				'name' => 'stock',
				'class' => 'form-control',
				'value' => $pengeluaran->stock
			));
			?>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label">Harga</label>
		<div class="col-sm-8">
			<?php 
			echo form_input(array(
				'name' => 'harga',
				'class' => 'form-control',
				'value' => $pengeluaran->harga
			));
			?>
		</div>
	</div>
	
</div>
<?php echo form_close(); ?>

<div id='ResponseInput'></div>

<script>
$(document).ready(function(){
	var Tombol = "<button type='button' class='btn btn-primary' id='SimpanEditPengeluaran'>Update Data</button>";
	Tombol += "<button type='button' class='btn btn-default' data-dismiss='modal'>Tutup</button>";
	$('#ModalFooter').html(Tombol);

	$('#SimpanEditPengeluaran').click(function(){
		$.ajax({
			url: $('#FormEditPengeluaran').attr('action'),
			type: "POST",
			cache: false,
			data: $('#FormEditPengeluaran').serialize(),
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