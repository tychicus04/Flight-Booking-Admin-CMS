/* ============================================
   SIDEBAR JAVASCRIPT - WITH ACCORDION & POPUP
   ============================================ */

(function() {
    'use strict';
    
    // Prevent duplicate initialization
    if (window.SidebarLoaded) return;
    window.SidebarLoaded = true;

document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mainContent = document.getElementById('mainContent') || document.querySelector('.main-content');
    
    console.log('Sidebar:', sidebar);
    console.log('MainContent:', mainContent);
    console.log('SidebarToggle:', sidebarToggle);
    
    if (!sidebar) {
        console.error('Sidebar element not found');
        return;
    }
    
    if (!mainContent) {
        console.warn('MainContent element not found');
    }
    
    // ============================================
    // DISABLE DEFAULT TOOLTIPS/TITLES
    // ============================================
    // Remove title attributes to prevent browser tooltips
    document.querySelectorAll('.sidebar .nav-link').forEach(link => {
        link.removeAttribute('title');
        link.removeAttribute('data-bs-toggle');
        link.removeAttribute('data-bs-placement');
    });
    
    // Restore state on load
    if (window.innerWidth > 768) {
        const sidebarCollapsed = localStorage.getItem('sidebarCollapsed');
        if (sidebarCollapsed === 'true') {
            sidebar.classList.add('collapsed');
            if (mainContent) {
                mainContent.classList.add('sidebar-collapsed');
            }
        }
    }
    
    setTimeout(() => {
        document.documentElement.classList.remove('sidebar-collapsed-state');
    }, 100);
    
    // Toggle sidebar
    function toggleSidebar() {
        if (window.innerWidth > 768) {
            sidebar.classList.toggle('collapsed');
            if (mainContent) {
                mainContent.classList.toggle('sidebar-collapsed');
            }
            
            void sidebar.offsetWidth;
            if (mainContent) {
                void mainContent.offsetWidth;
            }
            
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('sidebarCollapsed', isCollapsed);
            
            if (isCollapsed) {
                closeAllSubmenus();
            }
            
            window.dispatchEvent(new Event('resize'));
        } else {
            sidebar.classList.toggle('show');
        }
    }
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function(e) {
            e.preventDefault();
            toggleSidebar();
        });
    }
    
    // Close sidebar on outside click (mobile)
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        }
    });
    
    // Handle window resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('show');
                const sidebarCollapsed = localStorage.getItem('sidebarCollapsed');
                if (sidebarCollapsed === 'true') {
                    sidebar.classList.add('collapsed');
                    if (mainContent) {
                        mainContent.classList.add('sidebar-collapsed');
                    }
                }
            } else {
                sidebar.classList.remove('collapsed');
                if (mainContent) {
                    mainContent.classList.remove('sidebar-collapsed');
                }
            }
        }, 250);
    });
    
    // Close all submenus
    function closeAllSubmenus() {
        document.querySelectorAll('.nav-submenu.show').forEach(submenu => {
            submenu.classList.remove('show');
        });
        document.querySelectorAll('.nav-parent[aria-expanded="true"]').forEach(parent => {
            parent.setAttribute('aria-expanded', 'false');
        });
    }
    
    // ============================================
    // ACCORDION BEHAVIOR - One parent menu at a time
    // ============================================
    const parentLinks = document.querySelectorAll('.nav-parent');
    
    parentLinks.forEach(parent => {
        parent.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Don't do anything if sidebar is collapsed (popup menu will handle it)
            if (sidebar.classList.contains('collapsed')) {
                return;
            }
            
            const targetId = this.getAttribute('href').replace('#', '');
            const targetSubmenu = document.getElementById(targetId);
            
            if (!targetSubmenu) return;
            
            const isCurrentlyOpen = targetSubmenu.classList.contains('show');
            
            // Close ALL other submenus (Accordion behavior)
            document.querySelectorAll('.nav-submenu').forEach(submenu => {
                if (submenu !== targetSubmenu) {
                    submenu.classList.remove('show');
                }
            });
            
            // Update aria-expanded for all parents
            document.querySelectorAll('.nav-parent').forEach(p => {
                if (p !== this) {
                    p.setAttribute('aria-expanded', 'false');
                }
            });
            
            // Toggle current submenu
            if (isCurrentlyOpen) {
                targetSubmenu.classList.remove('show');
                this.setAttribute('aria-expanded', 'false');
            } else {
                targetSubmenu.classList.add('show');
                this.setAttribute('aria-expanded', 'true');
            }
        });
    });
    
    // ============================================
    // POPUP MENU - When sidebar is collapsed
    // ============================================
    document.querySelectorAll('.nav-group').forEach(group => {
        const parent = group.querySelector('.nav-parent');
        const submenu = group.querySelector('.nav-submenu');
        
        if (!parent || !submenu) return;
        
        let popupMenu = null;
        let hideTimer = null;
        
        // Show popup on hover
        parent.addEventListener('mouseenter', function() {
            if (!sidebar.classList.contains('collapsed') || window.innerWidth <= 768) return;
            
            clearTimeout(hideTimer);
            
            // Remove any existing popup
            if (popupMenu) {
                popupMenu.remove();
            }
            
            // Clone submenu for popup
            popupMenu = submenu.cloneNode(true);
            popupMenu.classList.add('show', 'popup-menu-active');
            popupMenu.style.display = 'block';
            
            // Get parent and sidebar position
            const parentRect = parent.getBoundingClientRect();
            const sidebarRect = sidebar.getBoundingClientRect();
            
            // Initial position (will be adjusted after append)
            popupMenu.style.position = 'fixed';
            popupMenu.style.zIndex = '1050';
            popupMenu.style.visibility = 'hidden'; // Hide until positioned correctly
            
            // Add to body first
            document.body.appendChild(popupMenu);
            
            // Calculate and set final position
            const popupRect = popupMenu.getBoundingClientRect();
            
            // Horizontal: No gap - attach directly to sidebar
            let leftPosition = sidebarRect.right;
            
            // Vertical: align with parent icon top
            let topPosition = parentRect.top;
            
            // Adjust if overflow bottom
            if (topPosition + popupRect.height > window.innerHeight - 10) {
                topPosition = Math.max(10, window.innerHeight - popupRect.height - 10);
            }
            
            // Adjust if overflow top
            if (topPosition < 10) {
                topPosition = 10;
            }
            
            // Adjust if overflow right
            if (leftPosition + popupRect.width > window.innerWidth - 10) {
                leftPosition = window.innerWidth - popupRect.width - 10;
            }
            
            // Apply final positions
            popupMenu.style.top = topPosition + 'px';
            popupMenu.style.left = leftPosition + 'px';
            popupMenu.style.visibility = 'visible'; // Show popup
            
            // Keep popup visible when hovering over it
            popupMenu.addEventListener('mouseenter', function() {
                clearTimeout(hideTimer);
            });
            
            popupMenu.addEventListener('mouseleave', function() {
                hideTimer = setTimeout(() => {
                    if (popupMenu) {
                        popupMenu.remove();
                        popupMenu = null;
                    }
                }, 100);
            });
        });
        
        // Hide popup when leaving parent
        parent.addEventListener('mouseleave', function() {
            hideTimer = setTimeout(() => {
                if (popupMenu && !popupMenu.matches(':hover')) {
                    popupMenu.remove();
                    popupMenu = null;
                }
            }, 100);
        });
    });
    
    // ============================================
    // TOOLTIP/POPUP for Single Nav Links (no submenu)
    // ============================================
    // Select only direct nav-links that are NOT nav-parent
    document.querySelectorAll('.sidebar-nav > .nav-link:not(.nav-parent)').forEach(link => {
        let popup = null;
        let hideTimer = null;
        
        link.addEventListener('mouseenter', function() {
            // Only show popup when sidebar is collapsed
            if (!sidebar.classList.contains('collapsed') || window.innerWidth <= 768) return;
            
            clearTimeout(hideTimer);
            
            // Remove existing popup
            if (popup) {
                popup.remove();
                popup = null;
            }
            
            // Get nav text and icon
            const navText = this.querySelector('.nav-text');
            const navIcon = this.querySelector('i');
            if (!navText) return;
            
            const text = navText.textContent.trim();
            const iconClass = navIcon ? navIcon.className : '';
            const href = this.getAttribute('href');
            if (!text) return;
            
            // Create popup (similar to submenu popup)
            popup = document.createElement('div');
            popup.className = 'nav-submenu popup-menu-active show';
            popup.style.display = 'block';
            
            // Create single link inside popup
            const popupLink = document.createElement('a');
            popupLink.className = 'nav-link';
            popupLink.href = href;
            if (iconClass) {
                const icon = document.createElement('i');
                icon.className = iconClass;
                popupLink.appendChild(icon);
            }
            const textSpan = document.createElement('span');
            textSpan.className = 'nav-text';
            textSpan.textContent = text;
            popupLink.appendChild(textSpan);
            
            popup.appendChild(popupLink);
            
            // Get positions
            const linkRect = this.getBoundingClientRect();
            const sidebarRect = sidebar.getBoundingClientRect();
            
            // Initial position
            popup.style.position = 'fixed';
            popup.style.zIndex = '1050';
            popup.style.visibility = 'hidden'; // Hide until positioned correctly
            
            // Add to body first
            document.body.appendChild(popup);
            
            // Calculate and set final position
            const popupRect = popup.getBoundingClientRect();
            
            // Horizontal: No gap - attach directly to sidebar
            let leftPosition = sidebarRect.right;
            
            // Vertical: align with link top
            let topPosition = linkRect.top;
            
            // Adjust if overflow bottom
            if (topPosition + popupRect.height > window.innerHeight - 10) {
                topPosition = Math.max(10, window.innerHeight - popupRect.height - 10);
            }
            
            // Adjust if overflow top
            if (topPosition < 10) {
                topPosition = 10;
            }
            
            // Adjust if overflow right
            if (leftPosition + popupRect.width > window.innerWidth - 10) {
                leftPosition = window.innerWidth - popupRect.width - 10;
            }
            
            // Apply final positions
            popup.style.top = topPosition + 'px';
            popup.style.left = leftPosition + 'px';
            popup.style.visibility = 'visible'; // Show popup
            
            // Keep popup visible when hovering over it
            popup.addEventListener('mouseenter', function() {
                clearTimeout(hideTimer);
            });
            
            popup.addEventListener('mouseleave', function() {
                hideTimer = setTimeout(() => {
                    if (popup) {
                        popup.remove();
                        popup = null;
                    }
                }, 100);
            });
        });
        
        link.addEventListener('mouseleave', function() {
            hideTimer = setTimeout(() => {
                if (popup && !popup.matches(':hover')) {
                    popup.remove();
                    popup = null;
                }
            }, 100);
        });
    });
    
    // ============================================
    // SEARCH FUNCTIONALITY
    // ============================================
    const searchInput = document.getElementById('sidebarSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            
            if (searchTerm === '') {
                // Show all items
                document.querySelectorAll('.nav-link, .nav-group').forEach(item => {
                    item.style.display = '';
                });
                return;
            }
            
            // Filter menu items
            document.querySelectorAll('.nav-link').forEach(link => {
                const text = link.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    link.style.display = '';
                    // Show parent group if it's a submenu item
                    const parentGroup = link.closest('.nav-group');
                    if (parentGroup) {
                        parentGroup.style.display = '';
                        const submenu = link.closest('.nav-submenu');
                        if (submenu) {
                            submenu.classList.add('show');
                        }
                    }
                } else {
                    // Don't hide parent links, only regular links
                    if (!link.classList.contains('nav-parent')) {
                        link.style.display = 'none';
                    }
                }
            });
        });
    }
});

})(); // End IIFE
