<?php
class BaseController {
    protected mysqli $conn;
    public function __construct(mysqli $conn) { $this->conn = $conn; }
    protected function view($view, $data = []) {
        extract($data);
        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        require __DIR__ . '/../views/layouts/header.php';
        require $viewFile;
        require __DIR__ . '/../views/layouts/footer.php';
    }
    protected function errors($errors) { return implode('<br>', array_map('e', $errors)); }
}
?>
