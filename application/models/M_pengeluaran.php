<?php
class M_pengeluaran extends CI_Model 
{
	function get_all()
	{
		return $this->db
			->select('id_kategori_barang, kategori')
			->where('dihapus', 'tidak')
			->order_by('kategori', 'asc')
			->get('pj_kategori_barang');
	}


	function get_bahan()
	{
		return $this->db
			->select('id_bahan, nama')
			->order_by('nama', 'asc')
			->get('m_bahan');
	}

	function fetch_data_pengeluaran($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
	{
		$sql = "
			SELECT 
				(@row:=@row+1) AS nomor,
				id, 
				nama, 
				stock,
				tanggal,
				harga
			FROM 
				`barang_masuk`, (SELECT @row := 0) r WHERE 1=1 
		";
		
		$data['totalData'] = $this->db->query($sql)->num_rows();
		
		if( ! empty($like_value))
		{
			$sql .= " AND ( ";    
			$sql .= "
				nama LIKE '%".$this->db->escape_like_str($like_value)."%' 
			";
			$sql .= " ) ";
		}
		
		$data['totalFiltered']	= $this->db->query($sql)->num_rows();
		
		$columns_order_by = array( 
			0 => 'nomor',
			1 => 'nama'
		);
		
		$sql .= " ORDER BY ".$columns_order_by[$column_order]." ".$column_dir.", nomor ";
		$sql .= " LIMIT ".$limit_start." ,".$limit_length." ";
		
		$data['query'] = $this->db->query($sql);
		return $data;
	}

	function tambah_kategori($kategori)
	{
		$dt = array(
			'kategori' => $kategori,
			'dihapus' => 'tidak'
		);

		return $this->db->insert('pj_kategori_barang', $dt);
	}

	function hapus_kategori($id_kategori_barang)
	{
		$dt = array(
			'dihapus' => 'ya'
		);

		return $this->db
			->where('id_kategori_barang', $id_kategori_barang)
			->update('pj_kategori_barang', $dt);
	}

	function get_baris($id_kategori_barang)
	{
		return $this->db
			->select('id_kategori_barang, kategori')
			->where('id_kategori_barang', $id_kategori_barang)
			->limit(1)
			->get('pj_kategori_barang');
	}

	function update_kategori($id_kategori_barang, $kategori)
	{
		$dt = array(
			'kategori' => $kategori
		);

		return $this->db
			->where('id_kategori_barang', $id_kategori_barang)
			->update('pj_kategori_barang', $dt);
	}
}