<?php
// Khởi tạo đường dẫn tuyệt đối đến thư mục hiện tại
$filepath = realpath(dirname(__FILE__));
// Kết nối tới các file cần thiết
include_once($filepath . '/../lib/database.php');
include_once($filepath . '/../lib/session.php');
include_once($filepath . '/../classes/product.php');
?>

<?php
// Định nghĩa class giỏ hàng
class cart 
{
    private $db;

    // Hàm khởi tạo, kết nối tới database
    public function __construct()
    {
        $this->db = new Database();
    }

    // Thêm sản phẩm vào giỏ hàng
    public function add($productId)
    {
        $userId = Session::get('userId');

        // Lấy thông tin sản phẩm từ database
        $query = "SELECT * FROM products WHERE id = '$productId' ";
        $result = $this->db->select($query)->fetch_assoc();

        $productName = $result["name"];
        $productPrice = $result["promotionPrice"];
        $image = $result["image"];

        // Kiểm tra sản phẩm đã có trong giỏ chưa
        $checkcart = "SELECT qty FROM cart WHERE productId = '$productId' AND userId = '$userId' ";
        $check_cart = $this->db->select($checkcart);

        if ($check_cart) {
            // Lấy số lượng sản phẩm hiện tại trong giỏ
            $qtyInCart = mysqli_fetch_row($check_cart)[0];

            // Kiểm tra số lượng tồn kho
            $product = new product();
            $productCheck = $product->getProductbyId($productId);
            if (intval($qtyInCart) >= intval($productCheck['qty'])) {
                return 'out of stock'; // Hết hàng
            }

            // Cập nhật số lượng nếu đã có trong giỏ
            $query_insert = "UPDATE cart SET qty = qty + 1 WHERE productId = $productId AND userId = $userId";
            $insert_cart = $this->db->update($query_insert);
            return $insert_cart ? true : false;
        } else {
            // Chưa có trong giỏ → thêm mới
            $query_insert = "INSERT INTO cart VALUES(NULL,'$userId','$productId',1,'$productName','$productPrice','$image' )";
            $insert_cart = $this->db->insert($query_insert);
            return $insert_cart ? true : false;
        }
    }

    // Cập nhật số lượng sản phẩm trong giỏ
    public function update($productId, $qty)
    {
        $userId = Session::get('userId');

        // Kiểm tra tồn kho
        $product = new product();
        $productCheck = $product->getProductbyId($productId);
        if (intval($qty) > intval($productCheck['qty'])) {
            return 'out of stock';
        }

        // Cập nhật số lượng
        $query_insert = "UPDATE cart SET qty = $qty WHERE productId = $productId AND userId = $userId";
        $insert_cart = $this->db->update($query_insert);
        return $insert_cart ? true : false;
    }

    // Lấy danh sách sản phẩm trong giỏ
    public function get()
    {
        $userId = Session::get('userId');
        $query = "SELECT * FROM cart WHERE userId = '$userId' ";
        $mysqli_result = $this->db->select($query);
        if ($mysqli_result) {
            $result = mysqli_fetch_all($mysqli_result, MYSQLI_ASSOC);
            return $result;
        }
        return false;
    }

    // Xoá sản phẩm khỏi giỏ hàng
    public function delete($cartId)
    {
        $userId = Session::get('userId');
        $query = "DELETE FROM cart WHERE userId = '$userId' AND id = $cartId";
        $row = $this->db->delete($query);
        return $row ? true : false;
    }

    // Tính tổng tiền sản phẩm trong giỏ của người dùng
    public function getTotalPriceByUserId()
    {
        $userId = Session::get('userId');
        $query = "SELECT SUM(productPrice*qty) as total FROM cart WHERE userId = '$userId' ";
        $mysqli_result = $this->db->select($query);
        if ($mysqli_result) {
            $result = mysqli_fetch_all($mysqli_result, MYSQLI_ASSOC)[0];
            return $result;
        }
        return false;
    }

    // Tính tổng số lượng sản phẩm trong giỏ
    public function getTotalQtyByUserId()
    {
        $userId = Session::get('userId');
        $query = "SELECT SUM(qty) as total FROM cart WHERE userId = '$userId' ";
        $mysqli_result = $this->db->select($query);
        if ($mysqli_result) {
            $result = mysqli_fetch_all($mysqli_result, MYSQLI_ASSOC)[0];
            return $result;
        }
        return false;
    }

    // Lấy danh sách productId của các sản phẩm trong giỏ
    public function getListProductIdInCartByUserId()
    {
        $userId = Session::get('userId');
        $query = "SELECT productId FROM cart WHERE userId = '$userId' ";
        $mysqli_result = $this->db->select($query);
        if ($mysqli_result) {
            $result = mysqli_fetch_all($mysqli_result, MYSQLI_ASSOC);
            return $result;
        }
        return false;
    }
}
?>
