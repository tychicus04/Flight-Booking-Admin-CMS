
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
function toggleActionMenu(button) {
    const menu = button.nextElementSibling;
    const allMenus = document.querySelectorAll('.action-menu');
    
    // Close all other menus
    allMenus.forEach(m => {
        if (m !== menu) {
            m.classList.remove('show');
        }
    });
    
    menu.classList.toggle('show');
    
    // Close when clicking outside
    document.addEventListener('click', function closeMenu(e) {
        if (!button.contains(e.target) && !menu.contains(e.target)) {
            menu.classList.remove('show');
            document.removeEventListener('click', closeMenu);
        }
    });
}

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
