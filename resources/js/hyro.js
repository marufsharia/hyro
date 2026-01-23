// Hyro Package JavaScript - ES Module Version

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
                submitBtn.textContent = 'Processing...';

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

                        // Reset form if needed
                        if (form.dataset.reset === 'true') {
                            form.reset();
                        }
                    } else {
                        this.showNotification(data.message || 'An error occurred', 'danger');
                        this.displayFormErrors(form, data.errors || {});
                    }
                } catch (error) {
                    this.showNotification('Network error occurred', 'danger');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            });
        });
    }

    displayFormErrors(form, errors) {
        // Clear previous errors
        form.querySelectorAll('.hyro-form-error').forEach((el) => el.remove());
        form.querySelectorAll('.error').forEach((el) => el.classList.remove('error'));

        // Add new errors
        Object.entries(errors).forEach(([field, messages]) => {
            const input = form.querySelector(`[name="${field}"]`);
            if (input) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'hyro-form-error';
                errorDiv.textContent = Array.isArray(messages) ? messages[0] : messages;
                input.parentNode.appendChild(errorDiv);
                input.classList.add('error');
            }
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
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    // Notification System
    initNotifications() {
        // Auto-remove existing notifications
        document.querySelectorAll('.hyro-alert').forEach((alert) => {
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });
    }

    showNotification(message, type = 'info', title = '') {
        const container = document.querySelector('.hyro-toast-container') || this.createToastContainer();

        const toast = document.createElement('div');
        toast.className = `hyro-toast hyro-toast-${type}`;
        toast.innerHTML = `
            <div class="hyro-toast-content">
                ${title ? `<div class="hyro-toast-title">${title}</div>` : ''}
                <div class="hyro-toast-message">${message}</div>
            </div>
            <button class="hyro-toast-close">&times;</button>
        `;

        toast.querySelector('.hyro-toast-close').addEventListener('click', () => {
            this.removeToast(toast);
        });

        container.appendChild(toast);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            this.removeToast(toast);
        }, 5000);
    }

    createToastContainer() {
        const container = document.createElement('div');
        container.className = 'hyro-toast-container';
        document.body.appendChild(container);
        return container;
    }

    removeToast(toast) {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 300);
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

    // Tooltips
    initTooltips() {
        document.querySelectorAll('[data-hyro-tooltip]').forEach((element) => {
            const tooltipText = element.dataset.hyroTooltip;

            element.addEventListener('mouseenter', (e) => {
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

                element.dataset.tooltipId = tooltip;
            });

            element.addEventListener('mouseleave', () => {
                const tooltip = element.dataset.tooltipId;
                if (tooltip && tooltip.parentNode) {
                    tooltip.parentNode.removeChild(tooltip);
                }
            });
        });
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

    // Utility Functions
    async confirm(message) {
        return new Promise((resolve) => {
            const modal = document.createElement('div');
            modal.className = 'hyro-modal active';
            modal.innerHTML = `
                <div class="hyro-modal-content">
                    <div class="hyro-modal-header">
                        <div class="hyro-modal-title">Confirm</div>
                        <button class="hyro-modal-close" data-hyro-modal-close>&times;</button>
                    </div>
                    <div class="hyro-modal-body">
                        <p>${message}</p>
                    </div>
                    <div class="hyro-modal-footer">
                        <button class="hyro-btn hyro-btn-secondary" data-confirm-action="cancel">Cancel</button>
                        <button class="hyro-btn hyro-btn-danger" data-confirm-action="ok">Confirm</button>
                    </div>
                </div>
            `;

            document.body.appendChild(modal);
            document.body.style.overflow = 'hidden';

            modal.querySelectorAll('[data-confirm-action]').forEach((button) => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    const result = button.dataset.confirmAction === 'ok';
                    modal.remove();
                    document.body.style.overflow = '';
                    resolve(result);
                });
            });

            modal.querySelector('[data-hyro-modal-close]').addEventListener('click', (e) => {
                e.preventDefault();
                modal.remove();
                document.body.style.overflow = '';
                resolve(false);
            });
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
