document.addEventListener('DOMContentLoaded', () => {
    // Updated dropdown functionality for Bootstrap dropdowns
    const handleDropdowns = () => {
        const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
        
        dropdownToggles.forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                if (this.hasAttribute('data-bs-toggle')) {
                    // Let Bootstrap handle its own dropdowns
                    return;
                }
                
                e.preventDefault();
                const dropdown = this.closest('.dropdown');
                const menu = dropdown.querySelector('.dropdown-menu');
                
                // Close all other dropdowns first
                document.querySelectorAll('.dropdown').forEach(d => {
                    if (d !== dropdown) {
                        d.classList.remove('show');
                        d.querySelector('.dropdown-menu').classList.remove('show');
                    }
                });
                
                // Toggle current dropdown
                dropdown.classList.toggle('show');
                menu.classList.toggle('show');
            });
        });
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown').forEach(dropdown => {
                    dropdown.classList.remove('show');
                    dropdown.querySelector('.dropdown-menu').classList.remove('show');
                });
            }
        });
    };

    // Sidebar toggle functionality
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        sidebar.classList.add('collapsed');
        document.body.classList.remove('sidebar-expanded');
        sidebar.style.overflowX = 'visible';

        document.querySelectorAll('.sidebar-toggler, .sidebar-menu-button').forEach((button) => {
            button.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');

                if (!sidebar.classList.contains('collapsed')) {
                    sidebar.style.overflowX = 'hidden';
                    document.body.classList.add('sidebar-expanded');
                } else {
                    sidebar.style.overflowX = 'visible';
                    document.body.classList.remove('sidebar-expanded');
                }
            });
        });

        if (window.innerWidth <= 1024) {
            sidebar.classList.add('collapsed');
            document.body.classList.remove('sidebar-expanded');
        }
    }

    // Filter functionality
    const filterRadios = document.querySelectorAll('input[name="filter-type"]');
    const priceRangeContainer = document.querySelector('.price-range-container');
    const priceRangeSlider = document.querySelector('.price-range-slider');
    const minPriceDisplay = document.querySelector('.min-price');
    const maxPriceDisplay = document.querySelector('.max-price');
    const housingPlanOptions = document.querySelector('.price-range-container .housing-plan-options');
    const housingPlanRadios = document.querySelectorAll('input[name="housing-plan"]');

    if (filterRadios.length && priceRangeContainer && priceRangeSlider && minPriceDisplay && maxPriceDisplay) {
        const priceRanges = {
			hotel: { min: 200000, max: 300000 },
			housing: {
				all: { min: 800000, max: 9600000 }, // Covers all plan types
				monthly: { min: 800000, max: 1000000 },
				quarterly: { min: 2280000, max: 2850000 },
				yearly: { min: 7680000, max: 9600000 }
			},
			venue: { min: 7500000, max: 15000000 }
		};

        let noUiSliderInstance = null;

        // Filter radio button events
        filterRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                const filterType = this.value;
                if (filterType === 'all') {
                    showAllSections();
                    housingPlanOptions.style.display = 'none';
                } else {
                    filterSections(filterType);
                    updatePriceRangeSlider(filterType);
                    
                    // Show/hide housing plan options
                    if (filterType === 'housing') {
                        housingPlanOptions.style.display = 'block';
                    } else {
                        housingPlanOptions.style.display = 'none';
                    }
                }
                highlightSelectedFilter(this);
            });
        });

        // Housing plan radio button events
        if (housingPlanRadios.length) {
            housingPlanRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    const filterType = document.querySelector('input[name="filter-type"]:checked').value;
                    if (filterType === 'housing') {
                        updatePriceRangeSlider(filterType);
                    }
                });
            });
        }

        function showAllSections() {
            priceRangeContainer.style.display = 'none';
            if (noUiSliderInstance) noUiSliderInstance.destroy();
            
            // Show all main sections
            document.querySelectorAll('.reservation-content, .contracted-housing, .venue-section')
                .forEach(section => section.style.display = 'block');
            
            // Show all sub-sections and items
            document.querySelectorAll('.hotel-section-1, .hotel-section-2, .housing-section-1, .housing-section-2, .venue-option')
                .forEach(item => item.style.display = 'block');
        }

        function filterSections(filterType) {
            // Get all main sections
            const hotelSection = document.querySelector('.reservation-content');
            const housingSection = document.querySelector('.contracted-housing');
            const venueSection = document.querySelector('.venue-section');

            // Hide all sections first
            if (hotelSection) hotelSection.style.display = 'none';
            if (housingSection) housingSection.style.display = 'none';
            if (venueSection) venueSection.style.display = 'none';

            // Show relevant sections
            switch(filterType) {
                case 'hotel':
                    if (hotelSection) hotelSection.style.display = 'block';
                    break;
                case 'housing':
                    if (housingSection) housingSection.style.display = 'block';
                    break;
                case 'venue':
                    if (venueSection) venueSection.style.display = 'block';
                    break;
            }
        }

        function updatePriceRangeSlider(filterType) {
            if (noUiSliderInstance) noUiSliderInstance.destroy();
            
            priceRangeContainer.style.display = 'block';
            let range;
            
            if (filterType === 'housing') {
                const housingPlan = document.querySelector('input[name="housing-plan"]:checked').value;
                range = priceRanges.housing[housingPlan];
            } else {
                range = priceRanges[filterType];
            }
            
            noUiSlider.create(priceRangeSlider, {
                start: [range.min, range.max],
                connect: true,
                range: { min: range.min, max: range.max },
                step: filterType === 'hotel' ? 50000 : 100000,
                format: {
                    to: value => 'IDR ' + Math.round(value).toLocaleString(),
                    from: value => Number(value.replace(/[^0-9.-]+/g, ""))
                }
            });

            noUiSliderInstance = priceRangeSlider.noUiSlider;
            noUiSliderInstance.on('update', (values) => {
                const [min, max] = values.map(v => parseInt(v.replace(/[^0-9]/g, '')));
                minPriceDisplay.textContent = `IDR ${min.toLocaleString()}`;
                maxPriceDisplay.textContent = `IDR ${max.toLocaleString()}`;
                filterByPrice(filterType, min, max);
            });
        }

        function filterByPrice(filterType, min, max) {
            try {
                if (filterType === 'hotel') {
                    const rateItems = document.querySelectorAll('.rate-item');
                    if (rateItems.length === 0) {
                        console.warn('No rate items found for hotel filter');
                        return;
                    }

                    rateItems.forEach(item => {
                        const priceElement = item.querySelector('.rate-price');
                        if (!priceElement) {
                            console.warn('Rate item missing price element', item);
                            return;
                        }

                        const price = parseInt(priceElement.textContent.replace(/[^0-9]/g, '')) || 0;
                        const shouldShow = price >= min && price <= max;
                        item.style.display = shouldShow ? 'block' : 'none';
                        
                        const section = item.closest('.hotel-section-1, .hotel-section-2');
                        if (section) {
                            const visibleItems = section.querySelectorAll('.rate-item[style="display: block;"]').length > 0;
                            section.style.display = visibleItems ? 'block' : 'none';
                        }
                    });
                } 
                else if (filterType === 'housing') {
                    const housingPlan = document.querySelector('input[name="housing-plan"]:checked').value;
					const planItems = document.querySelectorAll('.plan-item');
					
					planItems.forEach(item => {
						const priceElement = item.querySelector('.plan-price');
						const itemPlan = item.getAttribute('data-booking-type').split('-')[2];
						const price = parseInt(priceElement.textContent.replace(/[^0-9]/g, '')) || 0;
						
						// Show if "all" is selected or if plan matches
						const shouldShow = (housingPlan === 'all' || itemPlan === housingPlan) && 
										  (price >= min && price <= max);
						
						item.style.display = shouldShow ? 'block' : 'none';

                        const section = item.closest('.housing-section-1, .housing-section-2');
                        if (section) {
                            const visiblePlans = section.querySelectorAll('.plan-item[style="display: block;"]').length > 0;
                            section.style.display = visiblePlans ? 'block' : 'none';
                        }
                    });
                }
                else if (filterType === 'venue') {
                    const venueOptions = document.querySelectorAll('.venue-option');
                    if (venueOptions.length === 0) {
                        console.warn('No venue options found for venue filter');
                        return;
                    }

                    venueOptions.forEach(option => {
                        const priceElement = option.querySelector('.venue-price');
                        if (!priceElement) {
                            console.warn('Venue option missing price element', option);
                            return;
                        }

                        const price = parseInt(priceElement.textContent.replace(/[^0-9]/g, '')) || 0;
                        const shouldShow = price >= min && price <= max;
                        option.style.display = shouldShow ? 'block' : 'none';
                    });
                }
            } catch (error) {
                console.error('Error in filterByPrice:', error);
            }
        }

        function highlightSelectedFilter(selectedRadio) {
            document.querySelectorAll('.filter-option').forEach(option => 
                option.classList.remove('active'));
            selectedRadio.closest('.filter-option').classList.add('active');
        }

        // Initial setup
        showAllSections();
    }

    // Initialize dropdowns
    handleDropdowns();
});