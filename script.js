// ============================================
// University Transport Booking System - JS
// ============================================

document.addEventListener('DOMContentLoaded', () => {
    // Auto-dismiss alerts after 5 seconds
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });

    // Close sidebar on outside click (mobile)
    document.addEventListener('click', (e) => {
        const sidebar = document.getElementById('sidebar');
        const toggle = document.querySelector('.sidebar-toggle');
        if (sidebar && sidebar.classList.contains('open') &&
            !sidebar.contains(e.target) && !toggle.contains(e.target)) {
            sidebar.classList.remove('open');
        }
    });
});

// Modal helpers
function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.classList.add('show');
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.classList.remove('show');
}

// Confirmation dialog
function confirmAction(message, url) {
    if (confirm(message)) {
        window.location.href = url;
    }
    return false;
}

// Form validation helper
function validateBookingForm() {
    const date = document.getElementById('booking_date');
    const start = document.getElementById('start_time');
    const end = document.getElementById('end_time');

    if (date && start && end) {
        const today = new Date().toISOString().split('T')[0];
        if (date.value < today) {
            alert('Booking date cannot be in the past.');
            return false;
        }
        if (start.value >= end.value) {
            alert('End time must be after start time.');
            return false;
        }
    }
    return true;
}
