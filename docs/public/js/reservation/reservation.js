document.addEventListener('DOMContentLoaded', function() {
    // State Management
    const state = {
        currentBooking: {
            type: null,
            subType: null,
            item: null,
            checkIn: null,
            checkOut: null,
            id: null,
            plan: null,
            venueAddons: {
                floral: false,
                angpao: false,
                cake: false,
                projector: false,
                dressing: false,
                catering: false,
                sound: false,
                pianist: false
            }
        },
        blockedDates: [],
        downpaymentApplied: false,
        originalSelectedDate: null,
        bookings: {},
        bookedDates: {
            'hotel-1': [],
            'hotel-2': [],
            'hotel-3': [],
            'housing-1': [],
            'housing-2': [],
            'venue': []
        },
        today: new Date(new Date().setHours(0, 0, 0, 0)),
        currentMonthOffset: 0,
        hotelSelectionStep: 0 // 0: none, 1: check-in selected, 2: check-out selected
    };

    // DOM Elements
    const elements = {
        modal: document.getElementById('calendarModal'),
        closeBtn: document.querySelector('.close-calendar'),
        cancelBtn: document.getElementById('cancelBooking'),
        bookBtn: document.getElementById('bookNow'),
        monthsContainer: document.getElementById('calendarMonths'),
        prevMonthBtn: document.createElement('button'),
        nextMonthBtn: document.createElement('button'),
        inputGroups: {
            regular: document.getElementById('regularInputs'),
            venue: document.getElementById('venueInputs'),
            housing: document.getElementById('housingInputs')
        },
        inputs: {
            checkIn: document.getElementById('checkIn'),
            checkOut: document.getElementById('checkOut'),
            person: document.getElementById('person'),
            venueDate: document.getElementById('venueDate'),
            headcount: document.getElementById('venueHeadcount'),
            housingDate: document.getElementById('housingStartDate'),
            housingEndDate: document.getElementById('housingEndDate'),
            floral: document.getElementById('venueFloral'),
            angpao: document.getElementById('venueAngpao'),
            cake: document.getElementById('venueCake'),
            projector: document.getElementById('venueProjector'),
            dressing: document.getElementById('venueDressing'),
            catering: document.getElementById('venueCatering'),
            sound: document.getElementById('venueSound'),
            pianist: document.getElementById('venuePianist'),
            venueDownpayment: document.getElementById('venueDownpayment'),
            housingDownpayment: document.getElementById('housingDownpayment')
        },
        summary: {
            container: document.getElementById('selected-items-container'),
            details: document.getElementById('payment-details'),
            total: document.getElementById('grand-total')
        }
    };
    
	async function processPayment(bookings, totalAmount) {
		try {
			// Validate housing bookings
			const housingBookings = bookings.filter(b => b.type.includes('housing'));
			housingBookings.forEach(booking => {
				if (!booking.endDate) {
					throw new Error(`Housing booking (${booking.title}) is missing end date`);
				}
			});
			
			if (bookings.length === 0) {
				throw new Error("Please book a hotel, housing, or venue first");
			}
			
			// Prepare booking data with proper field names for backend
			const bookingDetails = bookings.map(booking => {
				console.log("Processing booking:", booking);
				
				// Safely parse the price whether it's a string or number
				const price = typeof booking.price === 'string' ? 
					parsePrice(booking.price) : 
					Number(booking.price);
				
				const originalTotal = typeof booking.originalTotal === 'string' ?
					parsePrice(booking.originalTotal) :
					Number(booking.originalTotal || booking.price);

				const baseData = {
					type: booking.type.includes('housing') ? 'housing' : 
						  booking.type.includes('venue') ? 'venue' : 'hotel',
					title: booking.title,
					price: price,
					downpayment: booking.downpayment || false,
					originalTotal: originalTotal
				};

				// Add type-specific date fields using snake_case for backend
				if (baseData.type === 'housing') {
					return {
						...baseData,
						start_date: booking.startDate,  // Changed to snake_case
						end_date: booking.endDate,     // Changed to snake_case
						dates: booking.dates,
						plan: booking.plan,
						unit_id: booking.type.split('-')[1],
						// deadline will be calculated by backend
					};
				} else if (baseData.type === 'venue') {
					return {
						...baseData,
						date: booking.date,
						event_date: booking.date, // Same as date for venues
						guests: booking.guests,
						addons: booking.addons,
						// deadline will be calculated by backend
					};
				} else {
					// Hotel booking - no deadline
					return {
						...baseData,
						checkIn: booking.checkIn,
						checkOut: booking.checkOut,
						guests: booking.guests,
						room_id: booking.type.split('-')[1],
						// deadline will be handled by backend
					};
				}
			});

			// Prepare customer data
			const customerData = {
				name: document.getElementById('customerName')?.value || "Guest",
				email: document.getElementById('customerEmail')?.value || "guest@example.com",
				phone: document.getElementById('customerPhone')?.value || "0000000000"
			};

			// Calculate total amount from individual bookings
			const calculatedTotal = bookingDetails.reduce((sum, booking) => sum + booking.price, 0);
			
			const payload = {
				totalAmount: calculatedTotal,
				bookingDetails: bookingDetails,
				customer: customerData
			};

			console.log("Sending payload to server:", payload);

			const response = await fetch('http://localhost/Jens-House/app/controller/payment_controller.php', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
				},
				body: JSON.stringify(payload),
				credentials: 'include' // Important for session cookies
			});

			if (!response.ok) {
				const errorData = await response.json();
				console.error("Server response error:", errorData);
				throw new Error(errorData.message || `Payment failed (${response.status})`);
			}

			const data = await response.json();
			console.log("Server response:", data);
			
			if (!data.redirect_url) {
				throw new Error("Missing redirect URL in response");
			}

			return data;
			
		} catch (error) {
			console.error('Payment error:', error);
			showAlertPopup(error.message);
			throw error;
		}
	}

	// Helper function to parse price strings like "IDR 800,000"
	function parsePrice(priceStr) {
		const numericPart = priceStr.replace(/[^0-9]/g, '');
		return Number(numericPart);
	}

    // Update payment button handler
   document.querySelector('.btn-next').addEventListener('click', async function() {
		try {
			// Set the downpayment status based on the current booking
			state.currentBooking.downpayment = elements.inputs.venueDownpayment?.checked || 
											 elements.inputs.housingDownpayment?.checked || 
											 false;

			const bookings = Object.values(state.bookings).flat();
			const totalAmount = parsePrice(elements.summary.total.textContent);
			
			// Check if there are any bookings before proceeding
			if (bookings.length === 0) {
				showAlertPopup("Please book a hotel, housing, or venue first before proceeding to payment");
				return;
			}
			
			if (totalAmount <= 0) {
				showAlertPopup("Invalid booking amount. Please check your booking details.");
				return;
			}

			const transaction = await processPayment(bookings, totalAmount);
			window.location.href = transaction.redirect_url;
			
		} catch (error) {
			console.error('Payment error:', error);
			
			if (error.message.includes('gross_amount must be greater than or equal to 0.01')) {
				showAlertPopup("Invalid booking amount. Please check your booking details.");
			} else if (error.message.includes('Please book')) {
				showAlertPopup(error.message);
			} else if (error.message.includes('not logged in')) {
				window.location.href = '/Jens-House/public/default/login';
			} else {
				alert(error.message);
			}
		}
	});
	
	function showAlertPopup(message) {
		// Create popup container if it doesn't exist
		let popup = document.getElementById('customAlertPopup');
		if (!popup) {
			popup = document.createElement('div');
			popup.id = 'customAlertPopup';
			popup.style.position = 'fixed';
			popup.style.top = '0';
			popup.style.left = '0';
			popup.style.width = '100%';
			popup.style.height = '100%';
			popup.style.backgroundColor = 'rgba(0,0,0,0.5)';
			popup.style.display = 'flex';
			popup.style.justifyContent = 'center';
			popup.style.alignItems = 'center';
			popup.style.zIndex = '1000';
			
			const popupContent = document.createElement('div');
			popupContent.style.backgroundColor = 'white';
			popupContent.style.padding = '20px';
			popupContent.style.borderRadius = '8px';
			popupContent.style.maxWidth = '400px';
			popupContent.style.textAlign = 'center';
			
			const messageEl = document.createElement('p');
			messageEl.style.marginBottom = '20px';
			
			const okButton = document.createElement('button');
			okButton.textContent = 'OK';
			okButton.style.padding = '8px 16px';
			okButton.style.backgroundColor = '#4CAF50';
			okButton.style.color = 'white';
			okButton.style.border = 'none';
			okButton.style.borderRadius = '4px';
			okButton.style.cursor = 'pointer';
			
			okButton.addEventListener('click', function() {
				document.body.removeChild(popup);
			});
			
			popupContent.appendChild(messageEl);
			popupContent.appendChild(okButton);
			popup.appendChild(popupContent);
			document.body.appendChild(popup);
		}
		
		// Update message and show
		const messageEl = popup.querySelector('p');
		messageEl.textContent = message;
		popup.style.display = 'flex';
	}

    function createNavigationButtons() {
        elements.prevMonthBtn.innerHTML = '&lsaquo;';
        elements.prevMonthBtn.className = 'month-nav-btn prev';
        elements.prevMonthBtn.addEventListener('click', () => {
            state.currentMonthOffset--;
            renderCalendar();
        });

        elements.nextMonthBtn.innerHTML = '&rsaquo;';
        elements.nextMonthBtn.className = 'month-nav-btn next';
        elements.nextMonthBtn.addEventListener('click', () => {
            state.currentMonthOffset++;
            renderCalendar();
        });

        const monthHeaders = document.createElement('div');
        monthHeaders.className = 'month-headers';
        
        for (let i = 0; i < CONFIG.monthsToShow; i++) {
            const headerContainer = document.createElement('div');
            headerContainer.className = 'month-header-container';
            
            if (i === 0) headerContainer.appendChild(elements.prevMonthBtn);
            
            const monthHeader = document.createElement('h3');
            monthHeader.className = 'month-header';
            headerContainer.appendChild(monthHeader);
            
            if (i === CONFIG.monthsToShow - 1) headerContainer.appendChild(elements.nextMonthBtn);
            
            monthHeaders.appendChild(headerContainer);
        }
        
        elements.monthsContainer.parentNode.insertBefore(monthHeaders, elements.monthsContainer);
    }

    function setupEventListeners() {
        document.querySelectorAll('.rate-item button, .plan-item button, .venue-option .book-btn').forEach(btn => {
            btn.addEventListener('click', handleBookingButtonClick);
        });
        const handleDownpaymentToggle = () => {
			handleDownpaymentChange();
			updateSummaryDisplay();
		};

        const reRenderCalendar = () => renderCalendar();
        
        if (elements.inputs.venueDownpayment) {
			elements.inputs.venueDownpayment.addEventListener('change', handleDownpaymentToggle);
		}

		if (elements.inputs.housingDownpayment) {
			elements.inputs.housingDownpayment.addEventListener('change', handleDownpaymentToggle);
		}

        if (elements.closeBtn) elements.closeBtn.addEventListener('click', closeModal);
        if (elements.cancelBtn) elements.cancelBtn.addEventListener('click', closeModal);
        if (elements.modal) {
            elements.modal.addEventListener('click', e => {
                if (e.target === elements.modal) closeModal();
            });
        }
        
        if (elements.inputs.venueDownpayment) {
            elements.inputs.venueDownpayment.addEventListener('change', function() {
                updateSummaryDisplay();
            });
        }

        if (elements.inputs.housingDownpayment) {
            elements.inputs.housingDownpayment.addEventListener('change', function() {
                updateSummaryDisplay();
            });
        }
        
        if (elements.bookBtn) elements.bookBtn.addEventListener('click', confirmBooking);

        if (elements.inputs.headcount) {
            elements.inputs.headcount.addEventListener('change', function() {
                const headcount = parseInt(this.value);
                if (headcount < CONFIG.rates.venue.minHeadcount || headcount > CONFIG.rates.venue.maxHeadcount) {
                    alert(`Number of guests must be between ${CONFIG.rates.venue.minHeadcount} and ${CONFIG.rates.venue.maxHeadcount}`);
                    this.value = CONFIG.rates.venue.minHeadcount;
                }
            });
        }
    }
    
	function handleDownpaymentChange() {
		const wasDownpaymentApplied = state.downpaymentApplied;
		updateBlockedDates();
		
		// Only reset if downpayment status changed to true
		if (state.downpaymentApplied && (!wasDownpaymentApplied || state.downpaymentApplied !== wasDownpaymentApplied)) {
			// Reset dates based on current booking type
			switch(state.currentBooking.subType) {
				case 'venue':
					if (state.currentBooking.checkIn) {
						const selectedDate = new Date(state.currentBooking.checkIn);
						const today = new Date();
						today.setHours(0,0,0,0);
						const sixDaysLater = new Date(today);
						sixDaysLater.setDate(today.getDate() + 6);
						
						if (selectedDate >= today && selectedDate <= sixDaysLater) {
							alert('Down-payment requires booking at least 7 days in advance. Date selection has been reset.');
							state.currentBooking.checkIn = null;
							elements.inputs.venueDate.value = '';
						}
					}
					break;
					
				case 'housing':
					if (state.currentBooking.checkIn) {
						const selectedDate = new Date(state.currentBooking.checkIn);
						const today = new Date();
						today.setHours(0,0,0,0);
						const sixDaysLater = new Date(today);
						sixDaysLater.setDate(today.getDate() + 6);
						
						if (selectedDate >= today && selectedDate <= sixDaysLater) {
							alert('Downpayment requires booking at least 7 days in advance. Date selection has been reset.');
							state.currentBooking.checkIn = null;
							state.currentBooking.checkOut = null;
							elements.inputs.housingDate.value = '';
							elements.inputs.housingEndDate.value = '';
						}
					}
					break;
			}
		}
		renderCalendar();
	}
    
	function updateBlockedDates() {
		const venueDownpayment = elements.inputs.venueDownpayment?.checked;
		const housingDownpayment = elements.inputs.housingDownpayment?.checked;
		state.downpaymentApplied = venueDownpayment || housingDownpayment;
		
		if (state.downpaymentApplied) {
			const today = new Date();
			today.setHours(0,0,0,0);
			const sixDaysLater = new Date(today);
			sixDaysLater.setDate(today.getDate() + 6);
			
			state.blockedDates = [];
			let currentDate = new Date(today);
			
			while (currentDate <= sixDaysLater) {
				state.blockedDates.push(currentDate.toISOString().split('T')[0]);
				currentDate.setDate(currentDate.getDate() + 1);
			}
		} else {
			state.blockedDates = [];
		}
	}
	
	function resetDateInputs() {
		if (elements.inputs.venueDate) elements.inputs.venueDate.value = '';
		if (elements.inputs.housingDate) elements.inputs.housingDate.value = '';
		if (elements.inputs.housingEndDate) elements.inputs.housingEndDate.value = '';
		if (elements.inputs.checkIn) elements.inputs.checkIn.value = '';
		if (elements.inputs.checkOut) elements.inputs.checkOut.value = '';
	}

    function setupDateInputs() {
        const todayStr = state.today.toISOString().split('T')[0];
        if (elements.inputs.checkIn) elements.inputs.checkIn.min = todayStr;
        if (elements.inputs.checkOut) elements.inputs.checkOut.min = todayStr;
    }

    // Booking Flow Handlers
    function handleBookingButtonClick(e) {
        e.preventDefault();
        const button = e.currentTarget;
        let bookingContainer = button.closest('[data-booking-type]');
        
        if (!bookingContainer && button.classList.contains('book-btn')) {
            bookingContainer = button.closest('.venue-option');
        }

        const bookingType = bookingContainer.getAttribute('data-booking-type');
        const [subType, id, plan] = bookingType.split('-');
        
        state.currentBooking = {
            type: bookingType,
            subType: subType,
            id: id,
            plan: plan,
            item: bookingContainer,
            checkIn: null,
            checkOut: null
        };

        state.currentMonthOffset = 0;
        state.hotelSelectionStep = 0;
        showCalendar();
    }

    function showCalendar() {
		resetDownpaymentState(); // Reset when showing new calendar
		updateBlockedDates();
		resetSelection();
		updateInputVisibility();
		renderCalendar();
		elements.modal.classList.add('active');
	}

    function updateInputVisibility() {
        Object.values(elements.inputGroups).forEach(group => {
            group.style.display = 'none';
        });

        switch(state.currentBooking.subType) {
            case 'hotel':
                elements.inputGroups.regular.style.display = 'flex';
                break;
            case 'venue':
                elements.inputGroups.venue.style.display = 'flex';
                break;
            case 'housing':
                elements.inputGroups.housing.style.display = 'flex';
                const label = document.getElementById('housingDateLabel');
                if (label) {
                    const planText = 
                        state.currentBooking.plan === 'monthly' ? 'Monthly' : 
                        state.currentBooking.plan === 'quarterly' ? 'Quarterly' : 
                        'Yearly';
                    label.textContent = `${planText} Plan Start Date`;
                }
                break;
        }
    }

    // Calendar Rendering
    function renderCalendar() {
        elements.monthsContainer.innerHTML = '';
        updateNavButtons();

        const monthsWrapper = document.createElement('div');
        monthsWrapper.className = 'months-wrapper';

        const monthHeaders = document.querySelectorAll('.month-header');
        for (let i = 0; i < CONFIG.monthsToShow; i++) {
            const monthDate = new Date(state.today);
            monthDate.setMonth(state.today.getMonth() + state.currentMonthOffset + i);
            
            monthHeaders[i].textContent = monthDate.toLocaleDateString('en-US', { 
                month: 'long', 
                year: 'numeric' 
            }).toUpperCase();
            
            monthsWrapper.appendChild(renderMonth(monthDate));
        }

        elements.monthsContainer.appendChild(monthsWrapper);
    }

    function updateNavButtons() {
        elements.prevMonthBtn.disabled = (state.currentMonthOffset <= 0);
        elements.nextMonthBtn.disabled = (state.currentMonthOffset >= 12 - CONFIG.monthsToShow);
    }

    function renderMonth(date) {
        const monthContainer = document.createElement('div');
        monthContainer.className = 'month-container';
        
        const daysContainer = document.createElement('div');
        daysContainer.className = 'days';
        
        const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        dayNames.forEach(day => {
            const dayHeader = document.createElement('div');
            dayHeader.className = 'day-header';
            dayHeader.textContent = day;
            daysContainer.appendChild(dayHeader);
        });
        
        const firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
        const startingDay = firstDay.getDay();
        const lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);
        const daysInMonth = lastDay.getDate();
        const prevMonthLastDay = new Date(date.getFullYear(), date.getMonth(), 0);
        const daysFromPrevMonth = startingDay;
        const daysFromNextMonth = 42 - (daysFromPrevMonth + daysInMonth);
        
        for (let i = daysFromPrevMonth - 1; i >= 0; i--) {
            const prevDate = new Date(date.getFullYear(), date.getMonth() - 1, prevMonthLastDay.getDate() - i);
            daysContainer.appendChild(createDayElement(prevDate, true));
        }
        
        for (let i = 1; i <= daysInMonth; i++) {
            const currentDate = new Date(date.getFullYear(), date.getMonth(), i);
            daysContainer.appendChild(createDayElement(currentDate, false));
        }
        
        for (let i = 1; i <= daysFromNextMonth; i++) {
            const nextDate = new Date(date.getFullYear(), date.getMonth() + 1, i);
            daysContainer.appendChild(createDayElement(nextDate, true));
        }
        
        monthContainer.appendChild(daysContainer);
        return monthContainer;
    }

    function createDayElement(date, isDisabled) {
        const dayElement = document.createElement('div');
        dayElement.className = 'day';
        dayElement.textContent = date.getDate();
        const dateStr = date.toISOString().split('T')[0];
        dayElement.dataset.date = dateStr;

        // Get current downpayment status from checkboxes
        const venueDownpayment = elements.inputs.venueDownpayment?.checked;
        const housingDownpayment = elements.inputs.housingDownpayment?.checked;
        const downpaymentActive = venueDownpayment || housingDownpayment;
        
        // Calculate date restrictions
        const today = new Date();
        const sixDaysLater = new Date(today);
        sixDaysLater.setDate(today.getDate() + 6);
        
        const checkDate = new Date(date);
        checkDate.setHours(0, 0, 0, 0);
        const todayClean = new Date(today);
        todayClean.setHours(0, 0, 0, 0);
        const isBlocked = state.blockedDates.includes(dateStr);
      
        if (downpaymentActive && checkDate >= todayClean && checkDate <= sixDaysLater) {
			isDisabled = true;
		}
      
        if (isDisabled || date < state.today || isBlocked) {
            dayElement.classList.add('disabled');
        } else {
            let isBooked = false;
            if (state.currentBooking.type) {
                const bookingType = state.currentBooking.type.startsWith('housing') 
                    ? `housing-${state.currentBooking.id}`
                    : state.currentBooking.type;
                
                isBooked = state.bookedDates[bookingType]?.includes(dateStr);
            } else if (state.currentBooking.type.startsWith('housing')) {
                const unit = state.currentBooking.id;
                isBooked = state.bookedDates[`housing-${unit}`]?.includes(dateStr);
            } else if (state.currentBooking.type === 'venue') {
                isBooked = state.bookedDates['venue']?.includes(dateStr);
            }
            
            if (isBooked) {
                dayElement.classList.add('booked');
            } else {
                dayElement.addEventListener('click', () => handleDaySelection(date));
            }
        }
        
        return dayElement;
    }
	
	function resetDownpaymentState() {
		state.downpaymentApplied = false;
		state.blockedDates = [];
		if (elements.inputs.venueDownpayment) elements.inputs.venueDownpayment.checked = false;
		if (elements.inputs.housingDownpayment) elements.inputs.housingDownpayment.checked = false;
	}

    // Date Selection Handling
    function handleDaySelection(date) {
        const dateStr = date.toISOString().split('T')[0];
        
        if (!state.originalSelectedDate) {
            state.originalSelectedDate = dateStr;
        }
        
        switch(state.currentBooking.subType) {
            case 'venue':
                selectVenueDate(dateStr);
                break;
            case 'housing':
                selectHousingDates(date);
                break;
            default:
                selectHotelDates(date, dateStr);
        }
    }

    function selectVenueDate(dateStr) {
        resetSelection();
        state.currentBooking.checkIn = dateStr;
        elements.inputs.venueDate.value = formatDate(dateStr, true);
        markSelectedDate(dateStr);
    }
    
    function hasDateConflicts(startDate, endDate, bookingType) {
        const datesToCheck = getDatesBetween(new Date(startDate), new Date(endDate));
        return datesToCheck.some(date => 
            state.bookedDates[bookingType]?.includes(date)
        );
    }

    function selectHousingDates(startDate) {
		resetSelection();
		
		const endDate = new Date(startDate);
		if (state.currentBooking.plan === 'monthly') {
			endDate.setMonth(startDate.getMonth() + 1);
		} else if (state.currentBooking.plan === 'quarterly') {
			endDate.setMonth(startDate.getMonth() + 3);
		} else {
			endDate.setFullYear(startDate.getFullYear() + 1);
		}
		endDate.setDate(endDate.getDate() - 1);

		// Store dates in state as ISO strings (YYYY-MM-DD)
		state.currentBooking.checkIn = startDate.toISOString().split('T')[0];
		state.currentBooking.checkOut = endDate.toISOString().split('T')[0];

		// Update UI
		elements.inputs.housingDate.value = formatDisplayDate(state.currentBooking.checkIn, true);
		elements.inputs.housingEndDate.value = formatDisplayDate(state.currentBooking.checkOut, true);
		highlightDateRange(state.currentBooking.checkIn, state.currentBooking.checkOut);
		
	}
    
    function calculateMinStay() {
        if (state.currentBooking.subType === 'housing') {
            const plan = state.currentBooking.plan;
            return plan === 'monthly' ? 30 : 
                   plan === 'quarterly' ? 90 : 
                   365;
        }
        return 1;
    }

    function selectHotelDates(date, dateStr) {
		if (state.hotelSelectionStep === 0 || state.hotelSelectionStep === 2) {
			// First selection or starting new selection
			resetSelection();
			const checkInDate = new Date(date);
			checkInDate.setHours(0, 0, 0, 0);
			state.currentBooking.checkIn = formatDateForBackend(checkInDate.toISOString().split('T')[0]);
			state.hotelSelectionStep = 1;
			
			elements.inputs.checkIn.value = formatDisplayDate(state.currentBooking.checkIn, true);
			markSelectedDate(state.currentBooking.checkIn);
		} 
		else if (state.hotelSelectionStep === 1) {
			if (date < new Date(state.currentBooking.checkIn)) {
				alert("Check-out date cannot be before check-in date. Please select a new check-out date.");
				
				// Reset only the check-out selection
				state.currentBooking.checkOut = null;
				elements.inputs.checkOut.value = '';
				
				// Clear any selection highlights
				document.querySelectorAll('.day').forEach(day => {
					day.classList.remove('selected', 'auto-selected-range');
				});
				
				// Re-highlight the check-in date
				markSelectedDate(state.currentBooking.checkIn);
				return;
			} 
			else if (date > new Date(state.currentBooking.checkIn)) {
				// Normal case - date is after check-in
				const minStay = calculateMinStay();
				const nights = calculateNights(state.currentBooking.checkIn, dateStr);
				
				if (nights < minStay) {
					alert(`Minimum stay required: ${minStay} nights`);
					return;
				}
				
				const checkOutDate = new Date(date);
				checkOutDate.setHours(0, 0, 0, 0);
				state.currentBooking.checkOut = formatDateForBackend(checkOutDate.toISOString().split('T')[0]);
				state.hotelSelectionStep = 2;
				
				elements.inputs.checkOut.value = formatDisplayDate(state.currentBooking.checkOut, true);
				markSelectedDate(dateStr);
				highlightDateRange(state.currentBooking.checkIn, state.currentBooking.checkOut);
			} else {
				return;
			}
		}
	}
    
    function markSelectedDate(dateStr) {
        document.querySelectorAll('.day').forEach(day => {
            day.classList.remove('selected');
        });
        
        const dayElement = document.querySelector(`.day[data-date="${dateStr}"]`);
        if (dayElement) {
            dayElement.classList.add('selected');
        }
    }

    function highlightDateRange(start, end, isAutoSelected = false) {
        const startDate = new Date(start);
        const endDate = new Date(end);
        
        document.querySelectorAll('.day:not(.disabled):not(.booked)').forEach(day => {
            const dayDate = new Date(day.dataset.date);
            if (dayDate >= startDate && dayDate <= endDate) {
                day.classList.add(isAutoSelected ? 'auto-selected-range' : 'selected');
            }
        });
    }

    function resetSelection() {
        document.querySelectorAll('.day').forEach(day => {
            day.classList.remove('selected', 'auto-selected-range');
        });
        state.currentBooking.checkIn = null;
        state.currentBooking.checkOut = null;
        state.originalSelectedDate = null;
    }

	function confirmBooking() {
		const downpaymentActive = elements.inputs.venueDownpayment?.checked || 
								elements.inputs.housingDownpayment?.checked;
		
		if (state.currentBooking.subType === 'housing') {
			if (!state.currentBooking.checkIn || !state.currentBooking.checkOut) {
				alert('Please select valid start and end dates for the housing booking');
				return;
			}
		}

        if (!validateBooking()) return;

        const bookings = createBooking();
        if (!bookings.length) return;
        
        bookings.forEach(booking => {
            if (!state.bookings[booking.type]) {
                state.bookings[booking.type] = [];
            }
            state.bookings[booking.type].push(booking);
            
            if (state.currentBooking.subType === 'housing' && booking.dates) {
                const bookingType = `housing-${state.currentBooking.id}`;
                const allDates = getDatesBetween(
                    new Date(booking.dates[0]),
                    new Date(booking.dates[booking.dates.length - 1])
                );
                
                state.bookedDates[bookingType] = [
                    ...new Set([...state.bookedDates[bookingType] || [], ...allDates])
                ];
            }
        });
        
        if (state.currentBooking.subType !== 'housing') {
            const booking = bookings[0];
            if (booking.dates && booking.dates.length > 0) {
                let bookingTypeKey = '';
                
                if (state.currentBooking.subType === 'hotel') {
                    bookingTypeKey = state.currentBooking.type;
                } else if (state.currentBooking.subType === 'venue') {
                    bookingTypeKey = 'venue';
                }
                
                if (bookingTypeKey) {
                    state.bookedDates[bookingTypeKey] = [
                        ...new Set([...state.bookedDates[bookingTypeKey], ...booking.dates])
                    ];
                }
            }
        }

        updateSummaryDisplay();
        closeModal();
        renderCalendar();
        
        const paymentSection = document.querySelector('.payment-summary-section');
        if (paymentSection) {
            paymentSection.scrollIntoView({ behavior: 'smooth' });
        }
    }
    
    function clearDateInputs() {
        elements.inputs.venueDate.value = '';
        elements.inputs.housingDate.value = '';
        elements.inputs.housingEndDate.value = '';
        elements.inputs.checkIn.value = '';
        elements.inputs.checkOut.value = '';
        
        state.currentBooking.checkIn = null;
        state.currentBooking.checkOut = null;
        state.originalSelectedDate = null;
    }

    function validateBooking() {
		let isValid = true;
		Object.values(state.bookings).flat().forEach(booking => {
			if (booking.downpayment) {
				try {
					// Get the relevant date based on booking type
					let bookingDate;
					if (booking.date) { // Venue booking
						bookingDate = new Date(booking.date);
					} else if (booking.checkIn) { // Hotel booking
						bookingDate = new Date(booking.checkIn);
					} else if (booking.startDate) { // Housing booking
						bookingDate = new Date(booking.startDate);
					} else {
						console.error("No valid date found in booking:", booking);
						throw new Error("Booking has no valid date");
					}

					if (isNaN(bookingDate.getTime())) {
						console.error("Invalid date in booking:", booking);
						throw new Error("Invalid date format");
					}

					const bookingDateStr = bookingDate.toISOString().split('T')[0];
					const today = new Date();
					today.setHours(0, 0, 0, 0);
					const sixDaysLater = new Date(today);
					sixDaysLater.setDate(today.getDate() + 6);

					if (bookingDate >= today && bookingDate <= sixDaysLater) {
						const itemName = booking.title || booking.type;
						showAlertPopup(`Cannot book ${itemName} with downpayment within 7 days`);
						isValid = false;
					}
				} catch (e) {
					console.error('Date validation error:', e);
					isValid = false;
				}
			}
		});

		if (!isValid) return false;

		// Validate current booking based on type
		switch(state.currentBooking.subType) {
			case 'venue':
				if (!state.currentBooking.checkIn) {
					showAlertPopup('Please select an event date');
					return false;
				}
				try {
					const venueDate = new Date(state.currentBooking.checkIn);
					if (isNaN(venueDate.getTime())) {
						showAlertPopup('Invalid venue date selected');
						return false;
					}
				} catch (e) {
					showAlertPopup('Invalid venue date format');
					return false;
				}
				// Validate headcount
				const headcount = parseInt(elements.inputs.headcount.value) || 0;
				if (headcount < CONFIG.rates.venue.minHeadcount || headcount > CONFIG.rates.venue.maxHeadcount) {
					showAlertPopup(`Number of guests must be between ${CONFIG.rates.venue.minHeadcount} and ${CONFIG.rates.venue.maxHeadcount}`);
					return false;
				}
				return true;

			case 'housing':
				if (!state.currentBooking.checkIn || !state.currentBooking.checkOut) {
					showAlertPopup('Please select valid start and end dates');
					return false;
				}
				try {
					const startDate = new Date(state.currentBooking.checkIn);
					const endDate = new Date(state.currentBooking.checkOut);
					if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
						showAlertPopup('Invalid housing dates selected');
						return false;
					}
				} catch (e) {
					showAlertPopup('Invalid housing date format');
					return false;
				}
				// Check for date conflicts
				const bookingType = `housing-${state.currentBooking.id}`;
				if (hasDateConflicts(
					state.currentBooking.checkIn,
					state.currentBooking.checkOut,
					bookingType
				)) {
					showAlertPopup('Selected dates conflict with existing bookings');
					return false;
				}
				return true;

			default: // Hotel booking
				if (!state.currentBooking.checkIn || !state.currentBooking.checkOut) {
					showAlertPopup('Please select both check-in and check-out dates');
					return false;
				}
				try {
					const checkIn = new Date(state.currentBooking.checkIn);
					const checkOut = new Date(state.currentBooking.checkOut);
					if (isNaN(checkIn.getTime()) || isNaN(checkOut.getTime())) {
						showAlertPopup('Invalid hotel dates selected');
						return false;
					}
				} catch (e) {
					showAlertPopup('Invalid hotel date format');
					return false;
				}
				return true;
		}
	}

    function createBooking() {
        const bookings = [];
        
        switch(state.currentBooking.subType) {
            case 'venue':
                bookings.push(createVenueBooking());
                break;
            case 'housing':
                bookings.push(createHousingBooking());
                break;
            default:
                bookings.push(createHotelBooking());
        }
        
        return bookings;
    }
	
	function setupHousingDates() {
		const startDateInput = document.getElementById('housingDate');
		const endDateInput = document.getElementById('housingEndDate');
		
		startDateInput.addEventListener('change', function() {
			if (!this.value) return;
			
			const startDate = new Date(this.value);
			const endDate = new Date(startDate);
			
			// Calculate end date based on plan
			if (state.currentBooking.plan === 'monthly') {
				endDate.setMonth(startDate.getMonth() + 1);
			} else if (state.currentBooking.plan === 'quarterly') {
				endDate.setMonth(startDate.getMonth() + 3);
			} else {
				endDate.setFullYear(startDate.getFullYear() + 1);
			}
			endDate.setDate(endDate.getDate() - 1); // Adjust to day before
			
			// Format and set end date
			const formattedEndDate = endDate.toISOString().split('T')[0];
			endDateInput.value = formattedEndDate;
			
			// Store in state
			state.currentBooking.endDate = formattedEndDate;
		});
	}

    function createVenueBooking() {
		const title = state.currentBooking.item.querySelector('h3')?.textContent || 'Venue';
		const basePrice = CONFIG.rates.venue.base;
		const downpayment = elements.inputs.venueDownpayment?.checked;
		state.currentBooking.downpayment = downpayment;
		let headcount = parseInt(elements.inputs.headcount.value) || CONFIG.rates.venue.minHeadcount;
        
        headcount = Math.min(Math.max(headcount, CONFIG.rates.venue.minHeadcount), CONFIG.rates.venue.maxHeadcount);
        
        const extraGuests = Math.max(0, headcount - CONFIG.rates.venue.minHeadcount);
        const extraGuestsUnits = Math.ceil(extraGuests / CONFIG.rates.venue.headcountIncrement);
        const extraGuestsCost = extraGuestsUnits * CONFIG.rates.venue.headcountPrice;
        
        let addons = [
            { name: 'Floral Arrangement', price: 'IDR 0' },
            { name: 'Dressing Room', price: 'IDR 0' }
        ];
        let addonsTotal = 0;
        
        const otherAddons = ['angpao', 'cake', 'projector', 'catering', 'sound', 'pianist'];
        otherAddons.forEach(addonId => {
            if (elements.inputs[addonId]?.checked) {
                let price = CONFIG.rates.venue.addons[addonId].price;
                if (addonId === 'catering') {
                    const cateringUnits = Math.ceil(headcount / CONFIG.rates.venue.addons.catering.increment);
                    price = CONFIG.rates.venue.addons.catering.base + 
                           (CONFIG.rates.venue.addons.catering.price * (cateringUnits - 1));
                }
                
                addons.push({
                    name: CONFIG.rates.venue.addons[addonId].name,
                    price: `IDR ${price.toLocaleString()}`
                });
                addonsTotal += price;
            }
        });
        
        let totalPrice = basePrice + extraGuestsCost + addonsTotal;
        
        if (downpayment) {
            totalPrice = Math.round(totalPrice * 0.5);
        }
        
        addons.unshift({
            name: 'Venue Base Price',
            price: `IDR ${basePrice.toLocaleString()}`
        });
        
        if (extraGuests > 0) {
            addons.unshift({
                name: `Extra Guests (${extraGuests})`,
                price: `IDR ${extraGuestsCost.toLocaleString()}`
            });
        }
        
       return {
			type: 'venue',
			title: title,
			date: state.currentBooking.checkIn, // Event date
			bookingDate: new Date().toISOString().split('T')[0], // When booking was made
			dates: [state.currentBooking.checkIn],
			guests: headcount,
			price: `IDR ${totalPrice.toLocaleString()}`,
			duration: '1 day',
			addons: addons,
			originalTotal: basePrice + extraGuestsCost + addonsTotal,
			downpayment: downpayment
		};
	}

	function createHousingBooking() {
		const title = `House ${state.currentBooking.id}`;
		let basePrice = parsePrice(state.currentBooking.item.querySelector('.plan-price')?.textContent);
		const downpayment = elements.inputs.housingDownpayment?.checked;
		
		// Apply 50% discount if downpayment
		let totalPrice = basePrice;
		if (downpayment) {
			totalPrice = Math.round(basePrice * 0.5);
		}

		return {
			type: `housing-${state.currentBooking.id}`,
			title: title,
			plan: state.currentBooking.plan === 'monthly' ? 'Monthly' : 
				  state.currentBooking.plan === 'quarterly' ? 'Quarterly' : 'Yearly',
			startDate: state.currentBooking.checkIn,
			endDate: state.currentBooking.checkOut,
			dates: getDatesBetween(new Date(state.currentBooking.checkIn), new Date(state.currentBooking.checkOut)),
			price: `IDR ${totalPrice.toLocaleString()}`,
			originalTotal: basePrice, // Preserve original price
			downpayment: downpayment, // Use the local downpayment variable
			addons: []
		};
	}
	
	function formatDateForBackend(dateStr) {
		const [year, month, day] = dateStr.split('-');
		return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
	}
	
   function createHotelBooking() {
		const roomName = state.currentBooking.item.dataset.roomName || 
					   `Hotel Room ${state.currentBooking.id.toUpperCase()}`;
		const price = calculateHotelPrice();
		const guests = parseInt(elements.inputs.person.value) || 1;
		
		return {
			type: state.currentBooking.type,
			title: roomName,
			checkIn: state.currentBooking.checkIn,  // YYYY-MM-DD format
			checkOut: state.currentBooking.checkOut, // YYYY-MM-DD format
			guests: guests,
			dates: getDatesBetween(new Date(state.currentBooking.checkIn), 
								 new Date(state.currentBooking.checkOut)),
			price: price,
			originalTotal: parsePrice(price),
			downpayment: false,  // Hotel doesn't support downpayment
			addons: []
		};
	}

    function calculateHotelPrice() {
		if (!state.currentBooking.checkIn || !state.currentBooking.checkOut) return 'IDR 0';
		
		const nights = calculateNights(state.currentBooking.checkIn, state.currentBooking.checkOut);
		const guests = parseInt(elements.inputs.person.value) || 1;
		const baseRate = CONFIG.rates.hotel[state.currentBooking.type];
		const extraNightRate = 50000; // Fixed rate for additional nights
		
		// First night calculation
		let price = baseRate;
		
		// Extra guest charge (only for first night)
		if (guests > 2) {
			const extraGuests = guests - 2;
			const surchargePerGuest = 50000;
			price += extraGuests * surchargePerGuest;
		}
		
		// Additional nights (without extra guest charges)
		if (nights > 1) {
			price += extraNightRate * (nights - 1);
		}
		
		return `IDR ${Math.round(price).toLocaleString()}`;
	}

    // Summary Display
    function updateSummaryDisplay() {
        elements.summary.container.innerHTML = '';
        elements.summary.details.innerHTML = '';
        
        let grandTotal = 0;
        
        for (const [type, bookings] of Object.entries(state.bookings)) {
            bookings.forEach(booking => {
                addBookingToSummary(booking);
                grandTotal += parsePrice(booking.price);
            });
        }
        
        elements.summary.total.textContent = `IDR ${grandTotal.toLocaleString()}`;
    }

    function addBookingToSummary(booking) {
		const itemElement = document.createElement('div');
		itemElement.className = 'selected-item';
		
		let content = `<h4>${booking.title}</h4>`;
			
		// Display dates based on booking type
		if (booking.date) {
			content += `<p>Date: ${formatDisplayDate(booking.date, true)}</p>`;
		} else if (booking.checkIn && booking.checkOut) {
			content += `<p>Dates: ${formatDisplayDate(booking.checkIn, true)} to ${formatDisplayDate(booking.checkOut, true)}</p>`;
		} else if (booking.startDate && booking.endDate) {
			content += `<p>Dates: ${formatDisplayDate(booking.startDate, true)} to ${formatDisplayDate(booking.endDate, true)}</p>`;
		}
		
		// Display plan if it's a housing booking
		if (booking.plan) {
			content += `<p>Plan: ${booking.plan}</p>`;
		}
	
		if (booking.guests) {
			if (booking.type === 'venue') {
				content += `<p>Guests: ${booking.guests} people</p>`;
			} else if (booking.type.includes('hotel')) {
				const nights = calculateNights(booking.checkIn, booking.checkOut);
				const baseRate = CONFIG.rates.hotel[booking.type];
				const extraNightRate = 50000;
				
				content += `<p>Guests: ${booking.guests} ${booking.guests > 1 ? 'people' : 'person'}</p>`;
				
				// First night pricing
				content += `<div class="base-price"><p>First night: IDR ${baseRate.toLocaleString()}</p></div>`;
				
				// Extra guest charges (first night only)
				if (booking.guests > 2) {
					const extraGuests = booking.guests - 2;
					const extraGuestCharge = extraGuests * 50000;
					content += `<div class="extra-guest-charge"><p>+${extraGuests} extra ${extraGuests > 1 ? 'guests' : 'guest'} (IDR ${extraGuestCharge.toLocaleString()})</p></div>`;
				}
				
				// Additional nights pricing
				if (nights > 1) {
					const extraNights = nights - 1;
					const totalExtraNightsCost = extraNightRate * extraNights;
					content += `
						<div class="extra-nights-charge">
							<p>+${extraNights} additional ${extraNights > 1 ? 'nights' : 'night'} (IDR ${extraNightRate.toLocaleString()}/night) = IDR ${totalExtraNightsCost.toLocaleString()}</p>
						</div>
					`;
				}
			}
		}
		
		// Display addons if they exist
		if (booking.addons && booking.addons.length > 0) {
			content += `<div class="addons-section"><p>Addons:</p><ul>`;
			
			booking.addons.forEach(addon => {
				// Skip base price and extra guests as they're already shown
				if (!addon.name.includes('Base Price') && !addon.name.includes('Extra Guests')) {
					content += `<li>${addon.name}: ${addon.price}</li>`;
				}
			});
			
			content += `</ul></div>`;
		}
		
		// Show downpayment information if applicable
		if (booking.downpayment) {
			content += `
				<div class="payment-notice">
					<p>50% Down Payment Applied</p>
					<p class="original-price">Original Total: IDR ${booking.originalTotal.toLocaleString()}</p>
				</div>
			`;
		}
		
		content += `<p class="total-cost">Total Cost: ${booking.price}</p>`;
		
		itemElement.innerHTML = content;
		elements.summary.container.appendChild(itemElement);
		
		// Create payment summary line item
		const paymentItem = document.createElement('div');
		paymentItem.className = 'payment-item';
		
		let paymentDetails = `${booking.title}`;
		if (booking.guests) {
			paymentDetails += ` (${booking.guests} ${booking.type === 'venue' ? 'guests' : 'people'})`;
		}
		
		// Include addons in payment details if they exist
		if (booking.addons && booking.addons.length > 0) {
			const addonCount = booking.addons.filter(a => 
				!a.name.includes('Base Price') && !a.name.includes('Extra Guests')
			).length;
			if (addonCount > 0) {
				paymentDetails += ` +${addonCount} addon${addonCount > 1 ? 's' : ''}`;
			}
		}
		
		paymentItem.innerHTML = `
			<span>${paymentDetails}</span>
			<span>${booking.price}</span>
		`;
		elements.summary.details.appendChild(paymentItem);
	}

    // Utility Functions
    function closeModal() {
		resetDownpaymentState(); // Reset when closing modal
		elements.modal.classList.remove('active');
		resetSelection();
	}

    function formatDate(dateString, addOneDay = false) {
        if (!dateString) return '';
        const date = new Date(dateString);
        if (addOneDay) {
            date.setDate(date.getDate() + 1);
        }
        const [year, month, day] = date.toISOString().split('T')[0].split('-');
        return `${day.padStart(2, '0')}/${month.padStart(2, '0')}/${year}`;
    }

    function formatDisplayDate(dateString, addOneDay = false) {
        if (!dateString) return '';
        const date = new Date(dateString);
        if (addOneDay) {
            date.setDate(date.getDate() + 1);
        }
        return date.toLocaleDateString('en-GB', CONFIG.dateFormatOptions);
    }

    function extractPrice(priceText) {
        if (!priceText) return 'IDR 0';
        const matches = priceText.match(/([A-Z]{3})?\s*([\d,]+\.?\d*)/);
        if (matches && matches.length >= 3) {
            const currency = matches[1] || 'IDR';
            const amount = matches[2].replace(/,/g, '');
            return `${currency} ${parseFloat(amount).toLocaleString()}`;
        }
        return priceText;
    }

    function parsePrice(priceString) {
		if (typeof priceString === 'number') {
			return priceString;
		}
		
		if (typeof priceString !== 'string') {
			console.warn('parsePrice received non-string value:', priceString);
			return 0;
		}
		
		// Remove all non-numeric characters except decimal point
		const numericString = priceString.replace(/[^0-9.]/g, '');
		return parseFloat(numericString) || 0;
	}

    function calculateNights(start, end) {
        const startDate = new Date(start);
        const endDate = new Date(end);
        return Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24));
    }

    function getDatesBetween(startDate, endDate) {
		const dates = [];
		const current = new Date(startDate);
		while (current <= endDate) {
			dates.push(current.toISOString().split('T')[0]);
			current.setDate(current.getDate() + 1);
		}
		return dates;
	}
    
    function init() {
        createNavigationButtons();
        setupEventListeners();
        setupDateInputs();
    }
    
    init();
});