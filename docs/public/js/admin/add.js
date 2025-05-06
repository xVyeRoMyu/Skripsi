document.addEventListener('DOMContentLoaded', function() {
    // Admin floating button functionality
    const adminBtn = document.querySelector('.admin-floating-btn .btn-add');
    if (adminBtn) {
        adminBtn.addEventListener('click', function(e) {
            e.preventDefault();
            showAdminForm();
        });
    }

    function showAdminForm() {
        // Create the modal backdrop
        const modal = document.createElement('div');
        modal.className = 'admin-modal';
        modal.style.opacity = '0';
        modal.style.transition = 'opacity 0.3s ease';
        
        modal.innerHTML = `
            <div class="admin-modal-content">
                <span class="close-admin-modal">&times;</span>
                <h3>Add New Property</h3>
                <form id="adminPropertyForm">
                    <div class="form-group">
                        <label for="propertyType">Property Type:</label>
                        <select id="propertyType" required class="styled-select">
                            <option value="">Select Type</option>
                            <option value="hotel">Hotel</option>
                            <option value="housing">Housing</option>
                            <option value="venue">Venue</option>
                        </select>
                    </div>
                    
                    <div id="propertyDetails" style="display: none;">
                        <!-- Dynamic content will be inserted here based on property type -->
                    </div>
                </form>
            </div>
        `;

        document.body.appendChild(modal);
        
        // Fade in effect
        setTimeout(() => { modal.style.opacity = '1'; }, 10);
        
        // Close modal handler with fade out
        modal.querySelector('.close-admin-modal').addEventListener('click', function() {
            modal.style.opacity = '0';
            setTimeout(() => {
                document.body.removeChild(modal);
            }, 300);
        });
        
        // Close when clicking outside with fade out
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.style.opacity = '0';
                setTimeout(() => {
                    document.body.removeChild(modal);
                }, 300);
            }
        });
        
        // Handle property type change
        const propertyTypeSelect = modal.querySelector('#propertyType');
        propertyTypeSelect.addEventListener('change', function() {
            updatePropertyDetails(this.value, modal);
        });
        
        // Form submission handler
        modal.querySelector('#adminPropertyForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (propertyTypeSelect.value === 'hotel') {
                addHotelRoom(modal);
            } else if (propertyTypeSelect.value === 'housing') {
                addHousing(modal);
            } else if (propertyTypeSelect.value === 'venue') {
                addVenue(modal);
            }
            
            // Close the modal
            modal.style.opacity = '0';
            setTimeout(() => {
                document.body.removeChild(modal);
            }, 300);
        });
    }

    // HOTEL PROPERTY FUNCTION
    function addHotelRoom(modal) {
        const roomName = modal.querySelector('#roomName').value;
        const bedType = modal.querySelector('#bedType').value;
        const guestQuantity = modal.querySelector('#guestQuantity').value;
        const roomPrice = modal.querySelector('#roomPrice').value;
        
        // Get features without the "close" text
        const features = Array.from(modal.querySelectorAll('.features-list li')).map(li => {
            const temp = document.createElement('span');
            temp.innerHTML = li.innerHTML;
            const button = temp.querySelector('button');
            if (button) button.remove();
            return temp.textContent.trim();
        }).filter(feature => feature);
        
        // Get media files
        const mediaInputs = modal.querySelectorAll('.media-input');
        const mediaFiles = Array.from(mediaInputs).map(input => input.files[0]).filter(file => file);
        
        // Create a new hotel section
        const hotelContainer = document.querySelector('.hotel-section .row .col-md-8');
        const newSectionNumber = hotelContainer.children.length + 1;
        
        const newHotelSection = document.createElement('div');
        newHotelSection.className = `hotel-section-${newSectionNumber}`;
        
        // Create the HTML structure for the new hotel room
        newHotelSection.innerHTML = `
            <div class="room-image mb-3">
                <div class="wrapper" role="group" aria-label="Room image slider">
                    <input type="radio" name="slide-${newSectionNumber}" id="slide-${newSectionNumber}-one" checked>
                    <input type="radio" name="slide-${newSectionNumber}" id="slide-${newSectionNumber}-two">
                    <input type="radio" name="slide-${newSectionNumber}" id="slide-${newSectionNumber}-three">
                    <!-- Slides will be added dynamically -->
                    <div class="navigation-arrows">
                        <button class="arrow prev" aria-label="Previous slide">❮</button>
                        <button class="arrow next" aria-label="Next slide">❯</button>
                    </div>
                    <div class="sliders">
                        <label for="slide-${newSectionNumber}-one" class="one"></label>
                        <label for="slide-${newSectionNumber}-two" class="two"></label>
                        <label for="slide-${newSectionNumber}-three" class="three"></label>
                    </div>
                </div>
            </div>
            <div class="room-info">
                <h3>${bedType === 'single' ? 'Single bed' : 'Double bed'} &mdash; ${guestQuantity} person room</h3>
                <div class="rate-item" data-booking-type="hotel-${newSectionNumber}" data-room-name="${roomName}">
                    <div class="rate-details">
                        <h4>${roomName}</h4>
                        <ul>
                            ${features.map(feature => `<li>${feature}</li>`).join('')}
                        </ul>
                    </div>
                    <div class="rate-price">IDR ${parseInt(roomPrice.replace(/,/g, '')).toLocaleString()}/day</div>
                    <button class="book-btn">Book Now</button>
                </div>
            </div>
        `;
        
        // Add the slides with media
        const wrapper = newHotelSection.querySelector('.wrapper');
        
        mediaFiles.forEach((file, index) => {
            const isVideo = file.type.includes('video');
            const slide = document.createElement('div');
            slide.className = `img img-${index + 1}`;
            
            if (isVideo) {
                slide.classList.add('video-container');
                slide.innerHTML = `
                    <video muted loop playsinline>
                        <source src="${URL.createObjectURL(file)}" type="${file.type}">
                    </video>
                `;
            } else {
                slide.innerHTML = `<img src="${URL.createObjectURL(file)}" alt="${roomName}">`;
            }
            
            wrapper.insertBefore(slide, wrapper.querySelector('.navigation-arrows'));
        });

        // Add the new section to the DOM
        hotelContainer.appendChild(newHotelSection);
        
        // Initialize the slider for the new section
        if (typeof initSlider === 'function') {
            initSlider(wrapper);
        }
        
        // Initialize the booking button with proper event handling
        const bookBtn = newHotelSection.querySelector('.book-btn');
        if (bookBtn) {
            bookBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Check if reservation.js is loaded and has the handler
                if (typeof handleBookingButtonClick === 'function') {
                    handleBookingButtonClick(e);
                } else {
                    // Fallback behavior if reservation.js isn't loaded
                    console.warn('Booking system not fully initialized');
                    alert('Booking system is loading, please try again shortly');
                }
            });
        }
        
        // Scroll to the new section
        setTimeout(() => {
            newHotelSection.scrollIntoView({ behavior: 'smooth' });
        }, 100);
    }

    // HOUSING PROPERTY FUNCTION
    function addHousing(modal) {
        const housingName = modal.querySelector('#housingName').value;
        const housingType = modal.querySelector('#housingType').value;
        const housingPlan = modal.querySelector('#housingPlan').value;
        const housingPrice = modal.querySelector('#housingPrice').value;
        
        // Get features without the "close" text
        const features = Array.from(modal.querySelectorAll('.features-list li')).map(li => {
            const temp = document.createElement('span');
            temp.innerHTML = li.innerHTML;
            const button = temp.querySelector('button');
            if (button) button.remove();
            return temp.textContent.trim();
        }).filter(feature => feature);
        
        // Get media files
        const mediaInputs = modal.querySelectorAll('.media-input');
        const mediaFiles = Array.from(mediaInputs).map(input => input.files[0]).filter(file => file);
        
        // Create a new housing section
        const housingContainer = document.querySelector('.housing-section .row .col-md-8');
        const newSectionNumber = housingContainer.children.length + 1;
        
        const newHousingSection = document.createElement('div');
        newHousingSection.className = `housing-section-${newSectionNumber}`;
        
        // Create the HTML structure for the new housing
        newHousingSection.innerHTML = `
            <div class="unit-image mb-3">
                <div class="wrapper" role="group" aria-label="Housing image slider">
                    <input type="radio" name="housing-${newSectionNumber}-slide" id="housing-${newSectionNumber}-one" checked>
                    <input type="radio" name="housing-${newSectionNumber}-slide" id="housing-${newSectionNumber}-two">
                    <input type="radio" name="housing-${newSectionNumber}-slide" id="housing-${newSectionNumber}-three">
                    <!-- Slides will be added dynamically -->
                    <div class="navigation-arrows">
                        <button class="arrow prev" aria-label="Previous slide">❮</button>
                        <button class="arrow next" aria-label="Next slide">❯</button>
                    </div>
                    <div class="sliders">
                        <label for="housing-${newSectionNumber}-one" class="one"></label>
                        <label for="housing-${newSectionNumber}-two" class="two"></label>
                        <label for="housing-${newSectionNumber}-three" class="three"></label>
                    </div>
                </div>
            </div>
            <div class="unit-info">
                <h3>${housingName} - ${housingType} House</h3>
            </div>
            <div class="plan-item" data-booking-type="housing-${newSectionNumber}-monthly">
                <div class="plan-details">
                    <h4>Monthly Plan</h4>
                    <ul>
                        ${features.map(feature => `<li>${feature}</li>`).join('')}
                    </ul>
                </div>
                <div class="plan-price">IDR ${parseInt(housingPrice.replace(/,/g, '')).toLocaleString()}/month</div>
                <button class="book-btn">Book Now</button>
            </div>
            <div class="plan-item" data-booking-type="housing-${newSectionNumber}-quarterly">
                <div class="plan-details">
                    <h4>Quarterly Plan</h4>
                    <ul>
                        ${features.map(feature => `<li>${feature}</li>`).join('')}
                        <li>5% discount quarterly commitment</li>
                    </ul>
                </div>
                <div class="plan-price">IDR ${Math.round(parseInt(housingPrice.replace(/,/g, '')) * 3 * 0.95).toLocaleString()}/quarterly</div>
                <button class="book-btn">Book Now</button>
            </div>
            <div class="plan-item" data-booking-type="housing-${newSectionNumber}-yearly">
                <div class="plan-details">
                    <h4>Yearly Plan</h4>
                    <ul>
                        ${features.map(feature => `<li>${feature}</li>`).join('')}
                        <li>20% discount yearly commitment</li>
                    </ul>
                </div>
                <div class="plan-price">IDR ${Math.round(parseInt(housingPrice.replace(/,/g, '')) * 12 * 0.8).toLocaleString()}/yearly</div>
                <button class="book-btn">Book Now</button>
            </div>
        `;
        
        // Add the slides with media
        const wrapper = newHousingSection.querySelector('.wrapper');
        
        mediaFiles.forEach((file, index) => {
            const isVideo = file.type.includes('video');
            const slide = document.createElement('div');
            slide.className = `img img-${index + 1}`;
            
            if (isVideo) {
                slide.classList.add('video-container');
                slide.innerHTML = `
                    <video muted loop playsinline>
                        <source src="${URL.createObjectURL(file)}" type="${file.type}">
                    </video>
                `;
            } else {
                slide.innerHTML = `<img src="${URL.createObjectURL(file)}" alt="${housingName}">`;
            }
            
            wrapper.insertBefore(slide, wrapper.querySelector('.navigation-arrows'));
        });

        // Add the new section to the DOM
        housingContainer.appendChild(newHousingSection);
        
        // Initialize the slider for the new section
        if (typeof initSlider === 'function') {
            initSlider(wrapper);
        }
        
        // Initialize all booking buttons
        newHousingSection.querySelectorAll('.book-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                
                if (typeof handleBookingButtonClick === 'function') {
                    handleBookingButtonClick(e);
                } else {
                    console.warn('Booking system not fully initialized');
                    alert('Booking system is loading, please try again shortly');
                }
            });
        });
        
        // Scroll to the new section
        setTimeout(() => {
            newHousingSection.scrollIntoView({ behavior: 'smooth' });
        }, 100);
    }

    // VENUE PROPERTY FUNCTION
    function addVenue(modal) {
        const venueName = modal.querySelector('#venueName').value;
        const venueDescription = modal.querySelector('#venueDescription').value;
        const venuePrice = modal.querySelector('#venuePrice').value;
        
        // Get features without the "close" text
        const features = Array.from(modal.querySelectorAll('.features-list li')).map(li => {
            const temp = document.createElement('span');
            temp.innerHTML = li.innerHTML;
            const button = temp.querySelector('button');
            if (button) button.remove();
            return temp.textContent.trim();
        }).filter(feature => feature);
        
        // Get media files
        const mediaInputs = modal.querySelectorAll('.media-input');
        const mediaFiles = Array.from(mediaInputs).map(input => input.files[0]).filter(file => file);
        
        // Create a new venue section
        const venueContainer = document.querySelector('.venue-options');
        const venueItems = venueContainer.querySelectorAll('.venue-option');
        
        // Check if we need to create a new row (after every 3 items)
        if (venueItems.length % 3 === 0) {
            const newRow = document.createElement('div');
            newRow.className = 'venue-row';
            venueContainer.appendChild(newRow);
        }
        
        const newVenueSection = document.createElement('div');
        newVenueSection.className = 'venue-option';
        newVenueSection.setAttribute('data-booking-type', 'venue');
        
        // Create the HTML structure for the new venue
        newVenueSection.innerHTML = `
            <div class="unit-image mb-3">
                <div class="wrapper" role="group" aria-label="Venue image slider">
                    <input type="radio" name="venue-${venueItems.length + 1}-slide" id="venue-${venueItems.length + 1}-one" checked>
                    <input type="radio" name="venue-${venueItems.length + 1}-slide" id="venue-${venueItems.length + 1}-two">
                    <input type="radio" name="venue-${venueItems.length + 1}-slide" id="venue-${venueItems.length + 1}-three">
                    <!-- Slides will be added dynamically -->
                    <div class="sliders">
                        <label for="venue-${venueItems.length + 1}-one" class="one"></label>
                        <label for="venue-${venueItems.length + 1}-two" class="two"></label>
                        <label for="venue-${venueItems.length + 1}-three" class="three"></label>
                    </div>
                </div>
            </div>
            <div class="venue-content">
                <h3>${venueName}</h3>
                <p>${venueDescription}</p>
                <ul class="venue-features">
                    ${features.map(feature => `<li>${feature}</li>`).join('')}
                    <li>50% Down-payment available</li>
                </ul>
                <div class="venue-price">IDR ${parseInt(venuePrice.replace(/,/g, '')).toLocaleString()}</div>
                <button class="book-btn">Book Now</button>
            </div>
        `;
        
        // Add the slides with media
        const wrapper = newVenueSection.querySelector('.wrapper');
        
        mediaFiles.forEach((file, index) => {
            const isVideo = file.type.includes('video');
            const slide = document.createElement('div');
            slide.className = `img img-${index + 1}`;
            
            if (isVideo) {
                slide.classList.add('video-container');
                slide.innerHTML = `
                    <video muted loop playsinline>
                        <source src="${URL.createObjectURL(file)}" type="${file.type}">
                    </video>
                `;
            } else {
                slide.innerHTML = `<img src="${URL.createObjectURL(file)}" alt="${venueName}">`;
            }
            
            wrapper.insertBefore(slide, wrapper.querySelector('.sliders'));
        });

        // Add the new section to the DOM
        venueContainer.appendChild(newVenueSection);
        
        // Initialize the slider for the new section
        if (typeof initSlider === 'function') {
            initSlider(wrapper);
        }
        
        // Initialize the booking button with proper event handling
        const bookBtn = newVenueSection.querySelector('.book-btn');
        if (bookBtn) {
            bookBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                if (typeof handleBookingButtonClick === 'function') {
                    handleBookingButtonClick(e);
                } else {
                    console.warn('Booking system not fully initialized');
                    alert('Booking system is loading, please try again shortly');
                }
            });
        }
        
        // Scroll to the new section
        setTimeout(() => {
            newVenueSection.scrollIntoView({ behavior: 'smooth' });
        }, 100);
    }

    function updatePropertyDetails(propertyType, modal) {
        const propertyDetails = modal.querySelector('#propertyDetails');
        
        if (!propertyType) {
            propertyDetails.style.display = 'none';
            return;
        }
        
        switch(propertyType) {
            case 'hotel':
                propertyDetails.innerHTML = `
                    <div class="form-group">
                        <label for="roomName">Room Name:</label>
                        <input type="text" id="roomName" required placeholder="e.g., Hotel Room D">
                    </div>
                    
                    <div class="form-group">
                        <label for="bedType">Bed Type:</label>
                        <select id="bedType" required class="styled-select">
                            <option value="">Select Bed Type</option>
                            <option value="single">Single Bed</option>
                            <option value="double">Double Bed</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="guestQuantity">Guest Quantity:</label>
                        <select id="guestQuantity" required class="styled-select">
                            <option value="1">1 Guest</option>
                            <option value="2">2 Guests</option>
                            <option value="3">3 Guests</option>
                            <option value="4">4 Guests</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="roomPrice">Room Price (IDR/day):</label>
                        <input type="text" id="roomPrice" required placeholder="300,000">
                    </div>
                    
                    <div class="form-group">
                        <label>Room Features:</label>
                        <div class="features-container">
                            <div class="feature-input-container">
                                <input type="text" class="feature-input" placeholder="Add a feature (e.g., Air conditioning)">
                                <button type="button" class="add-feature-btn">+</button>
                            </div>
                            <ul class="features-list"></ul>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Media Uploads:</label>
                        <div class="media-uploads">
                            <div class="media-upload">
                                <div class="upload-area" data-index="1">
                                    <input type="file" class="media-input" accept="image/*,video/*" style="display: none;">
                                    <div class="upload-prompt">
                                        <span class="material-symbols-rounded">upload</span>
                                        <p>Click or drag file here</p>
                                    </div>
                                    <div class="media-preview"></div>
                                </div>
                                <button type="button" class="remove-media">Remove</button>
                            </div>
                            <div class="media-upload">
                                <div class="upload-area" data-index="2">
                                    <input type="file" class="media-input" accept="image/*,video/*" style="display: none;">
                                    <div class="upload-prompt">
                                        <span class="material-symbols-rounded">upload</span>
                                        <p>Click or drag file here</p>
                                    </div>
                                    <div class="media-preview"></div>
                                </div>
                                <button type="button" class="remove-media">Remove</button>
                            </div>
                            <div class="media-upload">
                                <div class="upload-area" data-index="3">
                                    <input type="file" class="media-input" accept="image/*,video/*" style="display: none;">
                                    <div class="upload-prompt">
                                        <span class="material-symbols-rounded">upload</span>
                                        <p>Click or drag file here</p>
                                    </div>
                                    <div class="media-preview"></div>
                                </div>
                                <button type="button" class="remove-media">Remove</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
						<button type="submit" class="submit-btn">Add Property</button>
					</div>
                `;
                
                // Initialize features functionality
                initializeFeatures(modal, 'feature-input', 'add-feature-btn', 'features-list');
                // Initialize media uploads
                initializeMediaUploads(modal, 'media-input');
                break;
                
            case 'housing':
                propertyDetails.innerHTML = `
                    <div class="form-group">
                        <label for="housingName">Housing Name:</label>
                        <input type="text" id="housingName" required placeholder="e.g., House C, House D">
                    </div>
                    
                    <div class="form-group">
                        <label for="housingType">Housing Type:</label>
                        <select id="housingType" required class="styled-select">
                            <option value="">Select Type</option>
                            <option value="Single">Single</option>
                            <option value="Married">Married (2 persons)</option>
                            <option value="Family">Family (4+ persons)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="housingPlan">Base Plan:</label>
                        <select id="housingPlan" required class="styled-select">
                            <option value="">Select Plan</option>
                            <option value="Monthly">Monthly</option>
                            <option value="Quarterly">Quarterly</option>
                            <option value="Yearly">Yearly</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="housingPriceContainer" style="display: none;">
                        <label for="housingPrice">Base Price (IDR):</label>
                        <input type="text" id="housingPrice" required placeholder="e.g., 1,000,000">
                        <span id="pricePeriod"></span>
                    </div>
                    
                    <div class="form-group">
                        <label>Housing Features:</label>
                        <div class="features-container">
                            <div class="feature-input-container">
                                <input type="text" class="feature-input" placeholder="Add a feature (e.g., Kitchen, Garden)">
                                <button type="button" class="add-feature-btn">+</button>
                            </div>
                            <ul class="features-list"></ul>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Media Uploads:</label>
                        <div class="media-uploads">
                            <div class="media-upload">
                                <div class="upload-area" data-index="1">
                                    <input type="file" class="media-input" accept="image/*,video/*" style="display: none;">
                                    <div class="upload-prompt">
                                        <span class="material-symbols-rounded">upload</span>
                                        <p>Click or drag file here</p>
                                    </div>
                                    <div class="media-preview"></div>
                                </div>
                                <button type="button" class="remove-media">Remove</button>
                            </div>
                            <div class="media-upload">
                                <div class="upload-area" data-index="2">
                                    <input type="file" class="media-input" accept="image/*,video/*" style="display: none;">
                                    <div class="upload-prompt">
                                        <span class="material-symbols-rounded">upload</span>
                                        <p>Click or drag file here</p>
                                    </div>
                                    <div class="media-preview"></div>
                                </div>
                                <button type="button" class="remove-media">Remove</button>
                            </div>
                            <div class="media-upload">
                                <div class="upload-area" data-index="3">
                                    <input type="file" class="media-input" accept="image/*,video/*" style="display: none;">
                                    <div class="upload-prompt">
                                        <span class="material-symbols-rounded">upload</span>
                                        <p>Click or drag file here</p>
                                    </div>
                                    <div class="media-preview"></div>
                                </div>
                                <button type="button" class="remove-media">Remove</button>
                            </div>
                        </div>
                    </div>
                    
					<div class="form-actions">
						<button type="submit" class="submit-btn">Add Property</button>
					</div>
                `;
                
                // Initialize housing plan change handler
                const housingPlanSelect = modal.querySelector('#housingPlan');
                const housingPriceContainer = modal.querySelector('#housingPriceContainer');
                const pricePeriod = modal.querySelector('#pricePeriod');
                
                housingPlanSelect.addEventListener('change', function() {
                    if (this.value) {
                        housingPriceContainer.style.display = 'block';
                        pricePeriod.textContent = this.value === 'Monthly' ? '/month' : 
                                             this.value === 'Quarterly' ? '/quarter' : '/year';
                    } else {
                        housingPriceContainer.style.display = 'none';
                    }
                });
                
                // Initialize features and media uploads
                initializeFeatures(modal, 'feature-input', 'add-feature-btn', 'features-list');
                initializeMediaUploads(modal, 'media-input');
                break;
                
            case 'venue':
                propertyDetails.innerHTML = `
                    <div class="form-group">
                        <label for="venueName">Venue Name:</label>
                        <input type="text" id="venueName" required placeholder="e.g., Grand Venue">
                    </div>
                    
                    <div class="form-group">
                        <label for="venueDescription">Venue Description:</label>
                        <textarea id="venueDescription" required placeholder="Elegant space, perfect for weddings and galas"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="venuePrice">Venue Price (IDR):</label>
                        <input type="text" id="venuePrice" required placeholder="e.g., 7,500,000">
                    </div>
                    
                    <div class="form-group">
                        <label>Venue Features:</label>
                        <div class="features-container">
                            <div class="feature-input-container">
                                <input type="text" class="feature-input" placeholder="Add a feature (e.g., Capacity: 500 guests)">
                                <button type="button" class="add-feature-btn">+</button>
                            </div>
                            <ul class="features-list"></ul>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Media Uploads:</label>
                        <div class="media-uploads">
                            <div class="media-upload">
                                <div class="upload-area" data-index="1">
                                    <input type="file" class="media-input" accept="image/*,video/*" style="display: none;">
                                    <div class="upload-prompt">
                                        <span class="material-symbols-rounded">upload</span>
                                        <p>Click or drag file here</p>
                                    </div>
                                    <div class="media-preview"></div>
                                </div>
                                <button type="button" class="remove-media">Remove</button>
                            </div>
                            <div class="media-upload">
                                <div class="upload-area" data-index="2">
                                    <input type="file" class="media-input" accept="image/*,video/*" style="display: none;">
                                    <div class="upload-prompt">
                                        <span class="material-symbols-rounded">upload</span>
                                        <p>Click or drag file here</p>
                                    </div>
                                    <div class="media-preview"></div>
                                </div>
                                <button type="button" class="remove-media">Remove</button>
                            </div>
                            <div class="media-upload">
                                <div class="upload-area" data-index="3">
                                    <input type="file" class="media-input" accept="image/*,video/*" style="display: none;">
                                    <div class="upload-prompt">
                                        <span class="material-symbols-rounded">upload</span>
                                        <p>Click or drag file here</p>
                                    </div>
                                    <div class="media-preview"></div>
                                </div>
                                <button type="button" class="remove-media">Remove</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
						<button type="submit" class="submit-btn">Add Property</button>
					</div>
                `;
                
                // Initialize features and media uploads
                initializeFeatures(modal, 'feature-input', 'add-feature-btn', 'features-list');
                initializeMediaUploads(modal, 'media-input');
                break;
                
            default:
                propertyDetails.innerHTML = '';
        }
        
        propertyDetails.style.display = 'block';
    }
    
    function initializeFeatures(modal, inputClass, buttonClass, listClass) {
        const featureInput = modal.querySelector(`.${inputClass}`);
        const addFeatureBtn = modal.querySelector(`.${buttonClass}`);
        const featuresList = modal.querySelector(`.${listClass}`);
        
        function addFeature() {
            const featureText = featureInput.value.trim();
            if (featureText) {
                const li = document.createElement('li');
                li.innerHTML = `
                    <span class="feature-text">${featureText}</span>
                    <button type="button" class="remove-feature">
                        <span class="material-symbols-rounded">close</span>
                    </button>
                `;
                featuresList.appendChild(li);
                featureInput.value = '';
                
                li.querySelector('.remove-feature').addEventListener('click', function() {
                    li.remove();
                });
            }
        }
        
        addFeatureBtn.addEventListener('click', addFeature);
        
        featureInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                addFeature();
            }
        });
    }
    
    function initializeMediaUploads(modal, inputClass) {
        modal.querySelectorAll('.upload-area').forEach(area => {
            const input = area.querySelector(`.${inputClass}`);
            const preview = area.querySelector('.media-preview');
            const prompt = area.querySelector('.upload-prompt');
            const removeBtn = area.closest('.media-upload').querySelector('.remove-media');
            
            // Hide the default file input
            input.style.display = 'none';
            
            // Click handler
            area.addEventListener('click', function(e) {
                if (e.target.tagName !== 'INPUT') {
                    input.click();
                }
            });
            
            // Drag and drop handlers
            area.addEventListener('dragover', function(e) {
                e.preventDefault();
                area.classList.add('dragover');
            });
            
            area.addEventListener('dragleave', function() {
                area.classList.remove('dragover');
            });
            
            area.addEventListener('drop', function(e) {
                e.preventDefault();
                area.classList.remove('dragover');
                
                if (e.dataTransfer.files.length) {
                    input.files = e.dataTransfer.files;
                    handleFileUpload(input.files[0], preview, prompt);
                }
            });
            
            // File input change handler
            input.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    handleFileUpload(this.files[0], preview, prompt);
                }
            });
            
            // Remove button handler
            removeBtn.addEventListener('click', function() {
                input.value = '';
                preview.innerHTML = '';
                prompt.style.display = 'flex';
            });
        });
    }
    
    function handleFileUpload(file, preview, prompt) {
        preview.innerHTML = '';
        prompt.style.display = 'none';
        
        const isVideo = file.type.includes('video');
        
        if (isVideo) {
            const video = document.createElement('video');
            video.src = URL.createObjectURL(file);
            video.controls = true;
            video.muted = true;
            video.style.maxWidth = '100%';
            video.style.maxHeight = '150px';
            preview.appendChild(video);
        } else {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.style.maxWidth = '100%';
            img.style.maxHeight = '150px';
            preview.appendChild(img);
        }
    }
});