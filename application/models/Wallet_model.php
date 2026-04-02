<?php
class Wallet_model extends CI_Model {

    public function get_wallet($student_id = null, $parent_id = null) {
        if ($student_id !== null) {
            $student = $this->db->get_where('student', ['id' => $student_id])->row();
            if ($student && $student->parent_id) {
                return $this->get_wallet(null, $student->parent_id);
            } else {
                $query = $this->db->get_where('wallet', ['student_id' => $student_id]);
                if ($query->num_rows() > 0) return $query->row();
                $this->db->insert('wallet', ['student_id' => $student_id, 'parent_id' => null, 'balance' => 0.00]);
                return $this->db->get_where('wallet', ['id' => $this->db->insert_id()])->row();
            }
        } elseif ($parent_id !== null) {
            $query = $this->db->get_where('wallet', ['parent_id' => $parent_id]);
            if ($query->num_rows() > 0) return $query->row();
            $this->db->insert('wallet', ['parent_id' => $parent_id, 'student_id' => null, 'balance' => 0.00]);
            return $this->db->get_where('wallet', ['id' => $this->db->insert_id()])->row();
        }
        return null;
    }

    public function get_transactions($wallet_id) {
        $this->db->where('wallet_id', $wallet_id);
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get('wallet_transactions')->result();
    }

    public function deposit($amount, $student_id = null, $parent_id = null) {
        $wallet = $this->get_wallet($student_id, $parent_id);
        if (!$wallet) return false;
        
        $this->db->trans_start();
        $this->db->where('id', $wallet->id);
        $this->db->set('balance', 'balance+'.$amount, FALSE);
        $this->db->update('wallet');

        $data = array(
            'wallet_id' => $wallet->id,
            'amount' => $amount,
            'type' => 'deposit',
            'description' => 'Account deposit via portal'
        );
        $this->db->insert('wallet_transactions', $data);

        $this->db->trans_complete();
        return $this->db->trans_status();
    }
}
