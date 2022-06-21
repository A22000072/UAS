<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
    }

    

    public function index()
    {
        if ($this->session->userdata('email')) {
            redirect('user');
        }

        $this->form_validation->set_rules('username', 'Email', 'required|trim');
        $this->form_validation->set_rules('password', 'Email', 'required|trim');
        if ($this->form_validation->run() == FALSE) {
            # code...
            $data['title'] = 'Login';
            $this->load->view('templates/auth_header', $data);
            $this->load->view('auth/login');
            $this->load->view('templates/auth_footer');
        } else {
            $this->_login();
        }
    }

    private function _login()
    {
        $username = $this->input->post('username');
        $password = $this->input->post('password');

        $user = $this->db->get_where('petugas', ['username' => $username])->row_array();
        if ($user) {
            if (password_verify($password, $user['password'])) {
                $data = [
                    'username' => $user['username'],
                    'level' => $user['level']
                ];
                $this->session->set_userdata($data);
                redirect('User');
                    
            } else {
                $this->session->flashdata('message', '<div class="alert alert-danger" role="alert">
            Password Salah!
          </div>');
                redirect('auth');
            }
            } else {
            $this->session->flashdata('message', '<div class="alert alert-danger" role="alert">
            Akun tidak ditemukan!
          </div>');
            redirect('Auth');
        }
    }

    public function registration()
    {
        if ($this->session->userdata('username')) {
            redirect('user');
        }

        $this->form_validation->set_rules('name', 'name', 'required|trim');
        $this->form_validation->set_rules('username', 'username', 'required|trim|is_unique[petugas.username]');
        $this->form_validation->set_rules('password1', 'password1', 'required|trim|min_length[3]|matches[passsword2]', [
            'mactches' => 'password tidak sama!',
            'min_length' => 'password to short'
        ]);
        $this->form_validation->set_rules('password2', 'password2', 'required|trim|min_length[3]|matches[passsword1]');

        if ($this->form_validation->run() == false) {
            $data['title'] = 'Daftar';
            $this->load->view('templates/auth_header',$data);
            $this->load->view('auth/registration');
            $this->load->view('templates/auth_footer');
        } else {
            $data = [
                'username' => $this->input->post('username', true),
                'password' => password_hash($this->input->post('password1'), PASSWORD_DEFAULT),
                'nama_petugas' => htmlspecialchars($this->input->post('nama', true)),
                'level' => 'petugas'
            ];

            $this->db->insert('petugas', $data);
            $this->session->flashdata('message', '<div class="alert alert-primary" role="alert">
            Selamat akun anda sudah terdaftar!
          </div>');
            redirect('auth');
        }
    }
    public function logout()
    {
        $this->session->unset_userdata('username');
        $this->session->unset_userdata('level');

        $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">You have been logged out!</div>');
        redirect('auth');
    }
}
