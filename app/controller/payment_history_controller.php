public function generate_pdf() {
    // Get all customer data
    $data = $this->model->getAllDataCustomer();
    
    // Generate PDF
    $this->generateTablePDF($data, 'customer_history.pdf');
}

private function generateTablePDF($data, $filename) {
    require_once '../app/helpers/generate_pdf_helper.php';
    generateTablePDF($data, $filename);
}