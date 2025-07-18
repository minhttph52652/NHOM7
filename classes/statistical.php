<?php
$filepath = realpath(dirname(__FILE__));
include_once($filepath . '/../lib/database.php');
include_once($filepath . '/../lib/session.php');
include_once($filepath . '/../classes/statistical.php');
?>

<?php
//Xử lý thống kê đơn hàng
class statistical 
{
    private $db;
    public function __construct()
    {
        $this->db = new Database();
    }
//Hàm cập nhật hoặc thêm mới thống kê đơn hàng theo ngày 
    public function getStatistical($id, $sale, $quantity)
    {
        //Lấy ngày nhận hàng theo id đơn hàng 
         $query = "SELECT receivedDate FROM orders WHERE id = $id ";
        $mysqli_result = $this->db->select($query);
        $row_order = $mysqli_result->fetch_assoc();
        $order_date = $row_order['receivedDate'];//Ngày nhận hàng
        $profit = $sale;
        //Kiểm tra đã có thống kê trong ngày chưa
        $query1 = "SELECT * FROM statistical WHERE order_date = '$order_date'";
        $mysqli_result1 = $this->db->select($query1);
        if($mysqli_result1){
            // Nếu đã có, cập nhật số lượng, doanh thu, lợi nhuận và tổng đơn
            $query2 = 
            "UPDATE `statistical` SET `sales` = `sales` + '$sale', `profit` = `profit` + '$profit', 
            `quantity` = `quantity` + '$quantity', `total_order` = `total_order` + 1 
            WHERE `order_date` = '$order_date'";

            $mysqli_result2 = $this->db->update($query2);
        }
        else{
            // Nếu chưa có, thêm dòng mới vào bảng thống kê
            $query3 = "INSERT INTO statistical VALUES (NULL, '$order_date', '$sale', '$profit', '$quantity', 1)";
            $mysqli_result3 = $this->db->insert($query3);
        }

    }
// Hàm tạo danh sách tất cả ngày giữa 2 mốc thời gian
    public function getDatesInRange($startDate, $endDate) 
    {
        $dateArray = array();
        $currentDate = strtotime($startDate);
    
        while ($currentDate <= strtotime($endDate)) {
          $dateArray[] = date('Y-m-d', $currentDate);
          $currentDate = strtotime('+1 day', $currentDate);
        }
    
        return $dateArray;
    }
// Lấy tất cả dữ liệu thống kê trong khoảng thời gian được chọn
    public function filterByDate($start, $end) 
    {
        $query = "SELECT * FROM statistical WHERE order_date BETWEEN '$start' AND '$end'";
        $mysqli_result = $this->db->select($query);
        if ($mysqli_result) {
            $result = mysqli_fetch_all($this->db->select($query), MYSQLI_ASSOC);
            return $result;
        }
        return false;
    }
// Tính tổng số đơn hàng, doanh thu, lợi nhuận trong khoảng thời gian
     public function getSumTotalOrder($start, $end) 
    {
        $query = "SELECT SUM(total_order) AS sum_order, SUM(sales) AS sum_sale, SUM(profit) AS sum_profit  FROM statistical WHERE order_date BETWEEN '$start' AND '$end'";
        $mysqli_result = $this->db->select($query);
        if ($mysqli_result) {
            $result = $mysqli_result->fetch_assoc();
            return $result;
        }
        return false;
    }
}