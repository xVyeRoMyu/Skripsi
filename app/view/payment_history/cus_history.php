<?php
    $loggedIn = isset($_SESSION['user']) && $_SESSION['user']['role'] === 'operator';
    if ($loggedIn) {
        $name = $_SESSION['user']['name'];
        $email = $_SESSION['user']['email'];
    } else {
        $name  = 'Operator';
        $email = 'operator@unknown.com';
    }
?>
<div class="container">
    <h1>Customer History</h1>
    <p>Hi <span class="bold"><?= htmlspecialchars($name) ?></span>, what would you like to do today? </p>
    <p class="operator-info">
        Operator email: <span class="bold"><?= htmlspecialchars($email) ?></span>
    </p>
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#formInsertBox">
        Add Customer
    </button>
    <button type="button" class="btn btn-primary ms-2" id="downloadHistoryPdf">
        <i class="fas fa-file-pdf"></i> Download Customer History Data
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
                        <td class="text-center"><?= htmlspecialchars($lecs['cus_date'] ?? $lecs['date'] ?? 'N/A') ?></td>
                        <td class="text-center">
                            <button class="btn btn-info" 
                                    data-id="<?= htmlspecialchars($lecs['invoice'] ?? '') ?>" 
                                    onclick="
                                    $('#cus_invoice_update').val('<?= htmlspecialchars($lecs['invoice'] ?? '') ?>');
                                    $('#cus_name_update').val('<?= htmlspecialchars($lecs['name'] ?? '') ?>');
                                    $('#cus_email_update').val('<?= htmlspecialchars($lecs['email'] ?? '') ?>');
                                    $('#cus_phone_update').val('<?= htmlspecialchars($lecs['phone'] ?? '') ?>');
                                    $('#cus_payment_update').val('<?= htmlspecialchars($lecs['payment'] ?? '') ?>');
                                    $('#cus_date_update').val('<?= htmlspecialchars($lecs['date'] ?? '') ?>');
                                    new bootstrap.Modal(document.getElementById('formUpdateBox')).show();
                                    ">
                                Edit
                            </button>
                            <a href="<?= APP_PATH ?>payment_history/delete/<?= htmlspecialchars($lecs['invoice'] ?? '') ?>" 
                               class="btn btn-danger">
                                Delete
                            </a>
                        </td>
                    </tr>
                    <?php $count++; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<!-- INSERT Modal -->
<div class="modal fade" id="formInsertBox" tabindex="-1" aria-labelledby="insertModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- HEADER -->
            <div class="modal-header">
                <h4 class="modal-title" id="insertModalLabel">Insert New Customer Data</h4>
                <button type="button" class="close-button" data-bs-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <!-- BODY -->
            <div class="modal-body">
                <form action="<?= APP_PATH; ?>payment_history/insert" method="post">
                    <div class="input-field">
                        <input type="text" id="cus_invoice" name="cus_invoice" placeholder=" " required>
                        <label for="cus_invoice">Insert invoice number:</label>
                    </div>
                    <div class="input-field">
                        <input type="text" id="cus_name" name="cus_name" placeholder=" ">
                        <label for="cus_name">Insert customer name:</label>
                    </div>
                    <div class="input-field">
                        <input type="email" id="cus_email" name="cus_email" placeholder=" ">
                        <label for="cus_email">Insert email:</label>
                    </div>
					<div class="input-field">
						<input type="tel" id="cus_phone" name="cus_phone" placeholder=" ">
					</div>
                    <div class="input-field">
                        <input type="number" id="cus_payment" name="cus_payment" placeholder=" ">
                        <label for="cus_payment">Insert payment:</label>
                    </div>
                    <div class="input-field">
                        <input type="date" id="cus_date" name="cus_date" placeholder=" ">
                        <label for="cus_date">Insert date:</label>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Add Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- UPDATE Modal -->
<div class="modal fade" id="formUpdateBox" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- HEADER -->
            <div class="modal-header">
                <h4 class="modal-title" id="updateModalLabel">Update Customer Data</h4>
                <button type="button" class="close-button" data-bs-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <!-- BODY -->
            <div class="modal-body">
                <form id="updateForm" action="<?= APP_PATH; ?>payment_history/update" method="post">
                    <div class="input-field">
                        <input type="text" id="cus_invoice_update" name="cus_invoice_update" placeholder=" " required readonly style="background-color: rgba(233,233,233,0.5);">
                        <label for="cus_invoice_update">Invoice Number:</label>
                    </div>
                    <div class="input-field">
                        <input type="text" id="cus_name_update" name="cus_name_update" placeholder=" ">
                        <label for="cus_name_update">Update customer name:</label>
                    </div>
                    <div class="input-field">
                        <input type="email" id="cus_email_update" name="cus_email_update" placeholder=" ">
                        <label for="cus_email_update">Update email:</label>
                    </div>
                    <!-- Phone Input Updated -->
                    <div class="input-field">
                        <input type="tel" id="cus_phone_update" name="cus_phone_update" placeholder=" ">
                    </div>
                    <div class="input-field">
                        <input type="number" id="cus_payment_update" name="cus_payment_update" placeholder=" ">
                        <label for="cus_payment_update">Update payment:</label>
                    </div>
                    <div class="input-field">
                        <input type="date" id="cus_date_update" name="cus_date_update" placeholder=" ">
                        <label for="cus_date_update">Update date:</label>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" form="updateForm" class="btn btn-success">Update Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Booking Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detailsContent">
                <!-- Details will be inserted here -->
            </div>
        </div>
    </div>
</div>
<script>
// Wait for both jQuery and DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
    // Check if jQuery is loaded
    if (typeof jQuery == 'undefined') {
        console.error('jQuery is not loaded!');
        return;
    }
    
    // Now use jQuery safely
    jQuery(document).ready(function($) {
        $('.view-details').click(function() {
            const details = $(this).data('details');
            try {
                const parsedDetails = JSON.parse(details);
                let html = '<ul class="list-group">';
                
                // Format the details for display
                if (parsedDetails.type === 'venue') {
                    html += `<li class="list-group-item"><strong>Venue:</strong> ${parsedDetails.title}</li>`;
                    html += `<li class="list-group-item"><strong>Date:</strong> ${parsedDetails.date}</li>`;
                    html += `<li class="list-group-item"><strong>Guests:</strong> ${parsedDetails.guests}</li>`;
                    html += `<li class="list-group-item"><strong>Addons:</strong><ul>`;
                    parsedDetails.addons.forEach(addon => {
                        html += `<li>${addon.name}: ${addon.price}</li>`;
                    });
                    html += `</ul></li>`;
                } else if (parsedDetails.type.startsWith('housing')) {
                    html += `<li class="list-group-item"><strong>House:</strong> ${parsedDetails.title}</li>`;
                    html += `<li class="list-group-item"><strong>Plan:</strong> ${parsedDetails.plan}</li>`;
                    html += `<li class="list-group-item"><strong>Dates:</strong> ${parsedDetails.startDate} to ${parsedDetails.endDate}</li>`;
                } else {
                    // Hotel booking
                    html += `<li class="list-group-item"><strong>Room:</strong> ${parsedDetails.title}</li>`;
                    html += `<li class="list-group-item"><strong>Check-in:</strong> ${parsedDetails.checkIn}</li>`;
                    html += `<li class="list-group-item"><strong>Check-out:</strong> ${parsedDetails.checkOut}</li>`;
                    html += `<li class="list-group-item"><strong>Guests:</strong> ${parsedDetails.guests}</li>`;
                }
                
                html += '</ul>';
                $('#detailsContent').html(html);
            } catch (e) {
                $('#detailsContent').html('<p>Unable to display booking details</p>');
            }
            
            new bootstrap.Modal(document.getElementById('detailsModal')).show();
        });
    });
});
</script>