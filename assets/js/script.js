/**
 * MedMock - Main JavaScript File
 */

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(function () {
                alert.remove();
            }, 500);
        }, 5000);
    });

    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(function (form) {
        form.addEventListener('submit', function (e) {
            const password = form.querySelector('input[type="password"]');
            const confirmPassword = form.querySelector('#confirm_password');

            if (password && confirmPassword) {
                if (password.value !== confirmPassword.value) {
                    e.preventDefault();
                    showAlert('Passwords do not match!', 'danger');
                }
            }
        });
    });

    // Mobile navigation toggle
    const navbar = document.querySelector('#navbar');
    const navLinks = document.querySelector('.nav-links');

    if (navbar && navLinks) {
        const toggleBtn = document.createElement('button');
        toggleBtn.classList.add('nav-toggle');
        toggleBtn.innerHTML = '&#9776;';
        toggleBtn.style.cssText = `
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
        `;

        navbar.querySelector('.container').insertBefore(toggleBtn, navLinks);

        // Show toggle button on mobile
        if (window.innerWidth <= 768) {
            toggleBtn.style.display = 'block';
            navLinks.style.display = 'none';
        }

        toggleBtn.addEventListener('click', function () {
            if (navLinks.style.display === 'none' || navLinks.style.display === '') {
                navLinks.style.display = 'flex';
            } else {
                navLinks.style.display = 'none';
            }
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth <= 768) {
                toggleBtn.style.display = 'block';
            } else {
                toggleBtn.style.display = 'none';
                navLinks.style.display = 'flex';
            }
        });
    }

    /**
     * Show alert message
     * 
     * @param {string} message
     * @param {string} type (success, danger, warning)
     */
    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-' + type;
        alertDiv.textContent = message;

        const container = document.querySelector('.container');
        if (container) {
            container.insertBefore(alertDiv, container.firstChild);

            setTimeout(function () {
                alertDiv.style.transition = 'opacity 0.5s ease';
                alertDiv.style.opacity = '0';
                setTimeout(function () {
                    alertDiv.remove();
                }, 500);
            }, 5000);
        }
    }
});

