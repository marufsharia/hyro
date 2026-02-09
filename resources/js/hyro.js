// Hyro Package JavaScript - ES Module Version with Alpine.js 3 & Livewire 4
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import focus from '@alpinejs/focus';
import intersect from '@alpinejs/intersect';

// Register Alpine plugins
Alpine.plugin(collapse);
Alpine.plugin(focus);
Alpine.plugin(intersect);

// Make Alpine available globally
window.Alpine = Alpine;

// Alpine.js Data Components
Alpine.data('sidebar', () => ({
    open: localStorage.getItem('sidebar-open') === 'true',
    toggle() {
        this.open = !this.open;
        localStorage.setItem('sidebar-open', this.open);
    }
}));

Alpine.data('dropdown', () => ({
    open: false,
    toggle() {
        this.open = !this.open;
    },
    close() {
        this.open = false;
    }
}));

Alpine.data('modal', () => ({
    show: false,
    open() {
        this.show = true;
        document.body.style.overflow = 'hidden';
    },
    close() {
        this.show = false;
        document.body.style.overflow = '';
    }
}));

Alpine.data('tabs', (defaultTab = 0) => ({
    activeTab: defaultTab,
    setTab(index) {
        this.activeTab = index;
    }
}));

Alpine.data('toast', () => ({
    show: false,
    message: '',
    type: 'info',
    showToast(message, type = 'info') {
        this.message = message;
        this.type = type;
        this.show = true;
        setTimeout(() => {
            this.show = false;
        }, 5000);
    }
}));

// Start Alpine
Alpine.start();

export class Hyro {
    constructor() {
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        this.init();
    }

    init() {
        this.initForms();
        this.initModals();
        this.initNotifications();
        this.initDataTables();
        this.initTooltips();
        this.initDatePickers();
        this.bindGlobalEvents();
    }

    bindGlobalEvents() {
        // Handle click events on data attributes
        document.addEventListener('click', (e) => {
            // Confirm dialogs
            if (e.target.dataset.hyroConfirm) {
                e.preventDefault();
                this.handleConfirm(e.target);
            }

            // Modal triggers
            if (e.target.dataset.hyroModal) {
                e.preventDefault();
                this.openModal(e.target.dataset.hyroModal);
            }

            // Modal close
            if (e.target.dataset.hyroModalClose) {
                e.preventDefault();
                this.closeModal(e.target.closest('.hyro-modal'));
            }
        });
    }

    handleConfirm(element) {
        const message = element.dataset.hyroConfirmMessage ||
            element.getAttribute('title') ||
            element.textContent.trim() ||
            'Are you sure?';

        const form = element.closest('form');

        this.confirm(message).then((confirmed) => {
            if (confirmed && form) {
                form.submit();
            }
        });
    }

    // Form Handling
    initForms() {
        // AJAX Form Submission
        document.querySelectorAll('.hyro-ajax-form').forEach((form) => {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                const submitBtn = form.querySelector('[type="submit"]');
                const originalText = submitBtn.textContent;

                submitBtn.disabled = true;
                submitBtn.innerHTML = '<svg class="animate-spin h-5 w-5 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Processing...';

                try {
                    const formData = new FormData(form);
                    const response = await fetch(form.action, {
                        method: form.method,
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json',
                        },
                    });

                    const data = await response.json();

                    if (response.ok) {
                        this.showNotification(data.message || 'Success!', 'success');

                        if (form.dataset.redirect) {
                            setTimeout(() => {
                                window.location.href = form.dataset.redirect;
                            }, 1500);
                        }

                        if (form.dataset.reset === 'true') {
                            form.reset();
                        }
                    } else {
                        this.showNotification(data.message || 'An error occurred', 'error');
                        this.displayFormErrors(form, data.errors || {});
                    }
                } catch (error) {
                    this.showNotification('Network error occurred', 'error');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            });
        });
    }

    displayFormErrors(form, errors) {
        // Clear previous errors
        form.querySelectorAll('.text-red-500').forEach((el) => el.remove());
        form.querySelectorAll('.border-red-500').forEach((el) => el.classList.remove('border-red-500'));

        // Add new errors
        Object.entries(errors).forEach(([field, messages]) => {
            const input = form.querySelector(`[name="${field}"]`);
            if (input) {
                input.classList.add('border-red-500');
                const errorDiv = document.createElement('p');
                errorDiv.className = 'text-red-500 text-sm mt-1';
                errorDiv.textContent = Array.isArray(messages) ? messages[0] : messages;
                input.parentNode.appendChild(errorDiv);
            }
        });
    }

    // Notification System
    initNotifications() {
        // Auto-remove existing notifications
        document.querySelectorAll('[data-auto-dismiss]').forEach((alert) => {
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });
    }

    showNotification(message, type = 'info') {
        // Dispatch custom event for Alpine to handle
        window.dispatchEvent(new CustomEvent('notify', {
            detail: { message, type }
        }));
    }

    // Tooltips
    initTooltips() {
        document.querySelectorAll('[data-hyro-tooltip]').forEach((element) => {
            const tooltipText = element.dataset.hyroTooltip;

            element.addEventListener('mouseenter', () => {
                const tooltip = document.createElement('div');
                tooltip.className = 'hyro-tooltip';
                tooltip.textContent = tooltipText;

                document.body.appendChild(tooltip);

                const rect = element.getBoundingClientRect();
                tooltip.style.position = 'fixed';
                tooltip.style.left = `${rect.left + rect.width / 2}px`;
                tooltip.style.top = `${rect.top - 10}px`;
                tooltip.style.transform = 'translateX(-50%) translateY(-100%)';
                tooltip.style.zIndex = '1000';
                tooltip.style.padding = '0.5rem 0.75rem';
                tooltip.style.backgroundColor = 'rgba(0, 0, 0, 0.9)';
                tooltip.style.color = 'white';
                tooltip.style.borderRadius = '0.375rem';
                tooltip.style.fontSize = '0.75rem';
                tooltip.style.whiteSpace = 'nowrap';

                element._tooltip = tooltip;
            });

            element.addEventListener('mouseleave', () => {
                if (element._tooltip && element._tooltip.parentNode) {
                    element._tooltip.parentNode.removeChild(element._tooltip);
                    delete element._tooltip;
                }
            });
        });
    }

    // Modal System
    initModals() {
        // Close modal on backdrop click
        document.querySelectorAll('.hyro-modal').forEach((modal) => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.closeModal(modal);
                }
            });
        });

        // Close modal on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const openModal = document.querySelector('.hyro-modal.active');
                if (openModal) {
                    this.closeModal(openModal);
                }
            }
        });
    }

    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';

            // Focus first input in modal
            const firstInput = modal.querySelector('input, textarea, select, button');
            if (firstInput) {
                setTimeout(() => firstInput.focus(), 100);
            }
        }
    }

    closeModal(modal) {
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    // Data Tables
    initDataTables() {
        document.querySelectorAll('.hyro-table[data-sortable]').forEach((table) => {
            this.makeTableSortable(table);
        });
    }

    makeTableSortable(table) {
        const headers = table.querySelectorAll('th[data-sort]');
        headers.forEach((header) => {
            header.style.cursor = 'pointer';

            const sortIcon = document.createElement('span');
            sortIcon.className = 'hyro-sort-icon';
            sortIcon.innerHTML = '↕️';
            header.appendChild(sortIcon);

            header.addEventListener('click', () => {
                const sortBy = header.dataset.sort;
                const isAsc = header.classList.contains('asc');

                // Clear previous sort indicators
                headers.forEach((h) => {
                    h.classList.remove('asc', 'desc');
                });

                // Set new sort indicator
                header.classList.add(isAsc ? 'desc' : 'asc');

                // Sort table
                this.sortTable(table, sortBy, isAsc);
            });
        });
    }

    sortTable(table, sortBy, reverse = false) {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));

        rows.sort((a, b) => {
            const aCell = a.querySelector(`[data-sort="${sortBy}"]`);
            const bCell = b.querySelector(`[data-sort="${sortBy}"]`);

            const aValue = aCell?.textContent?.trim() || aCell?.dataset?.value || '';
            const bValue = bCell?.textContent?.trim() || bCell?.dataset?.value || '';

            // Try to compare as numbers if possible
            const aNum = parseFloat(aValue);
            const bNum = parseFloat(bValue);

            if (!isNaN(aNum) && !isNaN(bNum)) {
                return reverse ? bNum - aNum : aNum - bNum;
            }

            // Compare as strings
            if (reverse) {
                return bValue.localeCompare(aValue);
            }
            return aValue.localeCompare(bValue);
        });

        // Reorder rows
        rows.forEach((row) => tbody.appendChild(row));
    }

    // Date Pickers
    initDatePickers() {
        document.querySelectorAll('input[type="date"]').forEach((input) => {
            if (!input.value && input.dataset.defaultToday === 'true') {
                const today = new Date().toISOString().split('T')[0];
                input.value = today;
            }
        });
    }

    // Loading State
    showLoading(container) {
        const loadingDiv = document.createElement('div');
        loadingDiv.className = 'hyro-loading';
        loadingDiv.innerHTML = '<div class="hyro-spinner"></div>';

        if (container) {
            const originalContent = container.innerHTML;
            container.dataset.originalContent = originalContent;
            container.innerHTML = '';
            container.appendChild(loadingDiv);
        } else {
            document.body.appendChild(loadingDiv);
        }

        return loadingDiv;
    }

    hideLoading(container) {
        if (container) {
            const loadingDiv = container.querySelector('.hyro-loading');
            if (loadingDiv) {
                loadingDiv.remove();
                if (container.dataset.originalContent) {
                    container.innerHTML = container.dataset.originalContent;
                }
            }
        } else {
            document.querySelectorAll('.hyro-loading').forEach((el) => el.remove());
        }
    }

    // Fetch with timeout
    async fetchWithTimeout(resource, options = {}) {
        const { timeout = 8000 } = options;

        const controller = new AbortController();
        const id = setTimeout(() => controller.abort(), timeout);

        const response = await fetch(resource, {
            ...options,
            signal: controller.signal,
        });

        clearTimeout(id);
        return response;
    }

    // Utility Functions
    async confirm(message) {
        return window.confirm(message);
    }

    // Debounce function
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.Hyro = new Hyro();
});

// Export for ES module usage
export default Hyro;

// Auto-initialize if script is loaded as module
if (import.meta.hot) {
    import.meta.hot.accept();
}
