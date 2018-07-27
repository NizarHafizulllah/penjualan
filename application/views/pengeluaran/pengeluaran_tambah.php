<?php echo form_open('pengeluaran/tambah', array('id' => 'FormTambahPengeluaran')); ?>
<table class='table table-bordered' id='TabelTambahPengeluaran'>
	<thead>
		<tr>
			<th>#</th>
			<th>Tanggal</th>
			<th>Nama</th>
			<th>Stock</th>
			<th>Harga</th>
			<th>Batal</th>
		</tr>
	</thead>
	<tbody></tbody>
</table>
<?php echo form_close(); ?>

<button id='BarisBaru' class='btn btn-default'>Baris Baru</button>
<div id='ResponseInput'></div>
<link rel="stylesheet" type="text/css" href="<?php echo config_item('plugin'); ?>datetimepicker/jquery.datetimepicker.css"/>
<script src="<?php echo config_item('plugin'); ?>datetimepicker/jquery.datetimepicker.js"></script>
<script>
$(document).ready(function(){


	var Tombol = "<button type='button' class='btn btn-primary' id='SimpanTambahPengeluaran'>Simpan Data</button>";
	Tombol += "<button type='button' class='btn btn-default' data-dismiss='modal'>Tutup</button>";
	$('#ModalFooter').html(Tombol);

	BarisBaru();

	$('#BarisBaru').click(function(){
		BarisBaru();
	});

	$('#SimpanTambahPengeluaran').click(function(e){
		e.preventDefault();

		if($(this).hasClass('disabled'))
		{
			return false;
		}
		else
		{
			if($('#FormTambahPengeluaran').serialize() !== '')
			{
				$.ajax({
					url: $('#FormTambahPengeluaran').attr('action'),
					type: "POST",
					cache: false,
					data: $('#FormTambahPengeluaran').serialize(),
					dataType:'json',
					beforeSend:function(){
						$('#SimpanTambahPengeluaran').html("Menyimpan Data, harap tunggu ...");
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

						$('#SimpanTambahPengeluaran').html('Simpan Data');
					}
				});
			}
			else
			{
				$('#ResponseInput').html('');
			}
		}
	});

	$("#FormTambahPengeluaran").find('input[type=text],textarea,select').filter(':visible:first').focus();
});

$(document).on('click', '#HapusBaris', function(e){
	e.preventDefault();
	$(this).parent().parent().remove();

	var Nomor = 1;
	$('#TabelTambahPengeluaran tbody tr').each(function(){
		$(this).find('td:nth-child(1)').html(Nomor);
		Nomor++;
	});

	$('#SimpanTambahPengeluaran').removeClass('disabled');
});

function BarisBaru()
{
	var Nomor = $('#TabelTambahPengeluaran tbody tr').length + 1;
	var Baris = "<tr>";
	Baris += "<td>"+Nomor+"</td>";
	Baris += "<td><input type='text' name='tanggal[]' class='form-control input-sm tanggal'><span id='SamaKode'></span></td>";
	Baris += "<td><input type='text' name='nama[]' class='form-control input-sm'></td>";

	Baris += "<td><input type='text' name='stock[]' class='form-control input-sm' ></td>";
	Baris += "<td><input type='text' name='harga[]' class='form-control input-sm' onkeypress='return check_int(event)'></td>";
	Baris += "<td align='center'><a href='#' id='HapusBaris'><i class='fa fa-times' style='color:red;'></i></a></td>";
	Baris += "</tr>";

	$('#TabelTambahPengeluaran tbody').append(Baris);
}

$('.tanggal').datetimepicker({
	lang:'en',
	timepicker:false,
	format:'Y-m-d',
	closeOnDateSelect:true
});



function check_int(evt) {
	var charCode = ( evt.which ) ? evt.which : event.keyCode;
	return ( charCode >= 48 && charCode <= 57 || charCode == 8 );
}
</script>