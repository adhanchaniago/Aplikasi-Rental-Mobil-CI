
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Laporan extends CI_Controller {
	//private $limit=5;

	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('LaporanModel');
		$this->load->library('m_pdf');
		$this->load->library(array('session','form_validation','pagination'));
		$this->load->helper(array('form', 'url','html','cookie','date'));

		$level=$this->session->userdata('level');
		if (!($level==1)) {
			show_404();
		}
	}

	function index($offset=0,$order_column='id_sewa',$order_type='asc'){
		if ($this->session->has_userdata('login')==TRUE) {
        
        if(empty($offset)) $offset=0;
        if(empty($order_column)) $order_column='id_sewa';
        if(empty($order_type)) $order_type='asc';

		$totSewa = $this->LaporanModel->TotalSewa();
        $rows=$this->LaporanModel->semua($offset,$order_column,$order_type);
		$title="Data Laporan";
		$link = anchor('laporan/viewAll','DOWNLOAD ALL DATA to PDF');
        $main_view='laporan/tabel';
        $pagination = $this->_pagination('Laporan/index/');
        $this->load->view('page',compact('main_view','title','rows','pagination','link','totSewa'));
        }else{

			$this->session->set_flashdata('pesan', 'Login Dulu');
			redirect('login','refresh');
		}
	}

	public function filter(){
		$tgl_aw =  $this->input->post('tgl1');
		$tgl_ah =  $this->input->post('tgl2');

		$rows=$this->LaporanModel->rangetgl($tgl_aw,$tgl_ah);
		set_cookie('tgl1',$tgl_aw ,60);
		set_cookie('tgl2',$tgl_ah ,60);
		//print_r($rows);
		$totSewa = $this->LaporanModel->sewaRange($tgl_aw,$tgl_ah);
		$link = anchor('laporan/filterpdf','DOWNLOAD DATA to PDF');
		$title="Data Pencarian ".$tgl_aw." sampai dengan ".$tgl_ah;
		$main_view='laporan/tabel';
		$pagination = $this->_pagination('Laporan/index/');
        $this->load->view('page',compact('main_view','title','rows','pagination','link','totSewa'));	
	}

	public function filterpdf(){
		$tgl1 = get_cookie('tgl1');
		$tgl2 = get_cookie('tgl2');
		$rows=$this->LaporanModel->rangetgl($tgl1,$tgl2);
		$totSewa = $this->LaporanModel->sewaRange($tgl1,$tgl2);
		$main_report='laporan/reportFilter';
		$title = "Laporan Peminjaman Tanggal ".$tgl1." s/d ".$tgl2;
		//$this->load->view('report',compact('main_report','rows'));	
		$html =  $this->load->view('report',compact('main_report','rows','title','totSewa'),true);

		$pdfFilePath ="report-".time()."-filter.pdf";
		$pdf = $this->m_pdf->load();
		$pdf->WriteHTML($html,2);
		$pdf->Output($pdfFilePath, "D");

	}

	public function detail($id){
		$rows = $this->LaporanModel->detailLaporan($id);
		$title='Data Laporan/'.$rows->nama_customer;
		$main_view='laporan/detail';	
		$this->load->view('page',compact('main_view','title','rows'));
	}

	public function detailpdf(){
		$id = $this->input->post('thide');
		$rows = $this->LaporanModel->detailLaporan($id);

		$title='Laporan Peminjaman '.$rows->nama_customer;
		$main_report='laporan/reportDetail';
		$html = $this->load->view('report',compact('main_report','title','rows'),true);

		// $this->load->view('report',compact('main_report','title','rows','fotosepeda','fotopelanggan'));

		$pdfFilePath ="report-".time()."-detail.pdf";
		$pdf = $this->m_pdf->load();
		$pdf->WriteHTML($html,2);
		$pdf->Output($pdfFilePath, "D");

	}

	public function viewAll() //seluruh data pdf
	{
		$rows = $this->LaporanModel->All();
		$title = 'Laporan Peminjaman';
		$main_report='laporan/test';
		$totSewa = $this->LaporanModel->TotalSewa();
		$html = $this->load->view('report',compact('main_report','title','rows','totSewa'),TRUE);
		//$this->load->view('report',compact('main_report','title','rows','totSewa'));
	
		$pdfFilePath ="mypdfName-".time()."-download.pdf";
		$pdf = $this->m_pdf->load();
		$pdf->WriteHTML($html,2);
		$pdf->Output($pdfFilePath, "D");
	}

	public function _pagination($uerl){
       // $config['base_url']=site_url('siswa/index/');
		$config['base_url']=site_url($uerl);
        $config['total_rows']=$this->LaporanModel->jumlah();
        //$config['per_page']=$this->limit;
        $config['uri_segment']=3;
        $config['next_link'] = '>';
		$config['prev_link'] = '<';
		$config['first_link'] = FALSE;
		$config['last_link'] = FALSE;
		$config['full_tag_open'] = '<p class="pagination">';
		$config['full_tag_close'] = '</p>';
		$config['num_tag_open'] = '&nbsp;';
		$config['num_tag_close'] = '&nbsp;';
        $this->pagination->initialize($config);
        return  $this->pagination->create_links();
	}



}

/* End of file Laporan.php */
/* Location: ./application/controllers/Laporan.php */
