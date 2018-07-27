<?php $this->load->view('include/header'); ?>
<?php $this->load->view('include/navbar'); ?>

<?php
$level = $this->session->userdata('ap_level');
?>


<div class="container">
	<div class="panel panel-default">
		<div class="panel-body">
			<h5><i class='fa fa-file-text-o fa-fw'></i> Detail Penjualan Tanggal <?php echo $tgl; ?></h5>
			<hr />

			

			<br />
			<div id='result'>
				
	<table class='table table-bordered' width="100%">
		<thead>
			<tr>
				<th width="4%">No</th>
				<th width="26%">Nama Barang</th>
				<th width="15%">Qty</th>
				<th width="65%">Cost</th>
			</tr>
		</thead>
		<tbody>
			<?php 
			$no = 0;
			$total = 0;
			foreach ($data as $key) { 
				$no++;
				$total = $total+$key['harga'];
				?>
				<tr>
					<td><?php echo $no; ?></td>
					<td><?php echo $key['nama'] ?></td>
					<td><?php echo $key['stock'] ?></td>
					<td><?php echo 'Rp. '.str_replace(",", ".", number_format($key['harga'])) ?></td>
				</tr>
				
		<?php	} ?>
				<tr>
				<th width="30%" colspan="3">Total</th>
				<th width="15%"><?php echo 'Rp. '.str_replace(",", ".", number_format($total)); ?></th>
				
			</tr>
		</tbody>
	</table>
			</div>
		</div>
	</div>
</div>

