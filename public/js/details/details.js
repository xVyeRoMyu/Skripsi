document.addEventListener("DOMContentLoaded", () => {
	// ======================
    // TABLE SORTING
    // ======================
    const sortTable = (column, sortDirection) => {
		const table = document.querySelector('.table');
		const tbody = table.querySelector('tbody');
		const rows = Array.from(tbody.querySelectorAll('tr'));
		
		document.querySelectorAll('.sortable').forEach(header => {
			header.setAttribute('data-sort', 'none');
		});
		
		const clickedHeader = document.querySelector(`[data-column="${column}"]`);
		clickedHeader.setAttribute('data-sort', sortDirection);
		
        rows.sort((a, b) => {
            let valueA, valueB;
            
            // Get cell index based on column
            const headers = Array.from(table.querySelectorAll('th'));
            const cellIndex = headers.findIndex(h => h.getAttribute('data-column') === column);
            
            if (cellIndex !== -1) {
                valueA = a.cells[cellIndex].textContent.trim();
                valueB = b.cells[cellIndex].textContent.trim();
                
                // Handle date sorting
                if (column === 'booking_date' || column === 'deadline') {
                    // Convert date strings to Date objects for comparison
                    const dateA = valueA === 'N/A' ? new Date(0) : new Date(valueA);
                    const dateB = valueB === 'N/A' ? new Date(0) : new Date(valueB);
                    
                    return sortDirection === 'asc' 
                        ? dateA - dateB 
                        : dateB - dateA;
                }
                
                // Regular string comparison
                return sortDirection === 'asc' 
                    ? valueA.localeCompare(valueB) 
                    : valueB.localeCompare(valueA);
            }
            
            return 0;
        });
        
        // Update row numbers and reorder
        rows.forEach((row, index) => {
            row.cells[0].textContent = index + 1;
            tbody.appendChild(row);
        });
    };
    
    // Add click event listeners to sortable headers
    document.querySelectorAll('.sortable').forEach(header => {
        header.addEventListener('click', () => {
            const column = header.getAttribute('data-column');
            const currentSort = header.getAttribute('data-sort');
            
            // Toggle sort direction
            const newSort = currentSort === 'asc' ? 'desc' : 'asc';
            
            sortTable(column, newSort);
        });
    });
    // ======================
    // PRICE FORMATTING UTILS
    // ======================
    const formatPrice = (price) => {
        if (price === null || price === undefined) return 'IDR 0';
        
        let numValue;
        if (typeof price === 'number') {
            numValue = price;
        } else if (typeof price === 'string') {
            const matches = price.match(/[\d,.]+/);
            numValue = matches ? parseFloat(matches[0].replace(/[^\d.]/g, '')) : 0;
        } else {
            numValue = 0;
        }
        
        return `IDR ${numValue.toLocaleString('id-ID')}`;
    };

    const calculateOriginalPrice = (details) => {
        if (details.originalTotal !== undefined && details.originalTotal !== null) {
            return parseFloat(details.originalTotal);
        }
        
        let total = parseFloat(details.price) || 0;
        
        if (details.addons && Array.isArray(details.addons)) {
            details.addons.forEach(addon => {
                if (!addon.price) return;
                
                let addonPrice;
                if (typeof addon.price === 'number') {
                    addonPrice = addon.price;
                } else if (typeof addon.price === 'string') {
                    const matches = addon.price.match(/[\d,.]+/);
                    addonPrice = matches ? parseFloat(matches[0].replace(/[^\d.]/g, '')) : 0;
                } else {
                    addonPrice = 0;
                }
                
                total += addonPrice;
            });
        }
        
        return total;
    };

    // ======================
    // DATE HANDLING UTILS
    // ======================
    const parseJSONSafely = (jsonString) => {
        try {
            return JSON.parse(jsonString);
        } catch (e) {
            console.error('JSON parse error:', e);
            return null;
        }
    };

    const formatDisplayDate = (dateString) => {
        if (!dateString) return 'N/A';
        try {
            // Create date in Asia/Makassar timezone
            const date = new Date(dateString + 'T00:00:00+08:00');
            return isNaN(date) ? dateString : date.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        } catch (e) {
            return dateString;
        }
    };

    const getBookingDates = (details) => {
        if (typeof details === 'string') {
            try {
                details = JSON.parse(details);
            } catch (e) {
                console.error('Failed to parse details:', e);
                details = {};
            }
        }
        
        // Determine the primary event date based on booking type
        let eventDate;
        if (details.type?.includes('hotel')) {
            eventDate = details.checkIn;
        } else if (details.type?.includes('housing')) {
            eventDate = details.start_date || details.startDate;
        } else if (details.type?.includes('venue')) {
            eventDate = details.date || details.event_date;
        }

        return {
            bookingDate: eventDate || 'N/A',
            createdDate: details.created_date || 'N/A',
            checkInDate: details.checkIn || details.start_date || details.startDate || 'N/A',
            checkOutDate: details.checkOut || details.end_date || details.endDate || 'N/A',
            eventDate: details.date || details.event_date || 'N/A'
        };
    };

    // ======================
    // DETAILS MODAL LOGIC
    // ======================
    document.querySelectorAll('.view-details').forEach(btn => {
        btn.addEventListener('click', () => {
            const content = document.getElementById('detailsContent');
            const downloadBtn = document.getElementById('downloadReceipt');
            if (!content || !downloadBtn) {
                console.error("Required elements not found");
                return;
            }

            content.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i></div>';
            const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
            
            try {
                // Safely parse details data
                const invoice = btn.dataset.invoice || 'N/A';
                const details = parseJSONSafely(btn.dataset.details || '{}');
                
                // Set invoice data on download button
                downloadBtn.dataset.invoice = invoice;
                
                const dates = getBookingDates(details);
                const originalPriceValue = calculateOriginalPrice(details);
                const originalPrice = formatPrice(originalPriceValue);
                const paymentAmount = formatPrice(details.paymentAmount || originalPriceValue * (details.downpayment ? 0.5 : 1));

                let detailsHtml = `
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Booking Information</h5>
                            <p><strong>Invoice:</strong> ${invoice}</p>
                            <p><strong>Created Date:</strong> ${formatDisplayDate(dates.createdDate)}</p>
                            <p><strong>Type:</strong> ${details.type || 'N/A'}</p>
                            <p><strong>Title:</strong> ${details.title || 'N/A'}</p>
                            <p><strong>Original Price:</strong> ${originalPrice}</p>
                            <p><strong>Payment Type:</strong> ${details.downpayment ? '50% Downpayment' : 'Full Payment'}</p>
                            <p><strong>Payment Amount:</strong> ${paymentAmount}</p>
                        </div>
                        <div class="col-md-6">`;

                // Add type-specific details
                if (details.type?.includes('hotel')) {
                    detailsHtml += `
                        <h5>Stay Details</h5>
                        <p><strong>Check-in:</strong> ${formatDisplayDate(details.checkIn)}</p>
                        <p><strong>Check-out:</strong> ${formatDisplayDate(details.checkOut)}</p>
                        <p><strong>Guests:</strong> ${details.guests || 'N/A'}</p>`;
                } else if (details.type?.includes('housing')) {
                    detailsHtml += `
                        <h5>Stay Details</h5>
                        <p><strong>Plan:</strong> ${details.plan || 'N/A'}</p>
                        <p><strong>From:</strong> ${formatDisplayDate(details.start_date || details.startDate)}</p>
                        <p><strong>To:</strong> ${formatDisplayDate(details.end_date || details.endDate)}</p>`;
                } else if (details.type?.includes('venue')) {
                    detailsHtml += `
                        <h5>Event Details</h5>
                        <p><strong>Event Date:</strong> ${formatDisplayDate(details.date || details.event_date)}</p>
                        <p><strong>Guests:</strong> ${details.guests || 'N/A'}</p>`;
                }

                if (details.addons?.length) {
                    detailsHtml += `
                        <h5 class="mt-3">Addons</h5>
                        <ul class="list-group">
                            ${details.addons.map(addon => `
                                <li class="list-group-item d-flex justify-content-between">
                                    ${addon.name || 'Unnamed Addon'}
                                    <span>${formatPrice(addon.price)}</span>
                                </li>
                            `).join('')}
                        </ul>`;
                }

                detailsHtml += `</div></div>`;
                content.innerHTML = detailsHtml;
                modal.show();
                
            } catch (error) {
                console.error('Error loading details:', error);
                content.innerHTML = `
                    <div class="alert alert-danger">
                        <h5>Error Loading Details</h5>
                        <p>${error.message || 'Could not load booking details'}</p>
                        <button class="btn btn-sm btn-primary mt-2" onclick="window.location.reload()">Refresh Page</button>
                    </div>`;
                modal.show();
            }
        });
    });
    // =====================
    // RECEIPT DOWNLOAD LOGIC
    // =====================
    document.getElementById('downloadReceipt')?.addEventListener('click', function() {
        const invoice = this.dataset.invoice;
        if (invoice && invoice !== 'N/A') {
            window.location.href = `${window.location.origin}/Jens-House/public/generate_receipt.php?invoice=${encodeURIComponent(invoice)}`;
        } else {
            alert('No valid invoice found for this booking');
        }
    });
});