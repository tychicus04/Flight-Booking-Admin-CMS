// ============================================
// FILE: assets/js/column-visibility.js
// Column Visibility Toggle Component
// ============================================

(function() {
    'use strict';
    
    // Prevent duplicate initialization
    if (window.ColumnVisibilityLoaded) return;
    window.ColumnVisibilityLoaded = true;

class ColumnVisibility {
    constructor(tableSelector, storageKey) {
        this.tableSelector = tableSelector;
        this.storageKey = storageKey;
        this.table = document.querySelector(tableSelector);
        this.columns = [];
        this.settings = this.loadSettings();
    }

    /**
     * Initialize the column visibility component
     * @param {Array} columns - Array of column objects with label and key
     */
    init(columns) {
        if (!this.table) {
            console.warn('Table not found:', this.tableSelector);
            return;
        }

        this.columns = columns;
        this.createToggleUI();
        this.applyVisibility();
    }

    /**
     * Create the toggle UI dropdown
     */
    createToggleUI() {
        const cardActions = this.table.closest('.card').querySelector('.card-actions');
        if (!cardActions) {
            console.warn('Card actions not found');
            return;
        }

        const dropdown = document.createElement('div');
        dropdown.className = 'column-visibility-dropdown';
        dropdown.innerHTML = `
            <button class="btn btn-outline-secondary" type="button" id="columnToggleBtn">
                <i class="bi bi-list-columns"></i> Hiển thị cột
            </button>
            <div class="column-visibility-menu" id="columnVisibilityMenu">
                ${this.columns.map((col, index) => `
                    <div class="column-visibility-item">
                        <input type="checkbox" 
                               class="form-check-input column-checkbox" 
                               data-column="${col.key}" 
                               id="col-${col.key}-${index}"
                               ${this.isVisible(col.key) ? 'checked' : ''}>
                        <label for="col-${col.key}-${index}">${col.label}</label>
                    </div>
                `).join('')}
                <div class="column-visibility-footer">
                    <button type="button" class="btn btn-sm btn-link" onclick="columnVisibility.showAll()">
                        Hiện tất cả
                    </button>
                    <button type="button" class="btn btn-sm btn-link" onclick="columnVisibility.reset()">
                        Đặt lại
                    </button>
                </div>
            </div>
        `;

        cardActions.insertBefore(dropdown, cardActions.firstChild);
        this.attachEvents();
    }

    /**
     * Attach event listeners
     */
    attachEvents() {
        const button = document.getElementById('columnToggleBtn');
        const menu = document.getElementById('columnVisibilityMenu');

        if (!button || !menu) return;

        // Toggle menu
        button.addEventListener('click', (e) => {
            e.stopPropagation();
            menu.classList.toggle('show');
        });

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.column-visibility-dropdown')) {
                menu.classList.remove('show');
            }
        });

        // Prevent menu from closing when clicking inside
        menu.addEventListener('click', (e) => {
            e.stopPropagation();
        });

        // Handle checkbox changes
        menu.querySelectorAll('.column-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {
                const columnKey = e.target.dataset.column;
                this.toggleColumn(columnKey, e.target.checked);
            });
        });
    }

    /**
     * Toggle column visibility
     * @param {string} columnKey - The column key to toggle
     * @param {boolean} visible - Whether to show or hide the column
     */
    toggleColumn(columnKey, visible) {
        const cells = this.table.querySelectorAll(`.${columnKey}`);
        cells.forEach(cell => {
            cell.style.display = visible ? '' : 'none';
        });

        this.settings[columnKey] = visible;
        this.saveSettings();
    }

    /**
     * Apply visibility settings to all columns
     */
    applyVisibility() {
        this.columns.forEach(col => {
            if (!this.isVisible(col.key)) {
                this.toggleColumn(col.key, false);
            }
        });
    }

    /**
     * Check if a column is visible
     * @param {string} columnKey - The column key to check
     * @returns {boolean}
     */
    isVisible(columnKey) {
        return this.settings[columnKey] !== false;
    }

    /**
     * Show all columns
     */
    showAll() {
        this.columns.forEach(col => {
            this.toggleColumn(col.key, true);
            const checkbox = document.querySelector(`input[data-column="${col.key}"]`);
            if (checkbox) checkbox.checked = true;
        });
    }

    /**
     * Reset to default settings
     */
    reset() {
        this.settings = {};
        this.saveSettings();
        this.showAll();
    }

    /**
     * Load settings from localStorage
     * @returns {Object}
     */
    loadSettings() {
        const saved = localStorage.getItem(this.storageKey);
        return saved ? JSON.parse(saved) : {};
    }

    /**
     * Save settings to localStorage
     */
    saveSettings() {
        localStorage.setItem(this.storageKey, JSON.stringify(this.settings));
    }
}

// Global instance variable
window.columnVisibility = null;

// Export to global scope
window.ColumnVisibility = ColumnVisibility;

})();
