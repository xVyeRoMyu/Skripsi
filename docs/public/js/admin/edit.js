document.addEventListener('DOMContentLoaded', function() {
    const isAdmin = document.body.classList.contains('admin-logged-in');
    if (!isAdmin) return;

    addEditButtons();

    function addEditButtons() {
        // Hotel sections
        document.querySelectorAll('.hotel-section > .container > .row > .col-md-8 > [class^="hotel-section-"]').forEach(section => {
            addEditButtonToSection(section, '.room-info h3', 'hotel');
        });

        // Housing sections
        document.querySelectorAll('.housing-section > .container > .row > .col-md-8 > [class^="housing-section-"]').forEach(section => {
            addEditButtonToSection(section, '.unit-info h3', 'housing');
        });

        // Venue sections
        document.querySelectorAll('.venue-option').forEach(section => {
            addEditButtonToSection(section, '.venue-content h3', 'venue');
        });
    }

    function addEditButtonToSection(section, headerSelector, type) {
        const editBtn = createEditButton();
        const header = section.querySelector(headerSelector);
        if (header) {
            const container = document.createElement('div');
            container.className = 'edit-btn-container';
            container.appendChild(editBtn);
            header.insertAdjacentElement('afterend', container);
        }
        
        editBtn.addEventListener('click', () => {
            openEditModal(type, section);
        });
    }

    function createEditButton() {
        const btn = document.createElement('button');
        btn.className = 'edit-btn';
        btn.innerHTML = '<span class="material-symbols-rounded">edit</span> Edit';
        return btn;
    }

    function openEditModal(type, section) {
        const modal = document.createElement('div');
        modal.className = 'admin-modal';
        modal.style.opacity = '0';
        modal.style.transition = 'opacity 0.3s ease';
        
        modal.innerHTML = `
            <div class="admin-modal-content">
                <span class="close-admin-modal">&times;</span>
                <h3>Edit ${type.charAt(0).toUpperCase() + type.slice(1)} Property</h3>
                <form id="editPropertyForm">
                    <div id="editPropertyDetails"></div>
                    <div class="form-actions">
                        <button type="submit" class="submit-btn">Save Changes</button>
                        <button type="button" class="delete-btn">Delete Property</button>
                    </div>
                </form>
            </div>
        `;

        const formDetails = modal.querySelector('#editPropertyDetails');
        formDetails.innerHTML = getEditFormHTML(type, section);
        
        document.body.appendChild(modal);
        setTimeout(() => { modal.style.opacity = '1'; }, 10);
        
        // Initialize features and media uploads
        initializeEditForm(type, modal, section);
        
        // Close handlers
        modal.querySelector('.close-admin-modal').addEventListener('click', closeModal);
        
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });
        
        function closeModal() {
            modal.style.opacity = '0';
            setTimeout(() => {
                document.body.removeChild(modal);
            }, 300);
        }
        
        modal.querySelector('#editPropertyForm').addEventListener('submit', (e) => {
            e.preventDefault();
            saveChanges(type, section, modal);
            closeModal();
        });
        
        modal.querySelector('.delete-btn').addEventListener('click', () => {
            if (confirm('Are you sure you want to delete this property?')) {
                section.remove();
                closeModal();
            }
        });
    }

    function getEditFormHTML(type, section) {
        switch(type) {
            case 'hotel':
                return getHotelEditForm(section);
            case 'housing':
                return getHousingEditForm(section);
            case 'venue':
                return getVenueEditForm(section);
            default:
                return '';
        }
    }

    function getHotelEditForm(section) {
        const roomName = section.querySelector('.rate-details h4').textContent;
        const roomType = section.querySelector('.room-info h3').textContent;
        const price = section.querySelector('.rate-price').textContent.match(/\d+/g).join('');
        const features = Array.from(section.querySelectorAll('.rate-details ul li')).map(li => li.textContent);
        const mediaItems = Array.from(section.querySelectorAll('.room-media img, .room-media video'));
        
        return `
            <div class="form-group">
                <label for="editRoomName">Room Name:</label>
                <input type="text" id="editRoomName" value="${roomName}" required>
            </div>
            <div class="form-group">
                <label for="editBedType">Bed Type:</label>
                <select id="editBedType" class="styled-select">
                    <option value="single" ${roomType.includes('Single') ? 'selected' : ''}>Single Bed</option>
                    <option value="double" ${roomType.includes('Double') ? 'selected' : ''}>Double Bed</option>
                </select>
            </div>
            <div class="form-group">
                <label for="editGuestQuantity">Guest Quantity:</label>
                <select id="editGuestQuantity" class="styled-select">
                    ${[1,2,3,4].map(num => 
                        `<option value="${num}" ${roomType.includes(num + ' person') ? 'selected' : ''}>${num} Guest${num > 1 ? 's' : ''}</option>`
                    ).join('')}
                </select>
            </div>
            <div class="form-group">
                <label for="editRoomPrice">Price (IDR/day):</label>
                <input type="text" id="editRoomPrice" value="${price}" required>
            </div>
            <div class="form-group">
                <label>Features:</label>
                <div class="features-container">
                    <div class="feature-input-container">
                        <input type="text" class="feature-input" placeholder="Add feature">
                        <button type="button" class="add-feature-btn">+</button>
                    </div>
                    <ul class="features-list">
                        ${features.map(feature => `
                            <li>
                                <span class="feature-text">${feature}</span>
                                <button type="button" class="remove-feature">
                                    <span class="material-symbols-rounded">close</span>
                                </button>
                            </li>
                        `).join('')}
                    </ul>
                </div>
            </div>
            <div class="form-group">
                <label>Media:</label>
                <div class="media-uploads">
                    ${[1,2,3].map(i => {
                        const mediaItem = mediaItems[i-1];
                        if (mediaItem) {
                            const isVideo = mediaItem.tagName === 'VIDEO';
                            return `
                                <div class="media-upload">
                                    <div class="upload-area" data-index="${i}">
                                        <input type="file" class="media-input" accept="image/*,video/*" style="display: none;">
                                        <div class="upload-prompt" style="display: none;">
                                            <span class="material-symbols-rounded">upload</span>
                                            <p>Click or drag file here</p>
                                        </div>
                                        <div class="media-preview">
                                            ${isVideo ? 
                                                `<video src="${mediaItem.src}" controls muted style="max-width: 100%; max-height: 150px;"></video>` :
                                                `<img src="${mediaItem.src}" style="max-width: 100%; max-height: 150px;">`}
                                        </div>
                                    </div>
                                    <button type="button" class="remove-media">Remove</button>
                                </div>
                            `;
                        } else {
                            return `
                                <div class="media-upload">
                                    <div class="upload-area" data-index="${i}">
                                        <input type="file" class="media-input" accept="image/*,video/*" style="display: none;">
                                        <div class="upload-prompt">
                                            <span class="material-symbols-rounded">upload</span>
                                            <p>Click or drag file here</p>
                                        </div>
                                        <div class="media-preview"></div>
                                    </div>
                                    <button type="button" class="remove-media">Remove</button>
                                </div>
                            `;
                        }
                    }).join('')}
                </div>
            </div>
        `;
    }

    function getHousingEditForm(section) {
        const housingName = section.querySelector('.unit-info h3').textContent.split(' - ')[0];
        const housingType = section.querySelector('.unit-info h3').textContent.split(' - ')[1].replace(' House', '');
        const monthlyPrice = section.querySelector('[data-booking-type$="monthly"] .plan-price').textContent.match(/\d+/g).join('');
        const features = Array.from(section.querySelector('.plan-item ul').querySelectorAll('li')).map(li => li.textContent);
        const mediaItems = Array.from(section.querySelectorAll('.unit-media img, .unit-media video'));
        
        return `
            <div class="form-group">
                <label for="editHousingName">Housing Name:</label>
                <input type="text" id="editHousingName" value="${housingName}" required>
            </div>
            <div class="form-group">
                <label for="editHousingType">Housing Type:</label>
                <select id="editHousingType" class="styled-select">
                    <option value="Single" ${housingType === 'Single' ? 'selected' : ''}>Single</option>
                    <option value="Married" ${housingType === 'Married' ? 'selected' : ''}>Married (2 persons)</option>
                    <option value="Family" ${housingType === 'Family' ? 'selected' : ''}>Family (4+ persons)</option>
                </select>
            </div>
            <div class="form-group">
                <label for="editHousingPrice">Base Price (IDR/month):</label>
                <input type="text" id="editHousingPrice" value="${monthlyPrice}" required>
            </div>
            <div class="form-group">
                <label>Features:</label>
                <div class="features-container">
                    <div class="feature-input-container">
                        <input type="text" class="feature-input" placeholder="Add feature">
                        <button type="button" class="add-feature-btn">+</button>
                    </div>
                    <ul class="features-list">
                        ${features.map(feature => `
                            <li>
                                <span class="feature-text">${feature}</span>
                                <button type="button" class="remove-feature">
                                    <span class="material-symbols-rounded">close</span>
                                </button>
                            </li>
                        `).join('')}
                    </ul>
                </div>
            </div>
            <div class="form-group">
                <label>Media:</label>
                <div class="media-uploads">
                    ${[1,2,3].map(i => {
                        const mediaItem = mediaItems[i-1];
                        if (mediaItem) {
                            const isVideo = mediaItem.tagName === 'VIDEO';
                            return `
                                <div class="media-upload">
                                    <div class="upload-area" data-index="${i}">
                                        <input type="file" class="media-input" accept="image/*,video/*" style="display: none;">
                                        <div class="upload-prompt" style="display: none;">
                                            <span class="material-symbols-rounded">upload</span>
                                            <p>Click or drag file here</p>
                                        </div>
                                        <div class="media-preview">
                                            ${isVideo ? 
                                                `<video src="${mediaItem.src}" controls muted style="max-width: 100%; max-height: 150px;"></video>` :
                                                `<img src="${mediaItem.src}" style="max-width: 100%; max-height: 150px;">`}
                                        </div>
                                    </div>
                                    <button type="button" class="remove-media">Remove</button>
                                </div>
                            `;
                        } else {
                            return `
                                <div class="media-upload">
                                    <div class="upload-area" data-index="${i}">
                                        <input type="file" class="media-input" accept="image/*,video/*" style="display: none;">
                                        <div class="upload-prompt">
                                            <span class="material-symbols-rounded">upload</span>
                                            <p>Click or drag file here</p>
                                        </div>
                                        <div class="media-preview"></div>
                                    </div>
                                    <button type="button" class="remove-media">Remove</button>
                                </div>
                            `;
                        }
                    }).join('')}
                </div>
            </div>
        `;
    }

    function getVenueEditForm(section) {
        const venueName = section.querySelector('.venue-content h3').textContent;
        const venueDescription = section.querySelector('.venue-content p').textContent;
        const venuePrice = section.querySelector('.venue-price').textContent.match(/\d+/g).join('');
        const features = Array.from(section.querySelectorAll('.venue-features li')).map(li => li.textContent);
        const mediaItems = Array.from(section.querySelectorAll('.venue-media img, .venue-media video'));
        
        return `
            <div class="form-group">
                <label for="editVenueName">Venue Name:</label>
                <input type="text" id="editVenueName" value="${venueName}" required>
            </div>
            <div class="form-group">
                <label for="editVenueDescription">Description:</label>
                <textarea id="editVenueDescription" required>${venueDescription}</textarea>
            </div>
            <div class="form-group">
                <label for="editVenuePrice">Price (IDR):</label>
                <input type="text" id="editVenuePrice" value="${venuePrice}" required>
            </div>
            <div class="form-group">
                <label>Features:</label>
                <div class="features-container">
                    <div class="feature-input-container">
                        <input type="text" class="feature-input" placeholder="Add feature">
                        <button type="button" class="add-feature-btn">+</button>
                    </div>
                    <ul class="features-list">
                        ${features.map(feature => `
                            <li>
                                <span class="feature-text">${feature}</span>
                                <button type="button" class="remove-feature">
                                    <span class="material-symbols-rounded">close</span>
                                </button>
                            </li>
                        `).join('')}
                    </ul>
                </div>
            </div>
            <div class="form-group">
                <label>Media:</label>
                <div class="media-uploads">
                    ${[1,2,3].map(i => {
                        const mediaItem = mediaItems[i-1];
                        if (mediaItem) {
                            const isVideo = mediaItem.tagName === 'VIDEO';
                            return `
                                <div class="media-upload">
                                    <div class="upload-area" data-index="${i}">
                                        <input type="file" class="media-input" accept="image/*,video/*" style="display: none;">
                                        <div class="upload-prompt" style="display: none;">
                                            <span class="material-symbols-rounded">upload</span>
                                            <p>Click or drag file here</p>
                                        </div>
                                        <div class="media-preview">
                                            ${isVideo ? 
                                                `<video src="${mediaItem.src}" controls muted style="max-width: 100%; max-height: 150px;"></video>` :
                                                `<img src="${mediaItem.src}" style="max-width: 100%; max-height: 150px;">`}
                                        </div>
                                    </div>
                                    <button type="button" class="remove-media">Remove</button>
                                </div>
                            `;
                        } else {
                            return `
                                <div class="media-upload">
                                    <div class="upload-area" data-index="${i}">
                                        <input type="file" class="media-input" accept="image/*,video/*" style="display: none;">
                                        <div class="upload-prompt">
                                            <span class="material-symbols-rounded">upload</span>
                                            <p>Click or drag file here</p>
                                        </div>
                                        <div class="media-preview"></div>
                                    </div>
                                    <button type="button" class="remove-media">Remove</button>
                                </div>
                            `;
                        }
                    }).join('')}
                </div>
            </div>
        `;
    }

    function initializeEditForm(type, modal, section) {
        // Initialize features
        const featureInput = modal.querySelector('.feature-input');
        const addFeatureBtn = modal.querySelector('.add-feature-btn');
        const featuresList = modal.querySelector('.features-list');
        
        if (featureInput && addFeatureBtn && featuresList) {
            addFeatureBtn.addEventListener('click', addFeature);
            featureInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') addFeature();
            });
            
            modal.querySelectorAll('.remove-feature').forEach(btn => {
                btn.addEventListener('click', function() {
                    this.closest('li').remove();
                });
            });
        }
        
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
        
        // Initialize media uploads
        initializeMediaUploads(modal, section);
    }

    function initializeMediaUploads(modal, section) {
        modal.querySelectorAll('.upload-area').forEach(area => {
            const input = area.querySelector('.media-input');
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

    function saveChanges(type, section, modal) {
        switch(type) {
            case 'hotel':
                const roomName = modal.querySelector('#editRoomName').value;
                const bedType = modal.querySelector('#editBedType').value;
                const guestQuantity = modal.querySelector('#editGuestQuantity').value;
                const roomPrice = modal.querySelector('#editRoomPrice').value;
                const features = Array.from(modal.querySelectorAll('.features-list .feature-text')).map(el => el.textContent);
                
                // Update the DOM
                section.querySelector('.rate-details h4').textContent = roomName;
                section.querySelector('.room-info h3').textContent = `${bedType === 'single' ? 'Single' : 'Double'} bed — ${guestQuantity} person room`;
                section.querySelector('.rate-price').textContent = `IDR ${parseInt(roomPrice).toLocaleString()}/day`;
                
                const featuresList = section.querySelector('.rate-details ul');
                featuresList.innerHTML = features.map(feature => `<li>${feature}</li>`).join('');
                break;
                
            case 'housing':
                const housingName = modal.querySelector('#editHousingName').value;
                const housingType = modal.querySelector('#editHousingType').value;
                const housingPrice = modal.querySelector('#editHousingPrice').value;
                const housingFeatures = Array.from(modal.querySelectorAll('.features-list .feature-text')).map(el => el.textContent);
                
                // Update the DOM
                section.querySelector('.unit-info h3').textContent = `${housingName} - ${housingType} House`;
                
                // Update all plan prices
                section.querySelector('[data-booking-type$="monthly"] .plan-price').textContent = `IDR ${parseInt(housingPrice).toLocaleString()}/month`;
                section.querySelector('[data-booking-type$="quarterly"] .plan-price').textContent = 
                    `IDR ${Math.round(parseInt(housingPrice) * 3 * 0.95).toLocaleString()}/quarterly`;
                section.querySelector('[data-booking-type$="yearly"] .plan-price').textContent = 
                    `IDR ${Math.round(parseInt(housingPrice) * 12 * 0.8).toLocaleString()}/yearly`;
                
                // Update features in all plan items
                section.querySelectorAll('.plan-item ul').forEach(ul => {
                    ul.innerHTML = housingFeatures.map(feature => `<li>${feature}</li>`).join('');
                });
                break;
                
            case 'venue':
                const venueName = modal.querySelector('#editVenueName').value;
                const venueDescription = modal.querySelector('#editVenueDescription').value;
                const venuePrice = modal.querySelector('#editVenuePrice').value;
                const venueFeatures = Array.from(modal.querySelectorAll('.features-list .feature-text')).map(el => el.textContent);
                
                // Update the DOM
                section.querySelector('.venue-content h3').textContent = venueName;
                section.querySelector('.venue-content p').textContent = venueDescription;
                section.querySelector('.venue-price').textContent = `IDR ${parseInt(venuePrice).toLocaleString()}`;
                section.querySelector('.venue-features').innerHTML = venueFeatures.map(feature => `<li>${feature}</li>`).join('');
                break;
        }
    }
});