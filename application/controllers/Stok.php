<?php
defined('BASEPATH') OR exit('No direct script access allowed');



class Stok extends MY_Controller 
{
	public function index()
	{
		$this->load->view('stok/stok_data');
	}

	public function stok_json()
	{
		$level 			= $this->session->userdata('ap_level');
		$this->load->model('m_barang');
		  $draw = $_REQUEST['draw']; // get the requested page 
        $start = $_REQUEST['start'];
        $limit = $_REQUEST['length']; // get how many rows we want to have into the grid 
        $sidx = isset($_REQUEST['order'][0]['column'])?$_REQUEST['order'][0]['column']:0; // get index row - i.e. user click to sort 
        $sord = isset($_REQUEST['order'][0]['dir'])?$_REQUEST['order'][0]['dir']:"asc"; // get the direction if(!$sidx) $sidx =1;  
        
  
        $searchval = $_REQUEST['search']['value'];

      //  order[0][column]
        $req_param = array (
                "sort_by" => $sidx,
                "sort_direction" => $sord,
                "limit" => null,
                "searchval" => $searchval,
                
                 
        );     
           
        $row = $this->m_barang->fetch_data_stok($req_param)->result_array();

        $count = count($row); 
       
        
        $req_param['limit'] = array(
                    'start' => $start,
                    'end' => $limit
        );
          
        
        $result = $this->m_barang->fetch_data_stok($req_param)->result_array();
        

       
        $arr_data = array();
        foreach($result as $row) : 
        // $daft_id = $row['daft_id'];
        $id = $row['id_bahan'];
        if($level == 'admin' OR $level == 'inventory')
			{
				$edit  = "<a href='".site_url('stok/edit/'.$row['id_bahan'])."' id='EditBarang'><i class='fa fa-pencil'></i> Tambah</a>";
			}
                     
             
            $arr_data[] = array(
                $row['id_bahan'],
                $row['nama'],
                $row['stock'],
                $edit,

                     
                                );
        endforeach;

         $responce = array('draw' => $draw, // ($start==0)?1:$start,
                          'recordsTotal' => $count, 
                          'recordsFiltered' => $count,
                          'data'=>$arr_data
            );
         
        echo json_encode($responce); 
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
				
				foreach($_POST['nama'] as $kode)
				{
					$this->form_validation->set_rules('nama['.$no.']','Nama Bahan #'.($no + 1),'trim|required|max_length[40]|callback_exist_nama[nama['.$no.']]');
					$this->form_validation->set_rules('stock['.$no.']','Stok Bahan #'.($no + 1),'trim|required|max_length[60]|alpha_numeric_spaces');
					$no++;
				}
				
				$this->form_validation->set_message('required','%s harus diisi !');
				$this->form_validation->set_message('numeric','%s harus angka !');
				$this->form_validation->set_message('exist_nama','%s sudah ada di database, pilih Nama lain yang unik !');
				$this->form_validation->set_message('cek_titik','%s harus angka, tidak boleh ada titik !');
				$this->form_validation->set_message('alpha_numeric_spaces', '%s Harus huruf / angka !');
				if($this->form_validation->run() == TRUE)
				{
					
					$no_array = 0;
					$inserted = 0;
					foreach($_POST['nama'] as $k)
					{
						$data = array('nama' => $_POST['nama'][$no_array],
										'stock' =>  $_POST['stock'][$no_array] );
						
						$data2 = $data;
						$data2['tanggal'] = date('Y-m-d');

						

						// $this->db->insert('barang_masuk', $data2);

						$insert = $this->db->insert('m_bahan', $data);
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
				
				$this->load->view('stok/stok_tambah', $dt);
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

	public function exist_nama($nama)
	{
		$this->db->where('nama', $nama);
		$get = $this->db->get('m_bahan');
		if($get->num_rows() > 0)
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
						// print_r($_POST);
						// exit();


						
						
						$this->form_validation->set_rules('stock','Stok','trim|required|numeric|max_length[10]|callback_cek_titik[stock]');
						
						
						$this->form_validation->set_message('required','%s harus diisi !');
						$this->form_validation->set_message('numeric','%s harus angka !');
						$this->form_validation->set_message('exist_kode','%s sudah ada di database, pilih kode lain yang unik !');
						$this->form_validation->set_message('cek_titik','%s harus angka, tidak boleh ada titik !');
						$this->form_validation->set_message('alpha_numeric_spaces', '%s Harus huruf / angka !');
						$this->form_validation->set_message('alpha_numeric', '%s Harus huruf / angka !');
						
						if($this->form_validation->run() == TRUE)
						{
							
							$stock = $_POST['stock'];


							$this->db->where('id_bahan', $id_barang);
							$bahan = $this->db->get('m_bahan')->row_array();							
							$stock_baru = $stock+$bahan['stock'];

							$data2 = array('stock' => $stock, 
										'nama' => $bahan['nama'],
										'tanggal' => date('Y-m-d'),
										'harga' => $_POST['harga']);

							$data = array('stock' => $stock_baru);
																		
							

							$this->db->insert('barang_masuk', $data2);
							
							$this->db->where('id_bahan', $id_barang);
							$update = $this->db->update('m_bahan', $data);
							if($update)
							{
								echo json_encode(array(
									'status' => 1,
									'pesan' => "<div class='alert alert-success'><i class='fa fa-check'></i> Data bahan berhasil ditambah.</div>"
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
						
						$this->db->where('id_bahan', $id_barang);
						$dt = $this->db->get('m_bahan')->row_array();
						
						$this->load->view('stok/stok_edit', $dt);
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

			$get_stok = $this->m_barang->get_stok($kode);
			if($stok > $get_stok->row()->total_stok)
			{
				echo json_encode(array('status' => 0, 'pesan' => "Stok untuk <b>".$get_stok->row()->nama_barang."</b> saat ini hanya tersisa <b>".$get_stok->row()->total_stok."</b> !"));
			}
			else
			{
				echo json_encode(array('status' => 1));
			}
		}
	}
}