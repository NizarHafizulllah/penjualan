<?php echo form_open('resep/edit/'.$id, array('id' => 'FormTambahBarang')); ?>
<table class='table table-bordered' id='TabelTambahBarang'>
	<thead>
		<tr>
			<th>#</th>
			<th>Bahan</th>
			<th>Jumlah</th>
			<th>Batal</th>
		</tr>
	</thead>
	<tbody></tbody>
</table>
<?php echo form_close(); ?>

<button id='BarisBaru' class='btn btn-default'>Baris Baru</button>
<div id='ResponseInput'></div>
<span id="coba"></span>
<script>
$(document).ready(function(){
	
	<?php if(!empty($bahan)){ 
		$no = 0;
		foreach ($bahan as $k) { ?>
		
			console.log('<?php echo $no.' '.$k['jumlah'].' '.$k['id_resep']; ?>');
		BarisBaru();
		$('.id-resep:eq(<?php echo $no; ?>)').val(<?php echo $k['id_resep'] ?>);
		$('.id-bahan:eq(<?php echo $no; ?>)').val(<?php echo $k['id_bahan'] ?>);
		$('.jumlah:eq(<?php echo $no; ?>)').val(<?php echo $k['jumlah'] ?>);

		
		
	<?php $no++;	} }else{ ?>
		BarisBaru();

<?php	} ?>
  

	var Tombol = "<button type='button' class='btn btn-primary' id='SimpanTambahBarang'>Simpan Data</button>";
	Tombol += "<button type='button' class='btn btn-default' data-dismiss='modal'>Tutup</button>";
	$('#ModalFooter').html(Tombol);

	

	$('#BarisBaru').click(function(){
		BarisBaru();
	});

	$('#SimpanTambahBarang').click(function(e){
		e.preventDefault();

		if($(this).hasClass('disabled'))
		{
			return false;
		}
		else
		{
			if($('#FormTambahBarang').serialize() !== '')
			{
				$.ajax({
					url: $('#FormTambahBarang').attr('action'),
					type: "POST",
					cache: false,
					data: $('#FormTambahBarang').serialize(),
					dataType:'json',
					beforeSend:function(){
						$('#SimpanTambahBarang').html("Menyimpan Data, harap tunggu ...");
					},
					success: function(json){
						if(json.status == 1){ 
							$('.modal-dialog').removeClass('modal-lg');
							$('.modal-dialog').addClass('modal-sm');
							$('#ModalHeader').html('Sukses !');
							$('#ModalContent').html(json.pesan);
							$('#ModalFooter').html("<button type='button' class='btn btn-primary' data-dismiss='modal'>Ok</button>");
							$('#ModalGue').modal('show');
							$('#my-grid').DataTable().ajax.reload( null, false );
						}
						else {
							$('#ResponseInput').html(json.pesan);
						}

						$('#SimpanTambahBarang').html('Simpan Data');
					}
				});
			}
			else
			{
				$('#ResponseInput').html('');
			}
		}
	});

	$("#FormTambahBarang").find('input[type=text],textarea,select').filter(':visible:first').focus();
});

$(document).on('click', '#HapusBaris', function(e){
	e.preventDefault();
	$(this).parent().parent().remove();

	var Nomor = 1;
	$('#TabelTambahBarang tbody tr').each(function(){
		$(this).find('td:nth-child(1)').html(Nomor);
		Nomor++;
	});

	$('#SimpanTambahBarang').removeClass('disabled');
});

function BarisBaru()
{
	var Nomor = $('#TabelTambahBarang tbody tr').length + 1;
	var Baris = "<tr>";
	Baris += "<td>"+Nomor+"</td>";
	Baris += "<td>";
	Baris += "<select name='id_bahan[]' class='id-bahan form-control input-sm' style='width:100px;'>";
	Baris += "<option value=''></option>";

	<?php 
	if($arr_bahan->num_rows() > 0)
	{
		foreach($arr_bahan->result() as $k) { ?>
			Baris += "<option value='<?php echo $k->id_bahan; ?>'><?php echo $k->nama; ?></option>";
		<?php }
	}
	?>

	Baris += "</select>";
	Baris += "</td>";

	Baris += "<td><input type='hidden' name='id_resep[]' class='id-resep'><input type='hidden' name='id_menu[]' value='<?php echo $id ?>' ><input type='text' name='jumlah[]' class='jumlah form-control input-sm' onkeypress='return check_int(event)'></td>";
	Baris += "<td align='center'><a href='#' id='HapusBaris'><i class='fa fa-times' style='color:red;'></i></a></td>";
	Baris += "</tr>";

	$('#TabelTambahBarang tbody').append(Baris);
}

function check_int(evt) {
	var charCode = ( evt.which ) ? evt.which : event.keyCode;
	return ( charCode >= 48 && charCode <= 57 || charCode == 8 );
}
</script>