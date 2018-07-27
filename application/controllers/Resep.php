<?php
defined('BASEPATH') OR exit('No direct script access allowed');



class Resep extends MY_Controller 
{
	public function index()
	{
		$this->load->view('barang/resep/resep_data');
	}

	public function resep_json()
	{
		$this->load->model('m_barang');
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
           
        $row = $this->m_barang->get_data_barang($req_param)->result_array();

        $count = count($row); 
       
        
        $req_param['limit'] = array(
                    'start' => $start,
                    'end' => $limit
        );
          
        
        $result = $this->m_barang->get_data_barang($req_param)->result_array();
        
        
       
        $arr_data = array();
        foreach($result as $row) : 
        // $daft_id = $row['daft_id'];
        $id = $row['id_barang'];
        if($level == 'admin' OR $level == 'inventory')
			{
				$edit  = "<a href='".site_url('resep/edit/'.$row['id_barang'])."' id='EditBarang'><i class='fa fa-pencil'></i> Tambah</a>";
			}
			$this->db->where('id_menu', $row['id_barang']);
			$this->db->select('a.jumlah, b.nama as bahan')->from('m_resep a');
			$this->db->join('m_bahan b','a.id_bahan=b.id_bahan');
			$dresep = $this->db->get();
			if ($dresep->num_rows()>0) {
				$gresep = $dresep->result_array();
				// $no = 0;
				$resep = '';
				 foreach ($gresep as $key) {
					 $resep = $resep.' '.$key['bahan'].' : '.$key['jumlah'].'<br/>';
				}
			}else{
				$resep = '';
			}
            
            // exit();         
             
            $arr_data[] = array(
            	$row['id_barang'],
                $row['kode_barang'],
                $row['nama_barang'],
                $row['kategori'],
                $resep,
                $edit,

                     
                                );
        endforeach;

  //       echo "<pre>";
		// print_r($arr_data);
		// echo "</pre>";

		// exit();

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
				foreach($_POST['kode'] as $kode)
				{
					$this->form_validation->set_rules('kode['.$no.']','Kode Barang #'.($no + 1),'trim|required|alpha_numeric|max_length[40]|callback_exist_kode[kode['.$no.']]');
					$this->form_validation->set_rules('nama['.$no.']','Nama Barang #'.($no + 1),'trim|required|max_length[60]|alpha_numeric_spaces');
					$this->form_validation->set_rules('id_kategori_barang['.$no.']','Kategori #'.($no + 1),'trim|required');
					$this->form_validation->set_rules('stok['.$no.']','Stok #'.($no + 1),'trim|required|numeric|max_length[10]|callback_cek_titik[stok['.$no.']]');
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
						
						$stok 				= $_POST['stok'][$no_array];
						$harga 				= $_POST['harga'][$no_array];
						$diskon 				= $_POST['diskon'][$no_array];
						$keterangan 		= $this->clean_tag_input($_POST['keterangan'][$no_array]);

						$insert = $this->m_barang->tambah_baru($kode, $nama, $id_kategori_barang, $stok, $harga, $diskon, $keterangan);
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

	public function exist_bahan($bahan, $id_menu)
	{
		$this->db->where('id_bahan', $bahan);
		$this->db->where('id_menu', $id_menu);
		$cek_kode = $this->db->get('m_resep');

		if($cek_kode->num_rows() > 1)
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

		$post = $_POST;

						
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

				$no = 0;
				foreach($_POST['id_bahan'] as $id_bahan)
				{
					$this->form_validation->set_rules('id_bahan['.$no.']','Bahan #'.($no + 1),'trim|required|alpha_numeric|max_length[40]');
					
					
					$this->form_validation->set_rules('jumlah['.$no.']','Jumlah #'.($no + 1),'trim|required|numeric|max_length[10]|callback_cek_titik[jumlah['.$no.']]');
					$no++;
				}
				
				$this->form_validation->set_message('required','%s harus diisi !');
				$this->form_validation->set_message('numeric','%s harus angka !');
				$this->form_validation->set_message('exist_kode','%s sudah ada dalam resep ini !');
				$this->form_validation->set_message('cek_titik','%s harus angka, tidak boleh ada titik !');
				$this->form_validation->set_message('alpha_numeric_spaces', '%s Harus huruf / angka !');
				$this->form_validation->set_message('alpha_numeric', '%s Harus huruf / angka !');
				if($this->form_validation->run() == TRUE)
				
				{

					$no_arr = 0;
						$data_delete = array();

						foreach($_POST['id_bahan'] as $b)
						{
							if (!empty($_POST['id_resep'][$no_arr])) {
								array_push($data_delete, $_POST['id_resep'][$no_arr]);
							}
							
							$no_arr++;	
						}
						
						if (!empty($data_delete)) {
							// echo $_POST['id_menu']['0'];
						 	$this->db->where('id_menu', $_POST['id_menu']['0']);
						 	$this->db->where_not_in('id_resep', $data_delete);
						 	$delete = $this->db->delete('m_resep');
						 }

					$this->load->model('m_barang');

					$no_array = 0;
					$inserted = 0;
					foreach($_POST['id_bahan'] as $k)
					{

						$data = array('id_bahan' => $_POST['id_bahan'][$no_array],
									'id_menu' => $_POST['id_menu'][$no_array],
									'jumlah' => $_POST['jumlah'][$no_array]);

						// echo $_POST['id_menu'][$no_array];
						// echo "<pre>";
						// print_r($post);
						// echo "</pre>";

						if (!empty($_POST['id_resep'][$no_array])) {
							

							$this->db->where('id_resep', $_POST['id_resep'][$no_array]);
							$insert = $this->db->update('m_resep', $data);

							

							if($insert){
								$inserted++;
							}
						}else{

							$insert = $this->db->insert('m_resep', $data);
							if($insert){
								$inserted++;
							}
						}

						


						
						$no_array++;
					}

					if($inserted > 0)
					{
						echo json_encode(array(
							'status' => 1,
							'pesan' => "<i class='fa fa-check' style='color:green;'></i> Data berhasil dismpan."
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
				
						$data['arr_bahan'] = $this->m_kategori_barang->get_bahan();
						$this->db->where('id_menu', $id_barang);
						$data['bahan'] = $this->db->get('m_resep')->result_array();
						$data['id'] = $id_barang;

						// echo "<pre>";
						// print_r($data);
						// echo "</pre>";
						// exit();
						
						$this->load->view('barang/resep/resep_tambah', $data);
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