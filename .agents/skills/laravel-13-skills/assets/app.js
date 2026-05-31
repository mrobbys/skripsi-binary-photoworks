/* ========================================
   Laravel 13 Skills - JavaScript
   ======================================== */

document.addEventListener('DOMContentLoaded', function () {
    // Theme Toggle
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = themeToggle.querySelector('.theme-icon');

    // Check saved theme
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    updateThemeIcon(savedTheme);

    themeToggle.addEventListener('click', function () {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateThemeIcon(newTheme);
    });

    function updateThemeIcon(theme) {
        themeIcon.textContent = theme === 'dark' ? '☀️' : '🌙';
    }

    // Tab Switching
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabBtns.forEach((btn) => {
        btn.addEventListener('click', function () {
            const tabId = this.getAttribute('data-tab');

            // Remove active class from all
            tabBtns.forEach((b) => b.classList.remove('active'));
            tabContents.forEach((c) => c.classList.remove('active'));

            // Add active class to clicked
            this.classList.add('active');
            document.getElementById(tabId).classList.add('active');
        });
    });

    // Copy Buttons
    const copyBtns = document.querySelectorAll('.copy-btn');

    copyBtns.forEach((btn) => {
        btn.addEventListener('click', function () {
            const textToCopy = this.getAttribute('data-copy');
            const originalText = this.textContent;

            navigator.clipboard
                .writeText(textToCopy)
                .then(() => {
                    this.textContent = '✓ Copied!';
                    this.style.background = '#22c55e';

                    setTimeout(() => {
                        this.textContent = originalText;
                        this.style.background = '';
                    }, 2000);
                })
                .catch((err) => {
                    console.error('Failed to copy:', err);
                    this.textContent = '✗ Error';
                    setTimeout(() => {
                        this.textContent = originalText;
                    }, 2000);
                });
        });
    });

    // Smooth Scroll for Navigation
    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                const headerOffset = 80;
                const elementPosition = target.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth',
                });
            }
        });
    });

    // Header Background on Scroll
    const header = document.querySelector('.header');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            header.style.boxShadow = 'var(--shadow-md)';
        } else {
            header.style.boxShadow = 'none';
        }
    });

    // Search Functionality (if search input exists)
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function (e) {
            const searchTerm = e.target.value.toLowerCase();
            const cards = document.querySelectorAll('.feature-card, .example-block');

            cards.forEach((card) => {
                const text = card.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }

    // Lazy load images (if any)
    if ('loading' in HTMLImageElement.prototype) {
        const images = document.querySelectorAll('img[loading="lazy"]');
        images.forEach((img) => {
            img.src = img.dataset.src;
        });
    }

    // Console greeting
    console.log('%c⚡ Laravel 13 Skills', 'font-size: 24px; font-weight: bold; color: #6366f1;');
    console.log('%cPHP 8 Attributes for AI Agents', 'font-size: 14px; color: #64748b;');
    console.log('%cGitHub: https://github.com/shamimlaravel/php-laravel-13-skills', 'color: #6366f1;');
});

// Add copy functionality to all pre > code blocks
document.querySelectorAll('pre code').forEach((block) => {
    const pre = block.parentElement;
    if (!pre.querySelector('.copy-btn')) {
        const copyBtn = document.createElement('button');
        copyBtn.className = 'copy-btn';
        copyBtn.textContent = 'Copy';
        copyBtn.setAttribute('data-copy', block.textContent);
        copyBtn.addEventListener('click', function () {
            const text = this.getAttribute('data-copy');
            navigator.clipboard.writeText(text).then(() => {
                this.textContent = '✓';
                setTimeout(() => (this.textContent = 'Copy'), 1500);
            });
        });
        pre.style.position = 'relative';
        pre.appendChild(copyBtn);
    }
});
