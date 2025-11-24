class Notebook {
    constructor() {
        this.currentPage = 0;
        this.pages = document.querySelectorAll('.page');
        this.indicators = document.querySelectorAll('.page-indicator');
        this.prevBtn = document.querySelector('.prev-btn');
        this.nextBtn = document.querySelector('.next-btn');
        
        this.init();
    }
    
    init() {
        this.updateNavigation();
        this.setupEventListeners();
        this.showPage(this.currentPage);
    }
    
    setupEventListeners() {
        if (this.prevBtn) {
            this.prevBtn.addEventListener('click', () => this.prevPage());
        }
        
        if (this.nextBtn) {
            this.nextBtn.addEventListener('click', () => this.nextPage());
        }
        
        // Page indicator clicks
        this.indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', () => this.goToPage(index));
        });
        
        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') {
                this.prevPage();
            } else if (e.key === 'ArrowRight') {
                this.nextPage();
            }
        });
    }
    
    showPage(pageIndex) {
        // Hide all pages
        this.pages.forEach(page => {
            page.classList.remove('active', 'prev', 'next');
        });
        
        // Show current page
        this.pages[pageIndex].classList.add('active');
        
        // Show previous page if exists
        if (pageIndex > 0) {
            this.pages[pageIndex - 1].classList.add('prev');
        }
        
        // Show next page if exists
        if (pageIndex < this.pages.length - 1) {
            this.pages[pageIndex + 1].classList.add('next');
        }
        
        // Update indicators
        this.indicators.forEach((indicator, index) => {
            indicator.classList.toggle('active', index === pageIndex);
        });
        
        this.updateNavigation();
    }
    
    nextPage() {
        if (this.currentPage < this.pages.length - 1) {
            this.currentPage++;
            this.showPage(this.currentPage);
        }
    }
    
    prevPage() {
        if (this.currentPage > 0) {
            this.currentPage--;
            this.showPage(this.currentPage);
        }
    }
    
    goToPage(pageIndex) {
        if (pageIndex >= 0 && pageIndex < this.pages.length) {
            this.currentPage = pageIndex;
            this.showPage(this.currentPage);
        }
    }
    
    updateNavigation() {
        if (this.prevBtn) {
            this.prevBtn.disabled = this.currentPage === 0;
        }
        if (this.nextBtn) {
            this.nextBtn.disabled = this.currentPage === this.pages.length - 1;
        }
    }
}

// Initialize notebook when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const notebook = new Notebook();
    
    // Add some interactive elements
    initInteractiveElements();
});

function initInteractiveElements() {
    // Add hover effects to cards
    const cards = document.querySelectorAll('.notebook-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 5px 20px rgba(0,0,0,0.2)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
        });
    });
    
    // Form animations
    const formInputs = document.querySelectorAll('.form-group input, .form-group select, .form-group textarea');
    formInputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            if (!this.value) {
                this.parentElement.classList.remove('focused');
            }
        });
    });
}