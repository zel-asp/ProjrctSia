// Toggle mobile menu
document.getElementById('menuToggle').addEventListener('click', function () {
    document.getElementById('navMenu').style.transform = 'translateX(0)';
});

document.getElementById('Exbtn').addEventListener('click', function () {
    document.getElementById('navMenu').style.transform = 'translateX(-100%)';
});


// Add smooth scrolling to all links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();

        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });

        // Close mobile menu if open
        document.getElementById('navMenu').style.transform = 'translateX(-100%)';
    });
});

document.addEventListener('DOMContentLoaded', function () {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});


//for animayion and transition

// Intersection Observer for section animations
const sections = document.querySelectorAll('section');

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('in-view');
        }
    });
}, {
    threshold: 0.1
});

sections.forEach(section => {
    observer.observe(section);
});

// Mobile menu toggle animation
const menuToggle = document.getElementById('menuToggle');
const navMenu = document.getElementById('navMenu');
const Exbtn = document.getElementById('Exbtn');

menuToggle.addEventListener('click', () => {
    navMenu.classList.add('show');
});

Exbtn.addEventListener('click', () => {
    navMenu.classList.remove('show');
});

