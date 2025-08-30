// Dark mode toggle
document.getElementById('dark-mode-toggle')?.addEventListener('click', function() {
    fetch('user/toggle_preference.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'preference=dark_mode'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
});

// Language toggle
document.getElementById('language-toggle')?.addEventListener('click', function() {
    fetch('user/toggle_preference.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'preference=language'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
});

// Safe mode toggle
document.getElementById('safe-mode-toggle')?.addEventListener('click', function() {
    fetch('user/toggle_preference.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'preference=safe_mode'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
});

// Slideshow functionality
document.addEventListener('DOMContentLoaded', function() {
    const slides = document.querySelectorAll('.slide');
    if (slides.length > 0) {
        let currentSlide = 0;
        
        function showSlide(index) {
            slides.forEach(slide => slide.classList.remove('active'));
            slides[index].classList.add('active');
        }
        
        function nextSlide() {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        }
        
        // Auto advance slides every 5 seconds
        setInterval(nextSlide, 5000);
    }
    
    // Image viewer for mobile
    const readerImages = document.querySelectorAll('.reader-image');
    if (readerImages.length > 0) {
        let currentImageIndex = 0;
        
        readerImages.forEach((img, index) => {
            img.addEventListener('click', function() {
                currentImageIndex = (index + 1) % readerImages.length;
                readerImages[currentImageIndex].scrollIntoView({ behavior: 'smooth' });
            });
        });
    }
});