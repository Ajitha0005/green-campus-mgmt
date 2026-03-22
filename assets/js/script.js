document.addEventListener('DOMContentLoaded', () => {
    // Mobile menu toggle
    const menuBtn = document.querySelector('.mobile-menu-btn');
    const navLinks = document.querySelector('.nav-links');
    const navAuth = document.querySelector('.nav-auth');
    
    if(menuBtn) {
        menuBtn.addEventListener('click', () => {
            if(navLinks) navLinks.classList.toggle('active');
            // Small hack to show auth section on mobile if nav is active
            if(navAuth) navAuth.classList.toggle('mobile-active');
        });
    }
    
    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s ease';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });

    // Initialize the simple CSS bar charts (animate height)
    const bars = document.querySelectorAll('.bar');
    bars.forEach(bar => {
        // Assume inline style sets custom property or data attribute
        const targetHeight = bar.getAttribute('data-height');
        if(targetHeight) {
            // Start at 0, then animate
            bar.style.height = '0%';
            setTimeout(() => {
                bar.style.height = targetHeight + '%';
            }, 300);
        }
    });
});
