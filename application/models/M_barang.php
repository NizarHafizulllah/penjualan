<?php
class M_barang extends CI_Model 
{
	function fetch_data_barang($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
	{
		$sql = "
			SELECT 
				(@row:=@row+1) AS nomor, 
				a.`id_barang`, 
				a.`kode_barang`, 
				a.`nama_barang`,
				IF(a.`total_stok` = 0, 'Kosong', a.`total_stok`) AS total_stok,
				CONCAT('Rp. ', REPLACE(FORMAT(a.`harga`, 0),',','.') ) AS harga,
				a.`diskon`,
				a.`keterangan`,
				b.`kategori` 
			FROM 
				`pj_barang` AS a 
				LEFT JOIN `pj_kategori_barang` AS b ON a.`id_kategori_barang` = b.`id_kategori_barang`  
				, (SELECT @row := 0) r WHERE 1=1 
				AND a.`dihapus` = 'tidak' 
		";

		$data['totalData'] = $this->db->query($sql)->num_rows();
		// echo $this->db->last_query();

		// $this->db->select('a.*, b.kategori as kategori')->from('pj_barang a');
		// $this->db->join('pj_kategori_barang b', 'a.id_kategori_barang=b.id_kategori_barang')
		// $this->db->where('a.dihapus', 'tidak');
		// $data['totalData'] = $this->db->get()->num_rows();
		
		if( ! empty($like_value))
		{
			$sql .= " AND ( ";    
			$sql .= "
				a.`kode_barang` LIKE '%".$this->db->escape_like_str($like_value)."%' 
				OR a.`nama_barang` LIKE '%".$this->db->escape_like_str($like_value)."%'
				
				OR IF(a.`total_stok` = 0, 'Kosong', a.`total_stok`) LIKE '%".$this->db->escape_like_str($like_value)."%' 
				OR CONCAT('Rp. ', REPLACE(FORMAT(a.`harga`, 0),',','.') ) LIKE '%".$this->db->escape_like_str($like_value)."%' 
				OR a.`diskon` LIKE '%".$this->db->escape_like_str($like_value)."%'
				OR a.`keterangan` LIKE '%".$this->db->escape_like_str($like_value)."%' 
				OR b.`kategori` LIKE '%".$this->db->escape_like_str($like_value)."%' 
				
			";
			$sql .= " ) ";
		}
		
		$data['totalFiltered']	= $this->db->query($sql)->num_rows();
		
		$columns_order_by = array( 
			0 => 'nomor',
			1 => 'a.`kode_barang`',
			2 => 'a.`nama_barang`',
			3 => 'b.`kategori`',
			4 => 'a.`total_stok`',
			5 => 'a`harga`',
			6 => 'a`diskon`',
			7 => 'a.`keterangan`'
		);
		
		$sql .= " ORDER BY ".$columns_order_by[$column_order]." ".$column_dir.", nomor ";
		$sql .= " LIMIT ".$limit_start." ,".$limit_length." ";
		
		// echo  $this->db->last_query();

		$data['query'] = $this->db->query($sql);
		return $data;
	}



	

		function get_data_barang($param)
	{
		// print_r($param);
		// exit;

		 extract($param);

		 $kolom = array(0=>"id_barang",
							"nama",
							"stock",
							
		 	);

		 	$this->db->select('a.*, k.kategori as kategori')->from("pj_barang a");
		 	$this->db->join('pj_kategori_barang k','a.id_kategori_barang=k.id_kategori_barang');
		 	
		 	$this->db->where('a.dihapus', 'tidak');


		 

		 if(!empty($searchval)) {
		 	$this->db->where('a.kode_barang', $searchval);
		 	$this->db->or_like("a.nama_barang",$searchval);
		 	$this->db->or_like("k.kategori", $searchval);
		 }

		($param['limit'] != null ? $this->db->limit($param['limit']['end'], $param['limit']['start']) : '');
		//$this->db->limit($param['limit']['end'], $param['limit']['start']) ;
       
       ($param['sort_by'] != null) ? $this->db->order_by($kolom[$param['sort_by']], $param['sort_direction']) :'';
        
		$res = $this->db->get();
		// echo $this->db->last_query(); exit;
 		return $res;
	}


	function fetch_data_stok($param)
	{
		// print_r($param);
		// exit;

		 extract($param);

		 $kolom = array(0=>"id_bahan",
							"nama",
							"stock",
							
		 	);

		 	$this->db->select('*')->from("m_bahan");
		 	


		 

		 if(!empty($searchval)) {
		 	$this->db->where('id_bahan', $searchval);
		 	$this->db->or_like("nama",$searchval);
		 	$this->db->or_where("stock", $searchval);
		 }

		($param['limit'] != null ? $this->db->limit($param['limit']['end'], $param['limit']['start']) : '');
		//$this->db->limit($param['limit']['end'], $param['limit']['start']) ;
       
       ($param['sort_by'] != null) ? $this->db->order_by($kolom[$param['sort_by']], $param['sort_direction']) :'';
        
		$res = $this->db->get();
		// echo $this->db->last_query(); exit;
 		return $res;
	}

	function hapus_barang($id_barang)
	{
		$dt['dihapus'] = 'ya';
		return $this->db
				->where('id_barang', $id_barang)
				->update('pj_barang', $dt);
	}

	function tambah_baru($kode, $nama, $id_kategori_barang, $harga, $diskon, $keterangan)
	{
		$dt = array(
			'kode_barang' => $kode,
			'nama_barang' => $nama,
			'harga' => $harga,
			'diskon' => $diskon,
			'id_kategori_barang' => $id_kategori_barang,
			'keterangan' => $keterangan,
			'dihapus' => 'tidak'
		);

		return $this->db->insert('pj_barang', $dt);
	}

	function cek_kode($kode)
	{
		return $this->db
			->select('id_barang')
			->where('kode_barang', $kode)
			->where('dihapus', 'tidak')
			->limit(1)
			->get('pj_barang');
	}

	function get_baris($id_barang)
	{
		return $this->db
			->select('id_barang, kode_barang, nama_barang, total_stok, harga, diskon, id_kategori_barang, keterangan')
			->where('id_barang', $id_barang)
			->limit(1)
			->get('pj_barang');
	}

	function update_barang($id_barang, $kode_barang, $nama, $id_kategori_barang, $harga, $diskon, $keterangan)
	{
		$dt = array(
			'kode_barang' => $kode_barang,
			'nama_barang' => $nama,
			'harga' => $harga,
			'diskon' => $diskon,
			'id_kategori_barang' => $id_kategori_barang,
			
			'keterangan' => $keterangan
		);

		return $this->db
			->where('id_barang', $id_barang)
			->update('pj_barang', $dt);
	}

	function cari_kode($keyword, $registered)
	{
		$not_in = '';

		$koma = explode(',', $registered);
		if(count($koma) > 1)
		{
			$not_in .= " AND `kode_barang` NOT IN (";
			foreach($koma as $k)
			{
				$not_in .= " '".$k."', ";
			}
			$not_in = rtrim(trim($not_in), ',');
			$not_in = $not_in.")";
		}
		if(count($koma) == 1)
		{
			$not_in .= " AND `kode_barang` != '".$registered."' ";
		}

		$sql = "
			SELECT 
				`kode_barang`, `nama_barang`, `diskon`,  `harga` 
			FROM 
				`pj_barang` 
			WHERE 
				`dihapus` = 'tidak' 
				AND `total_stok` > 0 
				AND ( 
					`kode_barang` LIKE '%".$this->db->escape_like_str($keyword)."%' 
					OR `nama_barang` LIKE '%".$this->db->escape_like_str($keyword)."%' 
				) 
				".$not_in." 
		";

		return $this->db->query($sql);
	}

	function get_stok($kode)
	{
		return $this->db
			->select('nama_barang, total_stok')
			->where('kode_barang', $kode)
			->limit(1)
			->get('pj_barang');
	}

	function get_id($kode_barang)
	{
		return $this->db
			->select('id_barang, nama_barang')
			->where('kode_barang', $kode_barang)
			->limit(1)
			->get('pj_barang');
	}

	function update_stok($id_barang, $jumlah_beli)
	{

		$this->db->where('id_menu', $id_barang);
		$resep = $this->db->get('m_resep')->result_array();

		foreach ($resep as $key) {
			
			$this->db->where('id_bahan', $key['id_bahan']);
			$bahan = $this->db->get('m_bahan')->row_array();
			$stok_dicari = $bahan['stock']-($jumlah_beli*$key['jumlah']);
			$data = array('stock' => $stok_dicari);

				$this->db->where('id_bahan', $key['id_bahan']);
				$bahan = $this->db->update('m_bahan', $data);
				

			}

		return $bahan;
	}


		function update_stok2($id_barang, $jumlah_beli)
	{

		$this->db->where('id_menu', $id_barang);
		$resep = $this->db->get('m_resep')->result_array();

		// print_r($resep);
		foreach ($resep as $key) {
			
			$this->db->where('id_bahan', $key['id_bahan']);
			$bahan = $this->db->get('m_bahan')->row_array();
			

			$stok_dicari = $bahan['stock']+($jumlah_beli*$key['jumlah']);
			$data = array('stock' => $stok_dicari);
				if(!empty($data)){

				$this->db->where('id_bahan', $key['id_bahan']);
				$bahan = $this->db->update('m_bahan', $data);
				// echo $this->db->last_query();
				}

			}

		return $bahan;
	}
}