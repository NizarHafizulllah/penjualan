<?php
defined('BASEPATH') OR exit('No direct script access allowed');



class Pengeluaran extends MY_Controller 
{
	public function index()
	{
		$this->load->view('pengeluaran/pengeluaran_data');
	}

	public function pengeluaran_json()
	{
		$this->load->model('M_pengeluaran', 'dm');
		$level 			= $this->session->userdata('ap_level');

		$requestData	= $_REQUEST;
		$fetch			= $this->dm->fetch_data_pengeluaran($requestData['search']['value'], $requestData['order'][0]['column'], $requestData['order'][0]['dir'], $requestData['start'], $requestData['length']);
		
		$totalData		= $fetch['totalData'];
		$totalFiltered	= $fetch['totalFiltered'];
		$query			= $fetch['query'];

		$data	= array();
		foreach($query->result_array() as $row)
		{ 
			$nestedData = array(); 

			$nestedData[]	= $row['nomor'];
			$nestedData[]	= $row['tanggal'];
			$nestedData[]	= $row['nama'];
			$nestedData[]	= $row['stock'];
			$nestedData[]	= $row['harga'];

			if($level == 'admin' OR $level == 'inventory')
			{
				$nestedData[]	= "<a href='".site_url('pengeluaran/edit/'.$row['id'])."' id='EditPengeluaran'><i class='fa fa-pencil'></i> Edit</a>";
				$nestedData[]	= "<a href='".site_url('pengeluaran/hapus/'.$row['id'])."' id='HapusPengeluaran'><i class='fa fa-trash-o'></i> Hapus</a>";
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
				$data = array('id' => $id_barang, );
				$hapus = $this->db->delete('barang_masuk', $data);
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
				foreach($_POST['nama'] as $nama)
				{
					
					$this->form_validation->set_rules('harga['.$no.']','Harga #'.($no + 1),'trim|required|numeric|min_length[4]|max_length[10]|callback_cek_titik[harga['.$no.']]');
					
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
					
					// echo '<pre>';
					// print_r($_POST);
					// echo '</pre>';
					// exit();
					$no_array = 0;
					$inserted = 0;

					foreach($_POST['nama'] as $key => $value)
					{

						$data_array = array('tanggal' => $_POST['tanggal'][$key],
											'nama' => $_POST['nama'][$key],
											'stock' => $_POST['stock'][$key],
											'harga' => $_POST['harga'][$key],
						);
						

						$insert = $this->db->insert('barang_masuk', $data_array);


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
				
				$this->load->view('pengeluaran/pengeluaran_tambah', $dt);
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

						
						

						
						$this->form_validation->set_rules('harga','Harga','trim|required|numeric|min_length[4]|max_length[10]|callback_cek_titik[harga]');
						
						$this->form_validation->set_message('required','%s harus diisi !');
						$this->form_validation->set_message('numeric','%s harus angka !');
						$this->form_validation->set_message('exist_kode','%s sudah ada di database, pilih kode lain yang unik !');
						$this->form_validation->set_message('cek_titik','%s harus angka, tidak boleh ada titik !');
						$this->form_validation->set_message('alpha_numeric_spaces', '%s Harus huruf / angka !');
						$this->form_validation->set_message('alpha_numeric', '%s Harus huruf / angka !');
						
						if($this->form_validation->run() == TRUE)
						{
							$data_array = array('tanggal' => $_POST['tanggal'],
											'nama' => $_POST['nama'],
											'stock' => $_POST['stock'],
											'harga' => $_POST['harga'],
						);
							$this->db->where('id', $id_barang);
							$update = $this->db->update('barang_masuk', $data_array);
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
						// $this->load->model('m_kategori_barang');
						// $this->load->model('m_merk_barang');
						$this->db->where('id', $id_barang);
						$dt['pengeluaran'] 	= $this->db->get('barang_masuk')->row();
						$this->load->view('pengeluaran/pengeluaran_edit', $dt);
					}
				}
			}
		}
	}

	

}