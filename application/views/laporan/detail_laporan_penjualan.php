
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
				<th width="26%">No. Nota</th>
				<th width="15%">Total</th>
				<th width="65%">Detail</th>
			</tr>
		</thead>
		<tbody>
			<?php 
			$no = 0;
			$total = 0;
			foreach ($data as $key) { 
				$no++;
				$total = $total+$key['grand_total'];
				?>
				<tr>
					<td><?php echo $no; ?></td>
					<td><?php echo $key['nomor_nota'] ?></td>
					<td><?php echo "<b>Rp. ".str_replace(",", ".", number_format($key['grand_total']))."</b>" ?></td>
					<td><?php foreach ($key['detail'] as $key) { ?>
					<?php echo $key['jumlah_beli'].' '.$key['menu'].' ('.str_replace(",", ".", number_format($key['total'])).')<br>' ?>	
				<?php	} ?></td>
				</tr>
				
		<?php	} ?>
				<tr>
				<th width="30%" colspan="2">Total</th>
				<th width="15%"><?php echo 'Rp. '.str_replace(",", ".", number_format($total)); ?></th>
				<th width="65%">&nbsp;</th>
			</tr>
		</tbody>
	</table>
			</div>
		</div>
	</div>
</div>

