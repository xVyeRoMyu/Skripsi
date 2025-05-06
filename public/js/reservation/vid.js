function initSlider(wrapper) {
    const slides = wrapper.querySelectorAll('input[type="radio"]');
    const prevBtn = wrapper.querySelector('.arrow.prev');
    const nextBtn = wrapper.querySelector('.arrow.next');
    const images = wrapper.querySelectorAll('.img');
    let currentIndex = 0;
    
    // Ensure first slide is active on load
    if (slides.length > 0) {
        slides[0].checked = true;
        updateSlider();
    }

    // Arrow navigation
    prevBtn?.addEventListener('click', function() {
        currentIndex = (currentIndex - 1 + slides.length) % slides.length;
        slides[currentIndex].checked = true;
        updateSlider();
    });

    nextBtn?.addEventListener('click', function() {
        currentIndex = (currentIndex + 1) % slides.length;
        slides[currentIndex].checked = true;
        updateSlider();
    });

    // Dot navigation
    slides.forEach((slide, index) => {
        slide.addEventListener('change', function() {
            if (slide.checked) {
                currentIndex = index;
                updateSlider();
            }
        });
    });

    function updateSlider() {
        images.forEach((img, i) => {
            if (i === currentIndex) {
                // Show current slide
                img.style.display = 'block';
                setTimeout(() => {
                    img.style.opacity = '1';
                    img.style.zIndex = '1';
                }, 10);
                
                // Handle video playback if exists
                const video = img.querySelector('video');
                if (video) {
                    video.style.display = 'block';
                    video.style.opacity = '1';
                    video.play().catch(e => console.log('Video play prevented:', e));
                }
            } else {
                // Hide other slides
                img.style.opacity = '0';
                setTimeout(() => {
                    img.style.zIndex = '0';
                    img.style.display = 'none';
                }, 300);
                
                // Pause video if exists
                const video = img.querySelector('video');
                if (video) {
                    video.pause();
                    video.currentTime = 0;
                }
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all sliders on the page
    document.querySelectorAll('.room-image .wrapper, .unit-image .wrapper').forEach(wrapper => {
        initSlider(wrapper);
    });
});