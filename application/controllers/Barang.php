<?php
defined('BASEPATH') OR exit('No direct script access allowed');



class Barang extends MY_Controller 
{
	public function index()
	{
		$this->load->view('barang/barang_data');
	}

	public function barang_json()
	{
		$this->load->model('m_barang');
		$level 			= $this->session->userdata('ap_level');

		$requestData	= $_REQUEST;
		$fetch			= $this->m_barang->fetch_data_barang($requestData['search']['value'], $requestData['order'][0]['column'], $requestData['order'][0]['dir'], $requestData['start'], $requestData['length']);
		
		$totalData		= $fetch['totalData'];
		$totalFiltered	= $fetch['totalFiltered'];
		$query			= $fetch['query'];

		$data	= array();
		foreach($query->result_array() as $row)
		{ 
			$nestedData = array(); 

			$nestedData[]	= $row['nomor'];
			$nestedData[]	= $row['kode_barang'];
			$nestedData[]	= $row['nama_barang'];
			$nestedData[]	= $row['kategori'];
			$nestedData[]	= $row['harga'];
			$nestedData[]	= $row['diskon'];
			$nestedData[]	= preg_replace("/\r\n|\r|\n/",'<br />', $row['keterangan']);

			if($level == 'admin' OR $level == 'inventory')
			{
				$nestedData[]	= "<a href='".site_url('barang/edit/'.$row['id_barang'])."' id='EditBarang'><i class='fa fa-pencil'></i> Edit</a>";
				$nestedData[]	= "<a href='".site_url('barang/hapus/'.$row['id_barang'])."' id='HapusBarang'><i class='fa fa-trash-o'></i> Hapus</a>";
			}

			$data[] = $nestedData;
		}

		$json_data = array(
			"draw"            => intval( $requestData['draw'] ),  
			"recordsTotal"    => intval( $totalData ),  
			"recordsFiltered" => intval( $totalFiltered ), 
			"data"            => $data
			);

		echo json_encode($json_data);
	}

	public function hapus($id_barang)
	{
		$level = $this->session->userdata('ap_level');
		if($level == 'admin' OR $level == 'inventory')
		{
			if($this->input->is_ajax_request())
			{
				$this->load->model('m_barang');
				$hapus = $this->m_barang->hapus_barang($id_barang);
				if($hapus)
				{
					echo json_encode(array(
						"pesan" => "<font color='green'><i class='fa fa-check'></i> Data berhasil dihapus !</font>
					"));
				}
				else
				{
					echo json_encode(array(
						"pesan" => "<font color='red'><i class='fa fa-warning'></i> Terjadi kesalahan, coba lagi !</font>
					"));
				}
			}
		}
	}

	public function tambah()
	{
		$level = $this->session->userdata('ap_level');
		if($level == 'admin' OR $level == 'inventory')
		{
			if($_POST)
			{
				$this->load->library('form_validation');

				$no = 0;
				foreach($_POST['kode'] as $kode)
				{
					$this->form_validation->set_rules('kode['.$no.']','Kode Barang #'.($no + 1),'trim|required|alpha_numeric|max_length[40]|callback_exist_kode[kode['.$no.']]');
					$this->form_validation->set_rules('nama['.$no.']','Nama Barang #'.($no + 1),'trim|required|max_length[60]|alpha_numeric_spaces');
					$this->form_validation->set_rules('id_kategori_barang['.$no.']','Kategori #'.($no + 1),'trim|required');
					
					$this->form_validation->set_rules('harga['.$no.']','Harga #'.($no + 1),'trim|required|numeric|min_length[4]|max_length[10]|callback_cek_titik[harga['.$no.']]');
					$this->form_validation->set_rules('keterangan['.$no.']','Keterangan #'.($no + 1),'trim|max_length[2000]');
					$no++;
				}
				
				$this->form_validation->set_message('required','%s harus diisi !');
				$this->form_validation->set_message('numeric','%s harus angka !');
				$this->form_validation->set_message('exist_kode','%s sudah ada di database, pilih kode lain yang unik !');
				$this->form_validation->set_message('cek_titik','%s harus angka, tidak boleh ada titik !');
				$this->form_validation->set_message('alpha_numeric_spaces', '%s Harus huruf / angka !');
				$this->form_validation->set_message('alpha_numeric', '%s Harus huruf / angka !');
				if($this->form_validation->run() == TRUE)
				{
					$this->load->model('m_barang');

					$no_array = 0;
					$inserted = 0;
					foreach($_POST['kode'] as $k)
					{
						$kode 				= $_POST['kode'][$no_array];
						$nama 				= $_POST['nama'][$no_array];
						$id_kategori_barang	= $_POST['id_kategori_barang'][$no_array];
						
						
						$harga 				= $_POST['harga'][$no_array];
						$diskon 				= $_POST['diskon'][$no_array];
						$keterangan 		= $this->clean_tag_input($_POST['keterangan'][$no_array]);

						$insert = $this->m_barang->tambah_baru($kode, $nama, $id_kategori_barang, $harga, $diskon, $keterangan);
						if($insert){
							$inserted++;
						}
						$no_array++;
					}

					if($inserted > 0)
					{
						echo json_encode(array(
							'status' => 1,
							'pesan' => "<i class='fa fa-check' style='color:green;'></i> Data barang berhasil dismpan."
						));
					}
					else
					{
						$this->query_error("Oops, terjadi kesalahan, coba lagi !");
					}
				}
				else
				{
					$this->input_error();
				}
			}
			else
			{
				$this->load->model('m_kategori_barang');
				
				$dt['kategori'] = $this->m_kategori_barang->get_all();
				
				$this->load->view('barang/barang_tambah', $dt);
			}
		}
		else
		{
			exit();
		}
	}

	public function ajax_cek_kode()
	{
		if($this->input->is_ajax_request())
		{
			$kode = $this->input->post('kodenya');
			$this->load->model('m_barang');

			$cek_kode = $this->m_barang->cek_kode($kode);
			if($cek_kode->num_rows() > 0)
			{
				echo json_encode(array(
					'status' => 0,
					'pesan' => "<font color='red'>Kode sudah ada</font>"
				));
			}
			else
			{
				echo json_encode(array(
					'status' => 1,
					'pesan' => ''
				));
			}
		}
	}

	public function exist_kode($kode)
	{
		$this->load->model('m_barang');
		$cek_kode = $this->m_barang->cek_kode($kode);

		if($cek_kode->num_rows() > 0)
		{
			return FALSE;
		}
		return TRUE;
	}

	public function cek_titik($angka)
	{
		$pecah = explode('.', $angka);
		if(count($pecah) > 1){
			return FALSE;
		}
		return TRUE;
	}

	public function edit($id_barang = NULL)
	{
		if( ! empty($id_barang))
		{
			$level = $this->session->userdata('ap_level');
			if($level == 'admin' OR $level == 'inventory')
			{
				if($this->input->is_ajax_request())
				{
					$this->load->model('m_barang');
					
					if($_POST)
					{
						$this->load->library('form_validation');

						$kode_barang 		= $this->input->post('kode_barang');
						$kode_barang_old	= $this->input->post('kode_barang_old');

						$callback			= '';
						if($kode_barang !== $kode_barang_old){
							$callback = "|callback_exist_kode[kode_barang]";
						}

						$this->form_validation->set_rules('kode_barang','Kode Barang','trim|required|alpha_numeric|max_length[40]'.$callback);
						$this->form_validation->set_rules('nama_barang','Nama Barang','trim|required|max_length[60]|alpha_numeric_spaces');
						$this->form_validation->set_rules('id_kategori_barang','Kategori','trim|required');
						
						$this->form_validation->set_rules('harga','Harga','trim|required|numeric|min_length[4]|max_length[10]|callback_cek_titik[harga]');
						$this->form_validation->set_rules('keterangan','Keterangan','trim|max_length[2000]');
						
						$this->form_validation->set_message('required','%s harus diisi !');
						$this->form_validation->set_message('numeric','%s harus angka !');
						$this->form_validation->set_message('exist_kode','%s sudah ada di database, pilih kode lain yang unik !');
						$this->form_validation->set_message('cek_titik','%s harus angka, tidak boleh ada titik !');
						$this->form_validation->set_message('alpha_numeric_spaces', '%s Harus huruf / angka !');
						$this->form_validation->set_message('alpha_numeric', '%s Harus huruf / angka !');
						
						if($this->form_validation->run() == TRUE)
						{
							$nama 				= $this->input->post('nama_barang');
							$id_kategori_barang	= $this->input->post('id_kategori_barang');
							$harga 				= $this->input->post('harga');
							$diskon 				= $this->input->post('diskon');
							$keterangan 		= $this->clean_tag_input($this->input->post('keterangan'));

							$update = $this->m_barang->update_barang($id_barang, $kode_barang, $nama,  $id_kategori_barang, $harga, $diskon, $keterangan);
							if($update)
							{
								echo json_encode(array(
									'status' => 1,
									'pesan' => "<div class='alert alert-success'><i class='fa fa-check'></i> Data barang berhasil diupdate.</div>"
								));
							}
							else
							{
								$this->query_error();
							}
						}
						else
						{
							$this->input_error();
						}
					}
					else
					{
						$this->load->model('m_kategori_barang');
						$this->load->model('m_merk_barang');

						$dt['barang'] 	= $this->m_barang->get_baris($id_barang)->row();
						$dt['kategori'] = $this->m_kategori_barang->get_all();
						$dt['merek'] 	= $this->m_merk_barang->get_all();
						$this->load->view('barang/barang_edit', $dt);
					}
				}
			}
		}
	}

	public function list_kategori()
	{
		$this->load->view('barang/kategori/kategori_data');
	}

	public function list_kategori_json()
	{
		$this->load->model('m_kategori_barang');
		$level 			= $this->session->userdata('ap_level');

		$requestData	= $_REQUEST;
		$fetch			= $this->m_kategori_barang->fetch_data_kategori($requestData['search']['value'], $requestData['order'][0]['column'], $requestData['order'][0]['dir'], $requestData['start'], $requestData['length']);
		
		$totalData		= $fetch['totalData'];
		$totalFiltered	= $fetch['totalFiltered'];
		$query			= $fetch['query'];

		$data	= array();
		foreach($query->result_array() as $row)
		{ 
			$nestedData = array(); 

			$nestedData[]	= $row['nomor'];
			$nestedData[]	= $row['kategori'];

			if($level == 'admin' OR $level == 'inventory')
			{
				$nestedData[]	= "<a href='".site_url('barang/edit-kategori/'.$row['id_kategori_barang'])."' id='EditKategori'><i class='fa fa-pencil'></i> Edit</a>";
				$nestedData[]	= "<a href='".site_url('barang/hapus-kategori/'.$row['id_kategori_barang'])."' id='HapusKategori'><i class='fa fa-trash-o'></i> Hapus</a>";
			}

			$data[] = $nestedData;
		}

		$json_data = array(
			"draw"            => intval( $requestData['draw'] ),  
			"recordsTotal"    => intval( $totalData ),  
			"recordsFiltered" => intval( $totalFiltered ), 
			"data"            => $data
			);

		echo json_encode($json_data);
	}

	public function tambah_kategori()
	{
		$level = $this->session->userdata('ap_level');
		if($level == 'admin' OR $level == 'inventory')
		{
			if($_POST)
			{
				$this->load->library('form_validation');
				$this->form_validation->set_rules('kategori','Kategori','trim|required|max_length[40]|alpha_numeric_spaces');				
				$this->form_validation->set_message('required','%s harus diisi !');
				$this->form_validation->set_message('alpha_numeric_spaces', '%s Harus huruf / angka !');

				if($this->form_validation->run() == TRUE)
				{
					$this->load->model('m_kategori_barang');
					$kategori 	= $this->input->post('kategori');
					$insert 	= $this->m_kategori_barang->tambah_kategori($kategori);
					if($insert)
					{
						echo json_encode(array(
							'status' => 1,
							'pesan' => "<div class='alert alert-success'><i class='fa fa-check'></i> <b>".$kategori."</b> berhasil ditambahkan.</div>"
						));
					}
					else
					{
						$this->query_error();
					}
				}
				else
				{
					$this->input_error();
				}
			}
			else
			{
				$this->load->view('barang/kategori/kategori_tambah');
			}
		}
	}

	public function hapus_kategori($id_kategori_barang)
	{
		$level = $this->session->userdata('ap_level');
		if($level == 'admin' OR $level == 'inventory')
		{
			if($this->input->is_ajax_request())
			{
				$this->load->model('m_kategori_barang');
				$hapus = $this->m_kategori_barang->hapus_kategori($id_kategori_barang);
				if($hapus)
				{
					echo json_encode(array(
						"pesan" => "<font color='green'><i class='fa fa-check'></i> Data berhasil dihapus !</font>
					"));
				}
				else
				{
					echo json_encode(array(
						"pesan" => "<font color='red'><i class='fa fa-warning'></i> Terjadi kesalahan, coba lagi !</font>
					"));
				}
			}
		}
	}

	public function edit_kategori($id_kategori_barang = NULL)
	{
		if( ! empty($id_kategori_barang))
		{
			$level = $this->session->userdata('ap_level');
			if($level == 'admin' OR $level == 'inventory')
			{
				if($this->input->is_ajax_request())
				{
					$this->load->model('m_kategori_barang');
					
					if($_POST)
					{
						$this->load->library('form_validation');
						$this->form_validation->set_rules('kategori','Kategori','trim|required|max_length[40]|alpha_numeric_spaces');				
						$this->form_validation->set_message('required','%s harus diisi !');
						$this->form_validation->set_message('alpha_numeric_spaces', '%s Harus huruf / angka !');

						if($this->form_validation->run() == TRUE)
						{
							$kategori 	= $this->input->post('kategori');
							$insert 	= $this->m_kategori_barang->update_kategori($id_kategori_barang, $kategori);
							if($insert)
							{
								echo json_encode(array(
									'status' => 1,
									'pesan' => "<div class='alert alert-success'><i class='fa fa-check'></i> Data berhasil diupdate.</div>"
								));
							}
							else
							{
								$this->query_error();
							}
						}
						else
						{
							$this->input_error();
						}
					}
					else
					{
						$dt['kategori'] = $this->m_kategori_barang->get_baris($id_kategori_barang)->row();
						$this->load->view('barang/kategori/kategori_edit', $dt);
					}
				}
			}
		}
	}

	public function cek_stok()
	{
		if($this->input->is_ajax_request())
		{
			$this->load->model('m_barang');
			$kode = $this->input->post('kode_barang');
			$stok = $this->input->post('stok');

			$this->db->where('kode_barang', $kode);
			$get = $this->db->get('pj_barang')->row_array();


			$this->db->where('id_menu', $get['id_barang']);
			$resep = $this->db->get('m_resep')->result_array();

			$arr_kosong = array();
			$bahan_kurang = '';
			foreach ($resep as $key) {
				
				$this->db->where('id_bahan', $key['id_bahan']);
				$bahan = $this->db->get('m_bahan')->row_array();
				$stok_dicari = $stok*$key['jumlah'];

				if ($stok_dicari > $bahan['stock']) {

					$kurang = ($stok*$key['jumlah'])-$bahan['stock'];
					$bahan_kurang = $bahan_kurang.' '.$bahan['nama'].' kurang '. $kurang.', ';
					array_push($arr_kosong, array('nama' => $bahan['nama'], 'kurang' => $kurang));	
				}

			}

			$arr_kosong = array_filter($arr_kosong);

			


			if(empty($arr_kosong))
			{
				echo json_encode(array('status' => 1));
			}
			else
			{
				// print_r()
				// $barang_kosong = implode(', ', $arr_kosong);
				echo json_encode(array('status' => 0, 'pesan' => "Anda kekurangan <b>".$bahan_kurang."</b> saat ini !"));
				
			}
		}
	}
}