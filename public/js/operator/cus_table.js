document.addEventListener("DOMContentLoaded", () => {
    /* --------------------------------------------------
       1) Floating Labels for "Insert"/"Update" prefixes
       -------------------------------------------------- */
    const labels = document.querySelectorAll(".input-field label");

    labels.forEach((label) => {
        label.dataset.original = label.textContent;
        const input = document.getElementById(label.getAttribute("for"));
        if (!input) return;

        input.addEventListener("focus", () => {
            label.classList.add("float-label");
            setTimeout(() => {
                if (label.dataset.original.startsWith("Insert ")) {
                    label.textContent = label.dataset.original.replace("Insert ", "");
                } else if (label.dataset.original.startsWith("Update ")) {
                    label.textContent = label.dataset.original.replace("Update ", "");
                }
            }, 300);
        });

        input.addEventListener("blur", () => {
            if (input.value.trim() === "") {
                label.classList.remove("float-label");
                setTimeout(() => {
                    label.textContent = label.dataset.original;
                }, 300);
            }
        });
    });

    /* --------------------------------------------------
       2) Table Sorting
       -------------------------------------------------- */
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

    /* --------------------------------------------------
       3) Robust Telephone Input Initialization
       -------------------------------------------------- */
    function initializePhoneInputs() {
        const loadPhoneInput = (inputId) => {
            const input = document.querySelector(inputId);
            if (!input) return;

            // Try CDN first
            const cdnPromise = new Promise((resolve) => {
                try {
                    window.intlTelInput(input, {
                        initialCountry: "id",
                        separateDialCode: true,
                        preferredCountries: ["id", "us", "gb"],
                        utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"
                    });
                    resolve(true);
                } catch (e) {
                    resolve(false);
                }
            });

            cdnPromise.then((success) => {
                if (!success) {
                    console.warn(`CDN failed for ${inputId}, using fallback`);
                    // Fallback without utilsScript
                    window.intlTelInput(input, {
                        initialCountry: "id",
                        separateDialCode: true,
                        preferredCountries: ["id", "us", "gb"]
                    });
                }
            });
        };

        loadPhoneInput("#cus_phone");
        loadPhoneInput("#cus_phone_update");
    }

    // Initialize with delay to prevent race conditions
    setTimeout(initializePhoneInputs, 500);

    /* --------------------------------------------------
       4) Delete Confirmation
       -------------------------------------------------- */
    document.querySelectorAll(".delete-btn").forEach((btn) => {
        btn.addEventListener("click", (e) => {
            e.preventDefault();
            const invoice = btn.dataset.invoice;
            const name = btn.dataset.name;

            if (confirm(`This action cannot be undone. Are you sure you want to cancel booking ${invoice} by ${name}?`)) {
                window.location.href = btn.href;
            }
        });
    });

    /* --------------------------------------------------
       5) Enhanced Details Modal
       -------------------------------------------------- */
    document.querySelectorAll(".view-details").forEach((btn) => {
		btn.addEventListener("click", async () => {
			const content = document.getElementById("bookingDetailsContent");
			content.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i></div>';
			const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
			modal.show();

			try {
				const details = JSON.parse(btn.dataset.details || '{}');
				const invoice = btn.dataset.invoice || 'N/A';

				// Format price values
				const formatPrice = (price) => {
					if (price === null || price === undefined) return 'IDR 0';
					const num = typeof price === 'number' ? price : 
							   typeof price === 'string' ? parseFloat(price.replace(/[^\d.-]/g, '')) || 0 : 0;
					return `IDR ${num.toLocaleString('id-ID')}`;
				};

				// Use originalTotal directly for calculations
				const originalPrice = formatPrice(details.originalTotal);
				const paymentAmount = details.downpayment ? 
					formatPrice(details.originalTotal * 0.5) : 
					originalPrice; // Use originalTotal for payment amount

				let detailsHtml = `
					<div class="row">
						<div class="col-md-6">
							<h5>Booking Information</h5>
							<p><strong>Invoice:</strong> ${invoice}</p>
							<p><strong>Type:</strong> ${details.type || 'N/A'}</p>
							<p><strong>Title:</strong> ${details.title || 'N/A'}</p>
							<p><strong>Original Price:</strong> ${originalPrice}</p>
							<p><strong>Payment Type:</strong> ${details.downpayment ? '50% Downpayment' : 'Full Payment'}</p>
							<p><strong>Payment Amount:</strong> ${paymentAmount}</p>
						</div>
						<div class="col-md-6">
				`;

				if (details.type?.includes('venue')) {
					detailsHtml += `
						<h5>Event Details</h5>
						<p><strong>Date:</strong> ${details.date || 'N/A'}</p>
						<p><strong>Guests:</strong> ${details.guests || 'N/A'}</p>
					`;
				} else if (details.type?.includes('housing')) {
					detailsHtml += `
						<h5>Stay Details</h5>
						<p><strong>Plan:</strong> ${details.plan || 'N/A'}</p>
						<p><strong>From:</strong> ${details.start_date || details.startDate || 'N/A'}</p>
						<p><strong>To:</strong> ${details.end_date || details.endDate || 'N/A'}</p>
					`;
				}

				if (details.addons?.length) {
					detailsHtml += `
						<h5 class="mt-3">Addons</h5>
						<ul class="list-group">
							${details.addons.map(addon => `
								<li class="list-group-item d-flex justify-content-between">
									${addon.name}
									<span>${formatPrice(addon.price)}</span>
								</li>
							`).join('')}
						</ul>
					`;
				}

				detailsHtml += `</div></div>`;
				content.innerHTML = detailsHtml;
			} catch (error) {
				console.error('Error loading details:', error);
				content.innerHTML = `
					<div class="alert alert-danger">
						<h5>Error Loading Details</h5>
						<p>${error.message || 'Could not load booking details'}</p>
						<button class="btn btn-sm btn-primary mt-2" onclick="window.location.reload()">Refresh Page</button>
					</div>
				`;
			}
		});
	});

    /* --------------------------------------------------
       6) WhatsApp Auto-Send with Enhanced Message
       -------------------------------------------------- */
    document.querySelectorAll(".whatsapp-btn").forEach((btn) => {
        btn.addEventListener("click", function (e) {
            e.preventDefault();
            if (confirm("Send payment reminder via WhatsApp?")) {
                const phone = this.dataset.phone;
                const invoice = this.dataset.invoice;
                const deadline = this.dataset.deadline;
                const payment = this.dataset.payment || "payment";
                const name = this.dataset.name || "";

                let formattedPhone = phone.startsWith("0") ? "62" + phone.substring(1) : phone;
                const message = `Reminder: Your booking for invoice number ${invoice} with ${payment} left is due on ${deadline}. We appreciate your cooperation${name ? " " + name : ""}.`;

                window.open(`https://wa.me/${formattedPhone}?text=${encodeURIComponent(message)}`, "_blank");
                const originalHTML = this.innerHTML;
                this.innerHTML = '<i class="fas fa-check"></i> Sent';
                setTimeout(() => { this.innerHTML = originalHTML; }, 2000);
            }
        });
    });

    /* --------------------------------------------------
       7) Robust PDF Download Solution
       -------------------------------------------------- */
    async function downloadPDF(pdfType) {
		const btn = document.getElementById(`download${pdfType.charAt(0).toUpperCase() + pdfType.slice(1)}Pdf`);
		if (!btn) return;

		const originalHTML = btn.innerHTML;
		btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Preparing...';
		btn.disabled = true;

		try {
			// Add session verification and CSRF token if needed
			const params = new URLSearchParams({
				action: `${pdfType}_pdf`,
				_: Date.now(),
				// Add any additional required parameters
			});

			const response = await fetch(`/Jens-House/app/controller/payment_controller.php?action=${pdfType}_pdf`, {
				headers: {
					'Accept': 'application/pdf',
					'X-Requested-With': 'XMLHttpRequest'
				},
				credentials: 'include' // Include cookies if needed
			});

			// First check if we got a JSON error response
			const contentType = response.headers.get('content-type');
			if (contentType?.includes('application/json')) {
				const errorData = await response.json();
				throw new Error(errorData.message || 'PDF generation failed');
			}

			// Then verify PDF content
			if (!contentType?.includes('application/pdf')) {
				const text = await response.text();
				console.error('Server response:', text.substring(0, 500));
				throw new Error('Server did not return a PDF file');
			}

			const blob = await response.blob();
			if (blob.size === 0) {
				throw new Error('Received empty PDF file');
			}

			const url = window.URL.createObjectURL(blob);
			const a = document.createElement('a');
			a.href = url;
			a.download = `${pdfType}_bookings_${new Date().toISOString().slice(0,10)}.pdf`;
			
			document.body.appendChild(a);
			a.click();
			
			// Clean up
			setTimeout(() => {
				document.body.removeChild(a);
				window.URL.revokeObjectURL(url);
			}, 100);

		} catch (error) {
			console.error('PDF download failed:', error);
			
			// Show user-friendly error message
			let errorMessage = 'Failed to generate PDF.';
			if (error.message.includes('Server did not return')) {
				errorMessage = 'PDF generation failed. Please try again or contact support.';
			} else if (error.message.includes('session')) {
				errorMessage = 'Your session may have expired. Please refresh the page and try again.';
			}
			
			alert(errorMessage);
		} finally {
			btn.innerHTML = originalHTML;
			btn.disabled = false;
		}
	}
    // Initialize PDF download buttons with error handling
    function setupPDFDownloaders() {
        try {
            document.getElementById("downloadHistoryPdf")?.addEventListener("click", (e) => {
                e.preventDefault();
                downloadPDF("history");
            });

            document.getElementById("downloadActivePdf")?.addEventListener("click", (e) => {
                e.preventDefault();
                downloadPDF("active");
            });
        } catch (e) {
            console.error('Failed to setup PDF downloaders:', e);
        }
    }

    // Initialize with slight delay
    setTimeout(setupPDFDownloaders, 300);
});