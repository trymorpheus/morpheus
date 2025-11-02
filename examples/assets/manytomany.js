/**
 * DynamicCRUD - Advanced Many-to-Many UI
 * Provides checkbox interface with search functionality
 */

class ManyToManyUI {
    constructor(container) {
        this.container = container;
        this.searchInput = container.querySelector('.m2m-search');
        this.options = container.querySelectorAll('.m2m-option');
        this.checkboxes = container.querySelectorAll('.m2m-option input[type="checkbox"]');
        this.stats = container.querySelector('.m2m-stats');
        this.selectAllBtn = container.querySelector('.m2m-select-all');
        this.clearAllBtn = container.querySelector('.m2m-clear-all');
        
        this.init();
    }
    
    init() {
        // Search functionality
        if (this.searchInput) {
            this.searchInput.addEventListener('input', () => this.handleSearch());
        }
        
        // Select/Clear all buttons
        if (this.selectAllBtn) {
            this.selectAllBtn.addEventListener('click', () => this.selectAll());
        }
        
        if (this.clearAllBtn) {
            this.clearAllBtn.addEventListener('click', () => this.clearAll());
        }
        
        // Update stats on checkbox change
        this.checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => this.updateStats());
        });
        
        // Initial stats
        this.updateStats();
    }
    
    handleSearch() {
        const query = this.searchInput.value.toLowerCase().trim();
        
        this.options.forEach(option => {
            const label = option.querySelector('label').textContent.toLowerCase();
            
            if (label.includes(query)) {
                option.classList.remove('hidden');
            } else {
                option.classList.add('hidden');
            }
        });
    }
    
    selectAll() {
        const visibleCheckboxes = Array.from(this.options)
            .filter(option => !option.classList.contains('hidden'))
            .map(option => option.querySelector('input[type="checkbox"]'));
        
        visibleCheckboxes.forEach(checkbox => {
            checkbox.checked = true;
        });
        
        this.updateStats();
    }
    
    clearAll() {
        this.checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        
        this.updateStats();
    }
    
    updateStats() {
        const total = this.checkboxes.length;
        const selected = Array.from(this.checkboxes).filter(cb => cb.checked).length;
        
        if (this.stats) {
            // Try to get translation from data attribute, fallback to Spanish
            const template = this.stats.dataset.template || ':count de :total seleccionados';
            this.stats.textContent = template.replace(':count', selected).replace(':total', total);
        }
    }
}

// Auto-initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    const containers = document.querySelectorAll('.m2m-container');
    containers.forEach(container => {
        new ManyToManyUI(container);
    });
});
