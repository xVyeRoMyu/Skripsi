<?php
    $loggedIn = isset($_SESSION['operator']);
    if ($loggedIn) {
        $name  = $_SESSION['operator'];
        $email = $_SESSION['operator_email'];
    } else {
        $name  = 'Operator';
        $email = 'operator@unknown.com';
    }
?>
<div class="container">
    <h1>Current Active Booking</h1>
    <button type="button" class="btn btn-primary" id="downloadActivePdf">
        <i class="fas fa-file-pdf"></i> Download Current Active Booking Data
    </button>
    <div class="table-responsive no-wrap-table">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th class="text-center sortable" data-column="invoice">Invoice<i class="fas fa-sort"></i></th>
                    <th class="text-center sortable" data-column="name">Name<i class="fas fa-sort"></i></th>
                    <th class="text-center sortable" data-column="email">email<i class="fas fa-sort"></i></th>
                    <th class="text-center sortable" data-column="phone">Phone<i class="fas fa-sort"></i></th>
                    <th class="text-center sortable" data-column="payment">Payment<i class="fas fa-sort"></i></th>
                    <th class="text-center sortable" data-column="deadline">Deadline<i class="fas fa-sort"></i></th>
                    <th class="text-center sortable" data-column="date">Date<i class="fas fa-sort"></i></th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $count = 1; ?>
                <?php foreach ($data['customer'] ?? [] as $lecs): ?>
                    <tr>
                        <td><?= $count ?></td>
                        <td class="text-center"><?= htmlspecialchars($lecs['cus_invoice'] ?? $lecs['invoice'] ?? 'N/A') ?></td>
                        <td class="text-center"><?= htmlspecialchars($lecs['cus_name'] ?? $lecs['name'] ?? 'N/A') ?></td>
                        <td class="text-center"><?= htmlspecialchars($lecs['cus_email'] ?? $lecs['email'] ?? 'N/A') ?></td>
                        <td class="text-center"><?= htmlspecialchars($lecs['cus_phone'] ?? $lecs['phone'] ?? 'N/A') ?></td>
                        <td class="text-center"><?= htmlspecialchars($lecs['cus_payment'] ?? $lecs['payment'] ?? 'N/A') ?></td>
                        <td class="text-center"><?= htmlspecialchars($lecs['cus_deadline'] ?? $lecs['deadline'] ?? 'N/A') ?></td>
                        <td class="text-center"><?= htmlspecialchars($lecs['cus_date'] ?? $lecs['date'] ?? 'N/A') ?></td>
                        <td class="text-center">
                            <a href="<?= APP_PATH ?>active_booking/accept/<?= $lecs['invoice'] ?? '' ?>" 
                               class="btn btn-success btn-sm" 
                               title="Move to History"
                               onclick="return confirm('Are you sure you want to accept and move this booking to history?')">
                                <i class="fas fa-check"></i>
                            </a>
                            
                            <button class="btn btn-info btn-sm view-details" 
                                    data-details='<?= htmlspecialchars($lecs['details'] ?? '{}') ?>'
                                    data-invoice='<?= htmlspecialchars($lecs['invoice'] ?? '') ?>'
                                    title="View Details">
                                <i class="fas fa-info-circle"></i>
                            </button>
                            
                            <a href="<?= APP_PATH ?>active_booking/delete/<?= $lecs['invoice'] ?? '' ?>" 
                               class="btn btn-danger btn-sm delete-btn" 
                               title="Cancel Booking"
                               data-invoice="<?= htmlspecialchars($lecs['invoice'] ?? '') ?>"
                               data-name="<?= htmlspecialchars($lecs['name'] ?? '') ?>">
                                <i class="fas fa-times"></i>
                            </a>
                            
                            <a href="#" 
                               class="btn btn-success btn-sm whatsapp-btn" 
                               title="Send WhatsApp Reminder"
                               data-phone="<?= preg_replace('/[^0-9]/', '', $lecs['phone'] ?? '') ?>"
                               data-invoice="<?= htmlspecialchars($lecs['invoice'] ?? '') ?>"
                               data-deadline="<?= htmlspecialchars($lecs['deadline'] ?? '') ?>"
                               data-payment="<?= htmlspecialchars($lecs['payment'] ?? '') ?>"
                               data-name="<?= htmlspecialchars($lecs['name'] ?? '') ?>">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        </td>
                    </tr>
                    <?php $count++; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalLabel">Booking Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="bookingDetailsContent">
                <!-- Details will be inserted here -->
            </div>
        </div>
    </div>
</div>