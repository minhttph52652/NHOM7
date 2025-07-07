<?php
$filepath = realpath(dirname(__FILE__));
include_once($filepath . '/../lib/session.php');
include_once($filepath . '/../lib/database.php');

class user {
    private $db;
    public function __construct()
    {
        $this->db = new Database();
    }
    public function login($email, $password) {
        $password = md5($password); //Mã hóa mật khẩu
        $query = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
        $result = $this->db->select($query);
        if ($result) {
            $value = $result->fetch_assoc();
            Session::set('user', true);
            Session::set('userId', $value['id']);
            Session::set('role_id', $value['role_id']);
            header("Location:index.php");
            exit();
        }
        else{
            return "Tên đăng nhập hoặc mật khẩu không đúng ! ";
        }
    }
    public function insert($data)
    {
        $fullName = mysqli_real_escape_string($this->db->link, $data['fullName']);
		$email = mysqli_real_escape_string($this->db->link, $data['email']);
		$dob = mysqli_real_escape_string($this->db->link, $data['dob']);
		$address = mysqli_real_escape_string($this->db->link, $data['address']);
		$password = md5(mysqli_real_escape_string($this->db->link, $data['password']));

		$check_email = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
		$result_check = $this->db->select($check_email);

        if ($result_check) {
            return 'Email đã tồn tại!';
        }
        else {
            $query = "INSERT INTO users
            (email, fullname, dob, password, role_id, isConfirmed, address, status, captcha)
            VALUES ('$email', '$fullName', '$dob', '$password', 2, 1, '$address', 1, NULL)";
            $result = $this->db->insert($query);
            if ($result) {
                header("Location: login.php");
                exit();
            }
            else {
                return 'Đăng ký thất bại!';
            }
        }
    }
    
    public function update($data)
    {
        $userId = Session::get('userId');
		$fullName = mysqli_real_escape_string($this->db->link, $data['fullName']);
		$email = mysqli_real_escape_string($this->db->link, $data['email']);
		$dob = mysqli_real_escape_string($this->db->link, $data['dob']);
		$address = mysqli_real_escape_string($this->db->link, $data['address']);
		$password = md5(mysqli_real_escape_string($this->db->link, $data['password']));
        
        $query = "UPDATE users
        SET email = '$email', fullname = '$fullName', dob = '$dob', password = '$password', address = '$address'
        WHERE id = '$userId'";
        return $this->db->update($query);
    }

    public function get()
    {
        $userId = Session::get('userId');
        $query = "SELECT * FROM users WHERE id = '$userId' LIMIT 1";
        $result = $this->db->select($query);
        return $result ? mysqli_fetch_assoc($result) : false;
    }

    public function getUserById($id)
    {
        $query = "SELECT * FROM users WHERE id = '$id'";
        return $this->db->select($query);
    }

    public function getAllAdmin($page = 1, $total = 8)
    {
        $offset = ($page - 1) * $total;
        $query = "SELECT users.*, role.name as roleName
        FROM users INNER JOIN role ON users.role_id = role.id
        LIMIT $offset, $total";
        return $this->db->select($query);
    }

    public function getAll()
    {
        $query = "SELECT users.*, role.name as roleName
        FROM users INNER JOIN role ON users.role_id = role.id";
        return $this->db->select($query);
    }

    public function getCountPaging($row = 8)
    {
        $query = "SELECT COUNT(*) as total FROM users";
        $result = $this->db->select($query);
        if ($result) {
            $totalrow = intval(mysqli_fetch_assoc($result)['total']);
            return ceil($totalrow / $row);
        }
        return false;
    }

    public function getUserByName($name) 
    {
        $query = "SELECT * FROM users WHERE fullname LIKE '%$name%'";
        $result = $this->db->select($query);
        return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : false;
    }

    public function getLastUserId()
    {
        $query = "SELECT * FROM users ORDER BY id DESC LIMIT 1";
        $result = $this->db->select($query);
        return $result ? mysqli_fetch_assoc($result) : false;
    }

    public  function block($id)
    {
        $query = "UPDATE users SET status = 0 WHERE id = '$id'";
        return $this->db->update($query);
    }

    public function active($id)
    {
        $query = "UPDATE users SET status = 1 WHERE id = '$id'";
        return $this->db->update($query);
    }

    public function delete($id)
    {
        $query = "DELETE FROM users WHERE id = '$id'";
         return $this->db->delete($query);
    }

    public function getUserByOrder($orderId)
    {
        $orderQuery = "SELECT * FROM orders WHERE id = '$orderId'";
        $order = $this->db->select($orderQuery)->fetch_assoc();
        $userId = $order['userId'];
        $query = "SELECT * FROM users WHERE id = '$userId' LIMIT 1";
        $result = $this->db->select($query);
        return $result ? mysqli_fetch_assoc($result) : false;
    }

    public function getPassword($email)
    {
        $check_email = "SELECT * FROM  users WHERE email='$email' LIMIT 1";
        $result_check = $this->db->select($check_email);
        if ($result_check) {
			$newPassword = rand(10000, 99999);
			$newPass = md5($newPassword);
			$query = "UPDATE users SET password = '$newPass' WHERE email = '$email'";
			$result = $this->db->update($query);
			if ($result) {
				// Có thể gửi email ở đây
				return true;
			}
			return false;
		}
		return 'Email chưa tồn tại!';
	}
    }

