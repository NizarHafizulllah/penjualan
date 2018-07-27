<h1>Penjualan</h1>
<?php if($penjualan->num_rows() > 0) { ?>

	<table class='table table-bordered'>
		<thead>
			<tr>
				<th>No</th>
				<th>Tanggal</th>
				<th>Total Penjualan</th>
				<th>#</th>
			</tr>
		</thead>
		<tbody>

			<?php
			$no = 1;
			$total_penjualan = 0;
			foreach($penjualan->result() as $p)
			{
				echo "
					<tr>
						<td>".$no."</td>
						<td>".date('d F Y', strtotime($p->tanggal))."</td>
						<td>Rp. ".str_replace(",", ".", number_format($p->total_penjualan))."</td>
						<td><a href='".site_url('laporan/detail?tgl='.$p->tanggal)."' class='btn btn-primary'><i class='fa fa-eye'> </i> Detail</a></td>
					</tr>
				";

				$total_penjualan = $total_penjualan + $p->total_penjualan;
				$no++;
			}

			echo "
				<tr>
					<td colspan='2'><b>Total Seluruh Penjualan</b></td>
					<td colspan='2'><b>Rp. ".str_replace(",", ".", number_format($total_penjualan))."</b></td>
				</tr>
			";
			?>
		</tbody>
	</table>

	<p>
		<?php
		$from 	= date('Y-m-d', strtotime($from));
		$to		= date('Y-m-d', strtotime($to));
		?>
		<a href="<?php echo site_url('laporan/pdf/'.$from.'/'.$to); ?>" target='blank' class='btn btn-default'><img src="<?php echo config_item('img'); ?>pdf.png"> Export ke PDF</a>
		<a href="<?php echo site_url('laporan/excel/'.$from.'/'.$to); ?>" target='blank' class='btn btn-default'><img src="<?php echo config_item('img'); ?>xls.png"> Export ke Excel</a>
	</p>
	<br />
<?php } ?>

<?php if($penjualan->num_rows() == 0) { ?>
<div class='alert alert-info'>
Data dari tanggal <b><?php echo $from; ?></b> sampai tanggal <b><?php echo $to; ?></b> tidak ditemukan
</div>
<br />
<?php } ?>



<h1>Pengeluaran</h1>

<?php if($pengeluaran->num_rows() > 0) { ?>

	<table class='table table-bordered'>
		<thead>
			<tr>
				<th>No</th>
				<th>Tanggal</th>
				<th>Total Pengeluaran</th>
				<th>#</th>
			</tr>
		</thead>
		<tbody>

			<?php
			$no = 1;
			$t_pengeluaran = 0;
			foreach($pengeluaran->result() as $p)
			{
				echo "
					<tr>
						<td>".$no."</td>
						<td>".date('d F Y', strtotime($p->tanggal))."</td>
						<td>Rp. ".str_replace(",", ".", number_format($p->total_pembelian))."</td>
						<td><a href='".site_url('laporan/detail_pengeluaran?tgl='.$p->tanggal)."' class='btn btn-primary'><i class='fa fa-eye'> </i> Detail</a></td>
					</tr>
				";

				$t_pengeluaran = $t_pengeluaran + $p->total_pembelian;
				$no++;
			}

			echo "
				<tr>
					<td colspan='2'><b>Total Seluruh Pengeluaran</b></td>
					<td colspan='2'><b>Rp. ".str_replace(",", ".", number_format($t_pengeluaran))."</b></td>
				</tr>
			";
			?>
		</tbody>
	</table>

	<p>
		<?php
		$from 	= date('Y-m-d', strtotime($from));
		$to		= date('Y-m-d', strtotime($to));
		?>
		<a href="<?php echo site_url('laporan/pdf_pengeluaran/'.$from.'/'.$to); ?>" target='blank' class='btn btn-default'><img src="<?php echo config_item('img'); ?>pdf.png"> Export ke PDF</a>
		<a href="<?php echo site_url('laporan/excel_pengeluaran/'.$from.'/'.$to); ?>" target='blank' class='btn btn-default'><img src="<?php echo config_item('img'); ?>xls.png"> Export ke Excel</a>
	</p>
	<br />
<?php } ?>

<?php if($pengeluaran->num_rows() == 0) { ?>
<div class='alert alert-info'>
Data dari tanggal <b><?php echo $from; ?></b> sampai tanggal <b><?php echo $to; ?></b> tidak ditemukan
</div>
<br />
<?php } ?>

<?php 
	if(empty($total_penjualan)){
		$total_penjualan = 0;
	}
	if (empty($t_pengeluaran)) {
		$t_pengeluaran = 0;
	}
$total = $total_penjualan-$t_pengeluaran; ?>

<h1><b>Total = <?php echo "Rp. ".str_replace(",", ".", number_format($total)).",00</b>" ?></h1>