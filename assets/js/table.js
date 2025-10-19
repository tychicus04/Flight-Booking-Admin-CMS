
// ============================================
// FILE: assets/js/table.js
// ============================================

// Column Toggle
document.addEventListener('DOMContentLoaded', function() {
    const columnToggleBtn = document.getElementById('columnToggleBtn');
    const columnToggleMenu = document.getElementById('columnToggleMenu');
    
    if (columnToggleBtn) {
        columnToggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            columnToggleMenu.classList.toggle('show');
        });
    }
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (columnToggleMenu && !columnToggleMenu.contains(e.target)) {
            columnToggleMenu.classList.remove('show');
        }
    });
    
    // Column toggle checkboxes
    const columnCheckboxes = document.querySelectorAll('.column-checkbox');
    columnCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const columnClass = this.dataset.column;
            const columns = document.querySelectorAll('.' + columnClass);
            
            columns.forEach(col => {
                if (this.checked) {
                    col.style.display = '';
                } else {
                    col.style.display = 'none';
                }
            });
            
            // Save state to localStorage
            const hiddenColumns = Array.from(columnCheckboxes)
                .filter(cb => !cb.checked)
                .map(cb => cb.dataset.column);
            localStorage.setItem('hiddenColumns', JSON.stringify(hiddenColumns));
        });
    });
    
    // Restore column visibility
    const hiddenColumns = JSON.parse(localStorage.getItem('hiddenColumns') || '[]');
    hiddenColumns.forEach(columnClass => {
        const checkbox = document.querySelector(`[data-column="${columnClass}"]`);
        if (checkbox) {
            checkbox.checked = false;
            const columns = document.querySelectorAll('.' + columnClass);
            columns.forEach(col => col.style.display = 'none');
        }
    });
});

// Action Dropdown
(function() {
    let toggleTimeout = null;

    window.toggleActionMenu = function(button) {
        const menu = button.nextElementSibling;
        const allMenus = document.querySelectorAll('.action-menu');
        
        // Close all other menus
        allMenus.forEach(m => {
            if (m !== menu) {
                m.classList.remove('show');
            }
        });
        
        // Toggle current menu
        const isShowing = !menu.classList.contains('show');
        menu.classList.toggle('show');
        
        // Position menu using fixed positioning if showing
        if (isShowing) {
            const buttonRect = button.getBoundingClientRect();
            
            // Default position: below button, aligned to right
            let top = buttonRect.bottom + 4;
            let left = buttonRect.right;
            
            // Show menu to get its dimensions
            menu.style.display = 'block';
            const menuRect = menu.getBoundingClientRect();
            
            // Adjust left position (align right edge of menu with button)
            left = buttonRect.right - menuRect.width;
            
            // Check if menu overflows bottom of viewport
            if (top + menuRect.height > window.innerHeight - 10) {
                // Position above button instead
                top = buttonRect.top - menuRect.height - 4;
            }
            
            // Check if menu overflows top
            if (top < 10) {
                top = 10;
            }
            
            // Check if menu overflows left
            if (left < 10) {
                left = 10;
            }
            
            // Check if menu overflows right
            if (left + menuRect.width > window.innerWidth - 10) {
                left = window.innerWidth - menuRect.width - 10;
            }
            
            // Apply positions
            menu.style.top = top + 'px';
            menu.style.left = left + 'px';
        }
    };

    // Close all action menus when clicking outside
    document.addEventListener('click', function(e) {
        // Clear any pending timeout
        if (toggleTimeout) {
            clearTimeout(toggleTimeout);
        }
        
        // Check if click is on action button
        const actionButton = e.target.closest('.action-dropdown button');
        if (actionButton) {
            e.preventDefault();
            window.toggleActionMenu(actionButton);
            // Set a flag briefly to prevent immediate closing
            toggleTimeout = setTimeout(() => {
                toggleTimeout = null;
            }, 50);
            return;
        }
        
        // Skip if we just toggled
        if (toggleTimeout) {
            return;
        }
        
        // Check if click is inside action-dropdown container
        if (e.target.closest('.action-dropdown')) {
            return;
        }
        
        // Check if click is inside action-menu
        const clickedMenu = e.target.closest('.action-menu');
        if (clickedMenu) {
            // If clicked on a link, close the menu
            if (e.target.closest('.action-menu a')) {
                document.querySelectorAll('.action-menu').forEach(menu => {
                    menu.classList.remove('show');
                });
            }
            // Otherwise keep menu open
            return;
        }
        
        // Click is outside - close all menus
        document.querySelectorAll('.action-menu.show').forEach(menu => {
            menu.classList.remove('show');
        });
    });
})();

// Filter Toggle
function toggleFilter() {
    const filterSection = document.querySelector('.filter-section');
    if (filterSection) {
        filterSection.classList.toggle('collapsed');
        const icon = document.querySelector('.filter-header i');
        if (icon) {
            icon.classList.toggle('bi-funnel');
            icon.classList.toggle('bi-funnel-fill');
        }
    }
}

// Delete Confirmation
function confirmDelete(message = 'Bạn có chắc chắn muốn xóa?') {
    return confirm(message);
}

// Select All Checkboxes
function toggleSelectAll(source) {
    const checkboxes = document.querySelectorAll('.row-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = source.checked;
    });
    updateBulkActions();
}

function updateBulkActions() {
    const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
    const bulkActions = document.getElementById('bulkActions');
    
    if (bulkActions) {
        if (checkedBoxes.length > 0) {
            bulkActions.style.display = 'block';
            document.getElementById('selectedCount').textContent = checkedBoxes.length;
        } else {
            bulkActions.style.display = 'none';
        }
    }
}

// Search/Filter Form
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filterForm');
    if (filterForm) {
        const resetBtn = filterForm.querySelector('[type="reset"]');
        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                setTimeout(() => filterForm.submit(), 100);
            });
        }
    }
});
